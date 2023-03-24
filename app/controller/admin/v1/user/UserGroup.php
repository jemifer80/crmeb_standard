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
namespace app\controller\admin\v1\user;

use app\controller\admin\AuthController;
use app\services\user\group\UserGroupServices;
use think\facade\App;

/**
 * 会员设置
 * Class UserLevel
 * @package app\admin\controller\user
 */
class UserGroup extends AuthController
{
    /**
     * user constructor.
     * @param App $app
     * @param UserGroupServices $services
     */
    public function __construct(App $app, UserGroupServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 分组列表
     */
    public function index()
    {
        return $this->success($this->services->getGroupList('*', true));
    }

    /**
     * 添加/修改分组页面
     * @param int $id
     * @return string
     */
    public function add()
    {
        $data = $this->request->getMore([
            ['id', 0],
        ]);
        return $this->success($this->services->add((int)$data['id']));
    }

    /**
     *
     * @param int $id
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['group_name', ''],
        ]);
        if (!$data['group_name']) {
            return $this->fail('请输入分组名称');
        }
        $this->services->save((int)$data['id'], $data);
        return $this->success('提交成功！');
    }

    /**
     * 删除
     * @param $id
     * @throws \Exception
     */
    public function delete()
    {
        $data = $this->request->getMore([
            ['id', 0],
        ]);
        if (!$data['id']) return $this->fail('数据不存在');
        return $this->success($this->services->delGroup((int)$data['id']));
    }
}
