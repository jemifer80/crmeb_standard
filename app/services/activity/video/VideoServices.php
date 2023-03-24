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

namespace app\services\activity\video;

use app\dao\activity\video\VideoDao;
use app\jobs\activity\VideoJob;
use app\services\activity\live\LiveRoomServices;
use app\services\BaseServices;
use app\services\product\product\StoreProductServices;
use app\services\product\category\StoreCategoryServices;
use app\services\user\UserRelationServices;
use think\exception\ValidateException;


/**
 * Class VideoServices
 * @package app\services\activity\video
 * @mixin VideoDao
 */
class VideoServices extends BaseServices
{

	/**
 	* 移动端视频需要默认值
	* @var array
	 */
	protected $videoDefault = [
					'isMore' => false,
					'state' => "pause",
					'playIng' => false,
					'isShowimage' => false,
					'isShowProgressBarTime' => false,
					'isplay' => true
				];

    /**
     * VideoServices constructor.
     * @param VideoDao $dao
     */
    public function __construct(VideoDao $dao)
    {
        $this->dao = $dao;
    }

	/**
 	* 获取短视频信息（获取不到抛出异常）
	* @param int $id
	* @param string $field
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getVideoInfo(int $id, string $field = '*')
	{
		$videoInfo = $this->dao->getOne(['id' => $id], $field);
		if (!$videoInfo) {
            throw new ValidateException('获取短视频信息失败');
        }
		return $videoInfo->toArray();
	}

	/**
	* 获取视频详情
	* @param int $id
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getInfo(int $id)
	{
		$info = $this->dao->get($id);
		if (!$info) {
			throw new ValidateException('视频不存在');
		}
		$info = $info->toArray();
		$productInfo = [];
		$info['type_name'] = '平台';
		if ($info['product_id']) {
			$info['product_id'] = is_string($info['product_id']) ? explode(',', $info['product_id']) : $info['product_id'];
			/** @var StoreProductServices $productServices */
			$productServices = app()->make(StoreProductServices::class);
			$productInfo = $productServices->getColumn([['id', 'in', $info['product_id']]], 'id,type,product_type,price,image,store_name,cate_id,sales,stock');
			$cateIds = implode(',', array_column($productInfo, 'cate_id'));
			/** @var StoreCategoryServices $categoryService */
			$categoryService = app()->make(StoreCategoryServices::class);
			$cateList = $categoryService->getCateParentAndChildName($cateIds);
			foreach ($productInfo as $key => &$item) {
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
		$info['productInfo'] = $productInfo;
		return $info;
	}

