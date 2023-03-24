<?php

namespace app\jobs\product;

use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use crmeb\basic\BaseJobs;
use crmeb\exceptions\AdminException;
use crmeb\services\erp\Erp as erpServices;
use crmeb\traits\QueueTrait;

class ProductSyncErp extends BaseJobs
{
    use QueueTrait;

    /**
     * @return mixed
     */
    public static function queueName()
    {
        return 'CRMEB_PRO_ERP';
    }

    /**
     * 同步商品到erp
     * @param $id
     * @return mixed
     */
    public function upProductToErp($id)
    {
        try {
            /** @var StoreProductServices $productServices */
            $productServices = app()->make(StoreProductServices::class);
            // 获取商品信息
            $productInfo = $productServices->getInfo($id)['productInfo'];
            $data = [];
            $attrs = $productInfo['attrs'];
            if (!$attrs && $productInfo['attr']) {
                $attrs = [$productInfo['attr']];
            }
            foreach ($attrs as $item) {
                if ($item['pic'] && strstr($item['pic'], 'http') === false) {
                    $siteUrl = sys_config('site_url');
                    $item['pic'] = $siteUrl . $item['pic'];
                }

                $data[] = [
                    'i_id' => $productInfo['code'],
                    'sku_id' => $item['code'],
                    'name' => $productInfo['store_name'],
                    'properties_value' => str_replace(',', ' ', $item['values']),
                    's_price' => $item['price'],
                    'pic' => $item['pic'],
                    'c_price' => $item['cost'],
                    'market_price' => $item['ot_price'],
                ];
            }

            (new erpServices())->serviceDriver('product')->updateProduct($data);
        } catch (\Exception $e) {

            response_log_write([
                'message' => '商品上传失败, 原因: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }

    /**
     * 上传店铺商品
     * @param $id
     * @param $shop
     * @return bool
     */
    public function upBranchProductToErp($id, $shop)
    {
        try {
            /** @var StoreProductServices $productServices */
            $productServices = app()->make(StoreProductServices::class);
            // 获取商品信息
            $productInfo = $productServices->getInfo($id)['productInfo'];
            $data = [];
            $attrs = $productInfo['attrs'];
            if (!$attrs && $productInfo['attr']) {
                $attrs = [$productInfo['attr']];
            }
            foreach ($attrs as $item) {
                $data[] = [
                    'i_id' => $productInfo['code'],
                    'sku_id' => $item['code'],
                    'shop_i_id' => $shop['erp_shop_id'] . $productInfo['code'],
                    'shop_sku_id' => $shop['erp_shop_id'] . $item['code'],
                    'name' => $productInfo['store_name'],
                    'properties_value' => str_replace(',', ' ', $item['values']),
                    'shop_id' => $shop['erp_shop_id'],
                ];
            }

            (new erpServices())->serviceDriver('product')->updateShopProduct($data);
        } catch (\Exception $e) {
            response_log_write([
                'message' => '店铺商品上传失败, 原因: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }

    /**
     * 同步商品
     * @param $spuArr
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function productFromErp($spuArr)
    {
        try {
            $result = (new erpServices())->serviceDriver('product')->syncProduct([$spuArr]);;
            $productList = $result['datas'];
            $productInfo = [];
            /** @var StoreProductServices $productServices */
            $productServices = app()->make(StoreProductServices::class);
            /** @var StoreProductAttrServices $productAttrServices */
            $productAttrServices = app()->make(StoreProductAttrServices::class);

            foreach ($productList as $item) {
                $productInfo = [
                    'image' => (string)$item['pic'],
                    'slider_image' => json_encode([(string)$item['pic']]),
                    'store_name' => $item['name'],
                    'store_info' => $item['name'],
                    'cate_id' => 0,
                    'price' => floatval($item['s_price']),
                    'ot_price' => floatval($item['market_price']),
                    'delivery_type' => '1,2,3',
                    'freight' => 1,
                    'is_show' => 0,
                    'add_time' => time(),
                    'cost' => floatval($item['c_price']),
                    'ficti' => 0,
                    'spec_type' => 1,
                    'code' => $item['i_id'],
                ];
                $detail = $details = $value = [];
                foreach ($item['skus'] as $items) {
                    $detail[] = $items['properties_value'];
                    $details[] = [
                        'name' => $items['properties_value'],
                        'select' => false
                    ];
                    $value[] = [
                        'bar_code' => '',
                        'brokerage' => 0,
                        'brokerage_two' => 0,
                        'code' => $items['sku_id'],
                        'cost' => floatval($items['cost_price']),
                        'detail' => ['规格' => $items['properties_value']],
                        'ot_price' => floatval($items['market_price']),
                        'pic' => (string)$items['pic'],
                        'price' => floatval($items['sale_price']),
                        'select' => true,
                        'value1' => $items['properties_value'],
                        'values' => $items['properties_value'],
                        'vip_price' => 0,
                        'volume' => 0,
                        'weight' => 0,
                        'stock' => 0,
                    ];
                }
                $pid = $productServices->value(['code' => $item['i_id']], 'id');
                if (!$pid) {
                    $pid = $productServices->ErpProductSave($productInfo);
                }
                $attr = [[
                    'value' => '规格',
                    'detail' => $detail,
                    'details' => $details,
                ]];

                $skuList = $productAttrServices->validateProductAttr($attr, $value, $pid, 0, 0, 0);
                $productAttrServices->saveProductAttr($skuList, $pid);
            }

            //清除数据缓存
            $productServices->cacheTag()->clear();
            $productAttrServices->cacheTag()->clear();

        } catch (\Exception $e) {

            response_log_write([
                'message' => '商品同步失败, 原因: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }


        return true;
    }

    /**
     * 同步商品库存
     * @param array $ids
     * @return bool
     */
    public function stockFromErp(array $ids)
    {
        try {
            /** @var StoreProductServices $storeProductServices */
            $storeProductServices = app()->make(StoreProductServices::class);

            /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
            $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);

            //查询ids下的所有规格对应的sku
            $list = $storeProductAttrValueServices->getSkuArray(['product_id' => $ids, 'type' => 0], 'code', 'id');

            $values = array_filter(array_values($list));
            if (empty($values)) {
                throw new AdminException('没有符合同步库存的商品');
            }

            $skuData = $skuMap = [];

            $basic = 20; // 单次查询数量最多20
            $num = count($values);
            $rate = ceil($num / $basic);
            for ($i = 0; $i < $rate; $i++) {
                $code = array_slice($values, $i * $basic, $basic);
                $codeStr = implode(',', $code);
                $result = (new erpServices())->serviceDriver('product')->syncStock($codeStr);
                if (!empty($result['inventorys'])) {
                    foreach ($result['inventorys'] as $inventory) {
                        $skuMap[$inventory['sku_id']] = $inventory['qty'] - $inventory['order_lock'];
                    }
                }
            }

            // 拼装规格数据
            if (!empty($skuMap)) {
                foreach ($skuMap as $key => $item) {
                    if ($id = array_search($key, $list)) {
                        $skuData[] = ['id' => $id, 'stock' => $item, 'sum_stock' => $item];
                    }
                }
            }

            // 同步库存
            $storeProductServices->transaction(function () use ($ids, $skuData, $storeProductAttrValueServices, $storeProductServices) {
                // 同步规格库存
                $storeProductAttrValueServices->saveAll($skuData);
                // 同步商品库存
                $productData = $storeProductAttrValueServices->getProductStockByValues($ids);
                $storeProductServices->saveAll($productData);
            });

            //清除缓存
            $storeProductServices->cacheTag()->clear();
            /** @var StoreProductAttrServices $attrService */
            $attrService = app()->make(StoreProductAttrServices::class);
            $attrService->cacheTag()->clear();
        } catch (\Exception $e) {

            response_log_write([
                'message' => '库存获取失败, 原因: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        return true;
    }

    /**
     * 更新商品库存
     * @param array $list
     * @return bool
     */
    public function updatePlatformStock(array $list): bool
    {
        try {
            $data = array_column($list, 'qty', 'sku_id');
            $shopData = array_keys(array_column($list, 'shop_id', 'shop_id'));
            $erpShopId = $shopData[0] ?? 0;
            if ($erpShopId < 1) {
                return true;
            }

            // 更新平台商品库存
            $defaultShopId = (int)sys_config('jst_default_shopid');
            if ($defaultShopId == $erpShopId) {
                $this->updateStoreProductValueStock($data);
            }

        } catch (\Exception $e) {
            response_log_write([
                'message' => '更新门店库存失败, 原因: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }


    /**
     * 更新平台商品库存
     * @param array $data
     * @return bool
     */
    public function updateStoreProductValueStock(array $data): bool
    {
        try {
            /** @var StoreProductServices $storeProductServices */
            $storeProductServices = app()->make(StoreProductServices::class);

            /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
            $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);

            $skuList = $storeProductAttrValueServices->getSkuArray(['code' => array_keys($data), 'type' => 0], 'id, product_id,code', 'id');
            if (empty($skuList)) {
                throw new AdminException('没有符合同步库存的商品');
            }

            $ids = array_unique(array_column($skuList, 'product_id'));

            $skuData = [];
            foreach ($skuList as $key => $sku) {
                if (array_key_exists($sku['code'], $data)) {
                    $skuData[] = ['id' => $key, 'stock' => $data[$sku['code']]];
                }
            }

            if (empty($skuData)) {
                return true;
            }

            // 同步库存
            $storeProductServices->transaction(function () use ($ids, $skuData, $storeProductAttrValueServices, $storeProductServices) {
                // 同步规格库存
                $storeProductAttrValueServices->saveAll($skuData);
                // 同步商品库存
                $productData = $storeProductAttrValueServices->getProductStockByValues($ids);
                $storeProductServices->saveAll($productData);
            });
        } catch (\Exception $e) {
            response_log_write([
                'message' => '平台商品库存更新失败, 原因: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }
}
