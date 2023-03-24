<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\system\admin;

use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserExtractServices;
use app\webscoket\SocketPush;
use crmeb\exceptions\AdminException;
use app\dao\system\admin\SystemAdminDao;
use app\services\system\SystemMenusServices;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder;
use app\services\system\SystemRoleServices;
use crmeb\services\SystemConfigService;
use think\facade\Cache;
use app\services\user\UserServices;

/**
 * 管理员service
 * Class SystemAdminServices
 * @package app\services\system\admin
 * @mixin SystemAdminDao
 */
class SystemAdminServices extends BaseServices
{

    /**
     * form表单创建
     * @var FormBuilder
     */
    protected $builder;

    /**
     * 业务员角色option选项数据
     */
    public $salesman_option;

    /**
     * SystemAdminServices constructor.
     * @param SystemAdminDao $dao
     */
    public function __construct(SystemAdminDao $dao, FormBuilder $builder)
    {
        $this->dao = $dao;
        $this->builder = $builder;
        $this->salesman_option = ['label' => '业务员', 'value' => 2];
    }

    /**
     * 管理员登陆
     * @param string $account
     * @param string $password
     * @param bool $is_mobile
     * @param int $adminType
     * @return array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function verifyLogin(string $account, string $password, bool $is_mobile = false, int $adminType = 1)
    {
		$key = 'login_captcha_' . $account;
        if ($is_mobile) {
            $adminInfo = $this->dao->phoneByAdmin($account, $adminType);
        } else {
            $adminInfo = $this->dao->accountByAdmin($account, $adminType);
        }
        if (!$adminInfo) {
			Cache::inc($key);
            throw new AdminException('管理员不存在!');
        }
        if (!$adminInfo->status) {
			Cache::inc($key);
            throw new AdminException('您已被禁止登录!');
        }
        if (!$is_mobile && !password_verify($password, $adminInfo->pwd)) {
            Cache::inc($key);
            throw new AdminException('账号或密码错误，请重新输入');
        }
        $adminInfo->last_time = time();
        $adminInfo->last_ip = app('request')->ip();
        $adminInfo->login_count++;
        $adminInfo->save();

        return $adminInfo;
    }

    /**
     * 后台登陆获取菜单获取token
     * @param string $account
     * @param string $password
     * @param string $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login(string $account, string $password, string $type, bool $is_mobile = false)
    {
        $adminInfo = $this->verifyLogin($account, $password, $is_mobile, 1);
        $tokenInfo = $this->createToken($adminInfo->id, $type, $adminInfo['pwd']);
        /** @var SystemMenusServices $services */
        $services = app()->make(SystemMenusServices::class);
        [$menus, $uniqueAuth] = $services->getMenusList($adminInfo->roles, (int)$adminInfo['level']);
        $data = SystemConfigService::more(['site_logo', 'site_logo_square', 'new_order_audio_link']);
        return [
            'token' => $tokenInfo['token'],
            'expires_time' => $tokenInfo['params']['exp'],
            'menus' => $menus,
            'unique_auth' => $uniqueAuth,
            'user_info' => [
                'id' => $adminInfo['id'],
                'account' => $adminInfo['account'],
                'head_pic' => $adminInfo['head_pic'],
            ],
            'logo' => $data['site_logo'],
            'logo_square' => $data['site_logo_square'],
            'version' => get_crmeb_version(),
            'newOrderAudioLink' => get_file_link($data['new_order_audio_link'])
        ];
    }

    /**
     * 获取登陆前的login等信息
     * @return array
     */
    public function getLoginInfo()
    {
        $data = SystemConfigService::more(['admin_login_slide', 'site_logo_square', 'site_logo', 'login_logo']);
		return [
            'slide' => sys_config('admin_login_slide') ?? [],
            'logo_square' => $data['site_logo_square'] ?? '',//透明
            'logo_rectangle' => $data['site_logo'] ?? '',//方形
            'login_logo' => $data['login_logo'] ?? '',//登陆
            'version' => get_crmeb_version(),
            'upload_file_size_max' => config('upload.filesize'),//文件上传大小kb
        ];
    }

    /**
     * 获取一些用户的业务员姓名(管理员)
     * @param array $ids
     * @return array
     */
    public function getAdminName(array $ids)
    {
        return $this->dao->getColumn([['id', 'IN', $ids]], 'real_name', 'id');
    }

    /**
     * 获取条件的管理员列表(暂时用于根据roles筛选业务员)
     * @param array $where
     * @param string $field
     */
    public function getWhereAdminList(array $where, string $field = '*')
    {
        if ($where) {
            $whereData = [['roles', '=', $this->salesman_option['value']], ['is_del', '=', 0], $where];
        } else {
            $whereData = [['roles', '=', $this->salesman_option['value']], ['is_del', '=', 0]];
        }
        return $this->dao->getSalesmanList($whereData, $field ?? '*');
    }

    /**
     * 业务员列表
     * @param array $where
     * @return array
     */
    public function getSalesmanList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        //订单支付时间段
        $orderPaymentTime = $where['payment_time'];

        /** @var SystemRoleServices $SystemRoleServices */
        $SystemRoleServices = app()->make(SystemRoleServices::class);
        /** @var UserServices $UserServices */
        $UserServices = app()->make(UserServices::class);
        /** @var StoreOrderServices $StoreOrderServices */
        $StoreOrderServices = app()->make(StoreOrderServices::class);

        $allRole = $SystemRoleServices->getRoleArray();

        foreach ($list as &$item) {
            if ($item['roles']) {
                $roles = [];
                foreach ($item['roles'] as $id) {
                    if (isset($allRole[$id])) $roles[] = $allRole[$id];
                }
                if ($roles) {
                    $item['roles'] = implode(',', $roles);
                } else {
                    $item['roles'] = '';
                }
            }

            $item['_add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['_last_time'] = $item['last_time'] ? date('Y-m-d H:i:s', $item['last_time']) : '';

            $salesman_orders = 0;
            $salesman_amount = 0;
            $salesman_award = 0;
            //获取业务员绑定的用户uid
            $salesman_user_result = $UserServices->getListBySalesman($item['id'],'uid');

            foreach($salesman_user_result as $v):
                if ( $orderPaymentTime != '') {
                    list($startTime, $endTime) = explode('-', $orderPaymentTime);
                    $salesman_orders += $StoreOrderServices->storeOrderCountByUser($v['uid'],'uid',strtotime($startTime),strtotime($endTime) + 24 * 3600);
                    $salesman_amount += $StoreOrderServices->totalSalesByUser($v['uid'],'pay_price',strtotime($startTime),strtotime($endTime) + 24 * 3600);
                    $salesman_award += $StoreOrderServices->totalSalesByUser($v['uid'],'order_award',strtotime($startTime),strtotime($endTime) + 24 * 3600);
                }else{
                    $salesman_orders += $StoreOrderServices->storeOrderCountByUser($v['uid'],'uid');
                    $salesman_amount += $StoreOrderServices->totalSalesByUser($v['uid'],'pay_price');
                    $salesman_award += $StoreOrderServices->totalSalesByUser($v['uid'],'order_award');
                }
            endforeach;

            //业务员绑定用户数量
            $item['salesman_users'] = $UserServices->getCount([['salesman_id','=',$item['id'],['is_del','=',1]]]);
            //业务员绑定用户的下单数量
            $item['salesman_orders'] = $salesman_orders;
            //业务员绑定用户的下单总金额
            $item['salesman_amount'] =number_format($salesman_amount,2);
            $item['salesman_award'] = number_format($salesman_award,2);
        }

        return compact('list', 'count');

    }

    /**
     * 创建业务员表单
     * @param int $level
     * @param array $formData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createAdminSalesmanForm(int $level, array $formData = [])
    {
        if (!$level) {
            $f[] = $this->builder->frameImage('head_pic', '头像', $this->url('supplier/widget.images/index', ['fodder' => 'head_pic'], true), $formData['head_pic'] ?? '')->icon('ios-add')->width('960px')->height('505px')->modal(['footer-hide' => true]);
        }

        $f[] = $this->builder->input('account', '业务员账号', $formData['account'] ?? '')->required('请填写业务员账号');
        if ($formData) {
            $f[] = $this->builder->input('pwd', '业务员密码')->type('password')->placeholder('不修改密码请留空');
            $f[] = $this->builder->input('conf_pwd', '确认密码')->type('password')->placeholder('不修改密码请留空');
        } else {
            $f[] = $this->builder->input('pwd', '业务员密码')->type('password')->required('请填写业务员密码');
            $f[] = $this->builder->input('conf_pwd', '确认密码')->type('password')->required('请输入确认密码');
        }

        $f[] = $this->builder->input('real_name', '业务员姓名', $formData['real_name'] ?? '')->required('请输入业务员姓名');
        $f[] = $this->builder->input('phone', '业务员电话', $formData['phone'] ?? '')->required('请输入业务员电话');

        /** @var SystemRoleServices $service */
        $service = app()->make(SystemRoleServices::class);
        $options = $service->getRoleFormSelect($level);
        $roles = [];
        if ($formData && ($formData['roles'] ?? [])) {
            foreach ($formData['roles'] as $role) {
                $roles[] = (int)$role;
            }
        }
        if ($level) {
            $f[] = $this->builder->select('roles', '管理员身份', $roles)->setOptions(FormBuilder::setOptions([$this->salesman_option]))->multiple(true)->required('请选择管理员身份');
        }
        $f[] = $this->builder->radio('status', '状态', $formData['status'] ?? 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return $f;
    }

    /**
     * 添加业务员form表单获取
     * @param int $level
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createSalesmanForm(int $level)
    {
        return create_form('业务员添加', $this->createAdminSalesmanForm($level), $this->url('/setting/admin'));
    }

    /**
     * 创建业务员
     * @param array $data
     * @return bool
     */
    public function createSalesman(array $data)
    {

        if ($data['conf_pwd'] != $data['pwd']) {
            throw new AdminException('两次输入的密码不相同');
        }
        unset($data['conf_pwd']);

        if ($this->dao->count(['account' => $data['account'], 'admin_type' => $data['admin_type'] ?? 1, 'is_del' => 0])) {
            throw new AdminException('管理员账号已存在');
        }

        if ($this->dao->count(['phone' => $data['phone'], 'admin_type' => $data['admin_type'] ?? 1, 'is_del' => 0])) {
            throw new AdminException('管理员电话已存在');
        }

        $data['pwd'] = $this->passwordHash($data['pwd']);
        $data['add_time'] = time();
        $data['roles'] = implode(',', $data['roles']);

        return $this->transaction(function () use ($data) {
            if ($this->dao->save($data)) {
                \crmeb\services\CacheService::clear();
                return true;
            } else {
                throw new AdminException('添加失败');
            }
        });

    }

    /**
     * 修改业务员表单
     * @param int $level
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function updateSalesmanForm(int $level, int $id, string $url = '/setting/admin/')
    {
        $adminInfo = $this->dao->get($id);
        if (!$adminInfo) {
            throw new AdminException('管理员不存在!');
        }
        if ($adminInfo->is_del) {
            throw new AdminException('管理员已经删除');
        }
        return create_form('业务员修改', $this->createAdminSalesmanForm($level, $adminInfo->toArray()), $this->url($url . $id), 'PUT');
    }

    /**
     * 管理员列表
     * @param array $where
     * @return array
     */
    public function getAdminList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);

        /** @var SystemRoleServices $service */
        $service = app()->make(SystemRoleServices::class);
        $allRole = $service->getRoleArray(['type' => 1]);
        foreach ($list as &$item) {
            if ($item['roles']) {
                $roles = [];
                foreach ($item['roles'] as $id) {
                    if (isset($allRole[$id])) $roles[] = $allRole[$id];
                }
                if ($roles) {
                    $item['roles'] = implode(',', $roles);
                } else {
                    $item['roles'] = '';
                }
            }
            $item['_add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['_last_time'] = $item['last_time'] ? date('Y-m-d H:i:s', $item['last_time']) : '';
        }
        return compact('list', 'count');
    }

    /**
     * 创建管理员表单
     * @param int $level
     * @param array $formData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createAdminForm(int $level, array $formData = [])
    {
        if (!$level) {
            $f[] = $this->builder->frameImage('head_pic', '头像', $this->url('supplier/widget.images/index', ['fodder' => 'head_pic'], true), $formData['head_pic'] ?? '')->icon('ios-add')->width('960px')->height('505px')->modal(['footer-hide' => true]);
        }

        $f[] = $this->builder->input('account', '管理员账号', $formData['account'] ?? '')->required('请填写管理员账号');
        if ($formData) {
            $f[] = $this->builder->input('pwd', '管理员密码')->type('password')->placeholder('不修改密码请留空');
            $f[] = $this->builder->input('conf_pwd', '确认密码')->type('password')->placeholder('不修改密码请留空');
        } else {
            $f[] = $this->builder->input('pwd', '管理员密码')->type('password')->required('请填写管理员密码');
            $f[] = $this->builder->input('conf_pwd', '确认密码')->type('password')->required('请输入确认密码');
        }

        $f[] = $this->builder->input('real_name', '管理员姓名', $formData['real_name'] ?? '')->required('请输入管理员姓名');
        $f[] = $this->builder->input('phone', '管理员电话', $formData['phone'] ?? '')->required('请输入管理员电话');

        /** @var SystemRoleServices $service */
        $service = app()->make(SystemRoleServices::class);
        $options = $service->getRoleFormSelect($level);
        $roles = [];
        if ($formData && ($formData['roles'] ?? [])) {
            foreach ($formData['roles'] as $role) {
                $roles[] = (int)$role;
            }
        }
        if ($level) {
            $f[] = $this->builder->select('roles', '管理员身份', $roles)->setOptions(FormBuilder::setOptions($options))->multiple(true)->required('请选择管理员身份');
        }
        $f[] = $this->builder->radio('status', '状态', $formData['status'] ?? 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return $f;
    }

    /**
     * 添加管理员form表单获取
     * @param int $level
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm(int $level, string $url = '/setting/admin')
    {
        return create_form('管理员添加', $this->createAdminForm($level), $this->url($url));
    }



    /**
     * 创建管理员
     * @param array $data
     * @return bool
     */
    public function create(array $data)
    {
        if ($data['conf_pwd'] != $data['pwd']) {
            throw new AdminException('两次输入的密码不相同');
        }
        unset($data['conf_pwd']);

        if ($this->dao->count(['account' => $data['account'], 'admin_type' => $data['admin_type'] ?? 1, 'is_del' => 0])) {
            throw new AdminException('管理员账号已存在');
        }
        if ($this->dao->count(['phone' => $data['phone'], 'admin_type' => $data['admin_type'] ?? 1, 'is_del' => 0])) {
            throw new AdminException('管理员电话已存在');
        }

        $data['pwd'] = $this->passwordHash($data['pwd']);
        $data['add_time'] = time();
        $data['roles'] = implode(',', $data['roles']);

        return $this->transaction(function () use ($data) {
            if ($this->dao->save($data)) {
                \crmeb\services\CacheService::clear();
                return true;
            } else {
                throw new AdminException('添加失败');
            }
        });
    }

    /**
     * 修改管理员表单
     * @param int $level
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function updateForm(int $level, int $id, string $url = '/setting/admin/')
    {
        $adminInfo = $this->dao->get($id);
        if (!$adminInfo) {
            throw new AdminException('管理员不存在!');
        }
        if ($adminInfo->is_del) {
            throw new AdminException('管理员已经删除');
        }
        return create_form('管理员修改', $this->createAdminForm($level, $adminInfo->toArray()), $this->url($url . $id), 'PUT');
    }

    /**
     * 修改管理员
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function save(int $id, array $data)
    {
        if (!$adminInfo = $this->dao->get($id)) {
            throw new AdminException('管理员不存在,无法修改');
        }
        if ($adminInfo->is_del) {
            throw new AdminException('管理员已经删除');
        }
        //修改密码
        if ($data['pwd']) {

            if (!$data['conf_pwd']) {
                throw new AdminException('请输入确认密码');
            }

            if ($data['conf_pwd'] != $data['pwd']) {
                throw new AdminException('上次输入的密码不相同');
            }
            $adminInfo->pwd = $this->passwordHash($data['pwd']);
        }
        //修改账号
        if (isset($data['account']) && $data['account'] != $adminInfo->account && $this->dao->isAccountUsable($data['account'], $id)) {
            throw new AdminException('管理员账号已存在');
        }
        if (isset($data['phone']) && $data['phone'] != $adminInfo->phone && $this->dao->count(['phone' => $data['phone'], 'is_del' => 0])) {
            throw new AdminException('管理员电话已存在');
        }
        if (isset($data['roles'])) {
            $adminInfo->roles = implode(',', $data['roles']);
        }
        $adminInfo->real_name = $data['real_name'] ?? $adminInfo->real_name;
        $adminInfo->phone = $data['phone'] ?? $adminInfo->phone;
        $adminInfo->account = $data['account'] ?? $adminInfo->account;
        $adminInfo->head_pic = $data['head_pic'] ?? $adminInfo->head_pic;
        $adminInfo->status = $data['status'];
        if ($adminInfo->save()) {
            \crmeb\services\CacheService::clear();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改当前管理员信息
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateAdmin(int $id, array $data)
    {
        $adminInfo = $this->dao->get($id);
        if (!$adminInfo)
            throw new AdminException('管理员信息未查到');
        if ($adminInfo->is_del) {
            throw new AdminException('管理员已经删除');
        }
        if ($data['head_pic'] != '') {
            $adminInfo->head_pic = $data['head_pic'];
        } elseif ($data['real_name'] != '') {
            $adminInfo->real_name = $data['real_name'];
        } elseif ($data['pwd'] != '') {
            if (!password_verify($data['pwd'], $adminInfo['pwd']))
                throw new AdminException('原始密码错误');
            if (!$data['new_pwd'])
                throw new AdminException('请输入新密码');
            if (!$data['conf_pwd'])
                throw new AdminException('请输入确认密码');
            if ($data['new_pwd'] != $data['conf_pwd'])
                throw new AdminException('两次输入的密码不一致');
            $adminInfo->pwd = $this->passwordHash($data['new_pwd']);
        } elseif ($data['phone'] != '') {
            $verifyCode = CacheService::get('code_' . $data['phone']);
            if (!$verifyCode)
                throw new AdminException('请先获取验证码');
            $verifyCode = substr($verifyCode, 0, 6);
            if ($verifyCode != $data['code']) {
                CacheService::delete('code_' . $data['phone']);
                throw new AdminException('验证码错误');
            }
            $adminInfo->phone = $data['phone'];
        }
        if ($adminInfo->save()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 后台订单下单，评论，支付成功，后台消息提醒
     */
    public function adminNewPush()
    {
        try {
            /** @var StoreOrderServices $orderServices */
            $orderServices = app()->make(StoreOrderServices::class);
            $data['ordernum'] = $orderServices->count(['is_del' => 0, 'status' => 1, 'shipping_type' => 1]);
            /** @var StoreProductServices $productServices */
            $productServices = app()->make(StoreProductServices::class);
            $data['inventory'] = $productServices->count(['type' => 5]);
            /** @var StoreProductReplyServices $replyServices */
            $replyServices = app()->make(StoreProductReplyServices::class);
            $data['commentnum'] = $replyServices->count(['is_reply' => 0]);
            /** @var UserExtractServices $extractServices */
            $extractServices = app()->make(UserExtractServices::class);
            $data['reflectnum'] = $extractServices->getCount(['status' => 0]);//提现
            $data['msgcount'] = intval($data['ordernum']) + intval($data['inventory']) + intval($data['commentnum']) + intval($data['reflectnum']);
            SocketPush::admin()->type('ADMIN_NEW_PUSH')->data($data)->push();
        } catch (\Exception $e) {
        }
    }

    /**
     * 短信修改密码
     * @param $phone
     * @param $newPwd
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function resetPwd($phone, $newPwd)
    {
        $adminInfo = $this->dao->phoneByAdmin($phone);
        if ($adminInfo) {
            $adminInfo->pwd = $this->passwordHash($newPwd);
            $adminInfo->save();
            return true;
        } else {
            throw new AdminException('管理员不存在，请检查手机号码');
        }

    }

    /**
     * 获取供应商接收通知管理员
     * @param int $supplier_id
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNotifySupplierList(int $supplier_id, string $field = '*')
    {
        $where = [
            'relation_id' => $supplier_id,
            'status' => 1,
            'is_del' => 0
        ];
        $list = $this->dao->getList($where, 0, 0, $field);
        return $list;
    }

}
