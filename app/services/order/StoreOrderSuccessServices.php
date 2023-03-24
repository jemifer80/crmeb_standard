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

namespace app\services\order;


use app\dao\order\StoreOrderDao;
use app\services\activity\lottery\LuckLotteryServices;
use app\services\BaseServices;
use app\services\pay\PayServices;
use app\services\user\UserServices;
use crmeb\traits\ServicesTrait;
use think\exception\ValidateException;

/**
 * Class StoreOrderSuccessServices
 * @package app\services\order
 * @mixin StoreOrderDao
 */
class StoreOrderSuccessServices extends BaseServices
{
    use ServicesTrait;

    /**
     *
     * StoreOrderSuccessServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 0元支付
     * @param array $orderInfo
     * @param int $uid
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function zeroYuanPayment(array $orderInfo, int $uid, string $payType = PayServices::YUE_PAY)
    {
        $id = $orderInfo['id'] ?? 0;
        if (!$orderInfo || !$id) {
            throw new ValidateException('订单不存在!');
        }
        //更新订单信息
        $orderInfo = $this->dao->get($id);
        if (!$orderInfo) {
            throw new ValidateException('订单不存在');
        }
        $orderInfo = $orderInfo->toArray();
        if ($orderInfo['paid']) {
            throw new ValidateException('该订单已支付!');
        }
        return $this->paySuccess($orderInfo, $payType);//余额支付成功
    }

    /**
     * 支付成功
     * @param array $orderInfo
     * @param string $paytype
     * @return bool
     */
    public function paySuccess(array $orderInfo, string $paytype = PayServices::WEIXIN_PAY, array $other = [])
    {
        $updata = ['paid' => 1, 'pay_type' => $paytype, 'pay_time' => time()];
        if ($other && isset($other['trade_no'])) {
            $updata['trade_no'] = $other['trade_no'];
        }
        $res1 = $this->dao->update($orderInfo['id'], $updata);
        $orderInfo['pay_time'] = time();
        $orderInfo['pay_type'] = $paytype;
        //缓存抽奖次数 除过线下支付
        if (isset($orderInfo['pay_type']) && $orderInfo['pay_type'] != 'offline') {
            /** @var LuckLotteryServices $luckLotteryServices */
            $luckLotteryServices = app()->make(LuckLotteryServices::class);
            $luckLotteryServices->setCacheLotteryNum((int)$orderInfo['uid'], 'order');
        }
        //订单支付成功事件
        $userInfo = app()->make(UserServices::class)->get($orderInfo['uid']);
        event('order.pay', [$orderInfo, $userInfo]);
        $res = $res1;
        return false !== $res;
    }

}
