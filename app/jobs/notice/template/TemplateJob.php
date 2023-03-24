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

namespace app\jobs\notice\template;


use crmeb\basic\BaseJobs;
use crmeb\services\template\Template;
use crmeb\traits\QueueTrait;
use think\facade\Route;

/**
 * Class TemplateJob
 * @package app\jobs
 */
class TemplateJob extends BaseJobs
{
    use QueueTrait;

    /**
     * @param $type
     * @param $openid
     * @param $tempCode
     * @param $data
     * @param $link
     * @param $color
     * @return bool|mixed
     */
    public function doJob($type, $openid, $tempCode, $data, $link, $color)
    {
        try {
            if (!$openid) return true;
            $template = new Template($type ?: 'wechat');
            $template->to($openid);
            if ($color) {
                $template->color($color);
            }
            if ($link) {

                switch ($type) {
                    case 'wechat':
                        $link =
                            sys_config('site_url') . Route::buildUrl($link)
                                ->suffix('')
                                ->domain(false)->build();
                        break;
                }

                $template->url($link);
            }
            $res = $template->send($tempCode, $data);
            if (!$res) {
                $msg = $type == 'wechat' ? '微信模版消息' : '订阅消息';
                response_log_write([
                    'message' => $msg . '发送失败，原因：' . $template->getError() . '----参数：' . json_encode(compact('tempCode', 'openid', 'data', 'link')),
                ]);
            }
            return true;
        } catch (\Exception $e) {
            response_log_write([
                'message' => $msg . '发送失败，原因：' . $template->getError() . '----参数：' . json_encode(compact('tempCode', 'openid', 'data', 'link')),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return true;
        }
    }

}
