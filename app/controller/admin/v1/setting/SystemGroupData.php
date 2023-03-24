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
namespace app\controller\admin\v1\setting;

use crmeb\exceptions\AdminException;
use app\services\other\CacheServices;
use think\facade\App;
use app\controller\admin\AuthController;
use app\services\system\config\SystemGroupDataServices;
use app\services\system\config\SystemGroupServices;

/**
 * 数据管理
 * Class SystemGroupData
 * @package app\controller\admin\v1\setting
 */
class SystemGroupData extends AuthController
{
    /**
     * 构造方法
     * SystemGroupData constructor.
     * @param App $app
     * @param SystemGroupDataServices $services
     */
    public function __construct(App $app, SystemGroupDataServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取数据列表头
     * @return mixed
     */
    public function header(SystemGroupServices $services)
    {
        [$gid, $config_name] = $this->request->getMore([
            ['gid', 0],
            ['config_name', '']
        ], true);
        if (!$gid && !$config_name) return $this->fail('参数错误');
        if (!$gid) {
            $gid = $services->value(['config_name' => $config_name], 'id');
        }
        return $this->success($services->getGroupDataTabHeader($gid));
    }

    /**
     * 显示资源列表
     *
     * @param SystemGroupServices $group
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(SystemGroupServices $group)
    {
        $where = $this->request->getMore([
            ['gid', 0],
            ['status', ''],
            ['config_name', '']
        ]);
        if (!$where['gid'] && !$where['config_name']) return $this->fail('参数错误');
        if (!$where['gid']) {
            $where['gid'] = $group->value(['config_name' => $where['config_name']], 'id');
        }
        unset($where['config_name']);
        return $this->success($this->services->getGroupDataList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        $gid = $this->request->param('gid/d');
        if ($this->services->isGroupGidSave($gid, 4, 'index_categy_images')) {
            return $this->fail('不能大于四个！');
        }
        if ($this->services->isGroupGidSave($gid, 7, 'sign_day_num')) {
            return $this->fail('签到天数配置不能大于7天');
        }
        return $this->success($this->services->createForm($gid));
    }

    /**
     * 保存新建的资源
     *
     * @param SystemGroupServices $services
     * @return \think\Response
     */
    public function save(SystemGroupServices $services)
    {
        $params = request()->post();
        $gid = (int)$params['gid'];
        $group = $services->getOne(['id' => $gid], 'id,config_name,fields');
        if ($group && $group['config_name'] == 'order_details_images') {
            $groupDatas = $this->services->getColumn(['gid' => $gid], 'value', 'id');
            foreach ($groupDatas as $groupData) {
                $groupData = json_decode($groupData, true);
                if (isset($groupData['order_status']['value']) && $groupData['order_status']['value'] == $params['order_status']) {
                    return $this->fail('已存在请不要重复添加');
                }
            }
        }
        $this->checkSeckillTime($services, $gid, $params);
        $this->checkSign($services, $gid, $params);
        $this->checkRecharge($services, $gid, $params);
        $fields = json_decode($group['fields'], true) ?? [];
        $value = [];
        foreach ($params as $key => $param) {
            foreach ($fields as $index => $field) {
                if ($key == $field["title"]) {
                    if ($key != 'give_money' && $param == "")
                        return $this->fail($field["name"] . "不能为空！");
                    else {
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $key == 'give_money' && $param == "" ? 0 : $param;
                    }
                }
            }
        }
        $data = [
            "gid" => $params['gid'],
            "add_time" => time(),
            "value" => json_encode($value),
            "sort" => $params["sort"] ?: 0,
            "status" => $params["status"]
        ];
        $res = $this->services->save($data);
        $data['id'] = $res->id;
        $this->services->cacheUpdate($data);
        \crmeb\services\CacheService::clear();
        return $this->success('添加数据成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $gid = $this->request->param('gid/d');
        if (!$gid) {
            return $this->fail('缺少参数');
        }
        return $this->success($this->services->updateForm((int)$gid, (int)$id));
    }

    public function saveAll()
    {
        $params = request()->post();
        if (!isset($params['config_name']) || !isset($params['data'])) {
            return $this->fail('缺少参数');
        }
        $this->services->saveAllData($params['data'], $params['config_name']);
        return $this->success('添加数据成功!');
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(SystemGroupServices $services, $id)
    {
        $groupData = $this->services->get($id);
        $fields = $services->getValueFields((int)$groupData["gid"]);
        $params = request()->post();
        $this->checkSeckillTime($services, $groupData["gid"], $params, $id);
        $this->checkSign($services, $groupData["gid"], $params);
        $this->checkRecharge($services, $groupData["gid"], $params);
        foreach ($params as $key => $param) {
            foreach ($fields as $index => $field) {
                if ($key == $field["title"]) {
                    if ($key != 'give_money' && $param == '')
                        return $this->fail($field["name"] . "不能为空！");
                    else {
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $key == 'give_money' && $param == "" ? 0 : $param;
                    }
                }
            }
        }
        $data = [
            "value" => json_encode($value),
            "sort" => $params["sort"],
            "status" => $params["status"]
        ];
        $this->services->update($id, $data);
        $this->services->cacheSaveValue($id, $data);
        \crmeb\services\CacheService::clear();
        return $this->success('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$this->services->delete($id))
            return $this->fail('删除失败,请稍候再试!');
        else {
            \crmeb\services\CacheService::clear();
            $this->services->cacheDelById($id);
            return $this->success('删除成功!');
        }
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return $this->fail('参数错误');
        $this->services->update($id, ['status' => $status]);
        \crmeb\services\CacheService::clear();
        if ($status) {
            $this->services->cacheSaveValue($id, 'status', $status);
        } else {
            $this->services->cacheDelById($id);
        }
        return $this->success($status == 0 ? '隐藏成功' : '显示成功');
    }

    /**
     * 检查秒杀时间段
     * @param SystemGroupServices $services
     * @param $gid
     * @param $params
     * @param int $id
     * @return mixed
     */
    public function checkSeckillTime(SystemGroupServices $services, $gid, $params, $id = 0)
    {
        $name = $services->value(['id' => $gid], 'config_name');
        if ($name == 'routine_seckill_time') {
            if ($params['time'] == '') {
                throw new AdminException('请输入开始时间');
            }
            if (!$params['continued']) {
                throw new AdminException('请输入持续时间');
            }
            if (!preg_match('/^(\d|1\d|2[0-3])$/', $params['time'])) {
                throw new AdminException('请输入0-23点之前的整点数');
            }
            if (!preg_match('/^([1-9]|1\d|2[0-4])$/', $params['continued'])) {
                throw new AdminException('请输入1-24点之前的持续时间');
            }
            if (($params['time'] + $params['continued']) > 24) throw new AdminException('开始时间+持续时间不能大于24小时');
            $list = $this->services->getColumn(['gid' => $gid], 'value', 'id');
            if ($id) unset($list[$id]);
            $times = $time = [];
            foreach ($list as $item) {
                $info = json_decode($item, true);
                for ($i = 0; $i < $info['continued']['value']; $i++) {
                    $times[] = $info['time']['value'] + $i;
                }
            }
            for ($i = 0; $i < $params['continued']; $i++) {
                $time[] = $params['time'] + $i;
            }
            foreach ($time as $v) {
                if (in_array($v, $times)) throw new AdminException('时段已占用');
            }
        }
    }

    /**
     * 检查签到配置
     * @param SystemGroupServices $services
     * @param $gid
     * @param $params
     * @param int $id
     * @return mixed
     */
    public function checkSign(SystemGroupServices $services, $gid, $params, $id = 0)
    {
        $name = $services->value(['id' => $gid], 'config_name');
        if ($name == 'sign_day_num') {
            if (!$params['sign_num']) {
                throw new AdminException('请输入签到赠送积分');
            }
            if (!preg_match('/^\+?[1-9]\d*$/', $params['sign_num'])) {
                throw new AdminException('请输入大于等于0的整数');
            }
        }
    }

    /**
     * 检查充值金额配置
     * @param SystemGroupServices $services
     * @param $gid
     * @param $params
     * @param int $id
     * @return mixed
     */
    public function checkRecharge(SystemGroupServices $services, $gid, $params, $id = 0)
    {
        $name = $services->value(['id' => $gid], 'config_name');
        if ($name == 'user_recharge_quota') {
            if (!$params['price']) {
                throw new AdminException('请输入售价');
            }
            if (!preg_match('/^[0-9]{0,8}+(.[0-9]{1,2})?$/', $params['price'])) {
                throw new AdminException('售价限制最大10位浮点型且最多两位小数');
            }
            if ($params['give_money'] && !preg_match('/^[0-9]{0,8}+(.[0-9]{1,2})?$/', $params['give_money'])) {
                throw new AdminException('赠送金额限制最大10位浮点型且最多两位小数');
            }
            if ($this->services->count(['gid' => $gid]) >= 20) {
                throw new AdminException('充值优惠条数最多添加20个');
            }
        }
    }

    /**
     * 获取客服页面广告内容
     * @return mixed
     */
    public function getKfAdv()
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $content = $cache->getDbCache('kf_adv', '');
        return $this->success(compact('content'));
    }

    /**
     * 设置客服页面广告内容
     * @return mixed
     */
    public function setKfAdv()
    {
        $content = $this->request->post('content');
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $cache->setDbCache('kf_adv', $content);
        return $this->success('设置成功');
    }

    /**
     * 获取用户协议内容
     * @return mixed
     */
    public function getUserAgreement($type)
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $content = $cache->getDbCache($type, '');
        return $this->success(compact('content'));
    }

    /**
     * 设置用户协议内容
     * @return mixed
     */
    public function setUserAgreement($type)
    {
        $content = $this->request->post('content');
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $cache->setDbCache($type, $content);
        if ('privacy' === $type) {
            $html = <<<HTML
<!doctype html>
<html class="x-admin-sm">
    <head>
        <meta charset="UTF-8">
        <title>隐私协议</title>
        <meta name="renderer" content="webkit|ie-comp|ie-stand">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
        <meta http-equiv="Cache-Control" content="no-siteapp" />
    </head>
    <body class="index">
    $content
    </body>
</html>
HTML;
            file_put_contents(public_path() . 'protocol.html', $html);
        }
        return $this->success('设置成功');
    }

}
