<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace app\listener\activity;


use app\services\activity\seckill\StoreSeckillServices;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\GroupDataService;
use crmeb\utils\Cron;
use think\facade\Log;

/**
 * 自动更新过期缓存
 * Class AutoSeckill
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2022/11/4
 * @package app\listener\activity
 */
class AutoSeckill extends Cron implements ListenerInterface
{

    /**
     * @param $event
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/4
     */
    public function handle($event): void
    {
        $this->tick(2000, function () {
            /** @var StoreSeckillServices $seckillService */
            $seckillService = app()->make(StoreSeckillServices::class);

            $seckillTime = GroupDataService::getData('routine_seckill_time') ?? [];

            try {
                foreach ($seckillTime as $item) {
                    $res = $seckillService->cacheList((string)$item['id']);
                    foreach ((array)$res as $value) {
                        if (($value['stop_time'] + 86400) <= time()) {
                            $seckillService->cacheDelById((int)$value['id']);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('清除秒杀数据缓存失败:' . $e->getMessage() . '|file:' . $e->getFile());
            }

        });
    }
}
