<?php

namespace app\jobs\out;

use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;
use app\services\out\OutAccountServices;
use app\services\user\UserServices;
use crmeb\basic\BaseJobs;
use crmeb\services\CacheService;
use crmeb\services\HttpService;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class OutPushJob extends BaseJobs
{
    use QueueTrait;

    public function push($type, $data)
    {
        /** @var OutAccountServices $outAccountServices */
        $outAccountServices = app()->make(OutAccountServices::class);
        $outAccountList = $outAccountServices->selectList(['is_del' => 0, 'status' => 1])->toArray();
        foreach ($outAccountList as $item) {
            if ($item['push_open'] == 1) {
                $token = $this->getPushToken($item);
                if ($type == 'order_create_push') {
                    OutPushJob::dispatchDo('orderCreate', [$data['order_id'], $item['order_create_push'] . '?pushToken=' . $token]);
                } elseif ($type == 'order_pay_push') {
                    OutPushJob::dispatchDo('paySuccess', [$data['order_id'], $item['order_pay_push'] . '?pushToken=' . $token]);
                } elseif ($type == 'refund_create_push') {
                    OutPushJob::dispatchDo('refundCreate', [$data['order_id'], $item['refund_create_push'] . '?pushToken=' . $token]);
                } elseif ($type == 'refund_cancel_push') {
                    OutPushJob::dispatchDo('refundCancel', [$data['order_id'], $item['refund_cancel_push'] . '?pushToken=' . $token]);
                } elseif ($type == 'user_update_push') {
                    OutPushJob::dispatchDo('userUpdate', [$data, $item['user_update_push'] . '?pushToken=' . $token]);
                }
            }
        }

        return true;
    }

    /**
     * 获取推送token
     * @param array $info
     * @return false|mixed
     */
    public function getPushToken(array $info)
    {
        $token = CacheService::redisHandler()->get('pushToken' . $info['id']);
        if (!$token) {
            $param = json_encode(['push_account' => $info['push_account'], 'push_password' => $info['push_password']], JSON_UNESCAPED_UNICODE);
            $res = HttpService::postRequest($info['push_token_url'], $param, ['Content-Type:application/json', 'Content-Length:' . strlen($param)], 5);
            $res = $res ? json_decode($res, true) : [];
            if (!$res || !isset($res['code']) || $res['code'] != 0) {
                Log::error(['msg' => $info['title'] . '，获取token失败']);
                return false;
            }
            CacheService::redisHandler()->set('pushToken' . $info['id'], $res['token'], $res['time']);
            return $res['token'];
        } else {
            return $token;
        }

    }

    /**
     * 订单推送
     * @param int $oid
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function orderCreate(int $oid, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('订单' . $oid . '推送失败');
            return true;
        }

        try {
            /** @var StoreOrderServices $services */
            $services = app()->make(StoreOrderServices::class);
            if (!$services->orderCreatePush($oid, $pushUrl)) {
                OutPushJob::dispatchSece(($step + 1) * 5, 'orderCreate', [$oid, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            Log::error('订单' . $oid . '推送失败,失败原因:' . $e->getMessage());
            OutPushJob::dispatchSece(($step + 1) * 5, 'orderCreate', [$oid, $pushUrl, $step + 1]);
        }

        return true;
    }

    /**
     * 订单支付推送
     * @param int $oid
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function paySuccess(int $oid, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('订单支付' . $oid . '推送失败');
            return true;
        }

        try {
            /** @var StoreOrderServices $services */
            $services = app()->make(StoreOrderServices::class);
            if (!$services->paySuccessPush($oid, $pushUrl)) {
                OutPushJob::dispatchSece(($step + 1) * 5, 'paySuccess', [$oid, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            Log::error('订单支付' . $oid . '推送失败,失败原因:' . $e->getMessage());
            OutPushJob::dispatchSece(($step + 1) * 5, 'paySuccess', [$oid, $pushUrl, $step + 1]);
        }

        return true;
    }

    /**
     * 售后单生成
     * @param int $oid
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function refundCreate(int $oid, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('售后单' . $oid . '推送失败');
            return true;
        }

        try {
            /** @var StoreOrderRefundServices $services */
            $services = app()->make(StoreOrderRefundServices::class);
            if (!$services->refundCreatePush($oid, $pushUrl)) {
                OutPushJob::dispatchSece(($step + 1) * 5, 'refundCreate', [$oid, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            Log::error('售后单' . $oid . '推送失败,失败原因:' . $e->getMessage());
            OutPushJob::dispatchSece(($step + 1) * 5, 'refundCreate', [$oid, $pushUrl, $step + 1]);
        }
        return true;
    }

    /**
     * 取消申请
     * @param int $oid
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function refundCancel(int $oid, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('取消售后单' . $oid . '推送失败');
            return true;
        }

        try {
            /** @var StoreOrderRefundServices $services */
            $services = app()->make(StoreOrderRefundServices::class);
            if (!$services->cancelApplyPush($oid, $pushUrl)) {
                OutPushJob::dispatchSece(($step + 1) * 5, 'refundCancel', [$oid, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            Log::error('取消售后单' . $oid . '推送失败,失败原因:' . $e->getMessage());
            OutPushJob::dispatchSece(($step + 1) * 5, 'refundCancel', [$oid, $pushUrl, $step + 1]);
        }
        return true;
    }

    /**
     * 余额，积分，佣金，经验变动推送
     * @param array $data
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function userUpdate(array $data, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('用户变动推送失败');
            return true;
        }

        try {
            /** @var UserServices $services */
            $services = app()->make(UserServices::class);
            if (!$services->userUpdate($data, $pushUrl)) {
                OutPushJob::dispatchSece(($step + 1) * 5, 'userUpdate', [$data, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            OutPushJob::dispatchSece(($step + 1) * 5, 'userUpdate', [$data, $pushUrl, $step + 1]);
        }
        return true;
    }
}
