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

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 视频Model
 * Class StoreSeckill
 * @package app\model\activity\seckill
 */
class Video extends BaseModel
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
    protected $name = 'video';

    /**
     * 关联商品ID
     * @param $value
     * @return false|string
     */
    protected function setProductIdAttr($value)
    {
        if ($value) {
            return is_array($value) ? implode(',', $value) : $value;
        }
        return '';
    }

    /**
     * 关联商品ID
     * @param $value
     * @return mixed
     */
    protected function getProductIdAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
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
     * 状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsShowAttr($query, $value, $data)
    {
        $query->where('is_show', $value ?? 1);
    }

	/**
     * 推荐搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsRecommendAttr($query, $value, $data)
    {
        $query->where('is_recommend', $value ?? 1);
    }

	/**
     * 是否审核搜索器
     * @param $query
     * @param $value
     */
    public function searchIsVerifyAttr($query, $value)
    {
        if ($value !== '') $query->where('is_verify', $value ?? 1);
    }

    /**
     * 是否删除搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsDelAttr($query, $value, $data)
    {
        $query->where('is_del', $value ?? 0);
    }

}
