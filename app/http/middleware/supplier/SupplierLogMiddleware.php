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
use app\jobs\system\AdminLogJob;
use crmeb\interfaces\MiddlewareInterface;

/**
 * 操作日志记录
 * Class SupplierLogMiddleware
 * @package app\http\middleware\supplier
 */
class SupplierLogMiddleware implements MiddlewareInterface
{

    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $module = app('http')->getName();
        $rule = trim(strtolower($request->rule()->getRule()));
        //记录后台日志
        AdminLogJob::dispatch([$request->supplierId(), $request->supplierInfo()['supplier_name'], $module, $rule, $request->ip(), 'supplier']);

        return $next($request);
    }
}