	/**
	* 后台获取视频列表
	* @param $where
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function sysPage($where)
	{
		[$page, $limit] = $this->getPageValue();
		$list = $this->dao->getList($where, '*', $page, $limit);
		$count = 0;
		if ($list) {
			$site_name = sys_config('site_name');
			$site_image = sys_config('wap_login_logo');
			/** @var StoreProductServices $productServices */
			$productServices = app()->make(StoreProductServices::class);
			$product_where = ['is_del' => 0, 'is_show' => 1];
			foreach ($list as &$item) {
				$item['product_id'] = is_string($item['product_id']) ? explode(',', $item['product_id']) : $item['product_id'];
				$item['product_info'] = [];
				$item['product_num'] = 0;
				if ($item['product_id']) {
					$item['product_info'] = $productServices->getSearchList($product_where + ['ids' => $item['product_id']], 0, 0, ['id', 'store_name', 'image', 'price'], '', []);
					$item['product_num'] = count($item['product_info']);
				}

				$item['type_name'] = $site_name;
				$item['type_image'] = $site_image;
				$item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '';
			}
			$count = $this->dao->count($where);
		}

		return compact('list', 'count');
	}

	/**
	* 获取短视频列表
	* @param int $uid
	* @param string $field
 	* @param int $order_type
 	* @param int $id
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getVideoList(int $uid, string $field = '*', int $order_type = 1, int $id = 0)
	{
		//短视频未启用
		if (!sys_config('video_func_status', 1)) {
			return [];
		}
		$where = ['is_show' => 1, 'is_del' => 0];
		[$page, $limit] = $this->getPageValue();
		//限制一次最多请求条数
		if ($limit > 10) $limit = 10;
		if ($id) {//指定视频进入
			$ids = array_merge([$id], $this->dao->getColumn($where, 'id'));
			$where['order_by_id'] = array_slice($ids, 0, $limit);
			$order = '';
		} elseif ($order_type == 1) {//最新
			$order = 'id desc,sort desc';
		} else if ($order_type == 2) {//推荐
			$where['is_recommend'] = 1;
			$order = 'is_recommend desc,sort desc,id desc';
		} else {
			$order = 'sort desc,id desc';
		}
		$list = $this->dao->getList($where, $field, $page, $limit, $order);
		if ($list) {
			/** @var LiveRoomServices $liveServices */
			$liveServices = app()->make(LiveRoomServices::class);
			$liveCount = $liveServices->count(['is_show' => 1, 'is_del' => 0, 'status' => 1, 'live_status' => [101, 105, 106]]);
			$is_live = (bool)$liveCount;
			$site_name = sys_config('site_name');
			$site_image = sys_config('wap_login_logo');
			$userLike = $userCollect = [];
			$ids = array_column($list, 'id');
			if ($uid) {
				/** @var UserRelationServices $userRelationServices */
				$userRelationServices = app()->make(UserRelationServices::class);
				$userLike = $userRelationServices->getColumn([['uid', '=', $uid], ['relation_id', 'in', $ids], ['category', '=', 'video'], ['type', '=', 'like']], 'id,relation_id', 'relation_id');
				$userCollect = $userRelationServices->getColumn([['uid', '=', $uid], ['relation_id', 'in', $ids], ['category', '=', 'video'], ['type', '=', 'collect']], 'id,relation_id', 'relation_id');
			}
			/** @var StoreProductServices $storeProductServices */
			$storeProductServices = app()->make(StoreProductServices::class);
			$productWhere = ['is_show' => 1, 'is_del' => 0];
			foreach ($list as &$item) {
				$item['product_id'] = is_string($item['product_id']) ? explode(',', $item['product_id']) : $item['product_id'];
				$item['product_num'] = count($item['product_id']);
				//过滤下架、审核中等商品
				$item['product_num'] = $item['product_num'] ? $storeProductServices->getCount($productWhere + ['ids' => $item['product_id']]) : 0;
				$item['type_name'] = $site_name;
				$item['type_image'] = $site_image;
				$item['date'] = $item['add_time'] ? date('m月d日', $item['add_time']) : '';
				$item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '';
				$item['is_like'] = isset($userLike[$item['id']]);
				$item['is_collect'] = isset($userCollect[$item['id']]);
				//app需要
				$item['id'] = (string)$item['id'];
				$item = array_merge($item, $this->videoDefault);
				$item['is_live'] = $is_live;
			}
			//增加浏览量
			VideoJob::dispatchDo('setVideoPlayNum', [$ids, $uid]);
		}
		return $list;
	}

	/**
 	* diy获取短视频
	* @param int $uid
	* @param string $field
	* @param int $order_type
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getDiyVideoList(int $uid, string $field = '*', int $order_type = 0)
	{
		//短视频未启用
		if (!sys_config('video_func_status', 1)) {
			return [];
		}
		$where = ['is_show' => 1, 'is_del' => 0];
		[$page, $limit] = $this->getPageValue();
		//限制一次最多请求条数
		if ($limit > 10) $limit = 10;
		if ($order_type == 1) {//最新
			$order = 'id desc,sort desc';
		} else if ($order_type == 2) {//推荐
			$where['is_recommend'] = 1;
			$order = 'is_recommend desc,sort desc,id desc';
		} else {
			$order = 'sort desc,id desc';
		}
		$list = $this->dao->getList($where, $field, $page, $limit, $order);
		if ($list) {
			$site_name = sys_config('site_name');
			$site_image = sys_config('wap_login_logo');
			/** @var StoreProductServices $productServices */
			$productServices = app()->make(StoreProductServices::class);
			$product_where = ['is_del' => 0, 'is_show' => 1];
			foreach ($list as &$item) {
				$item['product_id'] = is_string($item['product_id']) ? explode(',', $item['product_id']) : $item['product_id'];
				$item['product_info'] = [];
				$item['product_num'] = 0;
				if ($item['product_id']) {
					$item['product_info'] = $productServices->getSearchList($product_where + ['ids' => $item['product_id']], 0, 0, ['id', 'store_name', 'image', 'price'], '', []);
					$item['product_num'] = count($item['product_info']);
				}
				$item['type_name'] = $site_name;
				$item['type_image'] = $site_image;
				$item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '';
			}
			$ids = array_column($list, 'id');
			//增加浏览量
			VideoJob::dispatchDo('setVideoPlayNum', [$ids, $uid]);
		}
		return $list;
	}

	/**
 	* 用户点赞、收藏、分享、浏览播放视频
	* @param int $uid
	* @param int $id
	* @param string $type
	* @param int $num
	* @return bool
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function userRelationVideo(int $uid, int $id, string $type = 'like', int $num = 1)
	{
		$info = $this->getVideoInfo((int)$id);
		$data = ['uid' => $uid, 'relation_id' => $id, 'category' => UserRelationServices::CATEGORY_VIDEO, 'type' => $type];
		$typeArr = UserRelationServices::TYPE_NAME;
		if (!isset($typeArr[$type])) {
			throw new ValidateException('类型错误');
		}
		/** @var UserRelationServices $userRelationServices */
		$userRelationServices = app()->make(UserRelationServices::class);
		//播放 每一次都是增加数量
		if ($type != 'play') {
			$relation = $userRelationServices->get($data);
		} else {
			$relation = [];
		}
		$field = $type . '_num';
		$balance = $info[$field] ?? 0;
		if ($relation) {//取消
			$userRelationServices->delete($relation['id']);
			$new = (int)bcsub((string)$balance, (string)$num);
		} else {//增加
			$data['add_time'] = time();
			$userRelationServices->save($data);
			$new = (int)bcadd((string)$balance, (string)$num);
		}
		$new = max($new, 0);
		$this->dao->update($id, [$field => $new]);
		return true;
	}
}
