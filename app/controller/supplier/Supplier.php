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

use think\facade\App;
use app\services\supplier\SystemSupplierServices;

/**
 * 供应商控制器
 * Class Supplier
 * @package app\controller\supplier
 */
class Supplier extends AuthController
{

    /**
     * 构造方法
     * Supplier constructor.
     * @param App $app
     * @param SystemSupplierServices $supplierServices
     */
    public function __construct(App $app, SystemSupplierServices $supplierServices)
    {
        parent::__construct($app);
        $this->services = $supplierServices;
    }

    /**
     * 获取供应商信息
     * @return void
     */
    public function read()
    {
        $info = $this->services->getSupplierInfo((int)$this->supplierId);
        return $this->success($info->toArray());
    }

    /**
     * 更新供应商信息
     * @return void
     */
    public function update()
    {
        $data = $this->request->postMore([
            ['supplier_name', ''],
            ['name', ''],
            ['phone', ''],
            ['email', ''],
            ['address', ''],
            ['province', 0],
            ['city', 0],
            ['area', 0],
            ['street', 0],
            ['detailed_address', ''],
        ]);

        $this->validate($data, \app\validate\supplier\SystemSupplierValidate::class, 'update');
        $data['address'] = str_replace([' ', '/', '\\'], '', $data['address']);
        $data['detailed_address'] = str_replace([' ', '/', '\\'], '', $data['detailed_address']);
        $this->services->update((int)$this->supplierId, $data);
        return $this->success('保存成功');
    }
}