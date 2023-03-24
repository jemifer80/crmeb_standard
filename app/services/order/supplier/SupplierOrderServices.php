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

namespace app\services\order\supplier;

use app\services\message\notice\NoticeSmsService;
use app\services\order\StoreOrderRefundServices;
use think\facade\Log;
use app\webscoket\SocketPush;
use app\services\BaseServices;
use crmeb\traits\ServicesTrait;
use app\dao\order\StoreOrderDao;
use crmeb\services\CacheService;
use think\exception\ValidateException;
use app\services\order\StoreOrderCartInfoServices;

/**
 * 供应商订单
 * Class SupplierOrderServices
 * @package app\sservices\order\supplier
 * @mixin StoreOrderDao
 */
class SupplierOrderServices extends BaseServices
{

    use ServicesTrait;

    /**
     * 支付类型
     * @var string[]
     */
    public $pay_type = ['weixin' => '微信支付', 'yue' => '余额支付', 'offline' => '线下支付', 'alipay' => '支付宝支付', 'cash' => '现金支付', 'automatic' => '自动转账', 'store' => '微信支付'];

    /**
     * SupplierOrderServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 配货单信息
     * @param array $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDistribution(array $id): array
    {
		$orderList = $this->dao->getList(['id' => $id], ['id', 'order_id', 'trade_no', 'real_name', 'user_phone', 'user_address', 'pay_time', 'pay_type', 'pay_postage as freight_price', 'coupon_price', 'deduction_price', 'use_integral', 'pay_price', 'mark','merchant_name']);
        if (!$orderList) {
            throw new ValidateException('订单不存在');
        }
		/** @var StoreOrderCartInfoServices $services */
		$cartServices = app()->make(StoreOrderCartInfoServices::class);
		foreach ($orderList as &$order) {
			$order['pay_type_name'] = $this->pay_type[$order['pay_type']] ?? '其他方式';
			$order['pay_time'] = $order['pay_time'] ? date('Y-m-d H:i:s', (int)$order['pay_time']) : '';

			$cartInfos = $cartServices->getCartColunm(['oid' => $order['id']], 'cart_num,is_writeoff,surplus_num,cart_info,refund_num,product_type,is_support_refund,is_gift,promotions_id', 'unique');

			$i = 1;
			$list = [];

			//核算优惠金额
			$vipTruePrice = 0;
			foreach ($cartInfos as $cartInfo) {
				$cart = json_decode($cartInfo['cart_info'], true);
				$vipTruePrice = bcadd((string)$vipTruePrice, bcmul($cart['vip_truePrice'], $cart['cart_num'] ?: 1, 2), 2);

				$list[] = [
					'index' => $i++,
					'store_name' => $cart['productInfo']['store_name'],
					'suk' => $cart['productInfo']['attrInfo']['suk'],
					'bar_code' => $cart['productInfo']['attrInfo']['bar_code'],
					'code' => $cart['productInfo']['attrInfo']['code'],
					'truePrice' => sprintf("%.2f", $cart['sum_price']),
					'cart_num' => $cart['cart_num'],
					'subtotal' => bcmul((string)$cart['sum_price'], (string)$cart['cart_num'], 2)
				];
			}
			$order['user_address'] = str_replace(' ', '', $order['user_address']);
			$order['vip_true_price'] = $vipTruePrice;
			$order['list'] = $list;
		}
        return $orderList;
    }

    /**
     * 供应商首页头部统计
     * @param int $supplierId
     * @param array $time
     * @return array
     */
    public function homeStatics(int $supplierId, array $time): array
    {
		$data = [];
        $where = ['time' => $time, 'supplier_id' => $supplierId];
        if ($supplierId < 1) {
            $where['supplier_id'] = -1;
        }
        $orderWhere = ['paid' => 1, 'is_system_del' => 0, 'refund_status' => 0];
        $refundWhere = ['refund_type' => 6];

        // 订单金额
        $data['pay_price'] = $this->dao->sum($where + $orderWhere, 'pay_price', true);
        // 订单量
        $data['pay_count'] = $this->dao->count($where + $orderWhere);
		/** @var  StoreOrderRefundServices $orderRefundServices */
		$orderRefundServices = app()->make(StoreOrderRefundServices::class);
        // 退款金额
        $data['refund_price'] = $orderRefundServices->sum($where + $refundWhere, 'refund_price', true);
        // 退款订单数
        $data['refund_count'] = $orderRefundServices->count($where + $refundWhere);
        return $data;
    }

    /**
     * 订单图表
     * @param int $supplierId
     * @param array $time
     * @return array
     */
    public function orderCharts(int $supplierId, array $time): array
    {
        if (is_int($time[0]) && is_int($time[1])) {
            $dayCount = (strtotime(date('Y/m/d', $time[1])) - strtotime(date('Y/m/d', $time[0]))) / 86400 + 1;
        } else {
            $time[0] = strtotime($time[0]);
            $time[1] = strtotime($time[1]);
            $dayCount = ($time[1] - $time[0]) / 86400 + 1;
        }
        if ($dayCount == 1) {
            $num = 0;
        } elseif ($dayCount > 1 && $dayCount <= 31) {
            $num = 1;
        } elseif ($dayCount > 31 && $dayCount <= 92) {
            $num = 3;
        } elseif ($dayCount > 92) {
            $num = 30;
        }

        if ($supplierId) {
            $where = ['supplier_id' => $supplierId];
        } else {
            $where = [['supplier_id', '>', 0]];
        }

        $data = $xAxis = $series = [];
        if ($num == 0) {
            $xAxis = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
            $timeType = '%H';
        } elseif ($num != 0) {
            $dt_start = $time[0];
            $dt_end = $time[1];
            while ($dt_start <= $dt_end) {
                if ($num == 30) {
                    $xAxis[] = date('Y-m', $dt_start);
                    $dt_start = strtotime("+1 month", $dt_start);
                    $timeType = '%Y-%m';
                } else {
                    $xAxis[] = date('m-d', $dt_start);
                    $dt_start = strtotime("+$num day", $dt_start);
                    $timeType = '%m-%d';
                }
            }
        }
        $pay_price = array_column($this->dao->getOrderStatistics($where, $time, $timeType, 'add_time', 'sum(pay_price)', 'pay'), 'num', 'days');
        $pay_count = array_column($this->dao->getOrderStatistics($where, $time, $timeType, 'add_time', 'count(id)', 'pay'), 'num', 'days');
        $refund_price = array_column($this->dao->getOrderStatistics($where, $time, $timeType, 'add_time', 'sum(refund_price)', 'refund'), 'num', 'days');
        $refund_count = array_column($this->dao->getOrderStatistics($where, $time, $timeType, 'add_time', 'count(id)', 'refund'), 'num', 'days');

        foreach ($xAxis as $item) {
            $data['订单金额'][] = isset($pay_price[$item]) ? floatval($pay_price[$item]) : 0;
            $data['订单量'][] = isset($pay_count[$item]) ? floatval($pay_count[$item]) : 0;
            $data['退款金额'][] = isset($refund_price[$item]) ? floatval($refund_price[$item]) : 0;
            $data['退款订单量'][] = isset($refund_count[$item]) ? floatval($refund_count[$item]) : 0;
        }
        foreach ($data as $key => $item) {
            $series[] = [
                'name' => $key,
                'data' => $item,
                'type' => 'line',
            ];
        }
        return compact('xAxis', 'series');
    }

    /**
     * 订单来源
     * @param int $supplierId
     * @param array string
     * @return array
     */
    public function getOrderChannel(int $supplierId, array $time): array
    {
        $bing_xdata = ['公众号', '小程序', 'H5', 'PC', 'APP'];
        $color = ['#6DD230', '#FFAB2B', '#4BCAD5', '#1890FF', '#B37FEB'];
        $bing_data = [];
        if ($supplierId) {
            $where = ['supplier_id' => $supplierId];
        } else {
            $where = ['supplier_id' => -1];
        }

        foreach ($bing_xdata as $key => $item) {
            $bing_data[] = [
                'name' => $item,
                'value' => $this->dao->count(['paid' => 1, 'pid' => 0, 'is_channel' => $key, 'time' => $time] + $where),
                'itemStyle' => ['color' => $color[$key]]
            ];
        }

        $list = [];
        $count = array_sum(array_column($bing_data, 'value'));
        foreach ($bing_data as $item) {
            $list[] = [
                'name' => $item['name'],
                'value' => $item['value'],
                'percent' => $count != 0 ? floatval(bcmul((string)bcdiv((string)$item['value'], (string)$count, 4), '100', 2)) : 0,
            ];
        }
        array_multisort(array_column($list, 'value'), SORT_DESC, $list);
        return compact('bing_xdata', 'bing_data', 'list');
    }

    /**
     * 订单类型
     * @param int $supplierId
     * @param array string
     * @return array
     */
    public function getOrderType(int $supplierId, array $time): array
    {
        $bing_xdata = [0 => '普通订单', 1 => '秒杀订单', 2 => '砍价订单', 3 => '拼团订单', 4 => '', 5 => '套餐订单', 6 => '预售订单'];
        $color = ['#64a1f4', '#3edeb5', '#70869f', '#ffc653', '', '#fc7d6a', '#fc7d2a'];
        $bing_data = [];

        if ($supplierId) {
            $where = ['supplier_id' => $supplierId];
        } else {
            $where = ['supplier_id' => -1];
        }
        foreach ($bing_xdata as $key => $item) {
            if (empty($item)) continue;
            $bing_data[] = [
                'name' => $item,
                'value' => $this->dao->together(['paid' => 1, 'pid' => 0, 'type' => $key, 'time' => $time] + $where, 'pay_price', 'sum'),
                'itemStyle' => ['color' => $color[$key]]
            ];
        }

        $list = [];
        $count = array_sum(array_column($bing_data, 'value'));
        foreach ($bing_data as $item) {
            $list[] = [
                'name' => $item['name'],
                'value' => $item['value'],
                'percent' => $count != 0 ? floatval(bcmul((string)bcdiv((string)$item['value'], (string)$count, 4), '100', 2)) : 0,
            ];
        }
        unset($bing_xdata[4]);
        $bing_xdata = array_values($bing_xdata);
        array_multisort(array_column($list, 'value'), SORT_DESC, $list);
        return compact('bing_xdata', 'bing_data', 'list');
    }

    /**
     * 提醒发货
     * @param int $supplierId
     * @param int $id
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deliverRemind(int $supplierId, int $id)
    {
        $expire = 600;
        $val = time() + $expire;

        $order = $this->dao->get($id);
        if (!$order) {
            throw new ValidateException('订单不存在');
        }
		$order = $order->toArray();

        $cacheName = 'order_deliver_remind_' . $id;
        if (CacheService::has($cacheName)) {
            $interval = CacheService::get($cacheName);
            $remain = $interval - time();
            if ($remain > 0) {
                throw new ValidateException('请' . ceil($remain / 60) . '分钟后再次提醒');
            }
            CacheService::delete($cacheName);
        } else {
            CacheService::set($cacheName, $val, $expire);
        }

        //向供应商后台发送待发货订单消息
        try {
			/** @var  NoticeSmsService $NoticeSms */
            $NoticeSms = app()->make(NoticeSmsService::class);
			$mark = 'admin_pay_success_code';
			$NoticeSms->setEvent($mark)->sendAdminPaySuccess($order);

            SocketPush::instance()->setUserType('supplier')->to($supplierId)->type('WAIT_DELIVER_ORDER')->data(['order_id' => $order['order_id']])->push();

        } catch (\Throwable $e) {
            Log::error('向供应商发送提醒发货消息失败,失败原因:' . $e->getMessage());
        }
        return true;
    }
}