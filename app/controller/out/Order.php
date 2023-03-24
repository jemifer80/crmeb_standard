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

namespace app\controller\out;

use app\Request;
use app\services\order\StoreCartServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderCreateServices;
use app\services\order\StoreOrderDeliveryServices;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderSuccessServices;
use app\services\order\StoreOrderTakeServices;
use app\services\other\ExpressServices;
use app\services\user\UserAddressServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserServices;
use app\services\order\StoreOrderServices;

/**
 * Class Order
 * @package app\kefuapi\controller
 */
class Order
{
    /**
     * 订单services
     * @var StoreOrderServices
     */
    protected $orderServices;

    /**
     * 退款订单services
     * @var StoreOrderRefundServices
     */
    protected $refundServices;

    /**
     * @param StoreOrderServices $orderServices
     * @param StoreOrderRefundServices $refundServices
     */
    public function __construct(StoreOrderServices $orderServices, StoreOrderRefundServices $refundServices)
    {
        $this->orderServices = $orderServices;
        $this->refundServices = $refundServices;
    }

    /**
     * 订单列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderList(Request $request)
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
            ['supplier_id', '']
        ]);

        if ($where['supplier_id'] < 1) {
            $where['supplier_id'] = -1;
        }
        $where['type'] = trim($where['type']);
        $where['is_system_del'] = 0;

        $where['store_id'] = 0;
        $where['type'] = trim($where['type'], ' ');
        return app('json')->success($this->orderServices->getOrderList($where, ['*'], ['split' => function ($query) {
            $query->field('id,pid');
        }, 'pink', 'invoice', 'supplier']));
    }

    /**
     * 订单详情
     * @param $order_id
     * @return mixed
     */
    public function orderInfo($order_id)
    {
        if (!$order_id) return app('json')->fail('订单不存在');
        if (!$orderInfo = $this->orderServices->get(['order_id' => $order_id])) return app('json')->fail('订单不存在');
        $orderInfo = $this->orderServices->tidyOrder($orderInfo->toArray(), true, true);
        //核算优惠金额
        $vipTruePrice = 0;
        foreach ($orderInfo['cartInfo'] ?? [] as $cart) {
            $vipTruePrice = bcadd((string)$vipTruePrice, (string)$cart['vip_sum_truePrice'], 2);
        }
        $orderInfo['vip_true_price'] = $vipTruePrice;
        $orderInfo['total_price'] = bcadd((string)$orderInfo['total_price'], (string)$orderInfo['vip_true_price'], 2);
        return app('json')->success(compact('orderInfo'));
    }

    /**
     * 订单备注
     * @param Request $request
     * @param $order_id
     * @return mixed
     */
    public function orderRemark(Request $request, $order_id)
    {
        $data = $request->postMore([['remark', '']]);
        if (!$data['remark'])
            return app('json')->fail('请输入要备注的内容');
        if (!$order_id)
            return app('json')->fail('缺少参数');

        if (!$order = $this->orderServices->get(['order_id' => $order_id])) {
            return app('json')->fail('修改的订单不存在!');
        }
        $order->remark = $data['remark'];
        if ($order->save()) {
            return app('json')->success('备注成功');
        } else
            return app('json')->fail('备注失败');
    }

    /**
     * 订单收货
     * @param $order_id
     * @return mixed
     */
    public function orderReceive($order_id)
    {
        /** @var StoreOrderTakeServices $takeOrderServices */
        $takeOrderServices = app()->make(StoreOrderTakeServices::class);
        $uid = $this->orderServices->value(['order_id' => $order_id], 'uid');
        $takeOrderServices->takeOrder($order_id, $uid);
        return app('json')->success('收货成功');
    }

    /**
     * 查询物流公司
     * @param ExpressServices $services
     * @return mixed
     */
    public function orderExpressList(ExpressServices $services)
    {
        $data['is_show'] = 1;

        return app('json')->success($services->express($data));
    }

