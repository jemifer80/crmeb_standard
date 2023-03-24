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
declare (strict_types=1);

namespace app\services\activity\coupon;

use app\services\activity\promotions\StorePromotionsServices;
use app\services\BaseServices;
use app\services\user\UserServices;
use app\dao\activity\coupon\StoreCouponUserDao;
use app\services\product\category\StoreCategoryServices;
use crmeb\utils\Arr;

/**
 * Class StoreCouponUserServices
 * @package app\services\activity\coupon
 * @mixin StoreCouponUserDao
 */
class StoreCouponUserServices extends BaseServices
{

    /**
     * StoreCouponUserServices constructor.
     * @param StoreCouponUserDao $dao
     */
    public function __construct(StoreCouponUserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public function issueLog(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, 'uid,add_time', ['userInfo'], $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function systemPage(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', ['issue'], $page, $limit);
        $count = 0;
        if ($list) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userAll = $userServices->getColumn([['uid', 'IN', array_column($list, 'uid')]], 'uid,nickname', 'uid');
            foreach ($list as &$item) {
                $item['nickname'] = $userAll[$item['uid']]['nickname'] ?? '';
            }
            $count = $this->dao->count($where);
        }
        return compact('list', 'count');
    }

    /**
     * 获取用户优惠券
     * @param int $id
     * @param int $status
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserCouponList(int $id, int $status = -1)
    {
        [$page, $limit] = $this->getPageValue();
        $where = ['uid' => $id];
        if ($status != -1) $where['status'] = $status;
        $list = $this->dao->getList($where, '*', ['issue'], $page, $limit);
        foreach ($list as &$item) {
            $item['_add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['_end_time'] = date('Y-m-d H:i:s', $item['end_time']);
            if (!$item['coupon_time']) {
                $item['coupon_time'] = ceil(($item['end_use_time'] - $item['start_use_time']) / '86400');
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 恢复优惠券
     * @param int $id
     * @return bool|mixed
     */
    public function recoverCoupon(int $id)
    {
        $status = $this->dao->value(['id' => $id], 'status');
        if ($status) return $this->dao->update($id, ['status' => 0, 'use_time' => 0]);
        else return true;
    }

    /**
     * 过期优惠卷失效
     */
    public function checkInvalidCoupon()
    {
        $this->dao->update([['end_time', '<', time()], ['status', '=', '0']], ['status' => 2]);
    }

    /**
     * 获取用户有效优惠劵数量
     * @param int $uid
     * @return int
     */
    public function getUserValidCouponCount(int $uid)
    {
        $this->checkInvalidCoupon();
        return $this->dao->getCount(['uid' => $uid, 'status' => 0]);
    }

    /**
     * 下单页面显示可用优惠券
     * @param $uid
     * @param $cartGroup
     * @param $price
     * @return array
     */
    public function getUseableCouponList(int $uid, array $cartGroup)
    {
        $userCoupons = $this->dao->getUserAllCoupon($uid);
        $result = [];
        if ($userCoupons) {
            $cartInfo = $cartGroup['valid'];
            $promotions = $cartGroup['promotions'] ?? [];
            $promotionsList = [];
            if($promotions){
                $promotionsList = array_combine(array_column($promotions, 'id'), $promotions);
            }
            $isOverlay = function($cart) use ($promotionsList) {
				$productInfo = $cart['productInfo'] ?? [];
				if (!$productInfo) {
					return false;
				}
                if (isset($cart['promotions_id']) && $cart['promotions_id']) {
                    foreach ($cart['promotions_id'] as $key => $promotions_id) {
                        $promotions = $promotionsList[$promotions_id] ?? [];
                        if ($promotions && $promotions['promotions_type'] != 4){
                            $overlay = is_string($promotions['overlay']) ? explode(',', $promotions['overlay']) : $promotions['overlay'];
                            if (!in_array(5, $overlay)) {
                                return false;
                            }
                        }
                    }
                }
                return true;
            };
            foreach ($userCoupons as $coupon) {
                $price = 0;
                $count = 0;
                switch ($coupon['applicable_type']) {
                    case 0:
                    case 3:
                        foreach ($cartInfo as $cart) {
                            if (!$isOverlay($cart))  continue;
                            $price = bcadd((string)$price, (string)bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                            $count++;
                        }
                        break;
                    case 1://品类券
                        /** @var StoreCategoryServices $storeCategoryServices */
                        $storeCategoryServices = app()->make(StoreCategoryServices::class);
                        $cateGorys = $storeCategoryServices->getAllById((int)$coupon['category_id']);
                        if ($cateGorys) {
                            $cateIds = array_column($cateGorys, 'id');
                            foreach ($cartInfo as $cart) {
                                if (!$isOverlay($cart))  continue;
                                if (isset($cart['productInfo']['cate_id']) && array_intersect(explode(',', $cart['productInfo']['cate_id']), $cateIds)) {
                                    $price = bcadd((string)$price, (string)bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                                    $count++;
                                }
                            }
                        }
                        break;
                    case 2:
                        foreach ($cartInfo as $cart) {
                            if (!$isOverlay($cart))  continue;
                            if (isset($cart['product_id']) && in_array($cart['product_id'], explode(',', $coupon['product_id']))) {
                                $price = bcadd((string)$price, (string)bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                                $count++;
                            }
                        }
                        break;
                }
                if ($count && $coupon['use_min_price'] <= $price) {
                    $coupon['start_time'] = $coupon['start_time'] ? date('Y/m/d', $coupon['start_time']) : date('Y/m/d', $coupon['add_time']);
                    $coupon['add_time'] = date('Y/m/d', $coupon['add_time']);
                    $coupon['end_time'] = date('Y/m/d', $coupon['end_time']);
                    $coupon['title'] = $coupon['coupon_title'];
                    $coupon['type'] = $coupon['applicable_type'];
                    $coupon['use_min_price'] = floatval($coupon['use_min_price']);
                    $coupon['coupon_price'] = floatval($coupon['coupon_price']);
                    $result[] = $coupon;
                }
            }
        }
        return $result;
    }

    /**
     * 用户领取优惠券
     * @param $uid
     * @param $issueCouponInfo
     * @param string $type
     * @return mixed
     */
    public function addUserCoupon($uid, $issueCouponInfo, $type = 'get')
    {
        $data = [];
        $data['cid'] = $issueCouponInfo['id'];
        $data['uid'] = $uid;
        $data['coupon_title'] = $issueCouponInfo['title'];
        $data['coupon_price'] = $issueCouponInfo['coupon_price'];
        $data['use_min_price'] = $issueCouponInfo['use_min_price'];
        $data['add_time'] = time();
        if ($issueCouponInfo['coupon_time']) {
            $data['start_time'] = $data['add_time'];
            $data['end_time'] = $data['add_time'] + $issueCouponInfo['coupon_time'] * 86400;
        } else {
            $data['start_time'] = $issueCouponInfo['start_use_time'];
            $data['end_time'] = $issueCouponInfo['end_use_time'];
        }
        $data['type'] = $type;
        return $this->dao->save($data);
    }

    /**会员领取优惠券
     * @param $uid
     * @param $issueCouponInfo
     * @param string $type
     * @return mixed
     */
    public function addMemberUserCoupon($uid, $issueCouponInfo, $type = 'get')
    {
        $data = [];
        $data['cid'] = $issueCouponInfo['id'];
        $data['uid'] = $uid;
        $data['coupon_title'] = $issueCouponInfo['title'];
        $data['coupon_price'] = $issueCouponInfo['coupon_price'];
        $data['use_min_price'] = $issueCouponInfo['use_min_price'];
        $data['add_time'] = time();
        $data['start_time'] = strtotime(date('Y-m-d 00:00:00', time()));
        $data['end_time'] = strtotime(date('Y-m-d 23:59:59', strtotime('+30 day')));
        $data['type'] = $type;
        return $this->dao->save($data);
    }

    /**
     * 获取用户已领取的优惠卷
     * @param int $uid
     * @param $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserCounpon(int $uid, $type)
    {
        $where = [];
        $where['uid'] = $uid;
        switch ($type) {
            case 0:
            case '':
                break;
            case 1:
                $where['status'] = 0;
                break;
            case 2:
                $where['status'] = 1;
                break;
            default:
                $where['status'] = 1;
                break;
        }
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getCouponListByOrder($where, 'status ASC,add_time DESC', $page, $limit);
        /** @var StoreCategoryServices $categoryServices */
        $categoryServices = app()->make(StoreCategoryServices::class);
        $category = $categoryServices->getColumn([], 'pid,cate_name', 'id');
        foreach ($list as &$item) {
            if ($item['category_id'] && isset($category[$item['category_id']])) {
                $item['category_type'] = $category[$item['category_id']]['pid'] == 0 ? 1 : 2;
                $item['category_name'] = $category[$item['category_id']]['cate_name'];
            } else {
                $item['category_type'] = '';
                $item['category_name'] = '';
            }
        }
        return $list ? $this->tidyCouponList($list) : [];
    }

    /**
     * 格式化优惠券
     * @param $couponList
     * @return mixed
     */
    public function tidyCouponList($couponList)
    {
        $time = time();
        foreach ($couponList as &$coupon) {
            if ($coupon['status'] == '已使用') {
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已使用';
                $coupon['pc_type'] = 0;
                $coupon['pc_msg'] = '已使用';
            } else if ($coupon['status'] == '已过期') {
                $coupon['is_fail'] = 1;
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
                $coupon['pc_type'] = 0;
                $coupon['pc_msg'] = '已过期';
            } else if ($coupon['end_time'] < $time) {
                $coupon['is_fail'] = 1;
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
                $coupon['pc_type'] = 0;
                $coupon['pc_msg'] = '已过期';
            } else if ($coupon['start_time'] > $time) {
                $coupon['_type'] = 0;
                $coupon['_msg'] = '未开始';
                $coupon['pc_type'] = 1;
                $coupon['pc_msg'] = '未开始';
            } else {
                if ($coupon['start_time'] + 3600 * 24 > $time) {
                    $coupon['_type'] = 2;
                    $coupon['_msg'] = '立即使用';
                    $coupon['pc_type'] = 1;
                    $coupon['pc_msg'] = '可使用';
                } else {
                    $coupon['_type'] = 1;
                    $coupon['_msg'] = '立即使用';
					$coupon['pc_type'] = 1;
                    $coupon['pc_msg'] = '可使用';
                }
            }
            $coupon['add_time'] = $coupon['_add_time'] = $coupon['start_time'] ? date('Y/m/d', $coupon['start_time']) : date('Y/m/d', $coupon['add_time']);
            $coupon['end_time'] = $coupon['_end_time'] = date('Y/m/d', $coupon['end_time']);
            $coupon['use_min_price'] = floatval($coupon['use_min_price']);
            $coupon['coupon_price'] = floatval($coupon['coupon_price']);
        }
        return $couponList;
    }

    /**
     * 获取会员优惠券列表
     * @param $uid
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMemberCoupon($uid)
    {
        if (!$uid) return [];
        /** @var StoreCouponIssueServices $couponIssueService */
        $couponIssueService = app()->make(StoreCouponIssueServices::class);
        //$couponWhere['status'] = 1;
        $couponWhere['receive_type'] = 4;
        //$couponWhere['is_del'] = 0;
        $couponInfo = $couponIssueService->getMemberCouponIssueList($couponWhere);
        $couponList = [];
        if ($couponInfo) {
            $couponIds = array_column($couponInfo, 'id');
            $couponType = array_column($couponInfo, 'type', 'id');
            $couponList = $this->dao->getCouponListByOrder(['uid' => $uid, 'coupon_ids' => $couponIds], 'add_time desc');
            if ($couponList) {
                foreach ($couponList as $k => $v) {
                    $couponList[$k]['type_name'] = $couponIssueService->_couponType[$couponType[$v['cid']]];
                }
            }
        }
        return $couponList ? $this->tidyCouponList($couponList) : [];
    }

    /**
     * 根据月分组看会员发放优惠券情况
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function memberCouponUserGroupBymonth(array $where)
    {
        return $this->dao->memberCouponUserGroupBymonth($where);
    }

    /**根据时间查询用户优惠券
     * @param array $where
     * @return array|bool|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserCounponByMonth(array $where)
    {
        if (!$where) return false;
        return $this->dao->getUserCounponByMonth($where);
    }

	/**
 	* 使用优惠券验证
	* @param int $couponId
	* @param int $uid
	* @param array $cartInfo
	* @param array $promotions
	* @return bool
	*/
	public function useCoupon(int $couponId, int $uid, array $cartInfo, array $promotions = [])
	{
		if (!$couponId || !$uid || !$cartInfo) {
			return true;
		}
		/** @var StorePromotionsServices $promotionsServices */
		$promotionsServices = app()->make(StorePromotionsServices::class);
		try {
			[$couponInfo, $couponPrice] = $promotionsServices->useCoupon($couponId, $uid, $cartInfo, $promotions);
		} catch (\Throwable $e) {
			$couponInfo = [];
			$couponPrice = 0;
		}
		if ($couponInfo) {
			$this->dao->useCoupon($couponId);
		}
		return true;
	}
}
