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

namespace crmeb\services\wechat;


use crmeb\exceptions\PayException;
use crmeb\services\wechat\config\MiniProgramConfig;
use crmeb\services\wechat\config\OpenAppConfig;
use crmeb\services\wechat\config\OpenWebConfig;
use crmeb\services\wechat\config\PaymentConfig;
use crmeb\services\wechat\config\V3PaymentConfig;
use crmeb\services\wechat\v3pay\ServiceProvider;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\Exception;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\Support\Collection;
use EasyWeChat\Payment\Application;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use think\facade\Event;
use think\Response;
use Yurun\Util\Swoole\Guzzle\SwooleHandler;
use crmeb\services\wechat\Factory as miniFactory;

/**
 *  微信支付
 * Class Payment
 * @package crmeb\services\wechat
 */
class Payment extends BaseApplication
{

    /**
     * @var PaymentConfig
     */
    protected $config;

    /**
     * @var
     */
    protected $v3Config;

    /**
     * 是否v3支付
     * @var bool
     */
    public $isV3PAy = true;

    /**
     * @var array
     */
    protected $application = [];

    /**
     * Payment constructor.
     * @param PaymentConfig $config
     */
    public function __construct(PaymentConfig $config, V3PaymentConfig $v3Config)
    {
        $this->config = $config;
        $this->v3Config = $v3Config;
        $this->isV3PAy = $this->v3Config->get('isV3PAy');
        $this->debug = DefaultConfig::value('logger');
    }

    /**
     * @return Payment
     */
    public static function instance()
    {
        return app()->make(static::class);
    }

    /**
     * @return Application|mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/11
     */
    public function application()
    {
        $request = request();
        $config = $this->config->all();
        switch ($accessEnd = $this->getAuthAccessEnd($request)) {
            case self::APP:
                /** @var OpenAppConfig $make */
                $make = app()->make(OpenAppConfig::class);
                $config['app_id'] = $make->get('appId');
                $config['notify_url'] = trim($make->getConfig(DefaultConfig::COMMENT_URL)) . DefaultConfig::value('app.notifyUrl');
                break;
            case self::PC:
                /** @var OpenWebConfig $make */
                $make = app()->make(OpenWebConfig::class);
                $config['app_id'] = $make->get('appId');
                break;
            case self::MINI:
                /** @var MiniProgramConfig $make */
                $make = app()->make(MiniProgramConfig::class);
                $config['app_id'] = $make->get('appId');
                $config['notify_url'] = trim($make->getConfig(DefaultConfig::COMMENT_URL)) . DefaultConfig::value('mini.notifyUrl');
                break;
        }

        //v3支付配置
        $config['v3_payment'] = $this->v3Config->all();

        if (!isset($this->application[$accessEnd])) {
            $this->application[$accessEnd] = Factory::payment($config);
            $this->application[$accessEnd]['guzzle_handler'] = SwooleHandler::class;
            $this->application[$accessEnd]->rebind('request', new Request($request->get(), $request->post(), [], [], [], $request->server(), $request->getContent()));
            $this->application[$accessEnd]->register(new ServiceProvider());
        }
        return $this->application[$accessEnd];
    }

    /**
     * @return \crmeb\services\wechat\MiniPayment\Application
     */
    public function miniApplication($isMerchantPay = false)
    {
        $request = request();
        $accessEnd = $this->getAuthAccessEnd($request);
        if (!$isMerchantPay && $accessEnd !== 'mini') {
            throw new PayException('支付方式错误，请刷新后重试！');
        }
        $config = $this->config->all();
        /** @var MiniProgramConfig $make */
        $make = app()->make(MiniProgramConfig::class);
        $config['app_id'] = $make->get('appId');
        $config['secret'] = $make->get('secret');
        $config['mch_id'] = $this->config->get('routineMchId');
        if (!isset($this->application[$accessEnd])) {
            $this->application[$accessEnd] = miniFactory::MiniPayment($config);
            $this->application[$accessEnd]['guzzle_handler'] = SwooleHandler::class;
            $this->application[$accessEnd]->rebind('request', new Request($request->get(), $request->post(), [], [], [], $request->server(), $request->getContent()));
        }
        return $this->application[$accessEnd];
    }

