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

namespace app\jobs\order;


use app\services\order\StoreDeliveryOrderServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 同城配送
 * Class CityDeliveryJob
 * @package app\jobs
 */
class CityDeliveryJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 同步创建、更新达达门店信息
     * @param $id
     * @param $is_new
     * @return bool
     */
    public function syncCityShop($id, $is_new)
    {
        try {
            /** @var StoreDeliveryOrderServices $deliveryOrderServices */
            $deliveryOrderServices = app()->make(StoreDeliveryOrderServices::class);
            $deliveryOrderServices->syncCityShop((int)$id);
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '同步创建同城配送达达门店失败，原因：' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }


}
