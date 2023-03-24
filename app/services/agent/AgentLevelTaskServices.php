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

use app\dao\agent\AgentLevelTaskDao;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use FormBuilder\Factory\Iview;
use think\exception\ValidateException;
use think\facade\Route as Url;


/**
 *
 * Class AgentLevelTaskServices
 * @package app\services\agent
 * @mixin AgentLevelTaskDao
 */
class AgentLevelTaskServices extends BaseServices
{
    /**
     * 任务类型
     * type 记录在数据库中用来区分任务
     * name 任务名 (任务名中的{$num}会自动替换成设置的数字 + 单位)
     * max_number 最大设定数值 0为不限定
     * min_number 最小设定数值
     * unit 单位
     * */
    protected $TaskType = [
        [
            'type' => 1,
            'method' => 'spread',
            'name' => '邀请好友{$num}成为下级',
            'real_name' => '邀请好友成为下级',
            'max_number' => 0,
            'min_number' => 1,
            'unit' => '人',
            'image' => '/uploads/system/agent_spread.png'
        ],
        [
            'type' => 2,
            'method' => 'consumePrice',
            'name' => '自身消费满{$num}',
            'real_name' => '自身消费金额',
            'max_number' => 0,
            'min_number' => 0,
            'unit' => '元',
            'image' => '/uploads/system/agent_self_order_price.png'
        ],
        [
            'type' => 3,
            'method' => 'consumeCount',
            'name' => '自身消费满{$num}',
            'real_name' => '自身消费单数',
            'max_number' => 0,
            'min_number' => 0,
            'unit' => '单',
            'image' => '/uploads/system/agent_self_order.png'
        ],
        [
            'type' => 4,
            'method' => 'spreadConsumePrice',
            'name' => '下级消费满{$num}',
            'real_name' => '下级消费金额',
            'max_number' => 0,
            'min_number' => 0,
            'unit' => '元',
            'image' => '/uploads/system/agent_spread_order_price.png'
        ],
        [
            'type' => 5,
            'method' => 'spreadConsumeCount',
            'name' => '下级消费满{$num}',
            'real_name' => '下级消费单数',
            'max_number' => 0,
            'min_number' => 0,
            'unit' => '单',
            'image' => '/uploads/system/agent_spread_order.png'
        ],
    ];

