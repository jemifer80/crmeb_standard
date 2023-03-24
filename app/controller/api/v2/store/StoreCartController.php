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

namespace app\controller\api\v2\store;


use app\services\order\StoreCartServices;
use app\Request;

class StoreCartController
{
    protected $services;

    public function __construct(StoreCartServices $services)
    {
        $this->services = $services;
    }

    /**
     * 购物车重选
     * @param Request $request
     * @return mixed
     */
    public function resetCart(Request $request)
    {
        list($id, $unique, $num, $product_id) = $request->postMore([
            ['id', 0],
            ['unique', ''],
            ['num', 1],
            ['product_id', 0]
        ], true);
        $this->services->resetCart($id, $request->uid(), $product_id, $unique, $num);
        return app('json')->successful('修改成功');
    }

    /**
     * 获取用户购物车
     * @param Request $request
     * @return mixed
     */
    public function getCartList(Request $request)
    {
        $uid = (int)$request->uid();
        [$store_id] = $request->postMore([
            ['store_id',0]
        ],true);
        $data = $this->services->getCartList(['uid' => $uid, 'is_del' => 0, 'is_new' => 0, 'is_pay' => 0, 'type' => 0], 0, 0, ['productInfo', 'attrInfo']);
        if($store_id) $this->services->setItem('store_id', (int)$store_id);
        [$data, $valid, $invalid] = $this->services->handleCartList($uid, $data);
        return app('json')->successful($valid);
    }

    /**
     * 首页加入购物车
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setCartNum(Request $request)
    {
        list($product_id, $num, $unique, $type) = $request->postMore([
            ['product_id', 0],
            ['num', 0],
            ['unique', ''],
            ['type', -1]
        ], true);
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        if (!$product_id || !is_numeric($product_id)) return app('json')->fail('参数错误');
        if (!(int)$num) return app('json')->fail('请提交加入购物车商品数量');
        $res = $cartService->setCartNum((int)$request->uid(), (int)$product_id, (int)$num, $unique, (int)$type);
        if ($res) return app('json')->successful('修改成功');
        return app('json')->fail('修改失败');
    }
}
