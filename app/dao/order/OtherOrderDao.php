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

namespace app\dao\order;


use app\dao\BaseDao;
use app\model\order\OtherOrder;

class OtherOrderDao extends BaseDao
{
    /** 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return OtherOrder::class;
    }

    /**
     * 重写搜索器
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    public function search(array $where = [])
    {
        return parent::search($where)->when(isset($where['name']) && $where['name'], function ($query) use ($where) {
            $query->where('uid', 'in', function ($que) use ($where) {
                $que->name('user')->where('nickname|real_name|phone', 'like', '%' . trim($where['name']) . '%')->field(['uid'])->select();
            });
        });
    }

    /**
     * 获取某个时间点一共有多少用户是付费会员状态
     * @param $time
     * @param string $channel_type
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPayUserCount(int $time, string $channel_type = '')
    {
        return $this->getModel()->when($channel_type != '', function ($query) use ($channel_type) {
            $query->where('channel_type', $channel_type);
        })->field('distinct(uid),add_time')
            ->group('uid')->having('add_time < ' . $time)
            ->order('add_time desc')
            ->select()->toArray();
    }

    /**
     * 获取VIP曲线
     * @param $time
     * @param $type
     * @param $timeType
     * @param string $str
     * @return mixed
     */
    public function getTrendData($time, $type, $timeType, $str = 'count(uid)')
    {
        return $this->getModel()->when($type != '', function ($query) use ($type) {
            $query->where('channel_type', $type);
        })->where(function ($query) use ($time) {
            if ($time[0] == $time[1]) {
                $query->whereDay('add_time', $time[0]);
            } else {
                $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                $query->whereTime('add_time', 'between', $time);
            }
        })->field("FROM_UNIXTIME(add_time,'$timeType') as days," . $str . " as num")
            ->group('days')->select()->toArray();
    }

    /**合计某字段值
     * @param array $where
     * @param string $sumField
     * @return float
     */
    public function getWhereSumField(array $where, string $sumField)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->sum($sumField);
    }

    /**根据某字段分组查询
     * @param array $where
     * @param string $field
     * @param string $group
     * @return mixed
     */
    public function getGroupField(array $where, string $field, string $group)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where, $field, $group) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
                if ($where['timeKey']['days'] == 1) {
                    $timeUinx = "%H";
                } elseif ($where['timeKey']['days'] == 30) {
                    $timeUinx = "%Y-%m-%d";
                } elseif ($where['timeKey']['days'] == 365) {
                    $timeUinx = "%Y-%m";
                } elseif ($where['timeKey']['days'] > 1 && $where['timeKey']['days'] < 30) {
                    $timeUinx = "%Y-%m-%d";
                } elseif ($where['timeKey']['days'] > 30 && $where['timeKey']['days'] < 365) {
                    $timeUinx = "%Y-%m";
                } else {
					$timeUinx = "%Y-%m";
                }
                $query->field("sum($field) as number,FROM_UNIXTIME($group, '$timeUinx') as time");
                $query->group("FROM_UNIXTIME($group, '$timeUinx')");
            })
            ->order('add_time ASC')->select()->toArray();
    }

    /**根据条件获取单条信息
     * @param array $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */

    public function getOneByWhere(array $where)
    {
        return $this->getModel()->where($where)->find();
    }

    /**收银订单
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getScanOrderList(array $where = [], int $page = 0, int $limit = 0, string $order = '')
    {
        foreach ($where as $k => $v) {
            if ($v == "") unset($where[$k]);
        }
        return $this->search($where)
            ->order(($order ? $order . ' ,' : '') . 'id desc')
            ->page($page, $limit)->select()->toArray();
    }

    /**
     * 获取会员记录
     * @param array $where
     * @param string $field
     * @param array|string[] $with
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMemberRecord(array $where = [], string $field = '*', array $with = ['user'], int $page = 0, int $limit = 0, string $order = '')
    {
        return $this->search($where)->field($field)
            ->when($with, function ($query) use ($with) {
                $query->with($with);
            })->order(($order ? $order . ' ,' : '') . 'id desc')
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->select()->toArray();
    }

    /**
     * 店员统计
     * @param array $where
     * @param string $countField
     * @param string $sumField
     * @param string $groupField
     * @return mixed
     */
    public function preStaffTotal(array $where, string $countField = 'uid', string $sumField = 'pay_price', string $groupField = 'staff_id')
    {
        return $this->search($where)
            ->field($groupField . ",count(" . $countField . ") as count,sum(`" . $sumField . "`) as price")
            ->group($groupField)
            ->select()->toArray();
    }
}