    /**
     * AgentLevelTaskServices constructor.
     * @param AgentLevelTaskDao $dao
     */
    public function __construct(AgentLevelTaskDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某一个任务信息
     * @param int $id
     * @param string $field
     * @param array $with
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLevelTaskInfo(int $id, string $field = '*', array $with = [])
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
    public function getLevelTaskList(array $where)
    {
        $where['is_del'] = 0;
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getTaskList($where, '*', [], $page, $limit);
        if ($list) {
            $allTyep = $this->getTaskTypeAll();
            $allTyep = array_combine(array_column($allTyep, 'type'), $allTyep);
            foreach ($list as &$item) {
                $item['type_name'] = $allTyep[$item['type']]['real_name'] ?? '';
            }
        }
        $count = $this->dao->count($where);
        return compact('count', 'list');
    }

    /**
     * 获取某个等级某个类型任务
     * @param int $level_id
     * @param int $type
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLevelTypeTask(int $level_id, int $type = 1)
    {
        return $this->dao->get(['level_id' => $level_id, 'type' => $type, 'is_del' => 0]);
    }

    /**
     * 添加等级任务表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm(int $level_id)
    {
        /** @var AgentLevelServices $levelServices */
        $levelServices = app()->make(AgentLevelServices::class);
        if (!$levelServices->getLevelInfo($level_id)) {
            throw new AdminException('选择的等级不存在，请返回重新选择');
        }
        $taskList = $this->getTaskTypeAll();
        $setOptionLabel = function () use ($taskList) {
            $menus = [];
            foreach ($taskList as $task) {
                $menus[] = ['value' => $task['type'], 'label' => ($task['real_name'] ?? '') . '(' . ($task['unit'] ?? '') . ')'];
            }
            return $menus;
        };
        $field[] = Form::hidden('level_id', $level_id);
        $field[] = Form::select('type', '任务类型')->setOptions(Form::setOptions($setOptionLabel))->filterable(true)->info('请选择需要达成的任务类型')->appendValidate(Iview::validateInt()->message('请选择任务类型')->required());
        $field[] = Form::number('number', '任务要求', 0)->info('达成任务需要完成的人数、金额或订单数')->required('请填写任务要求');
		$field[] = Form::input('name', '任务名称')->col(24)->required('请填写任务名称');
        $field[] = Form::textarea('desc', '任务描述');
        $field[] = Form::number('sort', '排序', 0)->min(0);
        $field[] = Form::radio('status', '是否显示', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return create_form('添加等级任务', $field, Url::buildUrl('/agent/level_task'), 'POST');
    }

    /**
     * 获取修改任务数据
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function editForm(int $id)
    {
        $levelTaskInfo = $this->getLevelTaskInfo($id);
        if (!$levelTaskInfo)
            throw new AdminException('数据不存在');
        $field = [];
        $field[] = Form::hidden('id', $id);
        $taskList = $this->getTaskTypeAll();
        $setOptionLabel = function () use ($taskList) {
            $menus = [];
            foreach ($taskList as $task) {
                $menus[] = ['value' => $task['type'], 'label' => $task['real_name'] ?? '' . '(' . $task['unit'] ?? '' . ')'];
            }
            return $menus;
        };
        $field[] = Form::select('type', '任务类型', $levelTaskInfo['type'])->setOptions(Form::setOptions($setOptionLabel))->filterable(true)->info('请选择需要达成的任务类型')->appendValidate(Iview::validateInt()->message('请选择任务类型')->required());
		$field[] = Form::number('number', '任务要求', $levelTaskInfo['number'])->min(0)->info('达成任务需要完成的人数、金额或订单数')->required('请填写任务要求');
        $field[] = Form::input('name', '任务名称', $levelTaskInfo['name'])->required('请填写任务名称');
        $field[] = Form::textarea('desc', '任务描述', $levelTaskInfo['desc']);
        $field[] = Form::number('sort', '排序', $levelTaskInfo['sort'])->min(0);
        $field[] = Form::radio('status', '是否显示', $levelTaskInfo['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);

        return create_form('编辑等级任务', $field, Url::buildUrl('/agent/level_task/' . $id), 'PUT');
    }

    /**
     * 获取任务类型
     * @return array[]
     */
    public function getTaskTypeAll()
    {
        return $this->TaskType;
    }

    /**
     * 获取某个任务
     * @param string $type 任务类型
     * @return array
     * */
    public static function getTaskType($type)
    {
        foreach (self::$TaskType as $item) {
            if ($item['type'] == $type) return $item;
        }
    }

    /**
     * 获取用户某一个分销等级任务情况
     * @param int $uid
     * @param int $level_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserLevelTaskList(int $uid, int $level_id)
    {
        //商城分销是否开启
        if (!sys_config('brokerage_func_status')) {
            return [];
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ValidateException('没有此用户');
        }
        /** @var AgentLevelServices $levelServices */
        $levelServices = app()->make(AgentLevelServices::class);
        $levelInfo = $levelServices->getLevelInfo($level_id);
        if (!$levelInfo) {//没有默认最低等级
            $list = $levelServices->getList(['is_del' => 0, 'status' => 1]);
            $levelInfo = $list[0] ?? [];
            $level_id = $levelInfo['id'] ?? 0;
        }
        $taskList = $this->dao->getTaskList(['level_id' => $level_id, 'is_del' => 0, 'status' => 1]);
        $speedAll = $speedCount = 0;
        if ($taskList) {
            $userLevel = [];
            if ($user['agent_level'] ?? 0) $userLevel = $levelServices->getLevelInfo($user['agent_level']);
            $allTyep = $this->getTaskTypeAll();
            $allTyep = array_combine(array_column($allTyep, 'type'), $allTyep);
            $baseUrl = sys_config('site_url');
            foreach ($taskList as &$task) {
                $task['finish'] = 1;
                $task['task_type_title'] = '已完成';
                $task['speed'] = 100;
                $task['new_number'] = $task['number'];
                //当前等级之前的等级任务 全部为完成
                if (!$userLevel || $userLevel['grade'] < $levelInfo['grade']) {
                    [$title, $num, $isComplete] = $this->checkLevelTaskFinish($uid, (int)$task['id']);
                    if (!$isComplete) {
                        $scale = in_array($task['type'], [2, 4]) ? 2 : 0;
                        $task['finish'] = 0;
                        $numdata = bcsub($task['number'], $num, $scale);
                        $task['task_type_title'] = '还需' . str_replace('{$num}', $numdata . $allTyep[$task['type']]['unit'] ?? '', $title);
                        $task['speed'] = bcmul((string)bcdiv((string)$num, (string)$task['number'], 2), '100', 0);
                        $task['new_number'] = $num;
                        $task['image'] = $baseUrl.$allTyep[$task['type']]['image'] ?? '';
                    }
                }
                $speedCount = bcadd((string)$speedCount, $task['speed'], 0);
            }
            $speedAll = count($taskList) > 0 ? floatval(bcdiv($speedCount, (string)count($taskList), 2)) : 0;
        }
        return ['list' => $taskList, 'speedAll' => $speedAll];
    }


