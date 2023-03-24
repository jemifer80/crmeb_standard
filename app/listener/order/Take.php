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


use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderStatusServices;
use app\services\user\UserBrokerageServices;
use crmeb\interfaces\ListenerInterface;


/**
 * 订单收货事件
 * Class Take
 * @package app\listener\order
 */
class Take implements ListenerInterface
{
    public function handle($event): void
    {
        [$orderInfo, $storeTitle] = $event;


        try {
            //修改收货状态
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            $userBrokerageServices->takeUpdate((int)$orderInfo['uid'], (int)$orderInfo['id']);
            //增加收货订单状态
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $statusService->save([
                'oid' => $orderInfo['id'],
                'change_type' => 'take_delivery',
                'change_message' => '已收货',
                'change_time' => time()
            ]);
            //收货给用户发送消息
            event('notice.notice', [['order' => $orderInfo, 'storeTitle' => $storeTitle], 'order_takever']);
            //收货给客服发送消息
            event('notice.notice', [['order' => $orderInfo, 'storeTitle' => $storeTitle], 'send_admin_confirm_take_over']);
            //发送短信
//            $storeOrderTake->smsSend($orderInfo, $storeTitle);
//            $storeOrderTake->smsSendTake($orderInfo);

			//检测主订单 是否全部收货
			if ($orderInfo['pid']) {
				$id = (int)$orderInfo['pid'];
				/** @var StoreOrderServices $orderServices */
				$orderServices = app()->make(StoreOrderServices::class);
				//默认部分收货
				$take_data = ['status' => '5'];
				$status_data = ['oid' => $id, 'change_time' => time()];
				if ($orderServices->count(['pid' => $id]) == $orderServices->count(['pid' => $id, 'status' => 3])) {
					$take_data = ['status' => 2];
					$status_data['change_type'] = 'take_split';
					$status_data['change_message'] = '已拆分收货';
				} else {
					$status_data['change_type'] = 'take_part_split';
					$status_data['change_message'] = '已拆分部分收货';
				}
				//改变主订单状态
				$orderServices->update($id, $take_data);
				//记录主订单状态
				$statusService->save($status_data);
			}
        } catch (\Throwable $e) {
        }
    }


}