    /**
     * 付款码支付
     * @param string $authCode
     * @param string $outTradeNo
     * @param string $totalFee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @return array
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public static function microPay(string $authCode, string $outTradeNo, string $totalFee, string $attach, string $body, string $detail = '')
    {
        $application = self::instance()->application();
        $totalFee = bcmul($totalFee, 100, 0);
        $response = $application->pay([
            'auth_code' => $authCode,
            'out_trade_no' => $outTradeNo,
            'total_fee' => (int)$totalFee,
            'attach' => $attach,
            'body' => $body,
            'detail' => $detail
        ]);

        self::logger('付款码支付', compact('authCode', 'outTradeNo', 'totalFee', 'attach', 'body', 'detail'), $response);

        //下单成功
        if ($response['return_code'] === 'SUCCESS') {
            //扫码付款直接支付成功
            if ($response['result_code'] === 'SUCCESS' && $response['trade_type'] === 'MICROPAY') {
                return [
                    'paid' => 1,
                    'message' => '支付成功',
                    'payInfo' => $response,
                ];
            } else {
                return [
                    'paid' => 0,
                    'message' => $response['err_code_des'],
                    'payInfo' => $response
                ];
            }
        } else {
            throw new PayException($response['return_msg']);
        }
    }

    /**
     * 撤销订单
     * @param string $outTradeNo
     * @return bool
     * @throws InvalidConfigException
     */
    public static function reverseOrder(string $outTradeNo)
    {
        $response = self::instance()->application()->reverse->byOutTradeNumber($outTradeNo);

        self::logger('撤销订单', compact('outTradeNo'), $response);

        if ($response['return_code'] === 'SUCCESS') {
            return true;
        } else {
            throw new PayException($response['return_msg']);
        }
    }

