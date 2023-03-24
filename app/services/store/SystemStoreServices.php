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
namespace app\services\store;

use app\dao\store\SystemStoreDao;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\system\SystemRoleServices;
use crmeb\exceptions\AdminException;
use crmeb\services\erp\Erp;
use crmeb\services\FormBuilder;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Log;
use think\Model;

/**
 * 门店
 * Class SystemStoreServices
 * @package app\services\system\store
 * @mixin SystemStoreDao
 */
class SystemStoreServices extends BaseServices
{
    /**
     * 创建form表单
     * @var Form
     */
    protected $builder;

    /**
     * 构造方法
     * SystemStoreServices constructor.
     * @param SystemStoreDao $dao
     * @param FormBuilder $builder
     */
    public function __construct(SystemStoreDao $dao, FormBuilder $builder)
    {
        $this->dao = $dao;
        $this->builder = $builder;
    }

    /**
     * 获取单个门店信息
     * @param int $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreInfo(int $id = 0)
    {
        if ($id) {
            $storeInfo = $this->dao->getOne(['id' => $id, 'is_del' => 0]);
        } else {
            $storeList = $this->dao->getStoreList(['is_del' => 0], ['*'], 0, 1);
            $storeInfo = $storeList[0] ?? [];
        }
        if (!$storeInfo) {
            return [];
        }
        $storeInfo['day_time'] = $storeInfo['day_time'] ? explode('-', $storeInfo['day_time']) : [];
        return is_object($storeInfo) ? $storeInfo->toArray() : $storeInfo;
    }

    /**
     * 附近门店
     * @param array $where
     * @param string $latitude
     * @param string $longitude
     * @param string $ip
     * @param int $limit
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNearbyStore(array $where, string $latitude, string $longitude, string $ip = '', int $limit = 0)
    {
        $where = array_merge($where, ['type' => 0]);
        if ($limit) {
            $page = 1;
        } else {
            [$page, $limit] = $this->getPageValue();
        }
        //默认附近门店
        $store_type = $where['store_type'] ?? 1;
        $uid = $where['uid'] ?? 0;
        unset($where['store_type'], $where['uid']);
        if ($store_type != 1) {//常用门店
            if ($uid) {
                /** @var StoreUserServices $storeUserServices */
                $storeUserServices = app()->make(StoreUserServices::class);
                $ids = $storeUserServices->getColumn(['uid' => $uid], 'store_id');
                if (!$ids) {
                    return [];
                }
                $where['ids'] = $ids;
            } else {//没登录，无常用门店
                return [];
            }
        }
        $storeList = [];
        if (isset($where['id']) && $where['id']) {
            $storeList = $this->dao->getStoreList($where, ['*'], $page, $limit, $latitude, $longitude, $latitude && $longitude ? 1 : 0);
        } elseif ($latitude && $longitude) {
            $storeList = $this->dao->getStoreList($where, ['*'], $page, $limit, $latitude, $longitude, 1);
        } elseif ($ip) {
            $addressArr = $this->addressHandle($this->convertIp($ip));
            $city = $addressArr['city'] ?? '';
            if ($city) {
                $storeList = $this->dao->getStoreByAddressInfo($city, $where, '*', $page, $limit);
            }
            $province = $addressArr['province'] ?? '';
            if (!$storeList && $province) {
                $storeList = $this->dao->getStoreByAddressInfo($province, $where, '*', $page, $limit);
            }
        }
        //上面条件都没获取到门店
        if (!$storeList) {
            $storeList = $this->dao->getStoreList($where, ['*'], $page, $limit);
        }
        if ($storeList) {
            foreach ($storeList as &$item) {
                $item['range'] = 0;
                if (isset($item['distance'])) {
                    $item['range'] = bcdiv($item['distance'], '1000', 1);
                } else {
                    $item['range'] = 0;
                }
                if (isset($item['is_show']) && $item['is_show'] == 1) {
                    $item['status_name'] = '营业中';
                } else {
                    $item['status_name'] = '已停业';
                }
            }
        }
        return $limit == 1 ? ($storeList[0] ?? []) : $storeList;
    }

    /**
     * 获取提货点列表
     * @param array $where
     * @param array $field
     * @param string $latitude
     * @param string $longitude
     * @param int $product_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreList(array $where, array $field = ['*'], string $latitude = '', string $longitude = '', int $product_id = 0)
    {
        [$page, $limit] = $this->getPageValue();
        $order = 0;
        if (isset($where['order_id']) && $where['order_id']) {
            /** @var StoreOrderServices $storeOrderServices */
            $storeOrderServices = app()->make(StoreOrderServices::class);
            $user_location = $storeOrderServices->value(['id' => $where['order_id']], 'user_location');
            [$longitude, $latitude] = explode(' ', $user_location);
        }
        if ($longitude && $latitude) {
            $order = 1;
        }
        $oid = (int)($where['order_id'] ?? 0);
        unset($where['order_id']);
        $storeList = $this->dao->getStoreList($where, $field, $page, $limit, $latitude, $longitude, $order);
        $list = [];
        foreach ($storeList as &$item) {
            if (isset($item['distance'])) {
                $item['range'] = bcdiv($item['distance'], '1000', 1);
            } else {
                $item['range'] = 0;
            }
            if ($item['is_show'] == 1) {
                $item['status_name'] = '营业中';
            } else {
                $item['status_name'] = '已停业';
            }
            $list[] = $item;
        }
        $count = $this->dao->count($where);

        return compact('list', 'count');
    }

    /**
     * 获取提货点头部统计信息
     * @return mixed
     */
    public function getStoreData()
    {
        $data['show'] = [
            'name' => '显示中的提货点',
            'num' => $this->dao->count(['type' => 0]),
        ];
        $data['hide'] = [
            'name' => '隐藏中的提货点',
            'num' => $this->dao->count(['type' => 1]),
        ];
        $data['recycle'] = [
            'name' => '回收站的提货点',
            'num' => $this->dao->count(['type' => 2])
        ];
        return $data;
    }

    /**
     * 门店重置账号密码表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function storeAdminAccountForm(int $id)
    {
        $storeInfo = $this->getStoreInfo($id);
        /** @var SystemStoreStaffServices $staffServices */
        $staffServices = app()->make(SystemStoreStaffServices::class);
        $staffInfo = $staffServices->getOne(['store_id' => $storeInfo['id'], 'level' => 0, 'is_admin' => 1, 'is_manager' => 1, 'is_del' => 0]);
        $field[] = $this->builder->hidden('staff_id', $staffInfo['id'] ?? 0);
        $field[] = $this->builder->input('account', '登录账号', $staffInfo['account'] ?? '')->col(24)->required('请输入账号');
        $field[] = $this->builder->input('password', '登录密码')->type('password')->col(24)->required('请输入密码');
        $field[] = $this->builder->input('true_password', '确认密码')->type('password')->col(24)->required('请再次确认密码');
        return create_form('门店重置账号密码', $field, $this->url('/store/store/reset_admin/' . $id));
    }

    /**
     * 获取erp门店列表
     * @return array|mixed
     * @throws \Exception
     */
    public function erpShopList()
    {
        [$page, $limit] = $this->getPageValue();
        if (!sys_config('erp_open')) {
            return [];
        }
        try {
            /** @var Erp $erpService */
            $erpService = app()->make(Erp::class);
            $res = Cache::tag('erp_shop')->remember('list_' . $page . '_' . $limit, function () use ($page, $limit, $erpService) {
                return $erpService->serviceDriver('Comment')->getShopList($page, $limit);
            }, 60);
        } catch (\Throwable $e) {
            Log::error([
                'message' => '读取ERP门店信息失败',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        return $res['data']['datas'] ?? [];
    }

    /**
 	* 保存或修改门店
	* @param int $id
	* @param array $data
	* @param array $staff_data
	* @return mixed
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	*/
    public function saveStore(int $id, array $data, array $staff_data = [])
    {
		$id = $this->transaction(function () use ($id, $data, $staff_data) {
			$storeInfo = $this->getStoreInfo();
            if ($storeInfo) {
				$id = $storeInfo['id'];
                if ($this->dao->update($id, $data)) {
                    return $id;
                } else {
                    throw new AdminException('修改失败或者您没有修改什么！');
                }
            } else {
                $data['add_time'] = time();
                if ($res = $this->dao->save($data)) {
                    return (int)$res->id;
                } else {
                    throw new AdminException('保存失败！');
                }
            }
        });

        if ($data['is_show'] && ($data['is_del'] ?? 1)) {
            //写入缓存
            $data['id'] = $id;
            $this->dao->cacheUpdate($data);
        }
        return $id;
    }

    /**
     * 获取门店缓存
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/21
     */
    public function getStoreDisposeCache(int $id, string $felid = '')
    {
        $storeInfo = $this->dao->cacheRemember($id, function () use ($id) {
            $storeInfo = $this->dao->get($id);
            return $storeInfo ? $storeInfo->toArray() : null;
        });

        if ($felid) {
            return $storeInfo[$felid] ?? null;
        }

        if ($storeInfo) {
            $storeInfo['latlng'] = $storeInfo['latitude'] . ',' . $storeInfo['longitude'];
            $storeInfo['dataVal'] = $storeInfo['valid_time'] ? explode(' - ', $storeInfo['valid_time']) : [];
            $storeInfo['timeVal'] = $storeInfo['day_time'] ? explode(' - ', $storeInfo['day_time']) : [];
            $storeInfo['address2'] = $storeInfo['address'] ? explode(',', $storeInfo['address']) : [];
            return $storeInfo;
        }
        return [];
    }

    /**
     * 后台获取提货点详情
     * @param int $id
     * @param string $felid
     * @return array|false|mixed|string|string[]|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreDispose(int $id, string $felid = '')
    {
        if ($felid) {
            return $this->dao->value(['id' => $id], $felid);
        } else {
            $storeInfo = $this->dao->get($id);
            if ($storeInfo) {
                $storeInfo['latlng'] = $storeInfo['latitude'] . ',' . $storeInfo['longitude'];
                $storeInfo['dataVal'] = $storeInfo['valid_time'] ? explode(' - ', $storeInfo['valid_time']) : [];
                $storeInfo['timeVal'] = $storeInfo['day_time'] ? explode(' - ', $storeInfo['day_time']) : [];
                $storeInfo['address2'] = $storeInfo['address'] ? explode(',', $storeInfo['address']) : [];
                return $storeInfo;
            }
            return false;
        }
    }

    /**
     * 获取门店不分页
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStore()
    {
        return $this->dao->getStore(['type' => 0]);
    }

    /**
     * 获得导出店员列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExportData(array $where)
    {
        return $this->dao->getStoreList($where, ['*']);
    }


}
