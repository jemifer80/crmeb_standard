<?php


use app\http\middleware\AllowOriginMiddleware;
use app\http\middleware\InstallMiddleware;
use app\http\middleware\supplier\AuthTokenMiddleware;
use app\http\middleware\StationOpenMiddleware;
use think\facade\Config;
use think\facade\Route;
use think\Response;

/**
 * 供应商路由配置
 */
Route::group('supplierapi', function () {

    /**
     * 不需要登录不验证权限
     */
    Route::group(function () {
		//图形验证码
        Route::get('ajcaptcha', 'Login/ajcaptcha')->name('ajcaptcha');
        //图形验证码
        Route::post('ajcheck', 'Login/ajcheck')->name('ajcheck');
        //是否需要滑块验证接口
        Route::post('is_captcha', 'Login/getAjCaptcha')->name('getAjCaptcha');
        Route::get('code', 'Test/code')->name('code')->option(['real_name' => '测试验证码']);
        Route::get('index', 'Test/index')->name('index')->option(['real_name' => '测试主页']);
        Route::post('login', 'Login/login')->name('login')->option(['real_name' => '账号密码登录']);
        Route::get('login/info', 'Login/info')->name('loginInfo')->option(['real_name' => '登录信息']);
        Route::get('captcha_store', 'Login/captcha')->name('captcha')->option(['real_name' => '图片验证码']);
		//获取版权
        Route::get('copyright', 'Common/getCopyright')->option(['real_name' => '获取版权']);
    });

    /**
     * 只需登录不验证权限
     */
    Route::group(function () {
        //获取logo
        Route::get('logo', 'Common/getLogo')->option(['real_name' => '获取logo']);
        //获取配置
        Route::get('config', 'Common/getConfig')->option(['real_name' => '获取配置']);
        //获取未读消息
        Route::get('jnotice', 'Common/jnotice')->option(['real_name' => '获取未读消息']);
        //获取省市区街道
        Route::get('city', 'Common/city')->option(['real_name' => '获取省市区街道']);
        //获取搜索菜单列表
        Route::get('menusList', 'Common/menusList')->option(['real_name' => '搜索菜单列表']);
        //退出登录
        Route::get('logout', 'Login/logOut')->option(['real_name' => '退出登录']);
        //修改密码
        Route::put('updatePwd', 'staff.StoreStaff/updateStaffPwd')->option(['real_name' => '修改密码']);
        //获取供应商信息
        Route::get('supplier', 'Supplier/read')->name('read')->option([['real_name' => '获取供应商信息']]);
        //更新供应商信息
        Route::put('supplier', 'Supplier/update')->name('update')->option([['real_name' => '更新供应商信息']]);
        //获取小票打印信息
        Route::get('printing', 'SupplierTicketPrint/read')->name('read')->option([['real_name' => '获取小票打印信息']]);
        //更新供应商信息
        Route::put('printing', 'SupplierTicketPrint/update')->name('update')->option([['real_name' => '更新小票打印']]);

        //管理员资源路由
        Route::resource('admin', 'SupplierAdmin')->option(['real_name' => [
            'index' => '获取管理员列表',
            'read' => '获取管理员详情',
            'create' => '获取创建管理员表单',
            'save' => '保存管理员',
            'edit' => '获取修改管理员表单',
            'update' => '修改管理员',
            'delete' => '删除管理员'
        ]]);

        //修改管理员状态
        Route::put('admin/set_status/:id/:status', 'SupplierAdmin/set_status')->option(['real_name' => '修改管理员状态']);
        //首页统计数据
        Route::get('home/header', 'Common/homeStatics')->option(['real_name' => '首页统计数据']);
        //首页订单图表
        Route::get('home/order', 'Common/orderChart')->option(['real_name' => '首页订单图表']);
        //订单来源分析
        Route::get('home/order_channel', 'Common/orderChannel')->option(['real_name' => '订单来源分析']);
        //订单类型分析
        Route::get('home/order_type', 'Common/orderType')->option(['real_name' => '订单订单类型分析']);
    })->middleware(AuthTokenMiddleware::class);

    /**
     * 附件相关路由
     */
    Route::group('file', function () {
        //图片附件列表
        Route::get('file', 'file.SystemAttachment/index')->option(['real_name' => '图片附件列表']);
        //删除图片
        Route::post('file/delete', 'file.SystemAttachment/delete')->option(['real_name' => '删除图片']);
        //移动图片分类表单
        Route::get('file/move', 'file.SystemAttachment/move')->option(['real_name' => '移动图片分类表单']);
        //移动图片分类
        Route::put('file/do_move', 'file.SystemAttachment/moveImageCate')->option(['real_name' => '移动图片分类']);
        //修改图片名称
        Route::put('file/update/:id', 'file.SystemAttachment/update')->option(['real_name' => '修改图片名称']);
        //上传图片
        Route::post('upload/[:upload_type]', 'file.SystemAttachment/upload')->option(['real_name' => '上传图片']);
        //附件分类管理资源路由
        Route::resource('category', 'file.SystemAttachmentCategory')->option(['real_name' => [
            'index' => '获取附件分类列表',
            'read' => '获取附件分类详情',
            'create' => '获取创建附件分类表单',
            'save' => '保存附件分类',
            'edit' => '获取修改附件分类表单',
            'update' => '修改附件分类',
            'delete' => '删除附件分类'
        ]]);
    })->middleware([AuthTokenMiddleware::class]);

    /**
     * 订单路由
     */
    Route::group('order', function () {
        //订单列表
        Route::get('list', 'Order/lst')->name('lst')->option(['real_name' => '订单列表']);
        //订单列表获取配送员
        Route::get('delivery/list', 'Order/get_delivery_list')->option(['real_name' => '订单列表获取配送员']);
        //获取物流公司
        Route::get('express_list', 'Order/express')->name('StoreOrdeRexpressList')->option(['real_name' => '获取物流公司']);
        //获取订单可拆分商品列表
        Route::get('split_cart_info/:id', 'Order/split_cart_info')->name('StoreOrderSplitCartInfo')->option(['real_name' => '获取订单可拆分商品列表']);
        //拆单发送货
        Route::put('split_delivery/:id', 'Order/split_delivery')->name('StoreOrderSplitDelivery')->option(['real_name' => '拆单发送货']);
        //面单默认配置信息
        Route::get('sheet_info', 'Order/getDeliveryInfo')->option(['real_name' => '面单默认配置信息']);
        //获取物流信息
        Route::get('express/:id', 'Order/get_express')->name('StoreOrderUpdateExpress')->option(['real_name' => '获取物流信息']);
        //快递公司电子面单模版
        Route::get('express/temp', 'Order/express_temp')->option(['real_name' => '快递公司电子面单模版']);
        //订单发送货
        Route::put('delivery/:id', 'Order/update_delivery')->name('StoreOrderUpdateDelivery')->option(['real_name' => '订单发送货']);
        //打印订单
        Route::get('print/:id', 'Order/order_print')->name('StoreOrderPrint')->option(['real_name' => '打印订单']);
        //确认收货
        Route::put('take/:id', 'Order/take_delivery')->name('StoreOrderTakeDelivery')->option(['real_name' => '确认收货']);
        //修改备注信息
        Route::put('remark/:id', 'Order/remark')->name('StoreOrderorRemark')->option(['real_name' => '修改备注信息']);
        //获取订单状态
        Route::get('status/:id', 'Order/status')->name('StoreOrderorStatus')->option(['real_name' => '获取订单状态']);
        //拆单发送货
        Route::put('split_delivery/:id', 'Order/split_delivery')->name('StoreOrderSplitDelivery')->option(['real_name' => '拆单发送货']);
        //获取订单拆分子订单列表
        Route::get('split_order/:id', 'Order/split_order')->name('StoreOrderSplitOrder')->option(['real_name' => '获取订单拆分子订单列表']);
        //订单退款表单
        Route::get('refund/:id', 'Order/refund')->name('StoreOrderRefund')->option(['real_name' => '订单退款表单']);
        //订单退款
        Route::put('refund/:id', 'Order/update_refund')->name('StoreOrderUpdateRefund')->option(['real_name' => '订单退款']);
        //订单详情
        Route::get('info/:id', 'Order/order_info')->name('SupplierOrderInfo')->option(['real_name' => '订单详情']);
        //批量发货
        Route::get('hand/batch_delivery', 'Order/hand_batch_delivery')->option(['real_name' => '批量发货']);
        //面单默认配置信息
        Route::get('sheet_info', 'Order/getDeliveryInfo')->option(['real_name' => '面单默认配置信息']);
        //获取不退款表单
        Route::get('no_refund/:id', 'Order/no_refund')->name('StoreOrderorNoRefund')->option(['real_name' => '获取不退款表单']);
        //修改不退款理由
        Route::put('no_refund/:id', 'Order/update_un_refund')->name('StoreOrderorUpdateNoRefund')->option(['real_name' => '修改不退款理由']);
        //线下支付
        Route::post('pay_offline/:id', 'Order/pay_offline')->name('StoreOrderorPayOffline')->option(['real_name' => '线下支付']);
        //获取退积分表单
        Route::get('refund_integral/:id', 'Order/refund_integral')->name('StoreOrderorRefundIntegral')->option(['real_name' => '获取退积分表单']);
        //修改退积分
        Route::put('refund_integral/:id', 'Order/update_refund_integral')->name('StoreOrderorUpdateRefundIntegral')->option(['real_name' => '修改退积分']);
        //更多操作打印电子面单
        Route::get('order_dump/:order_id', 'Order/order_dump')->option(['real_name' => '更多操作打印电子面单']);
        //删除单个订单
        Route::delete('del/:id', 'Order/del')->name('StoreOrderorDel')->option(['real_name' => '删除订单单个']);
        //批量删除订单
        Route::post('dels', 'Order/del_orders')->name('StoreOrderorDels')->option(['real_name' => '批量删除订单']);
        //获取订单编辑表单
        Route::get('edit/:id', 'Order/edit')->name('StoreOrderEdit')->option(['real_name' => '获取订单编辑表单']);
        //修改订单
        Route::put('update/:id', 'Order/update')->name('StoreOrderUpdate')->option(['real_name' => '修改订单']);
        //获取配送信息表单
        Route::get('distribution/:id', 'Order/distribution')->name('StoreOrderDistribution')->option(['real_name' => '获取配送信息表单']);
        //修改配送信息
        Route::put('distribution/:id', 'Order/update_distribution')->name('StoreOrderUpdateDistribution')->option(['real_name' => '修改配送信息']);
        // //订单核销 TODO:供应商暂时无需核销
        // Route::post('write', 'Order/write_order')->name('writeOrder')->option(['real_name' => '订单核销']);
        // //订单号核销
        // Route::put('write_update/:order_id', 'Order/write_update')->name('writeOrderUpdate')->option(['real_name' => '订单号核销']);
        //快递公司电子面单模版
        Route::get('express/temp', 'Order/express_temp')->option(['real_name' => '快递公司电子面单模版']);
        //打印配货单信息
        Route::get('distribution_info', 'Order/distributionInfo')->name('StoreOrderDistributionInfo')->option(['real_name' => '打印配货单信息']);

        //获取线下付款二维码
        Route::get('offline_scan', 'v1.order.OtherOrder/offline_scan')->name('OfflineScan')->option(['real_name' => '获取线下付款二维码']);
        //线下收银列表
        Route::get('scan_list', 'v1.order.OtherOrder/scan_list')->name('ScanList')->option(['real_name' => '线下收银列表']);
        //发票列表头部统计
        Route::get('invoice/chart', 'v1.order.StoreOrderInvoice/chart')->name('StoreOrderorInvoiceChart')->option(['real_name' => '发票列表头部统计']);
        //申请发票列表
        Route::get('invoice/list', 'v1.order.StoreOrderInvoice/list')->name('StoreOrderorInvoiceList')->option(['real_name' => '申请发票列表']);
        //设置发票状态
        Route::post('invoice/set/:id', 'v1.order.StoreOrderInvoice/set_invoice')->name('StoreOrderorInvoiceSet')->option(['real_name' => '设置发票状态']);
        //开票订单详情
        Route::get('invoice_order_info/:id', 'v1.order.StoreOrderInvoice/orderInfo')->name('StoreOrderorInvoiceOrderInfo')->option(['real_name' => '开票订单详情']);
        //电子面单模板列表
        Route::get('expr/temp', 'v1.order.StoreOrder/expr_temp')->option(['real_name' => '电子面单模板列表']);

    })->middleware([
        \app\http\middleware\supplier\AuthTokenMiddleware::class,
        \app\http\middleware\supplier\SupplierCheckRoleMiddleware::class,
        \app\http\middleware\supplier\SupplierLogMiddleware::class
    ]);

    /**
     * 导出excel相关路由
     */
    Route::group('export', function () {
        //订单
        Route::get('storeOrder', 'export.ExportExcel/storeOrder')->option(['real_name' => '订单导出']);
        //物流公司对照表导出
        Route::get('expressList', 'export.ExportExcel/expressList')->option(['real_name' => '物流公司对照表导出']);
        //导出批量发货记录
        Route::get('batchOrderDelivery/:id/:queueType/:cacheType', 'export.ExportExcel/batchOrderDelivery')->option(['real_name' => '批量发货记录导出']);

    })->middleware([
        \app\http\middleware\supplier\AuthTokenMiddleware::class,
        \app\http\middleware\supplier\SupplierCheckRoleMiddleware::class,
        \app\http\middleware\supplier\SupplierLogMiddleware::class
    ]);

    /**
     * 用户模块 相关路由
     */
    Route::group('user', function () {
        //用户信息
        Route::get('user/:id', 'user.User/read')->name('read')->option(['real_name' => '用户信息']);
        //获取指定用户的信息
        Route::get('one_info/:id', 'user.User/oneUserInfo')->name('oneUserInfo')->option(['real_name' => '获取指定用户的信息']);
        //商品浏览列表
        Route::get('visit_list/:id', 'user.User/visitList')->name('visitList')->option(['real_name' => '商品浏览列表']);
        //推荐人记录列表
        Route::get('spread_list/:id', 'user.User/spreadList')->name('spreadList')->option(['real_name' => '推荐人记录列表']);
    })->middleware([
        \app\http\middleware\supplier\AuthTokenMiddleware::class,
        \app\http\middleware\supplier\SupplierCheckRoleMiddleware::class,
        \app\http\middleware\supplier\SupplierLogMiddleware::class
    ]);

    /**
     * 队列任务 相关路由
     */
    Route::group('queue', function () {
        //队列任务列表
        Route::get('index', 'queue.Queue/index')->name('index')->option(['real_name' => '队列任务列表']);
        //队列批量发货记录
        Route::get('delivery/log/:id/:type', 'queue.Queue/delivery_log')->option(['real_name' => '队列批量发货记录']);
        //再次执行批量队列任务
        Route::get('again/do_queue/:id/:type', 'queue.Queue/again_do_queue')->option(['real_name' => '再次执行批量队列任务']);
        //清除异常任务队列
        Route::get('del/wrong_queue/:id/:type', 'queue.Queue/del_wrong_queue')->option(['real_name' => '清除异常任务队列']);
        //停止队列任务
        Route::get('stop/wrong_queue/:id', 'queue.Queue/stop_wrong_queue')->option(['real_name' => '停止队列任务']);
    })->middleware([
        \app\http\middleware\supplier\AuthTokenMiddleware::class,
        \app\http\middleware\supplier\SupplierCheckRoleMiddleware::class,
        \app\http\middleware\supplier\SupplierLogMiddleware::class
    ]);

    /**
     * 售后 相关路由
     */
    Route::group('refund', function () {
        //售后列表
        Route::get('list', 'Refund/getRefundList')->option(['real_name' => '售后订单列表']);
        //商家同意退款，等待用户退货
        Route::get('agree/:order_id', 'Refund/agreeRefund')->option(['real_name' => '商家同意退款，等待用户退货']);
        //售后订单备注
        Route::put('remark/:id', 'Refund/remark')->option(['real_name' => '售后订单备注']);
        //售后订单退款表单
        Route::get('refund/:id', 'Refund/refund')->name('StoreOrderRefund')->option(['real_name' => '售后订单退款表单']);
        //售后订单退款
        Route::put('refund/:id', 'Refund/update_refund')->name('StoreOrderUpdateRefund')->option(['real_name' => '售后订单退款']);
        //售后详情
        Route::get('detail/:id', 'Refund/detail')->option(['real_name' => '售后订单详情']);
    })->middleware([
        \app\http\middleware\supplier\AuthTokenMiddleware::class,
        \app\http\middleware\supplier\SupplierCheckRoleMiddleware::class,
        \app\http\middleware\supplier\SupplierLogMiddleware::class
    ]);

    /**
     * miss 路由
     */
    Route::miss(function () {
        if (app()->request->isOptions()) {
            $header = Config::get('cookie.header');
            $header['Access-Control-Allow-Origin'] = app()->request->header('origin');
            return Response::create('ok')->code(200)->header($header);
        } else
            return Response::create()->code(404);
    });

})->prefix('supplier.')->middleware(InstallMiddleware::class)->middleware(AllowOriginMiddleware::class)->middleware(StationOpenMiddleware::class);


