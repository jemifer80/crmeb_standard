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
namespace app\controller\admin\v1\marketing\video;

use app\controller\admin\AuthController;
use app\services\activity\video\VideoServices;
use think\facade\App;

/**
 * 短视频控制器
 * Class StoreCategory
 * @package app\admin\controller\v1\marketing\video
 */
class Video extends AuthController
{

    /**
     * Video constructor.
     * @param App $app
     * @param VideoServices $service
     */
    public function __construct(App $app, VideoServices $service)
    {
        parent::__construct($app);
        $this->services = $service;
    }

    /**
     * 分类列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time'],
            ['keyword', ''],
            ['is_verify', '']
        ]);
		$where['is_del'] = 0;
		return $this->success($this->services->sysPage($where));
    }

	/**
 	* 获取视频信息
	* @param $id
	* @return mixed
	 */
	public function info($id)
	{
		if (!$id) return $this->fail('缺少参数');
		return $this->success($this->services->getInfo((int)$id));
	}


    /**
     * 保存新增分类
     * @return mixed
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            ['image', ''],
            ['desc', ''],
            ['video_url', ''],
            ['product_id', []],
            ['is_show', 1],
            ['is_recommend', 0],
            ['sort', 0]
        ]);
		$data['type'] = 0;
		$data['relation_id'] = 0;
		$data['is_verify'] = 1;
		if ($id) {
			$info = $this->services->get($id);
			if (!$info) {
				$this->fail('视频不存在');
			}
			$this->services->update($id, $data);
		} else {
			$data['add_time'] = time();
			$this->services->save($data);
		}
        return $this->success('添加视频成功!');
    }

	/**
     * 修改状态
     * @param string $is_show
     * @param string $id
     */
    public function set_show($id = '', $status = '')
    {
        if ($status == '' || $id == '') return $this->fail('缺少参数');
        $this->services->update($id, ['is_show' => $status]);
        return $this->success($status == 1 ? '显示成功' : '隐藏成功');
    }


	/**
	* 审核
	* @param $id
	* @param $verify
	* @return mixed
	 */
	public function verify($id, $verify)
	{
		if ($verify == '' || $id == '') return $this->fail('缺少参数');
		$info = $this->services->get($id);
		if (!$info) {
			$this->fail('视频不存在');
		}
		if ($verify == 1) {
			$verify = 1;
		} else {//拒绝通过
			$verify = -1;
		}
        $this->services->update($id, ['is_verify' => $verify]);
        return $this->success('审核成功');
	}

	/**
	* 推荐
	* @param $id
	* @param $recommend
	* @return mixed
	 */
	public function recommend($id, $recommend)
	{
		if ($recommend == '' || $id == '') return $this->fail('缺少参数');
		$info = $this->services->get($id);
		if (!$info) {
			$this->fail('视频不存在');
		}
        $this->services->update($id, ['is_recommend' => $recommend]);
        return $this->success($recommend == 1 ? '推荐成功' : '取消推荐');
	}

	/**
	* 强制下架
	* @param $id
	* @param $recommend
	* @return mixed
	 */
	public function takeDown($id)
	{
		if ($id == '') return $this->fail('缺少参数');
		$info = $this->services->get($id);
		if (!$info) {
			$this->fail('视频不存在');
		}
		$this->services->update($id, ['is_verify' => -2, 'is_show' => 0, 'is_recommend' => 0]);
        return $this->success('下架成功');
	}

    /**
     * 删除视频
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if ($id == '') return $this->fail('缺少参数');
		$info = $this->services->get($id);
		if ($info) {
			$this->services->update($id, ['is_del' => 1]);
		}
        return $this->success('删除成功!');
    }
}
