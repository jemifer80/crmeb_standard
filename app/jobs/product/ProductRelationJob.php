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


use app\services\product\product\StoreProductRelationServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 商品关联关系
 * Class ProductRelationJob
 * @package app\jobs\product
 */
class ProductRelationJob extends BaseJobs
{
    use QueueTrait;

    /**
     * @param int $id
     * @param array $relation_id
     * @param int $type
     * @param int $is_show
     * @return bool
     */
    public function doJob(int $id, array $relation_id, int $type = 1, int $is_show = 1)
    {
        try {
            /** @var StoreProductRelationServices $storeProductRelationServices */
            $storeProductRelationServices = app()->make(StoreProductRelationServices::class);
            //商品关联
            $storeProductRelationServices->saveRelation($id, $relation_id, $type, $is_show);
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '写入商品关联[type：' . $type . ']发生错误,错误原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }

}
