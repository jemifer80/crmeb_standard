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
namespace app\model\product\shipping;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\Model;

/**
 * 地区运费Model
 * Class ShippingTemplatesRegion
 * @package app\model\product\shipping
 */
class ShippingTemplatesRegion extends BaseModel
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
    protected $name = 'shipping_templates_region';

    /**
     * 城市ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCityIdAttr($query, $value)
    {
        $query->where('city_id', $value);
    }

    /**
     * uniqid 搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUniqidAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uniqid', $value);
        } else {
            $query->where('uniqid', $value);
        }
    }

    /**
     * 模板id搜索
     * @param Model $query
     * @param $value
     */
    public function searchTempIdAttr($query, $value)
    {
        $query->where('temp_id', $value);
    }
}
