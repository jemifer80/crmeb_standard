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

namespace app\services\message;


use app\jobs\notice\EnterpriseWechatJob;
use app\jobs\notice\PrintJob;
use app\services\BaseServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\wechat\WechatUserServices;
use crmeb\services\CacheService;
use think\exception\ValidateException;


class NoticeService extends BaseServices
{

    /**
     * 发送消息类型
     * @var array
     */
//    protected $type = [
//        'is_sms' => NoticeSmsService::class,
//        'is_system' => SystemSendServices::class,
//        'is_wechat' => WechatTemplateService::class,
//        'is_routine' => RoutineTemplateServices::class,
//        'is_ent_wechat' => EntWechatServices::class,
//    ];

    /**
     * @var array
     */
    protected $noticeInfo = [];

    /**
     * @var string
     */
    protected $event;

    /**
 	* 缓存消息体
	* @param string $event
	* @return $this
	* @throws \Throwable
	*/
    public function setEvent(string $event)
    {
        if ($this->event != $event) {
            $this->noticeInfo = CacheService::redisHandler('NOTCEINFO')->remember('NOTCE_' . $event, function () use ($event) {
                /** @var SystemNotificationServices $services */
                $services = app()->make(SystemNotificationServices::class);
                $noticeInfo = $services->getOneNotce(['mark' => $event]);
				if ($noticeInfo) {
					return $noticeInfo->toArray();
				} else {
					return [];
				}

            });
            $this->event = $event;
        }
        return $this;
    }


    /**
     * @param array $notceinfo
     * @param $data
     * @param string $msgtype
     */
    //企业微信群机器人
    public function EnterpriseWechatSend($data)
    {
        if (isset($this->noticeInfo['is_ent_wechat']) && isset($this->noticeInfo['url']) && $this->noticeInfo['is_ent_wechat'] == 1 && $this->noticeInfo['url'] !== '') {
            $url = $this->noticeInfo['url'] ?? '';
            $ent_wechat_text = $this->noticeInfo['ent_wechat_text'] ?? '';
            EnterpriseWechatJob::dispatchDo('doJob', [$data, $url, $ent_wechat_text]);

        }
    }

    /**
     * 打印订单
     * @param $order
     * @param array $cartId
     */
    public function orderPrint($order)
    {
        try {
            /** @var StoreOrderCartInfoServices $cartServices */
            $cartServices = app()->make(StoreOrderCartInfoServices::class);
            $product = $cartServices->getCartInfoPrintProduct((int)$order['id']);
            if (!$product) {
                throw new ValidateException('订单商品获取失败,无法打印!');
            }
            if (isset($order['store_id']) && $order['store_id']) {
                $store_id = $order['store_id'];
                $switch = store_config($store_id, 'store_pay_success_printing_switch');
                $configdata = [
                    'clientId' => store_config($store_id, 'store_printing_client_id', ''),
                    'apiKey' => store_config($store_id, 'store_printing_api_key', ''),
                    'partner' => store_config($store_id, 'store_develop_id', ''),
                    'terminal' => store_config($store_id, 'store_terminal_number', '')
                ];
            } else {
                $switch = sys_config('pay_success_printing_switch');
                $configdata = [
                    'clientId' => sys_config('printing_client_id', ''),
                    'apiKey' => sys_config('printing_api_key', ''),
                    'partner' => sys_config('develop_id', ''),
                    'terminal' => sys_config('terminal_number', '')
                ];
            }
            if ($switch) {
                if (!$configdata['clientId'] || !$configdata['apiKey'] || !$configdata['partner'] || !$configdata['terminal']) {
                    throw new ValidateException('请先配置小票打印开发者');
                }
                PrintJob::dispatch('doJob', ['yi_lian_yun', $configdata, $order, $product]);
            }
            return true;
        } catch (\Throwable $e) {
            \think\facade\Log::error('小票打印失败，原因：' . $e->getMessage());
            return false;
        }

    }

	/**
 	* 根据UID,user_type获取openid
	* @param int $uid
	* @param string $userType
	* @return mixed
	 */
    public function getOpenidByUid(int $uid, string $userType = 'wechat')
    {
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        return $wechatServices->uidToOpenid($uid, $userType);
    }


}
