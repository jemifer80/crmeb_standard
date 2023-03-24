<?php


namespace crmeb\services\wechat;

use crmeb\services\wechat;

/**
 * Class Factory.
 *
 * @method static wechat\MiniPayment\Application        MiniPayment(array $config)
 */
class Factory
{
    /**
     * @param string $name
     * @param array  $config
     *
     * @return \EasyWeChat\Kernel\ServiceContainer
     */
    public static function make($name, array $config)
    {
        $namespace = \EasyWeChat\Kernel\Support\Str::studly($name);
        $application = "crmeb\\services\\wechat\\{$namespace}\\Application";

        return new $application($config);
    }

    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }
}
