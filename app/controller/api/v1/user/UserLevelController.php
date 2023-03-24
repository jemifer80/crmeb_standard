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
namespace app\controller\api\v1\user;

use app\Request;
use app\services\user\level\UserLevelServices;

/**
 * 会员等级类
 * Class UserLevelController
 * @package app\api\controller\user
 */
class UserLevelController
{
    protected $services = NUll;

    /**
     * UserLevelController constructor.
     * @param UserLevelServices $services
     */
    public function __construct(UserLevelServices $services)
    {
        $this->services = $services;
    }

    /**
     * 检测用户是否可以成为会员
     * @param Request $request
     * @return mixed
     */
    public function detection(Request $request)
    {
        return app('json')->successful($this->services->detection((int)$request->uid()));
    }

    /**
     * 会员等级列表
     * @param Request $request
     * @return mixed
     */
    public function grade(Request $request)
    {
        return app('json')->successful(['list' => $this->services->grade((int)$request->uid()), 'task' => ['list' => [], 'task' => []]]);
    }

    /**
     * 会员详情
     * @param Request $request
     * @return mixed
     */
    public function userLevelInfo(Request $request)
    {
        return app('json')->successful($this->services->getUserLevelInfo((int)$request->uid()));
    }

    /**
     * 经验列表
     * @param Request $request
     * @return mixed
     */
    public function expList(Request $request)
    {
        return app('json')->successful($this->services->expList((int)$request->uid()));
    }

    /**
     * 获取会员卡激活需要的信息
     * @param Request $request
     * @return mixed
     */
    public function activateInfo(Request $request)
    {
        return app('json')->successful($this->services->getActivateInfo());
    }

    /**
     * 会员卡激活
     * @param Request $request
     * @return mixed
     */
    public function activateLevel(Request $request)
    {
        $data = $request->post();
        return app('json')->successful($this->services->userActivatelevel((int)$request->uid(), $data));
    }

}
