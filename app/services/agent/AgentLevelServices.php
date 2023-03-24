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

use app\dao\agent\AgentLevelDao;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserExtractServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use FormBuilder\Factory\Iview;
use think\exception\ValidateException;
use think\facade\Route as Url;


/**
 * 分销等级
 * Class AgentLevelServices
 * @package app\services\agent
 * @mixin AgentLevelDao
 */
class AgentLevelServices extends BaseServices
{
    /**
     * AgentLevelServices constructor.
     * @param AgentLevelDao $dao
     */
    public function __construct(AgentLevelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某一个等级信息
     * @param int $id
     * @param string $field
     * @param array $with
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLevelInfo(int $id, string $field = '*', array $with = [])
    {
        return $this->dao->getOne(['id' => $id, 'is_del' => 0], $field, $with);
    }

    /**
     * 获取等级列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLevelList(array $where)
    {
        $where['is_del'] = 0;
        [$page, $limit] = $this->getPageValue();
		$count = $this->dao->count($where);
		$list = [];
		if ($count) {
			$list = $this->dao->getList($where, '*', ['task' => function ($query) {
				$query->field('count(*) as sum');
			}], $page, $limit);
			$store_brokerage_ratio = sys_config('store_brokerage_ratio');
			$store_brokerage_two = sys_config('store_brokerage_two');
			foreach ($list as &$item) {
				$item['one_brokerage_ratio'] = $this->compoteBrokerage($store_brokerage_ratio, $item['one_brokerage']);
				$item['two_brokerage_ratio'] = $this->compoteBrokerage($store_brokerage_two, $item['two_brokerage']);
			}
		}
        return compact('count', 'list');
    }

    /**
     * 商城获取分销员等级列表
     * @param int $uid
     * @return array
     */
    public function getUserlevelList(int $uid)
    {
        //商城分销是否开启
        if (!sys_config('brokerage_func_status')) {
            return [];
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserCacheInfo($uid);
        if (!$user) {
            throw new ValidateException('没有此用户');
        }
        try {
            //检测升级
            $this->checkUserLevelFinish($uid);
        } catch (\Throwable $e) {

        }
        $list = $this->dao->getList(['is_del' => 0, 'status' => 1]);
        $agent_level = $user['agent_level'] ?? 0;
        //没等级默认最低等级
        if (!$agent_level) {
            $levelInfo = [];
        } else {
            $levelInfo = $this->getLevelInfo($agent_level) ?: [];
        }
        $sum_task = $finish_task = 0;
        if ($levelInfo) {
            /** @var AgentLevelTaskServices $levelTaskServices */
            $levelTaskServices = app()->make(AgentLevelTaskServices::class);
            $sum_task = $levelTaskServices->count(['level_id' => $levelInfo['id'], 'is_del' => 0, 'status' => 1]);
            /** @var AgentLevelTaskRecordServices $levelTaskRecordServices */
            $levelTaskRecordServices = app()->make(AgentLevelTaskRecordServices::class);
            $finish_task = $levelTaskRecordServices->count(['level_id' => $levelInfo['id'], 'uid' => $uid]);
        }
        $levelInfo['sum_task'] = $sum_task;
        $levelInfo['finish_task'] = $finish_task;

        //推广订单总数
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $user['spread_order_count'] = $orderServices->count(['type' => 0, 'paid' => 1, 'refund_status' => [0, 3], 'is_del' => 0, 'is_system_del' => 0, 'spread_or_uid' => $uid]);

        //冻结佣金和可提现金额
        /** @var UserBrokerageServices $userBrokerageServices */
        $userBrokerageServices = app()->make(UserBrokerageServices::class);
        $user['broken_commission'] = $userBrokerageServices->getUserFrozenPrice($uid);
        $user['commissionCount'] = bcsub($user['brokerage_price'], $user['broken_commission'], 2);
        //佣金排行
        $user['position_count'] = $userBrokerageServices->getUserBrokerageRank($uid, 'week');
        //推广人排行
        $startTime = strtotime('this week Monday');
        $endTime = time();
        $field = 't0.uid,t0.spread_uid,count(t1.spread_uid) AS count,t0.add_time,t0.nickname,t0.avatar';
        $rankList = $userServices->getAgentRankList([$startTime, $endTime], $field);
        $rank = 0;
        foreach ($rankList as $key => $item) {
            if ($item['uid'] == $uid) $rank = $key + 1;
        }
        $user['rank_count'] = $rank;
        $user['spread_count'] = $userServices->count(['spread_uid' => $uid]);
        /** @var UserExtractServices $extractService */
        $extractService = app()->make(UserExtractServices::class);
        $user['extract_price'] = $extractService->sum(['uid' => $uid, 'status' => 1], 'extract_price');
        return ['user' => $user, 'level_list' => $list, 'level_info' => $levelInfo];
    }

    /**
     * 获取下一等级
     * @param int $level_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNextLevelInfo(int $level_id = 0)
    {
        $grade = 0;
        if ($level_id) {
            $grade = $this->dao->value(['id' => $level_id, 'is_del' => 0, 'status' => 1], 'grade') ?: 0;
        }
        return $this->dao->getOne([['grade', '>', $grade], ['is_del', '=', 0], ['status', '=', 1]]);
    }

    /**
     * 检测用户是否能升级
     * @param int $uid
     * @param array $uids
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkUserLevelFinish(int $uid, array $uids = [])
    {
        //商城分销是否开启
        if (!sys_config('brokerage_func_status')) {
            return false;
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserCacheInfo($uid);
        if (!$userInfo) {
            return false;
        }
        $list = $this->dao->getList(['is_del' => 0, 'status' => 1]);
        if (!$list) {
            return false;
        }
        if (!$uids) {
            //获取上级uid ｜｜ 开启自购返回自己uid
            $spread_uid = $userServices->getSpreadUid($uid, $userInfo);
            $two_spread_uid = 0;
            if ($spread_uid > 0 && $one_user_info = $userServices->getUserCacheInfo($spread_uid)) {
                $two_spread_uid = $userServices->getSpreadUid($spread_uid, $one_user_info, false);
            }
            $uids = array_unique([$uid, $spread_uid, $two_spread_uid]);
        }
        foreach ($uids as $uid) {
            if ($uid <= 0) continue;
            if ($uid != $userInfo['uid']) {
                $userInfo = $userServices->getUserCacheInfo($uid);
                if (!$userInfo)
                    continue;
            }

            $now_grade = 0;
            if ($userInfo['agent_level']) {
                $now_grade = $this->dao->value(['id' => $userInfo['agent_level']], 'grade') ?: 0;
            }
            foreach ($list as $levelInfo) {
                if (!$levelInfo || $levelInfo['grade'] <= $now_grade) {
                    continue;
                }
                /** @var AgentLevelTaskServices $levelTaskServices */
                $levelTaskServices = app()->make(AgentLevelTaskServices::class);
                $task_list = $levelTaskServices->getTaskList(['level_id' => $levelInfo['id'], 'is_del' => 0, 'status' => 1]);
                if (!$task_list) {
                    continue;
                }
                foreach ($task_list as $task) {
                    $levelTaskServices->checkLevelTaskFinish($uid, (int)$task['id'], $task);
                }
                /** @var AgentLevelTaskRecordServices $levelTaskRecordServices */
                $levelTaskRecordServices = app()->make(AgentLevelTaskRecordServices::class);
                $ids = array_column($task_list, 'id');
                $finish_task = $levelTaskRecordServices->count(['level_id' => $levelInfo['id'], 'uid' => $uid, 'task_id' => $ids]);
                //任务完成升这一等级
                if ($finish_task >= count($task_list)) {
                    $userServices->update($uid, ['agent_level' => $levelInfo['id']]);
                } else {
                    break;
                }
            }
        }

