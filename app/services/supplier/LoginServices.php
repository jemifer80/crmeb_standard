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

namespace app\services\supplier;


use app\Request;
use app\services\BaseServices;
use app\dao\supplier\SystemSupplierDao;
use app\services\system\SystemMenusServices;
use app\services\system\SystemRoleServices;
use crmeb\exceptions\AdminException;
use app\dao\system\admin\SystemAdminDao;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;
use crmeb\traits\ServicesTrait;
use crmeb\utils\ApiErrorCode;
use crmeb\utils\JwtAuth;
use Firebase\JWT\ExpiredException;
use think\exception\ValidateException;
use think\facade\Cache;


/**
 *
 * Class LoginServices
 * @package app\services\supplier
 * @mixin SystemSupplierDao
 */
class LoginServices extends BaseServices
{

    use ServicesTrait;

    protected $adminDao;

    /**
     * 权限缓存前缀
     */
    const SUPPLIER_RULES_LEVEL = 'store_supplier_rules_level_';

    /**
     * LoginServices constructor.
     * @param SystemSupplierDao $dao
     */
    public function __construct(SystemSupplierDao $dao, SystemAdminDao $adminDao)
    {
        $this->dao = $dao;
        $this->adminDao = $adminDao;
    }

    /**
     * 获取登陆前的login等信息
     * @return array
     */
    public function getLoginInfo()
    {
        return [
            'slide' => sys_data('admin_login_slide') ?? [],
            'logo_square' => sys_config('site_logo_square'),//透明
            'logo_rectangle' => sys_config('site_logo'),//方形
            'login_logo' => sys_config('login_logo'),//登陆
            'site_name' => sys_config('site_name'),
            'site_url' => sys_config('site_url'),
            'upload_file_size_max' => config('upload.filesize'),//文件上传大小kb
        ];
    }

    /**
     * H5账号登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($account, $password, $type)
    {
        $supplierInfo = $this->adminDao->getOne(['admin_type' => 4, 'account' => $account, 'is_del' => 0], '*', ['supplier']);
		$key = 'supplier_login_captcha_' . $account;
        if (!$supplierInfo) {
			Cache::inc($key);
            throw new AdminException('账号不存在!');
        }
        if ($password) {//平台还可以登录
            if (!$supplierInfo->status || !$supplierInfo->is_show) {
				Cache::inc($key);
                throw new AdminException('您已被禁止登录!');
            }

            if (!password_verify($password, $supplierInfo->pwd)) {
				Cache::inc($key);
                throw new AdminException('账号或密码错误，请重新输入');
            }
        }

        $supplierInfo->last_time = time();
        $supplierInfo->last_ip = app('request')->ip();
        $supplierInfo->login_count++;
        $supplierInfo->save();

        $tokenInfo = $this->createToken($supplierInfo['id'], $type, $supplierInfo->pwd);
        /** @var SystemMenusServices $services */
        $services = app()->make(SystemMenusServices::class);
        [$menus, $uniqueAuth] = $services->getMenusList($supplierInfo->roles, 0, 4);

