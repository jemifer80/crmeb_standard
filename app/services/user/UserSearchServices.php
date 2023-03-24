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

namespace app\services\user;

use app\services\BaseServices;
use app\services\product\product\StoreProductServices;
use app\dao\user\UserSearchDao;

/**
 *
 * Class UserLabelServices
 * @package app\services\user
 * @mixin UserSearchDao
 */
class UserSearchServices extends BaseServices
{

    /**
     * UserSearchServices constructor.
     * @param UserSearchDao $dao
     */
    public function __construct(UserSearchDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 分词搜索得到ids
     * @param int $uid
     * @param string $keyword
     * @param array $where
     * @return array
     */  
    public function vicSearch(int $uid, string $keyword, array $where)
    {
        //分词
        try {
            $vicWordArr = \crmeb\services\VicWordService::instance()->getWord($keyword);
        } catch (\Throwable $e) {
            $vicWordArr = [];
        }
        if ($vicWordArr) {
			$vicword = $vicWordArr;
			$where['keyword'] = $vicWordArr;
        }
        $result = $this->dao->getKeywordResult(0, $keyword);
        $ids = [];
        if ($result && isset($result['result']) && $result['result']) {//之前查询结果记录
            $ids = $result['result'];
        } else {//分词查询

        }
        //搜索没有记录
        if (!$ids && $where) {
            //查出所有结果ids存搜索记录表
            /** @var StoreProductServices $services */
            $services = app()->make(StoreProductServices::class);
            $idsArr = $services->getSearchList($where, 0, 0, ['id']);
            if ($idsArr) {
                $ids = array_column($idsArr, 'id');
            }
        }
        $vicword = is_string($vicword) ? [$vicword] : $vicword;
        $this->saveUserSearch($uid, $keyword, $vicword, $ids);
        return $ids;
    }


    /**
     * 获取用户搜索关键词列表
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserList(int $uid)
    {
        if (!$uid) {
            return [];
        }
        [$page, $limit] = $this->getPageValue();
        return $this->dao->getList(['uid' => $uid, 'is_del' => 0], 'add_time desc,num desc', $page, $limit);
    }

    /**
     * 用户增加搜索记录
     * @param int $uid
     * @param string $key
     * @param array $result
     */
    public function saveUserSearch(int $uid, string $keyword, array $vicword, array $result)
    {
        $result = json_encode($result);
        $vicword = json_encode($vicword, JSON_UNESCAPED_UNICODE);
        $userkeyword = $this->dao->getKeywordResult($uid, $keyword, 0);
        $data = [];
        $data['result'] = $result;
        $data['vicword'] = $vicword;
        $data['add_time'] = time();
        if ($userkeyword) {
            $data['num'] = $userkeyword['num'] + 1;
            $this->dao->update(['id' => $userkeyword['id']], $data);
        } else {
            $data['uid'] = $uid;
            $data['keyword'] = $keyword;
            $this->dao->save($data);
        }
        return true;
    }

}
