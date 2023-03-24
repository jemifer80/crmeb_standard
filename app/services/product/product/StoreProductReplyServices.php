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

namespace app\services\product\product;


use app\dao\product\product\StoreProductReplyDao;
use app\services\BaseServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\user\UserRelationServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use crmeb\traits\ServicesTrait;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\facade\Route as Url;

/**
 * Class StoreProductReplyService
 * @package app\services\product\product
 * @mixin StoreProductReplyDao
 */
class StoreProductReplyServices extends BaseServices
{
    use ServicesTrait;

    /**
     * StoreProductReplyServices constructor.
     * @param StoreProductReplyDao $dao
     */
    public function __construct(StoreProductReplyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取评论列表
     * @param array $where
     * @return array
     */
    public function sysPage(array $where)
    {
        /** @var StoreProductReplyStoreProductServices $storeProductReplyStoreProductServices */
        $storeProductReplyStoreProductServices = app()->make(StoreProductReplyStoreProductServices::class);
        $data = $storeProductReplyStoreProductServices->getProductReplyList($where, [
            'replyComment' => function ($query) {
                $query->where('uid', 0)->where('pid', 0);
            }
        ]);
        foreach ($data['list'] as &$item) {
            $item['time'] = time_tran(strtotime($item['add_time']));
            $item['create_time'] = $item['add_time'];
            $item['score'] = ($item['product_score'] + $item['service_score']) / 2;
        }
        return $data;
    }

    /**
     * 创建虚拟评论表单
     * @param int $product_id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm(int $product_id)
    {
        if ($product_id == 0) {
            $field[] = Form::frameImage('image', '商品', Url::buildUrl('admin/store.StoreProduct/index', array('fodder' => 'image')))->icon('ios-add')->width('960px')->height('560px')->modal(['footer-hide' => true])->Props(['srcKey' => 'image']);
        } else {
            $field[] = Form::hidden('product_id', $product_id);
        }
        $field[] = Form::frameImage('avatar', '用户头像', Url::buildUrl('admin/widget.images/index', array('fodder' => 'avatar')))->icon('ios-add')->width('960px')->height('505px')->modal(['footer-hide' => true]);
        $field[] = Form::input('nickname', '用户名称')->col(24);
        $field[] = Form::input('comment', '评价文字')->type('textarea');
        $field[] = Form::rate('product_score', '商品分数', 0)->allowHalf(false);
        $field[] = Form::rate('service_score', '服务分数', 0)->allowHalf(false);
        $field[] = Form::frameImages('pics', '评价图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pics', 'type' => 'many', 'maxLength' => 8)))->maxLength(8)->icon('ios-add')->width('960px')->height('505px')->modal(['footer-hide' => true])->modal(['footer-hide' => true])->props(['closeBtn' => false, 'okBtn' => false]);
        $field[] = Form::dateTime('add_time', '评论时间', '')->placeholder('请选择评论时间(不选择默认当前添加时间)');
        return create_form('添加虚拟评论', $field, Url::buildUrl('/product/reply/save_fictitious_reply'), 'POST');
    }

    /**
     * 添加虚拟评论
     * @param array $data
     */
    public function saveReply(array $data)
    {
        $time = time();
        $data['uid'] = 0;
        $data['oid'] = 0;
        $data['unique'] = uniqid();
        $data['reply_type'] = 'product';
        $data['add_time'] = empty($data['add_time']) ? $time : strtotime($data['add_time']);
        $data['pics'] = json_encode($data['pics']);
		if (isset($data['sku_unique']) && $data['sku_unique']) {
			/** @var  StoreProductAttrValueServices $productAttrValueServices */
			$productAttrValueServices = app()->make(StoreProductAttrValueServices::class);
			$attrDetail = $productAttrValueServices->getone(['product_id' => $data['product_id'], 'unique' => $data['sku_unique']]);
			$data['sku'] = $attrDetail ? $attrDetail['suk'] : '';
		}
        unset($data['image']);
        if ($data['add_time'] > $time) {
            throw new AdminException('评论时间应小于当前时间');
        }
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException('添加虚拟评论失败');
        $this->dao->cacheTag()->clear();
    }

    /**
     * 回复评论
     * @param int $replyId
     * @param string $content
     * @param int $store_id
     */
    public function setReply(int $replyId, string $content, int $store_id = 0)
    {
        if ($content == '') throw new AdminException('请输入回复内容');
        $save['content'] = $content;
        $save['create_time'] = time();
        $save['store_id'] = $store_id;
        $save['reply_id'] = $replyId;
        /** @var StoreProductReplyCommentServices $service */
        $service = app()->make(StoreProductReplyCommentServices::class);
        $where = ['reply_id' => $replyId, 'uid' => 0, 'store_id' => $store_id, 'pid' => 0];
        if ($service->count($where)) {
            $res = $service->update($where, ['content' => $content, 'update_time' => time()]);
        } else {
            $res = $service->save($save);
        }
        if (!$res) throw new AdminException('回复失败，请稍后再试');
        $this->dao->update($replyId, ['is_reply' => 1]);
        $this->dao->cacheTag()->clear();
    }

    /**
     * 删除
     * @param int $id
     */
    public function del(int $id)
    {
        $res = $this->dao->update($id, ['is_del' => 1]);
        if (!$res) throw new AdminException('删除失败');
        $this->dao->cacheTag()->clear();
    }

    /**
     * @param int $productId
     * @param int $limit
     * @return mixed
     * @throws \Throwable
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/3
     */
    public function getRecProductReplyCache(int $productId, int $limit = 1)
    {
        return $this->dao->cacheTag()->remember('id:' . $productId . ':limit:' . $limit, function () use ($productId, $limit) {
            return $this->getRecProductReply($productId, $limit);
        }, 3600);
    }

    /**
     * 获取最近的几条评论
     * @param int $productId
     * @param int $limit
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getRecProductReply(int $productId, int $limit = 1)
    {
        $page = $limit ? 1 : 0;
        $list = $this->dao->getProductReply($productId, '*', $page, $limit);
        if ($list) {
            foreach ($list as &$item) {
                $item['suk'] = $item['sku'];
                $item['nickname'] = anonymity($item['nickname']);
                $item['merchant_reply_time'] = date('Y-m-d H:i', $item['merchant_reply_time']);
                $item['add_time'] = time_tran($item['add_time']);
                $item['star'] = bcadd($item['product_score'], $item['service_score'], 2);
                $item['star'] = bcdiv($item['star'], '2', 0);
                $item['comment'] = $item['comment'] ?: '此用户没有填写评价';
                $item['pics'] = $item['pics'] ? (is_string($item['pics']) ? json_decode($item['pics'], true) : $item['pics']) : [];
            }
        }
        return $list;
    }

    /**
     * 获取好评率
     * @param int $id
     * @return int|string
     */
    public function getProductReplyChance(int $id)
    {
        $replyCount = $this->dao->replyCount($id);
        if ($replyCount) {
            $goodReply = $this->dao->replyCount($id, 1);
            if ($goodReply) {
                $replyCount = bcdiv((string)$goodReply, (string)$replyCount, 2);
                $replyCount = bcmul((string)$replyCount, '100', 0);
            } else {
                $replyCount = 0;
            }
        } else {
            $replyCount = 100;
        }
        return $replyCount;
    }

    /**
     * 获取评论数据 评论总数 好评总数 好评率
     * @param int $id
     * @return array
     */
    public function getProductReplyData(int $id)
    {
        return $this->dao->cacheTag()->remember('ReplyData_' . $id, function () use ($id) {
            $goodReply = $replyChance = 0;
            $replyCount = $this->dao->replyCount($id);
            if ($replyCount) {
                $goodReply = $this->dao->replyCount($id, 1);
                if ($goodReply) {
                    $replyChance = bcmul((string)bcdiv((string)$goodReply, (string)$replyCount, 2), '100', 0);
                } else {
                    $replyChance = 0;
                }
            } else {
                $replyChance = 100;
            }
            return [$replyCount, $goodReply, $replyChance];
        }, 3600);
    }

    /**
     * 商品评论数量
     * @param int $store_id
     * @return int
     */
    public function replyCount(int $store_id = 0)
    {
        return $this->dao->count(['is_reply' => 0, 'is_del' => 0, 'store_id' => $store_id]);
    }

    /**
     * 获取商品评论数量
     * @param int $id
     * @return mixed
     */
    public function productReplyCount(int $id)
    {

        $data['sum_count'] = $this->dao->replyCount($id);
        $data['good_count'] = $this->dao->replyCount($id, 1);
        $data['in_count'] = $this->dao->replyCount($id, 2);
        $data['poor_count'] = $this->dao->replyCount($id, 3);
        if ($data['sum_count'] != 0) {
            $data['reply_chance'] = bcdiv($data['good_count'], $data['sum_count'], 2);
            $data['reply_star'] = bcdiv(($this->dao->sum(['product_id' => $id, 'is_del' => 0], 'product_score') + $this->dao->sum(['product_id' => $id, 'is_del' => 0], 'service_score')), $data['sum_count'] * 2, 0);
        } else {
            $data['reply_chance'] = 100;
            $data['reply_star'] = 5;
        }
//        $data['reply_star'] = bcmul($data['reply_chance'], 5, 0);
        $data['reply_chance'] = $data['sum_count'] == 0 ? 100 : bcmul($data['reply_chance'], 100, 0);
        return $data;
    }

    /**
     * 获取商品评论列表
     * @param int $id
     * @param int $type
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getProductReplyList(int $id, int $type)
    {
        if (!$id) {
            return [];
        }
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->replyList($id, $type, $page, $limit);
        foreach ($list as &$item) {
            $item['suk'] = $item['sku'];
            $item['nickname'] = anonymity($item['nickname']);
            $item['merchant_reply_time'] = date('Y-m-d H:i', $item['merchant_reply_time']);
            $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
            $item['star'] = bcadd($item['product_score'], $item['service_score'], 2);
            $item['star'] = bcdiv($item['star'], 2, 0);
            $item['comment'] = $item['comment'] ?: '此用户没有填写评价';
            $item['pics'] = is_string($item['pics']) ? json_decode($item['pics'], true) : $item['pics'];
        }
        return $list;
    }

    /**
     * 评价点赞
     * @param int $id
     * @param int $uid
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function replyPraise(int $id, int $uid)
    {
        $relyInfo = $this->dao->get($id, ['id', 'praise']);
        if (!$relyInfo) {
            throw new ValidateException('点赞的评价不存在');
        }
        /** @var UserRelationServices $service */
        $service = app()->make(UserRelationServices::class);
        if ($service->getUserCount($uid, $id, UserRelationServices::TYPE_COLLECT, UserRelationServices::CATEGORY_REPLY)) {
            return true;
        }
        $relyInfo->praise++;
        $this->transaction(function () use ($uid, $relyInfo, $service, $id) {
            $res = $service->save([
                'uid' => $uid,
                'relation_id' => $id,
                'type' => UserRelationServices::TYPE_LIKE,
                'category' => UserRelationServices::CATEGORY_REPLY,
                'add_time' => time()
            ]);
            $res = $res && $relyInfo->save();
            if (!$res) {
                throw new ValidateException('点赞失败');
            }
        });
        event('product.reply.update', [$uid]);
        $this->dao->cacheTag()->clear();
        return true;
    }

