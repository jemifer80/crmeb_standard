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

namespace app\controller\api\v1;


use app\Request;
use app\services\order\StoreDeliveryOrderServices;
use \think\facade\Log;

/**
 * 同城配送相关回调
 * Class CityDeliveryController
 * @package app\api\controller\v1
 */
class CityDeliveryController
{

    /**
	* @return bool
	 */
    public function notify(Request $request)
    {
        try {
            $params = $request->param();
			/** @var StoreDeliveryOrderServices $storeDeliveryOrderServices */
			$storeDeliveryOrderServices = app()->make(StoreDeliveryOrderServices::class);
            $storeDeliveryOrderServices->notify($params);
			return true;
        } catch (\Throwable $e) {
            Log::info('同城配送订单回调失败:' . var_export([$e->getMessage(), $e->getFile() . ':' . $e->getLine()], true));
			return false;
        }

    }

}
