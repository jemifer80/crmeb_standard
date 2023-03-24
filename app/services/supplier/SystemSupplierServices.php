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
namespace app\services\supplier;

use app\dao\supplier\SystemSupplierDao;
use app\dao\system\admin\SystemAdminDao;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderRefundServices;
use app\services\system\admin\SystemAdminServices;
use crmeb\exceptions\AdminException;
use think\exception\ValidateException;

/**
 * 供应商
 * Class SystemSupplierServices
 * @package app\services\supplier
 * @mixin SystemSupplierDao
 */
class SystemSupplierServices extends BaseServices
{

    protected $adminDao = null;

    /**
     * 构造方法
     * SystemSupplierServices constructor.
     * @param SystemSupplierDao $dao
     * @param SystemAdminDao $adminDao
     */
    public function __construct(SystemSupplierDao $dao, SystemAdminDao $adminDao)
    {
        $this->dao = $dao;
        $this->adminDao = $adminDao;
    }

    /**
     * 获取供应商
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\DataNotFoundException
     */
    public function getSupplierInfo(int $id, string $field = '*', array $with = [])
    {
        $info = $this->dao->getOne(['id' => $id, 'is_del' => 0], $field, $with);
        if (!$info) {
            throw new ValidateException('供应商不存在');
        }
        return $info;
    }

    /**
     * 供应商列表
     * @param array $where
     * @param array $field
     * @return void
     */
    public function getSupplierList(array $where, array $field = ['*'])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getSupplierList($where, $field, $page, $limit);
        if (array_search('add_time', $field)) {
            foreach ($list as &$item) {
                $item['_add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            }
        }

        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 保存供应商
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->transaction(function () use ($data) {
            if (!$data['pwd']) {
                throw new AdminException('请输入登录密码');
            }

            if ($data['conf_pwd'] != $data['pwd']) {
                throw new AdminException('两次输入的密码不相同');
            }
            unset($data['conf_pwd']);

            /** @var SystemAdminServices $adminServices */
            $adminServices = app()->make(SystemAdminServices::class);

            if ($this->adminDao->count(['account' => $data['account'], 'admin_type' => 4, 'is_del' => 0])) {
                throw new AdminException('管理员账号已存在');
            }
            if ($this->adminDao->count(['phone' => $data['phone'], 'admin_type' => 4, 'is_del' => 0])) {
                throw new AdminException('管理员电话已存在');
            }

            $adminData = [
                'pwd' => $adminServices->passwordHash($data['pwd']),
                'admin_type' => 4,
                'account' => $data['account'],
                'roles' => '',
                'real_name' => $data['name'],
                'phone' => $data['phone'],
                'add_time' => time(),
                'level' => 0
            ];

            unset($data['pwd']);
            unset($data['account']);

            // 创建管理员
            $res = $this->adminDao->save($adminData);
            if (!$res) throw new AdminException('管理员添加失败');

            $data['admin_id'] = (int)$res->id;
            $data['add_time'] = time();

            // 创建供应商
            $relation_id = $this->dao->save($data)->id;
            if (!$relation_id) throw new AdminException('供应商添加失败');

            $this->adminDao->update($res->id, ['relation_id' => $relation_id]);
            return true;
        });
    }

    /**
     * 修改管理员
     * @param array $data
     * @return mixed
     */
    public function save(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {

            if (!$supplierInfo = $this->dao->get($id)) {
                throw new AdminException('供应商不存在,无法修改');
            }
            if ($supplierInfo->is_del) {
                throw new AdminException('供应商已经删除');
            }

            if (!$adminInfo = $this->adminDao->get($supplierInfo['admin_id'])) {
                throw new AdminException('管理员不存在,无法修改');
            }
            if ($adminInfo->is_del) {
                throw new AdminException('管理员已经删除');
            }

            //修改账号
            if (isset($data['account']) && $data['account'] != $adminInfo->account && $this->adminDao->isAccountUsable($data['account'], $supplierInfo['admin_id'])) {
                throw new AdminException('管理员账号已存在');
            }
            if (isset($data['phone']) && $data['phone'] != $adminInfo->phone && $this->adminDao->count(['phone' => $data['phone'], 'is_del' => 0])) {
                throw new AdminException('管理员电话已存在');
            }

            if ($data['pwd']) {
                if (!$data['conf_pwd']) {
                    throw new AdminException('请输入确认密码');
                }

                if ($data['conf_pwd'] != $data['pwd']) {
                    throw new AdminException('上次输入的密码不相同');
                }
                /** @var SystemAdminServices $adminServices */
                $adminServices = app()->make(SystemAdminServices::class);
                $adminInfo->pwd = $adminServices->passwordHash($data['pwd']);

            }

            $adminInfo->real_name = $data['name'] ?? $adminInfo->real_name;
            $adminInfo->phone = $data['phone'] ?? $adminInfo->phone;
            $adminInfo->account = $data['account'] ?? $adminInfo->account;

            unset($data['account']);
            unset($data['pwd']);
            unset($data['conf_pwd']);

            // 修改管理员
            $res = $adminInfo->save();
            if (!$res) throw new AdminException('管理员修改失败');

            $supplierInfo->supplier_name = $data['supplier_name'];
            $supplierInfo->name = $data['name'];
            $supplierInfo->phone = $data['phone'];
            $supplierInfo->email = $data['email'];
            $supplierInfo->address = $data['address'];
            $supplierInfo->province = $data['province'];
            $supplierInfo->city = $data['city'];
            $supplierInfo->area = $data['area'];
            $supplierInfo->street = $data['street'];
            $supplierInfo->detailed_address = $data['detailed_address'];
            $supplierInfo->sort = $data['sort'];
            $supplierInfo->is_show = $data['is_show'];
            $supplierInfo->mark = $data['mark'];
            // 修改供应商
            $res1 = $supplierInfo->save();
            if (!$res1) throw new AdminException('供应商修改失败');
            return true;
        });
    }

