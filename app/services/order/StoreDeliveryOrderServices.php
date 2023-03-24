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

namespace app\services\order;


use app\dao\order\StoreDeliveryOrderDao;
use app\jobs\order\OrderTakeJob;
use app\services\BaseServices;
use app\services\other\CityAreaServices;
use app\services\store\SystemStoreServices;
use app\services\supplier\SystemSupplierServices;
use crmeb\services\DeliverySevices;
use crmeb\services\FormBuilder as Form;
use crmeb\services\SystemConfigService;
use crmeb\traits\OptionTrait;
use think\exception\ValidateException;
use think\facade\Log;
use think\facade\Route as Url;

/**
 * 发货单
 * Class StoreDeliveryOrderServices
 * @package app\services\order
 * @mixin StoreDeliveryOrderDao
 */
class StoreDeliveryOrderServices extends BaseServices
{
	use OptionTrait;

	protected  $statusData  = [
        2   => '待取货',
        3   => '配送中',
        4   => '已完成',
        -1  => '已取消',
        9   => '物品返回中',
        10  => '物品返回完成',
        100 => '骑士到店',
    ];

	/**
 	* 平台达达门店
	* @var string
	*/
	public $platCityShopId = 'plat_delivery_city_shop_001';

    /**
     * 构造方法
     * StoreDeliveryOrderServices constructor.
     * @param StoreDeliveryOrderDao $dao
     */
    public function __construct(StoreDeliveryOrderDao $dao)
    {
        $this->dao = $dao;
    }

	/**
 	* 配送信息
	* @return string[]
	 */
	public function getStatusMsg()
	{
		return $this->statusData;
	}

