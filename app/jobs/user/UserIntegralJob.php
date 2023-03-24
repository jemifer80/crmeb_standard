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

namespace app\jobs\user;


use app\services\user\UserIntegralServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 清除到期积分
 * Class UserIntegralJob
 * @package app\jobs
 */
class UserIntegralJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 执行清除到期积分
     * @param $openids
     * @return bool
     */
    public function doJob($uids)
    {
        if (!$uids || !is_array($uids)) {
            return true;
        }
        try {
            /** @var UserIntegralServices $userIntegralServices */
            $userIntegralServices = app()->make(UserIntegralServices::class);
            $userIntegralServices->doClearExpireIntegral($uids);
        } catch (\Throwable $e) {

            response_log_write([
                'message' => '清除用户到期积分失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }

	/**
 	* 赠送新人礼积分
	* @param $uid
	* @return bool
	 */
	public function newcomerGiveIntegral($uid)
	{
		try {
			/** @var UserIntegralServices $userIntegralServices */
            $userIntegralServices = app()->make(UserIntegralServices::class);
            $userIntegralServices->newcomerGiveIntegral((int)$uid);
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '赠送新人礼积分失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
	}

	/**
 	* 会员卡激活赠送积分
	* @param $uid
	* @return bool
	 */
	public function levelGiveIntegral($uid)
	{
		try {
			/** @var UserIntegralServices $userIntegralServices */
            $userIntegralServices = app()->make(UserIntegralServices::class);
            $userIntegralServices->levelGiveIntegral((int)$uid);
        } catch (\Throwable $e) {

            response_log_write([
                'message' => '会员卡激活赠送积分失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
	}
}
