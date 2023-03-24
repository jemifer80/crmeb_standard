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

namespace app\dao\system\config;


use app\dao\BaseDao;
use app\model\system\config\SystemStorage;
use crmeb\traits\SearchDaoTrait;

/**
 * Class SystemStorageDao
 * @package app\dao\system\config
 */
class SystemStorageDao extends BaseDao
{

    use SearchDaoTrait;

    /**
     * @return string
     */
    protected function setModel(): string
    {
        return SystemStorage::class;
    }

    /**
     * 获取分页列表
     * @param array $where
     * @param array $field
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStorageList(array $where, array $field = ['*'], int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time desc')->select()->toArray();
    }

    /**
     * @param array $where
     * @return int
     */
    public function getCount(array $where)
    {
        return $this->search($where)->count();
    }

    /**
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    public function search(array $where = [])
    {
        return parent::search($where)->when(isset($where['type']), function ($query) use ($where) {
            $query->where('type', $where['type']);
        })->where('is_delete', 0)->when(isset($where['access_key']), function ($query) use ($where) {
            $query->where('access_key', $where['access_key']);
        });
    }
}
