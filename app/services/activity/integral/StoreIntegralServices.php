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

namespace app\services\activity\integral;

use app\Request;
use app\services\BaseServices;
use app\dao\activity\integral\StoreIntegralDao;
use app\services\diy\DiyServices;
use app\services\product\ensure\StoreProductEnsureServices;
use app\services\product\label\StoreProductLabelServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrResultServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\jobs\product\ProductLogJob;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use think\exception\ValidateException;

/**
 * 积分商品
 * Class StoreIntegralServices
 * @package app\services\activity\integral
 * @mixin StoreIntegralDao
 */
class StoreIntegralServices extends BaseServices
{
    const THODLCEG = 'ykGUKB';

    /**
     * 商品活动类型
     */
    const TYPE = 4;

    /**
     * StoreIntegralServices constructor.
     * @param StoreIntegralDao $dao
     */
    public function __construct(StoreIntegralDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取指定条件下的条数
     * @param array $where
     */
    public function getCount(array $where)
    {
        $this->dao->count($where);
    }

    /**
     * 积分商品添加
     * @param int $id
     * @param array $data
     */
    public function saveData(int $id, array $data)
    {
        if ($data['freight'] == 2 && !$data['postage']) {
            throw new AdminException('请设置运费金额');
        }
        if ($data['freight'] == 3 && !$data['temp_id']) {
            throw new AdminException('请选择运费模版');
        }
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        $productInfo = $storeProductServices->getOne(['is_show' => 1, 'is_del' => 0, 'id' => $data['product_id']]);
        if (!$productInfo) {
            throw new AdminException('原商品已下架或移入回收站');
        }
        if ($productInfo['is_vip_product'] || $productInfo['is_presale_product']) {
            throw new AdminException('该商品是预售或svip专享');
        }
        $data['product_type'] = $productInfo['product_type'];
        $data['type'] = $productInfo['type'] ?? 0;
        $data['relation_id'] = $productInfo['relation_id'] ?? 0;
        $custom_form = $productInfo['custom_form'] ?? [];
        $data['custom_form'] = is_array($custom_form) ? json_encode($custom_form) : $custom_form;
        $store_label_id = $productInfo['store_label_id'] ?? [];
        $data['store_label_id'] = is_array($store_label_id) ? implode(',', $store_label_id) : $store_label_id;
        $ensure_id = $productInfo['ensure_id'] ?? [];
        $data['ensure_id'] = is_array($ensure_id) ? implode(',', $ensure_id) : $ensure_id;
        $specs = $productInfo['specs'] ?? [];
        $data['specs'] = is_array($specs) ? json_encode($specs) : $specs;
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];
        if (!$data['image'] && count($data['images']) > 0) {
            $data['image'] = $data['images'][0];
        }
        $data['images'] = json_encode($data['images']);
        $integral_data = array_column($detail, 'integral', 'price');
        $data['integral'] = (int)min($integral_data);
        $data['price'] = array_search($data['integral'], $integral_data);
        $data['quota'] = $data['quota_show'] = array_sum(array_column($detail, 'quota'));
        if ($data['quota'] > $storeProductServices->value(['id' => $data['product_id']], 'stock')) {
            throw new ValidateException('限量不能超过商品库存');
        }
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        unset($data['section_time'], $data['description'], $data['attrs'], $data['items']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        $this->transaction(function () use ($id, $data, $description, $detail, $items, $storeDescriptionServices, $storeProductAttrServices, $storeProductServices) {
            if ($id) {
                $res = $this->dao->update($id, $data);
                if (!$res) throw new AdminException('修改失败');
            } else {
                if (!$storeProductServices->getOne(['is_show' => 1, 'is_del' => 0, 'id' => $data['product_id']])) {
                    throw new AdminException('原商品已下架或移入回收站');
                }
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                if (!$res) throw new AdminException('添加失败');
                $id = (int)$res->id;
            }
            $storeDescriptionServices->saveDescription((int)$id, $description, 4);
            $skuList = $storeProductAttrServices->validateProductAttr($items, $detail, (int)$id, 4);
            $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$id, 4);

            $res = true;
            foreach ($valueGroup as $item) {
                $res = $res && CacheService::setStock($item['unique'], (int)$item['quota_show'], 4);
            }
            if (!$res) {
                throw new AdminException('占用库存失败');
            }
        });
    }

