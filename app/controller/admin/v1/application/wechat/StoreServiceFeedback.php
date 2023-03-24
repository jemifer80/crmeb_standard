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

namespace app\controller\admin\v1\application\wechat;


use app\controller\admin\AuthController;
use app\services\message\service\StoreServiceFeedbackServices;
use think\facade\App;

/**
 * 客服用户留言反馈
 * Class StoreServiceFeedback
 * @package app\controller\admin\v1\application\wechat
 */
class StoreServiceFeedback extends AuthController
{

    /**
     * StoreServiceFeedback constructor.
     * @param App $app
     * @param StoreServiceFeedbackServices $services
     */
    public function __construct(App $app, StoreServiceFeedbackServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取留言列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['title', ''],
            ['time', '']
        ]);

        return $this->success($this->services->getFeedbackList($where));
    }

    /**
     * 获取修改表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id)
    {
        if (!$id) {
            return $this->fail('缺少参数');
        }
        return $this->success($this->services->editForm((int)$id));
    }

    /**
     * 修改
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['make', ''],
            ['status', 0],
        ]);
        if (!$id || !($feedInfo = $this->services->get($id))) {
            return $this->fail('反馈内容不存在');
        }
        $feedInfo->make = $data['make'];
        if ($data['status']) {
            $feedInfo->status = $data['status'];
        }
        $feedInfo->save();
        return $this->success('修改成功');
    }

    /**
     * 删除反馈
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function delete($id)
    {
        if (!$id) {
            return $this->fail('缺少参数');
        }
        if ($this->services->delete($id)) {
            return $this->success('删除成功');
        } else {
            return $this->fail('删除失败');
        }
    }
}
