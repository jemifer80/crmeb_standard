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

namespace app\services\agent;

use app\jobs\agent\AutoAgentJob;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderStatusServices;
use app\services\other\QrcodeServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserExtractServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\{QrcodeService, UploadService, wechat\MiniProgram};
use think\exception\ValidateException;

/**
 * 分销员
 * Class AgentManageServices
 * @package app\services\agent
 */
class AgentManageServices extends BaseServices
{

    /**
     * @param array $where
     * @return array
     */
    public function agentSystemPage(array $where, $is_page = true)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $data = $userServices->getAgentUserList($where, '*', $is_page);
        /** @var UserBrokerageServices $userBrokerageServices */
        $userBrokerageServices = app()->make(UserBrokerageServices::class);
        foreach ($data['list'] as &$item) {
            $item['headimgurl'] = $item['avatar'];
            $item['extract_count_price'] = $item['extract'][0]['extract_count_price'] ?? 0;
            $item['extract_count_num'] = $item['extract'][0]['extract_count_num'] ?? 0;
            $item['spread_name'] = $item['spreadUser']['nickname'] ?? '';
            if ($item['spread_name']) {
                $item['spread_name'] .= '/' . $item['spread_uid'];
            }
            $item['spread_count'] = $item['spreadCount'][0]['spread_count'] ?? 0;
            $item['order_price'] = $item['order'][0]['order_price'] ?? 0;
            $item['order_count'] = $item['order'][0]['order_count'] ?? 0;
            $item['broken_commission'] = $userBrokerageServices->getUserFrozenPrice((int)$item['uid']);
            if ($item['broken_commission'] < 0)
                $item['broken_commission'] = 0;
            $item['brokerage_money'] = $item['brokerage'][0]['brokerage_money'] ?? 0;
            if ($item['brokerage_price'] > $item['broken_commission'])
                $item['brokerage_money'] = bcsub($item['brokerage_price'], $item['broken_commission'], 2);
            else
                $item['brokerage_money'] = 0;
            $item['new_money'] = $item['brokerage_price'];
            unset($item['extract'], $item['order'], $item['bill'], $item['spreadUser'], $item['spreadCount']);
        }
        return $data;
    }

    /**
     * 分销头部信息
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSpreadBadge($where)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $uids = $userServices->getAgentUserIds($where);

        //分销员人数
        $data['uids'] = $uids;
        $data['sum_count'] = count($uids);
        $data['spread_sum'] = 0;
        $data['extract_price'] = 0;
        if ($data['sum_count']) {
            //发展会员人数
            $data['spread_sum'] = $userServices->getCount([['spread_uid', 'in', $uids]]);
            //获取某个用户可提现金额
            $data['extract_price'] = $userServices->getSumBrokerage(['uid' => $uids]);
        }
        //分销员人数
        $data['order_count'] = 0;
        $data['pay_price'] = 0;
        $data['pay_price'] = 0;
        $data['extract_count'] = 0;
        if ($data['sum_count']) {
            /** @var StoreOrderServices $storeOrder */
            $storeOrder = app()->make(StoreOrderServices::class);
			$order_where = ['uid' => $uids, 'pid' => 0, 'paid' => 1, 'refund_status' => [0, 3]];
            //订单总数
            $data['order_count'] = $storeOrder->count($order_where);
            //订单金额
            $data['pay_price'] = $storeOrder->sum($order_where, 'pay_price', true);
            //提现次数
            $data['extract_count'] = app()->make(UserExtractServices::class)->getCount([['uid', 'in', $uids], ['status', '=', 1]]);
        }
        return [
            [
                'name' => '分销员人数(人)',
                'count' => $data['sum_count'],
                'className' => 'md-contacts',
                'col' => 6,
            ],
            [
                'name' => '发展会员人数(人)',
                'count' => $data['spread_sum'],
                'className' => 'md-contact',
                'col' => 6,
            ],
            [
                'name' => '订单数(单)',
                'count' => $data['order_count'],
                'className' => 'md-cart',
                'col' => 6,
            ],
            [
                'name' => '订单金额(元)',
                'count' => $data['pay_price'],
                'className' => 'md-bug',
                'col' => 6,
            ],
            [
                'name' => '提现次数(次)',
                'count' => $data['extract_count'],
                'className' => 'md-basket',
                'col' => 6,
            ],
            [
                'name' => '未提现金额(元)',
                'count' => $data['extract_price'],
                'className' => 'ios-at-outline',
                'col' => 6,
            ],
        ];
    }

    /**
     * 推广人列表
     * @param array $where
     * @return mixed
     */
    public function getStairList(array $where)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $data = $userServices->getSairList($where);
        $store_brokerage_statu = sys_config('store_brokerage_statu');
        foreach ($data['list'] as &$item) {
            $item['spread_count'] = $item['spreadCount'][0]['spread_count'] ?? 0;
            $item['order_count'] = $item['order'][0]['order_count'] ?? 0;
            $item['promoter_name'] = $item['is_promoter'] || $store_brokerage_statu == 2 ? '是' : '否';
            $item['add_time'] = $item['add_time'] ? date("Y-m-d H:i:s", $item['add_time']) : '';
        }
        return $data;
    }

    /**
     * 推广人头部信息
     * @param array $where
     * @return array[]
     */
    public function getSairBadge(array $where)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $data['number'] = $userServices->getSairCount($where);
        $where['type'] = 1;
        $data['one_number'] = $userServices->getSairCount($where);
        $where['type'] = 2;
        $data['two_number'] = $userServices->getSairCount($where);

        $col = $data['two_number'] > 0 ? 4 : 6;
        return [
            [
                'name' => '总人数(人)',
                'count' => $data['number'],
                'col' => $col,
            ],
            [
                'name' => '一级人数(人)',
                'count' => $data['one_number'],
                'col' => $col,
            ],
            [
                'name' => '二级人数(人)',
                'count' => $data['two_number'],
                'col' => $col,
            ],
        ];
    }

    /**
     * 推广订单
     * @param array $where
     * @return array
     */
    public function getStairOrderList(int $uid, array $where)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid);
        if (!$userInfo) {
            return ['count' => 0, 'list' => []];
        }
        /** @var StoreOrderServices $storeOrder */
        $storeOrder = app()->make(StoreOrderServices::class);
        $data = $storeOrder->getUserStairOrderList($uid, $where);
        if ($data['list']) {
            $uids = array_unique(array_column($data['list'], 'uid'));
            $userList = [];
            if ($uids) {
                $userList = $userServices->getColumn([['uid', 'IN', $uids]], 'nickname,phone,avatar,real_name', 'uid');
            }
            $orderIds = array_column($data['list'], 'id');
            $orderChangTimes = [];
            if ($orderIds) {
                /** @var StoreOrderStatusServices $storeOrderStatus */
                $storeOrderStatus = app()->make(StoreOrderStatusServices::class);
                $orderChangTimes = $storeOrderStatus->getColumn([['oid', 'IN', $orderIds], ['change_type', '=', 'user_take_delivery']], 'change_time', 'oid');
            }
            foreach ($data['list'] as &$item) {
                $user = $userList[$item['uid']] ?? [];
                $item['user_info'] = '';
                $item['avatar'] = '';
                if (count($user)) {
                    $item['user_info'] = $user['nickname'] . '|' . ($user['phone'] ? $user['phone'] . '|' : '') . $user['real_name'];
                    $item['avatar'] = $user['avatar'];
                }
                $item['brokerage_price'] = $item['spread_uid'] == $uid ? $item['one_brokerage'] : $item['two_brokerage'];
                $item['_pay_time'] = $item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : '';
                $item['_add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '';
                $item['take_time'] = ($change_time = $orderChangTimes[$item['id']] ?? '') ? date('Y-m-d H:i:s', $change_time) : '暂无';
            }
        }
        return $data;
    }

    /**
     * 获取永久二维码
     * @param $type
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function wechatCode(int $uid)
    {
        /** @var QrcodeServices $qrcode */
        $qrcode = app()->make(QrcodeServices::class);
        $code = $qrcode->getForeverQrcode('spread', $uid);
        if (!$code['ticket']) exception('永久二维码获取错误');
        return $code;
    }

    /**
     * 查看小程序推广二维码
     * @param string $uid
     */
    public function lookXcxCode(int $uid)
    {
        $userInfo = app()->make(UserServices::class)->getUserInfo($uid);
        if (!$userInfo) {
            throw new AdminException('数据不存在');
        }
        $name = $userInfo['uid'] . '_' . $userInfo['is_promoter'] . '_user.jpg';
        /** @var SystemAttachmentServices $systemAttachmentModel */
        $systemAttachmentModel = app()->make(SystemAttachmentServices::class);
        $imageInfo = $systemAttachmentModel->getInfo(['name' => $name]);
        if (!$imageInfo) {
            /** @var QrcodeServices $qrcode */
            $qrcode = app()->make(QrcodeServices::class);
            $resForever = $qrcode->qrCodeForever($uid, 'spread_routine');
            if ($resForever) {
                $resCode = MiniProgram::appCodeUnlimit($resForever->id, '', 280);
                $res = ['res' => $resCode, 'id' => $resForever->id];
            } else {
                $res = false;
            }
            if (!$res) throw new ValidateException('二维码生成失败');
            $uploadType = (int)sys_config('upload_type', 1);
            $upload = UploadService::init($uploadType);
            if ($upload->to('routine/spread/code')->validate()->setAuthThumb(false)->stream((string)$res['res'], $name) === false) {
                return $upload->getError();
            }
            $imageInfo = $upload->getUploadInfo();
            $imageInfo['image_type'] = $uploadType;
            $systemAttachmentModel->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
            $qrcode->update($res['id'], ['status' => 1, 'time' => time(), 'qrcode_url' => $imageInfo['dir']]);
            $urlCode = $imageInfo['dir'];
        } else $urlCode = $imageInfo['att_dir'];
        return ['code_src' => $urlCode];
    }

    /**
     * 查看H5推广二维码
     * @param string $uid
     * @return mixed|string
     */
    public function lookH5Code(int $uid)
    {
        $userInfo = app()->make(UserServices::class)->getUserInfo($uid);
        if (!$userInfo) {
            throw new AdminException('数据不存在');
        }
        $name = $userInfo['uid'] . '_h5_' . $userInfo['is_promoter'] . '_user.jpg';
        /** @var SystemAttachmentServices $systemAttachmentModel */
        $systemAttachmentModel = app()->make(SystemAttachmentServices::class);
        $imageInfo = $systemAttachmentModel->getInfo(['name' => $name]);
        if (!$imageInfo) {
            $urlCode = QrcodeService::getWechatQrcodePath($uid . '_h5_' . $userInfo['is_promoter'] . '_user.jpg', '?spread=' . $uid);
        } else $urlCode = $imageInfo['att_dir'];
        return ['code_src' => $urlCode];
    }

    /**
     * 清除推广关系
     * @param int $uid
     * @return mixed
     */
    public function delSpread(int $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userServices->userExist($uid)) {
            throw new AdminException('数据不存在');
        }
        if ($userServices->update($uid, ['spread_uid' => 0]) !== false)
            return true;
        else
            throw new AdminException('解除失败');
    }

    /**
     * 取消推广资格
     * @param int $uid
     * @return mixed
     */
    public function delSystemSpread(int $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userServices->userExist($uid)) {
            throw new AdminException('数据不存在');
        }
        if ($userServices->update($uid, ['spread_uid' => 0, 'spread_time' => 0]) !== false)
            return true;
        else
            throw new AdminException('取消失败');
    }

    /**
     * @param $page
     * @param $limit
     * @param $where
     */
    public function startRemoveSpread($page, $limit, $where)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $list = $userServices->getList($where, 'uid,spread_uid,spread_time', $page, $limit);
        foreach ($list as $userInfo) {
            $userServices->update($userInfo['uid'], ['spread_uid' => 0, 'spread_time' => 0], 'uid');
        }
        return true;
    }

    /**
     * 取消绑定上级
     * @return bool
     */
    public function removeSpread()
    {
        //商城分销功能是否开启 0关闭1开启
        if (!sys_config('brokerage_func_status')) return true;

        //绑定类型
        $store_brokergae_binding_status = sys_config('store_brokerage_binding_status', 1);
        if ($store_brokergae_binding_status == 1 || $store_brokergae_binding_status == 3) {
            return true;
        } else {
            //分销绑定类型为时间段且没过期
            $store_brokerage_binding_time = (int)sys_config('store_brokerage_binding_time', 30) * 24 * 3600;
            $spread_time = bcsub((string)time(), (string)$store_brokerage_binding_time, 0);
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $where = ['not_spread_uid' => 0, 'status' => 1, 'time' => [0, $spread_time], 'time_key' => 'spread_time'];
            $count = $userServices->count($where);
            $pages = ceil($count / 100);
            for ($i = 1; $i <= $pages; $i++) {
                AutoAgentJob::dispatch([$i, 100, $where]);
            }
        }
        return true;
    }

    /**
     * 配置绑定类型切换重置绑定时间
     * @return bool
     */
    public function resetSpreadTime()
    {
        //商城分销功能是否开启 0关闭1开启
        if (!sys_config('brokerage_func_status')) return true;
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userServices->update(['not_spread_uid' => 0, 'status' => 1], ['spread_time' => time()]);
        return true;
    }
}
