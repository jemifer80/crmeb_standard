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

namespace app\services\product\shipping;


use app\dao\product\shipping\ShippingTemplatesFreeCityDao;
use app\services\BaseServices;

/**
 * 包邮和城市数据连表业务处理层
 * Class ShippingTemplatesFreeCityServices
 * @package app\services\product\shipping
 * @mixin ShippingTemplatesFreeCityDao
 */
class ShippingTemplatesFreeCityServices extends BaseServices
{
    /**
     * 构造方法
     * ShippingTemplatesFreeCityServices constructor.
     * @param ShippingTemplatesFreeCityDao $dao
     */
    public function __construct(ShippingTemplatesFreeCityDao $dao)
    {
        $this->dao = $dao;
    }
}
