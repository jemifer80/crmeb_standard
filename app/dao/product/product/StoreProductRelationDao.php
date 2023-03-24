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
use app\model\product\product\StoreProductRelation;

/**
 * 商品关联关系dao
 * Class StoreProductRelationDao
 * @package app\dao\product\product
 */
class StoreProductRelationDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductRelation::class;
    }

	 /**
     * 获取所有的分销员等级
     * @param array $where
     * @param string $field
     * @param array $with
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where = [], string $field = '*', array $with = [], int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)
		->when($with, function ($query) use ($with) {
			$query->with($with);
		})->when($page && $limit, function ($query) use ($page, $limit) {
			$query->page($page, $limit);
		})->select()->toArray();
    }

    /**
     * 保存数据
     * @param array $data
     * @return mixed|void
     */
    public function saveAll(array $data)
    {
        $this->getModel()->insertAll($data);
    }

    /**
     * 根据商品id获取分类id
     * @param array $productId
     * @return array
     */
    public function productIdByCateId(array $productId)
    {
        return $this->getModel()->whereIn('product_id', $productId)->where('type', 1)->column('relation_id');
    }

    /**
     * 根据分类获取商品id
     * @param array $cate_id
     * @return array
     */
    public function cateIdByProduct(array $cate_id)
    {
        return $this->getModel()->whereIn('relation_id', $cate_id)->where('type', 1)->column('product_id');
    }

	/**
 	* 设置
	* @param array $ids
	* @param int $is_show
	* @param int $type
	* @param string $key
	* @return \crmeb\basic\BaseModel
	*/
	public function setShow(array $ids, int $is_show = 1, int $type = 1, string $key = 'product_id')
	{
		return $this->getModel()->whereIn($key, $ids)->where('type', $type)->update(['status' => $is_show]);
	}

	/**
 	* 根据条件获取商品ID， 或者relation_id
	* @param array $where
	* @param string $field
	* @return array
	*/
	public function getIdsByWhere(array $where, string $field = 'product_id')
	{
		return $this->search($where)->column($field);
	}
}
