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

namespace app\model\activity\combination;

use app\model\product\product\StoreDescription;
use app\model\product\product\StoreProduct;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\Model;

/**
 * 拼团商品Model
 * Class StoreCombination
 * @package app\model\activity\combination
 */
class StoreCombination extends BaseModel
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
    protected $name = 'store_combination';


    /**
     * 配送信息
     * @param $value
     * @return false|string
     */
    protected function setDeliveryTypeAttr($value)
    {
        if ($value) {
            return is_array($value) ? implode(',', $value) : $value;
        }
        return '';
    }

    /**
     * 配送信息
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getDeliveryTypeAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
    }

    /**
     * 配送信息
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getCustomFormAttr($value)
    {
        if ($value) {
            return is_string($value) ? json_decode($value, true) : $value;
        }
        return [];
    }

	/**
     * @param $value
     * @return array|mixed
     */
    public function getStoreLabelIdAttr($value)
    {
        if ($value) {
            return is_string($value) ? array_map('intval', array_filter(explode(',', $value))) : $value;
        }
        return [];
    }

    /**
     * 商品标签
     * @param $value
     * @return array|mixed
     */
    protected function getLabelIdAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
    }

    /**
     * 商品保障服务
     * @param $value
     * @return array|mixed
     */
    protected function getEnsureIdAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
    }

    /**
     * 参数信息
     * @param $value
     * @return array|mixed
     */
    protected function getSpecsAttr($value)
    {
        if ($value) {
            return is_string($value) ? json_decode($value, true) : $value;
        }
        return [];
    }

    /**
     * 一对一获取原价
     * @return \think\model\relation\HasOne
     */
    public function getPrice()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id')->field(['id', 'ot_price', 'price'])->bind(['ot_price', 'product_price' => 'price']);
    }

    /**
     * 一对一关联
     * 商品关联商品商品详情
     * @return \think\model\relation\HasOne
     */
    public function total()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id')->where('is_show', 1)->where('is_del', 0)->field(['SUM(sales+ficti) as total', 'id', 'price'])->bind([
            'total' => 'total', 'product_price' => 'price'
        ]);
    }

    /**
     * 一对一关联
     * 商品关联商品商品详情
     * @return \think\model\relation\HasOne
     */
    public function descriptions()
    {
        return $this->hasOne(StoreDescription::class, 'product_id', 'id')->where('type', 3)->bind(['description']);
    }

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    protected function getAddTimeAttr($value)
    {
        if ($value) return date('Y-m-d H:i:s', (int)$value);
        return '';
    }

    /**
     * 轮播图获取器
     * @param $value
     * @return mixed
     */
    public function getImagesAttr($value)
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * 拼团商品名称搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchStoreNameAttr($query, $value, $data)
    {
        if ($value) $query->where('title|id', 'like', '%' . $value . '%');
    }

    /**
     * 是否推荐搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsHostAttr($query, $value, $data)
    {
        $query->where('is_host', $value ?? 1);
    }

    /**
     * 状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsShowAttr($query, $value, $data)
    {
        if ($value != '') $query->where('is_show', $value ?: 0);
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
}
