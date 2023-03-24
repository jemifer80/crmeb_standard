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

namespace app\model\activity\newcomer;

use app\model\product\product\StoreDescription;
use app\model\product\product\StoreProduct;
use app\model\product\sku\StoreProductAttrValue;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 新人礼商品Model
 * Class StoreNewcomer
 * @package app\model\activity\newcomer
 */
class StoreNewcomer extends BaseModel
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
    protected $name = 'store_newcomer';

	protected $updateTime = false;


    /**
     * 一对一关联
     * 商品关联商品商品详情
     * @return \think\model\relation\HasOne
     */
    public function descriptions()
    {
        return $this->hasOne(StoreDescription::class, 'product_id', 'product_id')->where('type', 1)->bind(['description']);
    }

    /**
     * 一对一关联
     * 商品关联商品商品详情
     * @return \think\model\relation\HasOne
     */
    public function product()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id')->where('is_show', 1)->where('is_del', 0);
    }

	/**
     * sku一对多
     * @return \think\model\relation\HasMany
     */
    public function attrValue()
    {
        return $this->hasMany(StoreProductAttrValue::class, 'product_id', 'id')->where('type', 7);
    }


    /**
     * 商品ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchProductIdAttr($query, $value, $data)
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
     * 是否删除
     * @param $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('is_del', $value);
        }
    }

}