    /**
     * 批量添加商品
     * @param array $data
     */
    public function saveBatchData(array $data)
    {
        /** @var StoreProductServices $service */
        $service = app()->make(StoreProductServices::class);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductAttrResultServices $storeProductAttrResultServices */
        $storeProductAttrResultServices = app()->make(StoreProductAttrResultServices::class);
        if (!$data) {
            throw new ValidateException('请先添加产品!');
        }
        $attrs = [];
        foreach ($data['attrs'] as $k => $v) {
            $attrs[$v['product_id']][] = $v;
        }
        foreach ($attrs as $k => $v) {
            $productInfo = $service->getOne(['id' => $k]);
            $productInfo = is_object($productInfo) ? $productInfo->toArray() : [];
            if ($productInfo) {
                $product = [];
                $result = $storeProductAttrResultServices->getResult(['product_id' => $productInfo['id'], 'type' => 0]);
                $product['product_id'] = $productInfo['id'];
                $product['description'] = $storeDescriptionServices->getDescription(['product_id' => $productInfo['id'], 'type' => 0]);
                $product['attrs'] = $v;
                $product['items'] = $result['attr'];
                $product['is_show'] = isset($data['is_show']) ? $data['is_show'] : 0;
                $product['title'] = $productInfo['store_name'];
                $product['unit_name'] = $productInfo['unit_name'];
                $product['image'] = $productInfo['image'];
                $product['images'] = $productInfo['slider_image'];
                $product['num'] = 0;
                $product['is_host'] = 0;
                $product['once_num'] = 0;
                $product['sort'] = 0;
                $this->saveData(0, $product);
            }
        }
        return true;
    }

    /**
     * 积分商品列表
     * @param array $where
     * @return array
     */
    public function systemPage(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取详情
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('查看的商品不存在!');
        }
        if ($info->is_del) {
            throw new ValidateException('您查看的积分商品已被删除!');
        }
        $info['price'] = floatval($info['price']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        $info['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'type' => 4]);
        $info['attrs'] = $this->attrList($id, $info['product_id']);
        return $info;
    }

    /**
     * 获取规格
     * @param int $id
     * @param int $pid
     * @return mixed
     */
    public function attrList(int $id, int $pid)
    {
        /** @var StoreProductAttrResultServices $storeProductAttrResultServices */
        $storeProductAttrResultServices = app()->make(StoreProductAttrResultServices::class);
        $combinationResult = $storeProductAttrResultServices->value(['product_id' => $id, 'type' => 4], 'result');
        $items = json_decode($combinationResult, true)['attr'];
        $productAttr = $this->getAttr($items, $pid, 0);
        $combinationAttr = $this->getAttr($items, $id, 4);
        foreach ($productAttr as $pk => $pv) {
            foreach ($combinationAttr as &$sv) {
                if ($pv['detail'] == $sv['detail']) {
                    $productAttr[$pk] = $sv;
                }
            }
            $productAttr[$pk]['detail'] = json_decode($productAttr[$pk]['detail']);
        }
        $attrs['items'] = $items;
        $attrs['value'] = $productAttr;
        foreach ($items as $key => $item) {
            $header[] = ['title' => $item['value'], 'key' => 'value' . ($key + 1), 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '兑换积分', 'key' => 'integral', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '金额', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '兑换次数', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $attrs['header'] = $header;
        return $attrs;
    }

    /**
     * 获得规格
     * @param $attr
     * @param $id
     * @param $type
     * @return array
     */
    public function getAttr($attr, $id, $type)
    {
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            $suk = implode(',', $item['detail']);
            $sukValue = $storeProductAttrValueServices->getSkuArray(['product_id' => $id, 'type' => $type, 'suk' => $suk], 'bar_code,cost,price,integral,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota,quota_show', 'suk');
            if (count($sukValue)) {
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                }
                $valueNew[$count]['detail'] = json_encode($detail);
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['integral'] = isset($sukValue[$suk]['integral']) ? floatval($sukValue[$suk]['integral']) : 0;
                $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
//                $valueNew[$count]['quota'] = $sukValue[$suk]['quota'] ? intval($sukValue[$suk]['quota']) : 0;
                $valueNew[$count]['quota'] = isset($sukValue[$suk]['quota_show']) && $sukValue[$suk]['quota_show'] ? intval($sukValue[$suk]['quota_show']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
                $valueNew[$count]['_checked'] = $type != 0;
                $count++;
            }
        }
        return $valueNew;
    }

    /**
     * 积分商品详情
     * @param Request $request
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function integralDetail(Request $request, int $id)
    {
        $storeInfo = $this->dao->getOne(['id' => $id], '*', ['getPrice']);
        if (!$storeInfo) {
            throw new ValidateException('商品不存在');
        } else {
            $storeInfo = $storeInfo->toArray();
        }
        /** @var DiyServices $diyServices */
        $diyServices = app()->make(DiyServices::class);
        $infoDiy = $diyServices->getProductDetailDiy();
        //diy控制参数
        if (!isset($infoDiy['is_specs']) || !$infoDiy['is_specs']) {
            $storeInfo['specs'] = [];
        }
        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['sale_stock'] = 0;
        if ($storeInfo['stock'] > 0) $storeInfo['sale_stock'] = 1;
        $uid = $request->uid();
        /** @var StoreDescriptionServices $storeDescriptionService */
        $storeDescriptionService = app()->make(StoreDescriptionServices::class);
        $storeInfo['description'] = $storeDescriptionService->getDescription(['product_id' => $id, 'type' => 4]);
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
        $storeInfo['small_image'] = get_thumb_water($storeInfo['image']);
        $data['storeInfo'] = $storeInfo;

        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        [$productAttr, $productValue] = $storeProductAttrServices->getProductAttrDetail($id, $uid, 0, 4, $storeInfo['product_id']);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        //浏览记录
        ProductLogJob::dispatch(['visit', ['uid' => $uid, 'id' => $id, 'product_id' => $storeInfo['product_id']], 'integral']);
        return $data;
    }

    /**
     * 修改销量和库存
     * @param $num
     * @param $integralId
     * @return bool
     */
    public function decIntegralStock(int $num, int $integralId, string $unique)
    {
        $product_id = $this->dao->value(['id' => $integralId], 'product_id');
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去积分商品的sku库存增加销量
            $res = false !== $skuValueServices->dao->decStockIncSalesDecQuota(['product_id' => $integralId, 'unique' => $unique, 'type' => 4], $num);
            //减去积分商品库存
            $res = $res && $this->dao->decStockIncSales(['id' => $integralId, 'type' => 4], $num);
            //获取拼团的sku
            $sku = $skuValueServices->value(['product_id' => $integralId, 'unique' => $unique, 'type' => 4], 'suk');
            //减去当前普通商品sku的库存增加销量
            $res = $res && $skuValueServices->decStockIncSales(['product_id' => $product_id, 'suk' => $sku, 'type' => 0], $num);
        } else {
            $res = false !== $this->dao->decStockIncSales(['id' => $integralId, 'type' => 4], $num);
        }
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        //减去普通商品库存
        $res = $res && $services->decProductStock($num, $product_id);
        return $res;
    }

