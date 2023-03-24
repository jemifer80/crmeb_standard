<?php

namespace app\listener\out;

use app\jobs\out\OutPushJob;
use app\services\out\OutAccountServices;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\CacheService;
use crmeb\services\HttpService;
use think\facade\Log;

class OutPush implements ListenerInterface
{
    public function handle($event): void
    {
        OutPushJob::dispatchDo('push', $event);
    }
}
