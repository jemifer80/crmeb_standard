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
//declare (strict_types=1);
namespace app\services\user;

use app\jobs\user\UserBatchJob;
use app\services\BaseServices;
use app\dao\user\UserDao;
use app\services\user\group\UserGroupServices;
use app\services\user\label\UserLabelServices;
use app\services\user\level\SystemUserLevelServices;
use crmeb\exceptions\AdminException;
use think\exception\ValidateException;

/**
 * 用户批量操作
 * Class UserBatchProcessServices
 * @package app\services\user
 * @mixin UserDao
 */
class UserBatchProcessServices extends BaseServices
{

	/**
 	* 批量处理参数
	* @var array[]
	*/
	protected $data = [
			'group_id' => [],//分组ID
			'label_id' => [],//标签ids
			'level_id' => 0,//等级ID
			'money_status' => 0,//余额变动状态
			'money'=> 0,//余额变动金额
			'integration_status'=> 0,//积分变动状态
			'integration' => 0,//积分变动数量
			'days_status' => 0,//付费会员条数变动状态
			'day' => 0,//变动天数
			'spread_uid' => 0,//上级推广人uid
		];

    /**
     * UserServices constructor.
     * @param UserDao $dao
     */
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

	/**
 	* 根据搜索条件查询uids
	* @param $where
	* @return array
	*/
	public function getUidByWhere($where)
	{
		if ($where) {
			if (is_string($where)) {
				$where = json_decode($where, true);
			} elseif (is_array($where)) {
				$where = $where;
			} else {
				$where = [];
			}
		}
		/** @var UserWechatuserServices $userWechatUser */
		$userWechatUser = app()->make(UserWechatuserServices::class);
		$fields = 'u.uid';
		[$list, $count] = $userWechatUser->getWhereUserList($where, $fields);
		$uids = array_unique(array_column($list, 'uid'));
		return $uids;
	}

	/**
 	* 用户批量操作
	* @param int $type
	* @param array $uids
	* @param array $input_data
	* @param bool $is_all
	* @param array $where
	* @return bool
	*/
	public function batchProcess(int $type, array $uids, array $input_data, bool $is_all = false, array $where = [])
	{
		//全选
		if ($is_all == 1) {
			$uids = $this->getUidByWhere($where);
		}
		$data = [];
		switch ($type) {
            case 1://分组
            	$group_id = $input_data['group_id'] ?? 0;
            	if (!$group_id) throw new ValidateException('请选择分组');
				/** @var UserGroupServices $userGroup */
				$userGroup = app()->make(UserGroupServices::class);
				if (!$userGroup->getGroup($group_id)) {
					throw new ValidateException('该分组不存在');
				}
				$data['group_id'] = $group_id;
                break;
            case 2://标签
            	$label_id = $input_data['label_id'] ?? [];
            	if (!$label_id) throw new ValidateException ('请选择标签');
				/** @var UserLabelServices $userLabelServices */
				$userLabelServices = app()->make(UserLabelServices::class);
				$count = $userLabelServices->getCount([['id', 'IN', $label_id]]);
				if ($count != count($label_id)) {
					throw new ValidateException('用户标签不存在或被删除');
				}
				$data['label_id'] = $label_id;
                break;
			case 3://等级
				$level_id = $input_data['level_id'] ?? 0;
            	if (!$level_id) throw new ValidateException('请选择用户等级');
				/** @var SystemUserLevelServices $systemLevelServices */
        		$systemLevelServices = app()->make(SystemUserLevelServices::class);
				//查询当前选择的会员等级
				$systemLevel = $systemLevelServices->getLevel($level_id);
				if (!$systemLevel) throw new AdminException('您选择赠送的用户等级不存在！');
				$data['level_id'] = $level_id;
                break;
			case 4://积分余额
				$data['money_status'] = $input_data['money_status'] ?? 0;
				$data['money'] = $input_data['money'] ?? 0;
				$data['integration_status'] = $input_data['integration_status'] ?? 0;
				$data['integration'] = $input_data['integration'] ?? 0;
				if (!$data['money_status'] && !$data['integration_status']) {
					throw new AdminException('请选择操作积分或余额');
				}
				if ($data['money_status'] && !$data['money']) {
					throw new AdminException('请填写变动余额数量');
				}
				if ($data['integration_status'] && !$data['integration']) {
					throw new AdminException('请填写变动积分数量');
				}
                break;
			case 5://赠送会员
				$data['days_status'] = $input_data['days_status'] ?? 0;
				$data['day'] = $input_data['day'] ?? 0;
				if (!$data['days_status']) {
					throw new AdminException('请选择增加或者减少会员天数');
				}
				if (!$data['day']) {
					throw new AdminException('请填写变动会员天数');
				}
                break;
			case 6://上级推广人
				$spread_uid = $input_data['spread_uid'] ?? 0;
				if (!$spread_uid) throw new ValidateException('请选择上级推广人');
				if (!$this->dao->count(['uid' => $spread_uid])) {
					throw new ValidateException('上级用户不存在');
				}
				$data['spread_uid'] = $spread_uid;
                break;
            default:
                throw new AdminException('暂不支持该类型批操作');
        }
		//加入批量队列
		UserBatchJob::dispatchDo('userBatch', [$type, $uids, $data]);
		return true;
	}
}
