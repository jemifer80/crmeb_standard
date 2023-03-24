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

namespace app\services\message\service;


use app\dao\message\service\StoreServiceLogDao;
use app\services\BaseServices;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;
use app\services\product\product\StoreProductServices;

/**
 * 客服聊天记录
 * Class StoreServiceLogServices
 * @package app\services\message\service
 * @mixin StoreServiceLogDao
 */
class StoreServiceLogServices extends BaseServices
{
    /**
     * 消息类型
     * @var array  1=文字 2=表情 3=图片 4=语音 5 = 商品链接 6 = 订单类型
     */
    const MSN_TYPE = [1, 2, 3, 4, 5, 6, 7];

    /**
     * 商品链接消息类型
     */
    const MSN_TYPE_GOODS = 5;

    /**
     * 订单信息消息类型
     */
    const MSN_TYPE_ORDER = 6;

    /**
     * 退款订单消息类型
     */
    const MSN_TYPE_REFUND_ORDER = 7;

    /**
     * 构造方法
     * StoreServiceLogServices constructor.
     * @param StoreServiceLogDao $dao
     */
    public function __construct(StoreServiceLogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取聊天记录中的uid和to_uid
     * @param int $uid
     * @return array
     */
    public function getChatUserIds(int $uid)
    {
        $list = $this->dao->getServiceUserUids($uid);
        $arr_user = $arr_to_user = [];
        foreach ($list as $key => $value) {
            array_push($arr_user, $value["uid"]);
            array_push($arr_to_user, $value["to_uid"]);
        }
        $uids = array_merge($arr_user, $arr_to_user);
        $uids = array_flip(array_flip($uids));
        $uids = array_flip($uids);
        unset($uids[$uid]);
        return array_flip($uids);
    }

    /**
     * 获取某个用户的客服聊天记录
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getChatLogList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getServiceList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取聊天记录列表
     * @param array $where
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getChatList(array $where, int $uid)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getServiceList($where, $page, $limit);
        return $this->tidyChat($list);
    }

    /**
     * 聊天列表格式化
     * @param array $list
     * @param int $uid
     * @return array
     */
    public function tidyChat(array $list)
    {
        $productIds = $orderIds = $productList = $orderInfo = $toUser = $user = $orderIds_refund = [];
        $toUid = $list[0]['to_uid'] ?? 0;
        $uid = $list[0]['uid'] ?? 0;
        foreach ($list as &$item) {
            $item['_add_time'] = $item['add_time'];
            $item['add_time'] = strtotime($item['_add_time']);
            $item['productInfo'] = $item['orderInfo'] = [];
            if ($item['msn_type'] == self::MSN_TYPE_GOODS && $item['msn']) {
                $productIds[] = $item['msn'];
            } elseif ($item['msn_type'] == self::MSN_TYPE_ORDER && $item['msn']) {
                $orderIds[] = $item['msn'];
            } elseif ($item['msn_type'] == self::MSN_TYPE_REFUND_ORDER && $item['msn']) {
                $orderIds_refund[] = $item['msn'];
            }
        }
        if ($productIds) {
            /** @var StoreProductServices $productServices */
            $productServices = app()->make(StoreProductServices::class);
            $where = [
                ['id', 'in', $productIds],
                ['is_del', '=', 0],
                ['is_show', '=', 1],
            ];
            $productList = get_thumb_water($productServices->getProductArray($where, '*', 'id'));
        }
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        if ($orderIds) {
            $orderWhere = [
                ['order_id|unique', 'in', $orderIds],
                ['is_del', '=', 0],
            ];
            $orderInfo = $orderServices->getColumn($orderWhere, '*', 'order_id');
        }
        /** @var StoreOrderRefundServices $orderRefundService */
        $orderRefundService = app()->make(StoreOrderRefundServices::class);
        if ($orderIds_refund) {
            $orderWhere = [
                ['order_id', 'in', $orderIds_refund],
                ['is_del', '=', 0],
            ];
            $orderInfo_refund = $orderRefundService->getColumn($orderWhere, '*', 'order_id');
        }
        if ($toUid && $uid) {
            /** @var StoreServiceRecordServices $recordServices */
            $recordServices = app()->make(StoreServiceRecordServices::class);
            $toUser = $recordServices->get(['user_id' => $uid, 'to_uid' => $toUid], ['nickname', 'avatar']);
            $user = $recordServices->get(['user_id' => $toUid, 'to_uid' => $uid], ['nickname', 'avatar']);
        }

        foreach ($list as &$item) {
            if ($item['msn_type'] == self::MSN_TYPE_GOODS && $item['msn']) {
                $item['productInfo'] = $productList[$item['msn']] ?? [];
            } elseif ($item['msn_type'] == self::MSN_TYPE_ORDER && $item['msn']) {
                $order = $orderInfo[$item['msn']] ?? null;
                if ($order) {
                    $order = $orderServices->tidyOrder($order, true, true);
                    $order['add_time_y'] = date('Y-m-d', $order['add_time']);
                    $order['add_time_h'] = date('H:i:s', $order['add_time']);
                    $item['orderInfo'] = $order;
                } else {
                    $item['orderInfo'] = [];
                }
            } elseif ($item['msn_type'] == self::MSN_TYPE_REFUND_ORDER && $item['msn']) {
                $order_refund = $orderInfo_refund[$item['msn']] ?? null;
                if ($order_refund) {
                    $order_refund['cartInfo'] = json_decode($order_refund['cart_info'], true);
                    $order_refund['add_time_y'] = date('Y-m-d', $order_refund['add_time']);
                    $order_refund['add_time_h'] = date('H:i:s', $order_refund['add_time']);
                    $order_refund['total_num'] = $order_refund['refund_num'];
                    $order_refund['total_price'] = $order_refund['refund_price'];
                    $order_refund['pay_price'] = $order_refund['refund_price'];
                    $item['orderInfo'] = $order_refund;
                } else {
                    $item['orderInfo'] = [];
                }
            }
            $item['msn_type'] = (int)$item['msn_type'];
            if (!isset($item['nickname'])) {
                $item['nickname'] = '';
            }
            if (!isset($item['avatar'])) {
                $item['avatar'] = '';
            }

            if (!$item['avatar'] && !$item['nickname']) {
                if ($item['uid'] == $uid && $item['to_uid'] == $toUid) {
                    $item['nickname'] = $user['nickname'] ?? '';
                    $item['avatar'] = $user['avatar'] ?? '';
                }
                if ($item['uid'] == $toUid && $item['to_uid'] == $uid) {
                    $item['nickname'] = $toUser['nickname'] ?? '';
                    $item['avatar'] = $toUser['avatar'] ?? '';
                }
            }
        }
        return $list;
    }

    /**
     * 获取聊天记录
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param bool $isUp
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getServiceChatList(array $where, int $limit, int $upperId)
    {
        return $this->dao->getChatList($where, $limit, $upperId);
    }
}
