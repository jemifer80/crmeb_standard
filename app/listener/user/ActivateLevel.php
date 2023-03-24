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

namespace app\listener\user;

use app\jobs\activity\StoreCouponJob;
use app\jobs\user\UserIntegralJob;
use app\jobs\user\UserMoneyJob;
use crmeb\interfaces\ListenerInterface;


/**
 * 激活用户会员卡事件
 * Class ActivateLevel
 * @package app\listener\user
 */
class ActivateLevel implements ListenerInterface
{
    /**
	* @param $event
	* @return void
	 */
    public function handle($event): void
    {
        [$uid] = $event;

		//会员卡激活赠送积分
		UserIntegralJob::dispatchDo('levelGiveIntegral', [$uid]);
		//会员卡激活赠送余额
		UserMoneyJob::dispatchDo('levelGiveMoney', [$uid]);
		//会员卡激活赠送优惠券
		StoreCouponJob::dispatchDo('levelGiveCoupon', [$uid]);
    }
}
