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

namespace app\controller\kefu;


use app\Request;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\group\UserGroupServices;
use crmeb\services\CacheService;
use crmeb\services\UploadService;
use think\facade\App;
use app\services\kefu\UserServices;
use app\services\user\label\UserLabelCateServices;
use app\services\user\label\UserLabelRelationServices;
use app\services\message\service\StoreServiceRecordServices;
use think\facade\Config;


/**
 * Class User
 * @package app\kefuapi\controller
 */
class User extends AuthController
{
    /**
     * User constructor.
     * @param App $app
     * @param StoreServiceRecordServices $services
     */
    public function __construct(App $app, StoreServiceRecordServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取当前客服和用户的聊天记录
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function recordList(string $nickname = '', $is_tourist = 0)
    {
        return $this->success($this->services->getServiceList($this->kefuInfo['uid'], $nickname, (int)$is_tourist));
    }

    /**
     * 获取用户信息
     * @param UserServices $services
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userInfo(UserServices $services, $uid)
    {
        return $this->success($services->getUserInfo((int)$uid));
    }

    /**
     * 标签分类
     * @param UserLabelCateServices $services
     * @return mixed
     */
    public function getUserLabel(UserLabelCateServices $services, $uid)
    {
        return $this->success($services->getUserLabel((int)$uid));
    }

    /**
     * 获取用户分组
     * @param UserGroupServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserGroup(UserGroupServices $services)
    {
        return $this->success($services->getGroupList());
    }

    /**
     * 设置分组
     * @param UserGroupServices $services
     * @param UserServices $userServices
     * @param $uid
     * @param $id
     * @return mixed
     */
    public function setUserGroup(UserGroupServices $services, UserServices $userServices, $uid, $id)
    {
        if (!$services->count(['id' => $id])) {
            return $this->fail('添加的会员标签不存在');
        }
        if (!($userInfo = $userServices->get($uid))) {
            return $this->fail('用户不存在');
        }
        if ($userInfo->group_id == $id) {
            return $this->fail('已拥有此分组');
        }
        $userInfo->group_id = $id;
        if ($userInfo->save()) {
            return $this->success('设置成功');
        } else {
            return $this->fail('设置失败');
        }
    }

    /**
     * 设置用户标签
     * @param UserLabelRelationServices $services
     * @param $uid
     * @return mixed
     */
    public function setUserLabel(UserLabelRelationServices $services, $uid)
    {
        [$labels, $unLabelIds] = $this->request->postMore([
            ['label_ids', []],
            ['un_label_ids', []]
        ], true);
        if (!count($labels) && !count($unLabelIds)) {
            return $this->fail('请选择标签');
        }
        if ($services->setUserLable($uid, $labels) && $services->unUserLabel($uid, $unLabelIds)) {
            return $this->success('设置成功');
        } else {
            return $this->fail('设置失败');
        }
    }

    /**
     * 退出登陆
     * @return mixed
     */
    public function logout()
    {
        $key = trim(ltrim($this->request->header(Config::get('cookie.token_name')), 'Bearer'));
        CacheService::redisHandler()->delete(md5($key));
        return $this->success();
    }

    /**
     * 图片上传
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function upload(Request $request, SystemAttachmentServices $services)
    {
        $data = $request->postMore([
            ['filename', 'file'],
        ]);
        if (!$data['filename']) return $this->fail('参数有误');
        if (CacheService::has('start_uploads_' . $request->kefuId()) && CacheService::get('start_uploads_' . $request->kefuId()) >= 100) return $this->fail('非法操作');
        $upload = UploadService::init();
        $info = $upload->to('store/comment')->validate()->move($data['filename']);
        if ($info === false) {
            return $this->fail($upload->getError());
        }
        $res = $upload->getUploadInfo();
        $services->attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 1, (int)sys_config('upload_type', 1), $res['time'], 2);
        if (CacheService::has('start_uploads_' . $request->kefuId()))
            $start_uploads = (int)CacheService::get('start_uploads_' . $request->kefuId());
        else
            $start_uploads = 0;
        $start_uploads++;
        CacheService::set('start_uploads_' . $request->kefuId(), $start_uploads, 86400);
        $res['dir'] = path_to_url($res['dir']);
        if (strpos($res['dir'], 'http') === false) $res['dir'] = $request->domain() . $res['dir'];
        return $this->success('图片上传成功!', ['name' => $res['name'], 'url' => $res['dir']]);
    }

}
