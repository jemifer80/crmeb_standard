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
use app\model\activity\video\Video;

/**
 * 视频
 * Class VideoDao
 * @package app\dao\activity\video
 */
class VideoDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return Video::class;
    }

	/**
	* @param array $where
	* @return \crmeb\basic\BaseModel|mixed|\think\Model
	 */
	public function search(array $where = []){
	 	return parent::search($where)->when(isset($where['keyword']) && $where['keyword'], function ($query) use ($where) {
			 $keyword = $where['keyword'];
			 $query->where(function ($q) use ($keyword) {
				 $q->whereOr('id|desc', 'like', '%' . $keyword . '%')->whereOr('id', 'in', function ($c) use ($keyword) {
					 $c->name('video_comment')->field('video_id')->whereLike('video_id|uid|content', '%' . $keyword . '%');
				 });
			 });
	 	})->when(isset($where['order_by_id']) && $where['order_by_id'], function ($query) use ($where) {
			 $query->where('id', 'in', $where['order_by_id'])->orderField('id', $where['order_by_id']);
	 	});
	}

    /**
	* 视频列表
	* @param array $where
	* @param string $field
	* @param int $page
	* @param int $limit
 	* @param string $order
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = 'sort desc,id desc')
    {
        return $this->search($where)->field($field)
            ->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->order($order)->select()->toArray();
    }

}
