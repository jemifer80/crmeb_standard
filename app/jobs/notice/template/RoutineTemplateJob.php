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

use app\services\message\TemplateMessageServices;
use crmeb\basic\BaseJobs;
use crmeb\services\wechat\MiniProgram;
use crmeb\traits\QueueTrait;
use crmeb\services\template\Template;

/**
 * 小程序模板消息消息队列
 * Class RoutineTemplateJob
 * @package app\jobs
 */
class RoutineTemplateJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 同步订阅消息
     * @return bool
     */
    public function doJob(array $template, array $errMessage)
    {
        $time = time();

        $works = [];
        try {
            $works = MiniProgram::getSubscribeTemplateKeyWords($template['tempkey']);
        } catch (\Throwable $e) {
            $wechatErr = $e->getMessage();
            if (is_string($wechatErr)) {
                response_log_write([
                    'message' => '同步订阅消息,获取关键词列表失败：' . $wechatErr,
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            } elseif (in_array($wechatErr->getCode(), array_keys($errMessage))) {
                response_log_write([
                    'message' => '同步订阅消息,获取关键词列表失败：' . $wechatErr->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        }
        $kid = [];
        if ($works) {
            $works = array_combine(array_column($works, 'name'), $works);
            $content = is_array($template['content']) ? $template['content'] : explode("\n", $template['content']);
            foreach ($content as $c) {
                $name = explode('{{', $c)[0] ?? '';
                if ($name && isset($works[$name])) {
                    $kid[] = $works[$name]['kid'];
                }
            }
        }
        if ($kid && isset($template['kid']) && !$template['kid']) {
            $tempid = '';
            try {
                $tempid = MiniProgram::addSubscribeTemplate($template['tempkey'], $kid, $template['name']);
            } catch (\Throwable $e) {
                $wechatErr = $e->getMessage();
                if (is_string($wechatErr)) {
                    response_log_write([
                        'message' => '同步订阅消息,添加订阅消息模版失败：' . $wechatErr,
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                } elseif (in_array($wechatErr->getCode(), array_keys($errMessage))) {
                    response_log_write([
                        'message' => '同步订阅消息,添加订阅消息模版失败：' . $wechatErr->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
            }
            if ($tempid != $template['tempid']) {
                /** @var TemplateMessageServices $templateMessageServices */
                $templateMessageServices = app()->make(TemplateMessageServices::class);
                $templateMessageServices->update($template['id'], ['tempid' => $tempid, 'kid' => json_encode($kid), 'add_time' => $time], 'id');
            }
        }

        return true;
    }

    /**
     * 确认收货
     * @param $openid
     * @param $order
     * @param $title
     * @return bool
     */
    public function sendOrderTakeOver($openid, $order, $title)
    {
        return $this->sendTemplate('ORDER_TAKEVER', $openid, [
            'thing1' => $order['order_id'],
            'thing2' => mb_substr_str($title, 20),
            'date5' => date('Y-m-d H:i:s', time()),
        ], '/pages/users/order_details/index?order_id=' . $order['order_id']);
    }

    /**
     * @param $openid
     * @param $order
     * @param $storeTitle
     * @param int $isGive 0 = 同城配送， 1 = 快递发货
     * @return bool
     */
    public function sendOrderPostage($openid, $order, $storeTitle, int $isGive = 0)
    {
        if ($isGive) {//快递发货
            return $this->sendTemplate('ORDER_DELIVER_SUCCESS', $openid, [
                'character_string2' => $order['delivery_id'],
                'thing1' => mb_substr_str($order['delivery_name'], 20),
                'time3' => date('Y-m-d H:i:s', time()),
                'thing5' => mb_substr_str($storeTitle, 20),
            ], '/pages/users/order_details/index?order_id=' . $order['order_id']);
        } else {//同城配送
            return $this->sendTemplate('ORDER_POSTAGE_SUCCESS', $openid, [
                'thing8' => mb_substr_str($storeTitle, 20),
                'character_string1' => $order['order_id'],
                'name4' => mb_substr_str($order['delivery_name'], 10, '...', 1),
                'phone_number10' => $order['delivery_id']
            ], '/pages/users/order_details/index?order_id=' . $order['order_id']);
        }
    }

    /**
     * 充值金额退款
     * @param $UserRecharge
     * @param $refund_price
     * @return bool
     */
    public function sendRechargeSuccess($openid, $UserRecharge, $now_money)
    {
        return $this->sendTemplate('RECHARGE_SUCCESS', $openid, [
            'character_string1' => $UserRecharge['order_id'],
            'amount3' => $UserRecharge['price'],
            'amount4' => $now_money,
            'date5' => date('Y-m-d H:i:s', time()),
        ], '/pages/users/user_bill/index?type=2');
    }

    /**
     * 订单退款成功发送消息
     * @param string $openid
     * @param array $order
     * @return bool
     */
    public function sendOrderRefundSuccess($openid, $order, $storeTitle)
    {
        return $this->sendTemplate('ORDER_REFUND', $openid, [
            'thing1' => '已成功退款',
            'thing2' => mb_substr_str($storeTitle, 20),
            'amount3' => $order['pay_price'],
            'character_string6' => $order['order_id']
        ], '/pages/users/order_details/index?order_id=' . $order['order_id'] . '&isReturen=1');
    }

    /**
     * 订单退款失败
     * @param string $openid
     * @param $order
     * @return bool
     */
    public function sendOrderRefundFail($openid, $order, $storeTitle)
    {
        return $this->sendTemplate('ORDER_REFUND', $openid, [
            'thing1' => '退款失败',
            'thing2' => mb_substr_str($storeTitle, 20),
            'amount3' => $order['pay_price'],
            'character_string6' => $order['order_id']
        ], '/pages/users/order_details/index?order_id=' . $order['order_id'] . '&isReturen=1');
    }

    /**
     * 用户申请退款给管理员发送消息
     * @param array $order
     * @param string $refundReasonWap
     * @param array $adminList
     */
    public function sendOrderRefundStatus($openid, $order)
    {
        $data['character_string4'] = $order['order_id'];
        $data['date5'] = date('Y-m-d H:i:s', time());
        $data['amount2'] = $order['pay_price'];
        $data['phrase7'] = '申请退款中';
        $data['thing8'] = '请及时处理';
        return $this->sendTemplate('ORDER_REFUND_STATUS', $openid, $data);
    }

    /**
     * 砍价成功通知
     * @param array $bargain
     * @param array $bargainUser
     * @param int $bargainUserId
     * @return bool
     */
    public function sendBargainSuccess($openid, $bargain = [], $bargainUser = [], $bargainUserId = 0)
    {
        $data['thing1'] = mb_substr_str($bargain['title'], 20);
        $data['amount2'] = $bargain['min_price'];
        $data['thing3'] = '恭喜您，已经砍到最低价了';
        return $this->sendTemplate('BARGAIN_SUCCESS', $openid, $data, '/pages/activity/goods_bargain_details/index?id=' . $bargain['id'] . '&bargain=' . $bargainUserId);
    }

    /**
     * 订单支付成功发送模板消息
     * @param $openidf
     * @param $pay_price
     * @param $orderId
     * @param $payTime
     * @return bool|void
     */
    public function sendOrderSuccess($openid, $pay_price, $orderId)
    {
        if ($orderId == '') return true;
        $data['character_string1'] = $orderId;
        $data['amount2'] = $pay_price . '元';
        $data['date3'] = date('Y-m-d H:i:s', time());
        return $this->sendTemplate('ORDER_PAY_SUCCESS', $openid, $data, '/pages/users/order_details/index?order_id=' . $orderId);
    }

    /**
     * 会员订单支付成功发送消息
     * @param $openid
     * @param $pay_price
     * @param $orderId
     * @return bool
     */
    public function sendMemberOrderSuccess($openid, $pay_price, $orderId)
    {
        if ($orderId == '') return true;
        $data['character_string1'] = $orderId;
        $data['amount2'] = $pay_price . '元';
        $data['date3'] = date('Y-m-d H:i:s', time());
        return $this->sendTemplate('ORDER_PAY_SUCCESS', $openid, $data, '/pages/annex/vip_paid/index');
    }

    /**
     * 提现失败
     * @param $openid
     * @param $msg
     * @param $extract_number
     * @param $extract_type
     * @return bool
     */
    public function sendExtractFail($openid, $msg, $extract_number, $nickname)
    {
        return $this->sendTemplate('USER_EXTRACT', $openid, [
            'thing1' => mb_substr_str('提现失败：' . $msg, 20),
            'amount2' => $extract_number . '元',
            'thing3' => mb_substr_str($nickname, 20),
            'date4' => date('Y-m-d H:i:s', time())
        ], '/pages/users/user_spread_money/index?type=2');
    }

    /**
     * 提现成功
     * @param $openid
     * @param $extract_number
     * @param $nickname
     * @return bool
     */
    public function sendExtractSuccess($openid, $extract_number, $nickname)
    {
        return $this->sendTemplate('USER_EXTRACT', $openid, [
            'thing1' => '提现成功',
            'amount2' => $extract_number . '元',
            'thing3' => mb_substr_str($nickname, 20),
            'date4' => date('Y-m-d H:i:s', time())
        ], '/pages/users/user_spread_money/index?type=2');
    }

    /**
     * 拼团成功通知
     * @param $uid
     * @param $pinkTitle
     * @param $nickname
     * @param $pinkTime
     * @param $count
     * @return bool
     */
    public function sendPinkSuccess($openid, $pinkTitle, $nickname, $pinkTime, $count, string $link = '')
    {
        return $this->sendTemplate('PINK_TRUE', $openid, [
            'thing1' => mb_substr_str($pinkTitle, 20),
            'name3' => mb_substr_str($nickname, 10, '...', 1),
            'date5' => date('Y-m-d H:i:s', $pinkTime),
            'number2' => $count
        ], $link);
    }

    /**
     * 拼团状态通知
     * @param $openid
     * @param $pinkTitle
     * @param $count
     * @param $remarks
     * @return bool
     */
    public function sendPinkFail($openid, $pinkTitle, $count, $remarks, $link)
    {
        return $this->sendTemplate('PINK_STATUS', $openid, [
            'thing2' => mb_substr_str($pinkTitle, 20),
            'thing1' => mb_substr_str($count, 20),
            'thing3' => mb_substr_str($remarks, 20)
        ], $link);
    }

    /**
     * 赠送积分消息提醒
     * @param $openid
     * @param $order
     * @param $storeTitle
     * @param $gainIntegral
     * @param $integral
     * @return bool
     */
    public function sendUserIntegral($openid, $order, $storeTitle, $gainIntegral, $integral)
    {
        if (!$order || !$openid) return true;
        if (is_string($order['cart_id']))
            $order['cart_id'] = json_decode($order['cart_id'], true);
        return $this->sendTemplate('INTEGRAL_ACCOUT', $openid, [
            'character_string2' => $order['order_id'],
            'thing3' => mb_substr_str($storeTitle, 20),
            'amount4' => $order['pay_price'],
            'number5' => $gainIntegral,
            'number6' => $integral
        ], '/pages/users/user_integral/index');
    }

    /**
     * 发送模板消息
     * @param string $TempCode 模板消息常量名称
     * @param int $openid 用户openid
     * @param array $data 模板内容
     * @param string $link 跳转链接
     * @return bool
     */
    public function sendTemplate(string $tempCode, $openid, array $data, string $link = '')
    {
        try {
            if (!$openid) return true;
            $template = new Template('subscribe');
            $res = $template->to($openid)->url($link)->send($tempCode, $data);
            if (!$res) {
                response_log_write([
                    'message' => '订阅消息发送失败，原因：' . $template->getError() . '----参数：' . json_encode(compact('tempCode', 'openid', 'data', 'link'))
                ]);
            }
            return true;
        } catch (\Exception $e) {
            response_log_write([
                'message' => '订阅消息发送失败，原因：' . $template->getError() . '----参数：' . json_encode(compact('tempCode', 'openid', 'data', 'link')),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return true;
        }
    }

    /**
     * 获得推广佣金发送提醒
     * @param string $openid
     * @param string $brokeragePrice
     * @param string $goods_name
     * @return bool
     */
    public function sendOrderBrokerageSuccess(string $openid, string $brokeragePrice, string $goods_name)
    {
        return $this->sendTemplate('ORDER_BROKERAGE', $openid, [
            'thing2' => mb_substr_str($goods_name, 20),
            'amount4' => $brokeragePrice . '元',
            'time1' => date('Y-m-d H:i:s', time())
        ], '/pages/users/user_spread_user/index');
    }

    /**
     * 绑定推广关系发送消息提醒
     * @param string $openid
     * @param string $userName
     * @return bool|mixed
     */
    public function sendBindSpreadUidSuccess(string $openid, string $userName)
    {
        $name3 = $userName . "加入您的团队";
        return $this->sendTemplate('BIND_SPREAD_UID', $openid, [
            'name3' => mb_substr_str($name3, 10, '...', 1),
            'date4' => date('Y-m-d H:i:s', time())
        ], '/pages/users/user_spread_user/index');
    }
}
