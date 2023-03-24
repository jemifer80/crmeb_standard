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

namespace app\model\system\attachment;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 附件管理分类模型
 * Class SystemAttachmentCategory
 * @package app\model\system\attachment
 */
class SystemAttachmentCategory extends BaseModel
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
    protected $name = 'system_attachment_category';


    /**
     * 附件分类昵称搜索器
     * @param Model $query
     * @param $value
     */
    public function searchNameAttr($query, $value)
    {
        if ($value !== '') $query->where('name', 'like', '%' . $value . '%');
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
     * FileType搜索器
     * @param Model $query
     * @param $value
     */
    public function searchFileTypeAttr($query, $value)
    {
		if (is_array($value)) {
			if ($value) $query->whereIn('file_type', $value);
		} else {
			if ($value !== '') $query->where('file_type', $value);
		}
    }

    /**
     * store_id搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStoreIdAttr($query, $value)
    {
        if ($value) $query->where('relation_id', $value)->where('type', 1);
    }

}
