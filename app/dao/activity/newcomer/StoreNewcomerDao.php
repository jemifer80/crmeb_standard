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
declare (strict_types=1);

namespace app\dao\activity\newcomer;

use app\dao\BaseDao;
use app\model\activity\newcomer\StoreNewcomer;


/**
 * 新人礼商品
 * Class StoreNewcomerDao
 * @package app\dao\activity\newcomer
 */
class StoreNewcomerDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreNewcomer::class;
    }

    /**
     * 搜索
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    protected function search(array $where = [])
    {
        return parent::search($where)->when(isset($where['storeProductId']), function ($query) {
            $query->where('product_id', 'IN', function ($query) {
                $query->name('store_product')->where('is_show', 1)->where('is_del', 0)->field('id');
            });
        });
    }

    /**
	* 新人专享商品列表
	* @param array $where
	* @param string $field
	* @param int $page
	* @param int $limit
	* @param array $with
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, array $with = [])
    {
        return $this->search($where)->field($field)
            ->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->when($with, function ($query) use ($with) {
				$query->with($with);
            })->when(isset($where['priceOrder']) && $where['priceOrder'] != '', function ($query) use ($where) {
				if ($where['priceOrder'] === 'desc') {
					$query->order("price desc");
				} else {
					$query->order("price asc");
				}
			})->when(isset($where['salesOrder']) && $where['salesOrder'] != '', function ($query) use ($where) {
				if ($where['salesOrder'] === 'desc') {
					$query->order("sales desc");
				} else {
					$query->order("sales asc");
				}
			})->order('id desc')->select()->toArray();
    }




}
