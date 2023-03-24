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

namespace app\services\activity\coupon;


use app\dao\activity\coupon\StoreCouponUserCouponDao;
use app\services\BaseServices;

/**
 * 根据下单金额获取用户能使用的优惠卷
 * Class StoreCouponUserCouponServices
 * @package app\services\activity\coupon
 * @mixin StoreCouponUserCouponDao
 */
class StoreCouponUserCouponServices extends BaseServices
{
    /**
     * StoreCouponUserCouponServices constructor.
     * @param StoreCouponUserCouponDao $dao
     */
    public function __construct(StoreCouponUserCouponDao $dao)
    {
        $this->dao = $dao;
    }

}
