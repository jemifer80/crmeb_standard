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

use app\controller\admin\AuthController;
use app\Request;
use app\services\store\SystemStoreServices;
use app\services\system\config\SystemConfigServices;
use app\services\system\config\SystemConfigTabServices;
use crmeb\services\CacheService;
use think\facade\App;
use think\facade\Cache;

/**
 * 系统配置
 * Class SystemConfig
 * @package app\controller\admin\v1\setting
 */
class SystemConfig extends AuthController
{

    /**
     * SystemConfig constructor.
     * @param App $app
     * @param SystemConfigServices $services
     */
    public function __construct(App $app, SystemConfigServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['tab_id', 0],
            ['status', -1]
        ]);
        if (!$where['tab_id']) {
            return $this->fail('参数错误');
        }
        if ($where['status'] == -1) {
            unset($where['status']);
        }
        $where['is_store'] = 0;
        return $this->success($this->services->getConfigList($where));
    }

    /**
     * 显示创建资源表单页.
     * @param $type
     * @return \think\Response
     */
    public function create()
    {
        [$type, $tabId] = $this->request->getMore([
            [['type', 'd'], ''],
            [['tab_id', 'd'], 1]
        ], true);
        return $this->success($this->services->createFormRule($type, $tabId));
    }

    /**
     * 保存新建的资源
     *
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['menu_name', ''],
            ['type', ''],
            ['input_type', ''],
            ['config_tab_id', []],
            ['parameter', ''],
            ['upload_type', 1],
            ['required', ''],
            ['width', 0],
            ['high', 0],
            ['value', ''],
            ['info', ''],
            ['desc', ''],
            ['sort', 0],
            ['status', 0],
            ['is_store', 0],
        ]);
        if ($data['config_tab_id']) $data['config_tab_id'] = end($data['config_tab_id']);
        if (!$data['info']) return $this->fail('请输入配置名称');
        if (!$data['menu_name']) return $this->fail('请输入字段名称');
        if (!$data['desc']) return $this->fail('请输入配置简介');
        if ($data['sort'] < 0) {
            $data['sort'] = 0;
        }
        if ($data['type'] == 'text') {
            if (!$data['width']) return $this->fail('请输入文本框的宽度');
            if ($data['width'] <= 0) return $this->fail('请输入正确的文本框的宽度');
        }
        if ($data['type'] == 'textarea') {
            if (!$data['width']) return $this->fail('请输入多行文本框的宽度');
            if (!$data['high']) return $this->fail('请输入多行文本框的高度');
            if ($data['width'] < 0) return $this->fail('请输入正确的多行文本框的宽度');
            if ($data['high'] < 0) return $this->fail('请输入正确的多行文本框的宽度');
        }
        if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
            if (!$data['parameter']) return $this->fail('请输入配置参数');
            $this->services->valiDateRadioAndCheckbox($data);
        }
        $data['value'] = json_encode($data['value']);
        $config = $this->services->getOne(['menu_name' => $data['menu_name']]);
        if ($config) {
            $this->services->update($config['id'], $data, 'id');
        } else {
            $this->services->save($data);
        }
        event('config.create', [$data]);
        return $this->success('添加配置成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (!$id) {
            return $this->fail('参数错误，请重新打开');
        }
        $info = $this->services->getReadList((int)$id);
        return $this->success(compact('info'));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        return $this->success($this->services->editConfigForm((int)$id));
    }

    /**
     * 保存更新的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function update($id)
    {
        $type = request()->post('type');
        if ($type == 'text' || $type == 'textarea' || $type == 'radio' || ($type == 'upload' && (request()->post('upload_type') == 1 || request()->post('upload_type') == 3))) {
            $value = request()->post('value');
        } else {
            $value = request()->post('value/a');
        }
        if (!$value) $value = request()->post(request()->post('menu_name'));
        $data = $this->request->postMore([['is_store', 0], 'status', 'info', 'desc', 'sort', 'config_tab_id', 'required', 'parameter', ['value', $value], 'upload_type', 'input_type']);
        $data['config_tab_id'] = end($data['config_tab_id']);
        if (!$this->services->get($id)) {
            return $this->fail('编辑的记录不存在!');
        }
        $data['value'] = json_encode($data['value']);
        $this->services->update($id, $data);
        \crmeb\services\SystemConfigService::clear();
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
            event('config.delete', [$id]);
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
        if ($status == '' || $id == 0) {
            return $this->fail('参数错误');
        }
        $this->services->update($id, ['status' => $status]);
        event('config.status', [$id, $status]);
        return $this->success($status == 0 ? '隐藏成功' : '显示成功');
    }

    /**
     * 基础配置
     * */
    public function edit_basics(Request $request)
    {
        $tabId = $this->request->param('tab_id', 1);
        if (!$tabId) {
            return $this->fail('参数错误');
        }
        $url = $request->baseUrl();
        return $this->success($this->services->getConfigForm($url, $tabId));
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getNewFormBuild(string $type)
    {
        return $this->success($this->services->getNewFormBuildRule($type));
    }

    /**
     * 获取用户配置
     * @return mixed
     */
    public function getUserConfig($type)
    {
        return $this->success($this->services->getUserConfig($type));
    }

    /**
     * 保存用户配置
     * @param $type
     * @return mixed
     */
    public function saveUserConfig($type)
    {
        $post = $this->request->post();
        $this->validate($post, \app\validate\admin\setting\SystemConfigValidata::class);
        $this->services->saveUserConfig($type, $post);
        return $this->success('保存成功');
    }

    /**
     * 获取缩略图配置
     * @return mixed
     */
    public function getImageConfig()
    {
        return $this->success($this->services->getImageConfig());
    }

    /**
     * 保存数据    true
     * */
    public function save_basics(Request $request)
    {
        $isStore = $this->request->get('is_store', false);
        $post = $this->request->post();
        if ($isStore && isset($post['store_self_mention'])) {//门店配置
            $baseData = [
                'name' => '',
                'phone' => '',
                'address' => '',
                'detailed_address' => '',
                'day_time' => '',
                'latlng' => '',
            ];
            if ($post['store_self_mention']) {
                $data = array_merge($baseData, array_intersect_key($post, $baseData));
                if (!$data['name']) {
                    return $this->fail('请输入提货点名称');
                }
                if (!$data['address']) {
                    return $this->fail('请选择提货点地址');
                }
                $address = $data['address'];
                $data['address'] = implode('', array_column($address, 'label'));
                $ids = array_column($address, 'id');
                $data['province'] = $ids[0] ?? 0;
                $data['city'] = $ids[1] ?? 0;
                $data['area'] = $ids[2] ?? 0;
                if (!$data['latlng']) {
                    return $this->fail('请给提货点定位获取经纬度');
                }
                [$data['longitude'], $data['latitude']] = explode(',', $data['latlng']);
                unset($data['latlng']);
                if (!isset($data['longitude']) || !isset($data['latitude'])) {
                    return $this->fail('请选择提货点位置');
                }
                if (!check_phone($data['phone'])) {
                    return $this->fail('请输入正确的手机号');
                }
                if (!$data['day_time'] || count($data['day_time']) != 2) {
                    return $this->fail('请选择提货点营业时间');
                }
                if ($data['day_time'] && count($data['day_time']) == 2) {
                    [$data['day_start'], $data['day_end']] = $data['day_time'];
                    $data['day_time'] = implode(' - ', $data['day_time']);
                }
                $data['address'] = str_replace([' ', '/', '\\'], '', $data['address']);
                $data['detailed_address'] = str_replace([' ', '/', '\\'], '', $data['detailed_address']);
                $data['is_show'] = 1;
                $data['is_store'] = 1;
                /** @var SystemStoreServices $storeServices */
                $storeServices = app()->make(SystemStoreServices::class);
                $storeServices->saveStore(0, $data);
            }
            foreach ($baseData as $key => $value) {
                unset($post[$key]);
            }
        }
        foreach ($post as $k => $v) {
            if (is_array($v)) {
                $res = $this->services->getUploadTypeList($k);
                foreach ($res as $kk => $vv) {
                    if ($kk == 'upload') {
                        if ($vv == 1 || $vv == 3) {
                            $post[$k] = $v[0];
                        }
                    }
                }
            }
        }
        if (!empty($post['links_list'])) {
            foreach ($post['links_list'] as $item) {
                foreach ($item as $v => $k) {
                    if ('' === $item[$v]) {
                        return $this->fail('友情链接内容不能为空');
                    }
                }
            }
        }
        if (!empty($post['filing_list'])) {
            foreach ($post['filing_list'] as $item) {
                foreach ($item as $v => $k) {
                    if ('' === $item[$v]) {
                        return $this->fail('PC底部自定义展示内容不能为空');
                    }
                }
            }
        }
        $this->validate($post, \app\validate\admin\setting\SystemConfigValidata::class);
        if (isset($post['upload_type'])) {
            $this->services->checkThumbParam($post);
        }
        if (isset($post['store_brokerage_binding_status'])) {
            $this->services->checkBrokerageBinding($post);
        }
        if (isset($post['store_brokerage_ratio']) && isset($post['store_brokerage_two'])) {
            $num = $post['store_brokerage_ratio'] + $post['store_brokerage_two'];
            if ($num > 100) {
                return $this->fail('一二级返佣比例不能大于100%');
            }
        }
        if (isset($post['spread_banner'])) {
            $num = count($post['spread_banner']);
            if ($num > 5) {
                return $this->fail('分销海报不能多于5张');
            }
        }
        if (isset($post['user_extract_min_price'])) {
            if (!preg_match('/[0-9]$/', $post['user_extract_min_price'])) {
                return $this->fail('提现最低金额只能为数字!');
            }
        }
        if (isset($post['store_extract_max_price']) && isset($post['store_extract_min_price'])) {
            if ($post['store_extract_max_price'] < $post['store_extract_min_price']) {
                return $this->fail('门店提现最低金额不能大于最高金额');
            }
        }
        //小程序支付
        if (isset($post['pay_routine_open']) && $post['pay_routine_open']) {
            if (empty($post['pay_routine_mchid'])) {
                return $this->fail('小程序商户号不能为空');
            }
        }
        //全场包邮开关
        if (isset($post['whole_free_shipping'])) {
            $wholeFreeShipping = (int)$post['whole_free_shipping'];
            if (!$wholeFreeShipping) {
                $post['store_free_postage'] = 0;
            }
            unset($post['whole_free_shipping']);
        }
        foreach ($post as $k => $v) {
            $config_one = $this->services->getOne(['menu_name' => $k]);
            if ($config_one) {
                $config_one['value'] = $v;
                $this->services->valiDateValue($config_one);
                $this->services->update($k, ['value' => json_encode($v)], 'menu_name');
            }
        }

        //记录缓存时间
        if (!empty($post['cache_config'])) {
            Cache::store('redis')->set(CacheService::CACHE_EXPIRE_NAME, $post['cache_config']);
        }

        \crmeb\services\SystemConfigService::clear();
        return $this->success('修改成功');
    }

    /**
     * 获取系统设置头部分类
     * @param SystemConfigTabServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function header_basics(SystemConfigTabServices $services)
    {
        [$type, $pid] = $this->request->getMore([
            [['type', 'd'], 0],
            [['pid', 'd'], 0]
        ], true);
        if ($type == 3) {//其它分类
            $config_tab = [];
        } else {
            $config_tab = $services->getConfigTab($pid);
        }
        return $this->success(compact('config_tab'));
    }

    /**
     * 获取单个配置的值
     * @param $name
     * @return mixed
     */
    public function get_system($name)
    {
        $value = sys_config($name);
        return $this->success(compact('value'));
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        $version = get_crmeb_version();

        return $this->success([
            'version' => $version,
            'label' => strripos($version, 'min') === false ? 3 : 2
        ]);
    }

}
