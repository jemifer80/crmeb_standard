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

namespace app\services\product\product;


use app\dao\product\product\StoreProductRelationDao;
use app\services\BaseServices;
use app\services\product\category\StoreCategoryServices;
use crmeb\services\CacheService;

/**
 * 商品关联关系
 * Class StoreProductRelationServices
 * @package app\services\product\product
 * @mixin StoreProductRelationDao
 */
class StoreProductRelationServices extends BaseServices
{

	/**
 	* 1：分类2：品牌3：商品标签4：用户标签5：保障服务6：商品参数
	* @var string[]
	*/
	protected $type = [
		1 => '商品分类',
		2 => '商品品牌',
		3 => '商品标签',
		4 => '用户标签',
		5 => '保障服务',
		6 => '商品参数'
	];

	/**
 	* 缓存key
	* @var string[]
	*/
	protected $typeKey = [
		1 => 'cate',
		2 => 'brand',
		3 => 'product_label',
		4 => 'user_label',
		5 => 'ensure',
		6 => 'specs'
	];

	/**
	* @param StoreProductRelationDao $dao
	*/
    public function __construct(StoreProductRelationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
 	* 保存商品关联关系
	* @param int $id
	* @param array $relation_id
	* @param int $type
	* @param int $is_show
	* @return bool
	*/
    public function saveRelation(int $id, array $relation_id, int $type = 1, int $is_show = 1)
    {
        $cateData = [];
		if ($relation_id) {
			$time = time();
			if ($type == 1) {//分类
				/** @var StoreCategoryServices $storeCategoryServices */
				$storeCategoryServices = app()->make(StoreCategoryServices::class);
				$cateGory = $storeCategoryServices->getColumn([['id', 'IN', $relation_id]], 'id,pid', 'id');
				foreach ($relation_id as $cid) {
					if ($cid && isset($cateGory[$cid]['pid'])) {
						$cateData[] = ['type' => $type, 'product_id' => $id, 'relation_id' => $cid, 'relation_pid' => $cateGory[$cid]['pid'], 'status' => $is_show, 'add_time' => $time];
					}
				}
			} else {
				foreach ($relation_id as $cid) {
					$cateData[] = ['type' => $type, 'product_id' => $id, 'relation_id' => $cid, 'status' => 1, 'add_time' => $time];
				}
			}
		}
        $this->change($id, $cateData, $type);
        return true;
    }

    /**
 	* 商品添加商品关联
	* @param int $id
	* @param array $cateData
	* @param int $type
	* @return bool
	*/
    public function change(int $id, array $cateData, int $type = 1)
    {
        $this->dao->delete(['product_id' => $id, 'type' => $type]);
        if ($cateData) $this->dao->saveAll($cateData);
		$this->setProductRelationCache($id, $cateData, $type);
		return true;
    }

	/**
 	* 批量设置关联状态
	* @param array $ids
	* @param int $is_show
	* @param int $type
	* @return bool
	*/
	public function setShow(array $ids, int $is_show = 1, int $type = 1)
	{
		$this->dao->setShow($ids, $is_show, $type);
		return true;
	}

	/**
 	* 设置商品关联缓存
	* @param int $product_id
	* @param array $data
	* @param int $type
	* @return array
	*/
	public function setProductRelationCache(int $product_id, array $data, int $type = 1)
	{
		$key ='cache_product_relation_' . ($this->typeKey[$type] ?? '') . '_' . $product_id;
		if ($type == 1) {
			$cacheData = $data;
		} else {
			$cacheData = array_column($data, 'relation_id');
		}
		$this->dao->cacheHander()->delete($key);
        $this->dao->cacheTag()->set($key, $cacheData);
		return $cacheData;
	}

	/**
 	* 更新缓存
	* @param int $product_id
	* @param int $type
	* @param string $key
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
	public function updateProductRelationCache(int $product_id, int $type = 1)
	{
		$data = $this->dao->getList(['product_id' => $product_id, 'type' => $type]);
		$cacheData = [];
		if ($data) {
			$cacheData = $this->setProductRelationCache($product_id, $data, $type);
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
	public function getProductRelationCache(int $product_id, array $type = [], bool $isCache = false)
	{
		if (!$type) {
			$typeArr = [1, 2, 3, 4, 5, 6];
		} else {
			$typeArr = $type;
		}
		$data = [];
		$typeKey = $this->typeKey;
		$key = 'cache_product_relation_';
		foreach ($typeArr as $value) {
			$key .= ($typeKey[$value] ?? '') . '_' . $product_id;
			$relation = $this->dao->cacheHander()->get($key);
			if (!$relation || $isCache) {
				$relation = $this->updateProductRelationCache($product_id, (int)$value, $key);
			}
			$data[$value] = $relation;
		}
		return $data;
	}


}
