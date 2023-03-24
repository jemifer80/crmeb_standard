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

namespace app\http\middleware\api;


use app\Request;
use think\exception\ValidateException;
use think\facade\Cache;

/**
 * 限流中间件
 * Class RateLimiterMiddleware
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/1/4
 * @package app\http\middleware\api
 */
class RateLimiterMiddleware
{

    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/4
     */
    public function handle(Request $request, \Closure $next)
    {

        $option = $request->rule()->getOption();

        //是否开启限流
        if (isset($option['rateLimiter']) && $option['rateLimiter']) {

            $rule = trim(strtolower($request->rule()->getRule()));
            $uid = 0;

            if ($request->hasMacro('uid') && !empty($option['isUser'])) {
                $uid = $request->uid();
            }

            $key = md5($rule . $uid);

            $this->createLimit($key, $option['limitNum'], $option['expire']);
        }

        return $next($request);
    }

    /**
     * @param string $key
     * @param int $initNum
     * @param int $expire
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/4
     */
    protected function createLimit(string $key, int $initNum, int $expire)
    {
        $nowTime = time();

        /** @var \Redis $redis */
        $redis = Cache::store('redis')->handler();
        $redis->watch($key);

        $limitVal = $redis->get($key);

        if ($limitVal) {
            $limitVal = json_decode($limitVal, true);
            $newNum = min($initNum, ($limitVal['num'] - 1) + (($initNum / $expire) * ($nowTime - $limitVal['time'])));
            if ($newNum <= 0) {
                throw new ValidateException('服务器拥堵正在排队处理！');
            } else {
                $redisVal = ['num' => $newNum, 'time' => $nowTime];
            }
        } else {
            $redisVal = ['num' => $initNum, 'time' => $nowTime];
        }

        $redis->multi();

        $redisValStr = json_encode($redisVal);
        $redis->set($key, $redisValStr);
        $redis->expire($key, $expire);

        $robResult = $redis->exec();
        if (!$robResult) {
            throw new ValidateException('访问频次过多！');
        }

        return true;
    }
}
