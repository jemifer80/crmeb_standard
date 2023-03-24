<?php

namespace crmeb\services\wechat\MiniPayment\Payment;

use EasyWeChat\Kernel\BaseClient;

class WeChatClient extends BaseClient
{
    private $expire_time = 7000;
    /**
     * 创建订单 支付
     */
    const API_SET_CREATE_ORDER = 'shop/pay/createorder';
    /**
     * 退款
     */
    const API_SET_REFUND_ORDER = 'shop/pay/refundorder';

    /**
     * 支付
     * @param array $params[
     *                      'openid'=>'支付者的openid',
     *                      'out_trade_no'=>'商家合单支付总交易单号',
     *                      'total_fee'=>'支付金额',
     *                      'wx_out_trade_no'=>'商家交易单号',
     *                      'body'=>'商品描述',
     *                      'attach'=>'支付类型',  //product 产品  member 会员
     *                      ]
     * @param $isContract
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createorder(array $params)
    {
        $data = [
            'openid'=>$params['openid'],    // 支付者的openid
            'combine_trade_no'=>$params['out_trade_no'],  // 商家合单支付总交易单号
            'expire_time'=>time()+$this->expire_time,
            'sub_orders'=>[
                [
                    'mchid'=>$this->app['config']['mch_id'],
                    'amount'=>(int)$params['total_fee'],
                    'trade_no'=>$params['out_trade_no'],
                    'description'=>$params['body'],
                ]
            ],
        ];
        return $this->httpPostJson(self::API_SET_CREATE_ORDER, $data);
    }

    /**
     * 退款
     * @param array $params[
     *                      'openid'=>'退款者的openid',
     *                      'trade_no'=>'商家交易单号',
     *                      'transaction_id'=>'支付单号',
     *                      'refund_no'=>'商家退款单号',
     *                      'total_amount'=>'订单总金额',
     *                      'refund_amount'=>'退款金额',  //product 产品  member 会员
     *                      ]
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refundorder(array $params)
    {
        $data = [
            'openid'=>$params['openid'],
            'mchid'=>$this->app['config']['mch_id'],
            'trade_no'=>$params['trade_no'],
            'transaction_id'=>$params['transaction_id'],
            'refund_no'=>$params['refund_no'],
            'total_amount'=>(int)$params['total_amount'],
            'refund_amount'=>(int)$params['refund_amount'],
        ];
        return $this->httpPostJson(self::API_SET_REFUND_ORDER, $data);
    }
}