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

namespace app\model\other\queue;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 队列辅助表
 * Class QueueAuxiliary
 * @package app\model\other\queue
 */
class QueueAuxiliary extends BaseModel
{

    use ModelTrait;

    /**
     * 表明
     * @var string
     */
    protected $name = 'queue_auxiliary';

    protected $insert = ['add_time'];

    protected $autoWriteTimestamp = false;

    /**
     * 主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 类型搜索器
     * @param $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        $query->where('type', $value);
    }

    /**
     * 类型绑定id搜索器
     * @param $query
     * @param $value
     */
    public function searchBindingIdAttr($query, $value)
    {
        $query->where('binding_id', $value);
    }

    /**
     * 类型状态搜索器
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value) {
            if (is_array($value)) {
                $query->whereIn('status', $value);
            } else {
                $query->where('status', $value);
            }
        }
    }

    /**
     * 类型关联id搜索器
     * @param $query
     * @param $value
     */
    public function searchRelationIdAttr($query, $value)
    {
        $query->where('relation_id', $value);
    }
}