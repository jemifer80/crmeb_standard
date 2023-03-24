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

use app\services\supplier\SupplierTicketPrintServices;
use think\facade\App;

/**
 * Class SupplierTicketPrint
 * @package app\controller\supplier
 */

class SupplierTicketPrint extends AuthController
{

    /**
     * 构造方法
     * Supplier constructor.
     * @param App $app
     * @param SupplierTicketPrintServices $services
     */
    public function __construct(App $app, SupplierTicketPrintServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取打印信息
     * @param int $id
     * @return void
     */
    public function read()
    {
        return $this->success($this->services->getTicketInfo((int)$this->supplierId));
    }

    /**
     * 更新打印信息
     * @param int $id
     * @return void
     */
    public function update()
    {
        $data = $this->request->postMore([
            ['develop_id', 0],
            ['api_key', ''],
            ['client_id', ''],
            ['terminal_number', ''],
            ['status', 0],
        ]);

        $this->validate($data, \app\validate\supplier\SupplierTicketPrintValidate::class, 'update');
        $this->services->savePrintData((int)$this->supplierId, $data);
        return $this->success('保存成功');
    }
}