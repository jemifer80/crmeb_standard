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
//declare (strict_types=1);
namespace app\services\product\product;


use app\jobs\product\ProductBatchJob;
use app\jobs\product\ProductCouponJob;
use app\jobs\product\ProductRelationJob;
use app\services\BaseServices;
use app\dao\product\product\StoreProductDao;
use app\services\product\category\StoreCategoryServices;
use crmeb\exceptions\AdminException;
use think\exception\ValidateException;

/**
 * 商品批量操作
 * Class StoreProductBatchProcessServices
 * @package app\services\product\product
 * @mixin StoreProductDao
 */
class StoreProductBatchProcessServices extends BaseServices
{
	/**
 	* 批量处理参数
	* @var array[]
	*/
	protected $data = [
			'cate_id' => [],//分类ids
			'store_label_id' => [],//商品标签
			'delivery_type' => [],//物流方式1：快递，2：门店自提，3：门店配送
			'freight' => 1,//运费设置1：包邮，2：固定运费，3：运费模版
			'postage' => 0,//邮费金额
			'temp_id' => 0,//运费模版
			'give_integral' => 0,//赠送积分
			'coupon_ids' => [],//赠送优惠券ids
			'label_id' => [],//关联用户标签ids
			'recommend' => [],//活动推荐：is_hot、is_benefit、is_new、is_best、is_good
			'custom_form' => [],//自定义留言
		];

    /**
     * StoreProductBatchProcessServices constructor.
     * @param StoreProductDao $dao
     */
    public function __construct(StoreProductDao $dao)
    {
        $this->dao = $dao;
    }

	/**
 	* 根据搜索条件查询ids
	* @param $where
	* @return array
	*/
	public function getIdsByWhere($where)
	{
		$ids = [];
		$cateIds = [];
		if (isset($where['cate_id']) && $where['cate_id']) {
			/** @var StoreCategoryServices $storeCategory */
			$storeCategory = app()->make(StoreCategoryServices::class);
			$cateIds = $storeCategory->getColumn(['pid' => $where['cate_id']], 'id');
		}
		if ($cateIds) {
			$cateIds[] = $where['cate_id'];
			$where['cate_id'] = $cateIds;
		}
		/** @var StoreProductServices $productService */
		$productService = app()->make(StoreProductServices::class);
		$dataInfo = $productService->getProductListByWhere($where, 'id');
		if ($dataInfo) {
			$ids = array_unique(array_column($dataInfo, 'id'));
		}
		return $ids;
	}

