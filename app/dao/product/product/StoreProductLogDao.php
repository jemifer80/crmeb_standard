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

namespace app\dao\product\product;


use app\dao\BaseDao;
use app\model\product\product\StoreProductLog;
use think\facade\Config;

class StoreProductLogDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductLog::class;
    }

    public function getRanking($where)
    {
//        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        $sortField = $where['sort'] ?? 'visit';
        return $this->search($where)->with('storeName')
            ->field([
                'product_id',
                'SUM(visit_num) as visit',
                'COUNT(distinct(uid)) as user',
                'SUM(cart_num) as cart',
                'SUM(order_num) as orders',
                'SUM(pay_num) as pay',
                'SUM(pay_price * pay_num) as price',
                'SUM(cost_price) as cost',
                'ROUND((SUM(pay_price)-SUM(cost_price))/SUM(cost_price),2) as profit',
                'SUM(collect_num) as collect',
                'ROUND((COUNT(distinct(pay_uid))-1)/COUNT(distinct(uid)),2) as changes',
//                '(select count(*) from (SELECT COUNT(`pay_uid`) as p,product_id FROM `' . $prefix . 'store_product_log` WHERE `product_id` = `' . $prefix . 'store_product_log`.`product_id` AND `type` = \'pay\' GROUP BY `pay_uid` HAVING p>1) u WHERE `product_id` = `' . $prefix . 'store_product_log`.`product_id`) as aaaa',
                'COUNT(distinct(pay_uid))-1 as repeats'
                //->having($sortField . ' > 0')
            ])->group('product_id')->order($sortField . ' desc')->limit(20)->select()->toArray();
    }

    public function getRepeats($where, $product_id)
    {
        return count($this->search($where)->where('type', 'pay')->where('product_id', $product_id)->field('count(pay_uid) as p')->group('pay_uid')->having('p>1')->select());
    }

    /**
     * 列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $group
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $group = '')
    {
        return $this->search($where)->with(['storeName'])->field($field)
            ->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->when($group, function ($query) use ($group) {
                $query->group($group);
            })->order('add_time desc')->select()->toArray();
    }
}
