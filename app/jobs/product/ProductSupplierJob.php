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


use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\combination\StoreCombinationServices;
use app\services\activity\integral\StoreIntegralServices;
use app\services\activity\seckill\StoreSeckillServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 商品供应商修改
 * Class ProductSupplierJob
 * @package app\jobs\product
 */
class ProductSupplierJob extends BaseJobs
{
    use QueueTrait;

	/**
	 * 修改商品、活动商品供应商ID
	 * @param $product_id
	 * @param $supplier_id
	 * @return bool
	 */
    public function updateSupplierId($product_id, $supplier_id = 0)
    {
		$supplier_id = (int)$supplier_id;
		$product_id = (int)$product_id;
		if ($product_id && $supplier_id) {
			try {
				/** @var StoreSeckillServices $seckillServices */
				$seckillServices = app()->make(StoreSeckillServices::class);
				$seckillServices->update(['product_id' => $product_id], ['type' => 2, 'relation_id' => $supplier_id]);
				/** @var StoreCombinationServices $pinkServices */
				$pinkServices = app()->make(StoreCombinationServices::class);
				$pinkServices->update(['product_id' => $product_id], ['type' => 2, 'relation_id' => $supplier_id]);
				/** @var StoreBargainServices $bargainServices */
				$bargainServices = app()->make(StoreBargainServices::class);
				$bargainServices->update(['product_id' => $product_id], ['type' => 2, 'relation_id' => $supplier_id]);
				/** @var StoreIntegralServices $integralServices */
				$integralServices = app()->make(StoreIntegralServices::class);
				$integralServices->update(['product_id' => $product_id], ['type' => 2, 'relation_id' => $supplier_id]);
			} catch (\Throwable $e) {
                response_log_write([
                    'message' => '修改商品供应商ID发生错误,错误原因:' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
			}
		}
		return true;
    }

}
