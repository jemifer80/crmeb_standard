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
declare (strict_types=1);

namespace app\services\pay;

use crmeb\services\AliPayService;
use crmeb\services\wechat\Payment;
use think\exception\ValidateException;

/**
 * 支付统一入口
 * Class PayServices
 * @package app\services\pay
 */
class PayServices
{
    /**
     * 微信支付类型
     */
    const WEIXIN_PAY = 'weixin';

    /**
     * 余额支付
     */
    const YUE_PAY = 'yue';

    /**
     * 线下支付
     */
    const OFFLINE_PAY = 'offline';

    /**
     * 支付宝
     */
    const ALIAPY_PAY = 'alipay';

    /**
     * 现金支付
     */
    const CASH_PAY = 'cash';

    /**
     * 支付方式
     * @var string[]
     */
    const PAY_TYPE = [
        PayServices::WEIXIN_PAY => '微信支付',
        PayServices::YUE_PAY => '余额支付',
        PayServices::OFFLINE_PAY => '线下支付',
        PayServices::ALIAPY_PAY => '支付宝',
        PayServices::CASH_PAY => '现金支付',
    ];

    /**
     * 二维码条码值
     * @var string
     */
    protected $authCode;

    /**
     * 设置二维码条码值
     * @param string $authCode
     * @return $this
     */
    public function setAuthCode(string $authCode)
    {
        $this->authCode = $authCode;
        return $this;
    }

    /**
     * 发起支付
     * @param string $payType
     * @param string $openid
     * @param string $orderId
     * @param string $price
     * @param string $successAction
     * @param string $body
     * @return array|string
     */
    public function pay(string $payType, string $openid, string $orderId, string $price, string $successAction, string $body, bool $isCode = false)
    {
        try {
            switch ($payType) {
                case 'routine':
                    //微信支付，从APP端请求过来
                    if (request()->isApp()) {
                        return Payment::appPay($openid, $orderId, $price, $successAction, $body);
                    } else {
                        //判断有没有打开小程序支付
                        if (sys_config('pay_routine_open', 0)) {
                            return Payment::miniPay($openid, $orderId, $price, $successAction, $body);
                        } else {
                            //开启了v3支付
                            if (Payment::instance()->isV3PAy) {
                                return Payment::instance()->application()->v3pay->miniprogPay($openid, $orderId, $price, $body, $successAction);
                            }
                            return Payment::jsPay($openid, $orderId, $price, $successAction, $body);
                        }
                    }
                case 'weixinh5':
                    ////开启了v3支付
                    if (Payment::instance()->isV3PAy) {
                        return Payment::instance()->application()->v3pay->h5Pay($orderId, $price, $body, $successAction);
                    }
                    //旧版v2支付
                    return Payment::paymentOrder(null, $orderId, $price, $successAction, $body, '', 'MWEB');
                case self::WEIXIN_PAY:
                    //微信支付，付款码支付，付款码支付使用v2支付接口
                    if ($this->authCode) {
                        return Payment::microPay($this->authCode, $orderId, $price, $successAction, $body);
                    } else {
                        //微信支付，从APP端请求过来
                        if (request()->isApp()) {
                            return Payment::appPay($openid, $orderId, $price, $successAction, $body);
                        } else {
                            //开启了v3支付
                            if (Payment::instance()->isV3PAy) {
                                return Payment::instance()->application()->v3pay->jsapiPay($openid, $orderId, $price, $body, $successAction);
                            }
                            //使用v2旧版支付接口
                            return Payment::jsPay($openid, $orderId, $price, $successAction, $body);
                        }
                    }
                case self::ALIAPY_PAY:
                    if ($this->authCode) {
                        return AliPayService::instance()->microPay($this->authCode, $body, $orderId, $price, $successAction);
                    } else {
                        return AliPayService::instance()->create($body, $orderId, $price, $successAction, $openid, $openid, $isCode);
                    }
                case 'pc':
                case 'store':
                    //方法内部已经做了区分v2和v3
                    return Payment::nativePay($openid, $orderId, $price, $successAction, $body);
                default:
                    throw new ValidateException('支付方式不存在');
            }
        } catch (\Throwable $e) {
            throw new ValidateException($e->getMessage());
        }
    }
}
