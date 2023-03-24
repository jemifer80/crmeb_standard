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

namespace app\services\activity\newcomer;

use app\dao\activity\newcomer\StoreNewcomerDao;
use app\jobs\product\ProductLogJob;
use app\services\BaseServices;
use app\services\diy\DiyServices;
use app\services\order\StoreOrderServices;
use app\services\product\ensure\StoreProductEnsureServices;
use app\services\product\label\StoreProductLabelServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\user\UserRelationServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\SystemConfigService;
use think\exception\ValidateException;

/**
 * Class StoreNewcomerServices
 * @package app\services\activity\newcomer
 * @mixin StoreNewcomerDao
 */
class StoreNewcomerServices extends BaseServices
{

	/**
	* 商品活动类型
	 */
	const TYPE = 7;

    /**
     * StoreNewcomerServices constructor.
     * @param StoreNewcomerDao $dao
     */
    public function __construct(StoreNewcomerDao $dao)
    {
        $this->dao = $dao;
    }

	/**
 	* 获取新人礼商品详情
	* @param int $id
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getInfo(int $id)
	{
		$info = $this->dao->get($id, ['*'], ['product']);
		$res = [];
		if ($info) {
			$res = $info->toArray();
			$product = $res['product'] ?? [];
			unset($res['product'], $product['id'], $product['price']);
			$res = array_merge($res, $product);
		}
		return $res;
	}

	/**
 	* 获取新人专享商品
	* @param array $where
	* @param string $field
	* @param array $with
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getCustomerProduct(array $where = [], string $field = '*', array $with = ['product', 'attrValue'])
	{
		[$page, $limit] = $this->getPageValue();
		$where['is_del'] = 0;
		$list = $this->dao->getList($where, $field, $page, $limit, $with);
		$res = [];
		if ($list) {
			foreach ($list as &$item) {
				$product = $item['product'] ?? [];
				if ($product) {
					unset($item['product'], $product['id'], $product['price']);
					$item = array_merge($item, $product);
					$res[] = $item;
				}
			}
		}
		return $res;
	}

	/**
 	* 保存新人礼专属商品
	* @param $data
	* @return bool
	 */
	public function saveNewcomer($data)
	{
		$productIds = [];
		$oldProductIds = $this->dao->getColumn(['is_del' => 0], 'id,product_id');
		if ($oldProductIds) $oldProductIds = array_column($oldProductIds, 'product_id');
		if ($data) {
			$productIds = array_column($data, 'product_id');
			$this->transaction(function () use ($data) {
				foreach ($data as $product) {
					$this->saveData(0, $product);
				}
			});
		}
		$deleteIds = array_merge(array_diff($oldProductIds, $productIds));
		//清空现有新人礼商品
		if ($deleteIds) {
			$this->dao->update(['is_del' => 0, 'product_id' => $deleteIds], ['is_del' => 1 ,'update_time' => time()]);
		}
		return true;
	}

