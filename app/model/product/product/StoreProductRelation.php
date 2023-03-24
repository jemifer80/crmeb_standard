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

namespace app\model\product\product;

use app\model\product\brand\StoreBrand;
use app\model\product\category\StoreCategory;
use app\model\product\ensure\StoreProductEnsure;
use app\model\product\label\StoreProductLabel;
use app\model\product\specs\StoreProductSpecs;
use app\model\user\label\UserLabel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 *  商品关联模型
 * Class StoreProductRelation
 * @package app\model\product\product
 */
class StoreProductRelation extends Model
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
    protected $name = 'store_product_relation';

    /**
     * 一对一关联获取分类名称
     * @return \think\model\relation\HasOne
     */
    public function cateName()
    {
        return $this->hasOne(StoreCategory::class, 'id', 'relation_id')->bind([
            'cate_name' => 'cate_name'
        ]);
    }

	/**
     * 一对一关联获取分类名称
     * @return \think\model\relation\HasOne
     */
    public function cate()
    {
        return $this->hasOne(StoreCategory::class, 'id', 'relation_id');
    }

	/**
 	* 品牌
	* @return \think\model\relation\HasOne
	*/
	public function brand()
	{
		return $this->hasOne(StoreBrand::class, 'id', 'relation_id');
	}

	/**
 	* 商品标签
	* @return \think\model\relation\HasOne
	*/
	public function productLabel()
	{
		return $this->hasOne(StoreProductLabel::class, 'id', 'relation_id');
	}

	/**
 	* 用户标签
	* @return \think\model\relation\HasOne
	*/
	public function userLabel()
	{
		return $this->hasOne(UserLabel::class, 'id', 'relation_id');
	}

	/**
 	* 用户保障服务
	* @return \think\model\relation\HasOne
	*/
	public function ensure()
	{
		return $this->hasOne(StoreProductEnsure::class, 'id', 'relation_id');
	}

	/**
 	* 用户保障服务
	* @return \think\model\relation\HasOne
	*/
	public function specs()
	{
		return $this->hasOne(StoreProductSpecs::class, 'id', 'relation_id');
	}

	/**
     * 商品ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchProductIdAttr($query, $value)
    {
		if ($value) {
            if (is_array($value)) {
                $query->whereIn('product_id', $value);
            } else {
                $query->where('product_id', $value);
            }
        }
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

}
