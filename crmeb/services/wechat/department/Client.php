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

use EasyWeChat\Work\Department\Client as WorkClient;

class Client extends WorkClient
{

    /**
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/9
     * @param int $limit
     * @param string $cursor
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserListIds(int $limit = 0, string $cursor = '')
    {
        $data = [];

        if ($limit) {
            $data['limit'] = $limit;
        }

        if ($cursor) {
            $data['cursor'] = $cursor;
        }

        return $this->httpPostJson('cgi-bin/user/list_id', $data);
    }
}
