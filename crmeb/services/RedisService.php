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

namespace crmeb\services;


use think\facade\Config;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Redis;

/**
 * Class RedisService
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2022/10/29
 * @package crmeb\services
 */
class RedisService
{

    /**
     * @var array
     */
    protected $config;

    /**
     * 获取进程池最大时间
     * @var int
     */
    protected $maxWaitTime = 3;

    /**
     * 最大连接池数量
     * @var int
     */
    protected $maxActive;

    /**
     * @var Channel
     */
    protected $pool;

    /**
     * RedisService constructor.
     * @param array $config
     * @param array $pool
     */
    public function __construct(array $config = [], array $pool = [])
    {
        $this->config = $config ?: Config::get('cache.stores.redis');
        $this->maxActive = $pool['maxActive'] ?? Config::get('swoole.pool.redis.cache.max_active', 50);
        $this->maxWaitTime = $pool['maxWaitTime'] ?? Config::get('swoole.pool.redis.cache.max_wait_time', 5);
    }

    /**
     * 初始化连接池
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/29
     */
    public function init()
    {
        $this->pool = new Channel($this->maxActive);

        go(function () {
            for ($i = 0; $i < $this->maxActive; $i++) {
                $redis = $this->createConnect($this->config);
                $this->pool->push($redis);
            }
        });

        return $this;
    }

    /**
     * 创建连接
     * @param array $config
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/29
     */
    public function createConnect(array $config)
    {
        $connection = new Redis();
        $res = $connection->connect($config['host'], $config['port'], \Redis::SERIALIZER_PHP);
        if ($config['password']) {
            $connection->auth($config['password']);
        }

        $connection->select($config['select']);

        if ($res === false) {
            throw new \RuntimeException("Failed to connect redis server");
        }

        return $connection;
    }

    /**
     * 销毁连接
     * @param $connection
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/29
     */
    public function removeConnect($connection)
    {
        \Swoole\Coroutine\defer(function () use ($connection) { //释放
            $connection->close();
        });
    }

    /**
     * 获取redis连接句柄
     * @return Redis
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/29
     */
    public function getRedis()
    {

        if ($this->pool->isEmpty()) {
            $redis = $this->createConnect($this->config);
            $this->removeConnect($redis);
            return $redis;
        }

        $redis = $this->pool->pop($this->maxWaitTime);

        if (false === $redis) {
            $redis = $this->createConnect($this->config);
            $this->removeConnect($redis);
        } else {
            \Swoole\Coroutine\defer(function () use ($redis) { //释放
                $this->pool->push($redis);
            });
        }

        return $redis;
    }

    /**
     * @param $name
     * @param $arguments
     * @return Redis
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/29
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getRedis(), $name], $arguments);
    }
}
