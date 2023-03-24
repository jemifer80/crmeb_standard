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

return [
    //默认驱动模式
    'default' => 'wechat',
    //记录发送日志
    'isLog' => true,
    //驱动模式
    'stores' => [
        //微信
        'wechat' => [
            //微信模板id
            'template_id' => [
                //绑定推广关系
                'BIND_SPREAD_UID' => 'OPENTM409880752',
                //支付成功
                'ORDER_PAY_SUCCESS' => 'OPENTM418062102',
                //订单发货提醒(送货)
                'ORDER_DELIVER_SUCCESS' => 'OPENTM416122303',
                //订单发货提醒(快递)
                'ORDER_POSTAGE_SUCCESS' => 'OPENTM415939287',
                //订单收货通知
                'ORDER_TAKE_SUCCESS' => 'OPENTM418528119',
                //改价成功通知
                'PRICE_REVISION' => 'OPENTM401202515',
                //退款成功通知,拒绝退款通知
                'ORDER_REFUND_STATUS'=>'OPENTM207284059',
                //充值成功通知
                'RECHARGE_SUCCESS' => 'OPENTM414089457',
                //积分到账通知
                'INTEGRAL_ACCOUT' => 'OPENTM201661503',
                //佣金到账
                'ORDER_BROKERAGE' => 'OPENTM400590844',
                //砍价成功
                'BARGAIN_SUCCESS' => 'OPENTM418554923',
                //拼团成功通知,参团成功
                'ORDER_USER_GROUPS_SUCCESS' => 'OPENTM409367318',
                //取消拼团,拼团失败
                'ORDER_USER_GROUPS_LOSE'=>'OPENTM418350969',
                //开团成功
                'OPEN_PINK_SUCCESS' => 'OPENTM410867947',
                //提现成功通知
                'USER_EXTRACT' => 'OPENTM405876306',
                //提现失败通知
                'USER_EXTRACT_FAIL' => 'OPENTM403167119',
                //提醒付款通知
                'ORDER_PAY_FALSE' => 'OPENTM408199008',
                //服务进度提醒
                'ADMIN_NOTICE' => 'OPENTM415269411',
                //卡密发货提醒
                'KAMI_DELIVER_GOODS_CODE' => 'OPENTM414876266',
            ],
        ],
        //订阅消息
        'subscribe' => [
            'template_id' => [
                //绑定推广关系
                'BIND_SPREAD_UID' => 3801,
                //订单支付成功
                'ORDER_PAY_SUCCESS' => 1927,
                //订单发货提醒(快递)
                'ORDER_DELIVER_SUCCESS' => 1458,
                //订单发货提醒(送货)
                'ORDER_POSTAGE_SUCCESS' => 1128,
                //确认收货通知
                'ORDER_TAKE' => 1481,
                //退款通知
                'ORDER_REFUND' => 1451,
                //充值成功
                'RECHARGE_SUCCESS' => 755,
                //积分到账提醒
                'INTEGRAL_ACCOUT' => 335,
                //佣金到账
                'ORDER_BROKERAGE' => 14403,
                //砍价成功
                'BARGAIN_SUCCESS' => 2727,
                //拼团成功
                'PINK_TRUE' => 3098,
                //拼团状态通知
                'PINK_STATUS' => 3353,
                //提现成功通知
                'USER_EXTRACT' => 1470,
            ],
        ],
    ]
];
