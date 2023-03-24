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

namespace app\services\pc;

use app\services\BaseServices;
use app\services\product\category\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserServices;
use crmeb\services\UploadService;
use crmeb\services\UtilService;
use crmeb\services\wechat\MiniProgram;
use GuzzleHttp\Psr7\Utils;

/**
 * Class ProductServices
 * @package app\services\pc
 */
class ProductServices extends BaseServices
{

    /**
     * PC端获取商品列表
     * @param array $where
     * @param int $uid
     * @return mixed
     */
    public function getProductList(array $where, int $uid)
    {
        /** @var StoreProductServices $product */
        $product = app()->make(StoreProductServices::class);

        $where['is_show'] = 1;
        $where['is_del'] = 0;
        $data['count'] = $product->getCount($where);
        [$page, $limit] = $this->getPageValue();
        $where['is_vip_product'] = 0;
        if ($uid) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $is_vip = $userServices->value(['uid' => $uid], 'is_money_level');
            $where['is_vip_product'] = $is_vip ? -1 : 0;
        }

        $list = $product->getSearchList($where + ['star' => 1], $page, $limit, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name']);
        foreach ($list as &$item) {
            if (isset($item['star']) && count($item['star'])) {
                $item['star'] = bcdiv((string)array_sum(array_column($item['star'], 'product_score')), (string)count($item['star']), 1);
            } else {
                $item['star'] = '3.0';
            }
			$item['presale_pay_status'] = $product->checkPresaleProductPay((int)$item['id'], $item);
        }
        $list = $product->getActivityList($list);
        $data['list'] = get_thumb_water($product->getProduceOtherList($list, $uid, !!$where['status']), 'mid');
        return $data;
    }

    /**
     * PC端商品详情小程序码
     * @param int $product_id
     * @return bool|int|mixed|string
     */
    public function getProductRoutineCode(int $product_id)
    {
        try {
            $namePath = 'routine_product_' . $product_id . '.jpg';
            $data = 'id=' . $product_id;
            /** @var SystemAttachmentServices $systemAttachmentService */
            $systemAttachmentService = app()->make(SystemAttachmentServices::class);
            $imageInfo = $systemAttachmentService->getOne(['name' => $namePath]);
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $res = MiniProgram::appCodeUnlimit($data, 'pages/goods_details/index', 280);
                if (!$res) return false;
                $uploadType = (int)sys_config('upload_type', 1);
                $upload = UploadService::init($uploadType);
                $res = (string)Utils::streamFor($res);
                $res = $upload->to('routine/product')->validate()->setAuthThumb(false)->stream($res, $namePath);
                if ($res === false) {
                    return false;
                }
                $imageInfo = $upload->getUploadInfo();
                $imageInfo['image_type'] = $uploadType;
                if ($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl . $imageInfo['dir']);
                else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                if (!$remoteImage['status']) return false;
                $systemAttachmentService->save([
                    'name' => $imageInfo['name'],
                    'att_dir' => $imageInfo['dir'],
                    'satt_dir' => $imageInfo['thumb_path'],
                    'att_size' => $imageInfo['size'],
                    'att_type' => $imageInfo['type'],
                    'image_type' => $imageInfo['image_type'],
                    'module_type' => 2,
                    'time' => time(),
                    'pid' => 1,
                    'type' => 2
                ]);
                $url = $imageInfo['dir'];
            } else $url = $imageInfo['att_dir'];
            if ($imageInfo['image_type'] == 1) $url = $siteUrl . $url;
            return $url;
        } catch (\Exception $e) {
            return '';
        }
    }

	/**
 	* pc首页分类商品
	* @param int $uid
	* @return array
	 */
    public function getCategoryProduct(int $uid = 0)
    {
        /** @var StoreCategoryServices $category */
        $category = app()->make(StoreCategoryServices::class);
        /** @var StoreProductServices $product */
        $product = app()->make(StoreProductServices::class);
        [$page, $limit] = $this->getPageValue();
        $list = $category->getCid($page, $limit);
		$where = ['star' => 1, 'is_show' => 1, 'is_del' => 0];
		$where['is_vip_product'] = 0;
        if ($uid) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $is_vip = $userServices->value(['uid' => $uid], 'is_money_level');
            $where['is_vip_product'] = $is_vip ? -1 : 0;
        }
		$where['pid'] = 0;
        foreach ($list as &$info) {
            $productList = $product->getSearchList($where + ['cid' => $info['id']], 1, 8, ['id,store_name,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,ot_price']);
            foreach ($productList as &$item) {
                if (isset($item['star']) && count($item['star'])) {
                    $item['star'] = bcdiv((string)array_sum(array_column($item['star'], 'product_score')), (string)count($item['star']), 1);
                } else {
                    $item['star'] = '3.0';
                }
            }
            $info['productList'] = get_thumb_water($productList, 'mid');
        }
        $data['list'] = $list;
        $data['count'] = $category->getCidCount();
        return $data;
    }
}
