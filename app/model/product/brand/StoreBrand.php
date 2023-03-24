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

namespace app\model\product\brand;

use app\model\product\product\StoreProduct;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 商品品牌Model
 * Class StoreCategory
 * @package app\model\product\product
 */
class StoreBrand extends BaseModel
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
    protected $name = 'store_brand';

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    protected function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 获取子集分类查询条件
     * @return \think\model\relation\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'pid', 'id')->where('is_show', 1)->order('sort DESC,id DESC');
    }

    /**
     * 一对一关联
     * 商品关联品牌
     * @return \think\model\relation\HasOne
     */
    public function product()
    {
        return $this->hasMany(StoreProduct::class, 'brand_id', 'id');

    }

    /**
     * 品牌是否显示搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsShowAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('is_show', $value);
    }

	/**
     * 品牌是否显示搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsDelAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('is_del', $value);
    }

    /**
     * 分类是否显示搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchPidAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('pid', $value);
    }

    /**
     * 分类是否显示搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchBrandNameAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('brand_name', 'like', '%' . $value . '%');
    }

    /**
     * 分类是否显示搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIdAttr($query, $value)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('id', $value);
            } else {
                $query->where('id', $value);
            }
        }
    }
}