    public function delete(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {

            if (!$supplierInfo = $this->dao->get($id)) {
                throw new AdminException('供应商不存在,无法修改');
            }
            if ($supplierInfo->is_del) {
                throw new AdminException('供应商已经删除');
            }

            if (!$adminInfo = $this->adminDao->get($supplierInfo['admin_id'])) {
                throw new AdminException('管理员不存在,无法删除');
            }
            if ($adminInfo->is_del) {
                throw new AdminException('管理员已经删除');
            }

            $adminInfo->status = 0;
            $adminInfo->is_del = 1;

            // 修改管理员
            $res = $adminInfo->save();
            if (!$res) throw new AdminException('管理员删除失败');

            $supplierInfo->is_show = 0;
            $supplierInfo->is_del = 1;
            // 修改供应商
            $res1 = $supplierInfo->save();
            if (!$res1) throw new AdminException('供应商删除失败');
            return true;
        });
    }


    /**
     * 平台供应商运营统计
     * @param int $supplierId
     * @param array $time
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function supplierChart(int $supplierId, array $time)
    {
        $list = $this->dao->getSupplierList(['is_del' => 0, 'is_show' => 1], ['id', 'supplier_name']);
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        /** @var  StoreOrderRefundServices $orderRefundServices */
        $orderRefundServices = app()->make(StoreOrderRefundServices::class);
        $where = ['time' => $time];

        $order_where = ['paid' => 1, 'pid' => 0, 'is_del' => 0, 'is_system_del' => 0, 'refund_status' => [0, 3]];
        $refund_where = ['refund_type' => 6];
        foreach ($list as &$item) {
            $supplier_where = ['supplier_id' => $item['id']];
            $item['order_price'] = $orderServices->sum($where + $supplier_where + $order_where, 'pay_price', true);
            $item['order_count'] = $orderServices->count($where + $supplier_where + $order_where);
            $item['refund_order_price'] = $orderRefundServices->sum($where + $supplier_where + $refund_where, 'refunded_price', true);
            $item['refund_order_count'] = $orderRefundServices->count($where + $supplier_where + $refund_where);
        }
        return $list;
    }

    /**
     * 供应商选择列表
     * @param array $where
     * @param array $field
     * @return void
     */
    public function getSupplierSearch(array $where, array $field = ['*'])
    {
        return $this->dao->getSupplierList($where, $field, 0, 0);
    }
}
