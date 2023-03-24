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
namespace app\controller\admin\v1\system;

use think\facade\App;
use app\controller\admin\AuthController;
use app\services\system\timer\SystemTimerServices;

/**
 * 定时任务表控制器
 * Class SystemTimer
 * @package app\controller\admin\v1\system
 */
class SystemTimer extends AuthController
{

    protected $services;

    /**
     * 构造方法
     * SystemTimer constructor.
     * @param App $app
     * @param SystemTimerServices $services
     */
    public function __construct(App $app, SystemTimerServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**定时任务名称及标识
     * @return mixed
     */
    public function task_name()
    {
        $data = [
            'auto_cancel' => '自动取消订单',
            'auto_take' => '自动确认收货',
            'auto_comment' => '自动好评',
            'auto_clear_integral' => '自动清空用户积分',
            'auto_off_user_svip' => '自动取消用户到期svip',
            'auto_agent' => '自动解绑上下级',
            'auto_clear_poster' => '自动清除昨日海报',
            'auto_sms_code' => '更新短信状态',
            'auto_live' => '自动更新直播产品状态和直播间状态',
            'auto_pink' => '拼团状态自动更新',
            'auto_show' => '自动上下架商品',
            'auto_channel' => '渠道码定时任务',
            'auto_moment' => '定时创建发送朋友圈任务',
            'auto_group_task' => '定时发送群发任务',
            'auto_seckill' => '定时清理秒杀数据过期的数据缓存'
        ];
        return $this->success($data);
    }

    /**
     * 显示列表
     * @return mixed
     */
    public function index()
    {
        $where['is_del'] = 0;
        return $this->success($this->services->getTimerList($where));
    }

    /**
     * 删除指定资源
     * @param string $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $this->services->del($id);
        $this->services->delOneTimerCache($id);
        return $this->success('删除成功');
    }

    /**
     * 修改状态
     * @param string $is_show
     * @param string $id
     */
    public function set_show($id = '', $is_show = '')
    {
        if ($is_show == '' || $id == '') return $this->fail('缺少参数');
        $this->services->setShow($id, $is_show);
        $this->services->updateOneTimerCache($id);
        return $this->success($is_show == 1 ? '显示成功' : '隐藏成功');
    }

    /**获取单条定时器数据
     * @param $id
     * @return void
     */
    public function get_timer_one($id)
    {
        return $this->success($this->services->getOneTimer($id));
    }

    /**
     * 保存定时任务
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['mark', ''],
            ['type', 0],
            ['title', ''],
            ['is_open', 0],
            ['cycle', '']
        ]);
        if (!$data['name']) {
            return $this->fail('请输入定时任务名称');
        }
        if (!$data['mark']) {
            return $this->fail('请输入定时任务标识');
        }
        $this->services->createData($data);
        $this->services->setAllTimerCache();
        return $this->success('添加定时器成功!');
    }

    /**
     * 更新定时任务
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['mark', ''],
            ['type', 0],
            ['title', ''],
            ['is_open', 0],
            ['cycle', '']
        ]);
        if (!$data['name']) {
            return $this->fail('请输入定时任务名称');
        }
        if (!$data['mark']) {
            return $this->fail('请输入定时任务标识');
        }
        $this->services->editData($id, $data);
        $this->services->updateOneTimerCache($id);
        return $this->success('修改成功!');
    }
}

