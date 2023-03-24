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

namespace app\dao\supplier;

use app\dao\BaseDao;
use app\model\supplier\SystemSupplier;

/**
 * 供应商
 * Class SystemSupplierDao
 * @package app\dao\system\store
 */
class SystemSupplierDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemSupplier::class;
    }

    /**
     * 列表
     * @param array $where
     * @param array $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSupplierList(array $where, array $field, int $page = 0, int $limit = 10, string $order = 'id desc'): array
    {
        return $this->search($where)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when(isset($order), function ($query) use ($order) {
            $query->order($order);
        })->field($field)->select()->toArray();
    }
}
