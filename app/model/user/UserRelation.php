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
namespace app\model\user;

use app\model\activity\video\Video;
use app\model\product\product\StoreProduct;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\Model;

/**
 *  用户点赞、收藏、分享（商品、短视频）model
 * Class UserRelation
 * @package app\model\product\product
 */
class UserRelation extends BaseModel
{
    use ModelTrait;

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_relation';

	/**
	* 关联商品
	* @return \think\model\relation\HasOne
	*/
    public function product()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'relation_id');
    }

	/**
	* 关联视频
	* @return \think\model\relation\HasOne
	*/
	public function video()
	{
		return $this->hasOne(Video::class, 'id', 'relation_id');
	}

    /**
     * 用户搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        $query->where('uid', $value);
    }

    /**
     * 关联搜索器
     * @param Model $query
     * @param $value
     */
    public function searchRelationIdAttr($query, $value)
    {
        if ($value) {
            if (is_array($value)) {
                $query->whereIn('relation_id', $value);
            } else {
                $query->where('relation_id', $value);
            }
        }
    }

    /**
     * 类型搜索器
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
		if ($value) {
            if (is_array($value)) {
                $query->whereIn('type', $value);
            } else {
                $query->where('type', $value);
            }
        }
    }

    /**
     * 商品类型搜索器
     * @param Model $query
     * @param $value
     */
    public function searchCategoryAttr($query, $value)
    {
		if ($value) {
            if (is_array($value)) {
                $query->whereIn('category', $value);
            } else {
                $query->where('category', $value);
            }
        }
    }
}
