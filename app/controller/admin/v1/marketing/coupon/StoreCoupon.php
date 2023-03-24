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
namespace app\controller\admin\v1\marketing\coupon;

use app\controller\admin\AuthController;
use app\services\activity\coupon\StoreCouponProductServices;
use app\services\activity\coupon\StoreCouponService;
use think\facade\App;

/**
 * 优惠券制作
 * Class StoreCoupon
 * @package app\controller\admin\v1\marketing
 */
class StoreCoupon extends AuthController
{

    /**
     * StoreCoupon constructor.
     * @param App $app
     * @param StoreCouponService $service
     */
    public function __construct(App $app, StoreCouponService $service)
    {
        parent::__construct($app);
        $this->services = $service;
    }

    /**
     * 优惠券模板列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['status', 1],
            ['title', ''],
        ]);
        $data = $this->services->getList($where);
        return $this->success($data);
    }

    /**
     * 创建添加模板表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        [$type] = $this->request->getMore(['type'], true);
        return $this->success($this->services->createForm($type));
    }

    /**
     * 保存优惠券模板
     * @return mixed
     */
    public function save(StoreCouponProductServices $services)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['image', ''],
            ['category_id', ''],
            ['coupon_price', ''],
            ['use_min_price', ''],
            ['coupon_time', ''],
            ['sort', ''],
            ['status', 0],
            ['type', 0]
        ]);
        $data['product_id'] = '';
        if ($data['type'] == 1) {
            $this->validate($data, \app\validate\admin\marketing\StoreCouponValidate::class, 'type');
        } elseif ($data['type'] == 2) {
            $this->validate($data, \app\validate\admin\marketing\StoreCouponValidate::class, 'product');
            $productIds = array_column($data['image'], 'product_id');
            $data['product_id'] = implode(',', $productIds);
        } else {
            $this->validate($data, \app\validate\admin\marketing\StoreCouponValidate::class, 'save');
        }
        $data['add_time'] = time();
        $this->services->save($data);
        return $this->success('添加优惠券成功!');
    }

    /**
     * 删除优惠券模板
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $data['is_del'] = 1;
        $this->services->update($id, $data);
        return $this->success('删除成功!');
    }

    /**
     * 立即失效优惠券模板
     * @param $id
     * @return mixed
     */
    public function status($id)
    {
        $this->services->invalid($id);
        return $this->success('修改成功!');
    }

    /**
     * 发布优惠券表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function issue($id)
    {
        return $this->success($this->services->createIssue($id));
    }

    /**
     * 保存发布优惠券信息
     * @param $id
     * @return mixed
     */
    public function update_issue($id)
    {
        list($_id, $coupon_title, $rangeTime, $count, $status, $is_permanent, $full_reduction, $is_give_subscribe, $is_full_give, $is_type) = $this->request->postMore([
            'id',
            ['coupon_title', ''],
            ['range_date', ['', '']],
            ['count', 0],
            ['status', 0],
            ['is_permanent', 0],
            ['full_reduction', 0],
            ['is_give_subscribe', 0],
            ['is_full_give', 0],
            ['is_type', 0]
        ], true);
        $this->services->upIssue($id, $_id, $coupon_title, $rangeTime, $count, $status, $is_permanent, $full_reduction, $is_give_subscribe, $is_full_give, $is_type);
        return $this->success('发布优惠劵成功!');
    }
}