    /**
     * 保存数据
     * @param int $id
     * @param array $info
     */
    public function saveData(int $id, array $info)
    {
		if (!$info || !isset($info['product_id']) || !$info['product_id']) {
			throw new ValidateException('请重新选择新人专享商品');
		}
		$attr = $info['attr'];
		if ($attr) {
			foreach ($attr as $a) {
				if (!isset($a['unique']) || !isset($a['price'])) throw new ValidateException('请重新选择新人专享商品');
				if (!$a['price']) {
					throw new ValidateException('请填写商品专享价');
				}
			}
		} else {
			if (!$info['price']) {
				throw new ValidateException('请填写商品专享价');
			}
		}
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        $productInfo = $storeProductServices->getOne(['is_show' => 1, 'is_del' => 0, 'id' => $info['product_id']]);
        if (!$productInfo) {
            throw new AdminException('原商品已下架或移入回收站');
        }
        if ($productInfo['is_vip_product'] || $productInfo['is_presale_product']) {
            throw new AdminException('该商品是预售或svip专享');
        }
		if (!$id) {
			$newcomer = $this->dao->getOne(['product_id' => $info['product_id'], 'is_del' => 0]);
			$id = $newcomer['id'] ?? 0;
		}

        $data = [];
		$data['product_id'] = $productInfo['id'];
		$data['type'] = $productInfo['type'] ?? 0;
		$data['product_type'] = $productInfo['product_type'];
		$data['relation_id'] = $productInfo['relation_id'] ?? 0;
		$data['price'] = $info['price'] ?? 0;

		if ($attr) $data['price'] = min(array_column($attr, 'price'));

		/** @var StoreProductAttrValueServices $productAttrValueServices */
		$productAttrValueServices = app()->make(StoreProductAttrValueServices::class);
		/** @var StoreProductAttrServices $productAttrServices */
		$productAttrServices = app()->make(StoreProductAttrServices::class);
		$attrValue = $productAttrValueServices->getList(['product_id' => $info['product_id'], 'type' => 0]);
		$skus = array_column($attr, 'unique');
		$attr = array_combine($skus, $attr);

        $this->transaction(function () use ($id, $data, $attrValue, $skus, $attr, $productAttrValueServices, $productAttrServices) {
			$newcomerAttrValue = [];
            if ($id) {
				$data['update_time'] = time();
                $res = $this->dao->update($id, $data);
				$newcomerAttrValue = $productAttrValueServices->getList(['product_id' => $id, 'type' => 7]);
                if (!$res) throw new AdminException('修改失败');
            } else {
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                if (!$res) throw new AdminException('添加失败');
                $id = (int)$res->id;
            }
			$detail = [];
			foreach ($attrValue as $item) {
				if (in_array($item['unique'], $skus)) {
					$item['product_id'] = $id;
					$item['type'] = 7;
					if (isset($attr[$item['unique']]['price'])) $item['price'] = $attr[$item['unique']]['price'];
					$item['unique'] = $productAttrServices->createAttrUnique((int)$id, $item['suk']);
					unset($item['id']);
					$detail[] = $item;
				}
			}
			if ($newcomerAttrValue) {
				foreach ($newcomerAttrValue as $item) {
					if (in_array($item['unique'], $skus)) {
						$item['product_id'] = $id;
						$item['type'] = 7;
						if (isset($attr[$item['unique']]['price'])) $item['price'] = $attr[$item['unique']]['price'];
						unset($item['id']);
						$detail[] = $item;
					}
				}
			}
			$productAttrValueServices->delete(['product_id' => $id, 'type' => 7]);
			if ($detail)  $productAttrValueServices->saveAll($detail);
        });
    }

	/**
	* 获取新人专享商品详情
	* @param int $id
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function newcomerDetail(int $uid, int $id)
	{
		$storeInfo = $this->getInfo($id);
		if (!$storeInfo) {
			throw new ValidateException('新人商品已下架或删除');
		}
		/** @var DiyServices $diyServices */
		$diyServices = app()->make(DiyServices::class);
		$infoDiy = $diyServices->getProductDetailDiy();
		//diy控制参数
		if (!isset($infoDiy['is_specs']) || !$infoDiy['is_specs']) {
			$storeInfo['specs'] = [];
		}

		$configData = SystemConfigService::more(['site_url', 'routine_contact_type', 'site_name', 'share_qrcode', 'store_self_mention', 'store_func_status', 'product_poster_title']);
        $siteUrl = $configData['site_url'] ?? '';
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
		/** @var StoreDescriptionServices $descriptionServices */
		$descriptionServices = app()->make(StoreDescriptionServices::class);
		$storeInfo['description'] = $descriptionServices->getDescription(['product_id' => $storeInfo['product_id']]);

