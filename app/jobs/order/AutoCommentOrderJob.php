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

namespace app\jobs\order;


use app\services\order\StoreOrderCommentServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 自动执行默认好评
 * Class AutoCommentOrderJob
 * @package app\jobs\order
 */
class AutoCommentOrderJob extends BaseJobs
{

    use QueueTrait;

    /**
     * @return string
     */
    protected static function queueName()
    {
        return 'CRMEB_PRO_TASK';
    }

    /**
     * @param $where
     * @param $page
     * @param $limit
     * @return bool
     */
    public function doJob($where, $page, $limit)
    {
        try {
            /** @var StoreOrderCommentServices $service */
            $service = app()->make(StoreOrderCommentServices::class);
            return $service->runAutoCommentOrder($where, $page, $limit);
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '自动默认好评,失败原因:[' . class_basename($this) . ']' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

    }
}
