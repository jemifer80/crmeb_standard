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

namespace app\dao\system\timer;

use app\dao\BaseDao;
use app\model\system\timer\SystemTimer;

/**
 * Class SystemTimerDao
 * @package app\dao\system\timer
 */
class SystemTimerDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemTimer::class;
    }

    /**
	* 获取列表
	* @param array $where
	* @param int $page
	* @param int $limit
	* @param string $field
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
    public function getList(array $where, int $page = 0, int $limit = 0, string $field = '*')
    {
        return $this->search($where)->field($field)->when($page && $limit, function ($query) use($page, $limit) {
			$query->page($page, $limit);
        })->select()->toArray();
    }
}
