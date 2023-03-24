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
namespace app\controller\supplier;

use app\services\supplier\LoginServices;
use app\services\supplier\SystemSupplierServices;
use app\services\system\admin\SystemAdminServices;
use think\facade\{App, Config};

/**
 * Class SystemAdmin
 * @package app\controller\admin\v1\setting
 */
class SupplierAdmin extends AuthController
{
    /**
     * @var LoginServices|null
     */
    protected $services = NUll;

    /**
     * @var SystemAdminServices|null
     */
    protected $adminServices = NUll;

    /**
     * SystemAdmin constructor.
     * @param App $app
     * @param SystemSupplierServices $services
     * @param SystemAdminServices $adminServices
     */
    public function __construct(App $app, SystemSupplierServices $services, SystemAdminServices $adminServices)
    {
        parent::__construct($app);
        $this->services = $services;
        $this->adminServices = $adminServices;
    }

    /**
     * 显示管理员资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = [
            'is_del' => 1,
            'admin_type' => 4,
            'relation_id' => $this->supplierId,
            'level' => 1
        ];
        return $this->success($this->adminServices->getAdminList($where));
    }

    /**
     * 创建表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return $this->success($this->adminServices->createForm(0, '/admin'));
    }

    /**
     * 保存管理员
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['phone', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['real_name', ''],
            ['phone', ''],
            ['roles', []],
            ['status', 0],
            ['head_pic', ''],
        ]);

        $this->validate($data, \app\validate\admin\setting\SystemAdminValidata::class, 'supplier_save');
        $data['admin_type'] = 4;
        $data['relation_id'] = $this->supplierId;
        if ($this->adminServices->create($data)) {
            return $this->success('添加成功');
        } else {
            return $this->fail('添加失败');
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit(int $id)
    {
        if (!$id) {
            return $this->fail('管理员信息读取失败');
        }
        return $this->success($this->adminServices->updateForm(0, (int)$id, '/admin/'));
    }

    /**
     * 更新管理员
     * @param int $id
     * @return mixed
     */
    public function update(int $id)
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['phone', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['real_name', ''],
            ['phone', ''],
            ['roles', []],
            ['status', 0],
            ['head_pic', ''],
        ]);

        $this->validate($data, \app\validate\admin\setting\SystemAdminValidata::class, 'supplier_update');
        if ($this->adminServices->save($id, $data)) {
            return $this->success('修改成功');
        } else {
            return $this->fail('修改失败');
        }
    }

    /**
     * 管理员详情
     * @param int $id
     * @return mixed
     */
    public function read(int $id)
    {
        $info = $this->adminServices->get($id);
        if (!$info) {
            return $this->fail('获取失败');
        }
        return $this->success($info->toArray());
    }

    /**
     * 删除管理员
     * @param int $id
     * @return void
     */
    public function delete(int $id)
    {
        if (!$id) {
            return $this->fail('删除失败，缺少参数');
        }

        if ($this->adminServices->update($id, ['is_del' => 1, 'status' => 0])) {
            return $this->success('删除成功！');
        } else {
            return $this->fail('删除失败');
        }
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        $this->adminServices->update((int)$id, ['status' => $status]);
        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }
}
