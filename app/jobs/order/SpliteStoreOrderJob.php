<?php


namespace app\jobs\order;


use app\services\message\NoticeService;
use app\webscoket\SocketPush;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

/**
 * 门店分配订单
 * Class SpliteStoreOrderJob
 * @package app\jobs
 */
class SpliteStoreOrderJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 订单分配完成后置方法
     * @param $orderInfo
     */
    public function splitAfter($orderInfo, bool $only_print = false)
    {
        //分配好向用户设置标签
        OrderJob::dispatchDo('setUserLabel', [$orderInfo]);
        if ($only_print) {
            /** @var NoticeService $NoticeService */
            $NoticeService = app()->make(NoticeService::class);
            $NoticeService->orderPrint($orderInfo);
        } else {
            //分配完成用户推送消息事件（门店小票打印）
            event('notice.notice', [$orderInfo, 'order_pay_success']);
        }
        return true;
    }

}
