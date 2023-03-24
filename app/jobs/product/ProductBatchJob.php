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

namespace app\jobs\product;


use app\services\product\product\StoreProductBatchProcessServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 商品批量任务队列
 * Class ProductBatchJob
 * @package app\jobs\product
 */
class ProductBatchJob extends BaseJobs
{
    use QueueTrait;

    /**
     * @return mixed
     */
    public static function queueName()
    {
        $default = config('queue.default');
        return config('queue.connections.' . $default . '.batch_queue');
    }


    /**
     * 商品批量队列
     * @param $type
     * @param $ids
     * @param $data
     * @param $isBatch
     * @return bool
     */
    public function productBatch($type, $ids, $data, $isBatch = false)
    {
        if (!$type || !$ids || !$data) {
            return true;
        }
        //是否批量多个修改
        if ($isBatch) {
            $length = 30;
        } else {
            $length = 100;
        }
        //拆分大数组 分批加入二级队列
        $idsArr = array_chunk($ids, $length);
        foreach ($idsArr as $ids) {
            //加入分批队列
            self::dispatchDo('chunkProductBatch', [$type, $ids, $data, $isBatch]);
        }
        return true;
    }

    /**
     * 拆分分批队列
     * @param $type
     * @param $ids
     * @param $data
     * @param $isBatch
     * @return bool
     */
    public function chunkProductBatch($type, $ids, $data, $isBatch = false)
    {
        if (!$type || !$ids || !$data) {
            return true;
        }
        //是否批量多个修改
        if ($isBatch) {
			self::dispatchDo('runProductBatch', [$type, $ids, $data]);
        } else {//拆分id,单个队列执行
            foreach ($ids as $id) {
                self::dispatchDo('runProductBatch', [$type, $id, $data]);
            }
        }
        return true;
    }

    /**
     * 实际执行商品操作队列
     * @param $type
     * @param $ids
     * @param $data
     * @return bool
     */
    public function runProductBatch($type, $id, $data)
    {
		if (!is_array($id)) {
			$id = (int)$id;
		}
        if (!$type || !$id || !$data) {
            return true;
        }
        try {
			/** @var StoreProductBatchProcessServices $batchProcessServices */
            $batchProcessServices = app()->make(StoreProductBatchProcessServices::class);
            switch ($type) {
                case 1://分类
                    $batchProcessServices->setPrdouctCate($id, $data);
                    break;
                case 4://购买即送积分、优惠券
                    $batchProcessServices->setGiveIntegralCoupon($id, $data);
                    break;
				case 2://商品标签
                case 3://物流设置
                case 5://关联用户标签
                case 6://活动推荐
                case 7://自定义留言
                case 8://运费设置
                	$batchProcessServices->runBatch($id, $data, (int)$type);
					break;
                default:
                    break;
            }
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '批量操作商品,type:' . $type . '；状态失败' . ';参数：' . json_encode(['id' => $id, 'data' => $data]) . ',失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }
}
