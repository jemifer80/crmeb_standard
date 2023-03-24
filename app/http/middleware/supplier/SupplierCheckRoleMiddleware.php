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
use crmeb\exceptions\AuthException;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\utils\ApiErrorCode;

/**
 * 权限规则验证
 * Class SupplierCheckRoleMiddleware
 * @package app\http\middleware\supplier
 */
class SupplierCheckRoleMiddleware implements MiddlewareInterface
{

    public function handle(Request $request, \Closure $next)
    {
        if (!$request->supplierId() || !$request->supplierInfo())
            throw new AuthException(ApiErrorCode::ERR_ADMINID_VOID);

        if ($request->supplierInfo()['level'] ?? 0) {
            /** @var LoginServices $services */
            $services = app()->make(LoginServices::class);
            $services->verifiAuth($request);
        }

        return $next($request);
    }
}
