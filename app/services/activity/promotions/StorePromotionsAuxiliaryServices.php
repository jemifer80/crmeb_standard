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

namespace app\services\activity\promotions;


use app\dao\activity\promotions\StorePromotionsAuxiliaryDao;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\BaseServices;

/**
 * 优惠活动辅助表
 * Class StorePromotionsAuxiliaryServices
 * @package app\services\activity\promotions
 * @mixin StorePromotionsAuxiliaryDao
 */
class StorePromotionsAuxiliaryServices extends BaseServices
{

    /**
     * @param StorePromotionsAuxiliaryDao $dao
     */
    public function __construct(StorePromotionsAuxiliaryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
    * 优惠活动关联保存
    * @param int $promotionsId
    * @param int $type
    * @param array $promotionsAuxiliaryData
    * @param array $couponData
    * @param array $giveProductData
    * @return bool
     */
    public function savePromotionsRelation(int $promotionsId, int $type, array $promotionsAuxiliaryData, array $couponData = [], array $giveProductData = [], bool $isAttr = true)
    {
        $this->dao->delete(['promotions_id' => $promotionsId]);
        if ($promotionsAuxiliaryData) $this->savePromotionsProducts($promotionsId, $type, $promotionsAuxiliaryData, $isAttr);
        if ($couponData) $this->savePromotionsGiveCoupon($promotionsId, $couponData);
        if ($giveProductData) $this->savePromotionsGiveProducts($promotionsId, $giveProductData);
        return true;
    }

    /**
     * 设置活动关联商品
     * @param int $promotionsId
     * @param int $type
     * @param array $productIds
     * @return bool
     */
    public function savePromotionsProducts(int $promotionsId, int $type, array $promotionsAuxiliaryData, bool $isAttr = true)
    {
        if ($promotionsAuxiliaryData) {
            $data = [];
			$unitData = ['type' => 1, 'promotions_id' => $promotionsId ,'product_partake_type' => $type];
			switch ($type) {
				case 1://所有商品
					$data[] = $unitData;
					break;
				case 2:
				case 3:
					if ($isAttr) {
						$productIds = array_column($promotionsAuxiliaryData, 'product_id');
						$promotionsAuxiliaryData = array_combine($productIds, $promotionsAuxiliaryData);
						/** @var StoreProductAttrValueServices $skuValueServices */
						$skuValueServices = app()->make(StoreProductAttrValueServices::class);
						foreach ($productIds as $productId) {
							$unique = $promotionsAuxiliaryData[$productId]['unique'] ?? [];
							$skuCount = $skuValueServices->count(['product_id' => $productId, 'type' => 0]);
							$unitData['product_id'] = $productId;
							$unitData['is_all'] = count($unique) >= $skuCount ? 1 : 0;
							$unitData['unique'] = implode(',', $unique);
							$data[] = $unitData;
						}
					} else {
						$productIds = $promotionsAuxiliaryData['product_id'] ?? [];
						foreach ($productIds as $productId) {
							$unitData['product_id'] = $productId;
							$data[] = $unitData;
						}
					}
					break;
				case 4://品牌
					$brandIds = $promotionsAuxiliaryData['brand_id'] ?? [];
					if ($brandIds) {
						foreach ($brandIds as $id) {
							$unitData['brand_id'] = $id;
							$data[] = $unitData;
						}
					}
					break;
				case 5://标签
					$storeLabelIds = $promotionsAuxiliaryData['store_label_id'] ?? [];
					if ($storeLabelIds) {
						foreach ($storeLabelIds as $id) {
							$unitData['store_label_id'] = $id;
							$data[] = $unitData;
						}
					}
					break;
			}
            if ($data) $this->dao->saveAll($data);
			$this->setPromotionsAuxiliaryCache($promotionsId, $data);
        }
        return true;
    }

    /**
    * 设置活动关联赠送优惠券
    * @param int $promotionsId
    * @param array $couponData
    * @return bool
     */
    public function savePromotionsGiveCoupon(int $promotionsId, array $couponData)
    {
        if ($couponData) {
            $data = [];
            $couponIds = array_column($couponData, 'give_coupon_id');
            $couponData = array_combine($couponIds, $couponData);
            foreach ($couponIds as $couponId) {
                $data[] = [
                    'type' => 2,
                    'promotions_id' => $promotionsId,
                    'coupon_id' => $couponId,
                    'limit_num' => $couponData[$couponId]['give_coupon_num'] ?? 0,
                    'surplus_num' => $couponData[$couponId]['give_coupon_num'] ?? 0
                ];
            }
            $this->dao->saveAll($data);
        }
        return true;
    }

    /**
     * 设置活动关联赠送商品
     * @param int $promotionsId
     * @param array $giveProductData
     * @return bool
     */
    public function savePromotionsGiveProducts(int $promotionsId, array $giveProductData)
    {
        if ($giveProductData) {
            $data = [];
            foreach ($giveProductData as $product) {
                $data[] = [
                    'type' => 3,
                    'promotions_id' => $promotionsId,
                    'product_id' => $product['give_product_id'],
                    'limit_num' => $product['give_product_num'] ?? 0,
                    'surplus_num' => $product['give_product_num'] ?? 0,
                    'unique' => $product['unique'] ?? ''
                ];
            }
            $this->dao->saveAll($data);
        }
        return true;
    }


    /**
     * 优惠活动关联赠品限量处理
     * @param array $promotions_id
     * @param int $type
     * @param int $id
     * @param bool $is_dec
     * @param string $unique
     * @param int $num
     * @return bool
     */
    public function updateLimit(array $promotions_id, int $type, int $id, bool $is_dec = true, string $unique = '', int $num = 1)
    {
        if (!$promotions_id) return true;
        $where = ['promotions_id' => $promotions_id, 'type' => $type];
        if ($type == 2) {
            $where['coupon_id'] = $id;
        } else {
            $where['product_id'] = $id;
            $where['unique'] = $unique;
        }
        $info = $this->dao->get($where);
        if ($info) {
            if ($is_dec) {
                if ($info['surplus_num'] < $num) {
                    $surplus_num = 0;
                } else {
                    $surplus_num = bcsub((string)$info['surplus_num'], (string)$num, 0);
                }
            } else {
                $surplus_num = bcadd((string)$info['surplus_num'], (string)$num, 0);
            }

            $this->dao->update($info['id'], ['surplus_num' => $surplus_num]);
        }
        return true;
    }

	/**
 	* 设置优惠活动关联缓存
	* @param int $promotions_id
	* @param array $data
	* @param int $product_partake_type
	* @return array
	*/
	public function setPromotionsAuxiliaryCache(int $promotions_id, array $data, int $product_partake_type = 1)
	{
		$key ='cache_promotions_auxiliary_' . $promotions_id;
		$cacheData = [];
		if ($data) {
			switch ($product_partake_type) {
				case 1://所有商品
					break;
				case 2:
				case 3:
					$cacheData = array_unique(array_column($data, 'product_id'));
					break;
				case 4://品牌
					$cacheData = array_unique(array_column($data, 'brand_id'));
					break;
				case 5://标签
					$cacheData = array_unique(array_column($data, 'store_label_id'));
					break;
			}
		}

		$this->dao->cacheHander()->delete($key);
        $this->dao->cacheTag()->set($key, $cacheData);
		return $cacheData;
	}

	/**
 	* 更新缓存
	* @param int $promotions_id
	* @param string $key
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
	public function updatePromotionsAuxiliaryCache(int $promotions_id)
	{
		$data = $this->dao->getList(['promotions_id' => $promotions_id, 'type' => 1]);
		$cacheData = [];
		if ($data) {
			$product_partake_type = $data[0]['product_partake_type'] ?? 0;
			$cacheData = $this->setPromotionsAuxiliaryCache($promotions_id, $data, $product_partake_type);
		}
		return $cacheData;
	}

	/**
 	* 获取商品关联缓存
	* @param int $product_id
	* @param array $type
	* @param bool $isCache
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
	public function getPromotionsAuxiliaryCache(int $promotions_id, bool $isCache = false)
	{
		$key = 'cache_promotions_auxiliary_' . $promotions_id;
		$data = $this->dao->cacheHander()->get($key);
		if (!$data || $isCache) {
			$data = $this->updatePromotionsAuxiliaryCache($promotions_id);
		}
		return $data;
	}
}
