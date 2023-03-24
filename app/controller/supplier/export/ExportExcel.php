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
namespace app\controller\supplier\export;

use app\controller\supplier\AuthController;
use app\services\other\export\ExportServices;
use app\services\order\StoreOrderServices;
use app\services\other\ExpressServices;
use app\services\other\queue\QueueAuxiliaryServices;
use app\services\other\queue\QueueServices;
use think\facade\App;

/**
 * 导出excel类
 * Class ExportExcel
 * @package app\controller\supplier\export
 */
class ExportExcel extends AuthController
{
    /**
     * @var ExportServices
     */
    protected $service;

    /**
     * ExportExcel constructor.
     * @param App $app
     * @param ExportServices $services
     */
    public function __construct(App $app, ExportServices $services)
    {
        parent::__construct($app);
        $this->service = $services;
    }

    /**
     * 订单列表导出
     * @param StoreOrderServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function storeOrder(StoreOrderServices $services)
    {
        $where_tmp = $this->request->getMore([
            ['status', ''],
            ['real_name', ''],
            ['data', '', '', 'time'],
            ['type', ''],
            ['ids', '']
        ]);
        $type = $where_tmp['type'];
        $with = [];
        if ($where_tmp['ids']) {
            $where['id'] = explode(',', $where_tmp['ids']);
        }
        if ($type) {
            $where['status'] = 1;
            $where['paid'] = 1;
            $where['is_del'] = 0;
            $where['shipping_type'] = 1;
            $where['is_system_del'] = 0;
            $with = ['pink', 'refund' => function ($query) {
						$query->whereIn('refund_type', [1, 2, 4, 5])->where('is_cancel', 0)->where('is_del', 0)->field('id,store_order_id');
					}];
        }
        if (!$where_tmp['ids'] && !$type) {
            unset($where_tmp['ids']);
            unset($where_tmp['type']);
            $where = $where_tmp;
        }
        $where['is_system_del'] = 0;
        $where['pid'] = 0;
        $where['store_id'] = 0;
        $where['supplier_id'] = $this->supplierId;
        $data = $services->getExportList($where, $with, $this->service->limit);
        return $this->success($this->service->storeOrder($data, $type));
    }

    /**
     * 导出批量任务发货的记录
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function batchOrderDelivery($id, $queueType, $cacheType)
    {
        /** @var QueueAuxiliaryServices $auxiliaryService */
        $auxiliaryService = app()->make(QueueAuxiliaryServices::class);
        /** @var QueueServices $queueService */
        $queueService = app()->make(QueueServices::class);
        $queueInfo = $queueService->getQueueOne(['id' => $id]);
        if (!$queueInfo) return $this->fail("数据不存在");
        $queueValue = json_decode($queueInfo['queue_in_value'], true);
        if (!$queueValue || !isset($queueValue['cacheType'])) return $this->fail("数据参数缺失");
        $data = $auxiliaryService->getExportData(['binding_id' => $id, 'type' => $cacheType], $this->service->limit);
        return $this->success($this->service->batchOrderDelivery($data, $queueType));
    }

    /**
     * 物流公司表导出
     * @return mixed
     */
    public function expressList()
    {
        /** @var ExpressServices $expressService */
        $expressService = app()->make(ExpressServices::class);
        $data = $expressService->apiExpressList();
        return $this->success($this->service->expressList($data));
    }
}
