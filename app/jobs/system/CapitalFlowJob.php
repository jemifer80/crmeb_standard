<?php

namespace app\jobs\system;

use app\services\system\CapitalFlowServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

class CapitalFlowJob extends BaseJobs
{
    use QueueTrait;

    public function doJob($data, $type)
    {
        try {
            /** @var CapitalFlowServices $capitalFlowServices */
            $capitalFlowServices = app()->make(CapitalFlowServices::class);
            $capitalFlowServices->setFlow($data, $type);
        } catch (\Throwable $e) {

            response_log_write([
                'message' => '写入资金流水错误:[' . class_basename($this) . ']' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;

    }
}
