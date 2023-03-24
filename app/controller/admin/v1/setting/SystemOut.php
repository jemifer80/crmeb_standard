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
use app\services\out\OutAccountServices;
use app\services\out\OutInterfaceServices;
use think\facade\App;


/**
 * 对外接口账户
 * Class SystemCity
 * @package app\controller\admin\v1\setting
 */
class SystemOut extends AuthController
{
    /**
     * 构造方法
     * SystemCity constructor.
     * @param App $app
     * @param OutAccountServices $services
     */
    public function __construct(App $app, OutAccountServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 账号信息
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['name', '', ''],
            ['status', ''],
        ]);
        return $this->success($this->services->getList($where));
    }

    /**
     * 修改状态
     * @param string $status
     * @param string $id
     * @return mixed
     */
    public function set_status($id = '', $status = '')
    {
        if ($status == '' || $id == '') return $this->fail('缺少参数');
        $this->services->update($id, ['status' => $status]);
        return $this->success($status == 1 ? '开启成功' : '禁用成功');
    }

    /**
     * 删除
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if ($id == '') return $this->fail('缺少参数');
        $this->services->update($id, ['is_del' => 1]);
        return $this->success('删除成功!');
    }

    public function info($id)
    {
        return $this->success($this->services->getOne(['id' => $id]));
    }

    /**
     * 添加保存
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            [['appid', 's'], ''],
            [['appsecret', 's'], ''],
            [['title', 's'], ''],
            ['rules', []],
        ]);
        if (!$data['appid']) {
            return $this->fail('参数错误');
        }
        if ($this->services->getOne(['appid' => $data['appid'], 'is_del' => 0])) return $this->fail('账号重复');
        if (!$data['appsecret']) {
            unset($data['appsecret']);
        } else {
            $data['appsecret'] = password_hash($data['appsecret'], PASSWORD_DEFAULT);
        }
        $data['rules'] = json_encode($data['rules']);
        $data['add_time'] = time();
        if (!$this->services->save($data)) {
            return $this->fail('添加失败');
        } else {
            return $this->success('添加成功');
        }
    }

    /**
     * 修改保存
     * @param string $id
     * @return mixed
     */
    public function update($id = '')
    {
        $data = $this->request->postMore([
            [['appid', 's'], ''],
            [['appsecret', 's'], ''],
            [['title', 's'], ''],
            ['rules', []],
        ]);
        if (!$data['appid']) {
            return $this->fail('参数错误');
        }
        if (!$data['appsecret']) {
            unset($data['appsecret']);
        } else {
            $data['appsecret'] = password_hash($data['appsecret'], PASSWORD_DEFAULT);
        }
        if (!$this->services->getOne(['id' => $id])) return $this->fail('没有此账号');
        $data['rules'] = json_encode($data['rules']);

        $res = $this->services->update($id, $data);
        if (!$res) {
            return $this->fail('修改失败');
        } else {
            return $this->success('修改成功!');
        }
    }

    /**
     * 对外接口权限
     * @param OutInterfaceServices $service
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function outInterfaceList(OutInterfaceServices $service)
    {
        return $this->success($service->outInterfaceList());
    }

    /**
     * 设置推送接口
     * @param $id
     * @return mixed
     */
    public function outSetUpSave($id)
    {
        $data = $this->request->postMore([
            ['push_open', 0],
            ['push_account', ''],
            ['push_password', ''],
            ['push_token_url', ''],
            ['user_update_push', ''],
            ['order_create_push', ''],
            ['order_pay_push', ''],
            ['refund_create_push', ''],
            ['refund_cancel_push', ''],
        ]);
        $this->services->outSetUpSave($id, $data);
        return $this->success('设置成功');
    }

    /**
     * 测试获取token接口
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function textOutUrl()
    {
        $data = $this->request->postMore([
            ['push_account', 0],
            ['push_password', 0],
            ['push_token_url', '']
        ]);
        return $this->success('测试成功',$this->services->textOutUrl($data));
    }

    /**
     * 对外接口文档
     * @param $id
     * @param OutInterfaceServices $service
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function interfaceInfo($id, OutInterfaceServices $service)
    {
        return app('json')->success($service->interfaceInfo($id));
    }

    /**
     * 保存接口文档
     * @param $id
     * @param OutInterfaceServices $service
     * @return mixed
     */
    public function saveInterface($id, OutInterfaceServices $service)
    {
        $data = $this->request->postMore([
            ['pid', 0], //上级id
            ['type', 0], //类型 0菜单 1接口
            ['name', ''], //名称
            ['describe', ''], //说明
            ['method', ''], //方法
            ['url', ''], //链接地址
            ['request_params', []], //请求参数
            ['return_params', []], //返回参数
            ['request_example', ''], //请求示例
            ['return_example', ''], //返回示例
            ['error_code', []] //错误码
        ]);
        $service->saveInterface((int)$id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 修改接口名称
     * @param OutInterfaceServices $service
     * @return mixed
     */
    public function editInterfaceName(OutInterfaceServices $service)
    {
        $data = $this->request->postMore([
            ['id', 0], //上级id
            ['name', ''], //名称
        ]);
        if (!$data['id'] || !$data['name']) {
            return app('json')->success('参数错误');
        }
        $service->editInterfaceName($data);
        return app('json')->success('修改成功');
    }

    /**
     * 删除接口
     * @param $id
     * @param OutInterfaceServices $service
     * @return mixed
     */
    public function delInterface($id, OutInterfaceServices $service)
    {
        if (!$id) return app('json')->success('参数错误');
        $service->delInterface($id);
        return app('json')->success('删除成功');
    }
}