        return true;
    }

    /**
     * 分销等级上浮
     * @param int $uid
     * @param array $userInfo
     * @return array|int[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgentLevelBrokerage(int $uid, $userInfo = [])
    {
        $one_brokerage_up = $two_brokerage_up = $spread_uid = $spread_two_uid = 0;
        $data = [$one_brokerage_up, $two_brokerage_up, $spread_uid, $spread_two_uid];
        if (!$uid) {
            return $data;
        }
        //商城分销是否开启
        if (!sys_config('brokerage_func_status')) {
            return $data;
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userInfo) {
            $userInfo = $userServices->getUserCacheInfo($uid);
        }
        if (!$userInfo) {
            return $data;
        }
        //获取上级uid ｜｜ 开启自购返回自己uid
        $spread_uid = $userServices->getSpreadUid($uid, $userInfo);
        $one_agent_level = 0;
        $two_agent_level = 0;;
        if ($spread_uid > 0 && $one_user_info = $userServices->getUserInfo($spread_uid)) {
            $one_agent_level = $one_user_info['agent_level'] ?? 0;
            $spread_two_uid = $userServices->getSpreadUid($spread_uid, $one_user_info, false);
            if ($spread_two_uid > 0 && $two_user_info = $userServices->getUserInfo($spread_two_uid)) {
                $two_agent_level = $two_user_info['agent_level'] ?? 0;
            }
        }
        $one_brokerage_up = $one_agent_level ? ($this->getLevelInfo($one_agent_level)['one_brokerage'] ?? 0) : 0;
        $two_brokerage_up = $two_agent_level ? ($this->getLevelInfo($two_agent_level)['two_brokerage'] ?? 0) : 0;
        return [$one_brokerage_up, $two_brokerage_up, $spread_uid, $spread_two_uid];
    }

	/**
 	* 计算一二级返佣比率上浮
	* @param $ratio
	* @param $brokerage
	* @return int|string
	*/
	public function compoteBrokerage($ratio, $brokerage)
	{
		if (!$ratio || !$brokerage) {
			return 0;
		}
		$brokerage = bcdiv((string)$brokerage, '100', 4);
		return bcmul((string)$ratio, bcadd('1', (string)$brokerage, 4), 2);
	}

    /**
     * 添加等级表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm()
    {
		$store_brokerage_ratio = sys_config('store_brokerage_ratio');
		$store_brokerage_two = sys_config('store_brokerage_two');

        $field[] = Form::input('name', '等级名称')->maxlength(4)->col(24);
        $field[] = Form::number('grade', '等级', 0)->min(0)->precision(0);
        $field[] = Form::frameImage('image', '背景图', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')))->icon('ios-add')->width('960px')->height('505px')->modal(['footer-hide' => true])->appendValidate(Iview::validateStr()->required()->message('请选择背景图'));
        $field[] = Form::number('one_brokerage', '一级上浮', 0)->info('在分销一级佣金基础上浮（0-1000之间整数）百分比，目前一级返佣比率：'. $store_brokerage_ratio . '%，例如上浮10%，则返佣比率：一级返佣比率 * (1 + 一级上浮比率) = ' . $this->compoteBrokerage($store_brokerage_ratio, 10) .'%')->min(0)->max(1000);
        $field[] = Form::number('two_brokerage', '二级上浮', 0)->info('在分销二级佣金基础上浮（0-1000之间整数）百分比，目前二级返佣比率：'. $store_brokerage_ratio . '%，例如上浮10%，则返佣比率：二级返佣比率 * (1 + 二级上浮比率) = ' . $this->compoteBrokerage($store_brokerage_two, 10). '%')->min(0)->max(1000);
        $field[] = Form::radio('status', '是否显示', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return create_form('添加分销员等级', $field, Url::buildUrl('/agent/level'), 'POST');
    }

    /**
     * 获取修改等级表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function editForm(int $id)
    {
		$store_brokerage_ratio = sys_config('store_brokerage_ratio');
		$store_brokerage_two = sys_config('store_brokerage_two');
        $levelInfo = $this->getLevelInfo($id);
        if (!$levelInfo)
            throw new AdminException('数据不存在');
        $field = [];
        $field[] = Form::hidden('id', $id);
        $field[] = Form::input('name', '等级名称', $levelInfo['name'])->maxlength(4)->col(24);
        $field[] = Form::number('grade', '等级', $levelInfo['grade'])->min(0)->precision(0);
        $field[] = Form::frameImage('image', '背景图', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $levelInfo['image'])->icon('ios-add')->width('960px')->height('505px')->modal(['footer-hide' => true])->appendValidate(Iview::validateStr()->required()->message('请选择背景图'));
        $field[] = Form::number('one_brokerage', '一级上浮', $levelInfo['one_brokerage'])->info('在分销一级佣金基础上浮（0-1000之间整数）百分比，目前一级返佣比率：'. $store_brokerage_ratio . '%，上浮'. $levelInfo['one_brokerage'] . '%，则返佣比率：一级返佣比率 * (1 + 一级上浮比率) = ' . $this->compoteBrokerage($store_brokerage_ratio, $levelInfo['one_brokerage']) .'%')->min(0)->max(1000);
        $field[] = Form::number('two_brokerage', '二级上浮', $levelInfo['two_brokerage'])->info('在分销二级佣金基础上浮（0-1000之间整数）百分比，目前二级返佣比率：'. $store_brokerage_ratio . '%，上浮' . $levelInfo['two_brokerage'] . '%，则返佣比率：二级返佣比率 * (1 + 二级上浮比率) = ' . $this->compoteBrokerage($store_brokerage_two, $levelInfo['two_brokerage']). '%')->min(0)->max(1000);
        $field[] = Form::radio('status', '是否显示', $levelInfo['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);

        return create_form('编辑分销员等级', $field, Url::buildUrl('/agent/level/' . $id), 'PUT');
    }

    /**
     * 赠送分销等级表单
     * @param int $uid
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function levelForm(int $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid);
        if (!$userInfo) {
            throw new AdminException('分销员不存在');
        }
        $levelList = $this->dao->getList(['is_del' => 0, 'status' => 1]);
        $setOptionLabel = function () use ($levelList) {
            $menus = [];
            foreach ($levelList as $level) {
                $menus[] = ['value' => $level['id'], 'label' => $level['name']];
            }
            return $menus;
        };
        $field[] = Form::hidden('uid', $uid);
        $field[] = Form::select('id', '分销等级', $userInfo['agent_level'] ?? 0)->setOptions(Form::setOptions($setOptionLabel))->filterable(true);
        return create_form('赠送分销等级', $field, Url::buildUrl('/agent/give_level'), 'post');
    }

    /**
     * 赠送分销等级
     * @param int $uid
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function givelevel(int $uid, int $id)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid);
        if (!$userInfo) {
            throw new AdminException('分销员不存在');
        }
        $levelInfo = $this->getLevelInfo($id);
        if (!$levelInfo) {
            throw new AdminException('分销等级不存在');
        }
        if ($userServices->update($uid, ['agent_level' => $id]) === false) {
            throw new AdminException('赠送失败');
        }
        return true;
    }
}
