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

namespace app\jobs\notice;


use app\services\message\sms\SmsSendServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 短信
 * Class SmsJob
 * @package app\jobs\notice
 */
class SmsJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 发送短信
     * @param $switch
     * @param $adminList
     * @param $order
     * @return bool
     */
    public function doJob($phone, array $data, string $template)
    {

        try {
            /** @var SmsSendServices $smsServices */
            $smsServices = app()->make(SmsSendServices::class);
            $smsServices->send(true, $phone, $data, $template);
            return true;
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '发送短信消息失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

    }

}
