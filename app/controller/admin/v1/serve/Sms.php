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

namespace app\controller\admin\v1\serve;


use app\controller\admin\AuthController;
use app\validate\admin\serve\ServeValidata;
use app\services\serve\ServeServices;
use think\facade\App;

/**
 * Class Sms
 * @package app\controller\admin\v1\serve
 */
class Sms extends AuthController
{
    /**
     * Sms constructor.
     * @param App $app
     * @param ServeServices $services
     */
    public function __construct(App $app, ServeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }


    /**
     * 开通服务
     * @param string $sign
     * @return mixed
     */
    public function openServe(string $sign)
    {
        if (!$sign) {
            return $this->fail('请设置短信签名');
        }
        $this->services->sms()->setSign($sign)->open();
        return $this->success('开通成功');
    }

    /**
     * 修改短信签名
     * @param string $sign
     * @return mixed
     */
    public function editSign(string $sign)
    {
        [$sign, $phone, $code] = $this->request->postMore([
            ['sign', ''],
            ['phone', ''],
            ['code', ''],
        ], true);

        $this->validate(['phone' => $phone], ServeValidata::class, 'phone');

        if (!$sign) {
            return $this->fail('请设置短信签名');
        }
        $this->services->sms()->modify($sign, $phone, $code);
        return $this->success('修改短信签名成功');
    }

    /**
     * 获取短信模板
     * @return mixed
     */
    public function temps()
    {
        [$page, $limit, $type] = $this->request->getMore([
            ['page', 1],
            ['limit', 10],
            ['temp_type', 0],
        ], true);

        return $this->success($this->services->getSmsTempsList((int)$page, (int)$limit, (int)$type));
    }

    /**
     * 申请模板
     * @return mixed
     */
    public function apply()
    {
        [$title, $content, $type] = $this->request->postMore([
            ['title', ''],
            ['content', ''],
            ['type', 0]
        ], true);

        if (!$title || !$content || !$type) {
            return $this->success('请填写申请模板内容');
        }
        return $this->success($this->services->sms()->apply($title, $content, (int)$type));
    }

    /**
     * 获取申请记录
     * @return mixed
     */
    public function applyRecord()
    {
        [$page, $limit, $tempType] = $this->request->getMore([
            [['page', 'd'], 1],
            [['limit', 'd'], 10],
            [['temp_type', 'd'], 0],
        ], true);

        return $this->success($this->services->sms()->applys($tempType, $page, $limit));
    }
}
