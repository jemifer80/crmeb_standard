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

use app\services\order\StoreOrderCommentServices;
use crmeb\utils\Cron;
use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 订单自动默认好评
 * Class AutoComment
 * @package app\listener\order
 */
class AutoComment extends Cron  implements ListenerInterface
{
    /**
     * @param $event
     */
    public function handle($event): void
    {
        //订单自动默认好评
        $this->tick(1000 * 60 * 30, function (){
            //自动默认好评
            try {
                /** @var StoreOrderCommentServices $services */
                $services = app()->make(StoreOrderCommentServices::class);
                return $services->autoCommentOrder();
            } catch (\Throwable $e) {
                Log::error('自动默认好评,失败原因:[' . class_basename($this) . ']' . $e->getMessage());
            }
        });

    }
}
