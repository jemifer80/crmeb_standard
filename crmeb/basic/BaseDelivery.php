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

namespace crmeb\basic;


/**
 * Class BaseDelivery
 * @package crmeb\basic
 */
abstract class BaseDelivery extends BaseStorage
{

	/**
 	* 回调地址
	* @var null
	*/
	protected $callback_url = null;

	/**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        $this->callback_url = rtrim(sys_config('site_url'), '/') . '/api/city_delivery/notify';
    }

	/**
	* @param $data
	* @return mixed
	*/
    abstract function addMerchant($data);     //注册商户

    /**
	* @param $data
	* @return mixed
	*/
    abstract function addShop($data);         //创建门店

    /**
	* @param $data
	* @return mixed
	*/
    abstract function updateShop($data);      //更新门店

    /**
	* @param $data
	* @return mixed
	*/
    abstract function addOrder($data);        //发布订单

    /**
	* @param $data
	* @return mixed
	*/
    abstract function getOrderPrice($data);   //计算订单价格

    /**
	* @param $data
	* @return mixed
	*/
    abstract function getOrderDetail($data);  //获取订单详情

    /**
	* @param $data
	* @return mixed
	*/
    abstract function cancelOrder($data);     //取消订单

    /**
	* @param $data
	* @return mixed
	*/
    abstract function getRecharge($data);     //获取充值地址

    /**
	* @param $data
	* @return mixed
	*/
    abstract function getBalance($data);      //获取余额

    /**
	* @param $data
	* @return mixed
	*/
    abstract function addTip($data);          //支付小费

    /**
	* @param $data
	* @return mixed
	*/
    abstract function getCity($data);         //获取城市信息

}
