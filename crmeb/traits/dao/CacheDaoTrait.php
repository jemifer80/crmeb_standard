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

namespace crmeb\traits\dao;


use crmeb\services\CacheService;
use crmeb\utils\Tag;
use think\cache\TagSet;
use think\Container;
use think\facade\Log;
use think\Model;

/**
 * Trait CacheDaoTrait
 * @package crmeb\traits\dao
 * @method Model getModel()
 * @method Model getPk()
 */
trait CacheDaoTrait
{

    /**
     * 获取redis
     * @return \Redis
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/29
     */
    private function getRedisConnect()
    {
        return CacheService::redisHandler()->handler();
    }

    /**
     * 获取缓存
     * @return TagSet|\think\facade\Cache
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    private function getCacheHander()
    {
        return CacheService::redisHandler();
    }

    /**
     * 对外开放方法
     * @return TagSet|\think\facade\Cache
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    public function cacheHander()
    {
        return $this->getCacheHander();
    }

    /**
     * 缓存标签
     * @param null $tag
     * @return Tag
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    public function cacheTag($tag = null)
    {
        $key = $this->cacheKey() . 'tag';
        $tag = $tag ? $key . ':' . $tag : $key;
        $redis = CacheService::redisHandler($tag);

        return new Tag($redis, $tag);
    }

    /**
     * 总缓存数据量
     * @return mixed
     */
    public function cacheCount()
    {
        $cacheKey = $this->cacheKey();
        $rds = $this->getRedisConnect();

        return $rds->hLen($cacheKey . 'map');
    }

    /**
     * 读取缓存全部数据
     * @return mixed
     */
    public function cacheList(string $key = '')
    {
        $cacheKey = $this->cacheKey() . $key;
        $rds = $this->getRedisConnect();
        $map = $rds->hGetAll($cacheKey . 'map');
        //key排序
        ksort($map);

        $list = array_values($map) ?: [];

        foreach ($list as $key => $item) {
            $list[$key] = $this->unserialize($item);
        }

        return $list;
    }

    /**
     * 读取缓存分页数据
     * @param int $page
     * @param int $limit
     * @param string $key
     * @return array
     */
    public function cachePageData(int $page = 1, int $limit = 10, string $key = '')
    {
        $cacheKey = $this->cacheKey() . $key;
        $rds = $this->getRedisConnect();

        $page = max($page, 1);
        $limit = max($limit, 1);

        //先读排序
        $pageList = $rds->zRangeByScore($cacheKey . 'page', ($page - 1) * $limit, ($page * $limit) - 1);

        //再读数据
        $list = $rds->hMGet($cacheKey . 'map', $pageList) ?: [];

        if (is_array($list)) {
            $newList = [];
            foreach ($list as $value) {
                $newList[] = $this->unserialize($value);
            }
            $list = $newList;
        }

        $count = $rds->hLen($cacheKey . 'map');

        return compact('list', 'count');
    }

    /**
     * 单个查询数据
     * @param int|string $id
     * @return false|string|array
     */
    public function cacheInfoById($id)
    {
        $cacheKey = $this->cacheKey();
        $rds = $this->getRedisConnect();

        $value = $rds->hGet($cacheKey . 'map', $id);

        return $value === null ? null : $this->unserialize($value);
    }

    /**
     * 批量查询数据
     * @param $ids
     * @return mixed
     */
    public function cacheInfoByIds(array $ids)
    {
        $cacheKey = $this->cacheKey();
        $rds = $this->getRedisConnect();

        $arr = $rds->hMGet($cacheKey . 'map', $ids);
        if (is_array($arr)) {
            $newList = [];
            foreach ($arr as $key => $value) {
                $arr[$key] = $this->unserialize($value);
            }
            $arr = $newList;
        }

        return $arr;
    }

    /**
     * 更新单个缓存
     * @param $info
     * @return false|mixed
     */
    public function cacheUpdate(array $info, $key = null)
    {
        $pk = $this->getPk();
        if ((empty($info) || !isset($info[$pk])) && !$key) {
            return false;
        }
        $cacheKey = $this->cacheKey();
        $rds = $this->getRedisConnect();
        $key = $key ?: $info[$pk];
        return $rds->hSet($cacheKey . 'map', $key, $this->serialize($info));
    }

