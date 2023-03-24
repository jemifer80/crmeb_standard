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

namespace app\services\message\sms;

use app\services\BaseServices;
use app\services\serve\ServeServices;
use think\exception\ValidateException;

/**
 * 短信发送
 * Class SmsSendServices
 * @package app\services\message\sms
 */
class SmsSendServices extends BaseServices
{
    /**
     * 发送短信
     * @param bool $switch
     * @param $phone
     * @param array $data
     * @param string $template
     * @return bool
     */
    public function send(bool $switch, $phone, array $data, string $template)
    {
        if ($switch && $phone) {
            /** @var ServeServices $services */
            $services = app()->make(ServeServices::class);
            $res = $services->sms()->send($phone, $template, $data);
            if ($res === false) {
                throw new ValidateException($services->getError());
            }
            return true;
        } else {
            return false;
        }
    }

}
