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

namespace app\controller\api\v1;

use crmeb\basic\BaseController;

/**
 * Class CommonController
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2022/11/8
 * @package app\controller\api\v1
 */
class CommonController extends BaseController
{
    /**
     * 获取版权
     * @return mixed
     */
    public function getCopyright()
    {
        try {
            $copyright = $this->__z6uxyJQ4xYa5ee1mx5();
        } catch (\Throwable $e) {
            $copyright = [
                'copyrightContext' => '',
                'copyrightImage' => '',
            ];
        }
        $copyright['record_No'] = sys_config('record_No');
        $copyright['version'] = get_crmeb_version();
		$copyright['routine_contact_type'] = sys_config('routine_contact_type');
		$copyright['site_name'] = sys_config('site_name', '');
        $copyright['site_logo'] = sys_config('wap_login_logo', '');
        return $this->success($copyright);
    }

    /**
     * 登录页面获取logo以及版本全新
     * @return mixed
     */
    public function getLogo()
    {
        try {
            $copyright = $this->__z6uxyJQ4xYa5ee1mx5();
        } catch (\Throwable $e) {
            $copyright = [
                'copyrightContext' => '',
                'copyrightImage' => '',
            ];
        }
        $copyright['record_No'] = sys_config('record_No');
        $copyright['version'] = get_crmeb_version();
        $logo = sys_config('wap_login_logo');
        if (strstr($logo, 'http') === false && $logo) $logo = sys_config('site_url') . $logo;
        $copyright['logo_url'] = str_replace('\\', '/', $logo);
        return app('json')->successful($copyright);

    }
}