    /**
     * 取消点赞
     * @param int $id
     * @param int $uid
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function unReplyPraise(int $id, int $uid)
    {
        $relyInfo = $this->dao->get($id, ['id', 'praise']);
        if (!$relyInfo) {
            throw new ValidateException('点赞的评价不存在');
        }
        /** @var UserRelationServices $service */
        $service = app()->make(UserRelationServices::class);
        $relyInfo->praise--;
        $this->transaction(function () use ($uid, $relyInfo, $service, $id) {
            $res = $service->delete([
                'uid' => $uid,
                'relation_id' => $id,
                'type' => UserRelationServices::TYPE_LIKE,
                'category' => UserRelationServices::CATEGORY_REPLY
            ]);
            $res = $res && $relyInfo->save();
            if (!$res) {
                throw new ValidateException('取消点赞失败');
            }
        });
        event('product.reply.update', [$uid]);
        $this->dao->cacheTag()->clear();
        return true;
    }

    /**
     * 获取评论详情
     * @param int $id
     * @param int $uid
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getReplyInfo(int $id, int $uid)
    {
        $replyInfo = $this->dao->get($id, ['*']);
        if (!$replyInfo) {
            throw new ValidateException('查看的评论不存在');
        }
        /** @var StoreProductServices $productServices */
        $productServices = app()->make(StoreProductServices::class);
        $productInfo = $productServices->get($replyInfo->product_id, ['image', 'store_name', 'id']);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userInfo = $userService->get($replyInfo->uid, ['nickname', 'uid', 'avatar', 'is_money_level']);
        $userInfo = $userInfo ? $userInfo->toArray() : [];
        $data['reply'] = $replyInfo->toArray();
        $data['reply']['add_time'] = $data['reply']['add_time'] ? date('Y-m-d H:i:s', $data['reply']['add_time']) : '';
        $data['reply']['suk'] = $replyInfo['sku'] ?? '';
        /** @var StoreProductReplyCommentServices $commentService */
        $commentService = app()->make(StoreProductReplyCommentServices::class);
        $data['reply']['comment_sum'] = $commentService->count(['reply_id' => $id, 'pid' => 0]);
        $data['product'] = $productInfo ? $productInfo->toArray() : [];
        $data['user'] = $userInfo;
        $data['star'] = bcdiv(bcadd($data['reply']['product_score'], $data['reply']['service_score'], 2), 2, 0);
        /** @var UserRelationServices $make */
        $make = app()->make(UserRelationServices::class);
        $data['is_praise'] = !!$make->getUserCount($uid, $id, UserRelationServices::TYPE_LIKE, UserRelationServices::CATEGORY_REPLY);
        //记录浏览量
        $replyInfo->views_num++;
        $replyInfo->save();
        return $data;
    }

    /**
     * 新版本获取商品评价
     * @param int $id
     * @param int $type
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getNewProductReplyList(int $id, int $type, int $uid)
    {
        if (!$id) {
            return [];
        }
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->replyList($id, $type, $page, $limit, ['userInfo',
            'replyComment' => function ($query) {
                $query->with([
                    'user' => function ($query) {
                        $query->field('uid,avatar,nickname');
                    }
                ])->where('pid', 0)->field(['uid', 'reply_id', 'content'])
                    ->order('praise desc,create_time desc');
            },
            'productRelation' => function ($query) use ($uid) {
                $query->where('uid', $uid)->where('type', UserRelationServices::TYPE_LIKE)->where('category', UserRelationServices::CATEGORY_REPLY)->field(['uid', 'relation_id']);
            }
        ]);
        if ($list) {
            $siteLogoSquare = sys_config('site_logo_square');
            $siteName = sys_config('site_name');
            $replyId = array_column($list, 'id');
            /** @var StoreProductReplyCommentServices $make */
            $make = app()->make(StoreProductReplyCommentServices::class);
            $replySum = $make->getReplyCommentCountList($replyId);
            foreach ($list as &$item) {
                $item['suk'] = $item['sku'];
                $item['nickname'] = anonymity($item['nickname']);
                $item['merchant_reply_time'] = date('Y-m-d H:i', $item['merchant_reply_time']);
                $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
                $item['star'] = bcadd($item['product_score'], $item['service_score'], 2);
                $item['star'] = bcdiv($item['star'], 2, 0);
                $item['comment'] = $item['comment'] ?: '此用户没有填写评价';
				$item['pics'] = $item['pics'] ? (is_string($item['pics']) ? json_decode($item['pics'], true) : $item['pics']) : [];
                if (isset($item['replyComment']) && !$item['replyComment']['user'] && $item['replyComment']['uid'] === 0) {
                    $item['replyComment']['user'] = ['nickname' => $siteName, 'avatar' => $siteLogoSquare];
                }
                if ($uid) {
                    $item['is_praise'] = !empty($item['productRelation']);
                } else {
                    $item['is_praise'] = false;
                }
                if (isset($item['replyComment'])) {
                    foreach ($replySum as $value) {
                        if ($item['id'] === $value['reply_id']) {
                            $item['replyComment']['sum'] = $value['sum'];
                        }
                    }
                }
            }
        }

        return $list;
    }

}
