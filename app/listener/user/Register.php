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


use app\jobs\agent\AgentJob;
use app\jobs\activity\StoreCouponJob;
use app\jobs\user\UserFriendsJob;
use app\jobs\user\UserIntegralJob;
use app\jobs\user\UserJob;
use app\jobs\user\UserMoneyJob;
use app\jobs\user\UserSpreadJob;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use crmeb\interfaces\ListenerInterface;

/**
 * 注册完成后置事件
 * Class Register
 * @package app\listener\user
 */
class Register implements ListenerInterface
{
    /**
     * 注册完成后置事件
     * @param $event
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handle($event): void
    {
        [$userInfo, $is_new, $spread_uid] = $event;
        $uid = (int)$userInfo['uid'] ?? 0;

        if (isset($userInfo['spread_uid']) && $userInfo['spread_uid']) {
            $spreadUid = (int)$userInfo['spread_uid'];
            if ($is_new) {
                //邀请新用户增加经验
                UserJob::dispatchDo('inviteUserIncExp', [$spreadUid]);
                //增加推广佣金
                UserJob::dispatchDo('addBrokeragePrice', [$uid, $spreadUid]);
                //推广新人 处理自己、上级分销等级升级
                AgentJob::dispatch([$uid]);
            }
            //记录推广绑定关系
            UserSpreadJob::dispatch([$uid, $spreadUid]);
        }
        if ($is_new) {
            //修改用户首单、新人专享状态
            UserJob::dispatchDo('setUserNewcomer', [$uid]);
            //新人礼优惠券
            StoreCouponJob::dispatchDo('NewcomerGiveCoupon', [$uid]);
            //新人礼积分
            UserIntegralJob::dispatchDo('newcomerGiveIntegral', [$uid]);
            //新人礼余额
            UserMoneyJob::dispatchDo('newcomerGiveMoney', [$uid]);
        }
        if (isset($spread_uid) && $spread_uid) {
            //记录好友关系
            UserFriendsJob::dispatch([$uid, $spread_uid]);
            //推送消息
            event('notice.notice', [['spreadUid' => $spread_uid, 'user_type' => $userInfo['user_type'], 'nickname' => $userInfo['nickname']], 'bind_spread_uid']);
        }
    }
}