        //品牌名称
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
		$productInfo = $storeProductServices->get($storeInfo['product_id'], ['id', 'delivery_type', 'brand_id', 'sales', 'ficti']);
        $storeInfo['brand_name'] = $storeProductServices->productIdByBrandName($storeInfo['product_id'], $productInfo);
		$delivery_type = $storeInfo['delivery_type'] ?? $productInfo['delivery_type'];
        $storeInfo['delivery_type'] = is_string($delivery_type) ? explode(',', $delivery_type) : $delivery_type;
        /**
         * 判断配送方式
         */
        $storeInfo['delivery_type'] = $storeProductServices->getDeliveryType($storeInfo['type'],$storeInfo['relation_id'],$storeInfo['delivery_type']);
		$storeInfo['total'] = $productInfo['sales'] + $productInfo['ficti'];
        $storeInfo['store_label'] = $storeInfo['ensure'] = [];
        if ($storeInfo['store_label_id']) {
            /** @var StoreProductLabelServices $storeProductLabelServices */
            $storeProductLabelServices = app()->make(StoreProductLabelServices::class);
            $storeInfo['store_label'] = $storeProductLabelServices->getColumn([['id', 'in', $storeInfo['store_label_id']]], 'id,label_name');
        }
        if ($storeInfo['ensure_id'] && isset($infoDiy['is_ensure']) && $infoDiy['is_ensure']) {
            /** @var StoreProductEnsureServices $storeProductEnsureServices */
            $storeProductEnsureServices = app()->make(StoreProductEnsureServices::class);
            $storeInfo['ensure'] = $storeProductEnsureServices->getColumn([['id', 'in', $storeInfo['ensure_id']]], 'id,name,image,desc');
        }
        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $data['buy_num'] = $storeOrderServices->getBuyCount($uid, 7, $id);

        /** @var UserRelationServices $userRelationServices */
        $userRelationServices = app()->make(UserRelationServices::class);
        $storeInfo['userCollect'] = $userRelationServices->isProductRelation(['uid' => $uid, 'relation_id' => $storeInfo['product_id'], 'type' => 'collect', 'category' => UserRelationServices::CATEGORY_PRODUCT]);
        $storeInfo['userLike'] = 0;

        $storeInfo['uid'] = $uid;

        //商品详情
        $storeInfo['small_image'] = get_thumb_water($storeInfo['image']);
        $data['storeInfo'] = $storeInfo;

		$data['reply'] = [];
		$data['replyChance'] = $data['replyCount'] = 0;
		if (isset($infoDiy['is_reply']) && $infoDiy['is_reply']) {
			/** @var StoreProductReplyServices $storeProductReplyService */
			$storeProductReplyService = app()->make(StoreProductReplyServices::class);
			$reply = $storeProductReplyService->getRecProductReply($storeInfo['product_id'], (int)($infoDiy['reply_num'] ?? 1));
			$data['reply'] = $reply ? get_thumb_water($reply, 'small', ['pics']) : [];
			[$replyCount, $goodReply, $replyChance] = $storeProductReplyService->getProductReplyData((int)$storeInfo['product_id']);
			$data['replyChance'] = $replyChance;
			$data['replyCount'] = $replyCount;
		}

        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        [$productAttr, $productValue] = $storeProductAttrServices->getProductAttrDetail($id, $uid, 0, 7, $storeInfo['product_id']);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['routine_contact_type'] = $configData['routine_contact_type'] ?? 0;
		$data['store_func_status'] = (int)($configData['store_func_status'] ?? 1);//门店是否开启
		$data['store_self_mention'] = $data['store_func_status'] ? (int)($configData['store_self_mention'] ?? 0) : 0;//门店自提是否开启
        $data['site_name'] = $configData['site_name'] ?? '';
        $data['share_qrcode'] = $configData['share_qrcode'] ?? 0;
		$data['product_poster_title'] = $configData['product_poster_title'] ?? '';
        //浏览记录
        ProductLogJob::dispatch(['visit', ['uid' => $uid, 'id' => $id, 'product_id' => $storeInfo['product_id']], 'newcomer']);

