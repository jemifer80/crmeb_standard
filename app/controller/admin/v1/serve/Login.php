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

namespace app\controller\admin\v1\serve;


use app\controller\admin\AuthController;
use app\validate\admin\serve\ServeValidata;
use app\Request;
use app\services\message\sms\SmsAdminServices;
use crmeb\services\CacheService;
use app\services\serve\ServeServices;
use think\facade\App;

/**
 * 服务登录
 * Class Login
 * @package app\controller\admin\v1\serve
 */
class Login extends AuthController
{

    public function __construct(App $app, ServeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 发送验证码
     * @param string $phone
     * @return mixed
     */
    public function captcha(string $phone)
    {
        $this->validate(['phone' => $phone], ServeValidata::class, 'phone');
        return $this->success('发送成功', $this->services->user()->code($phone));
    }

    /**
     * 验证验证码
     * @param string $phone
     * @param $code
     * @return mixed
     */
    public function checkCode()
    {
        [$phone, $verify_code] = $this->request->postMore([
            ['phone', ''],
            ['verify_code', ''],
        ], true);
        $this->validate(['phone' => $phone], ServeValidata::class, 'phone');
        return $this->success('success', $this->services->user()->checkCode($phone, $verify_code));
    }

    /**
     * 注册服务
     * @param Request $request
     * @param SmsAdminServices $services
     * @return mixed
     */
    public function register(Request $request, SmsAdminServices $services)
    {
        $data = $request->postMore([
            ['phone', ''],
            ['account', ''],
            ['password', ''],
            ['verify_code', ''],
        ]);

        $data['account'] = $data['phone'];
        $this->validate($data, ServeValidata::class);
        $data['password'] = md5($data['password']);
        $res = $this->services->user()->register($data);
        if ($res) {
            $services->updateSmsConfig($data['account'], md5($data['account'] . md5($data['password'])));
            return $this->success('注册成功');
        } else {
            return $this->fail('注册失败');
        }
    }

    /**
     * 平台登录
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login(SmsAdminServices $services)
    {
        [$account, $password] = $this->request->postMore([
            ['account', ''],
            ['password', '']
        ], true);

        $this->validate(['account' => $account, 'password' => $password], ServeValidata::class, 'login');

        $password = md5($account . md5($password));

        $res = $this->services->user()->login($account, $password);
        if ($res) {
            CacheService::redisHandler()->set('sms_account', $account);
            $services->updateSmsConfig($account, $password);
            return $this->success('登录成功', $res);
        } else {
            return $this->fail('登录失败');
        }
    }
}
