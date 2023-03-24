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
namespace app\controller\api\v1\diy;


use app\Request;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\activity\newcomer\StoreNewcomerServices;
use app\services\activity\video\VideoServices;
use app\services\diy\DiyServices;
use app\services\user\level\SystemUserLevelServices;
use app\services\user\UserServices;

/**
 * Class Diy
 * @package app\controller\api\v1\diy
 */
class Diy
{
    protected $services;

    public function __construct(DiyServices $services)
    {
        $this->services = $services;
    }

	/**
 	* 获取diy用户数据
	* @param Request $request
	* @param UserServices $userServices
	* @return mixed
	 */
	public function userInfo(Request $request, UserServices $userServices)
	{
		$uid = (int)$request->uid();
		$userInfo = [];
		if ($uid) {
			$userInfo = $userServices->getUserInfo($uid, 'uid,nickname,phone,avatar,level,integral,now_money,exp,is_money_level,bar_code');
			if ($userInfo) {
				$userInfo = $userInfo->toArray();
				/** @var StoreCouponUserServices $storeCoupon */
        		$storeCoupon = app()->make(StoreCouponUserServices::class);
				$userInfo['coupon_num'] = $storeCoupon->getUserValidCouponCount((int)$uid);
				$userInfo['next_exp'] = 0;
				$userInfo['vip_name'] = '';
				if ($userInfo['level']) {
					/** @var SystemUserLevelServices $systemUserLevel */
					$systemUserLevel = app()->make(SystemUserLevelServices::class);
					$levelList = $systemUserLevel->getList(['is_del' => 0, 'is_show' => 1], 'id,name,exp_num');
					$i = 0;
					foreach ($levelList as &$level) {
						if ($level['id'] == $userInfo['level']) {
							$userInfo['vip_name'] = $level['name'];
						}
						$level['next_exp_num'] = $levelList[$i + 1]['exp_num'] ?? $level['exp_num'];
						$i++;
					}
					$levelList = array_combine(array_column($levelList,'id'), $levelList);
					$userInfo['next_exp'] = $levelList[$userInfo['level']]['next_exp_num'] ?? 0;
				} else {
					/** @var SystemUserLevelServices $systemUserLevel */
					$systemUserLevel = app()->make(SystemUserLevelServices::class);
					$levelList = $systemUserLevel->getList(['is_del' => 0, 'is_show' => 1], 'id,name,exp_num');
					$userInfo['next_exp'] = $levelList[0]['exp_num'] ?? 0;
				}
			}
		}
		return app('json')->success($userInfo);
	}

	/**
 	* 获取diy短视频
	* @param Request $request
	* @param VideoServices $videoServices
	* @return mixed
	 */
	public function videoList(Request $request, VideoServices $videoServices)
	{
		$uid = (int)$request->uid();
		return app('json')->success($videoServices->getDiyVideoList($uid));
	}

	/**
 	* 获取新人礼商品
	* @param Request $request
	* @param StoreNewcomerServices $newcomerServices
	* @return mixed
	 */
	public function newcomerList(Request $request, StoreNewcomerServices $newcomerServices)
	{
		$where = $request->getMore([
            ['priceOrder', ''],
            ['salesOrder', ''],
        ]);
		$uid = (int)$request->uid();
		return app('json')->success($newcomerServices->getDiyNewcomerList($uid, $where));
	}


}
