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

namespace app\http\middleware\api;


use app\Request;
use app\services\user\LoginServices;
use crmeb\interfaces\MiddlewareInterface;

/**
 * 全局修改绑定分销关系
 * Class ClientMiddleware
 * @package app\http\middleware\api
 */
class UserSpreadMiddleware implements MiddlewareInterface
{

    public function handle(Request $request, \Closure $next)
    {
        $spread_uid = trim(ltrim($request->param('spread_sid')));
		//登录存在用户信息
		$user = $request->hasMacro('user') ? $request->user() : [];
		//更新绑定关系
		if ($user && $spread_uid) {
			/** @var LoginServices $loginServices */
			$loginServices = app()->make(LoginServices::class);
			$loginServices->updateUserInfo(['spread_uid' => $spread_uid], $user);
		}

        return $next($request);
    }
}
