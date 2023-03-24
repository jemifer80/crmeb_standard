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
use app\services\system\attachment\SystemAttachmentServices;
use think\facade\App;

/**
 * 图片管理类
 * Class SystemAttachment
 * @package app\controller\admin\v1\file
 */
class SystemAttachment extends AuthController
{
    protected $service;

    public function __construct(App $app, SystemAttachmentServices $service)
    {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 显示列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['pid', 0],
            ['file_type', 1]
        ]);
        $where['type'] = 1;
        return $this->success($this->service->getImageList($where));
    }

    /**
     * 删除指定资源
     *
     * @param string $ids
     * @return \think\Response
     */
    public function delete()
    {
        [$ids] = $this->request->postMore([
            ['ids', '']
        ], true);
        $this->service->del($ids);
        return $this->success('删除成功');
    }

    /**
     * 图片上传
     * @param int $upload_type
     * @param int $type
     * @return mixed
     */
    public function upload($upload_type = 0, $type = 0)
    {
        [$pid, $file] = $this->request->postMore([
            ['pid', 0],
            ['file', 'file'],
        ], true);
        $res = $this->service->upload((int)$pid, $file, (int)$upload_type, (int)$type);
        return $this->success('上传成功', ['src' => $res]);
    }

    /**
     * 移动图片
     * @return mixed
     */
    public function moveImageCate()
    {
        $data = $this->request->postMore([
            ['pid', 0],
            ['images', '']
        ]);
        $this->service->move($data);
        return $this->success('移动成功');
    }

    /**
     * 修改文件名
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $realName = $this->request->post('real_name', '');
        if (!$realName) {
            return $this->fail('文件名称不能为空');
        }
        $this->service->update($id, ['real_name' => $realName]);
        return $this->success('修改成功');
    }

    /**
     * 获取上传类型
     * @return mixed
     */
    public function uploadType()
    {
        $data['upload_type'] = (string)sys_config('upload_type', 1);
        return app('json')->success($data);
    }

    /**
     * 视频分片上传
     * @return mixed
     */
    public function videoUpload()
    {
        $data = $this->request->postMore([
            ['chunkNumber', 0],//第几分片
            ['currentChunkSize', 0],//分片大小
            ['chunkSize', 0],//总大小
            ['totalChunks', 0],//分片总数
            ['file', 'file'],//文件
            ['md5', ''],//MD5
            ['filename', ''],//文件名称
            ['pid', 0],//分类ID
        ]);
        $fileHandle = $this->request->file($data['file']);
        if (!$fileHandle) return $this->fail('上传信息为空');
        $res = $this->service->videoUpload($data, $fileHandle);
        return app('json')->success($res);
    }

	/**
 	* 保存云端视频记录
	* @return mixed
	*/
	public function saveVideoAttachment()
	{
		$data = $this->request->postMore([
            ['path', ''],//视频地址
            ['cover_image', ''],//封面地址
			['pid', 0],//分类ID
			['upload_type', 1],//上传类型
        ]);
		 $res = $this->service->saveOssVideoAttachment($data, 1, 0, (int)$data['upload_type']);
        return app('json')->success($res);
	}
}