    /**
     * 检测某个任务完成情况
     * @param int $uid
     * @param int $task_id
     * @return array|false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkLevelTaskFinish(int $uid, int $task_id, $levelTaskInfo = [])
    {
        if (!$levelTaskInfo) {
            $levelTaskInfo = $this->getLevelTaskInfo($task_id);
        }
        if (!$levelTaskInfo) return false;
        $allTyep = $this->getTaskTypeAll();
        $allTyep = array_combine(array_column($allTyep, 'type'), $allTyep);
        $userNumber = 0;
        $msg = $allTyep[$levelTaskInfo['type']]['name'] ?? '';
        switch ($levelTaskInfo['type']) {
            case 1:
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                $userNumber = $userServices->count(['spread_uid' => $uid]);
                break;
            case 2:
                /** @var StoreOrderServices $storeOrderServices */
                $storeOrderServices = app()->make(StoreOrderServices::class);
                $where = ['pid' => 0, 'uid' => $uid, 'paid' => 1, 'refund_status' => [0, 3], 'is_del' => 0, 'is_system_del' => 0];
                $userNumber = $storeOrderServices->sum($where, 'pay_price');
                break;
            case 3:
                /** @var StoreOrderServices $storeOrderServices */
                $storeOrderServices = app()->make(StoreOrderServices::class);
                $where = ['pid' => 0, 'uid' => $uid, 'paid' => 1, 'refund_status' => [0, 3], 'is_del' => 0, 'is_system_del' => 0];
                $userNumber = $storeOrderServices->count($where);
                break;
            case 4:
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                $spread_uids = $userServices->getColumn(['spread_uid' => $uid], 'uid');
                if ($spread_uids) {
                    /** @var StoreOrderServices $storeOrderServices */
                    $storeOrderServices = app()->make(StoreOrderServices::class);
                    $where = ['pid' => 0, 'uid' => $spread_uids, 'paid' => 1, 'refund_status' => [0, 3], 'is_del' => 0, 'is_system_del' => 0];
                    $userNumber = $storeOrderServices->sum($where, 'pay_price');
                }
                break;
            case 5:
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                $spread_uids = $userServices->getColumn(['spread_uid' => $uid], 'uid');
                if ($spread_uids) {
                    /** @var StoreOrderServices $storeOrderServices */
                    $storeOrderServices = app()->make(StoreOrderServices::class);
                    $where = ['pid' => 0, 'uid' => $spread_uids, 'paid' => 1, 'refund_status' => [0, 3], 'is_del' => 0, 'is_system_del' => 0];
                    $userNumber = $storeOrderServices->count($where);
                }
                break;
            default:
                return false;
        }
        $isComplete = false;
        if ($userNumber >= $levelTaskInfo['number']) {
            /** @var AgentLevelTaskRecordServices $agentLevelTaskRecordServices */
            $agentLevelTaskRecordServices = app()->make(AgentLevelTaskRecordServices::class);
            $isComplete = true;
            if (!$agentLevelTaskRecordServices->get(['uid' => $uid, 'level_id' => $levelTaskInfo['level_id'], 'task_id' => $levelTaskInfo['id']])) {
                $data = ['uid' => $uid, 'level_id' => $levelTaskInfo['level_id'], 'task_id' => $levelTaskInfo['id'], 'add_time' => time()];
                $isComplete = $agentLevelTaskRecordServices->save($data);
            }
        }
        return [$msg, $userNumber, $isComplete];
    }

    /**
     * 检测等级任务
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkTypeTask(int $id, array $data)
    {
        if (!$id && (!isset($data['level_id']) || !$data['level_id'])) {
            throw new ValidateException('缺少等级任务必要参数');
        }
        if ($id) {
            $task = $this->getLevelTaskInfo($id);
            if (!$task) {
                throw new ValidateException('编辑的任务不存在');
            }
            $data['level_id'] = $task['level_id'];
        }
        /** @var AgentLevelServices $agentLevelServices */
        $agentLevelServices = app()->make(AgentLevelServices::class);
        $levelInfo = $agentLevelServices->getLevelInfo($data['level_id']);
        if (!$levelInfo) {
            throw new ValidateException('所选择的分销等级不存在或者删除，请重新选择');
        }
        $task = $this->dao->getOne(['level_id' => $data['level_id'], 'type' => $data['type'], 'is_del' => 0]);
        if (($id && $task && $task['id'] != $id) || (!$id && $task)) {
            throw new ValidateException('该等级已存在此类型任务');
        }
        $taskList = $this->dao->getTypTaskList($data['type']);
        if ($taskList) {
            foreach ($taskList as $taskInfo) {
                if (is_null($taskInfo['grade'])) continue;
                if ($levelInfo['grade'] > $taskInfo['grade'] && $data['number'] <= $taskInfo['number']) {
                    throw new ValidateException('不能小于低等级同类型任务限定数量');
                }
                if ($levelInfo['grade'] < $taskInfo['grade'] && $data['number'] >= $taskInfo['number']) {
                    throw new ValidateException('不能大于高等级同类型任务限定数量');
                }
            }
        }

        return true;
    }
}
