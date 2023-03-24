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
namespace app\listener\system\timer;

use app\services\activity\combination\StorePinkServices;
use app\services\activity\live\LiveGoodsServices;
use app\services\activity\live\LiveRoomServices;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\agent\AgentManageServices;
use app\services\message\sms\SmsRecordServices;
use app\services\order\StoreOrderCommentServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderTakeServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\timer\SystemTimerServices;
use app\services\user\UserIntegralServices;
use app\services\user\UserServices;
use app\services\work\WorkChannelCodeServices;
use app\services\work\WorkGroupTemplateServices;
use app\services\work\WorkMomentServices;
use crmeb\services\GroupDataService;
use crmeb\utils\Cron;
use crmeb\interfaces\ListenerInterface;

/**
 * 定时任务
 * Class Create
 * @package app\listener\system\timer
 */
class SystemTimer extends Cron implements ListenerInterface
{
    /**
     * @param $event
     */
    public function handle($event): void
    {
        $this->tick(1000, function () {
            $time = time();
            /** @var SystemTimerServices $timerServices */
            $timerServices = app()->make(SystemTimerServices::class);
            $cacheCount = $timerServices->cacheCount();
            if (!$cacheCount) {
                $timerServices->setAllTimerCache();
            }
            $list = $timerServices->cacheList();
            foreach ($list as $key => $item) {
                if ($item['is_open'] == 1) {
                    $data = $timerServices->getTimerCycleTime($item['type'], $item['cycle'], $time, $item['update_execution_time']);
                    if ($time == $data['cycle_time']) {
                        $this->after(1000, function () use ($timerServices, $item, $time) {
                            $timerServices->cacheTag()->set($item['mark'], $time);//上次执行时间保存
                            $this->implement_timer($item);
                        });
                    }
                }
            }
        });
    }

    /**执行定时任务
     * @param $timerServices
     * @param $item
     * @param $time
     * @return void
     */
    public function implement_timer($item)
    {
        switch ($item['mark']) {
            case 'auto_cancel': //自动取消订单
                try {
                    /** @var StoreOrderServices $orderServices */
                    $orderServices = app()->make(StoreOrderServices::class);
                    return $orderServices->orderUnpaidCancel();
                } catch (\Throwable $e) {

                    response_log_write([
                        'message' => '自动取消订单,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_take' : //自动确认收货
                try {
                    /** @var StoreOrderTakeServices $services */
                    $services = app()->make(StoreOrderTakeServices::class);
                    return $services->autoTakeOrder();
                } catch (\Throwable $e) {

                    response_log_write([
                        'message' => '自动收货,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_comment' : //自动好评
                try {
                    /** @var StoreOrderCommentServices $services */
                    $services = app()->make(StoreOrderCommentServices::class);
                    return $services->autoCommentOrder();
                } catch (\Throwable $e) {

                    response_log_write([
                        'message' => '自动默认好评,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_clear_integral' : // 自动清空用户积分
                try {
                    /** @var UserIntegralServices $userIntegralServices */
                    $userIntegralServices = app()->make(UserIntegralServices::class);
                    [$clear_time, $start_time, $end_time] = $userIntegralServices->getTime();
                    //到清空积分的最后一天
                    if ($clear_time == strtotime(date('Y-m-d', time()))) {
                        return $userIntegralServices->clearExpireIntegral();
                    }
                    return true;
                } catch (\Throwable $e) {

                    response_log_write([
                        'message' => '清空积分,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_off_user_svip' : //自动取消用户到期svip
                try {
                    /** @var UserServices $userServices */
                    $userServices = app()->make(UserServices::class);
                    $userServices->offUserSvip();
                    return true;
                } catch (\Throwable $e) {

                    response_log_write([
                        'message' => '清空用户svip,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_agent' : // 自动解绑上下级
                try {
                    /** @var AgentManageServices $agentManage */
                    $agentManage = app()->make(AgentManageServices::class);
                    $agentManage->removeSpread();
                    return true;
                } catch (\Throwable $e) {

                    response_log_write([
                        'message' => '自动解除上级绑定失败,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);

                }
                break;
            case 'auto_clear_poster' : // 自动清除昨日海报
                try {
                    /** @var SystemAttachmentServices $attach */
                    $attach = app()->make(SystemAttachmentServices::class);
                    return $attach->emptyYesterdayAttachment();
                } catch (\Throwable $e) {
                    response_log_write([
                        'message' => '清除昨日海报,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_sms_code' : // 更新短信状态
                try {
                    //修改短信发送记录短信状态
                    /** @var SmsRecordServices $smsRecord */
                    $smsRecord = app()->make(SmsRecordServices::class);
                    return $smsRecord->modifyResultCode();
                } catch (\Throwable $e) {
                    response_log_write([
                        'message' => '自动更新短信状态:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_live' : // 自动更新直播产品状态和直播间状态
                try {
                    /** @var LiveGoodsServices $liveGoods */
                    $liveGoods = app()->make(LiveGoodsServices::class);
                    $liveGoods->syncGoodStatus();
                } catch (\Throwable $e) {
                    response_log_write([
                        'message' => '更新直播商品状态失败,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                //更新直播间状态
                try {
                    /** @var LiveRoomServices $liveRoom */
                    $liveRoom = app()->make(LiveRoomServices::class);
                    $liveRoom->syncRoomStatus();
                } catch (\Throwable $e) {
                    response_log_write([
                        'message' => '更新直播间状态失败,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                return true;
                break;
            case 'auto_pink' : // 拼团状态自动更新
                try {
                    /** @var StorePinkServices $storePinkServices */
                    $storePinkServices = app()->make(StorePinkServices::class);
                    $storePinkServices->useStatusPink();
                    return true;
                } catch (\Throwable $e) {
                    response_log_write([
                        'message' => '拼团失败处理,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_show' :  // 自动上下架商品
                try {
                    /** @var StoreProductServices $storeProductServices */
                    $storeProductServices = app()->make(StoreProductServices::class);
                    return $storeProductServices->autoUpperShelves();
                } catch (\Throwable $e) {
                    response_log_write([
                        'message' => '自动上下架,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_channel' : // 渠道码定时任务
                /** @var WorkChannelCodeServices $service */
                $service = app()->make(WorkChannelCodeServices::class);

                try {
                    $service->cronHandle();
                } catch (\Throwable $e) {
                    response_log_write([
                        'message' => '渠道码定时任务执行错误：' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_moment' : // 定时创建发送朋友圈任务
                /** @var WorkMomentServices $make */
                $make = app()->make(WorkMomentServices::class);

                try {
                    $make->cronHandle();
                } catch (\Throwable $e) {

                    response_log_write([
                        'message' => '执行发送朋友圈发生错误：' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
            case 'auto_group_task' : // 定时发送群发任务
                /** @var WorkGroupTemplateServices $service */
                $service = app()->make(WorkGroupTemplateServices::class);
                try {
                    $service->cornHandle();
                } catch (\Throwable $e) {
                    response_log_write([
                        'message' => '执行定时发送群发任务失败:' . $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                break;
        }
    }
}
