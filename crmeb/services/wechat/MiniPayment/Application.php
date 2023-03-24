<?php

namespace crmeb\services\wechat\MiniPayment;


use crmeb\services\wechat\MiniPayment\Payment\WeChatClient;
use EasyWeChat\MiniProgram\Application as PaymentApplication;

/**
 * Class Application.
 *
 *
 * @method WeChatClient createorder(array $params)
 * @property \crmeb\services\wechat\MiniPayment\Payment\WeChatClient            $orders
 */

class Application extends PaymentApplication
{

    protected $mini_providers = [
        Payment\ServiceProvider::class
    ];

    public function __construct(array $config = [], array $prepends = [], string $id = null)
    {
        $this->providers = array_merge($this->mini_providers,$this->providers);
        parent::__construct($config, $prepends, $id);

    }
}