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

namespace app\validate\out;


use think\Validate;

class LoginValidate extends Validate
{
    protected $regex = ['account' => '/^[a-zA-Z0-9]{4,30}$/'];
    /**
     * @var string[]
     */
    protected $rule = [
        'appid' => 'require|account',
        'appsecret' => 'require',
    ];

    /**
     * @var string[]
     */
    protected $message = [
        'appid.require' => '请填写账号',
        'appid.account' => '请填写正确的账号',
        'appsecret.regex' => '请填写密码',
    ];
}
