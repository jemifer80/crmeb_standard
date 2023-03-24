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
namespace app\controller\out;

use app\Request;
use app\services\product\category\StoreCategoryServices;
use app\services\product\product\StoreProductServices;


/**
 * 商品类
 * Class StoreProductController
 * @package app\api\controller\store
 */
class Product
{
    /**
     * 商品services
     * @var StoreProductServices
     */
    protected $productServices;

    /**
     * 分类services
     * @var StoreCategoryServices
     */
    protected $categoryServices;

    public function __construct(StoreProductServices $productServices, StoreCategoryServices $categoryServices)
    {
        $this->productServices = $productServices;
        $this->categoryServices = $categoryServices;
    }

    /**
     * 分类列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function categoryList(Request $request)
    {
        $where = $request->getMore([
            ['is_show', ''],
            ['pid', ''],
            ['cate_name', ''],
        ]);
        $where['pid'] = -2;
        $data = $this->categoryServices->getCategoryList($where);
        return app('json')->success($data);
    }

    /**
     * 获取分类
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function categoryInfo($id)
    {
        $info = $this->categoryServices->getInfo((int)$id);
        return app('json')->success($info);
    }

    /**
     * 新建分类
     * @param Request $request
     * @return mixed
     */
    public function categoryCreate(Request $request)
    {
        $data = $request->postMore([
            ['pid', 0],
            ['cate_name', ''],
            ['pic', ''],
            ['big_pic', ''],
            ['sort', 0],
            ['is_show', 0]
        ]);
        $cateId = $this->categoryServices->createData($data)->id;
        return app('json')->success('添加成功', ['id' => $cateId]);
    }

    /**
     * 修改分类
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function categoryUpdate(Request $request, $id)
    {
        $data = $request->postMore([
            ['pid', 0],
            ['cate_name', ''],
            ['pic', ''],
            ['big_pic', ''],
            ['sort', 0],
            ['is_show', 0]
        ]);
        $this->categoryServices->editData($id, $data);
        return app('json')->success('修改成功');
    }

    /**
     * 删除分类
     * @param $id
     * @return mixed
     */
    public function categoryDelete($id)
    {
        $this->categoryServices->del((int)$id);
        return app('json')->success('删除成功');
    }

    /**
     * 修改分类状态
     * @param $id
     * @param $is_show
     * @return mixed
     */
    public function categorySetShow($id, $is_show)
    {
        $this->categoryServices->setShow($id, $is_show);
        return app('json')->success('设置成功');
    }


    /**
     * 商品列表
     * @param Request $request
     * @return mixed
     */
    public function productList(Request $request)
    {
        $where = $request->getMore([
            ['cate_id', ''],
            ['store_name', ''],
            ['type', 1, '', 'status'],
            ['is_live', 0],
            ['is_new', ''],
            ['is_vip_product', ''],
            ['is_presale_product', ''],
            ['store_label_id', '']
        ]);
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        if ($where['cate_id'] !== '') {
            if ($this->categoryServices->value(['id' => $where['cate_id']], 'pid')) {
                $where['sid'] = $where['cate_id'];
            } else {
                $where['cid'] = $where['cate_id'];
            }
        }
        unset($where['cate_id']);
        $where['type'] = [0, 2];
        if ($where['store_name']) {//搜索
            $where['type'] = [];
            $where['pid'] = 0;
        }
        $list = $this->productServices->searchList($where);
        return app('json')->success($list);
    }

    /**
     * 新增商品
     * @param Request $request
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function productSave(Request $request, int $id = 0)
    {
        $data = $request->postMore([
            ['product_type', 0],//商品类型
            ['supplier_id', 0],//供应商ID
            ['cate_id', []],
            ['store_name', ''],
            ['store_info', ''],
            ['keyword', ''],
            ['unit_name', '件'],
            ['recommend_image', ''],
            ['slider_image', []],
            ['is_sub', []],//佣金是单独还是默认
            ['sort', 0],
            ['sales', 0],
            ['ficti', 100],
            ['give_integral', 0],
            ['is_show', 0],
            ['is_hot', 0],
            ['is_benefit', 0],
            ['is_best', 0],
            ['is_new', 0],
            ['mer_use', 0],
            ['is_postage', 0],
            ['is_good', 0],
            ['description', ''],
            ['spec_type', 0],
            ['video_open', 0],
            ['video_link', ''],
            ['items', []],
            ['attrs', []],
            ['recommend', []],//商品推荐
            ['activity', []],
            ['coupon_ids', []],
            ['label_id', []],
            ['command_word', ''],
            ['tao_words', ''],
            ['type', 0, '', 'is_copy'],
            ['delivery_type', []],//物流设置
            ['freight', 1],//运费设置
            ['postage', 0],//邮费
            ['temp_id', 0],//运费模版
            ['recommend_list', []],
            ['brand_id', []],
            ['soure_link', ''],
            ['bar_code', ''],
            ['code', ''],
            ['is_support_refund', 1],//是否支持退款
            ['is_presale_product', 0],//预售商品开关
            ['presale_time', []],//预售时间
            ['presale_day', 0],//预售发货日
            ['is_vip_product', 0],//是否付费会员商品
            ['auto_on_time', 0],//自动上架时间
            ['auto_off_time', 0],//自动下架时间
            ['custom_form', []],//自定义表单
            ['store_label_id', []],//商品标签
            ['ensure_id', []],//商品保障服务区
            ['specs', []],//商品参数
            ['specs_id', 0],//商品参数ID
            ['is_limit', 0],//是否限购
            ['limit_type', 0],//限购类型
            ['limit_num', 0]//限购数量
        ]);
        $this->productServices->save($id, $data);
        return app('json')->success(!$id ? '保存商品信息成功' : '修改商品信息成功');
    }

    /**
     * 商品详情
     * @param $id
     * @return mixed
     */
    public function productInfo($id)
    {
        return app('json')->success($this->productServices->getInfo((int)$id));
    }

    /**
     * 设置商品状态
     * @param $id
     * @param $is_show
     * @return mixed
     */
    public function productSetShow($id, $is_show)
    {
        $this->productServices->setShow([$id], $is_show);
        return app('json')->success('设置成功');
    }
}
