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

namespace app\services\activity\integral;

use app\dao\activity\integral\StoreIntegralOrderDao;
use app\services\activity\lottery\LuckLotteryServices;
use app\services\BaseServices;
use app\services\message\notice\NoticeSmsService;
use app\services\message\SystemMessageServices;
use app\services\pay\PayServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\product\sku\StoreProductVirtualServices;
use app\services\serve\ServeServices;
use app\services\other\ExpressServices;
use app\services\user\UserServices;
use app\services\user\UserAddressServices;
use app\services\user\UserBillServices;
use crmeb\services\FormBuilder as Form;
use crmeb\services\printer\Printer;
use crmeb\traits\ServicesTrait;
use think\exception\ValidateException;
use think\facade\Log;

/**
 * Class StoreIntegralOrderServices
 * @package app\services\activity\integral
 * @mixin StoreIntegralOrderDao
 */
class StoreIntegralOrderServices extends BaseServices
{

    use ServicesTrait;

    /**
     * 发货类型
     * @var string[]
     */
    public $deliveryType = ['send' => '商家配送', 'express' => '快递配送', 'fictitious' => '虚拟发货'];

    /**
     * StoreIntegralOrderServices constructor.
     * @param StoreIntegralOrderDao $dao
     */
    public function __construct(StoreIntegralOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderList(array $where, array $field = ['*'], array $with = [])
    {
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getOrderList($where, $field, $page, $limit, $with);
        $count = $this->dao->count($where);
        $data = $this->tidyOrderList($data);
        $batch_url = "file/upload/1";
        return compact('data', 'count', 'batch_url');
    }

    /**
     * 获取导出数据
     * @param array $where
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExportList(array $where, int $limit = 0)
    {
        if ($limit) {
            [$page] = $this->getPageValue();
        } else {
            [$page, $limit] = $this->getPageValue();
        }
        $data = $this->dao->getOrderList($where, ['*'], $page, $limit);
        $data = $this->tidyOrderList($data);
        return $data;
    }

    /**
     * 前端订单列表
     * @param array $where
     * @param array|string[] $field
     * @param array $with
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderApiList(array $where, array $field = ['*'], array $with = [])
    {
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getOrderList($where, $field, $page, $limit, $with);
        $data = $this->tidyOrderList($data);
        return $data;
    }

    /**
     * 订单详情数据格式化
     * @param $order
     * @return mixed
     */
    public function tidyOrder($order)
    {
        $order['_add_time'] = $order['add_time'] = date('Y-m-d H:i:s', $order['add_time']);
        if ($order['status'] == 1) {
            $order['status_name'] = '未发货';
        } else if ($order['status'] == 2) {
            $order['status_name'] = '待收货';
        } else if ($order['status'] == 3) {
            $order['status_name'] = '已完成';
        }
        return $order;
    }

    /**
     * 数据转换
     * @param array $data
     * @return array
     */
    public function tidyOrderList(array $data)
    {
        foreach ($data as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            if ($item['status'] == 1) {
                $item['status_name'] = '未发货';
            } else if ($item['status'] == 2) {
                $item['status_name'] = '待收货';
            } else if ($item['status'] == 3) {
                $item['status_name'] = '已完成';
            }
        }
        return $data;
    }

    /**
     * 创建订单
     * @param $uid
     * @param $addressId
     * @param string $mark
     * @param $user
     * @param $num
     * @param $productInfo
     * @throws \Exception
     */
    public function createOrder($uid, $addressId, $payType, $mark, $userInfo, $num, $productInfo, $customForm)
    {
        if ($productInfo['product_type'] == 0 && !$addressId) {
            throw new ValidateException('请选择收货地址!');
        }
        if ($addressId) {
            /** @var UserAddressServices $addressServices */
            $addressServices = app()->make(UserAddressServices::class);
            if (!$addressInfo = $addressServices->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]))
                throw new ValidateException('地址选择有误!');
            $addressInfo = $addressInfo->toArray();
        } else {
            $addressInfo = [];
        }

