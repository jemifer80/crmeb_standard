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

namespace app\validate\admin\setting;


use think\Validate;

/**
 * Class SystemConfigValidata
 * @package app\validate\admin\setting
 */
class SystemConfigValidata extends Validate
{

    protected $regex = [
			'float_two' => '/^[0-9]+(.[0-9]{1,2})?$/',
			'tel' => '/^((\d{3,4}-)?[0-9]{7,8}$)|(^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])[0-9]{8}$)$/'
		];
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'site_url' => 'url',
        'store_brokerage_ratio' => 'float|egt:0|elt:100|regex:float_two',
        'store_brokerage_two' => 'float|egt:0|elt:100|regex:float_two',
        'user_extract_min_price' => 'float|gt:0',
        'extract_time' => 'number|between:0,180',
        'store_stock' => 'number',
        'store_brokerage_price' => 'float|egt:0',
        'integral_ratio' => 'float|egt:0|elt:1000|regex:float_two',
        'integral_max_num' => 'number|egt:0',
        'order_give_integral' => 'float|egt:0|elt:1000',
        'order_cancel_time' => 'float',
        'order_activity_time' => 'float',
        'order_bargain_time' => 'float',
        'order_seckill_time' => 'float',
        'order_pink_time' => 'float',
        'system_delivery_time' => 'number',
        'system_comment_time' => 'number',
        'store_free_postage' => 'float',
        'integral_rule_number' => 'number|gt:0',
        'express_rule_number' => 'number|gt:0',
        'sign_rule_number' => 'number|gt:0',
        'offline_rule_number' => 'number|gt:0',
        'order_give_exp' => 'number|egt:0',
        'sign_give_exp' => 'number|egt:0',
        'invite_user_exp' => 'number|egt:0',
        'config_export_to_name' => 'chs|length:2,10',
        'config_export_to_tel' => 'mobile|number',
        'config_export_to_address' => 'chsAlphaNum|length:10,100',
        'config_export_siid' => 'alphaNum|length:10,50',
        'service_feedback' => 'length:10,90',
        'thumb_big_height' => 'number|egt:0',
        'thumb_big_width' => 'number|egt:0',
        'thumb_mid_height' => 'number|egt:0',
        'thumb_mid_width' => 'number|egt:0',
        'thumb_small_height' => 'number|egt:0',
        'thumb_small_width' => 'number|egt:0',
        'watermark_opacity' => 'number|between:0,100',
        'watermark_text' => 'chsAlphaNum|length:1,10',
        'watermark_text_size' => 'number|egt:0',
        'watermark_x' => 'integer',
        'watermark_y' => 'integer',
        'store_cashier_order_rate' => 'float|egt:0|elt:100',
        'store_recharge_order_rate' => 'float|egt:0|elt:100',
        'store_self_order_rate' => 'float|egt:0|elt:100',
        'store_svip_order_rate' => 'float|egt:0|elt:100',
        'store_writeoff_order_rate' => 'float|egt:0|elt:100',
        'store_extract_max_price' => 'float',
        'store_extract_min_price' => 'float|egt:1',
        'site_phone' => 'regex:tel',
        'contact_number' => 'regex:tel',
        'bast_number' => 'number|gt:0',
        'first_number' => 'number|gt:0',
        'withdraw_fee' => 'float|egt:0|elt:100|regex:float_two',
        'store_user_min_recharge' => 'float|egt:0.01|elt:999999999|regex:float_two',
        'newcomer_limit_time' => 'number',
        'register_give_integral' => 'number',
        'register_give_money' => 'regex:float_two',
        'first_order_discount' => 'float|egt:0|elt:100|regex:float_two',
        'first_order_discount_limit' => 'number',
		'level_give_integral' => 'number',
		'level_give_money' => 'regex:float_two'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'site_url.url' => '请输入有效的网址',
        'store_brokerage_ratio.float' => '一级返佣比例必须为数字',
        'store_brokerage_ratio.regex' => '一级返佣比例最多两位小数',
        'store_brokerage_ratio.egt' => '一级返佣比例填写范围在0-100之间',
        'store_brokerage_ratio.elt' => '一级返佣比例填写范围在0-100之间',
        'store_brokerage_two.float' => '二级返佣比例必须为数字',
        'store_brokerage_two.regex' => '二级返佣比例最多两位小数',
        'store_brokerage_two.egt' => '二级返佣比例填写范围在0-100之间',
        'store_brokerage_two.elt' => '二级返佣比例填写范围在0-100之间',
        'store_stock.number' => '警戒库存必须为整数',
        'store_brokerage_two.between' => '二级返佣比例填写范围在0-100之间',
        'user_extract_min_price.float' => '提现最小金额只能为数字',
        'user_extract_min_price.gt' => '提现最小金额必须大于0',
        'extract_time.number' => '佣金冻结时间范围在0-180之间',
        'extract_time.between' => '佣金冻结时间范围在0-180之间',
        'store_brokerage_price.float' => '满额分销金额金额必须为数字',
        'store_brokerage_price.gt' => '满额分销金额金额必须大于0',
        'integral_ratio.float' => '积分抵用比例必须为数字',
        'integral_ratio.regex' => '积分抵用比例最多两位小数',
        'integral_ratio.egt' => '积分抵用比例必须在0-1000之间',
        'integral_ratio.elt' => '积分抵用比例必须在0-1000之间',
        'integral_max_num.number' => '积分抵用上限必须为整数',
        'integral_max_num.egt' => '积分抵用上限必须大于等于0',
        'order_give_integral.float' => '下单赠送积分比例必须为数字',
        'order_give_integral.egt' => '下单赠送积分比例必须在0-1000之间',
        'order_give_integral.elt' => '下单赠送积分比例必须在0-1000之间',
        'order_cancel_time.float' => '普通商品未支付取消订单时间必须为数字',
        'order_activity_time.float' => '活动商品未支付取消订单时间必须为数字',
        'order_bargain_time.float' => '砍价商品未支付取消订单时间必须为数字',
        'order_pink_time.float' => '拼团商品未支付取消订单时间必须为数字',
        'system_delivery_time.number' => '订单发货后自动收货时间必须为整数',
        'system_comment_time.number' => '订单收货后自动默认好评时间必须为整数',
        'store_free_postage.float' => '满额包邮金额必须为数字',
        'integral_rule_number.number' => '积分倍数必须大于0',
        'express_rule_number.number' => '折扣数必须大于0',
        'sign_rule_number.number' => '积分倍数必须大于0',
        'offline_rule_number.number' => '折扣数必须大于0',
        'order_give_exp.number' => '下单赠送经验比例必须为整数',
        'order_give_exp.egt' => '下单赠送经验比例必须大于0',
        'sign_give_exp.number' => '签到赠送经验必须为整数',
        'sign_give_exp.egt' => '签到赠送经验必须大于0',
        'invite_user_exp.number' => '邀请新用户赠送经验必须为整数',
        'invite_user_exp.egt' => '邀请新用户赠送经验必须大于0',
        'config_export_to_name.chs' => '发货人姓名必须为汉字',
        'config_export_to_name.length' => '发货人姓名长度在2-10位',
        'config_export_to_tel.number' => '发货人电话必须为整数',
        'config_export_to_tel.mobile' => '发货人电话请填写有效的手机号',
        'config_export_to_address.chsAlphaNum' => '发货人地址只能是汉字、字母、数字',
        'config_export_to_address.length' => '发货人地址长度为10-100位',
        'config_export_siid.alphaNum' => '电子面单打印机编号必须为数字、字母',
        'config_export_siid.length' => '电子面单打印机编号长度为10-50位',
        'service_feedback.length' => '客服反馈长度位10-90位',
        'thumb_big_height.number' => '缩略图大图尺寸（高）必须为整数',
        'thumb_big_height.egt' => '缩略图大图尺寸（高）必须大于等于0',
        'thumb_big_width.number' => '缩略图大图尺寸（宽）必须为整数',
        'thumb_big_width.egt' => '缩略图大图尺寸（宽）必须大于等于0',
        'thumb_mid_height.number' => '缩略图中图尺寸（高）必须为整数',
        'thumb_mid_height.egt' => '缩略图中图尺寸（高）必须大于等于0',
        'thumb_mid_width.number' => '缩略图中图尺寸（宽）必须为整数',
        'thumb_mid_width.egt' => '缩略图中图尺寸（宽）必须大于等于0',
        'thumb_small_height.number' => '缩略图小图尺寸（高）必须为整数',
        'thumb_small_height.egt' => '缩略图小图尺寸（高）必须大于等于0',
        'thumb_small_width.number' => '缩略图小图尺寸（宽）必须为整数',
        'thumb_small_width.egt' => '缩略图小图尺寸（宽）必须大于等于0',
        'watermark_text.chsAlphaNum' => '水印文字只能是汉字、字母、数字',
        'watermark_text.length' => '水印文字长度为1-10位',
        'watermark_text_size.number' => '水印文字大小必须为整数',
        'watermark_text_size.egt' => '水印文字大小必须大于等于0',
        'watermark_x.integer' => '水印横坐标偏移量必须为整数',
        'watermark_y.integer' => '水印纵坐标偏移量必须为整数',
        'store_cashier_order_rate.float' => '收银订单费率必须为数字',
        'store_cashier_order_rate.egt' => '收银订单费率为0-100数字',
        'store_cashier_order_rate.elt' => '收银订单费率为0-100数字',
        'store_recharge_order_rate.float' => '充值订单返点必须为数字',
        'store_recharge_order_rate.egt' => '充值订单返点为0-100数字',
        'store_recharge_order_rate.elt' => '充值订单返点为0-100数字',
        'store_self_order_rate.float' => '分配订单费率必须为数字',
        'store_self_order_rate.egt' => '分配订单费率为0-100数字',
        'store_self_order_rate.elt' => '分配订单费率为0-100数字',
        'store_svip_order_rate.float' => '购买付费会员返点必须为数字',
        'store_svip_order_rate.egt' => '购买付费会员返点为0-100数字',
        'store_svip_order_rate.elt' => '购买付费会员返点为0-100数字',
        'store_writeoff_order_rate.float' => '核销订单费率必须为数字',
        'store_writeoff_order_rate.egt' => '核销订单费率为0-100数字',
        'store_writeoff_order_rate.elt' => '核销订单费率为0-100数字',
        'store_extract_max_price.float' => '门店提现最高金额必须为数字',
        'store_extract_min_price.float' => '门店提现最低金额必须为数字',
        'store_extract_min_price.egt' => '门店提现最低金额为1元',
        'site_phone.regex' => '请填写有效的联系电话',
        'contact_number.regex' => '请填写有效的联系电话',
        'bast_number.number' => '精品推荐个数必须为整数',
        'bast_number.gt' => '精品推荐个数必须大于0',
        'first_number.number' => '首发新品个数必须为整数',
        'first_number.gt' => '首发新品个数必须大于0',
        'withdraw_fee.float' => '提现手续费必须为数字',
        'withdraw_fee.egt' => '提现手续费为0-100数字',
        'withdraw_fee.elt' => '提现手续费为0-100数字',
        'withdraw_fee.regex' => '提现手续费最多两位小数',
        'store_user_min_recharge.float' => '最低充值金额必须为数字',
        'store_user_min_recharge.egt' => '最低充值金额为0.01',
        'store_user_min_recharge.elt' => '最低充值金额超过上限',
        'store_user_min_recharge.regex' => '最低充值金额最多两位小数',
        'newcomer_limit_time.number' => '新人礼专享限时时间必须为整数',
		'register_give_integral.number' => '用户注册赠送积分数量必须为整数',
		'register_give_money.regex' => '用户注册赠送余额数量最多两位小数',
		'first_order_discount_limit.number' => '首单优惠折扣上限必须为整数',
        'withdraw_fee.float' => '首单优惠折扣必须为数字',
        'withdraw_fee.egt' => '首单优惠折扣为0-100数字',
        'withdraw_fee.elt' => '首单优惠折扣为0-100数字',
        'withdraw_fee.regex' => '首单优惠折扣最多两位小数',
		'level_give_integral.regex' => '会员卡激活赠送积分数量必须为整数',
		'level_give_money.regex' => '会员卡激活赠送金额最多两位小数',
    ];

    protected $scene = [

    ];
}
