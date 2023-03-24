<?php


use app\http\middleware\AllowOriginMiddleware;
use app\http\middleware\InstallMiddleware;
use app\http\middleware\out\AuthTokenMiddleware;
use app\http\middleware\StationOpenMiddleware;
use think\facade\Route;

/**
 * 对外接口路由配置
 */
Route::group('outapi', function () {

    Route::group(function () {
        //获取token
        Route::post('get_token', 'OutAccount/getToken')->name('getToken');
        Route::post('refresh_token', 'OutAccount/refreshToken')->name('refreshToken');
    })->middleware(StationOpenMiddleware::class);

    //授权接口
    Route::group(function () {
        //商品分类
        Route::get('category/list', 'Product/categoryList')->option(['real_name' => '分类列表']);
        Route::get('category/:id', 'Product/categoryInfo')->option(['real_name' => '获取分类']);
        Route::post('category', 'Product/categoryCreate')->option(['real_name' => '新增分类']);
        Route::put('category/:id', 'Product/categoryUpdate')->option(['real_name' => '修改分类']);
        Route::delete('category/:id', 'Product/categoryDelete')->option(['real_name' => '删除分类']);
        Route::put('category/set_show/:id/:is_show', 'Product/categorySetShow')->option(['real_name' => '修改分类状态']);

        //商品
        Route::get('product/list', 'Product/productList')->option(['real_name' => '商品列表']);
        Route::post('product', 'Product/productSave')->option(['real_name' => '新增商品']);
        Route::put('product/:id', 'Product/productSave')->option(['real_name' => '修改商品']);
        Route::get('product/:id', 'Product/productInfo')->option(['real_name' => '获取商品']);
        Route::put('product/set_show/:id/:is_show', 'Product/productSetShow')->option(['real_name' => '修改商品状态']);

        //订单
        Route::get('order/list', 'Order/orderList')->name('orderList')->option(['real_name' => '订单列表']);
        Route::get('order/:order_id', 'Order/orderInfo')->name('orderInfo')->option(['real_name' => '订单详情']);
        Route::put('order/remark/:order_id', 'Order/orderRemark')->name('orderRemark')->option(['real_name' => '修改备注信息']);
        Route::put('order/receive/:order_id', 'Order/orderReceive')->name('orderReceive')->option(['real_name' => '确认收货']);
        Route::get('order/express_list', 'Order/orderExpressList')->name('orderExpressList')->option(['real_name' => '获取物流公司']);
        Route::put('order/delivery/:order_id', 'Order/orderDelivery')->name('orderDelivery')->option(['real_name' => '订单发货']);
        Route::put('order/distribution/:order_id', 'Order/updateDistribution')->name('updateDistribution')->option(['real_name' => '修改配送信息']);
        Route::get('order/split_cart_info/:order_id', 'Order/SplitCartInfo')->name('SplitCartInfo')->option(['real_name' => '获取订单可拆分商品列表']);
        Route::put('order/split_delivery/:order_id', 'Order/orderSplitDelivery')->name('StoreOrderSplitDelivery')->option(['real_name' => '拆单发送货']);
        Route::put('order/invoice/:order_id', 'Order/setInvoice')->option(['real_name' => '修改订单发票']);
        Route::put('order/invoice_status/:order_id', 'Order/setInvoiceStatus')->option(['real_name' => '修改订单发票状态']);

        //售后订单
        Route::get('refund/list', 'Order/refundList')->option(['real_name' => '售后订单列表']);
        Route::put('refund/remark/:order_id', 'Order/refundRemark')->option(['real_name' => '售后订单备注']);
        Route::put('refund/:order_id', 'Order/refundPrice')->option(['real_name' => '售后订单退款']);
        Route::put('refund/agree/:order_id', 'Order/agreeRefund')->option(['real_name' => '商家同意退款']);
        Route::put('refund/refuse/:order_id', 'Order/refuseRefund')->option(['real_name' => '商家拒绝退款']);
        Route::get('refund/:order_id', 'Order/refundInfo')->option(['real_name' => '售后订单详情']);

        //优惠券
        Route::get('coupon/list', 'Coupon/couponList')->option(['real_name' => '优惠券列表']);
        Route::post('coupon', 'Coupon/couponSave')->option(['real_name' => '新增优惠券']);
        Route::put('coupon/status/:id/:status', 'Coupon/setStatus')->option(['real_name' => '修改优惠券状态']);
        Route::delete('coupon/:id', 'Coupon/couponDel')->option(['real_name' => '删除优惠券']);

        //用户等级
        Route::get('user_level/list', 'User/levelList')->option(['real_name' => '用户等级列表']);

        //用户
        Route::get('user/list', 'User/userList')->option(['real_name' => '用户列表']);
        Route::post('user', 'User/userSave')->option(['real_name' => '新增用户']);
        Route::put('user/:uid', 'User/userUpdate')->option(['real_name' => '修改用户']);
        Route::put('user/give/:uid', 'User/userGive')->option(['real_name' => '赠送积分/金额']);

    })->middleware(AuthTokenMiddleware::class, true);


})->prefix('out.')->middleware(InstallMiddleware::class)->middleware(AllowOriginMiddleware::class)->middleware(StationOpenMiddleware::class);

