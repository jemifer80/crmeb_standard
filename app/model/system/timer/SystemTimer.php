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

namespace app\model\system\timer;

use crmeb\basic\BaseModel;
use crmeb\traits\JwtAuthModelTrait;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 定时任务模型
 * Class SystemTimer
 * @package app\model\system\timer
 */
class SystemTimer extends BaseModel
{
    use ModelTrait;
    use JwtAuthModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_timer';

    protected $insert = ['add_time'];

}