	/**
	* @param array $where
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
	public function systemPage(array $where)
    {
		[$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', $page, $limit, ['orderInfo', 'storeInfo']);
		$count = $this->dao->count();
		if ($list) {
			foreach ($list as &$item) {
				$item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s') : '';
			}
		}
        return compact('count', 'list');
    }

	/**
 	* 生成订单号
	* @return string
	* @throws \Exception
	 */
	public function getOrderSn(string $key = '')
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = number_format((floatval($msec) + floatval($sec)) * 1000, 0, '', '');
        $orderId = $key . $msectime . random_int(10000, max(intval($msec * 10000) + 10000, 98369));
        return $orderId;
    }

	/**
 	* 地址转经纬度
	* @param $region
	* @param $address
	* @return mixed|null
	*/
	public function lbs_address($region, $address)
    {
		$key = sys_config('tengxun_map_key', '');
		if (!$key) {
			throw new ValidateException('请先配置地图KEY');
		}
        $locationOption = new \Joypack\Tencent\Map\Bundle\AddressOption($key);
        $locationOption->setAddress($address);
        $locationOption->setRegion($region);
        $location = new \Joypack\Tencent\Map\Bundle\Address($locationOption);
        $res = $location->request();
        if ($res->error) {
            throw new ValidateException($res->error);
        }
        if ($res->status) {
            throw new ValidateException($res->message);
        }
        if (!$res->result) {
            throw new ValidateException('获取失败');
        }
        return $res->result;
    }


	/**
 	* 处理订单数据
	* @param array $station
	* @param array $order
	* @param int $type
	* @return array
	*/
	public function getPriceParams(array $station, array $order, int $type)
    {
        $data = [];
        $type = (int)$type;

        switch ($type) {
            case 1:
                $city = DeliverySevices::init(DeliverySevices::DELIVERY_TYPE_DADA)->getCity([]);
                $res = [];
                foreach ($city as $item) {
                    $res[$item['label']] = $item['key'];
                }
				//达达城市数据：西安
				$city_name = str_replace(['市','自治州','地区','区划','县'], '',$station['city_name'] ?? '');
                $data = [
                    'shop_no'           => $station['origin_shop_id'],
                    'city_code'         => $res[$city_name],
                    'cargo_price'       => $order['pay_price'],
                    'is_prepay'         => 0,
                    'receiver_name'     => $order['real_name'],
                    'receiver_address'  => $order['user_address'],
                    'cargo_weight'      => 0,
                    'receiver_phone'    => $order['user_phone'],
                    'is_finish_code_needed' => 1,
                ];
                break;
            case 2://uu城市数据：西安市
				$business = DeliverySevices::init(DeliverySevices::DELIVERY_TYPE_UU)->getBusiness();
				$business = array_combine(array_column($business, 'key'), $business);
                $data = [
                    'from_address'      => $station['station_address'],
                    'to_address'        => $order['user_address'],
                    'city_name'         => $station['city_name'] ?? '西安',
                    'goods_type'        => $business[$station['business'] ?? 1]['label'],
                    'send_type'         =>'0',
                    'to_lat'            => $order['latitude'],
                    'to_lng'            => $order['longitude'],
                    'from_lat'          => $station['lat'],
                    'from_lng'          => $station['lng'],
                ];
                break;
        }
        return $data;
    }

	/**
 	* 创建配送单
	* @param int $id
	* @param array $data
	* @param int $type
	* @param $order
	* @return bool
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function create(int $id, array $data, int $type = 1, $order = [])
    {
		if (!$order) {
			/** @var StoreOrderServices $orderServices */
			$orderServices = app()->make(StoreOrderServices::class);
			$order = $orderServices->get($id);
		}
		if (!$order) {
			throw new ValidateException('订单信息获取失败');
		}
		if (isset($order['user_address']) && !$order['user_address']) {
			throw new ValidateException('自提订单不支持同城配送');
		}
		$order = is_object($order) ? $order->toArray() : $order;
		//处理地址定位
        if (isset($order['user_location']) && $order['user_location']) {
            [$longitude, $latitude] = explode(' ', $order['user_location']);
            $order['longitude'] = $longitude;
            $order['latitude'] = $latitude;
			if (!$order['longitude'] || !$order['latitude']) {
				$addressArr = $this->addressHandle($order['user_address']);
				$city_name = $addressArr['city'] ?? '';
				try {
					$addres = $this->lbs_address($city_name, $order['user_address']);
					$order['latitude'] = $addres['location']['lat'] ?? '';
					$order['longitude'] = $addres['location']['lng'] ?? '';
				} catch (\Exception $e) {
					throw new ValidateException('获取经纬度失败');
				}
			}
        }
		if ($order['store_id']) {//门店
			$resType = 1;
			$relationId = (int)$order['store_id'];
		} elseif ($order['supplier_id']) {//供应商
			$resType = 2;
			$relationId = (int)$order['supplier_id'];
		} else {//平台信息
			$resType = 0;
			$relationId = 0;
		}
		//获取发货信息
		$station = $this->syncCityShop($relationId, $resType, $type);
		if (!$station || !isset($station['lat']) || !$station['lat'] || !isset($station['lng']) || !$station['lng']) {
			throw new ValidateException('获取发货信息失败');
		}
        $getPriceParams = $this->getPriceParams($station, $order, $type);
        $orderSn = $this->getOrderSn($type == 1 ? 'dd' : 'uu');
        $getPriceParams['origin_id'] = $orderSn;
        $getPriceParams['cargo_weight'] = $data['cargo_weight'] ?? '';

        $service = DeliverySevices::init($type);
		try {
			//计算价格
			$priceData = $service->getOrderPrice($getPriceParams);
			if ($type == DeliverySevices::DELIVERY_TYPE_UU) { //uu
				$priceData['receiver'] = $order['real_name'];
				$priceData['receiver_phone'] = $order['user_phone'];
				$priceData['note'] = $data['remark'];
				$priceData['push_type'] = 2;
				$priceData['special_type'] = $data['special_type'] ?? 0;
			}

			$res = $service->addOrder($priceData);
			$ret = [
				'type' => $resType,
				'relation_id' => $relationId,
				'oid' => $id,
				'order_id' => $orderSn,
				'delivery_no' => $type == 2 ? $res['ordercode'] : $priceData['deliveryNo'] ?? '',
				'city_code' => $station['city_name'] ?? '西安',
				'receiver_phone' => $order['user_phone'],
				'user_name' => $order['real_name'],
				'from_address' => $station['station_address'] ?? '',
				'to_address' => $order['user_address'],
				'info' => $data['remark'],
				'status' => $res['status'] ?? 0,
				'station_type' => $type,
				'to_lat' => $order['latitude'],
				'to_lng' => $order['longitude'],
				'from_lat' => $station['lat'] ?? '',
				'from_lng' => $station['lng'] ?? '',
				'distance' => $priceData['distance'],
				'fee' => $priceData['fee'] ?? $priceData['need_paymoney'] ?? 0,
				'mark' => $data['remark'],
				'uid' => $order['uid'],
				'add_time' => time()
			];
			//入库操作
			$this->dao->save($ret);
			return true;
		} catch (\Throwable $e) {
			if (isset($res['status']) && $res['status']  == 'success'){
				$error['origin_id'] = $orderSn;
				$error['reason'] = $type == 1 ? 36 : '信息错误';
				$error['delivery_no'] = $type == 2 ? $res['ordercode'] : $priceData['deliveryNo'];
				$service->cancelOrder($error);
			}
			throw new ValidateException($e->getMessage());
		}

    }

	/**
 	* 配送订单详情
	* @param int $id
	* @return array|\think\Model
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function detail(int $id)
    {
        $res = $this->dao->get($id, ['orderInfo']);
		if (!$res) {
			throw new ValidateException('配送订单不存在，或已取消');
		}
		$res = $res->toArray();
        $order = DeliverySevices::init((int)$res['station_type'])->getOrderDetail($res);
		if (!$res) throw new ValidateException('订单不存在');
        $res['data'] = [
            'order_id' => $order['order_code'],
            'to_address' => $order['to_address'],
            'from_address' => $order['from_address'],
            'state' => $order['state'],
            'note' => $order['note'],
            'order_price' => $order['order_price'],
            'distance' => round(($order['distance'] / 1000),2) . ' km',
        ];
        return $res;
    }

	/**
 	* 删除
	* @param int $id
	* @return mixed
	*/
	public function delete(int $id)
    {
        $res = $this->dao->get($id);
        if (!$res) throw new ValidateException('订单不存在');
        return $this->dao->delete($id);
    }

	/**
	* @param $id
	* @return mixed
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
	public function cancelForm($id)
    {
        $order = $this->dao->get($id);
        if (!$order) throw new ValidateException('订单不存在');
        if ($order['status'] == -1) throw new ValidateException('订单已取消，无法操作');
		$field = [];
		if ($order['station_type'] == 1) {
			$options = DeliverySevices::init(1)->reasons();
			$field[] = Form::select('reason', '取消原因')->setOptions(Form::setOptions($options))->filterable(1)->col(12);
			$field[] = Form::input('cancel_reason', '其他原因说明')->required('请输入原因');
		} else {
			$field[] = Form::input('reason', '取消原因')->required('请输入原因');
		}
		return create_form('取消同城配送订单', $field, Url::buildUrl('/order/delivery_order/cancel/'. $id), 'POST');
    }

	/**
 	* 取消订单
	* @param int $id
	* @param array $reason
	* @return mixed
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function cancel(int $id, array $reason)
    {
		$order = $this->dao->get($id);
        if (!$order) throw new ValidateException('配送订单不存在');
        if ($order['status'] == -1) throw new ValidateException('请勿重复操作');
        $data = [
            'origin_id' => $order['order_id'],
            'order_code'=> $order['delivery_no'],
            'reason'    => $reason['reason'],
            'cancel_reason' => $reason['cancel_reason'],
        ];
        $this->transaction(function () use($id, $order, $data){
            $mark = $data['reason'];
			$delivery = DeliverySevices::init((int)$order['station_type']);
            if ($order['station_type'] == DeliverySevices::DELIVERY_TYPE_DADA) {
                $options = $delivery->reasons();
				if ($options) $options = array_combine(array_column($options, 'value'), $options);
                $mark = $options[$data['reason']]['label'] ?? '';
            }
            if ($data['cancel_reason']) $mark .= ','.$data['cancel_reason'];
            $res = $delivery->cancelOrder($data);
            $deduct_fee = $res['deduct_fee'] ?? 0;
			$this->doCancel($id, $order, $deduct_fee, $mark);

        });
		return true;
    }

	/**
 	* 执行取消订单
	* @param int $id
	* @param $deliveryOrder
	* @param $deduct_fee
	* @param $mark
	* @return bool
	 */
	public function doCancel(int $id , $deliveryOrder, $deduct_fee, $mark)
	{
		//修改配送单
		$this->dao->update($id, ['status' => 1, 'mark' => $mark, 'deduct_fee' => $deduct_fee]);
		//修改愿订单
		/** @var StoreOrderServices $storeOrderServices */
		$storeOrderServices = app()->make(StoreOrderServices::class);
		$storeOrderServices->update($deliveryOrder['oid'], ['status' => 0, 'delivery_type' => '', 'delivery_name' => '', 'delivery_id' => '', 'delivery_uid' => '']);
		/** @var StoreOrderStatusServices $statusServices */
		$statusServices = app()->make(StoreOrderStatusServices::class);
		$statusServices->save([
			'oid' => $deliveryOrder['oid'],
			'change_type' => 'city_delivery_cancel',
			'change_message' => '同城配送取消',
			'change_time' => time()
		]);
		return true;
	}


	/**
     * TODO 回调
     * @param $data
     * @author Qinii
     * @day 2/17/22
     */
    public function notify($data)
    {
        //达达
        /**
         * 订单状态(待接单＝1,待取货＝2,配送中＝3,已完成＝4,已取消＝5, 指派单=8,妥投异常之物品返回中=9, 妥投异常之物品返回完成=10, 骑士到店=100,创建达达运单失败=1000 可参考文末的状态说明）
         */
        Log::info('同城回调参数：'.var_export(['=======',$data,'======='],1));
        if (isset($data['data'])) {
            $data  = json_decode($data['data'], 1);
        }

        $reason = '';
        $deductFee = 0;
        $delivery = [];
        if (isset($data['order_status'])){
            $order_id = $data['order_id'];
            if ($data['order_status'] == 1) {
                $orderData = $this->dao->getOne(['order_id' => $data['order_id']]);
                if (!$orderData['finish_code']) {
                    $orderData->finish_code = $data['finish_code'];
                    $orderData->save();
                }
                return ;
            } else if (in_array( $data['order_status'],[2,3,4,5,9,10,100])){
                $status =  $data['order_status'];
                if ($data['order_status'] == 5){
                    $msg = [
                        '取消：',
                        '达达配送员取消：',
                        '商家主动取消：',
                        '系统或客服取消：',
                    ];
                    //1:达达配送员取消；2:商家主动取消；3:系统或客服取消；0:默认值
                    $status = -1;
                    $reason = $msg[$data['cancel_from']].$data['cancel_reason'];
                }
                $deductFee = $data['deductFee'] ?? 0;
                if (isset($data['dm_name']) && $data['dm_name']) {
                    $delivery = [
                        'delivery_name' => $data['dm_name'],
                        'delivery_id'  => $data['dm_mobile'],
                    ];
                }

            }
        } else if (isset($data['state'])){  //uu
            if (!$data['origin_id']) $deliveryOrder = $this->dao->getOne(['delivery_no' => $data['order_code']]);
            $order_id = $data['origin_id'] ?: $deliveryOrder['order_id'] ;
            //当前状态 1下单成功 3跑男抢单 4已到达 5已取件 6到达目的地 10收件人已收货 -1订单取消
            switch ($data['state']) {
                case 3:
                    $status = 2;
                    break;
                case 4:
                    $status = 100;
                    break;
                case 5:
                    $status = 3;
                    break;
                case 10:
                    $status = 4;
                    break;
                case -1:
                    $status = -1;
                    $reason = $data['state_text'];
                    break;
                default:
                    break;
            }
            if (isset($data['driver_name']) && $data['driver_name']) {
                $delivery = [
                    'delivery_name' => $data['driver_name'],
                    'delivery_id'  => $data['driver_mobile'],
                ];
            }
        }

        if (isset($order_id) && isset($status)){
            $deliveryOrder = $this->dao->getOne(['order_id' => $order_id]);
            if ($deliveryOrder) {
                $this->notifyAfter($status, $reason, $deliveryOrder, $delivery, $deductFee);
            }else {
                Log::info('同城配送回调，未查询到订单：'.$order_id);
            }
        }
    }

	/**
	* @param $status
	* @param $reason
	* @param $res
	* @param $data
	* @param $deductFee
	* @return bool
	 */
    public function notifyAfter($status, $reason, $deliveryOrder, $data, $deductFee)
    {
        if (!isset($this->statusData[$status])) return true;
		/** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $orderData = $orderServices->get($deliveryOrder['oid']);

        if ($orderData['status'] != $status ) {
			$oid = (int)$deliveryOrder['oid'];
			$order = $orderServices->get($oid);
			//修改配送单
			$this->dao->update($deliveryOrder['id'], ['status' => $status, 'reason' => $reason]);
			//增加
            $message = '订单已配送【'. $this->statusData[$status].'】';
			/** @var StoreOrderStatusServices $statusServices */
			$statusServices = app()->make(StoreOrderStatusServices::class);
			$statusServices->save([
				'oid' => $oid,
				'change_type' => 'city_delivery_' . $status,
				'change_message' => $message,
				'change_time' => time()
			]);
			switch ($status) {
				case 2:
					if (!empty($data)) $orderServices->update($oid, $data);
					break;
				case 4:
					$orderServices->update($oid, ['status' => 2]);
					//订单收货
            		OrderTakeJob::dispatch([$order]);
					break;
				case -1:
					$this->doCancel((int)$deliveryOrder['id'], $deliveryOrder, $deductFee , $reason);
					break;
			}
        }
		return true;
    }

	/**
 	* 同步达达门店信息
	* @param int $id
	* @param bool $is_new
	* @param int $type
	* @param int $station_type
	* @return array
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
	public function syncCityShop(int $id, int $type = 1, int $station_type = 1)
	{
		$data = [];
		if ($station_type == 1) {
			$status = sys_config('dada_delivery_status');
		} else {
			$status = sys_config('uu_delivery_status');
		}
		if (!$status) {//未开启
			return $data;
		}
		if ($type == 1) {//门店
			/** @var SystemStoreServices $storeServices */
			$storeServices = app()->make(SystemStoreServices::class);
			$station = $storeServices->getStoreInfo($id);
			/** @var CityAreaServices $cityArea */
			$cityArea = app()->make(CityAreaServices::class);
			$station['city_name'] = $cityArea->value(['id' => $station['city']], 'name');
			$station['address'] = $station['detailed_address'];
			if (!$station['city_shop_id']) {
				$station['city_shop_id'] = 'deliver_store_' . $station['id'] . '_'. $this->getOrderSn();
				$storeServices->update($id, ['city_shop_id' => $station['city_shop_id']]);
			}
		} elseif ($type == 2){//供应商
			/** @var SystemSupplierServices $services */
			$services = app()->make(SystemSupplierServices::class);
			$station = $services->getSupplierInfo($id);
			if ($station) {
				$station['name'] = $station['supplier_name'] ?? '';
			}
		} else {//平台
			$station = SystemConfigService::more(['refund_address', 'refund_name', 'refund_phone']);
			$address = $station['refund_address'];
			if ($address) {
				$station['address'] = $station['refund_address'];
				$station['name'] = $station['refund_name'];
				$station['phone'] = $station['refund_phone'];
				$station['city_shop_id'] = $this->platCityShopId;
			}
		}
		if (!isset($station['latitude'])) {//地址转经纬度
			$addressArr = $this->addressHandle($station['address']);
			if (!$addressArr['province'] || !$addressArr['city']) {
			    throw new ValidateException($type == 0 ? '请检查（设置->商城设置->交易设置->退货收货人地址）完整性' : '请检查该订单关联供应商地址信息是否正确');
			}
			$station['city_name'] = $addressArr['city'] ?? '';
			try {
				$addres = $this->lbs_address($station['city_name'], $station['address']);
				$station['latitude'] = $addres['location']['lat'] ?? '';
				$station['longitude'] = $addres['location']['lng'] ?? '';
			} catch (\Exception $e) {
				throw new ValidateException('获取经纬度失败');
			}
		}
		$data = [
			'lng' => (float)($station['longitude'] ?? 0),
			'lat' => (float)($station['latitude'] ?? 0),
			'phone' => $station['phone'] ?? '',
			'business' => (int)($station['business'] ?? 5),
			'contact_name' => $station['name'] ?? '',
			'station_name' => $station['name'],
			'station_address' => $station['address'] ?? '',
			'status' => 1,
			'origin_shop_id' => $station['city_shop_id'],
		];
		if ($data) {
			$serve = DeliverySevices::init($station_type);
			try {
				$shop = $serve->getShopDetail($data['origin_shop_id']);
 			} catch (\Throwable $e) {
				$shop = [];
 			}
			try {
				if (!$shop) {
					$serve->addShop($data);
				} else {
					$serve->updateShop($data);
				}
			} catch (\Throwable $e) {
				throw new ValidateException('创建达达门店失败，原因：' . $e->getMessage());
			}
		}
		$data['city_name'] = $station['city_name'];
		return $data;
	}


}
