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
namespace crmeb\services\template\storage;

use app\services\message\TemplateMessageServices;
use crmeb\basic\BaseMessage;
use crmeb\services\CacheService;
use crmeb\services\wechat\MiniProgram;
use think\facade\Log;

/**
 * 订阅消息
 * Class Subscribe
 * @package crmeb\services\template\storage
 */
class Subscribe extends BaseMessage
{

    protected function initialize(array $config)
    {
        parent::initialize($config); //
    }

    /**
     * @param string $templateId
     * @return mixed
     */
    public function getTempId(string $templateId)
    {
        /** @var TemplateMessageServices $services */
        $services = app()->make(TemplateMessageServices::class);
        return CacheService::handler('TEMPLATE')->remember('subscribe_' . $templateId, function () use ($services, $templateId) {
            return $services->getTempId($templateId);
        });
    }

    /**
     * 发送订阅消息
     * @param string $templateId
     * @param array $data
     * @return bool|\EasyWeChat\Support\Collection|mixed|null
     */
    public function send(string $templateId, array $data = [])
    {
        $templateId = $this->getTemplateCode($templateId);
        if (!$templateId) {
            return $this->setError('Template number does not exist');
        }
        $tempid = $this->getTempId($templateId);
        if (!$tempid) {
            return $this->setError('Template ID does not exist');
        }
        if (!$this->openId) {
            return $this->setError('Openid does not exist');
        }
        try {
            $res = MiniProgram::sendSubscribeTemlate($this->openId, $tempid, $data, $this->toUrl);
            $this->clear();
            return $res;
        } catch (\Throwable $e) {
            $this->isLog() && Log::error('发送给openid为:' . $this->openId . '小程序订阅消息失败,模板id为:' . $tempid . ';错误原因为:' . $e->getMessage());
            return $this->setError($e->getMessage());
        }
    }

    public function delete(string $templateId)
    {
        //
    }

    public function add(string $shortId)
    {
        //
    }

    public function list()
    {
        //
    }
}
