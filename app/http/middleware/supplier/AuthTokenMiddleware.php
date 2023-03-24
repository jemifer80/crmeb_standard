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

namespace app\http\middleware\supplier;


use app\Request;
use app\services\supplier\LoginServices;
use crmeb\interfaces\MiddlewareInterface;
use think\facade\Config;

/**
 * Class AuthTokenMiddleware
 * @package app\http\middleware\supplier
 */
class AuthTokenMiddleware implements MiddlewareInterface
{

    /**
     * @param Request $request
     * @param \Closure $next
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handle(Request $request, \Closure $next)
    {
        $token = trim(ltrim($request->header(Config::get('cookie.token_name', 'Authori-zation')), 'Bearer'));
        /** @var LoginServices $services */
        $services = app()->make(LoginServices::class);
        $outInfo = $services->parseToken($token);

        Request::macro('supplierId', function () use (&$outInfo) {
            return (int)$outInfo['id'];
        });

        Request::macro('supplierInfo', function () use (&$outInfo) {
            return $outInfo;
        });

        return $next($request);
    }
}
