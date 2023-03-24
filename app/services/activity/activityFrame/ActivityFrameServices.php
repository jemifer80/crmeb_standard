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
declare (strict_types=1);

namespace app\services\activity\activityFrame;

use app\dao\activity\promotions\StorePromotionsDao;
use app\services\activity\promotions\StorePromotionsAuxiliaryServices;
use app\services\BaseServices;
use app\services\product\brand\StoreBrandServices;
use app\services\product\category\StoreCategoryServices;
use app\services\product\label\StoreProductLabelServices;
use app\services\product\product\StoreProductRelationServices;
use app\services\product\product\StoreProductServices;
use crmeb\exceptions\AdminException;
use think\exception\ValidateException;
use \crmeb\traits\OptionTrait;


/**
 * 活动边框
 * Class ActivityFrameServices
 * @package app\services\activity\activityFrame
 * @mixin StorePromotionsDao
 */
class ActivityFrameServices extends BaseServices
{
    use OptionTrait;
    /**
     * 活动类型
     * @var string[]
     */
    protected $promotionsType = [
        1 => '限时折扣',
        2 => '第N件N折',
        3 => '满减满折',
        4 => '满送',
        5 => '活动边框',
        6 => '活动背景',
    ];

	/**
 	* 参与活动商品类型
	* @var string[]
	*/
	protected $productPartakeType = [
		1 => '全部商品',
		2 => '指定商品参与',
		3 => '指定商品不参与',
		4 => '指定品牌参与',
		5 => '指定商品标签参与',
	];


    /**
     * StorePromotionsServices constructor.
     * @param StorePromotionsDao $dao
     */
    public function __construct(StorePromotionsDao $dao)
    {
        $this->dao = $dao;
    }

