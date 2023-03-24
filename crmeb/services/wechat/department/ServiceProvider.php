<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\wechat\department;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2022/10/9
 * @package crmeb\services\wechat\department
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $app
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/9
     */
    public function register(Container $app)
    {
        $app['department'] = function ($app) {
            return new Client($app);
        };
    }
}