    /**
     * 获取一条积分商品
     * @param $id
     * @return mixed
     */
    public function getIntegralOne($id)
    {
        return $this->dao->validProduct($id, '*');
    }

    /**
     * 验证积分商品下单库存限量
     * @param int $uid
     * @param int $integralId
     * @param int $num
     * @param string $unique
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkoutProductStock(int $uid, int $integralId, int $num = 1, string $unique = '')
    {
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        if ($unique == '') {
            $unique = $attrValueServices->value(['product_id' => $integralId, 'type' => 4], 'unique');
        }
        $StoreIntegralInfo = $this->getIntegralOne($integralId);
        if (!$StoreIntegralInfo) {
            throw new ValidateException('该商品已下架或删除');
        }
        /** @var StoreIntegralOrderServices $orderServices */
        $orderServices = app()->make(StoreIntegralOrderServices::class);
        $userBuyCount = $orderServices->getBuyCount($uid, $integralId);
        if ($StoreIntegralInfo['once_num'] < $num && $StoreIntegralInfo['once_num'] != -1) {
            throw new ValidateException('每个订单限购' . $StoreIntegralInfo['once_num'] . '件');
        }
        if ($StoreIntegralInfo['num'] < ($userBuyCount + $num) && $StoreIntegralInfo['num'] != -1) {
            throw new ValidateException('每人总共限购' . $StoreIntegralInfo['num'] . '件');
        }
        $res = $attrValueServices->getOne(['product_id' => $integralId, 'unique' => $unique, 'type' => 4], 'suk,quota');
        if ($num > $res['quota']) {
            throw new ValidateException('该商品库存不足' . $num);
        }
        $product_stock = $attrValueServices->value(['product_id' => $StoreIntegralInfo['product_id'], 'suk' => $res['suk'], 'type' => 0], 'stock');
        if ($product_stock < $num) {
            throw new ValidateException('该商品库存不足' . $num);
        }
        if (!CacheService::checkStock($unique, $num, 4)) {
            throw new ValidateException('该商品库存不足' . $num);
        }
        return $unique;
    }

    /**
     * 获取推荐积分商品
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getIntegralList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit, 'id,image,title,integral,price,sales');
        return $list;
    }

    /**
     * 获取全部积分商品
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAllIntegralList(array $where)
    {
        $list = $this->dao->getList($where, 0, 0, 'id,image,title,integral,price,sales');
        return $list;
    }
}
