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

namespace app\jobs\user;


use app\services\user\label\UserLabelRelationServices;
use app\services\user\UserServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

/**
 * 用户批量任务队列
 * Class UserBatchJob
 * @package app\jobs\user
 */
class UserBatchJob extends BaseJobs
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
     * 用户批量队列
     * @param $type
     * @param $uids
     * @param $data
     * @return bool
     */
    public function userBatch($type, $uids, $data)
    {
        if (!$type || !$uids || !$data) {
            return true;
        }
        //拆分大数组 分批加入二级队列
        $uidsArr = array_chunk($uids, 100);
        foreach ($uidsArr as $ids) {
            //加入分批队列
            self::dispatchDo('chunkUserBatch', [$type, $ids, $data]);
        }
        return true;
    }

    /**
     * 拆分分批队列
     * @param $type
     * @param $uids
     * @param $data
     * @return bool
     */
    public function chunkUserBatch($type, $uids, $data)
    {
        if (!$type || !$uids || !$data) {
            return true;
        }
        foreach ($uids as $id) {
            self::dispatchDo('runUserBatch', [$type, $id, $data]);
        }
        return true;
    }

    /**
     * 实际执行用户操作队列
     * @param $type
     * @param $uid
     * @param $data
     * @return bool
     */
    public function runUserBatch($type, $uid, $data)
    {
        $uid = (int)$uid;
        if (!$type || !$uid || !$data) {
            return true;
        }
        try {
            switch ($type) {
                case 1://分组
                    $group_id = (int)($data['group_id'] ?? 0);
                    if ($group_id) {
                        /** @var UserServices $userServices */
                        $userServices = app()->make(UserServices::class);
                        $userServices->setUserGroup([$uid], $group_id);
                    }
                    break;
                case 2://标签
                    $label_id = $data['label_id'] ?? [];
                    if ($label_id) {
                        /** @var UserLabelRelationServices $services */
                        $services = app()->make(UserLabelRelationServices::class);
                        $services->setUserLable($uid, $label_id);
                    }
                    break;
                case 3://等级
                    $level_id = (int)($data['level_id'] ?? 0);
                    if ($level_id) {
                        /** @var UserServices $userServices */
                        $userServices = app()->make(UserServices::class);
                        $userServices->saveGiveLevel($uid, $level_id);
                    }
                    break;
                case 4://积分余额
                    $data['money'] = (string)$data['money'];
                    $data['integration'] = (string)$data['integration'];
                    $data['is_other'] = true;
                    /** @var UserServices $userServices */
                    $userServices = app()->make(UserServices::class);
                    $userServices->updateInfo($uid, $data);
                    break;
                case 5://赠送会员
                    $day = (int)($data['day'] ?? 0);
                    if ($day) {
                        $day_status = (int)($data['days_status'] ?? 1);
                        /** @var UserServices $userServices */
                        $userServices = app()->make(UserServices::class);
                        $userServices->saveGiveLevelTime($uid, $day, $day_status);
                    }
                    break;
                case 6://上级推广人
                    $spread_uid = (int)($data['spread_uid'] ?? 0);
                    if ($spread_uid) {
                        /** @var UserServices $userServices */
                        $userServices = app()->make(UserServices::class);
                        $userServices->saveUserSpreadUid($uid, $spread_uid);
                    }
                    break;
                default:
                    break;
            }
        } catch (\Throwable $e) {

            response_log_write([
                'message' => '批量操作用户,type:' . $type . '；状态失败,' . ';参数：' . json_encode(['uid' => $uid, 'data' => $data]) . ', 失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

        }
        return true;
    }
}
