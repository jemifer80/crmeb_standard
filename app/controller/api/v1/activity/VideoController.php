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
namespace app\controller\api\v1\activity;


use app\Request;
use app\services\activity\video\VideoCommentServices;
use app\services\activity\video\VideoServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserRelationServices;


/**
 * 短视频类
 * Class VideoController
 * @package app\api\controller\activity
 */
class VideoController
{

    protected $services;

    public function __construct(VideoServices $services)
    {
        $this->services = $services;
    }


    /**
     * 获取短视频列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list(Request $request)
    {
        [$order_type, $id] = $request->getMore([
            ['order_type', ''],
            ['id', 0]
        ], true);
        return app('json')->success($this->services->getVideoList((int)$request->uid(), '*', (int)$order_type, (int)$id));
    }

    /**
     * 获取短视频评价列表
     * @param Request $request
     * @param VideoCommentServices $commentServices
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function commentList(Request $request, VideoCommentServices $commentServices, $id)
    {
        if (!(int)$id) return app('json')->fail('缺少参数');
        return app('json')->success($commentServices->getVideoCommentList((int)$request->uid(), (int)$id));
    }

    /**
     * 获取短视频评价回复列表
     * @param Request $request
     * @param VideoCommentServices $commentServices
     * @param $pid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function commentReplyList(Request $request, VideoCommentServices $commentServices, $pid)
    {
        if (!(int)$pid) return app('json')->fail('缺少参数');
        return app('json')->success($commentServices->getVideoCommentList((int)$request->uid(), 0, (int)$pid));
    }

    /**
     * 短视频关联商品
     * @param Request $request
     * @param StoreProductServices $productServices
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function productList(Request $request, StoreProductServices $productServices, $id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        $video = $this->services->getVideoInfo((int)$id);
        $list = [];
        $count = 0;
        if ($video['product_id']) {
            $ids = is_string($video['product_id']) ? explode(',', $video['product_id']) : $video['product_id'];
            $where = ['ids' => $ids];
            $list = $productServices->getGoodsList($where, (int)$request->uid());
            $newList = [];
            foreach ($list as $key => $item) {
                $item['promotions'] = !isset($item['promotions']) || !$item['promotions'] ? (object)[] : $item['promotions'];
                if ($item['relation_id'] && $item['type'] == 1) {
                    $item['store_id'] = $item['relation_id'];
                } else {
                    $item['store_id'] = 0;
                }
                $newList[$key] = $item;
            }
            $where['is_verify'] = 1;
            $where['is_show'] = 1;
            $where['is_del'] = 0;
            $count = $productServices->getCount($where);
            $list = get_thumb_water($newList, 'small');
        }
        return app('json')->success(compact('list', 'count'));
    }

    /**
     * 短视频点赞、收藏、分享(再次点击取消)
     * @param Request $request
     * @param $id
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function relation(Request $request, $id, $type = 'like')
    {
        if (!$id) return app('json')->fail('缺少参数');
        if (!in_array($type, ['like', 'collect', 'share'])) {
            return app('json')->fail('类型错误');
        }
        $uid = (int)$request->uid();
        $this->services->userRelationVideo($uid, $id, $type);
        return app('json')->success();
    }

    /**
     * 保存评价
     * @param Request $request
     * @param VideoCommentServices $commentServices
     * @param $video_id
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveComment(Request $request, VideoCommentServices $commentServices, $id, $pid = 0)
    {
        if (!$id) return app('json')->fail('缺少参数');
        [$content] = $request->getMore([
            ['content', '']
        ], true);
        if (!$content) {
            return app('json')->fail('请输入评论内容');
        }
        $uid = (int)$request->uid();
        $comment = $commentServices->saveComment($uid, (int)$id, (int)$pid, ['content' => $content, 'ip' => $request->ip()]);
        return app('json')->success('评价成功', $comment->toArray());
    }


    /**
     * 评论点赞(再次点击取消)
     * @param Request $request
     * @param UserRelationServices $userRelationServices
     * @param $id
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function commentRelation(Request $request, UserRelationServices $userRelationServices, VideoCommentServices $commentServices, $id, $type = 'like')
    {
        if (!$id) return app('json')->fail('缺少参数');
        if (!in_array($type, ['like', 'collect', 'share'])) {
            return app('json')->fail('类型错误');
        }
        $comment = $commentServices->get($id);
        if (!$comment) {
            return app('json')->fail('评论不存在或已删除');
        }
        $uid = (int)$request->uid();
        $data = ['uid' => $uid, 'relation_id' => $id, 'category' => UserRelationServices::CATEGORY_VIDEO_COMMENT, 'type' => $type];

        $relation = $userRelationServices->get($data);
        if ($relation) {//取消
            $userRelationServices->delete($relation['id']);
            $commentServices->bcDec($id, $type . '_num', 1);
            $status = 0;
        } else {
            $data['add_time'] = time();
            $userRelationServices->save($data);
            $commentServices->bcInc($id, $type . '_num', 1);
            $status = 1;
        }
        return app('json')->success(($status == 1 ? '取消' : '') . (UserRelationServices::TYPE_NAME[$type] ?? '收藏') . '成功');
    }

    /**
     * 撤销评价
     * @param Request $request
     * @param VideoCommentServices $commentServices
     * @param $id
     * @return mixed
     */
    public function commentDelete(Request $request, VideoCommentServices $commentServices, $id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        $comment = $commentServices->get($id);
        if (!$comment) {
            return app('json')->fail('评论不存在或已删除');
        }
        $uid = (int)$request->uid();
        if ($comment['uid'] != $uid) {
            return app('json')->fail('只能撤销自己的评价');
        }
        $commentServices->update($id, ['is_del' => 1]);
        $this->services->bcDec($comment['video_id'], 'comment_num', 1);
        return app('json')->success('撤销成功');
    }

}
