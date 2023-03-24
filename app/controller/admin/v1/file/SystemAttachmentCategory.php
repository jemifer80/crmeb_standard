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
namespace app\controller\admin\v1\file;

use app\controller\admin\AuthController;
use app\services\system\attachment\SystemAttachmentCategoryServices;
use think\facade\App;

/**
 * 图片分类管理类
 * Class SystemAttachmentCategory
 * @package app\controller\admin\v1\file
 */
class SystemAttachmentCategory extends AuthController
{

    protected $service;

    public function __construct(App $app, SystemAttachmentCategoryServices $service)
    {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['name', ''],
            ['pid', 0],
            ['file_type', 1]
        ]);
        $where['type'] = 1;
        if ($where['name'] != '') $where['pid'] = '';
        return $this->success($this->service->getAll($where));
    }

    /**
     * 新增表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create($id)
    {
		[$file_type] = $this->request->postMore([
            ['file_type', 1]
        ], true);
        return $this->success($this->service->createForm($id, 1, 0, $file_type));
    }

    /**
     * 保存新增
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['pid', 0],
            ['name', ''],
            ['file_type', 1]
        ]);
        if (!$data['name']) {
            return $this->fail('请输入分类名称');
        }
        $data['type'] = 1;
        $this->service->save($data);
        return $this->success('添加成功');
    }

    /**
     * 编辑表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        return $this->success($this->service->editForm($id));
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['pid', 0],
            ['name', ''],
            ['file_type', 1]
        ]);
        if (!$data['name']) {
            return $this->fail('请输入分类名称');
        }
        $info = $this->service->get($id);
        $count = $this->service->count(['pid' => $id]);
        if ($count && $info['pid'] != $data['pid']) return $this->fail('该分类有下级分类，无法修改上级');
        $this->service->update($id, $data);
        return $this->success('分类编辑成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $this->service->del($id);
        return $this->success('删除成功!');
    }
}
