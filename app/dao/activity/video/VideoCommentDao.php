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

namespace app\dao\activity\video;

use app\dao\BaseDao;
use app\model\activity\video\VideoComment;

/**
 * 视频评论
 * Class VideoCommentDao
 * @package app\dao\activity\video
 */
class VideoCommentDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return VideoComment::class;
    }

	/**
	* @param array $where
	* @return \crmeb\basic\BaseModel|mixed|\think\Model
	 */
	public function search(array $where = []){
	 	return parent::search($where)->when(isset($where['keyword']) && $where['keyword'], function ($query) use ($where) {
				 $keyword = $where['keyword'];
				 $query->where(function ($q) use ($keyword) {
					 $q->whereOr('video_id|uid|content', 'like', '%' . $keyword . '%')->whereOr('video_id', 'in', function ($c) use ($keyword) {
						 $c->name('video')->field('id')->whereLike('id|desc', '%' . $keyword . '%');
					 });
				 });
			 });
	}

	/**
	* 视频评论列表
	* @param array $where
	* @param string $field
 	* @param array $with
	* @param int $page
	* @param int $limit
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
    public function getList(array $where, string $field = '*', array $with = [], int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)
        	->when($with, function ($query) use ($with) {
				$query->with($with);
        	})->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->order('id desc')->select()->toArray();
    }


}
