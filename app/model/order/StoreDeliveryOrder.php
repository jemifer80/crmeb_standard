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

namespace app\model\order;


use app\model\store\SystemStore;
use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;


class StoreDeliveryOrder extends BaseModel
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
    protected $name = 'store_delivery_order';

    protected $updateTime = false;

	/**
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }

	/**
     * @return \think\model\relation\HasOne
     */
    public function orderInfo()
    {
        return $this->hasOne(StoreOrder::class, 'id', 'oid');
    }

	/**
	* @return \think\model\relation\HasOne
	*/
	public function storeInfo()
	{
		return $this->hasOne(SystemStore::class, 'id', 'relation_id')->where(['is_show' => 1, 'is_del' => 0]);
	}

	/**
	* @param $query
	* @param $value
	* @return void
	 */
	public function searchKeywordAttr($query, $value)
	{
		$query->where('');
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
     * 订单ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchOidAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('oid', $value);
		} else {
			if ($value !== '') $query->where('oid', $value);
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
 	* 平台类型搜索器
	* @param $query
	* @param $value
	* @return void
	 */
	public function searchStationTypeAttr($query, $value)
	{
		if ($value !== '') $query->where('station_type', $value);
	}

	/**
     * status搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('status', $value);
		} else {
			if ($value !== '') $query->where('status', $value);
		}
    }

}
