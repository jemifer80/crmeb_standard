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

namespace app\listener\system\http;

use think\facade\Log;
use think\Request;
use think\Response;

/**
 * 订单创建事件
 * Class HttpEnd
 * @package app\listener\http
 */
class HttpEnd
{
    public function handle(Response $response):void
    {
		try {
			//业务成功和失败分开存储
			$status = isset($response->getData()["status"]) ? $response->getData()["status"] : 0;
			if ($status == 200) {
				//业务成功日志开关
				if (!config("log.success_log")) return;
				$logType = "success";
			} else {
				//业务失败日志开关
				if (!config("log.fail_log")) return;
				$logType = "fail";
			}
			$request = app()->make(Request::class);
			 //当前用户身份标识
			if ($request->hasMacro('uid')) {
				$uid = $request->uid();
				$type = 'user';
			} elseif ($request->hasMacro('adminId')) {
				$uid = $request->adminId();
				$type = 'admin';
			} elseif ($request->hasMacro('kefuId')) {
				$uid = $request->kefuId();
				$type = 'kefu';
			} elseif ($request->hasMacro('outId')) {
				$uid = $request->outId();
				$type = 'out';
			} elseif ($request->hasMacro('storeId')) {
				$uid = $request->storeId();
				$type = 'store';
			} elseif ($request->hasMacro('cashierId')) {
				$uid = $request->cashierId();
				$type = 'cashier';
			} elseif ($request->hasMacro('supplierId')) {
				$uid = $request->supplierId();
				$type = 'supplier';
			} else {
				$uid = 0;
				$type = '';
			}
			//日志内容
			$log = [
				$uid,                                                                                 //用户ID
				$type,																				//类型
				$request->ip(),                                                                      //客户ip
				ceil(msectime() - (request()->time(true) * 1000)),                                    //耗时（毫秒）
				str_replace("/", "", $request->rootUrl()),                                           //应用
				$request->baseUrl(),                                                                 //路由
				json_encode($request->param(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),     //请求参数
				json_encode($response->getData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),   //响应数据
			];
			Log::write(implode("|", $log), $logType);
		} catch (\Throwable $e) {
			$data = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace'    => $e->getTrace(),
            	'previous' => $e->getPrevious(),
            ];
			Log::error(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		}
    }
}
