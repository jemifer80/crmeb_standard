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

namespace app\model\activity\promotions;

use app\model\activity\coupon\StoreCouponIssue;
use app\model\product\product\StoreProduct;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 促销活动活动
 * Class StorePromotions
 * @package app\model\activity\promotions
 */
class StorePromotions extends BaseModel
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
    protected $name = 'store_promotions';

    protected $updateTime = false;

    /**
     * 优惠叠加
     * @param $value
     * @return string
     */
    protected function setDiscountUseAttr($value)
    {
        if ($value) {
            return is_array($value) ? implode(',', $value) : $value;
        }
        return '';
    }

    /**
     * 优惠叠加
     * @param $value
     * @return array|false|string[]
     */
    protected function getDiscountUseAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
    }

    /**
     * 标签
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
     * 关联商品
     * @param $value
     * @return array|mixed
     */
    protected function getProductIdAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
    }

    /**
     * 叠加使用
     * @param $value
     * @return array|mixed
     */
    protected function getOverlayAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
    }

    /**
     * 赠送优惠券
     * @param $value
     * @return array|mixed
     */
    protected function getIGiveCouponIdAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
    }

    /**
     * 赠送商品
     * @param $value
     * @return array|mixed
     */
    protected function getIGiveProductIdAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
    }

    /**
     * 赠送商品
     * @param $value
     * @return array|mixed
     */
    protected function getIGiveProductUniqueAttr($value)
    {
        if ($value) {
            return is_string($value) ? explode(',', $value) : $value;
        }
        return [];
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
     * 关联商品
     * @return \think\model\relation\HasMany
     */
    public function products()
    {
        return $this->hasMany(StorePromotionsAuxiliary::class, 'promotions_id', 'id')->where('type',1)->where('product_partake_type', 2);
    }

    /**
     * 关联优惠券
     * @return \think\model\relation\HasOne
     */
    public function giveCoupon()
    {
        return $this->hasMany(StorePromotionsAuxiliary::class, 'promotions_id', 'id')->where('type',2);
    }

    /**
     * 赠送商品
     * @return \think\model\relation\HasOne
     */
    public function giveProducts()
    {
        return $this->hasMany(StorePromotionsAuxiliary::class, 'promotions_id', 'id')->where('type',3);
    }

    /**
     * 阶梯优惠
     * @return \think\model\relation\HasMany
     */
    public function promotions()
    {
        return $this->hasMany(self::class, 'pid', 'id')->order('id asc');
    }

	/**
 	* 关联品牌
	* @return \think\model\relation\HasMany
	*/
	public function brands()
	{
		return $this->hasMany(StorePromotionsAuxiliary::class, 'promotions_id', 'id')->where('type',1)->where('product_partake_type', 4);
	}


	/**
 	* 关联商品标签
	* @return \think\model\relation\HasMany
	*/
	public function productLabels()
	{
		return $this->hasMany(StorePromotionsAuxiliary::class, 'promotions_id', 'id')->where('type',1)->where('product_partake_type', 5);
	}

    /**
     * pid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPidAttr($query, $value)
    {
        if ($value !== '') $query->where('pid', $value);
    }

    /**
     * type搜索器
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value) $query->where('type', $value);
    }


    /**
     * store_id搜索器
     * @param $query
     * @param $value
     */
    public function searchStoreIdAttr($query, $value)
    {
        if ($value !== '') $query->where('store_id', $value);
    }

    /**
     * promotions_type搜索器
     * @param $query
     * @param $value
     */
    public function searchPromotionsTypeAttr($query, $value)
    {
        if ($value !== '') {
			if (is_array($value)) {
				if ($value) $query->whereIn('promotions_type', $value);
			} else {
				if ($value) $query->where('promotions_type', $value);
			}
        }
    }

    /**
     * promotions_cate搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPromotionsCateAttr($query, $value)
    {
        if ($value !== '') $query->where('promotions_cate', $value);
    }

    /**
     * name搜索器
     * @param Model $query
     * @param $value
     */
    public function searchNameAttr($query, $value)
    {
        if ($value !== '') $query->whereLike('id|name|title|desc', "%" . $value . "%");
    }

    /**
     * threshold_type搜索器
     * @param Model $query
     * @param $value
     */
    public function searchThresholdTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('threshold_type', $value);
    }

    /**
     * discount_type搜索器
     * @param Model $query
     * @param $value
     */
    public function searchDiscountTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('discount_type', $value);
    }
	
	/**
     * n_piece_n_discount搜索器
     * @param Model $query
     * @param $value
     */
    public function searchNPieceNDiscountAttr($query, $value)
    {
        if ($value !== '') $query->where('n_piece_n_discount', $value);
    }

    /**
     * product_partake_type搜索器
     * @param Model $query
     * @param $value
     */
    public function searchProductPartakeTypeAttr($query, $value)
    {
        if ($value !== '') {
			if (is_array($value)) {
				if ($value) $query->whereIn('product_partake_type', $value);
			} else {
				if ($value) $query->where('product_partake_type', $value);
			}
        }
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
     * 状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value != '') $query->where('status', $value);
    }

	/**
 	* 活动事件搜索器
	* @param $query
	* @param $value
	* @param $data
	* @return void
	*/
	public function searchActivityTimeAttr($query, $value, $data)
    {
        if ($value) {
			$startTime = $endTime = 0;
            if (is_array($value)) {
                $startTime = trim($value[0] ?? 0);
                $endTime = trim($value[1] ?? 0);
            } elseif (is_string($value)) {
				if (strstr($value, '-') !== false) {
					[$startTime, $endTime] = explode('-', $value);
					$startTime = trim($startTime);
					$endTime = trim($endTime);
				}
            }
			if ($startTime || $endTime) {
				try {
					date('Y-m-d', $startTime);
				} catch (\Throwable $e) {
					$startTime = strtotime($startTime);
				}
				try {
					date('Y-m-d', $endTime);
				} catch (\Throwable $e) {
					$endTime = strtotime($endTime);
				}
				if ($startTime == $endTime || $endTime == strtotime(date('Y-m-d', $endTime))) {
					$endTime = $endTime + 86399;
				}
				$query->where(function ($b) use($startTime, $endTime) {
					$b->whereBetween('start_time', [$startTime, $endTime])->whereOr(function ($q) use ($startTime, $endTime) {
						$q->whereBetween('stop_time', [$startTime, $endTime]);
					})->whereOr(function ($q) use ($startTime, $endTime) {
						$q->where('start_time', '<=', $startTime)->where('stop_time', '>=', $endTime);
					});
				});
			}
        }
    }

}