    /**
     * 查询订单支付状态
     * @param string $outTradeNo
     * @return array
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public static function queryOrder(string $outTradeNo)
    {
        $response = self::instance()->application()->order->queryByOutTradeNumber($outTradeNo);

        self::logger('查询订单支付状态', compact('outTradeNo'), $response);

        if ($response['return_code'] === 'SUCCESS') {
            if ($response['result_code'] === 'SUCCESS') {
                return [
                    'paid' => 1,
                    'out_trade_no' => $outTradeNo,
                    'payInfo' => $response
                ];
            } else {
                return [
                    'paid' => 0,
                    'out_trade_no' => $outTradeNo,
                    'payInfo' => $response
                ];
            }
        } else {
            throw new PayException($response['return_msg']);
        }
    }

    /**
     * 企业付款到零钱
     * @param string $openid openid
     * @param string $orderId 订单号
     * @param string $amount 金额
     * @param string $desc 说明
     * @param string $type 类型
     * @return bool
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public static function merchantPay(string $openid, string $orderId, string $amount, string $desc, string $type = 'wechat')
    {
        $application = self::instance()->setAccessEnd($type)->application();
        $config = $application->getConfig();
        if (!isset($config['cert_path'])) {
            throw new PayException('企业微信支付到零钱需要支付证书，检测到您没有上传！');
        }
        if (!$config['cert_path']) {
            throw new PayException('企业微信支付到零钱需要支付证书，检测到您没有上传！');
        }

        if (self::instance()->isV3PAy) {
            //v3支付使用发起商家转账API
            $res = $application->v3pay->setType($type)->batches(
                $orderId,
                $amount,
                $desc,
                $desc,
                [
                    [
                        'out_detail_no' => $orderId,
                        'transfer_amount' => $amount,
                        'transfer_remark' => $desc,
                        'openid' => $openid
                    ]
                ]
            );

            return $res;

        } else {
            $merchantPayData = [
                'partner_trade_no' => $orderId, //随机字符串作为订单号，跟红包和支付一个概念。
                'openid' => $openid, //收款人的openid
                'check_name' => 'NO_CHECK',  //文档中有三种校验实名的方法 NO_CHECK OPTION_CHECK FORCE_CHECK
                'amount' => (int)bcmul($amount, '100', 0),  //单位为分
                'desc' => $desc,
                'spbill_create_ip' => request()->ip(),  //发起交易的IP地址
            ];
            $result = $application->transfer->toBalance($merchantPayData);

            self::logger('企业付款到零钱', compact('merchantPayData'), $result);

            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] != 'FAIL') {
                return true;
            } else {
                throw new PayException(($result['return_msg'] ?? '支付失败') . ':' . ($result['err_code_des'] ?? '发起企业支付到零钱失败'));
            }
        }

    }

    /**
     * 生成支付订单对象
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param $trade_type
     * @param array $options
     * @return array|Collection|object|ResponseInterface|string
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public static function paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', array $options = [])
    {
        $total_fee = bcmul($total_fee, 100, 0);
        $order = array_merge(compact('out_trade_no', 'total_fee', 'attach', 'body', 'detail', 'trade_type'), $options);
        if (!is_null($openid)) $order['openid'] = $openid;
        if ($order['detail'] == '') unset($order['detail']);
        $order['spbill_create_ip'] = request()->ip();
        $result = self::instance()->application()->order->unify($order);

        self::logger('生成支付订单对象', compact('order'), $result);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return $result;
        } else {
            if ($result['return_code'] == 'FAIL') {
                throw new PayException('微信支付错误返回：' . $result['return_msg']);
            } else if (isset($result['err_code'])) {
                throw new PayException('微信支付错误返回：' . $result['err_code_des']);
            } else {
                throw new PayException('没有获取微信支付的预支付ID，请重新发起支付!');
            }
        }
    }

    /**
     * 生成支付订单对象(小程序商户号支付时)
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param $trade_type
     * @param array $options
     * @return array|Collection|object|ResponseInterface|string
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public static function paymentMiniOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', array $options = [])
    {
        $total_fee = bcmul($total_fee, 100, 0);
        $order = array_merge(compact('out_trade_no', 'total_fee', 'attach', 'body', 'detail', 'trade_type'), $options);
        if (!is_null($openid)) $order['openid'] = $openid;
        if ($order['detail'] == '') unset($order['detail']);
        $order['spbill_create_ip'] = request()->ip();
        $result = self::instance()->miniApplication()->orders->createorder($order);
        self::logger('生成支付订单对象', compact('order'), $result);
        if ($result['errcode'] == '0') {
            return $result;
        } else {
            throw new PayException('微信支付错误返回：' . $result['errmsg']);
        }
    }


    /**
     * 获得jsSdk支付参数
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array
     */
    public static function jsPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $paymentPrepare = self::paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        $config = self::instance()->application()->jssdk->bridgeConfig($paymentPrepare['prepay_id'], false);
        $config['timestamp'] = $config['timeStamp'];
        unset($config['timeStamp']);
        return $config;
    }

    /**
     * 获得jsSdk支付参数(小程序商户号支付时)
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array
     */
    public static function miniPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $paymentPrepare = self::paymentMiniOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        $paymentPrepare['payment_params']['timestamp'] = $paymentPrepare['payment_params']['timeStamp'];
        return $paymentPrepare['payment_params'] ?? [];
    }

    /**
     * 获得APP付参数
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array|string
     */
    public static function appPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'APP', $options = [])
    {
        if (self::instance()->isV3PAy) {
            return self::instance()->v3pay->appPay($out_trade_no, $total_fee, $body, $attach);
        } else {
            $paymentPrepare = self::paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
            return self::instance()->application()->jssdk->appConfig($paymentPrepare['prepay_id']);
        }
    }

    /**
     * 获得native支付参数
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array|string
     */
    public static function nativePay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'NATIVE', $options = [])
    {
        $instance = self::instance();

        if ($instance->isV3PAy) {
            $data = $instance->application()->v3pay->nativePay($out_trade_no, $total_fee, $body, $attach);
            $res['code_url'] = $data['code_url'];
            $res['invalid'] = time() + 60;
            $res['logo'] = [];
            return $res;
        }

        $data = $instance->setAccessEnd(self::WEB)->paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        if ($data) {
            $res['code_url'] = $data['code_url'];
            $res['invalid'] = time() + 60;
            $res['logo'] = [];
        } else $res = [];
        return $res;
    }

    /**
     * 使用商户订单号退款
     * @param $orderNo
     * @param $refundNo
     * @param $totalFee
     * @param null $refundFee
     * @param null $opUserId
     * @param string $refundReason
     * @param string $type
     * @param string $refundAccount
     * @return array|Collection|object|ResponseInterface|string
     * @throws InvalidConfigException
     */
    public function refund($orderNo, $refundNo, $totalFee, $refundFee = null, $opUserId = null, string $refundReason = '', string $type = 'out_trade_no', string $refundAccount = 'REFUND_SOURCE_UNSETTLED_FUNDS')
    {
        $totalFee = floatval($totalFee);
        $refundFee = floatval($refundFee);
        if ($type == 'out_trade_no') {
            $result = $this->application()->refund->byOutTradeNumber($orderNo, $refundNo, $totalFee, $refundFee, [
                'refund_account' => $refundAccount,
                'notify_url' => self::instance()->config->get('refundUrl'),
                'refund_desc' => $refundReason
            ]);
        } else {
            $result = $this->application()->refund->byTransactionId($orderNo, $refundNo, $totalFee, $refundFee, [
                'refund_account' => $refundAccount,
                'notify_url' => self::instance()->config->get('refundUrl'),
                'refund_desc' => $refundReason
            ]);
        }

        self::logger('使用商户订单号退款', compact('orderNo', 'refundNo', 'totalFee', 'refundFee', 'opUserId', 'refundReason', 'type', 'refundAccount'), $result);

        return $result;
    }

    /**
     * 小程序商户退款
     * @param $orderNo //微信支付单号
     * @param $refundNo //微信退款单号
     * @param $totalFee
     * @param null $refundFee
     * @param null $opUserId
     * @param string $refundReason
     * @param string $type
     * @param string $refundAccount
     * @return array|Collection|object|ResponseInterface|string
     * @throws InvalidConfigException
     */
    public function miniRefund($orderNo, $refundNo, $totalFee, $refundFee = null, array $opt = [])
    {
        $totalFee = floatval($totalFee);
        $refundFee = floatval($refundFee);

        $order = [
            'openid' => $opt['open_id'],
            'trade_no' => $opt['routine_order_id'],
            'transaction_id' => $orderNo,
            'refund_no' => $refundNo,
            'total_amount' => $totalFee,
            'refund_amount' => $refundFee,
        ];
        $result = $this->miniApplication()->orders->refundorder($order);

        self::logger('使用商户订单号退款', compact('orderNo', 'refundNo', 'totalFee', 'refundFee', 'opt'), $result);

        return $result;
    }

    /**
     * 退款
     * @param $orderNo
     * @param array $opt
     * @return bool
     */
    public function payOrderRefund($orderNo, array $opt)
    {
        if (isset($opt['pay_routine_open']) && $opt['pay_routine_open']) {
            return $this->payMiniOrderRefund($orderNo, $opt);
        }
        if (!isset($opt['pay_price'])) {
            throw new PayException('缺少pay_price');
        }
        $certPath = $this->config->get('certPath');
        if (!$certPath) {
            throw new PayException('请上传支付证书cert');
        }
        $keyPath = $this->config->get('keyPath');
        if (!$keyPath) {
            throw new PayException('请上传支付证书key');
        }
        if (!is_file($certPath)) {
            throw new PayException('支付证书cert不存在');
        }
        if (!is_file($keyPath)) {
            throw new PayException('支付证书key不存在');
        }

        if ($this->isV3PAy) {
            return $this->application()->v3pay->refund($orderNo, $opt);
        }

        $totalFee = floatval(bcmul($opt['pay_price'], 100, 0));
        $refundFee = isset($opt['refund_price']) ? floatval(bcmul($opt['refund_price'], 100, 0)) : null;
        $refundReason = $opt['desc'] ?? '';
        $refundNo = $opt['refund_id'] ?? $orderNo;
        $opUserId = $opt['op_user_id'] ?? null;
        $type = $opt['type'] ?? 'out_trade_no';
        /*仅针对老资金流商户使用
        REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
        REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款*/
        $refundAccount = $opt['refund_account'] ?? 'REFUND_SOURCE_UNSETTLED_FUNDS';
        try {
            $res = $this->refund($orderNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundReason, $type, $refundAccount);

            if ($res['return_code'] == 'FAIL') {
                throw new PayException('退款失败:' . $res['return_msg']);
            }
            if (isset($res['err_code'])) {
                throw new PayException('退款失败:' . $res['err_code_des']);
            }
        } catch (\Exception $e) {

            self::error($e);

            throw new PayException($e->getMessage());
        }
        return true;
    }

    /**
     * 小程序商户退款
     * @param $orderNo
     * @param array $opt
     * @return bool
     */
    public function payMiniOrderRefund($orderNo, array $opt)
    {
        if (!isset($opt['pay_price'])) {
            throw new PayException('缺少pay_price');
        }
        if (!isset($opt['routine_order_id'])) {
            throw new PayException('缺少订单单号');
        }
        $totalFee = floatval(bcmul($opt['pay_price'], 100, 0));
        $refundFee = isset($opt['refund_price']) ? floatval(bcmul($opt['refund_price'], 100, 0)) : null;
        $refundNo = $opt['refund_no'];
        try {
            $result = $this->miniRefund($orderNo, $refundNo, $totalFee, $refundFee, $opt);
            if ($result['errcode'] == '0') {
                return true;
            } else {
                throw new PayException('退款失败：' . $result['errmsg']);
            }
        } catch (\Exception $e) {

            self::error($e);

            throw new PayException($e->getMessage());
        }
    }

    /**
     * 微信支付成功回调接口
     * @return Response
     * @throws Exception
     */
    public function handleNotify()
    {
        if ($this->isV3PAy) {
            $response = $this->application()->v3pay->handleNotify(function ($notify, $success) {

                self::logger('微信支付成功回调接口', [], $notify);

                if (isset($notify['out_trade_no']) && $success) {
                    $res = Event::until('pay.notify', [$notify]);
                    if ($res) {
                        return $res;
                    } else {
                        return false;
                    }
                }

            });
        } else {
            $response = $this->application()->handlePaidNotify(function ($notify, $fail) {

                self::logger('微信支付成功回调接口', [], $notify);

                if (isset($notify['out_trade_no'])) {
                    $res = Event::until('pay.notify', [$notify]);
                    if ($res) {
                        return $res;
                    } else {
                        return $fail('支付通知失败');
                    }
                }
            });
        }

        return response($response->getContent());
    }

    /**
     * 扫码支付通知
     * @return Response
     * @throws Exception
     */
    public static function handleScannedNotify()
    {
        $make = self::instance();
        $response = $make->application()->handleScannedNotify(function ($message, $fail, $alert) use ($make) {

            self::logger('扫码支付通知', [], $message);

            $res = Event::until('pay.scan.notify', [$message]);
            if ($res) {
                return $res;
            } else {
                return $fail('扫码通知支付失败');
            }
        });

        return response($response->getContent());
    }

    /**
     * 退款结果通知
     * @return Response
     * @throws Exception
     */
    public function handleRefundedNotify()
    {
        $response = $this->application()->handleRefundedNotify(function ($message, $reqInfo, $fail) {

            self::logger('退款结果通知', [], compact('message', 'reqInfo'));

            $res = Event::until('pay.refunded.notify', [$message, $reqInfo]);
            if ($res) {
                return $res;
            } else {
                return $fail('退款通知处理失败');
            }
        });

        return response($response->getContent());
    }

    /**
     * 是否时微信付款二维码值
     * @param string $authCode
     * @return bool
     */
    public static function isWechatAuthCode(string $authCode)
    {
        return preg_match('/^[0-9]{18}$/', $authCode) && in_array(substr($authCode, 0, 2), ['10', '11', '12', '13', '14', '15']);
    }
}
