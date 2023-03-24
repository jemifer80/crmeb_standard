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
use app\services\order\supplier\SupplierOrderServices;
use app\services\supplier\SystemSupplierServices;

/**
 * 公共接口基类 主要存放公共接口
 * Class Common
 * @package app\controller\admin
 */
class Common extends AuthController
{
    /**
     * 首页运营头部统计
     * @param SupplierOrderServices $orderServices
     * @return mixed
     */
    public function homeStatics(SupplierOrderServices $orderServices)
    {
		[$supplierId, $time] = $this->request->getMore([
            ['supplier_id', 0],
            ['data', '', '', 'time']
        ], true);

        $time = $orderServices->timeHandle($time, true);
        $data = $orderServices->homeStatics((int)$supplierId, $time);
        return $this->success($data);
    }

    /**
     * 营业趋势图表
     * @param SupplierOrderServices $orderServices
     * @return mixed
     */
    public function orderChart(SupplierOrderServices $orderServices)
    {
        [$supplierId, $time] = $this->request->getMore([
            ['supplier_id', 0],
            ['data', '', '', 'time']
        ], true);
        $time = $orderServices->timeHandle($time, true);
        $data = $orderServices->orderCharts((int)$supplierId, $time);
        return $this->success($data);
    }

    /**
     * 订单类型分析
     * @param SupplierOrderServices $orderServices
     * @return mixed
     */
    public function orderType(SupplierOrderServices $orderServices)
    {
        [$supplierId, $time] = $this->request->getMore([
            ['supplier_id', 0],
            ['data', '', '', 'time']
        ], true);
        $time = $orderServices->timeHandle($time, true);
        $data = $orderServices->getOrderType((int)$supplierId, $time);
        return $this->success($data);
    }

    /**
     * 订单来源分析
     * @param SupplierOrderServices $orderServices
     * @return mixed
     */
    public function orderChannel(SupplierOrderServices $orderServices)
    {
        [$supplierId, $time] = $this->request->getMore([
            ['supplier_id', 0],
            ['data', '', '', 'time']
        ], true);
        $time = $orderServices->timeHandle($time, true);
        $data = $orderServices->getOrderChannel((int)$supplierId, $time);
        return $this->success($data);
    }

	/**
 	* 首页供应商统计
	* @param SystemSupplierServices $supplierServices
	* @return mixed
	 */
    public function supplierChart(SystemSupplierServices $supplierServices)
    {
        [$supplierId, $time] = $this->request->getMore([
            ['supplier_id', 0],
            ['data', '', '', 'time']
        ], true);
        $time = $supplierServices->timeHandle($time);
        return $this->success($supplierServices->supplierChart((int)$supplierId, $time));
    }

}
