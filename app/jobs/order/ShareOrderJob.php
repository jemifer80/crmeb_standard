<?php


namespace app\jobs\order;


use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderSplitServices;
use app\services\supplier\SystemSupplierServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 分配订单
 * Class ShareOrderJob
 * @package app\jobs
 */
class ShareOrderJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 门店分配订单
     * @param $orderInfo
     * @return bool
     */
    public function doJob($orderInfo)
    {
        if (!$orderInfo) {
            return true;
        }
        //整单分给门店
        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $order = $storeOrderServices->get($orderInfo['id']);
        if (!$order) {
            return true;
        }
        $orderInfo = $order->toArray();
        //已经分配或者门店自提
        if (!isset($orderInfo['shipping_type']) || $orderInfo['shipping_type'] != 1 || $orderInfo['store_id'] > 0) {
            //分配给门店
            SpliteStoreOrderJob::dispatchDo('splitAfter', [$orderInfo]);
            return true;
        }

        try {
            $id = (int)$orderInfo['id'];
            /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
            $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
            //订单下原商品信息
            $cartInfo = $storeOrderCartInfoServices->getCartColunm(['oid' => $id, 'split_status' => [0, 1]], 'cart_id,type,relation_id,is_gift,cart_num,split_surplus_num', 'cart_id');
            if (!$cartInfo) {
                return true;
            }
            $suppplierIds = $storeIds = [];
            foreach ($cartInfo as $cart) {
                $type = $cart['type'] ?? 0;
                switch ($type) {
                    case 0://兼容之前供应商商品
                        if ($cart['relation_id']) {
                            $suppplierIds[] = $cart['relation_id'];
                        }
                        break;
                    case 1:
                        $storeIds[] = $cart['relation_id'];
                        break;
                    case 2:
                        $suppplierIds[] = $cart['relation_id'];
                        break;
                }
            }
            if ($suppplierIds) {//验证供应商状态（关闭｜删除不分配）
                /** @var  SystemSupplierServices $supplierServices */
                $supplierServices = app()->make(SystemSupplierServices::class);
                $suppplierIds = $supplierServices->getColumn([['id', 'in', $suppplierIds], ['is_show', '=', 1], ['is_del', '=', 0]], 'id');
            }
            $cart_ids = [];
            $other_cart_ids = [];
            //分配给供应商、门店
            if ($suppplierIds) {
                $suppplier_id = $suppplierIds[0] ?? 0;
                $updateData = [];
                //先拆分供应商
                if ($suppplier_id) {
                    foreach ($cartInfo as $cart_id => $cart) {
                        if ($cart['type'] == 2 && $cart['relation_id'] == $suppplier_id) {//拆分
                            $cart_ids[] = ['cart_id' => $cart_id, 'cart_num' => $cart['cart_num']];
                        } else {
                            $other_cart_ids[] = ['cart_id' => $cart_id, 'cart_num' => $cart['cart_num']];
                        }
                    }
                    $updateData['supplier_id'] = $suppplier_id;
                }
                //下单商品都是某一个供应商|| 门店商品，不用拆分
                if (!$other_cart_ids && count($suppplierIds) == 1) {
                    $storeOrderServices->update(['id' => $id], ['supplier_id' => $suppplier_id]);
                } else {
                    //分配订单
                    /** @var  StoreOrderSplitServices $storeOrderSplitServices */
                    $storeOrderSplitServices = app()->make(StoreOrderSplitServices::class);
                    $splitResult = $storeOrderSplitServices->equalSplit($id, $cart_ids);
                    $otherOrder = [];
                    if ($splitResult) {//拆分供应商订单
                        [$orderInfo, $otherOrder] = $splitResult;
                    }
                    $storeOrderServices->update(['id' => $orderInfo['id']], $updateData);
                    if (isset($updateData['store_id'])) {//拆分门店订单
                        SpliteStoreOrderJob::dispatchDo('splitAfter', [$orderInfo]);
                    }
                    //还有商品
                    if ($other_cart_ids && $otherOrder) {
                        //还有其他供应商 || 门店 继续分配
                        if (count($suppplierIds) >= 1) {
                            ShareOrderJob::dispatch([$otherOrder]);
                        }
                    }
                }

            } else {//平台配送
                SpliteStoreOrderJob::dispatchDo('splitAfter', [$orderInfo]);
            }

        } catch (\Throwable $e) {
            response_log_write([
                'message' => '自动拆分供应商订单失败，原因：' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }


}