        return [
            'token' => $tokenInfo['token'],
            'expires_time' => $tokenInfo['params']['exp'],
            'menus' => $menus,
            'unique_auth' => $uniqueAuth,
            'user_info' => [
                'id' => $supplierInfo->getData('id'),
                'account' => $supplierInfo->getData('account'),
                'avatar' => $supplierInfo->getData('head_pic'),
            ],
            'logo' => sys_config('site_logo'),
            'logo_square' => sys_config('site_logo_square'),
            'version' => get_crmeb_version(),
            'newOrderAudioLink' => get_file_link(sys_config('new_order_audio_link', ''))
        ];
    }

    /**
     * 重置密码
     * @param $account
     * @param $password
     */
    public function reset($account, $password)
    {
        $user = $this->dao->getOne(['account|phone' => $account]);
        if (!$user) {
            throw new ValidateException('用户不存在');
        }
        if (!$this->dao->update($user['id'], ['pwd' => md5((string)$password)])) {
            throw new ValidateException('修改密码失败');
        }
        return true;
    }


    /**
     * 获取Admin授权信息
     * @param string $token
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function parseToken(string $token): array
    {
        /** @var CacheService $cacheService */
        $cacheService = app()->make(CacheService::class);

        if (!$token || $token === 'undefined') {
            throw new AuthException(ApiErrorCode::ERR_LOGIN);
        }
        //检测token是否过期
        $md5Token = md5($token);
        if (!$cacheService->hasToken($md5Token) || !($cacheToken = $cacheService->getTokenBucket($md5Token))) {
            throw new AuthException(ApiErrorCode::ERR_LOGIN);
        }
        //是否超出有效次数
        if (isset($cacheToken['invalidNum']) && $cacheToken['invalidNum'] >= 3) {
            if (!request()->isCli()) {
                $cacheService->clearToken($md5Token);
            }
            throw new AuthException(ApiErrorCode::ERR_LOGIN_INVALID);
        }

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        //设置解析token
        [$id, $type, $auth] = $jwtAuth->parseToken($token);
        //验证token
        try {
            $jwtAuth->verifyToken();
            $cacheService->setTokenBucket($md5Token, $cacheToken, $cacheToken['exp']);
        } catch (ExpiredException $e) {
            $cacheToken['invalidNum'] = isset($cacheToken['invalidNum']) ? $cacheToken['invalidNum']++ : 1;
            $cacheService->setTokenBucket($md5Token, $cacheToken, $cacheToken['exp']);
        } catch (\Throwable $e) {
            if (!request()->isCli()) {
                $cacheService->clearToken($md5Token);
            }
            throw new AuthException(ApiErrorCode::ERR_LOGIN_INVALID);
        }
        //获取管理员信息
		$adminInfo = $this->adminDao->getOne(['id' => $id, 'is_del' => 0, 'status' => 1]);
		if(!$adminInfo){
			throw new AuthException(ApiErrorCode::ERR_ADMINID_VOID);
		}
		if ($auth !== md5($adminInfo->pwd)) {
			throw new AuthException(ApiErrorCode::ERR_LOGIN_INVALID);
		}
		$supplierInfo = $this->dao->getOne(['id' =>(int)$adminInfo->relation_id, 'is_del' => 0], '*', ['admin']);
		if (!$supplierInfo || !$supplierInfo->account || $supplierInfo->admin_is_del) {
			if (!request()->isCli()) {
				$cacheService->clearToken($md5Token);
			}
			throw new AuthException(ApiErrorCode::ERR_LOGIN_STATUS);
		}

        $supplierInfo->type = $type;
        return $supplierInfo->hidden(['pwd', 'is_del', 'status'])->toArray();
    }

    /**
     * 后台验证权限
     * @param Request $request
     */
    public function verifiAuth(Request $request)
    {
        // TODO: 供应商不做验证
        return true;
        $rule = str_replace('supplierapi/', '', trim(strtolower($request->rule()->getRule())));
        if (in_array($rule, ['supplier/logout', 'menuslist'])) {
            return true;
        }
        /** @var SystemRoleServices $roleServices */
        $roleServices = app()->make(SystemRoleServices::class);
        $auth = $roleServices->getAllRoles(2, 4, self::SUPPLIER_RULES_LEVEL);
        //验证访问接口是否存在
        if ($auth && !in_array($rule, array_map(function ($item) {
                return trim(strtolower(str_replace(' ', '', $item)));
            }, array_column($auth, 'api_url')))) {
            return true;
        }
        $auth = $roleServices->getRolesByAuth($request->supplierInfo()['roles'], 2, 4, self::SUPPLIER_RULES_LEVEL);
        $method = trim(strtolower($request->method()));
        //验证访问接口是否有权限
        if (empty(array_filter($auth, function ($item) use ($rule, $method) {
            if (trim(strtolower($item['api_url'])) === $rule && $method === trim(strtolower($item['methods'])))
                return true;
        }))) {
            throw new AuthException(ApiErrorCode::ERR_AUTH);
        }
    }


}
