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

namespace app\services\system;


use app\Request;
use app\services\BaseServices;
use app\dao\system\SystemRoleDao;
use app\services\store\SystemStoreStaffServices;
use crmeb\exceptions\AuthException;
use crmeb\utils\ApiErrorCode;
use crmeb\services\CacheService;


/**
 * Class SystemRoleServices
 * @package app\services\system
 * @mixin SystemRoleDao
 */
class SystemRoleServices extends BaseServices
{

    /**
     * 当前管理员权限缓存前缀
     */
    const ADMIN_RULES_LEVEL = 'Admin_rules_level_';

    /**
     * SystemRoleServices constructor.
     * @param SystemRoleDao $dao
     */
    public function __construct(SystemRoleDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取权限
     * @return mixed
     */
    public function getRoleArray(array $where = [], string $field = '', string $key = '')
    {
        return $this->dao->getRoule($where, $field, $key);
    }

    /**
     * 获取表单所需的权限名称列表
     * @param int $level
     * @return array
     */
    public function getRoleFormSelect(int $level, int $type = 1, int $store_id = 0)
    {
        $list = $this->getRoleArray(['level' => $level, 'type' => $type, 'store_id' => $store_id, 'status' => 1]);
        $options = [];
        foreach ($list as $id => $roleName) {
            $options[] = ['label' => $roleName, 'value' => $id];
        }
        return $options;
    }

    /**
     * 身份管理列表
     * @param array $where
     * @return array
     */
    public function getRoleList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getRouleList($where, $page, $limit);
        $count = $this->dao->count($where);
        /** @var SystemMenusServices $service */
        $service = app()->make(SystemMenusServices::class);
        foreach ($list as &$item) {
            $item['rules'] = implode(',', array_merge($service->column(['id' => $item['rules']], 'menu_name', 'id')));
        }
        return compact('count', 'list');
    }

    /**
     * 后台验证权限
     * @param Request $request
     */
    public function verifiAuth(Request $request)
    {
        $rule = str_replace('adminapi/', '', trim(strtolower($request->rule()->getRule())));
        if (in_array($rule, ['setting/admin/logout', 'menuslist'])) {
            return true;
        }
		$auth = $this->getAllRoles(2);
        //验证访问接口是否存在
        if (!in_array($rule, array_map(function ($item) {
            return trim(strtolower(str_replace(' ', '', $item)));
        }, array_column($auth, 'api_url')))) {
            return true;
        }
		$auth = $this->getRolesByAuth($request->adminInfo()['roles'], 2);
		$method = trim(strtolower($request->method()));
        //验证访问接口是否有权限
        if (empty(array_filter($auth, function ($item) use ($rule, $method) {
            if (trim(strtolower($item['api_url'])) === $rule && $method === trim(strtolower($item['methods'])))
                return true;
        }))) {
            throw new AuthException(ApiErrorCode::ERR_AUTH);
        }
    }

	/**
     * 获取所有权限
     * @param int $auth_type
     * @param int $type
     * @param string $cachePrefix
     * @return array|bool|mixed|null
     */
    public function getAllRoles(int $auth_type = 1, int $type = 1, string $cachePrefix = self::ADMIN_RULES_LEVEL)
    {
        $cacheName = md5($cachePrefix . '_' . $auth_type . '_' . $type . '_ALl' );
        return CacheService::redisHandler('system_menus')->remember($cacheName, function () use ($auth_type, $type) {
            /** @var SystemMenusServices $menusService */
            $menusService = app()->make(SystemMenusServices::class);
            return $menusService->getColumn([['auth_type', '=', $auth_type], ['type', '=', $type]], 'api_url,methods');
        });
    }

    /**
     * 获取指定权限
     * @param array $rules
     * @param int $auth_type
     * @param int $type
     * @param string $cachePrefix
     * @return array|bool|mixed|null
     */
    public function getRolesByAuth(array $rules, int $auth_type = 1, int $type = 1, string $cachePrefix = self::ADMIN_RULES_LEVEL)
    {
        if (empty($rules)) return [];
        $cacheName = md5($cachePrefix . '_' . $auth_type . '_' . $type . '_' . implode('_', $rules));
        return CacheService::redisHandler('system_menus')->remember($cacheName, function () use ($rules, $auth_type, $type) {
            /** @var SystemMenusServices $menusService */
            $menusService = app()->make(SystemMenusServices::class);
            return $menusService->getColumn([['id', 'IN', $this->getRoleIds($rules)], ['auth_type', '=', $auth_type], ['type', '=', $type]], 'api_url,methods');
        });
    }

    /**
     * 获取权限id
     * @param array $rules
     * @return array
     */
    public function getRoleIds(array $rules)
    {
        $rules = $this->dao->getColumn([['id', 'IN', $rules], ['status', '=', '1']], 'rules', 'id');
        return array_unique(explode(',', implode(',', $rules)));
    }

    /**
     * 门店角色状态更改改变角色下店员、管理员状态
     * @param int $store_id
     * @param int $role_id
     * @param $status
     * @return mixed
     */
    public function setStaffStatus(int $store_id, int $role_id, $status)
    {
        /** @var SystemStoreStaffServices $storeStaffServices */
        $storeStaffServices = app()->make(SystemStoreStaffServices::class);
        if ($status) {
            return $storeStaffServices->update(['store_id' => $store_id, 'roles' => $role_id, 'is_del' => 0, 'status' => 0], ['status' => 1]);
        } else {
            return $storeStaffServices->update(['store_id' => $store_id, 'roles' => $role_id, 'status' => 1], ['status' => 0]);
        }
    }
}
