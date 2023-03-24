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

use app\dao\activity\video\VideoCommentDao;
use app\services\BaseServices;
use app\services\user\UserRelationServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\exception\ValidateException;
use think\facade\Route as Url;


/**
 * Class VideoCommentServices
 * @package app\services\activity\video
 * @mixin VideoCommentDao
 */
class VideoCommentServices extends BaseServices
{

    /**
     * VideoCommentServices constructor.
     * @param VideoCommentDao $dao
     */
    public function __construct(VideoCommentDao $dao)
    {
        $this->dao = $dao;
    }

	/**
 	* 视频评价列表
	* @param array $where
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function sysPage(array $where)
	{
		$where['pid'] = 0;
		$where['is_del'] = 0;
		[$page, $limit] = $this->getPageValue();
		$with = ['video', 'reply' => function($query) {
			$query->field('id,pid,content')->bind(['reply' => 'content']);
		}];
        $list = $this->dao->getList($where, '*', $with, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
	}

	/**
     * 回复评论
     * @param int $id
     * @param string $content
     */
    public function setReply(int $id, string $content)
    {
        if ($content == '') throw new AdminException('请输入回复内容');
		$comment = $this->dao->get($id);
		if (!$comment) {
			throw new AdminException('回复的评论不存在');
		}
		$save = [];
		$save['type'] = $comment['type'];
		$save['relation_id'] = $comment['relation_id'];
		$save['pid'] = $comment['pid'] ? $comment['pid'] : $id;
		$save['video_id'] = $comment['video_id'];
        $save['content'] = $content;
        $save['nickname'] = sys_config('site_name');
        $save['avatar'] = sys_config('wap_login_logo');
        $where = ['video_id' => $comment['video_id'], 'uid' => 0, 'pid' => $id,'is_del' => 0];
        if ($this->dao->count($where)) {
            $res = $this->dao->update($where, ['content' => $content, 'update_time' => time()]);
        } else {
			$save['add_time'] = time();
            $res = $this->dao->save($save);
        }
        if (!$res) throw new AdminException('回复失败，请稍后再试');
		$this->dao->update($id, ['is_reply' => 1]);
		return true;
    }


	/**
	* 获取评论回复列表
	* @param int $id
	* @param array $where
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getCommentReplyList(int $id, array $where)
	{
		$comment = $this->dao->get($id);
		if (!$comment) {
			throw new AdminException('回复的评论不存在');
		}
		$where['pid'] = $id;
		$where['is_del'] = 0;
		[$page, $limit] = $this->getPageValue();
		$list = $this->dao->getList($where, '*', ['video', 'user', 'children' => function ($query) {
                $query->with([
                    'user' => function ($query) {
                        $query->field('uid,avatar,nickname,is_money_level');
                    }
                ]);
            }], $page, $limit);
		$count = $this->dao->count($where);
		return compact('list', 'count');
	}

	/**
 	* 创建虚拟评论表单
	* @param int $video_id
	* @param $store_id
	* @return mixed
	 */
    public function createForm(int $video_id, $store_id = 0)
    {
        if ($video_id == 0) {
            $field[] = Form::frameImage('video', '视频', Url::buildUrl($store_id ? 'store/video.shortVideo/index' : 'admin/video.shortVideo/index', array('fodder' => 'video')))->icon('ios-add')->width('960px')->height('560px')->modal(['footer-hide' => true])->Props(['srcKey' => 'image']);
        } else {
            $field[] = Form::hidden('video_id', $video_id);
        }
        $field[] = Form::frameImage('avatar', '用户头像', Url::buildUrl($store_id ? 'store/widget.images/index' : 'admin/widget.images/index', array('fodder' => 'avatar')))->icon('ios-add')->width('960px')->height('505px')->modal(['footer-hide' => true]);
        $field[] = Form::input('nickname', '用户名称')->col(24);
        $field[] = Form::input('content', '评价文字')->type('textarea');
		$field[] = Form::dateTime('add_time', '评论时间', '')->placeholder('请选择评论时间(不选择默认当前添加时间)');
        return create_form('添加虚拟评论', $field, Url::buildUrl('/marketing/video/comment/save_fictitious'), 'POST');
    }

