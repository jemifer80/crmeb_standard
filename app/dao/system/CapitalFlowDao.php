<?php

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\CapitalFlow;

class CapitalFlowDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return CapitalFlow::class;
    }

    /**
 	* 资金流水
	* @param array $where
	* @param string $field
	* @param int $page
	* @param int $limit
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }

    /**
     * 账单记录
     * @param $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecordList($where, $page = 0, $limit = 0)
    {
        $model = $this->search($where)
            ->when(isset($where['type']) && $where['type'] !== '', function ($query) use ($where) {
                $timeUnix = '%d';
                switch ($where['type']) {
                    case "day" :
                        $timeUnix = "%d";
                        break;
                    case "week" :
                        $timeUnix = "%u";
                        break;
                    case "month" :
                        $timeUnix = "%m";
                        break;
                }
                $query->field("FROM_UNIXTIME(add_time,'$timeUnix') as day,sum(if(price >= 0,price,0)) as income_price,sum(if(price < 0,price,0)) as exp_price,add_time,group_concat(id) as ids");
                $query->group("FROM_UNIXTIME(add_time, '$timeUnix')");
            });
        $count = $model->count();
        $list = $model->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time desc')->select()->toArray();
        return compact('list', 'count');
    }
}
