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

namespace app\listener\store;


use app\jobs\order\CityDeliveryJob;
use crmeb\interfaces\ListenerInterface;

/**
 * 门店创建成功事件
 * Class StoreSuccess
 * @package app\listener\store
 */
class StoreSuccess implements ListenerInterface
{

    public function handle($event): void
    {
        //提交数据,门店id
        [$data, $id, $is_new] = $event;

		//修改、保存uu、达达门店
		CityDeliveryJob::dispatchDo('syncCityShop', [$id, $is_new]);
    }
}
