<?php

namespace app\controller\out;

use app\Request;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\product\product\StoreProductCouponServices;

class Coupon
{
    /**
     * 优惠券services
     * @var StoreCouponIssueServices
     */
    protected $issueServices;

    /**
     * @param StoreCouponIssueServices $issueServices
     */
    public function __construct(StoreCouponIssueServices $issueServices)
    {
        $this->issueServices = $issueServices;
    }

    /**
     * 优惠券列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function couponList(Request $request)
    {
        $where = $request->getMore([
            ['status', 1],
            ['coupon_title', ''],
            ['receive_type', ''],
            ['coupon_type', ''],
            ['type', '', '', 'receive'],
        ]);
        $list = $this->issueServices->getCouponIssueList($where);
        return app('json')->success($list);
    }

    /**
     * 新增优惠券
     * @param Request $request
     * @return void
     */
    public function couponSave(Request $request)
    {
        $data = $request->postMore([
            ['coupon_title', ''],
            ['coupon_price', 0.00],
            ['use_min_price', 0.00],
            ['coupon_time', 0],
            ['start_use_time', 0],
            ['end_use_time', 0],
            ['start_time', 0],
            ['end_time', 0],
            ['receive_type', 0],
            ['is_permanent', 0],
            ['total_count', 0],
            ['product_id', ''],
            ['category_id', []],
            ['type', 0],
            ['sort', 0],
            ['status', 0],
            ['coupon_type', 1],
        ]);
        if ($data['category_id']) {
            $data['category_id'] = end($data['category_id']);
        }
        if ($data['start_time'] && $data['start_use_time']) {
            if ($data['start_use_time'] < $data['start_time']) {
                return app('json')->fail('使用开始时间不能小于领取开始时间');
            }
        }
        if ($data['end_time'] && $data['end_use_time']) {
            if ($data['end_use_time'] < $data['end_time']) {
                return app('json')->fail('使用结束时间不能小于领取结束时间');
            }
        }
        //赠送券 、新人券不限量
        if (in_array($data['receive_type'], [2, 3])) {
            $data['is_permanent'] = 1;
            $data['total_count'] = 0;
        }
        if (!$data['coupon_price']) {
            return app('json')->fail($data['coupon_type'] == 1 ? '请输入优惠券金额' : '请输入优惠券折扣');
        }
        if ($data['coupon_type'] == 2 && ($data['coupon_price'] < 0 || $data['coupon_price'] > 100)) {
            return app('json')->fail('优惠券折扣为0～100数字');
        }
        $res = $this->issueServices->saveCoupon($data);
        if ($res) return app('json')->success('添加成功!');
    }

    /**
     * 修改优惠券状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function setStatus($id, $status)
    {
        $this->issueServices->update($id, ['status' => $status]);
        return app('json')->success('修改成功');
    }

    /**
     * 删除优惠券
     * @param $id
     * @return mixed
     */
    public function couponDel($id)
    {
        $this->issueServices->update($id, ['is_del' => 1]);
        /** @var StoreProductCouponServices $storeProductService */
        $storeProductService = app()->make(StoreProductCouponServices::class);
        //删除商品关联这个优惠券
        $storeProductService->delete(['issue_coupon_id' => $id]);
        return app('json')->success('删除成功!');
    }
}