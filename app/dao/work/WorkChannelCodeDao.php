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

namespace app\dao\work;


use app\dao\BaseDao;
use app\model\work\WorkChannelCode;
use crmeb\traits\SearchDaoTrait;

/**
 * 企业微信渠道码
 * Class WorkChannelCodeDao
 * @package app\dao\work
 */
class WorkChannelCodeDao extends BaseDao
{

    use SearchDaoTrait;

    /**
     * @return string
     */
    protected function setModel(): string
    {
        return WorkChannelCode::class;
    }

}
