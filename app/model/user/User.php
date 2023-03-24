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

namespace app\model\user;

use app\model\agent\AgentLevel;
use app\model\order\StoreOrder;
use app\model\user\level\SystemUserLevel;
use app\model\user\group\UserGroup;
use app\model\user\label\UserLabel;
use app\model\user\label\UserLabelRelation;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * Class User
 * @package app\model\user
 */
class User extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    /**
     * @var string
     */
    protected $pk = 'uid';

    protected $name = 'user';

    protected $insert = ['add_time', 'add_ip', 'last_time', 'last_ip'];

    protected $hidden = [
        'add_ip', 'account', 'clean_time', 'last_ip', 'pwd'
    ];

    protected $deleteTime = 'delete_time';

    /**
     * 自动转类型
     * @var string[]
     */
    protected $type = [
        'birthday' => 'int'
    ];

    protected $updateTime = false;

    /**
     * 更新用户事件
     * @param Model $user
     */
    public static function onAfterUpdate($user)
    {
        event('user.update');
    }

    protected function setAddTimeAttr($value)
    {
        return time();
    }

    protected function setAddIpAttr($value)
    {
        return app('request')->ip();
    }

    protected function setLastTimeAttr($value)
    {
        return time();
    }

    protected function setLastIpAttr($value)
    {
        return app('request')->ip();
    }

	/**
     * 自定义信息
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function setExtendInfoAttr($value)
    {
        if ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }
        return '';
    }

    /**
     * 自定义信息
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getExtendInfoAttr($value)
    {
        if ($value) {
            return is_string($value) ? json_decode($value, true) : $value;
        }
        return [];
    }

	/**
     * 自定义会员卡信息
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function setLevelExtendInfoAttr($value)
    {
        if ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }
        return '';
    }

    /**
     * 自定义会员卡信息
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getLevelExtendInfoAttr($value)
    {
        if ($value) {
            return is_string($value) ? json_decode($value, true) : $value;
        }
        return [];
    }

    /**
     * 链接会员登陆设置表
     * @return \think\model\relation\HasOne
     */
    public function systemUserLevel()
    {
        return $this->hasOne(SystemUserLevel::class, 'id', 'level');
    }

    /**
     * 关联用户分组
     * @return \think\model\relation\HasOne
     */
    public function userGroup()
    {
        return $this->hasOne(UserGroup::class, 'id', 'group_id');
    }

    /**
     * 关联自己
     * @return \think\model\relation\HasOne
     */
    public function spreadUser()
    {
        return $this->hasOne(self::class, 'uid', 'spread_uid');
    }

    /**
     * 关联自己
     * @return \think\model\relation\HasOne
     */
    public function spreadCount()
    {
        return $this->hasMany(User::class, 'spread_uid', 'uid');
    }

    /**
     * 关联用户标签关系
     * @return \think\model\relation\HasMany
     */
    public function LabelRelation()
    {
        return $this->hasMany(UserLabelRelation::class, 'uid', 'uid');
    }

    /**
     * 关联用户标签
     * @return \think\model\relation\HasManyThrough
     */
    public function label()
    {
        return $this->hasManyThrough(UserLabel::class, UserLabelRelation::class, 'uid', 'id', 'uid', 'label_id');
    }

    /**
     * 关联用户地址
     * @return \think\model\relation\HasMany
     */
    public function address()
    {
        return $this->hasMany(UserAddress::class, 'uid', 'uid');
    }

    /**
     * 关联提现
     * @return \think\model\relation\HasMany
     */
    public function extract()
    {
        return $this->hasMany(UserExtract::class, 'uid', 'uid');
    }

    /**
     * 关联订单
     * @return User|\think\model\relation\HasMany
     */
    public function order()
    {
        return $this->hasMany(StoreOrder::class, 'uid', 'uid');
    }

    /**
     * 关联分销等级
     * @return \think\model\relation\HasOne
     */
    public function agentLevel()
    {
        return $this->hasOne(AgentLevel::class, 'id', 'agent_level')->where('is_del', 0)->where('status', 1);
    }

    /**
     * 关联积分数据
     * @return \think\model\relation\HasMany
     */
    public function bill()
    {
        return $this->hasMany(UserBill::class, 'uid', 'uid');
    }

    /**
     * 关联佣金数据
     * @return \think\model\relation\HasMany
     */
    public function brokerage()
    {
        return $this->hasMany(UserBrokerage::class, 'uid', 'uid');
    }

    /**
     * 关联余额数据
     * @return \think\model\relation\HasMany
     */
    public function money()
    {
        return $this->hasMany(UserMoney::class, 'uid', 'uid');
    }

    /**
     * 用户uid
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('uid', $value);
        else
            $query->where('uid', $value);
    }

    /**
     * 账号搜索器
     * @param Model $query
     * @param $value
     */
    public function searchAccountAttr($query, $value)
    {
        $query->where('account', $value);
    }

    /**
     * 密码搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPwdAttr($query, $value)
    {
        $query->where('pwd', $value);
    }

    /**
     * uid范围查询搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUidsAttr($query, $value)
    {
        $query->whereIn('uid', $value);
    }

    /**
     * 模糊条件搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStoreLikeAttr($query, $value)
    {
        $query->where('uid|phone', 'LIKE', "%$value%");
    }

    /**
     * 模糊条件搜索器
     * @param Model $query
     * @param $value
     */
    public function searchLikeAttr($query, $value)
    {
        $query->where('account|nickname|phone|real_name|uid', 'LIKE', "%$value%");
    }

    /**
     * 手机号搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPhoneAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('phone', $value);
        } else {
            $query->where('phone', $value);
        }
    }

    /**
     * 分组搜索器
     * @param Model $query
     * @param $value
     */
    public function searchGroupIdAttr($query, $value)
    {
        $query->where('group_id', $value);
    }

    /**
     * 是否推广人搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsPromoterAttr($query, $value)
    {
        $query->where('is_promoter', $value);
    }

    /**
     * 状态搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 会员等级搜索器
     * @param Model $query
     * @param $value
     */
    public function searchLevelAttr($query, $value)
    {
        $query->where('level', $value);
    }

    /**
     * 推广人uid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchSpreadUidAttr($query, $value)
    {
        $query->where('spread_uid', $value);
    }

    /**
     * 推广人uid不等于搜索器
     * @param Model $query
     * @param $value
     */
    public function searchNotSpreadUidAttr($query, $value)
    {
        $query->where('spread_uid', '<>', $value);
    }

    /**
     * 推广人时间搜索器
     * @param Model $query
     * @param $value
     */
    public function searchSpreadTimeAttr($query, $value)
    {
        if ($value) {
            if (is_array($value)) {
                if (count($value) == 2) $query->where('spread_time', $value[0], $value[1]);
            } else {
                $query->where('spread_time', $value);
            }
        }
    }

    /**
     * 用户类型搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUserTypeAttr($query, $value)
    {
        if ($value != '') $query->where('user_type', $value);
    }

    /**
     * 购买次数搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPayCountAttr($query, $value)
    {
        if ($value !== '') {
            if ($value == -1) {
                $query->where('pay_count', '>', 0);
            } else {
                $query->where('pay_count', $value);
            }
        }
    }

    /**
     * 用户推广资格
     * @param Model $query
     * @param $value
     */
    public function searchSpreadOpenAttr($query, $value)
    {
        if ($value != '') $query->where('spread_open', $value);
    }

    public function searchNicknameAttr($query, $value)
    {
        $query->where('nickname', "like", "%" . $value . "%");
    }

    /**
     * bar_code搜索器
     * @param Model $query
     * @param $value
     */
    public function searchBarCodeAttr($query, $value)
    {
        if ($value != '') $query->where('bar_code', $value);
    }
}
