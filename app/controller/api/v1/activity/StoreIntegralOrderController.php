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
namespace app\controller\api\v1\activity;


use app\Request;
use app\services\activity\integral\StoreIntegralOrderServices;
use app\services\activity\integral\StoreIntegralServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\other\ExpressServices;
use crmeb\services\CacheService;
use app\services\pay\PayServices;
use app\services\pay\OrderPayServices;
use app\services\pay\YuePayServices;

/**
 * 积分订单
 * Class StoreIntegralController
 * @package app\api\controller\activity
 */
class StoreIntegralOrderController
{

    protected $services;

    public function __construct(StoreIntegralOrderServices $services)
    {
        $this->services = $services;
    }

    /**
     * 订单确认
     * @param Request $request
     * @return mixed
     */
    public function confirm(Request $request)
    {
        [$unique, $num] = $request->postMore([
            'unique',
            'num'
        ], true);
        if (!$unique) {
            return app('json')->fail('请提交购买的商品');
        }
        $user = $request->user()->toArray();
        return app('json')->successful($this->services->getOrderConfirmData($user, $unique, $num));
    }

    /**
     * 订单创建
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create(Request $request, StoreProductAttrValueServices $storeProductAttrValueServices, StoreIntegralServices $storeIntegralServices)
    {
        $uid = (int)$request->uid();
        [$addressId, $mark, $payType, $from, $quitUrl, $unique, $num, $customForm] = $request->postMore([
            [['addressId', 'd'], 0],
            ['mark', ''],
            ['payType', ''],
            ['from', 'weixin'],
            ['quitUrl', ''],
            ['unique', ''],
            [['num', 'd'], 0],
            ['custom_form', []],
        ], true);

        $attrValue = $storeProductAttrValueServices->uniqueByField($unique, 4);
        if (!$attrValue) {
            return app('json')->fail('商品不存在，请重新选择商品下单!');
        }
        $productInfo = $storeIntegralServices->getIntegralOne((int)$attrValue['product_id']);
        if (!$productInfo) {
            return app('json')->fail('商品不存在，请重新选择商品下单!');
        }
        $attrValue = is_object($attrValue) ? $attrValue->toArray() : $attrValue;
        $productInfo = is_object($productInfo) ? $productInfo->toArray() : $productInfo;
        $productInfo['attrInfo'] = $attrValue;

        $num = (int)$num;
        //判断积分商品限量
        $unique = $storeIntegralServices->checkoutProductStock($uid, $productInfo['id'], $num, $unique);
        try {
            //弹出队列
            if (!CacheService::popStock($unique, $num, 4)) {
                return app('json')->fail('该商品库存不足');
            }
            $order = $this->services->createOrder($uid, $addressId, $payType, $mark, $request->user()->toArray(), $num, $productInfo, $customForm);
        } catch (\Throwable $e) {
            //生成失败归还库存
            CacheService::setStock($unique, $num, 4, false);
            return app('json')->fail($e->getMessage());
        }
        $orderId = $order['order_id'];
        if ($orderId && !$order['paid']) {
            $orderInfo = $order->toArray();
            if (!$orderInfo || !isset($orderInfo['paid'])) {
                return app('json')->fail('支付订单不存在!');
            }
            if ($orderInfo['paid']) return app('json')->fail('支付已支付!');
            $info = compact('orderId');
            switch ($payType) {
                case PayServices::WEIXIN_PAY:
                    /** @var OrderPayServices $payServices */
                    $payServices = app()->make(OrderPayServices::class);
                    if (!$from && $request->isApp()) {
                        $from = 'weixin';
                    }
                    $info['jsConfig'] = $payServices->orderIntegralPay($orderInfo, $from);
                    if ($from == 'weixinh5') {
                        return app('json')->status('wechat_h5_pay', '订单创建成功', $info);
                    } else {
                        return app('json')->status('wechat_pay', '订单创建成功', $info);
                    }
                    break;
                case PayServices::YUE_PAY:
                    /** @var YuePayServices $yueServices */
                    $yueServices = app()->make(YuePayServices::class);
                    $pay = $yueServices->yueIntegralOrderPay($orderInfo, $uid);
                    if ($pay['status'] === true)
                        return app('json')->status('success', '余额支付成功', $info);
                    else {
                        if (is_array($pay))
                            return app('json')->status($pay['status'], $pay['msg'], $info);
                        else
                            return app('json')->status('pay_error', $pay);
                    }
                    break;
                case PayServices::ALIAPY_PAY:
                    if (!$quitUrl && ($request->isH5() || $request->isWechat())) {
                        return app('json')->status('pay_error', '请传入支付宝支付回调URL', $info);
                    }
                    $quitUrl = $quitUrl . '?order_id=' . $orderInfo['order_id'];
                    /** @var OrderPayServices $payServices */
                    $payServices = app()->make(OrderPayServices::class);
                    $info['jsConfig'] = $payServices->alipayIntegralOrder($orderInfo, $quitUrl, $from == 'routine');
                    $payKey = md5($orderInfo['order_id']);
                    CacheService::set($payKey, ['order_id' => $orderInfo['order_id'], 'other_pay_type' => false], 300);
                    $info['pay_key'] = $payKey;
                    return app('json')->status(PayServices::ALIAPY_PAY . '_pay', '订单创建成功', $info);
                    break;
                default:
                    return app('json')->status('success', '订单创建成功', $info);
                    break;
            }
        } elseif ($orderId && $order['paid']) {
            return app('json')->status('success', '订单创建成功', ['orderId' => $order['order_id']]);
        } else return app('json')->fail('订单生成失败!');
    }

    /**
     * 订单详情
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function detail(Request $request, $uni)
    {
        if (!strlen(trim($uni))) return app('json')->fail('参数错误');
        $order = $this->services->getOne(['order_id' => $uni, 'is_del' => 0]);
        if (!$order) return app('json')->fail('订单不存在');
        $order = $order->toArray();
        if (!$order['paid']) return app('json')->fail('订单未支付，无法查看');
        $orderData = $this->services->tidyOrder($order);
        return app('json')->successful('ok', $orderData);
    }

    /**
     * 订单列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $where['uid'] = $request->uid();
        $where['paid'] = 1;
        $where['is_del'] = 0;
        $where['is_system_del'] = 0;
        $list = $this->services->getOrderApiList($where);
        return app('json')->successful($list);
    }

    /**
     * 订单收货
     * @param Request $request
     * @return mixed
     */
    public function take(Request $request)
    {
        list($order_id) = $request->postMore([
            ['order_id', ''],
        ], true);
        if (!$order_id) return app('json')->fail('参数错误!');
        $order = $this->services->takeOrder($order_id, (int)$request->uid());
        if ($order) {
            return app('json')->successful('收货成功');
        } else
            return app('json')->fail('收货失败');
    }

    /**
     * 订单 查看物流
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function express(Request $request, ExpressServices $expressServices, $uni)
    {
        if (!$uni || !($order = $this->services->getUserOrderDetail($uni, $request->uid()))) return app('json')->fail('查询订单不存在!');
        if ($order['delivery_type'] != 'express') return app('json')->fail('该订单不是快递发货，无法查询物流信息');
        if (!$order['delivery_id']) return app('json')->fail('该订单不存在快递单号!');
        $cacheName = 'integral' . $order['order_id'] . $order['delivery_id'];
        return app('json')->successful([
            'order' => $order,
            'express' => [
                'result' => ['list' => $expressServices->query($cacheName, $order['delivery_id'], $order['delivery_code'])
                ]
            ]
        ]);
    }

    /**
     * 订单删除
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        [$order_id] = $request->postMore([
            ['order_id', ''],
        ], true);
        if (!$order_id) return app('json')->fail('参数错误!');
        $res = $this->services->removeOrder($order_id, (int)$request->uid());
        if ($res) {
            return app('json')->successful();
        } else {
            return app('json')->fail('删除失败');
        }
    }
}
