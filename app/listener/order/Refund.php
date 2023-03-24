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
namespace app\listener\order;

use app\jobs\system\CapitalFlowJob;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderStatusServices;
use app\services\user\UserServices;
use crmeb\interfaces\ListenerInterface;

/**
 * 订单退款事件
 * Class PriceRevision
 * @package app\listener\order
 */
class Refund implements ListenerInterface
{
    /**
     * @param $event
     */
    public function handle($event): void
    {
        [$data, $order] = $event;

        //修改开票数据退款状态
        $orderInvoiceServices = app()->make(StoreOrderInvoiceServices::class);
        $orderInvoiceServices->update(['order_id' => $order['id']], ['is_refund' => 1]);


        //退款写入资金流水
        if ($order['pay_type'] == 'wechat' || $order['pay_type'] == 'alipay') {
            $userInfo = app()->make(UserServices::class)->get($order['uid']);
            //记录资金流水队列
            CapitalFlowJob::dispatch([[
                'order_id' => $order['order_id'],
                'store_id' => $order['store_id'],
                'uid' => $order['uid'],
                'nickname' => $userInfo['nickname'],
                'phone' => $userInfo['phone'],
                'price' => $data['refund_price'],
                'pay_type' => $order['pay_type'],
                'add_time' => time(),
            ], 'refund']);
        }

        //订单退款消息推送
        event('notice.notice', [['data' => $data, 'order' => $order], 'order_refund']);

		//检测主订单 是否全部退款
		if ($order['pid']) {
			$id = (int)$order['pid'];
			/** @var StoreOrderServices $orderServices */
			$orderServices = app()->make(StoreOrderServices::class);
			//默认部分退款
            $refund_data = ['refund_status' => '3', 'refund_type' => 4];
			$status_data = ['oid' => $id, 'change_time' => time()];
			if ($orderServices->count(['pid' => $id]) == $orderServices->count(['pid' => $id, 'refund_status' => 2])) {
				$refund_data = ['refund_status' => 2, 'refund_type' => 6];
				$status_data['change_type'] = 'refund_split';
                $status_data['change_message'] = '已拆分退款';
			} else {
				$status_data['change_type'] = 'refund_part_split';
                $status_data['change_message'] = '已拆分部分退款';
			}
			//改变主订单状态
            $orderServices->update($id, $refund_data);
            /** @var StoreOrderStatusServices $services */
            $services = app()->make(StoreOrderStatusServices::class);
            //记录主订单状态
            $services->save($status_data);
		}
		//对外接口推送订单
        event('out.outPush', ['refund_create_push', ['order_id' => (int)$order['id']]]);
    }
}
