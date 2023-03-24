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

namespace app\jobs\product;


use app\services\product\product\StoreProductLogServices;
use app\services\product\product\StoreVisitServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 商品记录
 * Class ProductLogJob
 * @package app\jobs
 */
class ProductLogJob extends BaseJobs
{
    use QueueTrait;

    /**
     * @return mixed
     */
    public static function queueName()
    {
        return 'CRMEB_PRO_LOG';
    }

    /**
     * @param $type 'visit','cart','order','pay','collect','refund'
     * @param $data
     * @return bool
     */
    public function doJob($type, $data, $productType = 'product')
    {
        try {
            /** @var StoreProductLogServices $productLogServices */
            $productLogServices = app()->make(StoreProductLogServices::class);
            $productLogServices->createLog($type, $data);
            if ($type == 'visit') {
                /** @var StoreVisitServices $storeVisit */
                $storeVisit = app()->make(StoreVisitServices::class);
                $storeVisit->setView($data['uid'] ?? 0, $data['id'] ?? 0, $productType, $data['product_id'] ?? [], 'view');
            }
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '写入商品记录发生错误,错误原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }

}
