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

namespace app\services\order;

use app\dao\order\StoreOrderCartInfoDao;
use app\services\BaseServices;
use crmeb\services\CacheService;
use crmeb\traits\OptionTrait;
use crmeb\traits\ServicesTrait;
use think\exception\ValidateException;

/**
 * Class StoreOrderCartInfoServices
 * @package app\services\order
 * @mixin StoreOrderCartInfoDao
 */
class StoreOrderCartInfoServices extends BaseServices
{
	use OptionTrait;
    use ServicesTrait;

    /**
     * StorePinkServices constructor.
     * @param StoreOrderCartInfoDao $dao
     */
    public function __construct(StoreOrderCartInfoDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 检测订单商品是否核销完
     * @param int $oid
     * @return bool
     */
    public function checkWriteOff(int $oid)
    {
        return $this->dao->count(['oid' => $oid, 'is_writeoff' => 0]) ? false : true;
    }

    /**
     * 清空订单商品缓存
     * @param int $oid
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function clearOrderCartInfo(int $oid)
    {
        return CacheService::delete(md5('store_order_cart_info_' . $oid));
    }

    /**
     * 获取指定订单下的商品详情
     * @param int $oid
     * @return array|mixed
     */
    public function getOrderCartInfo(int $oid)
    {
        $cartInfo = CacheService::get(md5('store_order_cart_info_' . $oid));
        if ($cartInfo) return $cartInfo;
        $cart_info = $this->dao->getCartColunm(['oid' => $oid], 'cart_info', 'cart_id');
        $info = [];
        foreach ($cart_info as $k => $v) {
            $_info = is_string($v) ? json_decode($v, true) : $v;
            if (!isset($_info['productInfo'])) $_info['productInfo'] = [];
            //缩略图处理
            if (isset($_info['productInfo']['attrInfo'])) {
                $_info['productInfo']['attrInfo'] = get_thumb_water($_info['productInfo']['attrInfo']);
            }
            $_info['product_type'] = $_info['productInfo']['product_type'] ?? 0;
			$_info['supplier_id'] = $_info['productInfo']['supplier_id'] ?? 0;
            $_info['is_support_refund'] = $_info['productInfo']['is_support_refund'] ?? 1;
            $_info['productInfo'] = get_thumb_water($_info['productInfo']);
            $_info['refund_num'] = $this->dao->sum(['cart_id' => $_info['id']], 'refund_num');
            $info[$k]['cart_info'] = $_info;
            unset($_info);
        }
        CacheService::set(md5('store_order_cart_info_' . $oid), $info);
        return $info;
    }

    /**
     * 查找购物车里的所有商品标题
     * @param int $oid
     * @param false $goodsNum
     * @return bool|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCarIdByProductTitle(int $oid, bool $goodsNum = false)
    {
        $key = md5('store_order_cart_product_title_' . $oid);
        $title = CacheService::get($key);
        if (!$title) {
            $orderCart = $this->dao->getCartInfoList(['oid' => $oid], ['cart_info']);
            foreach ($orderCart as $item) {
                if (isset($item['cart_info']['productInfo']['store_name'])) {
                    if ($goodsNum && isset($item['cart_info']['cart_num'])) {
                        $title .= $item['cart_info']['productInfo']['store_name'] . ' * ' . $item['cart_info']['cart_num'] . ' | ';
                    } else {
                        $title .= $item['cart_info']['productInfo']['store_name'] . '|';
                    }
                }
            }
            if ($title) {
                $title = substr($title, 0, strlen($title) - 1);
            }
            CacheService::set($key, $title);
        }
        return $title ? $title : '';
    }

    /**
     * 获取打印订单的商品信息
     * @param int $oid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCartInfoPrintProduct(int $oid)
    {
        $cartInfo = $this->dao->getCartInfoList(['oid' => $oid], ['is_gift','cart_info']);
        $product = [];
        foreach ($cartInfo as $item) {
            $value = is_string($item['cart_info']) ? json_decode($item['cart_info'], true) : $item['cart_info'];
            $value['productInfo']['store_name'] = $value['productInfo']['store_name'] ?? "";
            $value['productInfo']['store_name'] = substrUTf8($value['productInfo']['store_name'], 10, 'UTF-8', '');
			$value['is_gift'] = $item['is_gift'];
            $product[] = $value;
        }
        return $product;
    }

    /**
     * 保存购物车info
     * @param $oid
     * @param array $cartInfo
     * @param $uid
     * @param array $promotions
     * @return int
     */
    public function setCartInfo($oid, array $cartInfo, $uid, array $promotions = [])
    {
        $group = [];
        foreach ($cartInfo as $cart) {
            $group[] = [
                'oid' => $oid,
                'uid' => $uid,
                'cart_id' => $cart['id'],
                'type' => $cart['productInfo']['type'] ?? 0,
                'relation_id' => $cart['productInfo']['relation_id'] ?? 0,
                'product_id' => $cart['productInfo']['id'],
                'product_type' => $cart['productInfo']['product_type'] ?? 0,
                'promotions_id' => implode(',', $cart['promotions_id'] ?? []),
                'is_gift' => isset($cart['is_gift']) && $cart['is_gift'] ? 1 : 0,
                'is_support_refund' => isset($cart['is_gift']) && $cart['is_gift'] ? 0 : ($cart['productInfo']['is_support_refund'] ?? 1),
                'cart_info' => json_encode($cart),
                'cart_num' => $cart['cart_num'],
                'surplus_num' => $cart['cart_num'],
                'split_surplus_num' => $cart['cart_num'],
                'unique' => md5($cart['id'] . '' . $oid)
            ];
        }
        if ($promotions) {
            /** @var StoreOrderPromotionsServices $services */
            $services = app()->make(StoreOrderPromotionsServices::class);
            $services->setPromotionsDetail((int)$uid, (int)$oid, $cartInfo, $promotions);
        }
        return $this->dao->saveAll($group);
    }

    /**
     * 订单创建成功之后计算订单（实际优惠、积分、佣金、上级、上上级）
     * @param $oid
     * @param array $cartInfo
     * @return bool
     */
    public function updateCartInfo($oid, array $cartInfo)
    {
        foreach ($cartInfo as $cart) {
            $group = [
                'cart_info' => json_encode($cart)
            ];
            $this->dao->update(['oid' => $oid, 'cart_id' => $cart['id']], $group);
        }
        return true;
    }

    /**
     * 商品编号
     * @param int $oid
     * @return array
     */
    public function getCartIdsProduct(int $oid)
    {
        $where = [
            'oid' => $oid,
        ];
        return $this->dao->getColumn($where, 'product_id', 'oid', true);
    }

    /**
     * 检测这些商品是否还可以拆分
     * @param int $oid
     * @param array $cart_data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkCartIdsIsSplit(int $oid, array $cart_data)
    {
        if (!$cart_data) return false;
        $ids = array_unique(array_column($cart_data, 'cart_id'));
        if ($this->dao->getCartInfoList(['oid' => $oid, 'cart_id' => $ids, 'split_status' => 2], ['cart_id'])) {
            throw new ValidateException('您选择的商品已经拆分完成，请刷新或稍后重新选择');
        }
        $cartInfo = $this->getSplitCartList($oid, 'surplus_num,split_surplus_num,cart_info,cart_num', 'cart_id');
        if (!$cartInfo) {
            throw new ValidateException('该订单已发货完成');
        }
        foreach ($cart_data as $cart) {
            $surplus_num = $cartInfo[$cart['cart_id']]['surplus_num'] ?? 0;
            if (!$surplus_num) {//兼容之前老数据
                $_info = $cartInfo[$cart['cart_id']]['cart_info'] ?? [];
                $surplus_num = $_info['cart_num'] ?? 0;
            }
            if ($cart['cart_num'] > $surplus_num) {
                throw new ValidateException('您选择商品拆分数量大于购买数量');
            }
        }
        return true;
    }

    /**
     * 获取可退款商品
     * @param int $oid
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getRefundCartList(int $oid, string $field = '*', string $key = '')
    {
        $cartInfo = $this->dao->getColumn([['oid', '=', $oid]], $field, $key);
        foreach ($cartInfo as $key => &$item) {
            if ($field == 'cart_info') {
                $item = is_string($item) ? json_decode($item, true) : $item;
            } else {
                if (isset($item['cart_info'])) $item['cart_info'] = is_string($item['cart_info']) ? json_decode($item['cart_info'], true) : $item['cart_info'];
                if (isset($item['cart_num']) && !$item['cart_num']) {//兼容之前老数据
                    $item['cart_num'] = $item['cart_info']['cart_num'] ?? 0;
                }
            }
            $surplus = (int)bcsub((string)$item['cart_num'], (string)$item['refund_num'], 0);
            if ($surplus > 0) {
                $item['surplus_num'] = $surplus;
            } else {
                unset($cartInfo[$key]);
            }
        }
        return array_merge($cartInfo);
    }

    /**
     * 获取某个订单还可以拆分商品 split_status 0：未拆分1：部分拆分2：拆分完成
     * @param int $oid
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getSplitCartList(int $oid, string $field = '*', string $key = '')
    {
		/** @var StoreOrderServices $orderServices */
		$orderServices = app()->make(StoreOrderServices::class);
		$order = $orderServices->get($oid);
		if (!$order) {
			throw new ValidateException('订单不存在');
		}
		$store_id = $this->getItem('store_id', 0);
		$supplier_id = $this->getItem('supplier_id', 0);

		//拆分完整主订单查询未发货子订单
		if ($order['pid'] == -1) {
			$oid = $orderServices->value(['pid' => $oid, 'status' => 0, 'supplier_id' => $supplier_id, 'store_id' => $store_id, 'refund_type' => [0, 3]], 'id');
		}
        $cartInfo = $this->dao->getColumn([['oid', '=', $oid], ['split_status', 'IN', [0, 1]]], $field, $key);
        foreach ($cartInfo as &$item) {
            if ($field == 'cart_info') {
                $item = is_string($item) ? json_decode($item, true) : $item;
            } else {
                if (isset($item['cart_info'])) $item['cart_info'] = is_string($item['cart_info']) ? json_decode($item['cart_info'], true) : $item['cart_info'];
                if (isset($item['cart_num']) && !$item['cart_num']) {//兼容之前老数据
                    $item['cart_num'] = $item['cart_info']['cart_num'] ?? 0;
                }
                $item['surplus_num'] = $item['split_surplus_num'];
            }
        }
        return $cartInfo;
    }


}
