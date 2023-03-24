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

namespace app\controller\admin\v1\supplier;

use app\controller\admin\AuthController;
use app\services\supplier\LoginServices;
use app\services\supplier\SystemSupplierServices;
use think\facade\App;

/**
 * 供应商管理控制器
 * Class SystemSupplier
 * @package app\controller\admin\v1\supplier
 */
class SystemSupplier extends AuthController
{

    /**
     * 构造方法
     * SystemSupplier constructor.
     * @param App $app
     * @param SystemSupplierServices $services
     */
    public function __construct(App $app, SystemSupplierServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取供应商列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            [['keywords', 's'], ''],
        ]);
        $where['is_del'] = 0;
        return $this->success($this->services->getSupplierList($where, ['id', 'supplier_name', 'name', 'phone', 'address', 'is_show', 'add_time', 'mark', 'sort']));
    }

    /**
     * 保存供应商
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['supplier_name', ''],
            ['account', ''],
            ['name', ''],
            ['phone', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['email', ''],
            ['roles', []],
            ['is_show', 0],
            ['sort', 0],
            ['address', ''],
            ['province', 0],
            ['city', 0],
            ['area', 0],
            ['street', 0],
            ['detailed_address', ''],
            ['mark', '']
        ]);

        $this->validate($data, \app\validate\supplier\SystemSupplierValidate::class, 'save');

        $this->services->create($data);
        return $this->success('添加成功');
    }

    /**
     * 修改供应商信息
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        if (!$id) return $this->fail('缺少参数');
        $data = $this->request->postMore([
            ['supplier_name', ''],
            ['account', ''],
            ['name', ''],
            ['phone', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['email', ''],
            ['roles', []],
            ['is_show', 0],
            ['sort', 0],
            ['address', ''],
            ['province', 0],
            ['city', 0],
            ['area', 0],
            ['street', 0],
            ['detailed_address', ''],
            ['mark', '']
        ]);

        $this->validate($data, \app\validate\supplier\SystemSupplierValidate::class, 'save');

        $this->services->save((int)$id, $data);
        return $this->success('修改成功');
    }

    /**
     * 删除供应商
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('删除失败，缺少参数');
        $this->services->delete((int)$id, ['is_del' => 1, 'status' => 0]);
        return $this->success('删除成功！');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        $this->services->update((int)$id, ['is_show' => $status]);
        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }

    /**
     * 获取供应商信息
     * @return mixed
     */
    public function read($id)
    {
        if (!$id) return $this->fail('缺少参数');
        $info = $this->services->getSupplierInfo((int)$id, 'id, supplier_name, name, phone, admin_id, email, address, province, city, area, street, detailed_address, sort, is_show, mark', ['admin']);
        $info->hidden(['roles', 'admin_is_del', 'admin_type', 'level','admin_id']);
		$info = $info->toArray();
		$info['pwd'] = '';
        return $this->success($info);
    }

    /**
     * 供应商选择列表
     * @return mixed
     */
    public function search()
    {
        return $this->success($this->services->getSupplierSearch(['is_del' => 0], ['id', 'supplier_name']));
    }

    /**
     * 供应登录
     * @param LoginServices $services
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function supplierLogin(LoginServices $services, $id)
    {
        $supplierInfo = $this->services->getOne(['id' => $id, 'is_del' => 0], '*', ['admin']);
        if (!$supplierInfo || !$supplierInfo->account || $supplierInfo->admin_is_del) {
            return $this->fail('供应商管理员异常');
        }
        return $this->success($services->login($supplierInfo['account'], '', 'supplier'));
    }
}