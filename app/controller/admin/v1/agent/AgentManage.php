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
namespace app\controller\admin\v1\agent;


use app\controller\admin\AuthController;
use app\jobs\user\UserFriendsJob;
use app\jobs\user\UserSpreadJob;
use app\services\agent\AgentLevelServices;
use app\services\agent\AgentManageServices;
use app\services\other\AgreementServices;
use app\services\user\UserServices;
use think\facade\App;


/**
 * 分销商管理控制器
 * Class AgentManage
 * @package app\controller\admin\v1\agent
 */
class AgentManage extends AuthController
{
    /**
     * AgentManage constructor.
     * @param App $app
     * @param AgentManageServices $services
     */
    public function __construct(App $app, AgentManageServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 分销管理列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['nickname', ''],
            ['data', ''],
        ]);
        return $this->success($this->services->agentSystemPage($where));
    }

    /**
     * 分销头部统计
     * @return mixed
     */
    public function get_badge()
    {
        $where = $this->request->getMore([
            ['data', ''],
            ['nickname', ''],
        ]);
        return $this->success(['res' => $this->services->getSpreadBadge($where)]);
    }

    /**
     * 推广人列表
     * @return mixed
     */
    public function get_stair_list()
    {
        $where = $this->request->getMore([
            ['uid', 0],
            ['data', ''],
            ['nickname', ''],
            ['type', '']
        ]);
        return $this->success($this->services->getStairList($where));
    }

    /**
     * 推广人列表头部统计
     * @return mixed
     */
    public function get_stair_badge()
    {
        $where = $this->request->getMore([
            ['uid', ''],
            ['data', ''],
            ['nickname', ''],
            ['type', ''],
        ]);
        return $this->success(['res' => $this->services->getSairBadge($where)]);
    }

    /**
     * 统计推广订单列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_stair_order_list()
    {
        $where = $this->request->getMore([
            ['uid', 0],
            ['data', ''],
            ['order_id', ''],
            ['type', ''],
            ['nickname', '']
        ]);
        return $this->success($this->services->getStairOrderList((int)$where['uid'], $where));
    }

    /**
     * 统计推广订单头部统计
     * @return mixed
     */
    public function get_stair_order_badge()
    {
        return $this->success();
    }

    /**
     * 查看公众号推广二维码
     * @param int $uid
     * @return json
     * */
    public function look_code($uid = '', $action = '')
    {
        if (!$uid || !$action) return $this->fail('缺少参数');
        try {
            if (method_exists($this, $action)) {
                $res = $this->$action($uid);
                if ($res)
                    return $this->success($res);
                else
                    return $this->fail(isset($res['msg']) ? $res['msg'] : '获取失败，请稍后再试！');
            } else
                return $this->fail('暂无此方法');
        } catch (\Exception $e) {
            return $this->fail('获取推广二维码失败，请检查您的微信配置', ['line' => $e->getLine(), 'messag' => $e->getMessage()]);
        }
    }

    /**
     * 获取公众号二维码
     * */
    public function wechant_code($uid)
    {
        $qr_code = $this->services->wechatCode((int)$uid);
        if (isset($qr_code['url']))
            return ['code_src' => $qr_code['url']];
        else
            return $this->fail('获取失败，请稍后再试！');
    }

    /**
     * 查看小程序推广二维码
     * @param string $uid
     */
    public function look_xcx_code($uid = '')
    {
        if (!strlen(trim($uid))) {
            return $this->fail('缺少参数');
        }
        return $this->success($this->services->lookXcxCode((int)$uid));
    }

    /**
     * 查看H5推广二维码
     * @param string $uid
     * @return mixed|string
     */
    public function look_h5_code($uid = '')
    {
        if (!strlen(trim($uid))) return $this->fail('缺少参数');
        return $this->success($this->services->lookH5Code((int)$uid));
    }

    /**
     * 解除单个用户的推广权限
     * @param int $uid
     * */
    public function delete_spread($uid)
    {
        if (!$uid) $this->fail('缺少参数');
        return $this->success($this->services->delSpread((int)$uid) ? '解除成功' : '解除失败');
    }

    /**
     * 修改上级推广人
     * @param UserServices $services
     * @return mixed
     */
    public function editSpread(UserServices $services)
    {
        [$uid, $spreadUid] = $this->request->postMore([
            [['uid', 'd'], 0],
            [['spread_uid', 'd'], 0],
        ], true);
        if (!$uid || !$spreadUid) {
            return $this->fail('缺少参数');
        }
        if ($uid == $spreadUid) {
            return $this->fail('上级推广人不能为自己');
        }
        $userInfo = $services->get($uid);
        if (!$userInfo) {
            return $this->fail('用户不存在');
        }
        if (!$services->count(['uid' => $spreadUid])) {
            return $this->fail('上级用户不存在');
        }
        if ($userInfo->spread_uid == $spreadUid) {
            return $this->fail('当前推广人已经是所选人');
        }
        $spreadInfo = $services->get($spreadUid);
        if ($spreadInfo->spread_uid == $uid) {
            return $this->fail('上级推广人不能为自己下级');
        }
        $userInfo->spread_uid = $spreadUid;
        $userInfo->spread_time = time();
        $userInfo->save();
        //记录推广绑定关系
        UserSpreadJob::dispatch([(int)$uid, (int)$spreadUid]);
        //记录好友关系
        UserFriendsJob::dispatch([(int)$uid, (int)$spreadUid]);
        return $this->success('修改成功');
    }

    /**
     * 取消推广员推广资格
     * @param $uid
     * @return mixed
     */
    public function delete_system_spread($uid)
    {
        if (!$uid) $this->fail('缺少参数');
        return $this->success($this->services->delSystemSpread((int)$uid) ? '取消成功' : '取消失败');
    }

    /**
     * 获取赠送分销等级表单
     * @param $uid
     * @return mixed
     */
    public function getLevelForm(AgentLevelServices $services, $uid)
    {
        if (!$uid) $this->fail('缺少参数');
        return $this->success($services->levelForm((int)$uid));
    }

    /**
     * 赠送分销等级
     * @param AgentLevelServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function giveAgentLevel(AgentLevelServices $services)
    {
        [$uid, $id] = $this->request->postMore([
            [['uid', 'd'], 0],
            [['id', 'd'], 0],
        ], true);
        if (!$uid || !$id) {
            return $this->fail('缺少参数');
        }
        return $this->success($services->givelevel((int)$uid, (int)$id) ? '赠送成功' : '赠送失败');
    }

    /**
     * 保存分销说明
     * @param $id
     * @param AgreementServices $agreementServices
     * @return mixed
     */
    public function setAgentAgreement($id, AgreementServices $agreementServices)
    {
        $data = $this->request->postMore([
            ['type', 2],
            ['title', ""],
            ['content', ''],
            ['status', ''],
        ]);
		$data['title'] = $data['title'] ?: '分销说明';
        return $this->success($agreementServices->saveAgreement($data, $id));
    }

    /**
     * 获取分销说明
     * @param AgreementServices $agreementServices
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgentAgreement(AgreementServices $agreementServices)
    {
        $list = $agreementServices->getAgreementBytype(2);
        if($list && isset($list['title']))  $list['title'] = $list['title'] ?: '分销说明';
        return $this->success($list);
    }
}
