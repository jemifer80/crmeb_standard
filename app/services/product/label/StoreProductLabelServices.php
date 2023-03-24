<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\services\product\label;


use app\dao\product\label\StoreProductLabelDao;
use app\services\BaseServices;
use crmeb\services\FormBuilder;
use think\facade\Route as Url;

/**
 * 商品标签
 * Class StoreProductLabelServices
 * @package app\services\product\label
 * @mixin StoreProductLabelDao
 */
class StoreProductLabelServices extends BaseServices
{
    /**
     * StoreProductLabelServices constructor.
     * @param StoreProductLabelDao $dao
     */
    public function __construct(StoreProductLabelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $ids
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function getLabelCache(array $ids, array $field = null)
    {
        if (app()->config->get('cache.is_data')) {
            $list = $this->dao->cacheInfoByIds($ids);
        } else {
            $list = null;
        }

        if (!$list) {
            $list = $this->dao->getList(['ids' => $ids]);
            foreach ($list as $item) {
                $this->dao->cacheUpdate($item);
            }
        }

        if ($field && $list) {
            $newList = [];
            foreach ($list as $item) {
                $data = [];
                foreach ($field as $k) {
                    $data[$k] = $item[$k] ?? null;
                }
                $newList[] = $data;
            }
            $list = $newList;
        }

        return $list;
    }

    /**
     * 获取商品标签表单
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLabelForm()
    {
        /** @var StoreProductLabelCateServices $service */
        $service = app()->make(StoreProductLabelCateServices::class);
        $options = $service->getAllProductLabelCate();
        $data = [];
        foreach ($options as $option) {
            $data[] = ['label' => $option['name'], 'value' => $option['id']];
        }
        $rule = [
            FormBuilder::select('label_cate', '标签分组')->options($data),
            FormBuilder::input('label_name', '标签名称')->maxlength(20),
        ];
        return create_form('添加商品标签', $rule, Url::buildUrl('/product/label/0'), 'POST');
    }
}
