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

namespace app\services\out;


use app\dao\out\OutAccountDao;
use crmeb\basic\BaseAuth;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\AuthException;
use crmeb\services\HttpService;
use crmeb\utils\ApiErrorCode;
use think\exception\ValidateException;

/**
 * 获取token
 * Class LoginServices
 * @package app\services\kefu
 * @mixin OutAccountDao
 */
class OutAccountServices extends BaseServices
{
    const FEPAORPL = 'OSeCVa';

    /**
     * LoginServices constructor.
     * @param OutAccountDao $dao
     */
    public function __construct(OutAccountDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 账号密码登录
     * @param string $appid
     * @param string $appsecret
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function authLogin(string $appid, string $appsecret = null)
    {
        $autInfo = $this->dao->get(['appid' => $appid, 'is_del' => 0]);
        if (!$autInfo) {
            throw new ValidateException('没有此用户');
        }
        if ($appsecret && !password_verify($appsecret, $autInfo->appsecret)) {
            throw new ValidateException('appid或appsecret错误');
        }
        if ($autInfo->status == 2) {
            throw new ValidateException('您已被禁止登录');
        }
        $token = $this->createToken($autInfo->id, 'out', $autInfo->appsecret);
        $data['last_time'] = time();
        $data['ip'] = request()->ip();
        $this->update($autInfo['id'], $data);
        return [
            'token' => $token['token'],
            'exp_time' => $token['params']['exp'],
//            'autInfo' => $autInfo->hidden(['appsecret', 'ip', 'is_del', 'add_time', 'status', 'last_time'])->toArray()
        ];
    }

    /**
     * 解析token
     * @param string $token
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function parseToken(string $token)
    {
        /** @var BaseAuth $services */
        $services = app()->make(BaseAuth::class);
        $adminInfo = $services->parseToken($token, function ($id) {
            return $this->dao->get($id);
        });
        if (isset($adminInfo->auth) && $adminInfo->auth !== md5($adminInfo->appsecret)) {
            throw new AuthException(ApiErrorCode::ERR_LOGIN_INVALID);
        }
        return $adminInfo->hidden(['appsecret', 'ip', 'status']);
    }

    /**
     * 获取一条
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne($where = [])
    {
        $info = $this->dao->getOne($where);
        return $info ? $info->toArray() : [];
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getList((array)$where, (int)$page, (int)$limit);
        $count = $this->dao->count($where);
        if ($list) {
            foreach ($list as &$item) {
                $item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '暂无';
                $item['last_time'] = $item['last_time'] ? date('Y-m-d H:i:s', $item['last_time']) : '暂无';
                $item['rules'] = is_null($item['rules']) ? [] : json_decode($item['rules'], true);
            }
        }
        return compact('count', 'list');
    }

    /**
     * 设置推送接口
     * @param $id
     * @param $data
     * @return mixed
     */
    public function outSetUpSave($id, $data)
    {
        return $this->dao->update($id, $data);
    }

    /**
     * 测试获取token接口
     * @param $data
     * @return int[]|mixed
     */
    public function textOutUrl($data)
    {
        if (!$data['push_account'] || !$data['push_password'] || !$data['push_token_url']) throw new AdminException(100100);
        $param = ['push_account' => $data['push_account'], 'push_password' => $data['push_password']];
        $res = HttpService::getRequest($data['push_token_url'], $param);
        $res = $res ? json_decode($res, true) : ['status' => 400];
        if ($res['status'] != 200) {
            throw new AdminException('测试失败');
        } else {
            return $res['data'];
        }
    }
}