    /**
     * 序列化数据
     * @param $value
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    private function serialize($value)
    {
        try {
            return serialize($value);
        } catch (\Throwable $e) {
            Log::error('序列化发生错误:' . $e->getMessage());
            return $value;
        }
    }

    /**
     * 反序列化数据
     * @param $value
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    private function unserialize($value)
    {
        try {
            return unserialize($value);
        } catch (\Throwable $e) {
            Log::error('反序列化发生错误:' . $e->getMessage());
            return $value;
        }
    }

    /**
     * @param int $id
     * @param $field
     * @param null $value
     * @return false|mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function cacheSaveValue(int $id, $field, $value = null)
    {
        $pk = $this->getPk();
        $info = $this->cacheInfoById($id);
        if (!$info) {
            $newInfo = $this->get($id);
            $info = $newInfo ? $newInfo->toArray() : [];
        }
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $info[$k] = $v;
            }
        } else {
            $info[$field] = $value;
        }

        $info[$pk] = $id;
        return $this->cacheUpdate($info);
    }

    /**
     * 不存在则写入，存在则返回
     * @param $key
     * @param callable|null $fn
     * @param null $default
     * @return array|false|mixed|string|null
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function cacheRemember($key, callable $fn = null, $default = null)
    {

        //不开启数据缓存直接返回
        if (!app()->config->get('cache.is_data')) {

            if ($fn instanceof \Closure) {
                return Container::getInstance()->invokeFunction($fn);
            } else {
                return $default;
            }

        }

        $info = $this->cacheInfoById($key);

        if ((null === $info || false === $info) && is_callable($fn)) {

            //读取数据库缓存
            $newInfo = $fn();

            if (null !== $newInfo) {
                //缓存数据存在则更新
                $this->cacheUpdate($newInfo, $key);
            }

            $info = $newInfo;
        }

        return null !== $info ? $info : $default;
    }

    /**
     * 批量更新缓存
     * @param $list
     * @return false|mixed
     */
    public function cacheUpdateList(array $list, string $key = '')
    {
        if (empty($list)) {
            return false;
        }
        $cacheKey = $this->cacheKey() . $key;
        $pk = $this->getPk();
        $rds = $this->getRedisConnect();
        $map = [];
        foreach ($list as $item) {
            if (empty($item) || !isset($item[$pk])) {
                continue;
            }
            $map[$item[$pk]] = $this->serialize($item);
        }
        return $rds->hMSet($cacheKey . 'map', $map);
    }

    /**
     * 删除单条缓存
     * @param $id
     */
    public function cacheDelById(int $id)
    {
        $cacheKey = $this->cacheKey();
        $rds = $this->getRedisConnect();
        $rds->hDel($cacheKey . 'map', $id);
        $rds->zRem($cacheKey . 'page', $id);
    }

    /**
     * 批量删除缓存
     * @param $ids
     */
    public function cacheDelByIds(array $ids)
    {
        $cacheKey = $this->cacheKey();
        $rds = $this->getRedisConnect();
        foreach ($ids as $id) {
            $rds->hDel($cacheKey . 'map', $id);
            $rds->zRem($cacheKey . 'page', $id);
        }
    }

    /**
     * 创建缓存
     * @param array $list
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/29
     */
    public function cacheCreate(array $list, string $key = '')
    {
        $pk = $this->getPk();
        $cacheKey = $this->cacheKey() . $key;

        $rds = $this->getRedisConnect();

        //启动事务
        $rds->multi();

        //删除旧数据
        $rds->del($cacheKey . 'map');
        $rds->del($cacheKey . 'page');
        //组合数据
        $map = [];
        foreach ($list as $i => $item) {
            $map[$item[$pk]] = $item;
            //存zset     排序
            $rds->zAdd($cacheKey . 'page', $i, $item[$pk]);
        }
        foreach ($map as $k => &$item) {
            $item = $this->serialize($item);
        }
        //存hmset    数据
        $rds->hMSet($cacheKey . 'map', $map);

        //执行事务
        $rds->exec();
    }

    protected function cacheKey()
    {
        return 'mc:' . $this->getModel()->getName() . ':';
    }

    /**
     *
     * @param $key
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    public function getCacheKey($key)
    {
        return $this->cacheKey() . $key;
    }

    /**
     * 更新缓存
     * @param string $key
     * @param $value
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function cacheStrUpdate(string $key, $value, int $expire = null)
    {
        return $this->getCacheHander()->set($this->cacheKey() . 'str:' . $key, $value, $expire);
    }

    /**
     * 获取缓存
     * @param string $key
     * @return false|mixed|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function cacheStrGet(string $key)
    {
        return $this->getCacheHander()->get($this->cacheKey() . 'str:' . $key);
    }

    /**
     * 获取表缓存是否有数据
     * @return false|mixed|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function cacheStrTable()
    {
        return $this->getRedisConnect()->get($this->cacheKey());
    }

    /**
     * 设置表缓存是否有数据
     * @param int $value
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function cacheStrSetTable(int $value = 1)
    {
        return $this->getRedisConnect()->set($this->cacheKey(), $value);
    }

    /**
     * 删除缓存
     * @param string $key
     * @return int
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function cacheStrDel(string $key)
    {
        return $this->getCacheHander()->delete($this->cacheKey() . 'str:' . $key);
    }

    /**
     * 获取缓存,没有则写入缓存并返回
     * @param string $key
     * @param callable|null $fn
     * @param null $default
     * @return false|mixed|string|null
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/1
     */
    public function cacheStrRemember(string $key, callable $fn = null, int $expire = null, $default = null)
    {

        //不开启数据缓存直接返回
        if (!app()->config->get('cache.is_data')) {

            if ($fn instanceof \Closure) {
                return Container::getInstance()->invokeFunction($fn);
            } else {
                return $default;
            }
        }

        $value = $this->cacheStrGet($key);

        if ((null === $value || false === $value) && is_callable($fn)) {

            $newValue = $fn();

            if (null !== $newValue) {
                $this->cacheStrUpdate($key, $value, $expire);
            }

            $value = $newValue;
        }

        return null !== $value ? $value : $default;
    }
}