	/**
 	* 商品批量操作
	* @param int $type
	* @param array $ids
	* @param array $input_data
	* @param bool $is_all
	* @param array $where
	* @return bool
	*/
	public function batchProcess(int $type, array $ids, array $input_data, bool $is_all = false, array $where = [])
	{
		//全选
		if ($is_all == 1) {
			$ids = $this->getIdsByWhere($where);
		}
		$data = [];
		$isBatch = true;
		switch ($type) {
            case 1://分类
            	$isBatch = false;
            	$cate_id = $input_data['cate_id'] ?? 0;
            	if (!$cate_id) throw new ValidateException('请选择分类');

				$data['cate_id'] = $cate_id;
                break;
            case 2://商品标签 不选默认清空标签
            	$store_label_id = $input_data['store_label_id'] ?? [];
				$data['store_label_id'] = $store_label_id;
                break;
			case 3://物流设置
				$data['delivery_type'] = $input_data['delivery_type'] ?? [];
				if (!$data['delivery_type']) throw new AdminException('请选择商品配送方式');
                break;
			case 4://购买即送积分、优惠券
				$isBatch = false;
				$data['give_integral'] = $input_data['give_integral'] ?? 0;
				$data['coupon_ids'] = $input_data['coupon_ids'] ?? [];
				if (!$data['give_integral'] && !$data['coupon_ids']) {
					throw new AdminException('请输入赠送积分或选择赠送优惠券');
				}
                break;
			case 5://关联用户标签 不选默认清空标签
				$label_id = $input_data['label_id'] ?? [];
				$data['label_id'] = $label_id;
                break;
			case 6://活动推荐
				$recommend = $input_data['recommend'] ?? [];
				$data['is_hot'] = $data['is_benefit'] = $data['is_new'] = $data['is_good'] = $data['is_best'] = 0;
				if ($recommend) {
					$arr = ['is_hot' , 'is_benefit', 'is_new', 'is_good', 'is_best'];
					foreach ($recommend as $item) {
						if (in_array($item, $arr)) {
							$data[$item] = 1;
						}
					}
				}
                break;
			case 7://自定义留言 可以为空 清空之前的设置
				$data['custom_form'] = json_encode($input_data['custom_form'] ?? []);
				break;
			case 8://运费设置
				$data['freight'] = $input_data['freight'] ?? 0;
				$data['postage'] = $input_data['postage'] ?? 0;
				$data['temp_id'] = $input_data['temp_id'] ?? 0;
				if ($data['freight'] == 2 && !$data['postage']) {
					throw new AdminException('请设置运费金额');
				}
				if ($data['freight'] == 3 && !$data['temp_id']) {
					throw new AdminException('请选择运费模版');
				}
				if ($data['freight'] == 1) {
					$data['temp_id'] = 0;
					$data['postage'] = 0;
				} elseif ($data['freight'] == 2) {
					$data['temp_id'] = 0;
				} elseif ($data['freight'] == 3) {
					$data['postage'] = 0;
				}
				break;
            default:
                throw new AdminException('暂不支持该类型批操作');
        }
		//加入批量队列
		ProductBatchJob::dispatchDo('productBatch', [$type, $ids, $data, $isBatch]);
		return true;
	}

	/**
 	* 执行批量修改eb_store_product
	* @param array $ids
	* @param array $data
	* @return bool
	*/
	public function runBatch(array $ids, array $data, int $type = 2)
	{
		if (!$ids || !$data) {
			return false;
		}
		if (isset($data['delivery_type']) && $data['delivery_type']) {
			$data['delivery_type'] = is_array($data['delivery_type']) ? implode(',', $data['delivery_type']) : $data['delivery_type'];
		}
		switch ($type) {
			case 2://商品标签
				$this->dao->batchUpdate($ids, ['store_label_id' => implode(',', $data['store_label_id'])], 'id');
				foreach ($ids as $id) {
					ProductRelationJob::dispatch([$id, $data['store_label_id'], 3]);
				}
				break;
			case 5://用户标签
				$this->dao->batchUpdate($ids, ['label_id' => implode(',', $data['label_id'])], 'id');
				foreach ($ids as $id) {
					ProductRelationJob::dispatch([$id, $data['label_id'], 4]);
				}
				break;
			default://
				$this->dao->batchUpdate($ids, $data, 'id');
				break;
		}
		return true;
	}

	/**
 	* 保存商品分类
	* @param int $id
	* @param array $data
	* @return bool
	*/
	public function setPrdouctCate(int $id, array $data)
	{
		if (!$id || !$data) {
			return true;
		}
		$cate_id = $data['cate_id'] ?? 0;
		if ($cate_id) {
			$this->dao->update($id, ['cate_id' => implode(',', $cate_id)]);
			//商品分类关联
			ProductRelationJob::dispatch([$id, $cate_id, 1]);
		}
		return true;
	}

	/**
 	* 保存商品赠送积分、优惠券
	* @param int $id
	* @param array $data
	* @return bool
	*/
	public function setGiveIntegralCoupon(int $id, array $data)
	{
		if (!$id || !$data) {
			return true;
		}
		$give_integral = $data['give_integral'] ?? 0;
		if ($give_integral) {
			$this->dao->update($id, ['give_integral' => $give_integral]);
		}
		$coupon_ids = $data['coupon_ids'] ?? [];
		if ($coupon_ids) {
			//商品关联优惠券
			ProductCouponJob::dispatchDo('setProductCoupon', [$id, $coupon_ids]);
		}
		return true;
	}
}