        $total_price = bcmul($productInfo['attrInfo']['price'], $num, 2);
        $total_integral = bcmul($productInfo['attrInfo']['integral'], $num, 2);
        if ($total_integral > $userInfo['integral']) throw new ValidateException('积分不足!');
        if ($total_price <= 0) {
            $paid = 1;
            $orderInfo['pay_time'] = time();
            $payType = 'integral';
        } else {
            $paid = 0;
        }
        $productInfo['cart_num'] = $num;
        $orderInfo = [
            'uid' => $uid,
            'order_id' => $this->getNewOrderId(),
            'real_name' => $addressInfo['real_name'] ?? '',
            'user_phone' => $addressInfo['phone'] ?? '',
            'user_address' => $addressInfo ? $addressInfo['province'] . ' ' . $addressInfo['city'] . ' ' . $addressInfo['district'] . ' ' . $addressInfo['detail'] : '',
            'product_id' => $productInfo['id'],
            'image' => $productInfo['attrInfo']['image'] ?? '',
            'store_name' => $productInfo['title'],
            'suk' => $productInfo['attrInfo']['suk'] ?? '',
            'unique' => $productInfo['attrInfo']['unique'] ?? '',
            'total_num' => $num,
            'price' => $productInfo['attrInfo']['price'] ?? '',
            'total_price' => $total_price,
            'integral' => $productInfo['attrInfo']['integral'] ?? '',
            'total_integral' => $total_integral,
            'paid' => $paid,
            'pay_type' => $payType,
            'add_time' => time(),
            'status' => 1,
            'mark' => $mark,
            'channel_type' => $userInfo['user_type'],
            'product_type' => $productInfo['product_type'],
            'custom_form' => json_encode($customForm),
            'cart_info' => json_encode([$productInfo])
        ];
        $order = $this->transaction(function () use ($orderInfo, $userInfo, $productInfo, $uid, $num, $total_integral, $total_price) {
            //创建订单
            $order = $this->dao->save($orderInfo);
            if (!$order) {
                throw new ValidateException('订单生成失败!');
            }
            //扣库存
            $this->decGoodsStock($productInfo, $num);
            if ($total_price <= 0) {
                //减积分
                $this->deductIntegral($userInfo, $total_integral, (int)$userInfo['uid'], $order->id);
                //卡密发放
                if ($orderInfo['product_type']) {
                    $this->sendCard((int)$order->id);
                    //修改订单状态
                    $this->dao->update((int)$order->id, ['status' => 3]);
                }
            }
            return $order;
        });
        /** @var StoreIntegralOrderStatusServices $statusService */
        $statusService = app()->make(StoreIntegralOrderStatusServices::class);
        $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'cache_key_create_order',
            'change_message' => '订单生成',
            'change_time' => time()
        ]);
        return $order;
    }

    /**
     * 抵扣积分
     * @param array $userInfo
     * @param bool $useIntegral
     * @param array $priceData
     * @param int $uid
     * @param string $key
     */
    public function deductIntegral(array $userInfo, $priceIntegral, int $uid, string $orderId)
    {
        $res2 = true;
        if ($userInfo['integral'] > 0) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            if ($userInfo['integral'] > $priceIntegral) {
                $integral = bcsub((string)$userInfo['integral'], (string)$priceIntegral);
            } else {
                $integral = 0;
            }
            $res2 = $userServices->update($uid, ['integral' => $integral]);
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            $res3 = $userBillServices->income('storeIntegral_use_integral', $uid, (int)$priceIntegral, (int)$integral, $orderId);
            $res2 = $res2 && false != $res3;
        }
        if (!$res2) {
            throw new ValidateException('使用积分抵扣失败!');
        }
    }

    /**
     * 扣库存
     * @param array $cartInfo
     * @param int $combinationId
     * @param int $seckillId
     * @param int $bargainId
     */
    public function decGoodsStock(array $productInfo, int $num)
    {
        $res5 = true;
        /** @var StoreIntegralServices $StoreIntegralServices */
        $StoreIntegralServices = app()->make(StoreIntegralServices::class);
        try {
            $res5 = $res5 && $StoreIntegralServices->decIntegralStock((int)$num, $productInfo['attrInfo']['product_id'] ?? 0, $productInfo['attrInfo']['unique'] ?? '');
            if (!$res5) {
                throw new ValidateException('库存不足!');
            }
        } catch (\Throwable $e) {
            throw new ValidateException('库存不足!');
        }
    }

    /**
     * 发送卡密
     * @param int $id
     * @param array $orderInfo
     * @return bool
     */
    public function sendCard(int $id, $orderInfo = [])
    {
        if (!$id && !$orderInfo) return false;

        if (!$orderInfo) {
            $orderInfo = $this->dao->get($id);
        }
        if ($orderInfo['fictitious'] && $orderInfo['fictitious'] == 'fictitious') {
            return true;
        }
        try {
            switch ($orderInfo['product_type']) {
                case 1:
                    /** @var SystemMessageServices $SystemMessageServices */
                    $SystemMessageServices = app()->make(SystemMessageServices::class);
                    /** @var StoreIntegralOrderStatusServices $statusService */
                    $statusService = app()->make(StoreIntegralOrderStatusServices::class);
                    $orderInfo['cart_info'] = is_string($orderInfo['cart_info']) ? json_decode($orderInfo['cart_info'], true) : $orderInfo['cart_info'];

                    $title = $content = $disk_info = $virtual_info = '';
                    $unique = $orderInfo['unique'];
                    //活动订单共用原商品规格卡密
                    /** @var StoreProductAttrValueServices $skuValueServices */
                    $skuValueServices = app()->make(StoreProductAttrValueServices::class);
                    $attrValue = $skuValueServices->getUniqueByActivityUnique($unique, (int)$orderInfo['product_id'], 4, ['unique', 'disk_info']);
                    if ($attrValue) {
                        $disk_info = $attrValue['disk_info'] ?? '';
                        $unique = $attrValue['unique'] ?? '';
                    }
                    if ($disk_info) {
                        $title = '虚拟密钥发放';
                        $content = '您购买的密钥商品已支付成功，';
                        if ($orderInfo['total_price'] > 0) {
                            $content .= '支付金额' . $orderInfo['total_price'] . '元，';
                        } elseif ($orderInfo['total_integral'] > 0) {
                            $content .= '支付积分' . $orderInfo['total_integral'];
                        }
                        $content .= '，订单号：' . $orderInfo['order_id'] . '，密钥：' . $disk_info . '，感谢您的光临！';
                        $virtual_info = '密钥自动发放：' . $disk_info;
                        $value = '密钥:' . $disk_info;
//                        $remark = '密钥自动发放：' . $disk_info;
                    } else {
                        /** @var StoreProductVirtualServices $virtualService */
                        $virtualService = app()->make(StoreProductVirtualServices::class);
                        $cardList = $virtualService->getOrderCardList(['store_id' => $orderInfo['store_id'], 'attr_unique' => $unique, 'uid' => 0], (int)$orderInfo['total_num']);
                        $title = '虚拟卡密发放';
                        $virtual_info = '卡密已自动发放';
                        $value = '';
//                        $remark = '卡密已自动发放';
                        if ($cardList) {
                            $content = '您购买的密钥商品已支付成功，';
                            if ($orderInfo['total_price'] > 0) {
                                $content .= '支付金额' . $orderInfo['total_price'] . '元，';
                            } elseif ($orderInfo['total_integral'] > 0) {
                                $content .= '支付积分' . $orderInfo['total_integral'];
                            }
                            $content .= '，订单号：' . $orderInfo['order_id'];
                            $update = [];
                            $update['order_id'] = $orderInfo['order_id'];
                            $update['uid'] = $orderInfo['uid'];
                            $update['order_type'] = 2;
                            foreach ($cardList as $virtual) {
                                $virtualService->update($virtual['id'], $update);
                                $content .= '，卡号：' . $virtual['card_no'] . '；密码：' . $virtual['card_pwd'] . "\n";
                                $virtual_info .= '，卡号：' . $virtual['card_no'] . '；密码：' . $virtual['card_pwd'] . ';';
                                $value .= '卡号:' . $virtual['card_no'] . '；密码:' . $virtual['card_pwd'];
//                                $remark .= '，卡号：' . $virtual['card_no'] . '；密码：' . $virtual['card_pwd'] . ';';
                            }
                            $content .= '，感谢您的光临！';
                        }
                    }
                    //修改订单虚拟备注
                    $this->dao->update(['id' => $orderInfo['id']], ['status' => 1, 'delivery_type' => 'fictitious', 'virtual_info' => $virtual_info]);
                    $data['id'] = $orderInfo['id'];
                    $data['uid'] = $orderInfo['uid'];
                    $data['order_id'] = $orderInfo['order_id'];
                    $data['title'] = $title;
                    $data['value'] = $value;
                    $data['content'] = $content;
                    $data['is_integral'] = 1;
                    event('notice.notice', [$data, 'kami_deliver_goods_code']);
                    $statusService->save([
                        'oid' => $orderInfo['id'],
                        'change_type' => 'delivery_fictitious',
                        'change_message' => '卡密自动发货',
                        'change_time' => time()
                    ]);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('订单虚拟商品自动发放失败，原因：' . $e->getMessage());
        }
        return true;
    }

    /**
     * 使用雪花算法生成订单ID
     * @return string
     * @throws \Exception
     */
    public function getNewOrderId(string $prefix = 'wx')
    {
        $snowflake = new \Godruoyi\Snowflake\Snowflake();
        //32位
        if (PHP_INT_SIZE == 4) {
            $id = abs($snowflake->id());
        } else {
            $id = $snowflake->setStartTimeStamp(strtotime('2020-06-05') * 1000)->id();
        }
        return $prefix . $id;
    }

    /**
     *获取订单数量
     * @param array $where
     * @return mixed
     */
    public function orderCount(array $where)
    {
        //全部订单
        $data['statusAll'] = (string)$this->dao->count($where + ['is_system_del' => 0]);
        //未发货
        $data['unshipped'] = (string)$this->dao->count($where + ['status' => 1, 'is_system_del' => 0]);
        //待收货
        $data['untake'] = (string)$this->dao->count($where + ['status' => 2, 'is_system_del' => 0]);
        //待评价
//        $data['unevaluate'] = (string)$this->dao->count(['status' => 3, 'time' => $where['time'], 'is_system_del' => 0]);
        //交易完成
        $data['complete'] = (string)$this->dao->count($where + ['status' => 3, 'is_system_del' => 0]);
        return $data;
    }


    /**
     * 打印订单
     * @param $order
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderPrint($order)
    {
        $data = [
            'clientId' => sys_config('printing_client_id', ''),
            'apiKey' => sys_config('printing_api_key', ''),
            'partner' => sys_config('develop_id', ''),
            'terminal' => sys_config('terminal_number', '')
        ];
        if (!$data['clientId'] || !$data['apiKey'] || !$data['partner'] || !$data['terminal']) {
            throw new ValidateException('请先配置小票打印开发者');
        }
        $printer = new Printer('yi_lian_yun', $data);
        $res = $printer->setIntegralPrinterContent([
            'name' => sys_config('site_name'),
            'orderInfo' => is_object($order) ? $order->toArray() : $order,
        ])->startPrinter();
        if (!$res) {
            throw new ValidateException($printer->getError());
        }
        return $res;
    }

    /**
     * 获取订单确认数据
     * @param array $user
     * @param $cartId
     * @return mixed
     */
    public function getOrderConfirmData(array $user, $unique, $num)
    {
        /** @var StoreProductAttrValueServices $StoreProductAttrValueServices */
        $StoreProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        $attrValue = $StoreProductAttrValueServices->uniqueByField($unique, 4);
        if (!$attrValue) {
            throw new ValidateException('请重新选择商品规格');
        }
        /** @var StoreIntegralServices $storeIntrgralServices */
        $storeIntrgralServices = app()->make(StoreIntegralServices::class);
        $productInfo = $storeIntrgralServices->getIntegralOne((int)$attrValue['product_id']);
        if (!$productInfo) {
            throw new ValidateException('该商品已下架');
        }
        $data = [];
        $attrValue = is_object($attrValue) ? $attrValue->toArray() : $attrValue;
        $productInfo['attrInfo'] = $attrValue;
        $productInfo['unique'] = $attrValue['unique'];
        $productInfo['store_name'] = $productInfo['title'];
        $data['now_money'] = $user['now_money'];
        $data['integral'] = $user['integral'];
        $data['yue_pay_status'] = (int)sys_config('balance_func_status') && (int)sys_config('yue_pay_status') == 1 ? (int)1 : (int)2;//余额支付 1 开启 2 关闭
        $data['pay_weixin_open'] = (int)sys_config('pay_weixin_open') ?? 0;//微信支付 1 开启 0 关闭
        $data['ali_pay_status'] = (bool)sys_config('ali_pay_status');//支付包支付 1 开启 0 关闭
        $data['num'] = $num;
        $data['total_integral'] = bcmul($num, $attrValue['integral'], 0);
        $data['total_price'] = bcmul($num, $attrValue['price'], 2);
        $data['productInfo'] = $productInfo;
        $custom_form = $productInfo['custom_form'] ?? [];
        $data['custom_form'] = is_string($custom_form) ? json_decode($custom_form, true) : $custom_form;
        return $data;
    }

    /**
     * 删除订单
     * @param $uni
     * @param $uid
     * @return bool
     */
    public function removeOrder(string $order_id, int $uid)
    {
        $order = $this->getUserOrderDetail($order_id, $uid);
        if ($order['status'] != 3)
            throw new ValidateException('该订单无法删除!');

        $order->is_del = 1;
        /** @var StoreIntegralOrderStatusServices $statusService */
        $statusService = app()->make(StoreIntegralOrderStatusServices::class);
        $res = $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'remove_order',
            'change_message' => '删除订单',
            'change_time' => time()
        ]);
        if ($order->save() && $res) {
            return true;
        } else
            throw new ValidateException('订单删除失败!');
    }

    /**
     * 订单发货
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function delivery(int $id, array $data)
    {
        $orderInfo = $this->dao->get($id);
        if (!$orderInfo) {
            throw new ValidateException('订单未能查到,不能发货!');
        }
        if ($orderInfo->is_del) {
            throw new ValidateException('订单已删除,不能发货!');
        }
        if ($orderInfo->status != 1) {
            throw new ValidateException('订单已发货请勿重复操作!');
        }
        $type = (int)$data['type'];
        unset($data['type']);
        switch ($type) {
            case 1:
                //发货
                $this->orderDeliverGoods($id, $data, $orderInfo);
                break;
            case 2:
                $this->orderDelivery($id, $data, $orderInfo);
                break;
            case 3:
                $this->orderVirtualDelivery($id, $data, $orderInfo);
                break;
            default:
                throw new ValidateException('暂时不支持其他发货类型');
        }
        return true;
    }

    /**
     * 虚拟发货
     * @param int $id
     * @param array $data
     */
    public function orderVirtualDelivery(int $id, array $data)
    {
        $data['delivery_type'] = 'fictitious';
        $data['status'] = 2;
        unset($data['sh_delivery_name'], $data['sh_delivery_id'], $data['delivery_name'], $data['delivery_id']);
        //保存信息
        /** @var StoreIntegralOrderStatusServices $services */
        $services = app()->make(StoreIntegralOrderStatusServices::class);
        $this->transaction(function () use ($id, $data, $services) {
            $this->dao->update($id, $data);
            $services->save([
                'oid' => $id,
                'change_type' => 'delivery_fictitious',
                'change_message' => '已虚拟发货',
                'change_time' => time()
            ]);
        });
    }

    /**
     * 订单配送
     * @param int $id
     * @param array $data
     */
    public function orderDelivery(int $id, array $data, $orderInfo)
    {
        $data['delivery_type'] = 'send';
        $data['delivery_name'] = $data['sh_delivery_name'];
        $data['delivery_id'] = $data['sh_delivery_id'];
        $data['delivery_uid'] = $data['sh_delivery_uid'];
//        获取核销码
        $data['verify_code'] = $this->getStoreCode();
        unset($data['sh_delivery_name'], $data['sh_delivery_id'], $data['sh_delivery_uid']);
        if (!$data['delivery_name']) {
            throw new ValidateException('请输入送货人姓名');
        }
        if (!$data['delivery_id']) {
            throw new ValidateException('请输入送货人电话号码');
        }
        if (!$data['delivery_uid']) {
            throw new ValidateException('请输入送货人信息');
        }
        if (!check_phone($data['delivery_id'])) {
            throw new ValidateException('请输入正确的送货人电话号码');
        }
        $data['status'] = 2;
        $orderInfo->delivery_type = $data['delivery_type'];
        $orderInfo->delivery_name = $data['delivery_name'];
        $orderInfo->delivery_id = $data['delivery_id'];
        $orderInfo->status = $data['status'];
        /** @var StoreIntegralOrderStatusServices $services */
        $services = app()->make(StoreIntegralOrderStatusServices::class);
        $this->transaction(function () use ($id, $data, $services) {
            $this->dao->update($id, $data);
            //记录订单状态
            $services->save([
                'oid' => $id,
                'change_type' => 'delivery',
                'change_time' => time(),
                'change_message' => '已配送 发货人：' . $data['delivery_name'] . ' 发货人电话：' . $data['delivery_id']
            ]);
        });
        return true;
    }

    /**
     * 订单快递发货
     * @param int $id
     * @param array $data
     */
    public function orderDeliverGoods(int $id, array $data, $orderInfo)
    {
        if (!$data['delivery_name']) {
            throw new ValidateException('请选择快递公司');
        }
        $data['delivery_type'] = 'express';
        if ($data['express_record_type'] == 2) {//电子面单
            if (!$data['delivery_code']) {
                throw new ValidateException('快递公司编缺失');
            }
            if (!$data['express_temp_id']) {
                throw new ValidateException('请选择电子面单模板');
            }
            if (!$data['to_name']) {
                throw new ValidateException('请填写寄件人姓名');
            }
            if (!$data['to_tel']) {
                throw new ValidateException('请填写寄件人电话');
            }
            if (!$data['to_addr']) {
                throw new ValidateException('请填写寄件人地址');
            }
            /** @var ServeServices $ServeServices */
            $ServeServices = app()->make(ServeServices::class);
            $expData['com'] = $data['delivery_code'];
            $expData['to_name'] = $orderInfo->real_name;
            $expData['to_tel'] = $orderInfo->user_phone;
            $expData['to_addr'] = $orderInfo->user_address;
            $expData['from_name'] = $data['to_name'];
            $expData['from_tel'] = $data['to_tel'];
            $expData['from_addr'] = $data['to_addr'];
            $expData['siid'] = sys_config('config_export_siid');
            $expData['temp_id'] = $data['express_temp_id'];
            $expData['count'] = $orderInfo->total_num;
            $expData['cargo'] = $orderInfo->store_name . '(' . $orderInfo->suk . ')*' . $orderInfo->total_num;
            $expData['order_id'] = $orderInfo->order_id;
            if (!sys_config('config_export_open', 0)) {
                throw new ValidateException('系统通知：电子面单已关闭，请选择其他发货方式！');
            }
            $dump = $ServeServices->express()->dump($expData);
            $orderInfo->delivery_id = $dump['kuaidinum'];
            $data['express_dump'] = json_encode([
                'com' => $expData['com'],
                'from_name' => $expData['from_name'],
                'from_tel' => $expData['from_tel'],
                'from_addr' => $expData['from_addr'],
                'temp_id' => $expData['temp_id'],
                'cargo' => $expData['cargo'],
            ]);
            $data['delivery_id'] = $dump['kuaidinum'];
        } else {
            if (!$data['delivery_id']) {
                throw new ValidateException('请输入快递单号');
            }
            $orderInfo->delivery_id = $data['delivery_id'];
        }
        $data['status'] = 2;
        $orderInfo->delivery_type = $data['delivery_type'];
        $orderInfo->delivery_name = $data['delivery_name'];
        $orderInfo->status = $data['status'];
        /** @var StoreIntegralOrderStatusServices $services */
        $services = app()->make(StoreIntegralOrderStatusServices::class);
        $this->transaction(function () use ($id, $data, $services) {
            $res = $this->dao->update($id, $data);
            $res = $res && $services->save([
                    'oid' => $id,
                    'change_time' => time(),
                    'change_type' => 'delivery_goods',
                    'change_message' => '已发货 快递公司：' . $data['delivery_name'] . ' 快递单号：' . $data['delivery_id']
                ]);
            if (!$res) {
                throw new ValidateException('发货失败：数据保存不成功');
            }
        });
        return true;
    }

    /**
     * 核销订单生成核销码
     * @return false|string
     */
    public function getStoreCode()
    {
        mt_srand();
        list($msec, $sec) = explode(' ', microtime());
        $num = time() + mt_rand(10, 999999) . '' . substr($msec, 2, 3);//生成随机数
        if (strlen($num) < 12)
            $num = str_pad((string)$num, 12, 0, STR_PAD_RIGHT);
        else
            $num = substr($num, 0, 12);
        if ($this->dao->count(['verify_code' => $num])) {
            return $this->getStoreCode();
        }
        return $num;
    }

    /**
     * 获取修改配送信息表单结构
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function distributionForm(int $id)
    {
        if (!$orderInfo = $this->dao->get($id))
            throw new ValidateException('订单不存在');

        $f[] = Form::input('order_id', '订单号', $orderInfo->getData('order_id'))->disabled(1);

        switch ($orderInfo['delivery_type']) {
            case 'send':
                $f[] = Form::input('delivery_name', '送货人姓名', $orderInfo->getData('delivery_name'))->required('请输入送货人姓名');
                $f[] = Form::input('delivery_id', '送货人电话', $orderInfo->getData('delivery_id'))->required('请输入送货人电话');
                break;
            case 'express':
                /** @var ExpressServices $expressServices */
                $expressServices = app()->make(ExpressServices::class);
                $f[] = Form::select('delivery_name', '快递公司', (string)$orderInfo->getData('delivery_name'))->setOptions(array_map(function ($item) {
                    $item['value'] = $item['label'];
                    return $item;
                }, $expressServices->expressSelectForm(['is_show' => 1])))->required('请选择快递公司');
                $f[] = Form::input('delivery_id', '快递单号', $orderInfo->getData('delivery_id'))->required('请填写快递单号');
                break;
        }
        return create_form('配送信息', $f, $this->url('/marketing/integral/order/distribution/' . $id), 'PUT');
    }

    /**
     * 用户订单收货
     * @param string $order_id
     * @param int $uid
     * @return array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function takeOrder(string $order_id, int $uid)
    {
        $order = $this->dao->getUserOrderDetail($order_id, $uid);
        if (!$order) {
            throw new ValidateException('订单不存在!');
        }
        if ($order['status'] != 2) {
            throw new ValidateException('订单状态错误!');
        }
        $order->status = 3;
        /** @var StoreIntegralOrderStatusServices $statusService */
        $statusService = app()->make(StoreIntegralOrderStatusServices::class);
        $res = $order->save() && $statusService->save([
                'oid' => $order['id'],
                'change_type' => 'user_take_delivery',
                'change_message' => '用户已收货',
                'change_time' => time()
            ]);
        if (!$res) {
            throw new ValidateException('收货失败');
        }
        return $order;
    }

    /**
     * 修改配送信息
     * @param int $id 订单id
     * @return mixed
     */
    public function updateDistribution(int $id, array $data)
    {
        $order = $this->dao->get($id);
        if (!$order) {
            throw new ValidateException('数据不存在！');
        }
        switch ($order['delivery_type']) {
            case 'send':
                if (!$data['delivery_name']) {
                    throw new ValidateException('请输入送货人姓名');
                }
                if (!$data['delivery_id']) {
                    throw new ValidateException('请输入送货人电话号码');
                }
                if (!check_phone($data['delivery_id'])) {
                    throw new ValidateException('请输入正确的送货人电话号码');
                }
                break;
            case 'express':
                if (!$data['delivery_name']) {
                    throw new ValidateException('请选择快递公司');
                }
                if (!$data['delivery_id']) {
                    throw new ValidateException('请输入快递单号');
                }
                break;
            default:
                throw new ValidateException('未发货，请先发货再修改配送信息');
                break;
        }
        /** @var StoreIntegralOrderStatusServices $statusService */
        $statusService = app()->make(StoreIntegralOrderStatusServices::class);
        $statusService->save([
            'oid' => $id,
            'change_type' => 'distribution',
            'change_message' => '修改发货信息为' . $data['delivery_name'] . '号' . $data['delivery_id'],
            'change_time' => time()
        ]);
        return $this->dao->update($id, $data);
    }

    /**
     * 支付成功
     * @param array $orderInfo
     * @param string $paytype
     * @return bool
     */
    public function paySuccess(array $orderInfo, string $paytype = PayServices::WEIXIN_PAY, array $other = [])
    {
        $updata = ['paid' => 1, 'pay_type' => $paytype, 'pay_time' => time()];
        if ($other && isset($other['trade_no'])) {
            $updata['trade_no'] = $other['trade_no'];
        }
        if ($other && isset($other['userInfo'])) {
            $userInfo = $other['userInfo'];
        } else {
            /** @var UserServices $services */
            $services = app()->make(UserServices::class);
            $userInfo = $services->getUserInfo($orderInfo['uid']);
            if ($userInfo) {
                $userInfo = $userInfo->toArray();
            } else {
                throw new ValidateException('用户信息不存在!');
            }
        }
        $res1 = $this->dao->update($orderInfo['id'], $updata);
        if ($res1) {
            //减积分
            $this->deductIntegral($userInfo, $orderInfo['total_integral'], (int)$userInfo['uid'], $orderInfo['id']);
            //卡密发放
            if ($orderInfo['product_type']) {
                $this->sendCard((int)$orderInfo['id']);
                //修改订单状态
                $this->dao->update((int)$orderInfo['id'], ['status' => 3]);
            }
        }
        $res = $res1;
        return false !== $res;
    }

    /**
     * 获取全部积分商品订单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAllIntegralOrderList(array $where)
    {
        $list = $this->dao->getIntegralOrderList($where);
        return $list;
    }
}
