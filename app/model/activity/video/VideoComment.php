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

namespace app\model\activity\video;

use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 视频评价Model
 * Class VideoComment
 * @package app\model\activity\video
 */
class VideoComment extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'video_comment';

	/**
     * @var bool
     */
    protected $autoWriteTimestamp = false;


	/**
     * 一对一关联
     * 视频评论关联视频
     * @return \think\model\relation\HasOne
     */
    public function video()
    {
        return $this->hasOne(Video::class, 'id', 'video_id');
    }

	/**
     * 一对一关联
     * 视频评论关联用户
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }

	/**
 	* 管理回复
	* @return \think\model\relation\HasOne
	*/
	public function reply()
	{
		return $this->hasOne(self::class, 'pid', 'id')->where('is_del', 0)->where('uid', 0);
	}

	/**
     * @return \think\model\relation\hasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'pid', 'id')->where('is_del', 0);
    }

	/**
     * 商户搜索器
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('type', $value);
		} else {
			if ($value !== '') $query->where('type', $value);
		}
    }

	/**
     * 关联门店ID、供应商ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchRelationIdAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('relation_id', $value);
		} else {
			if ($value !== '') $query->where('relation_id', $value);
		}
    }


	/**
     * 上级评论搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPidAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('pid', $value);
		} else {
			if ($value !== '') $query->where('pid', $value);
		}
    }

	/**
     * 视频ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchVideoIdAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('video_id', $value);
		} else {
			if ($value) $query->where('video_id', $value);
		}
    }


	/**
     * UID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('uid', $value);
		} else {
			if ($value !== '') $query->where('uid', $value);
		}
    }

	/**
     * is_reply搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsReplyAttr($query, $value)
    {
		if ($value !== '') $query->where('is_reply', $value);
    }

	/**
     * is_del搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
		if ($value !== '') $query->where('is_del', $value);
    }


}