	/**
 	* 后台获取活动边框列表
	* @param array $where
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
	public function systemPage(array $where)
	{
		[$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', $page, $limit);
		$count = 0;
        if ($list) {
			/** @var StoreProductServices $storeProductServices */
			$storeProductServices = app()->make(StoreProductServices::class);
			/** @var StoreProductRelationServices  $storeProductRelationServices */
			$storeProductRelationServices = app()->make(StoreProductRelationServices::class);
			/** @var StorePromotionsAuxiliaryServices $promotionsAuxiliaryServices */
			$promotionsAuxiliaryServices = app()->make(StorePromotionsAuxiliaryServices::class);
			foreach ($list as &$item) {
				if ($item['status']) {
                    if ($item['start_time'] > time()){
						$item['start_status'] = 0;
                        $item['start_name'] = '未开始';
                    } else if ((int)$item['stop_time'] < time()) {
                        $item['start_name'] = '已结束';
						$item['start_status'] = -1;
                    } else if ((int)$item['stop_time'] > time() && $item['start_time'] < time()) {
                        $item['start_name'] = '进行中';
						$item['start_status'] = 1;
                    }
                } else {
					$item['start_status'] = -1;
					$item['start_name'] = '已结束';
                }
				$item['start_time'] = $item['start_time'] ? date('Y-m-d H:i:s', $item['start_time']) : '';
				$item['stop_time'] = $item['stop_time'] ? date('Y-m-d H:i:s', $item['stop_time']) : '';
				$promotionsAuxiliaryData = $promotionsAuxiliaryServices->getPromotionsAuxiliaryCache($item['id']);
				switch ($item['product_partake_type']) {
					case 1://所有商品
						$item['product_count'] = $storeProductServices->count(['is_show' => 1, 'is_del' => 0]);
						break;
					case 2://选中商品参与
						$product_ids = $promotionsAuxiliaryData;
						$item['product_count'] = $product_ids ? $storeProductServices->count(['is_show' => 1, 'is_del' => 0, 'id' => $product_ids]) : 0;
						break;
					case 3:
						$item['product_count'] = 0;
						break;
					case 4://品牌
						$product_ids = $promotionsAuxiliaryData ? $storeProductRelationServices->getIdsByWhere(['type' => 2, 'relation_id' => $promotionsAuxiliaryData]) : [];
						$item['product_count'] = $product_ids ? $storeProductServices->count(['is_show' => 1, 'is_del' => 0, 'id' => $product_ids]) : 0;
						break;
					case 5://商品标签
						$product_ids = $promotionsAuxiliaryData ? $storeProductRelationServices->getIdsByWhere(['type' => 3, 'relation_id' => $promotionsAuxiliaryData]) : [];
						$item['product_count'] = $product_ids ? $storeProductServices->count(['is_show' => 1, 'is_del' => 0, 'id' => $product_ids]) : 0;
						break;
				}
			}
            $count = $this->dao->count($where);
		}
		return compact('list', 'count');
	}

	/**
 	* 获取活动边框想你去那个
	* @param int $id
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
	public function getInfo(int $id)
	{
		$info = $this->dao->get($id, ['*'], ['products' => function ($query) {
            $query->field('promotions_id,product_id,unique')->with(['productInfo']);
        }, 'brands' => function ($query) {
			$query->field('promotions_id,brand_id')->with(['brandInfo' => function ($q) { $q->field('id,brand_name');}]);
        }, 'productLabels' => function ($query) {
			$query->field('promotions_id,store_label_id')->with(['productLabelInfo' => function ($q) { $q->field('id,label_name');}]);
        }]);
		if (!$info) {
            throw new AdminException('数据不存在');
        }
        $info = $info->toArray();
		$info['start_time'] = $info['start_time'] ? date('Y-m-d H:i:s', $info['start_time']) : '';
		$info['stop_time'] = $info['stop_time'] ? date('Y-m-d H:i:s', $info['stop_time']) : '';
		$products = [];
		if (isset($info['products']) && $info['products']) {
			foreach ($info['products'] as $item) {
				$product = is_object($item) ? $item->toArray() : $item;
				$product = array_merge($product, $product['productInfo'] ?? []);
				unset($product['productInfo']);
				$products[] = $product;
			}
			if ($products) {
				$cateIds = implode(',', array_column($products, 'cate_id'));
				/** @var StoreCategoryServices $categoryService */
				$categoryService = app()->make(StoreCategoryServices::class);
				$cateList = $categoryService->getCateParentAndChildName($cateIds);
				foreach ($products as $key => &$item) {
					$cateName = array_filter($cateList, function ($val) use ($item) {
						if (in_array($val['id'], explode(',', $item['cate_id']))) {
							return $val;
						}
					});
					$item['cate_name'] = [];
					foreach ($cateName as $k => $v) {
						$item['cate_name'][] = $v['one'] . '/' . $v['two'];
					}
					$item['cate_name'] = is_array($item['cate_name']) ? implode(',', $item['cate_name']) : '';
				}
			}
		}
		$info['products'] = $products;
		$info['brand_id'] = isset($info['brands']) && $info['brands'] ? array_column($info['brands'], 'brand_id') : [];
		$info['store_label_id'] = [];
		if (isset($info['productLabels']) && $info['productLabels']) {
			foreach ($info['productLabels'] as $label) {
				if (isset($label['productLabelInfo']) && $label['productLabelInfo']) {
					$info['store_label_id'][] = $label['productLabelInfo'];
				}
			}
		}
		unset($info['brands'], $info['productLabels']);
		return $info;
	}

	/**
     * 保存活动边框
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function saveData(int $id, array $data)
    {
        if (!$data['section_time'] || count($data['section_time']) != 2) {
            throw new AdminException('请选择活动时间');
        }
        [$start_time, $end_time] = $data['section_time'];
        if (strtotime($end_time) < time()) {
            throw new AdminException('活动结束时间不能小于当前时间');
        }
        if ($id) {
            $info = $this->dao->get((int)$id);
            if (!$info) {
                throw new AdminException('数据不存在');
            }
        }
        $data['start_time'] = strtotime($start_time);
        $data['stop_time'] = strtotime($end_time);
		$promotionsAuxiliaryData = [];
		switch ($data['product_partake_type']) {
			case 1://全部
				$data['product_id'] = [];
				break;
			case 2://指定ID参与
			case 3://指定ID不参与
				/** @var StoreProductServices $storeProductServices */
				$storeProductServices = app()->make(StoreProductServices::class);
				$count = $storeProductServices->count(['is_show' => 1, 'is_del' => 0, 'id' => $data['product_id']]);
				$productCount = count(array_unique($data['product_id']));
				if ($count != $productCount) {
					throw new AdminException('选择商品中有已下架或移入回收站');
				}
				$promotionsAuxiliaryData['product_id'] = $data['product_id'];
				break;
			case 4://指定品牌
				$data['brand_id'] = array_unique($data['brand_id']);
				/** @var StoreBrandServices $storeBrandServices */
				$storeBrandServices = app()->make(StoreBrandServices::class);
				$brandCount = $storeBrandServices->count(['is_show' => 1, 'is_del' => 0, 'id' => $data['brand_id']]);
				if (count(array_unique($data['brand_id'])) != $brandCount) {
					throw new AdminException('选择商品品牌中有已下架或删除的');
				}
				$promotionsAuxiliaryData['brand_id'] = $data['brand_id'];
				break;
			case 5://指定商品标签
				$data['store_label_id'] = array_unique($data['store_label_id']);
				/** @var StoreProductLabelServices $storeProductLabelServices */
				$storeProductLabelServices = app()->make(StoreProductLabelServices::class);
				$labelCount = $storeProductLabelServices->count(['id' => $data['store_label_id']]);
				if (count(array_unique($data['store_label_id'])) != $labelCount) {
					throw new ValidateException('选择商品标签中有已下架或删除的');
				}
				$promotionsAuxiliaryData['store_label_id'] = $data['store_label_id'];
				break;
			default:
				throw new ValidateException('暂不支持该类型商品');
				break;
		}
        unset($data['section_time'], $data['promotions']);
        $this->transaction(function () use ($id, $data, $promotionsAuxiliaryData) {
            $time = time();
            $data['update_time'] = $time;
            if ($id) {
                $this->dao->update($id, $data);
            } else {
                $data['add_time'] = $time;
                $res = $this->dao->save($data);
                $id = $res->id;
			}
			/** @var StorePromotionsAuxiliaryServices $storePromotionsAuxiliaryServices */
            $storePromotionsAuxiliaryServices = app()->make(StorePromotionsAuxiliaryServices::class);
			//保存关联数据
            $storePromotionsAuxiliaryServices->savePromotionsRelation((int)$id, (int)$data['product_partake_type'], $promotionsAuxiliaryData, [], [], false);
        });
        return true;
    }



}
