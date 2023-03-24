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

namespace app\jobs;


use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 测试队列
 * Class BatchHandleJob
 * @package app\jobs
 */
class TestJob extends BaseJobs
{
    use QueueTrait;

    public function doJob()
    {
        \think\facade\Log::error('队列执行了');
    }
}
