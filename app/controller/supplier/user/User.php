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
namespace app\controller\supplier\user;

use app\services\user\UserSpreadServices;
use think\facade\App;
use app\services\user\UserServices;
use app\controller\supplier\AuthController;
use app\services\product\product\StoreProductLogServices;

class User extends AuthController
{
    /**
     * user constructor.
     * @param App $app
     * @param UserServices $services
     */
    public function __construct(App $app, UserServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示用户信息
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (is_string($id)) {
            $id = (int)$id;
        }
        return $this->success($this->services->read($id));
    }

    /**
     * 获取单个用户信息
     * @param $id 用户id
     * @return mixed
     */
    public function oneUserInfo($id)
    {
        $data = $this->request->getMore([
            ['type', ''],
        ]);
        $id = (int)$id;
        if ($data['type'] == '') return $this->fail('缺少参数');
        return $this->success($this->services->oneUserInfo($id, $data['type']));
    }

    /**
     * 商品浏览记录
     * @param $id
     * @param StoreProductLogServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function visitList($id, StoreProductLogServices $services)
    {
        $where['uid'] = (int)$id;
        $where['type'] = 'visit';
        return app('json')->success($services->getList($where, 'product_id'));
    }

    /**
     * 获取推广人记录
     * @param $id
     * @param UserSpreadServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function spreadList($id, UserSpreadServices $services)
    {
        $where['store_id'] = 0;
        $where['staff_id'] = 0;
        $where['uid'] = $id;
        return app('json')->success($services->getSpreadList($where, '*', ['spreadUser', 'admin'], false));
    }
}