    /**
     * 订单发货
     * @param Request $request
     * @param $order_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderDelivery(Request $request, $order_id)
    {
        if (!$order_id) return app('json')->fail('参数错误');
        $data = $request->postMore([
            ['delivery_name', ''],//快递公司名称
            ['delivery_id', ''],//快递单号
            ['delivery_code', ''],//快递公司编码
        ]);
        $data['express_record_type'] = 1;
        $data['type'] = 1;
        /** @var StoreOrderDeliveryServices $deliveryServices */
        $deliveryServices = app()->make(StoreOrderDeliveryServices::class);
        $id = $this->orderServices->value(['order_id' => $order_id], 'id');
        $deliveryServices->delivery($id, $data);
        return app('json')->success('发货成功');
    }

    /**
     * 修改配送信息
     * @param Request $request
     * @param $order_id
     * @return mixed
     */
    public function updateDistribution(Request $request, $order_id)
    {
        if (!$order_id) return app('json')->fail(100100);
        $data = $request->postMore([
            ['delivery_name', ''],
            ['delivery_code', ''],
            ['delivery_id', '']
        ]);
        /** @var StoreOrderDeliveryServices $deliveryServices */
        $deliveryServices = app()->make(StoreOrderDeliveryServices::class);
        $id = $this->orderServices->value(['order_id' => $order_id], 'id');
        $deliveryServices->updateDistribution($id, $data);
        return app('json')->success('修改成功');
    }

    /**
     * 获取订单可拆分商品列表
     * @param $order_id
     * @return mixed
     */
    public function SplitCartInfo($order_id)
    {
        if (!$order_id) {
            return app('json')->fail('缺少发货ID');
        }
        $id = $this->orderServices->value(['order_id' => $order_id], 'id');
        /** @var StoreOrderCartInfoServices $orderCartServices */
        $orderCartServices = app()->make(StoreOrderCartInfoServices::class);
        return app('json')->success($orderCartServices->getSplitCartList((int)$id));
    }

    /**
     * 拆单发货
     * @param Request $request
     * @param $order_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderSplitDelivery(Request $request, $order_id)
    {
        if (!$order_id) return app('json')->fail('参数错误');
        $data = $request->postMore([
            ['delivery_name', ''],//快递公司名称
            ['delivery_id', ''],//快递单号
            ['delivery_code', ''],//快递公司编码
            ['fictitious_content', ''],//虚拟发货内容
            ['cart_ids', []]
        ]);

        if (!$data['cart_ids']) {
            return app('json')->fail('参数错误');
        }
        foreach ($data['cart_ids'] as &$cart) {
            if (!isset($cart['cart_id']) || !$cart['cart_id'] || !isset($cart['cart_num']) || !$cart['cart_num']) {
                return app('json')->fail('数据不存在');
            }
            $cart['cart_id'] = (int)$cart['cart_id'];
            $cart['cart_num'] = (int)$cart['cart_num'];
        }
        $data['express_record_type'] = 1;
        $data['type'] = 1;
        /** @var StoreOrderDeliveryServices $deliveryServices */
        $deliveryServices = app()->make(StoreOrderDeliveryServices::class);
        $id = $this->orderServices->value(['order_id' => $order_id], 'id');
        $deliveryServices->splitDelivery($id, $data);
        return app('json')->success('发货成功');
    }

    /**
     * 修改订单开票信息
     * @param Request $request
     * @param $order_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setInvoice(Request $request, $order_id)
    {
        if (!$order_id) return app('json')->fail(100100);
        $data = $request->postMore([
            [['header_type', 'd'], 1],
            [['type', 'd'], 1],
            ['drawer_phone', ''],
            ['email', ''],
            ['name', ''],
            ['duty_number', ''],
            ['tell', ''],
            ['address', ''],
            ['bank', ''],
            ['card_number', ''],
        ]);

        if (!$data['drawer_phone']) return app('json')->fail('请填写开票手机号');
        if (!check_phone($data['drawer_phone'])) return app('json')->fail('手机号格式不正确');
        if (!$data['name']) return app('json')->fail('请填写发票抬头（开具发票企业名称）');
        if (!in_array($data['header_type'], [1, 2])) {
            $data['header_type'] = empty($data['duty_number']) ? 1 : 2;
        }
        if ($data['header_type'] == 1 && !preg_match('/^[\x80-\xff]{2,60}$/', $data['name'])) {
            return app('json')->fail('请填写发票抬头（开具发票企业名称）');
        }
        if ($data['header_type'] == 2 && !preg_match('/^[0-9a-zA-Z&\(\)\（\）\x80-\xff]{2,150}$/', $data['name'])) {
            return app('json')->fail('请填写发票抬头（开具发票企业名称）');
        }
        if ($data['header_type'] == 2 && !$data['duty_number']) {
            return app('json')->fail('请填写发票税号');
        }
        if ($data['header_type'] == 2 && !preg_match('/^[A-Z0-9]{15}$|^[A-Z0-9]{17}$|^[A-Z0-9]{18}$|^[A-Z0-9]{20}$/', $data['duty_number'])) {
            return app('json')->fail('请填写正确的发票税号');
        }
        if ($data['card_number'] && !preg_match('/^[1-9]\d{11,19}$/', $data['card_number'])) {
            return app('json')->fail('请填写正确的银行卡号');
        }
        $orderInfo = $this->orderServices->get(['order_id' => $order_id], ['id'], ['invoice']);
        if (!$orderInfo) return app('json')->fail('订单不存在');
        if (!$orderInfo->invoice || !$invoiceId = $orderInfo->invoice->id) {
            return app('json')->fail('订单未提交开票申请');
        }
        /** @var StoreOrderInvoiceServices $invoiceServices */
        $invoiceServices = app()->make(StoreOrderInvoiceServices::class);
        if ($invoiceServices->setInvoice($invoiceId, $data)) {
            return app('json')->success('修改成功');
        } else {
            return app('json')->fail('修改失败');
        }
    }

    public function setInvoiceStatus(Request $request, $order_id)
    {
        if (!$order_id) return app('json')->fail(100100);
        $data = $request->postMore([
            ['is_invoice', 0],
            ['invoice_number', 0],
            ['remark', '']
        ]);

        if ($data['is_invoice'] == 1 && !$data['invoice_number']) {
            return app('json')->fail('请填写开票号');
        }
        if ($data['invoice_number'] && !preg_match('/^\d{8,10}$/', $data['invoice_number'])) {
            return app('json')->fail('请填写正确的开票号');
        }
        $orderInfo = $this->orderServices->get(['order_id' => $order_id], ['id'], ['invoice']);
        if (!$orderInfo) return app('json')->fail('订单不存在');
        if (!$orderInfo->invoice || !$invoiceId = $orderInfo->invoice->id) {
            return app('json')->fail('订单未提交开票申请');
        }
        /** @var StoreOrderInvoiceServices $invoiceServices */
        $invoiceServices = app()->make(StoreOrderInvoiceServices::class);
        $invoiceServices->setInvoice($invoiceId, $data);
        return app('json')->success('修改成功');
    }

    /**
     * 售后单列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundList(Request $request)
    {
        $where = $request->getMore([
            ['order_id', ''],
            ['time', ''],
            ['refund_type', 0]
        ]);
        $data = $this->refundServices->refundList($where);
        unset($data['num']);
        return app('json')->success($data);
    }

    /**
     * 退款单备注
     * @param Request $request
     * @param $order_id
     * @return mixed
     */
    public function refundRemark(Request $request, $order_id)
    {
        $data = $request->postMore([['remark', '']]);
        if (!$data['remark']) return app('json')->fail('请输入要备注的内容');
        if (!$order_id) return app('json')->fail('缺少参数');
        if (!$order = $this->refundServices->get(['order_id' => $order_id])) {
            return app('json')->fail('修改的订单不存在!');
        }
        $order->remark = $data['remark'];
        if ($order->save()) {
            return app('json')->success('备注成功');
        } else
            return app('json')->fail('备注失败');
    }

    /**
     * 退款单退款
     * @param Request $request
     * @param $order_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundPrice(Request $request, $order_id)
    {
        $data = $request->postMore([
            ['refund_price', 0],
            ['type', 1],
            ['refuse_reason', '']
        ]);
        if (!$order_id) {
            return app('json')->fail('数据不存在');
        }
        $orderRefund = $this->refundServices->get(['order_id' => $order_id]);
        if (!$orderRefund) {
            return app('json')->fail('数据不存在');
        }
        if ($orderRefund['is_cancel'] == 1) {
            return app('json')->fail('用户已取消申请');
        }
        $order = $this->orderServices->get((int)$orderRefund['store_order_id']);
        if (!$order) {
            return app('json')->fail('数据不存在');
        }
        if (!in_array($orderRefund['refund_type'], [1, 2, 5])) {
            return app('json')->fail('售后订单状态不支持该操作');
        }
        if ($data['type'] == 1) {
            $data['refund_type'] = 6;
        } else if ($data['type'] == 2) {
            $data['refund_type'] = 3;
        }
        $data['refunded_time'] = time();
        $type = $data['type'];
        //拒绝退款
        if ($type == 2) {
            $this->refundServices->refuseRefund((int)$orderRefund['id'], $data, $orderRefund);
            return app('json')->successful('修改退款状态成功!');
        } else {
            //0元退款
            if ($orderRefund['refund_price'] == 0) {
                $refund_price = 0;
            } else {
                if (!$data['refund_price']) {
                    return app('json')->fail('请输入退款金额');
                }
                if ($orderRefund['refund_price'] == $orderRefund['refunded_price']) {
                    return app('json')->fail('已退完支付金额!不能再退款了');
                }
                $refund_price = $data['refund_price'];
                $data['refunded_price'] = bcadd($data['refund_price'], $orderRefund['refunded_price'], 2);
                $bj = bccomp((string)$orderRefund['refund_price'], (string)$data['refunded_price'], 2);
                if ($bj < 0) {
                    return app('json')->fail('退款金额大于支付金额，请修改退款金额');
                }
            }

            unset($data['type']);
            $refund_data['pay_price'] = $order['pay_price'];
            $refund_data['refund_price'] = $refund_price;
            if ($order['refund_price'] > 0) {
                mt_srand();
                $refund_data['refund_id'] = $order['order_id'] . rand(100, 999);
            }
            //修改订单退款状态
            unset($data['refund_price']);
            if ($this->refundServices->agreeRefund($orderRefund['id'], $refund_data)) {
                $this->refundServices->update($orderRefund['id'], $data);
                return app('json')->success('退款成功');
            } else {
                $this->refundServices->storeProductOrderRefundYFasle((int)$orderRefund['id'], $refund_price);
                return app('json')->fail('退款失败');
            }
        }
    }

    /**
     * 同意退货
     * @param $order_id
     * @return mixed
     */
    public function agreeRefund($order_id)
    {
        $this->refundServices->agreeRefundProdcut((int)$order_id);
        return app('json')->success('操作成功');
    }

    /**
     * 拒绝退款
     * @param Request $request
     * @param $order_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refuseRefund(Request $request, $order_id)
    {
        if (!$order_id) return app('json')->fail('订单不存在');
        if (!$orderRefundInfo = $this->refundServices->get(['order_id' => $order_id])) return app('json')->fail('订单不存在');
        [$refund_reason] = $request->postMore([['refund_reason', '']], true);
        if (!$refund_reason) {
            return app('json')->fail('请输入不退款原因');
        }
        $refundData = [
            'refuse_reason' => $refund_reason,
            'refund_type' => 3,
            'refunded_time' => time()
        ];
        //拒绝退款处理
        $this->refundServices->refuseRefund((int)$orderRefundInfo['id'], $refundData, $orderRefundInfo);

        return app('json')->success('Modified success');
    }

    /**
     * 退款单详情
     * @param $order_id
     * @return mixed
     */
    public function refundInfo($order_id)
    {
        if (!$order_id) return app('json')->fail('缺少参数');
        $orderInfo = $this->refundServices->refundDetail($order_id);
		unset($orderInfo['cartInfo']);
        return app('json')->success(compact('orderInfo'));
    }
}
