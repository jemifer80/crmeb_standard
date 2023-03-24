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

namespace app\services\user;


use app\dao\user\UserRelationDao;
use app\jobs\product\ProductLogJob;
use app\services\BaseServices;
use app\services\product\product\StoreProductServices;
use crmeb\traits\ServicesTrait;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;

/**
 * Class UserRelationServices
 * @package app\services\user
 * @mixin UserRelationDao
 */
class UserRelationServices extends BaseServices
{
    use ServicesTrait;

    const CATEGORY_PRODUCT = 'product';//商品
    const CATEGORY_REPLY = 'reply';//评价
    const CATEGORY_COMMENT = 'comment';//评价回复
    const CATEGORY_VIDEO = 'video';//短视频
    const CATEGORY_VIDEO_COMMENT = 'video_comment';//短视频评价

    const TYPE_COLLECT = 'collect';//收藏
    const TYPE_LIKE = 'like';//点赞
    const TYPE_SHARE = 'share';//分享
    const TYPE_PLAY = 'play';//播放

    const TYPE_NAME = [
        'collect' => '收藏',
        'like' => '点赞',
        'share' => '分享',
        'play' => '播放',
    ];


    /**
     * UserRelationServices constructor.
     * @param UserRelationDao $dao
     */
    public function __construct(UserRelationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 用户是否点赞或收藏商品
     * @param array $where
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function isProductRelation(array $where)
    {
        $res = $this->dao->getOne($where, 'id');
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $where
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/17
     */
    public function isProductRelationCache(array $where)
    {
        return $this->cacheTag()->remember(md5(json_encode($where)), function () use ($where) {
            return $this->isProductRelation($where);
        });
    }

    /**
     * 获取用户收藏数量
     * @param int $uid
     * @param int $relationId
     * @param string $type
     * @param string $category
     * @return int
     */
    public function getUserCount(int $uid, int $relationId = 0, string $type = self::TYPE_COLLECT, string $category = self::CATEGORY_PRODUCT)
    {
        $where = ['uid' => $uid];
        if ($type) {
            $where['type'] = $type;
        }
        if ($category) {
            $where['category'] = $category;
        }
        if ($relationId) {
            $where['relation_id'] = $relationId;
        }
        return $this->dao->count($where);
    }

    /**
     * @param int $uid
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getUserRelationList(int $uid, string $category = self::CATEGORY_PRODUCT, string $type = self::TYPE_COLLECT)
    {
        $where['uid'] = $uid;
        $where['type'] = $type;
        $where['category'] = $category;
        [$page, $limit] = $this->getPageValue();
        $with = [];
        switch ($category) {
            case 'product':
                $with = ['product'];
                break;
            case 'video':
                $with = ['video'];
                break;
        }
        //短视频未启用
        if ($category == 'video' && !sys_config('video_func_status', 1)) {
            $list = [];
        } else {
            $list = $this->dao->getList($where, 'relation_id,category', $with, $page, $limit);
        }
        $result = [];
        foreach ($list as $k => $item) {
            switch ($category) {
                case 'product':
                    if (isset($item['product']) && isset($item['product']['id'])) {
                        $product = $item['product'];
                        $data = [
                            'id' => $product['id'] ?? 0,
                            'product_id' => $item['relation_id'],
                            'store_name' => $product['store_name'] ?? 0,
                            'price' => $product['price'] ?? 0,
                            'ot_price' => $product['ot_price'] ?? 0,
                            'sales' => $product['sales'] ?? 0,
                            'image' => get_thumb_water($product['image'] ?? 0),
                            'is_del' => $product['is_del'] ?? 0,
                            'is_show' => $product['is_show'] ?? 0,
                            'is_fail' => $product['is_del'] && $product['is_show'],
                            'activity' => $product['activity'] ?? ''
                        ];
                        $result[] = $data;
                    }
                    break;
                case 'video':
                    if (isset($item['video']) && isset($item['video']['id'])) {
                        $video = $item['video'];
                        $data = [
                            'id' => $video['id'] ?? 0,
                            'video_id' => $item['relation_id'],
                            'image' => $video['image'] ?? '',
                            'desc' => $video['desc'] ?? '',
                            'video_url' => $video['video_url'] ?? '',
                            'like_num' => $video['like_num'] ?? 0
                        ];
                        $result[] = $data;
                    }
                    break;
            }
        }
        if ($result && $category == 'product') {
            /** @var StoreProductServices $productServices */
            $productServices = app()->make(StoreProductServices::class);
            $result = $productServices->getActivityList($result);
            $result = $productServices->getProductPromotions($result);
        }
        return $result;
    }

    /**
     * 添加点赞 收藏
     * @param int $uid
     * @param array $productIds
     * @param string $relationType
     * @param string $category
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function productRelation(int $uid, array $productIds, string $relationType, string $category = self::CATEGORY_PRODUCT)
    {
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        $relationId = $this->dao->getColumn([['uid', '=', $uid], ['relation_id', 'IN', $productIds], ['type', '=', $relationType], ['category', '=', $category]], 'relation_id');
        $data = ['uid' => $uid, 'add_time' => time(), 'type' => $relationType, 'category' => $category];
        $dataAll = [];
        foreach ($productIds as $key => $product_id) {
            if (in_array($product_id, $relationId)) {
                continue;
            }
            $data['relation_id'] = $product_id;
            $dataAll[] = $data;
        }
        if ($dataAll) {
            if (!$this->dao->saveAll($dataAll)) {
                throw new ValidateException('添加失败');
            }
        }
        if ($category == 'product') {
            //收藏记录
            ProductLogJob::dispatch(['collect', ['uid' => $uid, 'relation_id' => $productIds, 'product_id' => $productIds]]);
        }

        $this->cacheTag()->clear();

        return true;
    }

    /**
     * 取消 点赞 收藏
     * @param int $uid
     * @param array $productId
     * @param string $relationType
     * @param string $category
     * @return bool
     * @throws \Exception
     */
    public function unProductRelation(int $uid, array $productId, string $relationType = self::TYPE_COLLECT, string $category = self::CATEGORY_PRODUCT)
    {
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        $storeProductRelation = $this->dao->delete(['uid' => $uid, 'relation_id' => $productId, 'type' => $relationType, 'category' => $category]);
        if ($category == 'video') {
            foreach ($productId as $id) {
                $this->dao->bcDec($id, $relationType . '_num', 1);
            }
        }
        if (!$storeProductRelation) throw new ValidateException('取消失败');

        $this->cacheTag()->clear();

        return true;
    }

}
