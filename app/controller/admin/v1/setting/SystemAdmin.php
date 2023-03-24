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
namespace app\controller\admin\v1\setting;

use app\controller\admin\AuthController;
use app\services\system\admin\SystemAdminServices;
use crmeb\services\CacheService;
use think\facade\{App, Config};

/**
 * Class SystemAdmin
 * @package app\controller\admin\v1\setting
 */
class SystemAdmin extends AuthController
{
    /**
     * SystemAdmin constructor.
     * @param App $app
     * @param SystemAdminServices $services
     */
    public function __construct(App $app, SystemAdminServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示管理员资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['name', '', '', 'account_like'],
            ['roles', ''],
            ['is_del', 1],
            ['status', '']
        ]);
        $where['level'] = $this->adminInfo['level'] + 1;
        $where['admin_type'] = 1;
        return $this->success($this->services->getAdminList($where));
    }

    /**
     * 显示业务员资源列表
     *
     * @return \think\Response
     */
    public function salesman_list()
    {
        $where = $this->request->getMore([
            ['name', '', '', 'account_like'],
            ['roles', ''],
            ['is_del', 1],
            ['status', ''],
            ['payment_time','']
        ]);
        $where['level'] = $this->adminInfo['level'] + 1;
        $where['admin_type'] = 1;
        $where['roles'] = $this->services->salesman_option['value'];
        return app('json')->success($this->services->getSalesmanList($where));
    }

    /**
     * 创建业务员表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create_salesman()
    {
        return app('json')->success($this->services->createSalesmanForm($this->adminInfo['level'] + 1));
    }

    /**
     * 保存业务员
     * @return mixed
     */
    public function save_salesman()
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['real_name', ''],
            ['roles', []],
            ['status', 0]
        ]);

        $this->validate($data, \app\adminapi\validate\setting\SystemAdminValidata::class);

        $data['level'] = $this->adminInfo['level'] + 1;
        $data['is_salesman'] = 1;
        $this->services->createSalesman($data);
        return $this->success('添加成功');
    }

    /**
     * 显示编辑业务员表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit_salesman($id)
    {
        if (!$id) {
            return $this->fail('管理员信息读取失败');
        }

        return app('json')->success($this->services->updateSalesmanForm($this->adminInfo['level'] + 1, (int)$id));
    }

    /**
     * 创建表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return $this->success($this->services->createForm($this->adminInfo['level'] + 1));
    }

    /**
     * 保存管理员
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['real_name', ''],
            ['phone', ''],
            ['roles', []],
            ['status', 0],
        ]);

        $this->validate($data, \app\validate\admin\setting\SystemAdminValidata::class);

        $data['level'] = $this->adminInfo['level'] + 1;
        $this->services->create($data);
        return $this->success('添加成功');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) {
            return $this->fail('管理员信息读取失败');
        }

        return $this->success($this->services->updateForm($this->adminInfo['level'] + 1, (int)$id));
    }

    /**
     * 修改管理员信息
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['real_name', ''],
            ['phone', ''],
            ['roles', []],
            ['status', 0],
        ]);

        $this->validate($data, \app\validate\admin\setting\SystemAdminValidata::class, 'update');

        if ($this->services->save((int)$id, $data)) {
            return $this->success('修改成功');
        } else {
            return $this->fail('修改失败');
        }
    }

    /**
     * 删除管理员
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('删除失败，缺少参数');
        if ($this->services->update((int)$id, ['is_del' => 1, 'status' => 0]))
            return $this->success('删除成功！');
        else
            return $this->fail('删除失败');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        $this->services->update((int)$id, ['status' => $status]);
        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }

    /**
     * 获取当前登陆管理员的信息
     * @return mixed
     */
    public function info()
    {
        return $this->success($this->adminInfo);
    }

    /**
     * 修改当前登陆admin信息
     * @return mixed
     */
    public function update_admin()
    {
        $data = $this->request->postMore([
            ['real_name', ''],
            ['head_pic', ''],
            ['pwd', ''],
            ['new_pwd', ''],
            ['conf_pwd', ''],
            ['phone', ''],
            ['code', '']
        ]);
        if ($this->services->updateAdmin($this->adminId, $data))
            return $this->success('修改成功');
        else
            return $this->fail('修改失败');
    }

    /**
     * 退出登陆
     * @return mixed
     */
    public function logout()
    {
        $key = trim(ltrim($this->request->header(Config::get('cookie.token_name')), 'Bearer'));
        CacheService::redisHandler()->delete(md5($key));
        return $this->success();
    }
}
