<?php

namespace app\jobs\user;

use app\services\user\UserServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class LoginJob extends BaseJobs
{
    use QueueTrait;

    public function doJob($uid,$ip)
    {

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
		$city = $userServices->convertIp($ip);
		if ($city) {
			$userInfo = $userServices->get($uid);
			if ($userInfo->login_city != $city) {
				if($userInfo->login_city != ''){
					event('notice.notice', [['phone' => $userInfo->phone, 'time' => date('Y-m-d H:i:s'), 'city' => $city, 'login_city' => $userInfo->login_city], 'login_city_error']);
				}
				$userInfo->login_city = $city;
				$userInfo->save();
			}
		}
		return true;
    }

}
