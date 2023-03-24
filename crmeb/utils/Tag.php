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

namespace crmeb\utils;


use think\cache\TagSet;
use think\Container;

/**
 * Class Tag
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2022/11/10
 * @package crmeb\utils
 * @mixin TagSet
 */
class Tag
{

    protected $tag;

    /**
     * @var string
     */
    protected $tagStr;

    /**
     * Tag constructor.
     * @param TagSet $set
     * @param string $tagStr
     */
    public function __construct(TagSet $set, string $tagStr)
    {
        $this->tag = $set;
        $this->tagStr = $tagStr;
    }

    /**
     * @param string $name
     * @param $value
     * @param null $expire
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    public function remember(string $name, $value, $expire = null)
    {
        //不开启数据缓存直接返回
        if (!app()->config->get('cache.is_data')) {

            if ($value instanceof \Closure) {
                $value = Container::getInstance()->invokeFunction($value);
            }
            return $value;
        }

        $name = $this->tagStr . $name;
        return $this->tag->remember($name, $value, $expire);
    }

    /**
     * @param $name
     * @param $arguments
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/10
     */
    public function __call($name, $arguments)
    {
        $this->tag->{$name}(...$arguments);
    }
}
