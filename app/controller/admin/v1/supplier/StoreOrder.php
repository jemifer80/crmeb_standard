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

namespace app\controller\admin\v1\supplier;

use think\facade\App;
use app\controller\admin\AuthController;
use app\services\order\StoreOrderServices;
use app\common\controller\Order as CommonOrder;
use app\services\order\supplier\SupplierOrderServices;

/**
 * Class StoreOrder
 * @package app\controller\admin\v1\supplier
 */
class StoreOrder extends AuthController
{
    use CommonOrder;

    /**
     * @var StoreOrderServices
     */
    protected $services;

    /**
     * StoreOrder constructor.
     * @param App $app
     * @param StoreOrderServices $services
     */
    public function __construct(App $app, StoreOrderServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 订单列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', ''],
            ['data', '', '', 'time'],
            ['type', ''],
            ['pay_type', ''],
            ['order', ''],
            ['field_key', ''],
            ['supplier_id', '']
        ]);

        if ($where['supplier_id'] < 1) {
            $where['supplier_id'] = -1;
        }
        $where['type'] = trim($where['type']);
        $where['is_system_del'] = 0;

        $where['store_id'] = 0;
        $where['type'] = trim($where['type'], ' ');
        return $this->success($this->services->getOrderList($where, ['*'], ['split' => function ($query) {
            $query->field('id,pid');
        }, 'pink', 'invoice', 'supplier']));
    }

    /**
     * 提醒发货
     * @param $id
     * @return mixed
     */
    public function deliverRemind(int $supplierId, int $id)
    {
        if (!$supplierId || !$id) return $this->fail('参数异常');

        /** @var SupplierOrderServices $supplierOrderServices */
        $supplierOrderServices = app()->make(SupplierOrderServices::class);
        $supplierOrderServices->deliverRemind($supplierId, $id);
        return $this->success('提醒成功');
    }

}