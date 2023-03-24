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


use app\services\message\SystemMessageServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;


/**
 * 站内信
 * Class SystemMsgJob
 * @package app\jobs\notice
 */
class SystemMsgJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 发送站内信
     * @param $uid
     * @param $noticeInfo
     * @param $data
     * @param $type
     * @return bool
     */
    public function doJob($uid, $noticeInfo, $data, $type = 1)
    {
        if (!$uid || !$noticeInfo) {
            return true;
        }
        try {
            $title = $noticeInfo['system_title'];
            $str = $noticeInfo['system_text'];
            foreach ($data as $key => $item) {
                $str = str_replace('{' . $key . '}', $item, $str);
                $title = str_replace('{' . $key . '}', $item, $title);
            }

            $sdata = [];
            $sdata['mark'] = $noticeInfo['mark'];
            $sdata['uid'] = $uid;
            $sdata['content'] = $str;
            $sdata['title'] = $title;
            $sdata['type'] = $type;
            $sdata['add_time'] = time();
            /** @var SystemMessageServices $systemMessageServices */
            $systemMessageServices = app()->make(SystemMessageServices::class);
            $systemMessageServices->save($sdata);
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '发送站内信失败,原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }
}
