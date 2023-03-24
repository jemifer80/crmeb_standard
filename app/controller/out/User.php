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
namespace app\controller\out;

use app\Request;
use app\services\user\UserServices;
use app\services\user\level\UserLevelServices;

/**
 * 用户类
 * Class StoreProductController
 * @package app\api\controller\store
 */
class User
{
    /**
     * 用户services
     * @var UserServices
     */
    protected $services;

    public function __construct(UserServices $services)
    {
        $this->services = $services;
    }

    /**
     * 用户等级列表
     * @param Request $request
     * @return mixed
     */
    public function levelList(Request $request)
    {
        $where = $request->getMore([
            ['page', 0],
            ['limit', 10],
            ['title', ''],
            ['is_show', ''],
        ]);
        /** @var UserLevelServices $levelServices */
        $levelServices = app()->make(UserLevelServices::class);
        return app('json')->success($levelServices->getSytemList($where));
    }

    /**
     * 用户列表
     * @return mixed
     */
    public function userList()
    {
        return app('json')->success($this->services->index([]));
    }

    /**
     * 添加用户
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userSave(Request $request)
    {
        $data = $request->postMore([
            ['is_promoter', 0],
            ['real_name', ''],
            ['card_id', ''],
            ['birthday', ''],
            ['mark', ''],
            ['status', 0],
            ['level', 0],
            ['phone', 0],
            ['addres', ''],
            ['label_id', []],
            ['group_id', 0],
            ['pwd', ''],
            ['true_pwd', ''],
            ['spread_open', 1],
            ['sex', 0],
            ['provincials', ''],
            ['province', 0],
            ['city', 0],
            ['area', 0],
            ['street', 0],
            ['extend_info', []],
            ['adminId', 0],
        ]);
        if ($data['phone']) {
            if (!check_phone($data['phone'])) {
                return app('json')->fail('手机号码格式不正确');
            }
            if ($this->services->count(['phone' => $data['phone']])) {
                return app('json')->fail('手机号已经存在不能添加相同的手机号用户');
            }
            $data['nickname'] = substr_replace($data['phone'], '****', 3, 4);
        }
        if ($data['card_id']) {
			try {
				 if (!check_card($data['card_id'])) return $this->fail('请输入正确的身份证');
			} catch (\Throwable $e) {
//				 return $this->fail('请输入正确的身份证');
			}
        }
        if ($data['birthday']) {
            if (strtotime($data['birthday']) > time()) return app('json')->fail('生日请选择今天之前日期');
        }
        if ($data['pwd']) {
            if (strlen($data['pwd']) < 6) {
                return app('json')->fail('密码长度最小6位');
            }
            if ($data['pwd'] == '123456') {
                return app('json')->fail('您设置的密码太过简单');
            }
            $data['pwd'] = md5($data['pwd']);
        } else {
            unset($data['pwd']);
        }
        unset($data['true_pwd']);
        $data['avatar'] = sys_config('h5_avatar');
        $data['user_type'] = 'h5';
        $labels = $data['label_id'];
        unset($data['label_id']);
        foreach ($labels as $k => $v) {
            if (!$v) {
                unset($labels[$k]);
            }
        }
        $data['birthday'] = empty($data['birthday']) ? 0 : strtotime($data['birthday']);
        $data['add_time'] = time();
        $data['extend_info'] = $this->services->handelExtendInfo($data['extend_info']);
        $this->services->transaction(function () use ($data, $labels) {
            $res = true;
            $userInfo = $this->services->save($data);
            if ($labels) {
                $res = $this->services->saveSetLabel([$userInfo->uid], $labels);
            }
            if ($data['level']) {
                $res = $this->services->saveGiveLevel((int)$userInfo->uid, (int)$data['level']);
            }
            if (!$res) {
                return app('json')->fail('保存添加用户失败');
            }
            event('user.register', [$this->services->get((int)$userInfo->uid), true, 0]);
        });
        event('user.create', $data);
        return app('json')->success('添加成功');
    }

    /**
     * 更新用户信息
     * @param Request $request
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userUpdate(Request $request, $uid)
    {
        $data = $request->postMore([
            ['money_status', 0],
            ['is_promoter', 0],
            ['real_name', ''],
            ['card_id', ''],
            ['birthday', ''],
            ['mark', ''],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
            ['status', 0],
            ['level', 0],
            ['phone', 0],
            ['addres', ''],
            ['label_id', []],
            ['group_id', 0],
            ['pwd', ''],
            ['true_pwd'],
            ['spread_open', 1],
            ['sex', 0],
            ['provincials', ''],
            ['province', 0],
            ['city', 0],
            ['area', 0],
            ['street', 0],
            ['spread_uid', -1],
            ['extend_info', []],
            ['adminId', 0],
        ]);
        if ($data['phone']) {
            if (!check_phone($data['phone'])) return app('json')->fail('手机号码格式不正确');
        }
        if ($data['card_id']) {
			try {
				if (!check_card($data['card_id'])) return app('json')->fail('请输入正确的身份证');
			} catch (\Throwable $e) {
//				return app('json')->fail('请输入正确的身份证');
			}
        }
        if ($data['birthday']) {
            if (strtotime($data['birthday']) > time()) return app('json')->fail('生日请选择今天之前日期');
        }
        if ($data['pwd']) {
            $data['pwd'] = md5($data['pwd']);
        } else {
            unset($data['pwd']);
        }
        $userInfo = $this->services->get($uid);
        if (!$userInfo) {
            return app('json')->fail('用户不存在');
        }
        if (!in_array($data['spread_uid'], [0, -1])) {
            $spreadUid = $data['spread_uid'];
            if ($uid == $spreadUid) {
                return app('json')->fail('上级推广人不能为自己');
            }
            if (!$this->services->count(['uid' => $spreadUid])) {
                return app('json')->fail('上级用户不存在');
            }
            $spreadInfo = $this->services->get($spreadUid);
            if ($spreadInfo->spread_uid == $uid) {
                return app('json')->fail('上级推广人不能为自己下级');
            }
        }
        unset($data['true_pwd']);
        if (!$uid) return app('json')->fail('数据不存在');
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        $data['extend_info'] = $this->services->handelExtendInfo($data['extend_info']);
        return app('json')->success($this->services->updateInfo((int)$uid, $data) ? '修改成功' : '修改失败');
    }

    /**
     * 修改用户余额和积分
     * @param Request $request
     * @param $uid
     * @return mixed
     */
    public function userGive(Request $request, $uid)
    {
        $data = $request->postMore([
            ['money_status', 0],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
        ]);
        if (!$uid) return app('json')->fail('数据不存在');
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        $data['is_other'] = true;
        return app('json')->success($this->services->updateInfo($uid, $data) ? '修改成功' : '修改失败');
    }
}
