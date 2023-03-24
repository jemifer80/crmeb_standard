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
namespace app\controller\api\pc;

use app\Request;
use app\services\other\QrcodeServices;
use app\services\pc\ProductServices;
use crmeb\services\SystemConfigService;

/**
 * Class HomeController
 * @package app\api\controller\pc
 */
class HomeController
{
    /**
     * PC端首页轮播图
     * @return mixed
     */
    public function getBanner()
    {
        $list = sys_data('pc_home_banner');
        return app('json')->successful(compact('list'));
    }

    /**
 	* 首页分类尚品
	* @param Request $request
	* @param ProductServices $productServices
	* @return mixed
	*/
    public function getCategoryProduct(Request $request, ProductServices $productServices)
    {
        return app('json')->successful($productServices->getCategoryProduct((int)$request->uid()));
    }

    /**
     * 获取手机购买跳转url配置
     * @return string
     */
    public function getProductPhoneBuy()
    {
        $data = SystemConfigService::more(['product_phone_buy_url', 'site_url']);
        return app('json')->successful(['phone_buy' => $data['product_phone_buy_url'] ?? 1, 'sit_url' => $data['site_url'] ?? '']);
    }

    /**
     * 付费会员购买二维码
     * @return mixed
     */
    public function getPayVipCode()
    {
        $type = sys_config('product_phone_buy_url', 1);
        $url = '/pages/annex/vip_paid/index';
        $name = "wechat_pay_vip_code.png";
        /** @var QrcodeServices $QrcodeService */
        $QrcodeService = app()->make(QrcodeServices::class);
        if ($type == 1) {
            $codeUrl = $QrcodeService->getWechatQrcodePath($name, $url, false, false);
        } else {
            //生成小程序地址
            $codeUrl = $QrcodeService->getRoutineQrcodePath(0, 0, 5, [], false);
        }
        return app('json')->successful(['url' => $codeUrl ? $codeUrl : '']);
    }
}
