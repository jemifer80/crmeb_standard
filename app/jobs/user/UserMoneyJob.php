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



use app\services\user\UserMoneyServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

/**
 * 用户余额
 * Class UserMoneyJob
 * @package app\jobs
 */
class UserMoneyJob extends BaseJobs
{
    use QueueTrait;

	/**
 	* 赠送新人礼余额
	* @param $uid
	* @return bool
	 */
	public function newcomerGiveMoney($uid)
	{
		try {
			/** @var UserMoneyServices $userMoneyServices */
            $userMoneyServices = app()->make(UserMoneyServices::class);
			$userMoneyServices->newcomerGiveMoney((int)$uid);
        } catch (\Throwable $e) {

            response_log_write([
                'message' => '赠送新人礼余额失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
	}

	/**
 	* 会员卡激活赠送余额
	* @param $uid
	* @return bool
	 */
	public function levelGiveMoney($uid)
	{
		try {
			/** @var UserMoneyServices $userMoneyServices */
            $userMoneyServices = app()->make(UserMoneyServices::class);
			$userMoneyServices->levelGiveMoney((int)$uid);
        } catch (\Throwable $e) {

            response_log_write([
                'message' => '会员卡激活赠送余额失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
	}
}