    /**
 	* 保存评价、回复
	* @param int $uid
	* @param int $video_id
	* @param $id
	* @param array $data
	* @return \crmeb\basic\BaseModel|\think\Model
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
    public function saveComment(int $uid, int $video_id, $id, array $data)
    {
		/** @var VideoServices $videoServices */
		$videoServices = app()->make(VideoServices::class);
		$video = $videoServices->get($video_id);
		if (!$video) {
			throw new AdminException('评论的视频不存在');
		}
		$pid = $id;
		if ($id) {//回复评价
			$comment = $this->dao->get($id);
			if (!$comment) {
				return app('json')->fail('评论不存在或已删除');
			}
			if ($comment['pid']) $pid = $comment['pid'];
		}
		if ($uid && $uid != -1) {
			/** @var UserServices $userServices */
			$userServices = app()->make(UserServices::class);
			$userInfo = $userServices->getUserInfo($uid);
			if (!$userInfo) {
				return app('json')->fail('用户不存在');
			}
			$data['nickname'] = $userInfo['nickname'] ?? '';
			$data['avatar'] = $userInfo['avatar'] ?? '';
		}
		//ip转城市信息
		if (isset($data['ip']) && $data['ip']) {
			$data['city'] = $this->convertIp($data['ip']);
		}
		$data['uid'] = $uid;
		$data['video_id'] = $video_id;
		$data['pid'] = $pid;
		$data['type'] = $video['type'];
		$data['relation_id'] = $video['relation_id'];
		$time = time();
		if (isset($data['add_time']) && $data['add_time']) {
			if ($data['add_time'] > $time) {
				throw new AdminException('评论时间应小于当前时间');
			}
			$data['add_time'] = strtotime($data['add_time']);
		} else {
			$data['add_time'] = $time;
		}
        $res = $this->dao->save($data);
		/** @var VideoServices $videoServices */
		$videoServices = app()->make(VideoServices::class);
		$videoServices->bcInc($video_id, 'comment_num', 1);
        if (!$res) throw new AdminException('保存评论失败');
		return $res;
    }

	/**
 	* 获取短视频评价列表
	* @param int $uid
	* @param int $id
 	* @param int $pid
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function getVideoCommentList(int $uid, int $id, int $pid = 0)
	{
		$where = ['video_id' => $id, 'is_del' => 0];
		[$page, $limit] = $this->getPageValue();
		if ($limit > 20)  $limit = 20;

		if ($pid) {
			$comment = $this->dao->get($pid);
			if (!$comment) {
				throw new ValidateException('评论不存在或已删除');
			}
			$where['video_id'] = $comment['video_id'];
		}
		$where['pid'] = $pid;
		$list = $this->dao->getList($where, 'id,pid,video_id,uid,nickname,avatar,content,like_num,city,add_time', ['children' => function($query) {
			$query->field("id,pid")->with([
				'user' => function ($query) {
					$query->field('uid,avatar,nickname,is_money_level')->bind(['is_money_level']);
				}
			]);
		}, 'user' => function ($query) {
                $query->field('uid,avatar,nickname,is_money_level')->bind(['is_money_level']);
        }], $page, $limit);
		if ($list) {
			$userLike = [];
			if ($uid) {
				$ids = array_column($list, 'id');
				/** @var UserRelationServices $userRelationServices */
				$userRelationServices = app()->make(UserRelationServices::class);
				$userLike = $userRelationServices->getColumn([['uid', '=', $uid], ['relation_id', 'in', $ids], ['category', '=', 'video_comment'], ['type', '=', 'like']], 'id,relation_id', 'relation_id');
			}
			foreach ($list as &$item) {
				$item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '';
				$item['is_like'] = isset($userLike[$item['id']]);
				$item['reply'] = [];
				$item['reply_count'] = isset($item['children']) ? (int)count($item['children']) : 0;
				$item['city'] = $this->addressHandle($item['city'])['city'] ?? '';
				unset($item['children']);
			}
		}
		return $list;
	}


}
