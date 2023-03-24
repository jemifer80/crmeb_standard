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

namespace app\jobs\activity;


use app\services\activity\coupon\StoreCouponIssueServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 营销：优惠券
 * Class StoreCouponJob
 * @package app\jobs\user
 */
class StoreCouponJob extends BaseJobs
{

    use QueueTrait;

	/**
	* 新人礼赠送优惠券
	* @param $uid
	* @return bool
	 */
	public function newcomerGiveCoupon($uid)
	{
		 try {
            /**@var StoreCouponIssueServices $storeCoupon */
            $storeCoupon = app()->make(StoreCouponIssueServices::class);
            $storeCoupon->newcomerGiveCoupon((int)$uid);
        } catch (\Throwable $e) {
             response_log_write([
                 'message' => '赠送新人礼优惠券失败,失败原因:' . $e->getMessage(),
                 'file' => $e->getFile(),
                 'line' => $e->getLine()
             ]);
        }
        return true;
	}

	/**
	* 会员卡激活赠送优惠券
	* @param $uid
	* @return bool
	 */
	public function levelGiveCoupon($uid)
	{
		 try {
            /**@var StoreCouponIssueServices $storeCoupon */
            $storeCoupon = app()->make(StoreCouponIssueServices::class);
            $storeCoupon->levelGiveCoupon((int)$uid);
        } catch (\Throwable $e) {
             response_log_write([
                 'message' => '会员卡激活赠送优惠券,失败原因:' . $e->getMessage(),
                 'file' => $e->getFile(),
                 'line' => $e->getLine()
             ]);
        }
        return true;
	}

    /**
     * 增加新人券
     * @param $uid
     * @return bool
     */
    public function newUserGiveCoupon($uid)
    {
        try {
            /**@var StoreCouponIssueServices $storeCoupon */
            $storeCoupon = app()->make(StoreCouponIssueServices::class);
            $storeCoupon->userFirstSubGiveCoupon((int)$uid);
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '赠送新人券失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }

}
