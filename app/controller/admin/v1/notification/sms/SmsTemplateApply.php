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
namespace app\controller\admin\v1\notification\sms;

use app\controller\admin\AuthController;
use app\services\serve\ServeServices;
use think\facade\App;

/**
 * 短信模板申请
 * Class SmsTemplateApply
 * @package app\admin\controller\sms
 */
class SmsTemplateApply extends AuthController
{

    public function __construct(App $app, ServeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 异步获取模板列表
     */
    public function index()
    {
        $where = $this->request->getMore([
            [['type', 'd'], 0],
            ['status', ''],
            ['title', ''],
            [['page', 'd'], 1],
            [['limit', 'd'], 20],
        ]);
        $where['temp_type'] = $where['type'];
        $templateList = $this->services->sms()->temps($where['page'], $where['limit'], $where['type']);
        $templateList['data'] = $templateList['data'] ?? [];
        foreach ($templateList['data'] as $key => &$item) {
            $item['templateid'] = $item['temp_id'];
            switch ((int)$item['temp_type']) {
                case 1:
                    $item['type'] = '验证码';
                    break;
                case 2:
                    $item['type'] = '通知';
                    break;
                case 30:
                    $item['type'] = '营销短信';
                    break;
            }
        }
        return $this->success($templateList);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return string
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return $this->success($this->services->getSmsTemplateForm());
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['content', ''],
            ['type', 0]
        ]);
        if (!strlen(trim($data['title']))) {
            return $this->fail('请输入模板名称');
        }
        if (!strlen(trim($data['content']))) {
            return $this->fail('请输入模板内容');
        }
        $this->services->sms()->apply($data['title'], $data['content'], $data['type']);
        return $this->success('申请成功');
    }
}
