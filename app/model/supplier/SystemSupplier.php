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
namespace app\model\supplier;

use app\model\system\admin\SystemAdmin;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 供应商模型
 * Class SystemSupplier
 * @package app\model\store
 */
class SystemSupplier extends BaseModel
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
    protected $name = 'system_supplier';

    /**
     * 管理员
     * @return \think\model\relation\HasOne
     */
    public function admin()
    {
        return $this->hasOne(SystemAdmin::class, 'id', 'admin_id')->field(['id', 'account', 'pwd', 'admin_type', 'is_del', 'level', 'roles'])->bind([
            'account',
			'pwd',
            'admin_type',
            'level',
            'roles',
            'admin_is_del' => 'is_del',
        ]);
    }
    /**
     * 手机号,id,昵称搜索器
     * @param Model $query
     * @param $value
     */
    public function searchKeywordsAttr($query, $value)
    {
        if ($value != '') {
            $query->where('supplier_name', 'LIKE', "%$value%");
        }
    }

    /**
     * 是否删除搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        $query->where('is_del', $value ?? 0);
    }
}
