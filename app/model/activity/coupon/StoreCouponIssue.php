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

namespace app\model\activity\coupon;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 发布优惠券Model
 * Class StoreCouponIssue
 * @package app\model\activity\coupon
 */
class StoreCouponIssue extends BaseModel
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
    protected $name = 'store_coupon_issue';

    /**
     * 用户是否拥有
     * @return \think\model\relation\HasOne
     */
    public function used()
    {
        return $this->hasOne(StoreCouponUser::class, 'cid', 'id')->field('id,cid');
    }

    /**
     * id
     * @param Model $query
     * @param $value
     */
    public function searchIdAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('id', $value);
        else
            $query->where('id', $value);
    }

    /**
     * 优惠券模板搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCidAttr($query, $value, $data)
    {
        if ($value != '') $query->where('cid', $value);
    }

    /**
     * type
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value) { 
            if (is_array($value))
                $query->whereIn('type', $value);
            else
                $query->where('type', $value);
        }
    }

    /**
     * 优惠类型搜索器
     * @param Model $query
     * @param $value
     */
    public function searchCouponTypeAttr($query, $value)
    {
        if ($value != '') $query->where('coupon_type', $value);
    }

    /**
     * receive_type
     * @param Model $query
     * @param $value
     */
    public function searchReceiveTypeAttr($query, $value)
    {
        if ($value) {
            if (is_array($value))
                $query->whereIn('receive_type', $value);
            else
                $query->where('receive_type', $value);
        }
    }

    /**
     * 优惠券是否不限量
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsPermanentAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('is_permanent', $value);
    }

    /**
     * 优惠券是否新人券
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsGiveSubscribeAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('is_give_subscribe', $value);
    }

    /**
     * 优惠券是否满赠
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsFullGiveAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('is_full_give', $value);
    }

    /**
     * 优惠券状态
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value != '') $query->where('status', $value);
    }

    /**
     * 优惠券是否删除
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsDelAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('is_del', $value ?? 0);
    }

    /**
     * 优惠券名称
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCouponTitleAttr($query, $value, $data)
    {
        if ($value) $query->whereLike('coupon_title', '%' . $value . '%');
    }
}
