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

namespace app\services\wechat;


use app\dao\wechat\WechatMessageDao;
use app\services\BaseServices;
use think\exception\ValidateException;
use crmeb\services\CacheService;
use think\facade\Log;

/**
 *
 * Class WechatMenuServices
 * @package app\services\wechat
 * @mixin WechatMessageDao
 */
class WechatMessageServices extends BaseServices
{
    /**
     * 构造方法
     * WechatMessageServices constructor.
     * @param WechatMessageDao $dao
     */
    public function __construct(WechatMessageDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param $result
     * @param $openid
     * @param $type
     * @return \think\Model
     */
    public function setMessage($result, $openid, $type)
    {
        if (is_object($result) || is_array($result)) $result = json_encode($result);
        $add_time = time();
        $data = compact('result', 'openid', 'type', 'add_time');
        return $this->dao->save($data);
    }

    /**
     * @param $result
     * @param $openid
     * @param $type
     * @param $unique
     * @param int $cacheTime
     * @return bool|\think\Model
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setOnceMessage($result, $openid, $type, $unique, $cacheTime = 172800)
    {
        $cacheName = 'wechat_message_' . $type . '_' . $unique;
        if (CacheService::has($cacheName)) return true;
        $res = $this->setMessage($result, $openid, $type);
        if ($res) CacheService::set($cacheName, 1, $cacheTime);
        return $res;
    }

    /**
     * 微信消息前置操作
     * @param $message
     * @param $spread_uid
     */
    public function wechatMessageBefore($message, $spread_uid = 0)
    {
		//是否开启
		if (sys_config('create_wechat_user', 1)) {
			try {
				/** @var WechatUserServices $wechatUser */
				$wechatUser = app()->make(WechatUserServices::class);
				$wechatUser->saveUser($message['FromUserName'], $spread_uid);
			} catch (\Throwable $e) {
				Log::error('关注公众号生成用户失败，原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
			}
		}

        $msgType = $message['MsgType'] ?? '';
        $event = isset($message['Event']) ?
            $msgType . (
            $message['Event'] == 'subscribe' && isset($message['EventKey']) ? '_scan' : ''
            ) . '_' . $message['Event'] : $msgType;
        $result = json_encode($message);
        $openid = $message['FromUserName'];
        $type = strtolower($event);
        $add_time = time();
        if (!$this->dao->save(compact('result', 'openid', 'type', 'add_time'))) {
            throw new ValidateException('更新信息失败');
        }
        return true;
    }
}
