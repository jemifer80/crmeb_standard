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
declare (strict_types=1);

namespace app\services\pc;


use app\services\BaseServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\services\CacheService;
use crmeb\services\wechat\OfficialAccount;
use think\exception\ValidateException;

class LoginServices extends BaseServices
{
    /**
     * 扫码登陆
     * @param string $key
     * @return array|int[]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function scanLogin(string $key)
    {
        $hasKey = CacheService::has($key);
        if ($hasKey === false) {
            $status = 0;//不存在需要刷新二维码
        } else {
            $keyValue = CacheService::get($key);
            if ($keyValue === 0) {
                $status = 1;//正在扫描中
                /** @var UserServices $user */
                $user = app()->make(UserServices::class);
                $userInfo = $user->get(['uniqid' => $key], ['account', 'uniqid']);
                if ($userInfo) {
                    $tokenInfo = $this->authLogin($userInfo->account);
                    $tokenInfo['status'] = 3;
                    $userInfo->uniqid = '';
                    $userInfo->save();
                    CacheService::delete($key);
                    return $tokenInfo;
                }
            } else {
                $status = 2;//没有扫描
            }
        }
        return ['status' => $status];
    }

    /**
     * 扫码登陆
     * @param string $account
     * @param string|null $password
     * @return array
     */
    public function authLogin(string $account, string $password = null)
    {
        /** @var UserServices $user */
        $user = app()->make(UserServices::class);

        $userInfo = $user->get(['account' => $account]);
        if (!$userInfo) {
            throw new ValidateException('没有此用户');
        }
        if ($password && !password_verify($password, $userInfo->pwd)) {
            throw new ValidateException('账号或密码错误');
        }
        if (!$userInfo->status) {
            throw new ValidateException('您已被禁止登录');
        }
        $token = $this->createToken($userInfo->id, 'api', $userInfo->pwd ?? '');
        $userInfo->update_time = time();
        $userInfo->ip = request()->ip();
        $userInfo->save();
        return [
            'token' => $token['token'],
            'exp_time' => $token['params']['exp'],
            'userInfo' => $userInfo->toArray()
        ];
    }


    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wechatAuth()
    {
        /** @var OfficialAccount $service */
        $service = app()->make(OfficialAccount::class);
        $info = $service->setAccessEnd(OfficialAccount::PC)->userFromCode();
        if (!$info) {
            throw new ValidateException('授权失败');
        }
        $wechatInfo = $info;
        if (!isset($wechatInfo['unionid'])) {
            throw new ValidateException('unionid不存在');
        }
        if (!isset($wechatInfo['nickname'])) {
            $wechatInfo = OfficialAccount::userService()->get($wechatInfo['openid'])->toArray();
            if (!isset($wechatInfo['nickname']))
                throw new ValidateException('授权失败');
            if (isset($wechatInfo['tagid_list']))
                $wechatInfo['tagid_list'] = implode(',', $wechatInfo['tagid_list']);
        } else {
            if (isset($wechatInfo['privilege'])) unset($wechatInfo['privilege']);
            /** @var WechatUserServices $wechatUser */
            $wechatUser = app()->make(WechatUserServices::class);
            if (!$wechatUser->getOne(['openid' => $wechatInfo['openid']])) {
                $wechatInfo['subscribe'] = 0;
            }
        }
        $wechatInfo['user_type'] = 'pc';
        $openid = $wechatInfo['openid'];
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $user = $wechatUserServices->getAuthUserInfo($openid, 'pc');
        $createData = [$openid, $wechatInfo, 0, 'pc', 'pc'];
        if (!$user) {
            $user = $wechatUserServices->wechatOauthAfter($createData);
        } else {
            //更新用户信息
            $wechatUserServices->wechatUpdata([$user['uid'], $wechatInfo]);
        }
        $token = $this->createToken((int)$user->uid, 'api', $user->pwd ?? '');
        return [
            'token' => $token['token'],
            'exp_time' => $token['params']['exp']
        ];
    }
}
