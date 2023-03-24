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
namespace app\controller\admin\v1\order;

use app\controller\admin\AuthController;
use app\services\order\StoreDeliveryOrderServices;
use app\services\user\UserServices;
use app\services\user\UserWechatuserServices;
use crmeb\exceptions\AdminException;
use think\facade\App;

/**
 * 配送订单
 * Class StoreDeliveryOrder
 * @package app\admin\controller\store
 */
class StoreDeliveryOrder extends AuthController
{
    /**
     * StoreDeliveryOrder constructor.
     * @param App $app
     * @param StoreDeliveryOrderServices $services
     */
    public function __construct(App $app, StoreDeliveryOrderServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
		$where = $this->request->getMore([
            ['keyword', ''],
            ['store_id',],
            ['status', ''],
            ['data', '', '', 'time'],
            ['station_type']
        ]);
		if ($where['store_id']) {
			$where['type'] = 1;
			$where['relation_id'] = $where['store_id'];
			unset($where['store_id']);
		}
        return $this->success($this->services->systemPage($where));
    }

	/**
 	* 详情
	* @param $id
	* @return mixed
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function detail($id)
    {
		if (!$id) {
			return app('json')->fail('缺少参数ID');
		}
        $data = $this->services->detail($id);
        return app('json')->success($data);
    }

    public function cancelForm($id)
    {
		if (!$id) {
			return app('json')->fail('缺少参数ID');
		}
        return app('json')->success($this->services->cancelForm($id));
    }

	/**
 	* 取消发单
	* @param $id
	* @return mixed
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
    public function cancel($id)
    {
        $reason = $this->request->getMore([
			['reason', ''],
			['cancel_reason', '']
		]);
		if (!$id) {
			return app('json')->fail('缺少参数ID');
		}
        if (empty($reason['reason']))
            return app('json')->fail('取消理由不能为空');
        $this->services->cancel($id, $reason);
        return app('json')->success('取消成功');
    }

	/**
	* @param $id
	* @return mixed
	*/
    public function delete($id)
    {
		if (!$id) {
			return app('json')->fail('缺少参数ID');
		}
        $this->services->delete((int)$id);
        return app('json')->success('删除成功');
    }


}
