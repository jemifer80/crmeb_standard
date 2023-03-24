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

namespace app\controller\admin\v1\marketing\activityBackground;

use app\controller\admin\AuthController;
use app\services\activity\activityBackground\ActivityBackgroundServices;
use think\facade\App;

/**
 * 活动背景图
 * Class ActivityBackground
 * @package app\controller\admin\v1\marketing\activityBackground
 */
class ActivityBackground extends AuthController
{

    /**
     * ActivityBackground constructor.
     * @param App $app
     * @param ActivityBackgroundServices $services
     */
    public function __construct(App $app, ActivityBackgroundServices $services)
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
			['time', '', '', 'activity_time'],
            [['status', 's'], '', '', 'start_status'],
            [['name', 's'], ''],
            ['create_time', '', '', 'time']
        ]);
        $where['promotions_type'] = 6;
        $where['type'] = 1;
        $where['store_id'] = 0;
        $where['pid'] = 0;
        $where['is_del'] = 0;
        return $this->success($this->services->systemPage($where));
    }

    /**
     * 详情
     * @param $id
     * @return mixed
     */
    public function getInfo($id)
    {
        $info = $this->services->getInfo((int)$id);
        return $this->success(compact('info'));
    }

    /**
     * 保存促销活动
     * @param $id
     * @return mixed
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            [['name', 's'], ''],//名称
            ['image', ''],//活动图
            [['product_partake_type', 'd'], 1],//商品参与类型
            ['product_id', []],//关联商品
            ['brand_id', []],//关联品牌ID
            ['store_label_id', []],//关联商品标签ID
            ['section_time', []],//时间
            ['status', 1],//状态
            [['sort', 'd'], 0],//排序
        ]);
        $data['promotions_type'] = 6;
		if (!$data['name']) {
			return $this->fail('请输入活动名称');
		}
		if (!$data['image']) {
			return $this->fail('请选择活动图');
		}
		if ($data['product_partake_type'] == 2 && !$data['product_id']) {
			return $this->fail('请选择要参与活动的商品');
		}
		if ($data['product_partake_type'] == 4 && !$data['brand_id']) {
			return $this->fail('请选择要参与活动的商品品牌');
		}
		if ($data['product_partake_type'] == 5 && !$data['store_label_id']) {
			return $this->fail('请选择要参与活动的商品标签');
		}
        $this->services->saveData((int)$id, $data);
        return $this->success('保存成功');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('缺少参数');
        $this->services->update(['id|pid' => $id], ['is_del' => 1]);
        return $this->success('删除成功!');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function setStatus($id, $status)
    {
        $this->services->update($id, ['status' => $status, 'update_time' => time()]);
        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }

}
