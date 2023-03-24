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

namespace crmeb\services;

use crmeb\interfaces\DeliveryInterface;
use crmeb\services\delivery\Delivery;
use crmeb\services\delivery\storage\Dada;
use crmeb\services\delivery\store\Uupt;

/**
 * Class BaseExpress
 * @package crmeb\basic
 */
class DeliverySevices
{
	protected static $delivery = [];

	const DELIVERY_TYPE_DADA = 1;
    const DELIVERY_TYPE_UU = 2;

	/**
	* @param int $type
	* @param bool $is_cache
	* @return Delivery|mixed
	 */
    public static function init(int $type = self::DELIVERY_TYPE_DADA, bool $is_cache = false)
    {
		$type = (int)$type;

		if ($is_cache && isset(self::$delivery['delivery_' . $type]) && self::$delivery['delivery_' . $type]) {
            return self::$delivery['delivery_' . $type];
        }
		$config = [];
        switch ($type) {
            case 1:
                $config = [
                    'app_key' => sys_config('dada_app_key'),
                    'app_secret' => sys_config('dada_app_sercret'),
                    'source_id' => sys_config('dada_source_id'),
                ];
                break;
            case 2:
                $config = [
                    'app_key' => sys_config('uupt_appkey'),
                    'app_id' => sys_config('uupt_app_id'),
                    'open_id' => sys_config('uupt_open_id'),
                ];
                break;
        }
        return self::$delivery['delivery_' . $type] = new Delivery($type, $config);
    }

}
