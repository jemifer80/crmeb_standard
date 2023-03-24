<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\controller\supplier;

use app\common\controller\Order as CommonOrder;
use app\Request;
use app\services\order\StoreOrderDeliveryServices;
use app\services\order\StoreOrderServices;
use app\services\store\DeliveryServiceServices;
use app\services\order\supplier\SupplierOrderServices;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\App;

/**
 * Class Order
 * @package app\controller\supplier
 * @property Request $request
 */
class Order extends AuthController
{

    use CommonOrder;

    protected $orderServices;

    /**
     * Order constructor.
     * @param App $app
     * @param SupplierOrderServices $service
     * @param StoreOrderServices $orderServices
     */
    public function __construct(App $app, SupplierOrderServices $supplierOrderServices, StoreOrderServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
        $this->supplierOrderServices = $supplierOrderServices;
    }

    /**
     * 配货单信息
     * @return mixed
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws \think\db\exception\DbException
     */
    public function distributionInfo()
    {
		[$ids] = $this->request->postMore([
            ['ids', '']
        ], true);
		if (!$ids) {
			return app('json')->fail('缺少参数');
		}
		$id = explode(',', $ids);
        $data = $this->supplierOrderServices->getDistribution($id);
        if (!$data) {
            return $this->fail('获取失败');
        }
        return $this->success('', $data);
    }

    /**
     * 获取订单列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst(Request $request)
    {
        $where = $request->getMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', ''],
            ['data', '', '', 'time'],
            ['type', ''],
            ['pay_type', ''],
            ['order', ''],
            ['field_key', ''],
        ]);
        $where['type'] = trim($where['type']);
        $where['is_system_del'] = 0;

        $where['supplier_id'] = $this->supplierId;
        $where['type'] = trim($where['type'], ' ');
        return $this->success($this->services->getOrderList($where, ['*'], ['split' => function ($query) {
            $query->field('id,pid');
        }, 'pink', 'invoice']));
    }

    /**
     *获取所有配送员列表
     */
    public function get_delivery_list()
    {
        /** @var DeliveryServiceServices $deliverServices */
        $deliverServices = app()->make(DeliveryServiceServices::class);
        $data = $deliverServices->getDeliveryList();
        return $this->success($data);
    }

	    /**
     * 订单发送货
     * @param Request $request
     * @param StoreOrderDeliveryServices $services
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update_delivery(Request $request, StoreOrderDeliveryServices $services, $id)
    {
        $data = $request->postMore([
            ['type', 1],
            ['delivery_name', ''],//快递公司名称
            ['delivery_id', ''],//快递单号
            ['delivery_code', ''],//快递公司编码

            ['express_record_type', 2],//发货记录类型
            ['express_temp_id', ""],//电子面单模板
            ['to_name', ''],//寄件人姓名
            ['to_tel', ''],//寄件人电话
            ['to_addr', ''],//寄件人地址

            ['sh_delivery_name', ''],//送货人姓名
            ['sh_delivery_id', ''],//送货人电话
            ['sh_delivery_uid', ''],//送货人ID
            ['delivery_type', 1],//送货类型
            ['station_type', 1],//送货类型
			['cargo_weight', 0],//重量
			['mark', ''],//备注
			['remark', ''],//配送备注

            ['fictitious_content', '']//虚拟发货内容
        ]);
        if (!$id) {
            return app('json')->fail('缺少发货ID');
        }
		$services->setItem('supplier_id', $this->supplierId);
        $services->delivery((int)$id, $data);
		$services->reset();
        return app('json')->success('SUCCESS');
    }

    /**
     * 订单拆单发送货
     * @param Request $request
     * @param StoreOrderDeliveryServices $services
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function split_delivery(Request $request, StoreOrderDeliveryServices $services, $id)
    {
        $data = $request->postMore([
            ['type', 1],
            ['delivery_name', ''],//快递公司名称
            ['delivery_id', ''],//快递单号
            ['delivery_code', ''],//快递公司编码

            ['express_record_type', 2],//发货记录类型
            ['express_temp_id', ""],//电子面单模板
            ['to_name', ''],//寄件人姓名
            ['to_tel', ''],//寄件人电话
            ['to_addr', ''],//寄件人地址

            ['sh_delivery_name', ''],//送货人姓名
            ['sh_delivery_id', ''],//送货人电话
            ['sh_delivery_uid', ''],//送货人ID
            ['delivery_type', 1],//送货类型
            ['station_type', 1],//送货类型
			['cargo_weight', 0],//重量
			['mark', ''],//备注
			['remark', ''],//配送备注

            ['fictitious_content', ''],//虚拟发货内容

            ['cart_ids', []]
        ]);
        if (!$id) {
            return app('json')->fail('缺少发货ID');
        }
        if (!$data['cart_ids']) {
            return app('json')->fail('请选择发货商品');
        }
        foreach ($data['cart_ids'] as $cart) {
            if (!isset($cart['cart_id']) || !$cart['cart_id'] || !isset($cart['cart_num']) || !$cart['cart_num']) {
                return app('json')->fail('请重新选择发货商品，或发货件数');
            }
        }
		$services->setItem('supplier_id', $this->supplierId);
        $services->splitDelivery((int)$id, $data);
		$services->reset();
        return app('json')->success('SUCCESS');
    }


    /**
     * 获取订单拆分子订单列表
     * @return mixed
     */
    public function split_order(Request $request, $id)
    {
        if (!$id) {
            return app('json')->fail('缺少订单ID');
        }
        $where = ['pid' => $id, 'is_system_del' => 0, 'supplier_id' => $this->supplierId];
        if (!$this->services->count($where)) {
            $where = ['id' => $id, 'is_system_del' => 0, 'supplier_id' => $this->supplierId];
        }
        return app('json')->success($this->services->getSplitOrderList($where, ['*'], ['split', 'pink', 'invoice', 'supplier']));
    }
}