		return $data;
	}


    /**
     * 修改秒杀库存
     * @param int $num
     * @param int $newcomerId
     * @param string $unique
     * @param int $store_id
     * @return bool
     */
    public function decNewcomerStock(int $num, int $newcomerId, string $unique = '', int $store_id = 0)
    {
        $product_id = $this->dao->value(['id' => $newcomerId], 'product_id');
        if ($product_id && $unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去当前普通商品sku的库存增加销量
            $suk = $skuValueServices->value(['unique' => $unique, 'product_id' => $newcomerId, 'type' => 7], 'suk');
            $productUnique = $skuValueServices->value(['suk' => $suk, 'product_id' => $product_id, 'type' => 0], 'unique');
			/** @var StoreProductServices $services */
			$services = app()->make(StoreProductServices::class);
			//减去普通商品库存
			$res = false !== $services->decProductStock($num, $product_id, $productUnique);
        } else {
            $res = false;
        }

        return $res;
    }

    /**
     * 加库存减销量
     * @param int $num
     * @param int $newcomerId
     * @param string $unique
 	 * @param int $store_id
     * @return bool
     */
    public function incNewcomerStock(int $num, int $newcomerId, string $unique = '', int $store_id = 0)
    {
        $product_id = $this->dao->value(['id' => $newcomerId], 'product_id');
        if ($product_id && $unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去当前普通商品sku的库存增加销量
            $suk = $skuValueServices->value(['unique' => $unique, 'product_id' => $newcomerId, 'type' => 7], 'suk');
            $productUnique = $skuValueServices->value(['suk' => $suk, 'product_id' => $product_id, 'type' => 0], 'unique');
			/** @var StoreProductServices $services */
			$services = app()->make(StoreProductServices::class);
			//减去普通商品库存
			$res = $services->incProductStock($num, $product_id, $productUnique);
        } else {
            $res = false;
        }
        return $res;
    }


    /**
     * 下单｜加入购物车验证秒杀商品库存
     * @param int $uid
     * @param int $newcomerId
     * @param int $cartNum
     * @param string $unique
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkNewcomerStock(int $uid, int $newcomerId, int $cartNum = 1, string $unique = '')
    {
		if (!$this->checkUserNewcomer($uid)) {
			throw new ValidateException('您已无法享受新人专享价');
		}
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        if ($unique == '') {
            $unique = $attrValueServices->value(['product_id' => $newcomerId, 'type' => 7], 'unique');
        }
        //检查商品活动状态
        $newcomerInfo = $this->getInfo($newcomerId);
		if (!$newcomerInfo) {
			throw new ValidateException('该活动已下架');
		}
        $attrInfo = $attrValueServices->getOne(['product_id' => $newcomerId, 'unique' => $unique, 'type' => 7]);
        if (!$attrInfo || $attrInfo['product_id'] != $newcomerId) {
            throw new ValidateException('请选择有效的商品属性');
        }
		$suk = $attrInfo['suk'];
		$productAttrInfo = $attrValueServices->getOne(['suk' => $suk, 'product_id' => $newcomerInfo['product_id'], 'type' => 0]);
		if (!$productAttrInfo) {
            throw new ValidateException('请选择有效的商品属性');
        }
		//库存要验证愿商品库存
        if ($cartNum > $productAttrInfo['stock']) {
            throw new ValidateException('该商品库存不足' . $cartNum);
        }
        return [$attrInfo, $unique, $newcomerInfo];
    }

	/**
 	* 验证用户是否可以享受首单优惠
	* @param int $uid
	* @param $userInfo
	* @return array
	*/
	public function checkUserFirstDiscount(int $uid, $userInfo = [])
	{
		if (!$uid) {
			return [];
		}
		//开启新人礼
		if (!sys_config('newcomer_status')) {
			return [];
		}
		//开启首单优惠
		if (!sys_config('first_order_status')) {
			return [];
		}
		if (!$userInfo) {
			/** @var UserServices $userServices */
			$userServices = app()->make(UserServices::class);
			$userInfo = $userServices->getUserInfo($uid);
			if (!$userInfo) {
				return [];
			}
		}
		if (isset($userInfo['is_first_order']) && $userInfo['is_first_order'] != 0) {
			return [];
		}
		$timeStatus = sys_config('newcomer_limit_status', 1);
		if ($timeStatus) {//设置限时，且超过时间不再享受
			$time = sys_config('newcomer_limit_time');
			if ($time) {
				$time = (int)bcsub((string)time(), (string)bcmul((string)$time, '86400'));
				if ($time > $userInfo['add_time']) {
					return [];
				}
			}
		}
		/** @var StoreOrderServices $storeOrderServices */
		$storeOrderServices = app()->make(StoreOrderServices::class);
		$count = $storeOrderServices->getCount([['type', 'not in', [7]], ['uid', '=', $uid]]);
		if ($count) {//有过订单
			return [];
		}
		$discount = sys_config('first_order_discount', 100);
		$discount_limit = sys_config('first_order_discount_limit', 0);
		return [$discount, $discount_limit];
	}

	/**
 	* 验证用户是否可以购买新人专享
	* @param int $uid
	* @param $userInfo
	* @return bool
	 */
	public function checkUserNewcomer(int $uid, $userInfo = [])
	{
		if (!$uid) {
			return false;
		}
		//开启新人礼
		if (!sys_config('newcomer_status')) {
			return false;
		}
		//开启新人专享
		if (!sys_config('register_price_status')) {
			return false;
		}
		if (!$userInfo) {
			/** @var UserServices $userServices */
			$userServices = app()->make(UserServices::class);
			$userInfo = $userServices->getUserInfo($uid);
			if (!$userInfo) {
				return false;
			}
		}
		if (isset($userInfo['is_newcomer']) && $userInfo['is_newcomer'] != 0) {
			return false;
		}
		$timeStatus = sys_config('newcomer_limit_status', 1);
		if ($timeStatus) {//设置限时，且超过时间不再享受
			$time = sys_config('newcomer_limit_time');
			if ($time) {
				$time = (int)bcsub((string)time(), (string)bcmul((string)$time, '86400'));
				if ($time > $userInfo['add_time']) {
					return false;
				}
			}
		}
		/** @var StoreOrderServices $orderServices */
		$orderServices = app()->make(StoreOrderServices::class);
		$count = $orderServices->count(['uid' => $uid, 'type' => 9]);
		if ($count) {
			return false;
		}
		return true;
	}

	/**
 	* 用户注册设置：首单优惠、新人专享是否可用 -1：不可用 0：未使用 1：已使用
	* @param $uid
	* @return false|mixed
	*/
	public function setUserNewcomer($uid)
	{
		if (!$uid) {
			return false;
		}
		//是否开启新人礼
		$newcomer_status = sys_config('newcomer_status');
		$update = [];
		if ($newcomer_status) {
			//开启首单优惠
			$first_order = sys_config('first_order_status');
			$update['is_first_order'] = $first_order ? 0 : -1;
			//开启新人专享
			$is_newcomer = sys_config('register_price_status');
			$update['is_newcomer'] = $is_newcomer ? 0 : -1;
		} else {//不可用，数据记录已使用
			$update = ['is_first_order' => -1, 'is_newcomer' => -1];
		}
		/** @var UserServices $userServices */
		$userServices = app()->make(UserServices::class);
		return $userServices->update($uid, $update);
	}

	/**
 	* 下单修改用户首单优惠
	* @param $uid
	* @param $orderInfo
	* @return bool
	*/
	public function updateUserNewcomer($uid, $orderInfo)
	{
		if (!$uid || !$orderInfo) {
			return false;
		}
		$update = [];
		//首单优惠
		if (isset($orderInfo['first_order_price']) && $orderInfo['first_order_price'] > 0) {
			$update['is_first_order'] = 1;
		}
		//新人专享
		if (isset($orderInfo['type']) && $orderInfo['type'] == 7) {
			$update['is_newcomer'] = 1;
		}
		if ($update) {
			/** @var UserServices $userServices */
			$userServices = app()->make(UserServices::class);
			$userServices->update($uid, $update);
		}
		return true;
	}

	/**
 	* 获取新人专享商品
	* @param int $uid
	* @param array $where
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getDiyNewcomerList(int $uid = 0, array $where = [])
	{
		//验证用户是否享受过新人礼
		if ($uid && !$this->checkUserNewcomer($uid)) {
			return [];
		}
		[$page, $limit] = $this->getPageValue();
		$where['is_del'] = 0;
		$list = $this->dao->getList($where, '*', $page, $limit, ['product']);
		$res = [];
		if ($list) {
			foreach ($list as &$item) {
				$product = $item['product'] ?? [];
				if ($product) {
					unset($item['product'], $product['id'], $product['price'], $product['sales']);
					$item = array_merge($item, $product);
					$res[] = $item;
				}
			}
		}
		return $res;
	}

}
