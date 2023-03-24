<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\system;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

class CapitalFlow extends BaseModel
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
    protected $name = 'capital_flow';

    /**
     * 交易类型搜索器
     * @param $query
     * @param $value
     */
    public function searchTradingTypeAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('trading_type', $value);
		} else {
			if ($value) $query->where('trading_type', $value);
		}
    }

    /**
     * 门店搜索器
     * @param $query
     * @param $value
     */
    public function searchStoreIdAttr($query, $value)
    {
        if ($value) $query->where('store_id', $value);
    }

    /**
     * 关键字搜索器
     * @param $query
     * @param $value
     */
    public function searchKeywordsAttr($query, $value)
    {
        if ($value !== '') $query->where('order_id|uid|nickname|phone', 'like', '%' . $value . '%');
    }

    /**
     * 批量id搜索器
     * @param $query
     * @param $value
     */
    public function searchIdsAttr($query, $value)
    {
        if ($value != '') $query->whereIn('id', $value);
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
}
