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
declare (strict_types=1);

namespace app\controller\admin\v1\marketing\lottery;

use app\controller\admin\AuthController;
use app\services\activity\lottery\LuckLotteryRecordServices;
use think\facade\App;

/**
 * 抽奖中奖记录
 * Class LuckLotteryRecord
 * @package app\controller\admin\v1\marketing\lottery
 */
class LuckLotteryRecord extends AuthController
{

    /**
     * LuckLotteryRecord constructor.
     * @param App $app
     * @param LuckLotteryRecordServices $services
     */
    public function __construct(App $app, LuckLotteryRecordServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 用抽奖id查询中奖记录
     * @param $id
     * @return mixed
     */
    public function index($id)
    {
        $where = $this->request->postMore([
            ['lottery_id', 0],
            ['is_receive', ''],
            ['is_deliver', ''],
            ['type', ''],
            ['keyword', ''],
            ['data', '', '', 'time'],
        ]);
        if (!$id) {
            return $this->fail('缺少活动ID');
        }
        $where['lottery_id'] = $id;
        return $this->success($this->services->getList($where));
    }

    public function list()
    {
        $where = $this->request->postMore([
            ['lottery_id', 0],
            ['is_receive', ''],
            ['is_deliver', ''],
            ['type', ''],
            ['keyword', ''],
            ['data', '', '', 'time'],
            ['factor', ''],
        ]);
        return $this->success($this->services->getList($where));
    }

    public function deliver($id)
    {
        $data = $this->request->postMore([
            ['deliver_name', ''],
            ['deliver_number', ''],
            ['mark', ''],
        ]);
        if (!$id) {
            return $this->fail('缺少ID');
        }
        return $this->success($this->services->setDeliver((int)$id, $data) ? '处理成功' : '处理失败');
    }
}
