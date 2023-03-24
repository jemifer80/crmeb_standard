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

namespace app\services\activity\seckill;


use app\Request;
use app\services\BaseServices;
use app\dao\activity\seckill\StoreSeckillDao;
use app\services\diy\DiyServices;
use app\services\order\StoreOrderServices;
use app\services\other\QrcodeServices;
use app\services\product\ensure\StoreProductEnsureServices;
use app\services\product\label\StoreProductLabelServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\user\UserRelationServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrResultServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\system\config\SystemGroupDataServices;
use app\jobs\product\ProductLogJob;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\GroupDataService;
use crmeb\services\SystemConfigService;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

/**
 * Class StoreSeckillServices
 * @package app\services\activity\seckill
 * @method getSeckillIdsArray(array $ids, array $field)
 * @mixin StoreSeckillDao
 */
class StoreSeckillServices extends BaseServices
{
    const OPOXMWTJ = 'k8kkOJ';

    /**
     * 商品活动类型
     */
    const TYPE = 1;

    /**
     * StoreSeckillServices constructor.
     * @param StoreSeckillDao $dao
     */
    public function __construct(StoreSeckillDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $productIds
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/14
     */
    public function getSeckillIdsArrayCache(array $productIds)
    {
        $list = $this->dao->cacheList();
        if (!$list) {
            return $this->dao->getSeckillIdsArray($productIds, ['id', 'time_id', 'product_id']);
        } else {
            $seckill = [];
			$time = time();
            foreach ($list as $item) {
                if ($item['is_del'] == 0 && $item['status'] == 1 && $item['start_time'] <= $time && $item['stop_time'] >= ($time - 86400) && in_array($item['product_id'], $productIds)) {
                    $seckill[] = [
                        'id' => $item['id'],
                        'time_id' => $item['time_id'],
                        'product_id' => $item['product_id'],
                    ];
                }
            }
            return $seckill;
        }
    }

    public function getCount(array $where)
    {
        $this->dao->count($where);
    }

    /**
     * 秒杀是否存在
     * @param int $id
     * @param string $field
     * @return array|int|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSeckillCount(int $id = 0, string $field = 'time_id')
    {
        $where = [];
        $where[] = ['is_del', '=', 0];
        $where[] = ['status', '=', 1];
        if ($id) {
            $time = time();
            $where[] = ['id', '=', $id];
            $where[] = ['start_time', '<=', $time];
            $where[] = ['stop_time', '>=', $time - 86400];
            $seckill_one = $this->dao->getOne($where, $field);
            if (!$seckill_one) {
                throw new ValidateException('活动已结束');
            }
            /** @var SystemGroupDataServices $systemGroupDataService */
            $systemGroupDataService = app()->make(SystemGroupDataServices::class);
            $seckillTime = array_column($systemGroupDataService->getConfigNameValue('routine_seckill_time'), null, 'id');
            $config = $seckillTime[$seckill_one['time_id']] ?? false;
            if (!$config) {
                throw new ValidateException('活动已结束');
            }
            $now_hour = date('H', time());
            $start_hour = $config['time'];
            $end_hour = (int)$start_hour + (int)$config['continued'];
            if ($start_hour <= $now_hour && $end_hour > $now_hour) {
                return $seckill_one;
            } else if ($start_hour > $now_hour) {
                throw new ValidateException('活动未开始');
            } else {
                throw new ValidateException('活动已结束');
            }
        } else {
            $seckillTime = sys_data('routine_seckill_time') ?: [];//秒杀时间段
            $timeInfo = ['time' => 0, 'continued' => 0];
            foreach ($seckillTime as $key => $value) {
                $currentHour = date('H');
                $activityEndHour = (int)$value['time'] + (int)$value['continued'];
                if ($currentHour >= (int)$value['time'] && $currentHour < $activityEndHour && $activityEndHour < 24) {
                    $timeInfo = $value;
                    break;
                }
            }
            if ($timeInfo['time'] == 0) return 0;
            $activityEndHour = $timeInfo['time'] + (int)$timeInfo['continued'];
            $startTime = strtotime(date('Y-m-d')) + (int)$timeInfo['time'] * 3600;
            $stopTime = strtotime(date('Y-m-d')) + (int)$activityEndHour * 3600;

            $where[] = ['start_time', '<', $startTime];
            $where[] = ['stop_time', '>', $stopTime];
            return $this->dao->getCount($where);
        }
    }


    /**
     * 保存数据
     * @param int $id
     * @param array $data
     */
    public function saveData(int $id, array $data)
    {
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
        if (in_array($data['product_type'], [1, 2, 3])) {
            $data['freight'] = 2;
            $data['temp_id'] = 0;
            $data['postage'] = 0;
        } else {
            if ($data['freight'] == 1) {
                $data['temp_id'] = 0;
                $data['postage'] = 0;
            } elseif ($data['freight'] == 2) {
                $data['temp_id'] = 0;
            } elseif ($data['freight'] == 3) {
                $data['postage'] = 0;
            }
            if ($data['freight'] == 2 && !$data['postage']) {
                throw new AdminException('请设置运费金额');
            }
            if ($data['freight'] == 3 && !$data['temp_id']) {
                throw new AdminException('请选择运费模版');
            }
        }
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        $data['image'] = $data['images'][0] ?? '';
        $data['images'] = json_encode($data['images']);
        $data['price'] = min(array_column($detail, 'price'));
        $data['ot_price'] = min(array_column($detail, 'ot_price'));
        $data['quota'] = $data['quota_show'] = array_sum(array_column($detail, 'quota'));
        if ($data['quota'] > $productInfo['stock']) {
            throw new ValidateException('限量不能超过商品库存');
        }
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        unset($data['section_time'], $data['description'], $data['attrs'], $data['items']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        $id = $this->transaction(function () use ($id, $data, $description, $detail, $items, $storeDescriptionServices, $storeProductAttrServices) {
            if ($id) {
                $res = $this->dao->update($id, $data);
                if (!$res) throw new AdminException('修改失败');
            } else {
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                if (!$res) throw new AdminException('添加失败');
                $id = (int)$res->id;
            }
            $storeDescriptionServices->saveDescription((int)$id, $description, 1);
            $skuList = $storeProductAttrServices->validateProductAttr($items, $detail, (int)$id, 1);
            $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$id, 1);

            $res = true;
            foreach ($valueGroup as $item) {
                $res = $res && CacheService::setStock($item['unique'], (int)$item['quota_show']);
            }
            if (!$res) {
                throw new AdminException('占用库存失败');
            }

            return $id;
        });
        $this->dao->cacheTag()->clear();
        //保存
        $seckill = $this->dao->get($id, ['*'], ['descriptions']);
        $this->dao->cacheUpdate($seckill->toArray());
        CacheService::redisHandler('product_attr')->clear();
    }

    /**
     * @param int $timeId
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/4
     */
    public function createSeckillListCache(int $timeId, int $id = 0)
    {
        //创建数据缓存
        $list = $this->dao->getListByTime($timeId);
        $timeId = (string)$timeId;
        if ($id) {
            $this->dao->cacheUpdateList($list, $timeId);
        } else {
            $this->dao->cacheCreate($list, $timeId);
        }
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function systemPage(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        foreach ($list as &$item) {
            $item['store_name'] = $item['title'];
            if ($item['status']) {
                if ($item['start_time'] > time())
                    $item['start_name'] = '未开始';
                else if (bcadd($item['stop_time'], '86400') < time())
                    $item['start_name'] = '已结束';
                else if (bcadd($item['stop_time'], '86400') > time() && $item['start_time'] < time()) {
                    $item['start_name'] = '进行中';
                }
            } else $item['start_name'] = '已结束';
            $end_time = $item['stop_time'] ? date('Y/m/d', (int)$item['stop_time']) : '';
            $item['_stop_time'] = $end_time;
            $item['stop_status'] = $item['stop_time'] + 86400 < time() ? 1 : 0;
        }
        return compact('list', 'count');
    }

    /**
     * 获取秒杀详情
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->get($id);
        if ($info) {
            if ($info['start_time'])
                $start_time = date('Y-m-d', (int)$info['start_time']);

            if ($info['stop_time'])
                $stop_time = date('Y-m-d', (int)$info['stop_time']);
            if (isset($start_time) && isset($stop_time))
                $info['section_time'] = [$start_time, $stop_time];
            else
                $info['section_time'] = [];
            unset($info['start_time'], $info['stop_time']);
            $info['give_integral'] = intval($info['give_integral']);
            $info['price'] = floatval($info['price']);
            $info['ot_price'] = floatval($info['ot_price']);
            $info['postage'] = floatval($info['postage']);
            $info['cost'] = floatval($info['cost']);
            $info['weight'] = floatval($info['weight']);
            $info['volume'] = floatval($info['volume']);
            if (!$info['delivery_type']) {
                $info['delivery_type'] = [1];
            }
            if ($info['postage']) {
                $info['freight'] = 2;
            } elseif ($info['temp_id']) {
                $info['freight'] = 3;
            } else {
                $info['freight'] = 1;
            }
            /** @var StoreDescriptionServices $storeDescriptionServices */
            $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
            $info['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'type' => 1]);
            $info['attrs'] = $this->attrList($id, $info['product_id']);
        }
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
        $seckillResult = $storeProductAttrResultServices->value(['product_id' => $id, 'type' => 1], 'result');
        $items = json_decode($seckillResult, true)['attr'];
        $productAttr = $this->getAttr($items, $pid, 0);
        $seckillAttr = $this->getAttr($items, $id, 1);
        foreach ($productAttr as $pk => $pv) {
            foreach ($seckillAttr as &$sv) {
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
        $header[] = ['title' => '秒杀价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品条形码', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'code', 'align' => 'center', 'minWidth' => 80];
        $attrs['header'] = $header;
        return $attrs;
    }

    /**
     * 获取规格
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
//            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $sukValue = $storeProductAttrValueServices->getSkuArray(['product_id' => $id, 'type' => $type, 'suk' => $suk], 'bar_code,code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota,quota_show', 'suk');
            if (count($sukValue)) {
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                }
                $valueNew[$count]['detail'] = json_encode($detail);
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
//                $valueNew[$count]['quota'] = $sukValue[$suk]['quota'] ? intval($sukValue[$suk]['quota']) : 0;
                $valueNew[$count]['quota'] = isset($sukValue[$suk]['quota_show']) && $sukValue[$suk]['quota_show'] ? intval($sukValue[$suk]['quota_show']) : 0;
                $valueNew[$count]['code'] = $sukValue[$suk]['code'] ?? '';
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
     *
     * @param int $time
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/4
     */
    public function getListByTimeCache(int $time)
    {
        [$page, $limit] = $this->getPageValue();
        $res = $this->dao->cacheList();

        $list = [];
        $stime = time();
		$etime = $stime - 86400;
		foreach ($res as $value) {
			if (isset($value['is_del']) && $value['is_del'] == 0 && isset($value['start_time']) && $value['start_time'] <= $stime && isset($value['stop_time']) && $value['stop_time'] >= $etime && isset($value['status']) && isset($value['time_id']) && $value['status'] && $value['time_id'] == $time) {
				$list[] = $value;
			}
		}

        $newResList = array_slice($list, ($page - 1) * $limit, $limit);

        if (!$newResList) {
            $newResList = $this->getListByTime($time);
        } else {
            foreach ($newResList as &$item) {
                if ($item['quota'] > 0) {
                    $percent = (int)(($item['quota_show'] - $item['quota']) / $item['quota_show'] * 100);
                    $item['percent'] = $percent;
                    $item['stock'] = $item['quota'];
                } else {
                    $item['percent'] = 100;
                    $item['stock'] = 0;
                }
                $item['price'] = floatval($item['price']);
                $item['ot_price'] = floatval($item['ot_price']);
            }
        }

        return $newResList;
    }

    /**
     * 获取某个时间段的秒杀列表
     * @param int $time
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getListByTime(int $time, array $ids = [], bool $isStore = false)
    {
        [$page, $limit] = $this->getPageValue();
        $seckillInfo = $this->dao->getListByTime($time, $ids, $page, $limit, $isStore);
        if (count($seckillInfo)) {
            foreach ($seckillInfo as $key => &$item) {
                if ($item['quota'] > 0) {
                    $percent = (int)(($item['quota_show'] - $item['quota']) / $item['quota_show'] * 100);
                    $item['percent'] = $percent;
                    $item['stock'] = $item['quota'];
                } else {
                    $item['percent'] = 100;
                    $item['stock'] = 0;
                }
                $item['price'] = floatval($item['price']);
                $item['ot_price'] = floatval($item['ot_price']);
            }
        }
        return $seckillInfo;
    }

    /**
     * 获取秒杀详情
     * @param Request $request
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function seckillDetail(Request $request, int $id)
    {
        $uid = (int)$request->uid();
        //读取秒杀商品缓存信息
        $storeInfo = $this->dao->cacheRemember($id, function () use ($id) {
            $storeInfo = $this->dao->getOne(['id' => $id], '*', ['descriptions']);
            if (!$storeInfo) {
                throw new ValidateException('商品不存在');
            } else {
                $storeInfo = $storeInfo->toArray();
            }
            return $storeInfo;
        });
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

        //品牌名称
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);

        $productInfo = $storeProductServices->getCacheProductInfo((int)$storeInfo['product_id']);

        $storeInfo['brand_name'] = $storeProductServices->productIdByBrandName($storeInfo['product_id'], $productInfo);
        $delivery_type = $storeInfo['delivery_type'] ?? $productInfo['delivery_type'];
        $storeInfo['delivery_type'] = is_string($delivery_type) ? explode(',', $delivery_type) : $delivery_type;

        /**
         * 判断配送方式
         */
        $storeInfo['delivery_type'] = $storeProductServices->getDeliveryType($storeInfo['type'], $storeInfo['relation_id'], $storeInfo['delivery_type']);
        $storeInfo['total'] = $productInfo['sales'] + $productInfo['ficti'];
        $storeInfo['store_label'] = $storeInfo['ensure'] = [];
        if ($storeInfo['store_label_id']) {
            /** @var StoreProductLabelServices $storeProductLabelServices */
            $storeProductLabelServices = app()->make(StoreProductLabelServices::class);
            $storeInfo['store_label'] = $storeProductLabelServices->getLabelCache($storeInfo['store_label_id'], ['id', 'label_name']);
        }
        if ($storeInfo['ensure_id'] && isset($infoDiy['is_ensure']) && $infoDiy['is_ensure']) {
            /** @var StoreProductEnsureServices $storeProductEnsureServices */
            $storeProductEnsureServices = app()->make(StoreProductEnsureServices::class);
            $storeInfo['ensure'] = $storeProductEnsureServices->getEnsurCache($storeInfo['ensure_id'], ['id', 'name', 'image', 'desc']);
        }

        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $data['buy_num'] = $storeOrderServices->getBuyCount($uid, 1, $id);

        /** @var UserRelationServices $userRelationServices */
        $userRelationServices = app()->make(UserRelationServices::class);
        $storeInfo['userCollect'] = $userRelationServices->isProductRelation(['uid' => $uid, 'relation_id' => $storeInfo['product_id'], 'type' => 'collect', 'category' => UserRelationServices::CATEGORY_PRODUCT]);
        $storeInfo['userLike'] = 0;

        $storeInfo['uid'] = $uid;
        if ($storeInfo['quota'] > 0) {
            $percent = (int)(($storeInfo['quota_show'] - $storeInfo['quota']) / $storeInfo['quota_show'] * 100);
            $storeInfo['percent'] = $percent;
            $storeInfo['stock'] = $storeInfo['quota'];
        } else {
            $storeInfo['percent'] = 100;
            $storeInfo['stock'] = 0;
        }

        /** @var SystemGroupDataServices $groupDataService */
        $groupDataService = app()->make(SystemGroupDataServices::class);
        $timeInfo = json_decode($groupDataService->cacheInfoValue((int)$storeInfo['time_id']), true);
        $today = strtotime(date('Y-m-d'));
        $activityEndHour = ($timeInfo['time']['value'] ?? '0') + ($timeInfo['continued']['value'] ?? '0');
        $storeInfo['last_time'] = (int)bcadd((string)$today, (string)bcmul((string)$activityEndHour, '3600', 0));

        //获取秒杀商品状态
        if ($storeInfo['status'] == 1) {
            $seckillTime = array_column(sys_data('routine_seckill_time'), null, 'id');
            $config = $seckillTime[$storeInfo['time_id']] ?? false;
            if (!$config) {
                throw new ValidateException('活动已结束');
            }
            $now_hour = date('H', time());
            $start_hour = $config['time'];
            $end_hour = (int)$start_hour + (int)$config['continued'];
            if ($start_hour <= $now_hour && $end_hour > $now_hour) {
                $storeInfo['status'] = 1;
            } else if ($start_hour > $now_hour) {
                $storeInfo['status'] = 2;
            } else {
                $storeInfo['status'] = 0;
            }
        }
        //商品详情
        $storeInfo['small_image'] = get_thumb_water($storeInfo['image']);
        $data['storeInfo'] = $storeInfo;
        $storeInfo['product_id'] = (int)$storeInfo['product_id'];
        $data['reply'] = [];
        $data['replyChance'] = $data['replyCount'] = 0;
        if (isset($infoDiy['is_reply']) && $infoDiy['is_reply']) {
            /** @var StoreProductReplyServices $storeProductReplyService */
            $storeProductReplyService = app()->make(StoreProductReplyServices::class);
            $reply = $storeProductReplyService->getRecProductReplyCache($storeInfo['product_id'], (int)($infoDiy['reply_num'] ?? 1));
            $data['reply'] = $reply ? get_thumb_water($reply, 'small', ['pics']) : [];
            [$replyCount, $goodReply, $replyChance] = $storeProductReplyService->getProductReplyData((int)$storeInfo['product_id']);
            $data['replyChance'] = $replyChance;
            $data['replyCount'] = $replyCount;
        }

        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        [$productAttr, $productValue] = $storeProductAttrServices->getProductAttrDetailCache($id, $uid, 0, 1, $storeInfo['product_id'], $productInfo);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['routine_contact_type'] = $configData['routine_contact_type'] ?? 0;
        $data['store_func_status'] = (int)($configData['store_func_status'] ?? 1);//门店是否开启
        $data['store_self_mention'] = $data['store_func_status'] ? (int)($configData['store_self_mention'] ?? 0) : 0;//门店自提是否开启
        $data['site_name'] = $configData['site_name'] ?? '';
        $data['share_qrcode'] = $configData['share_qrcode'] ?? 0;
        $data['product_poster_title'] = $configData['product_poster_title'] ?? '';
        //浏览记录
        ProductLogJob::dispatch(['visit', ['uid' => $uid, 'id' => $id, 'product_id' => $storeInfo['product_id']], 'seckill']);
        return $data;
    }

    /**
     * 修改秒杀库存
     * @param int $num
     * @param int $seckillId
     * @param string $unique
     * @param int $store_id
     * @return bool
     */
    public function decSeckillStock(int $num, int $seckillId, string $unique = '', int $store_id = 0)
    {
        $product_id = $this->dao->value(['id' => $seckillId], 'product_id');
        $res = false;
        if ($product_id) {
            if ($unique) {
                /** @var StoreProductAttrValueServices $skuValueServices */
                $skuValueServices = app()->make(StoreProductAttrValueServices::class);
                //减去秒杀商品的sku库存增加销量
                $res = false !== $skuValueServices->decProductAttrStock($seckillId, $unique, $num, 1);
                //减去秒杀库存
                $res = $res && $this->dao->decStockIncSales(['id' => $seckillId, 'type' => 1], $num);
                //减去当前普通商品sku的库存增加销量
                $suk = $skuValueServices->value(['unique' => $unique, 'product_id' => $seckillId, 'type' => 1], 'suk');
                $productUnique = $skuValueServices->value(['suk' => $suk, 'product_id' => $product_id, 'type' => 0], 'unique');
                /** @var StoreProductServices $services */
                $services = app()->make(StoreProductServices::class);
                //减去普通商品库存
                $res = $res && $services->decProductStock($num, $product_id, $productUnique);
            } else {
                $res = false !== $this->dao->decStockIncSales(['id' => $seckillId, 'type' => 1], $num);
            }
        }
		//更新单个缓存
		$info = $this->dao->getOne(['id' => $seckillId], '*', ['descriptions']);
		if ($info) {
			$info = $info->toArray();
			$this->dao->cacheUpdate($info);
		}
        return $res;
    }

    /**
     * 加库存减销量
     * @param int $num
     * @param int $seckillId
     * @param string $unique
     * @param int $store_id
     * @return bool
     */
    public function incSeckillStock(int $num, int $seckillId, string $unique = '', int $store_id = 0)
    {
        $product_id = $this->dao->value(['id' => $seckillId], 'product_id');
        $res = false;
        if ($product_id) {
            if ($unique) {
                /** @var StoreProductAttrValueServices $skuValueServices */
                $skuValueServices = app()->make(StoreProductAttrValueServices::class);
                //减去秒杀商品的sku库存增加销量
                $res = false !== $skuValueServices->incProductAttrStock($seckillId, $unique, $num, 1);
                //减去秒杀库存
                $res = $res && $this->dao->incStockDecSales(['id' => $seckillId, 'type' => 1], $num);
                //减去当前普通商品sku的库存增加销量
                $suk = $skuValueServices->value(['unique' => $unique, 'product_id' => $seckillId, 'type' => 1], 'suk');
                $productUnique = $skuValueServices->value(['suk' => $suk, 'product_id' => $product_id, 'type' => 0], 'unique');
                /** @var StoreProductServices $services */
                $services = app()->make(StoreProductServices::class);
                //减去普通商品库存
                $res = $res && $services->incProductStock($num, $product_id, $productUnique);
            } else {
                $res = false !== $this->dao->incStockDecSales(['id' => $seckillId, 'type' => 1], $num);
            }
        }
		//更新单个缓存
		$info = $this->dao->getOne(['id' => $seckillId], '*', ['descriptions']);
		if ($info) {
			$info = $info->toArray();
			$this->dao->cacheUpdate($info);
		}
        return $res;
    }

    /**
     * 下单｜加入购物车验证秒杀商品库存
     * @param int $uid
     * @param int $seckillId
     * @param int $cartNum
     * @param string $unique
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkSeckillStock(int $uid, int $seckillId, int $cartNum = 1, string $unique = '')
    {
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        if ($unique == '') {
            $unique = $attrValueServices->value(['product_id' => $seckillId, 'type' => 1], 'unique');
        }
        //检查商品活动状态
        $storeSeckillinfo = $this->getSeckillCount($seckillId, '*,title as store_name');
        if (!$storeSeckillinfo) {
            throw new ValidateException('该活动已下架');
        }
        if ($storeSeckillinfo['once_num'] < $cartNum) {
            throw new ValidateException('每个订单限购' . $storeSeckillinfo['once_num'] . '件');
        }
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $userBuyCount = $orderServices->getBuyCount($uid, 1, $seckillId);
        if ($storeSeckillinfo['num'] < ($userBuyCount + $cartNum)) {
            throw new ValidateException('每人总共限购' . $storeSeckillinfo['num'] . '件');
        }
        if ($storeSeckillinfo['num'] < $cartNum) {
            throw new ValidateException('每人限购' . $storeSeckillinfo['num'] . '件');
        }
        $attrInfo = $attrValueServices->getOne(['product_id' => $seckillId, 'unique' => $unique, 'type' => 1]);
        if (!$attrInfo || $attrInfo['product_id'] != $seckillId) {
            throw new ValidateException('请选择有效的商品属性');
        }
        if ($cartNum > $attrInfo['quota']) {
            throw new ValidateException('该商品库存不足' . $cartNum);
        }
        return [$attrInfo, $unique, $storeSeckillinfo];
    }

    /**
     * 获取当前的秒杀时间time
     * @return int|string
     */
    public function getSeckillTime()
    {
        $seckillTime = GroupDataService::getData('routine_seckill_time') ?? [];
        $currentHour = (int)date('H');
        $time = 0;
        foreach ($seckillTime as $item) {
            $activityEndHour = (int)bcadd((string)$item['time'], (string)$item['continued'], 0);
            if ($currentHour >= $item['time'] && $currentHour < $activityEndHour) {
                $time = $item['id'];
            }
        }
        return $time;
    }
}
