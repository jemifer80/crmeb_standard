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

use crmeb\basic\BaseJobs;
use crmeb\services\HttpService;
use crmeb\traits\QueueTrait;

class EnterpriseWechatJob extends BaseJobs
{
    use QueueTrait;


    /**
     * 给企业微信群发送消息
     */
    public function doJob($data, $url, $ent_wechat_text)
    {
        try {
            $str = $ent_wechat_text;
            foreach ($data as $key => $item) {
                $str = str_replace('{' . $key . '}', $item, $str);
            }
            $s = explode('\n', $str);
            $d = '';
            foreach ($s as $item) {
                $d .= $item . "\n>";
            }
            $d = substr($d, 0, strlen($d) - 2);
            $datas = [
                'msgtype' => 'markdown',
                'markdown' => ['content' => $d]
            ];
            HttpService::postRequest($url, json_encode($datas));
            return true;
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '发送企业群消息失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

    }

}
