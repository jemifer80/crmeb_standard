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

namespace app\services\system\attachment;

use app\services\BaseServices;
use app\dao\system\attachment\SystemAttachmentDao;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\UploadException;
use crmeb\services\UploadService;
use crmeb\traits\ServicesTrait;
use think\exception\ValidateException;

/**
 *
 * Class SystemAttachmentServices
 * @package app\services\attachment
 * @mixin SystemAttachmentDao
 */
class SystemAttachmentServices extends BaseServices
{
    use ServicesTrait;

    /**
     * SystemAttachmentServices constructor.
     * @param SystemAttachmentDao $dao
     */
    public function __construct(SystemAttachmentDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取单个资源
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo(array $where, string $field = '*')
    {
        return $this->dao->getOne($where, $field);
    }

    /**
     * 获取图片列表
     * @param array $where
     * @return array
     */
    public function getImageList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        if ($list) {
            $site_url = sys_config('site_url');
            foreach ($list as &$item) {
                if ($item['file_type'] == 1) {
                    if ($site_url) {
                        $item['satt_dir'] = (strpos($item['satt_dir'], $site_url) !== false || strstr($item['satt_dir'], 'http') !== false) ? $item['satt_dir'] : $site_url . $item['satt_dir'];
                        $item['att_dir'] = (strpos($item['att_dir'], $site_url) !== false || strstr($item['att_dir'], 'http') !== false) ? $item['satt_dir'] : $site_url . $item['att_dir'];
                    }
                }
            }
            $list = get_thumb_water($list, 'mid', ['satt_dir']);
        }
        $where['module_type'] = 1;
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 删除图片
     * @param string $ids
     */
    public function del(string $ids)
    {
        $ids = explode(',', $ids);
        if (empty($ids)) throw new AdminException('请选择要删除的图片');
        foreach ($ids as $v) {
            $attinfo = $this->dao->get((int)$v);
            if ($attinfo) {
                try {
                    $upload = UploadService::init($attinfo['image_type']);
                    if ($attinfo['image_type'] == 1) {
                        if (strpos($attinfo['att_dir'], '/') == 0) {
                            $attinfo['att_dir'] = substr($attinfo['att_dir'], 1);
                        }
                        if ($attinfo['att_dir']) $upload->delete(public_path() . $attinfo['att_dir']);
                    } else {
                        if ($attinfo['name']) $upload->delete($attinfo['name']);
                    }
                } catch (\Throwable $e) {
                }
                $this->dao->delete((int)$v);
            }
        }
    }

    /**
     * 图片上传
     * @param int $pid
     * @param string $file
     * @param int $upload_type
     * @param int $type
     * @return mixed
     */
    public function upload(int $pid, string $file, int $upload_type, int $type)
    {
        if ($upload_type == 0) {
            $upload_type = (int)sys_config('upload_type', 1);
        }
        try {
            $path = make_path('attach', 2, true);
            $upload = UploadService::init($upload_type);
            $res = $upload->to($path)->setAuthThumb(false)->validate()->move($file);
            if ($res === false) {
                throw new UploadException($upload->getError());
            } else {
                $fileInfo = $upload->getUploadInfo();
                //保存附件记录
                if ($type == 0) $this->saveAttachment($fileInfo, $pid, $type, 0, 1, $upload_type);
                return $res->filePath;
            }
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * @param array $data
     * @return \crmeb\basic\BaseModel
     */
    public function move(array $data)
    {
        $res = $this->dao->move($data);
        if (!$res) throw new AdminException('移动失败或不能重复移动到同一分类下');
    }

    /**
     * 添加信息
     * @param array $data
     */
    public function save(array $data)
    {
        $this->dao->save($data);
    }

    /**
     *  添加附件记录
     * @param $name
     * @param $att_size
     * @param $att_type
     * @param $att_dir
     * @param string $satt_dir
     * @param int $pid
     * @param int $imageType
     * @param int $time
     * @return SystemAttachment
     */
    public function attachmentAdd($name, $att_size, $att_type, $att_dir, $satt_dir = '', $pid = 0, $imageType = 1, $time = 0, $module_type = 1)
    {
        $data['name'] = $name;
        $data['att_dir'] = $att_dir;
        $data['satt_dir'] = $satt_dir;
        $data['att_size'] = $att_size;
        $data['att_type'] = $att_type;
        $data['image_type'] = $imageType;
        $data['module_type'] = $module_type;
        $data['time'] = $time ? $time : time();
        $data['pid'] = $pid;
        if (!$this->dao->save($data)) {
            throw new ValidateException('添加附件失败');
        }
        return true;
    }

    /**
     * 推广名片生成
     * @param $name
     */
    public function getLikeNameList($name)
    {
        return $this->dao->getLikeNameList(['like_name' => $name], 0, 0);
    }

    /**
     * 清除昨日海报
     * @return bool
     * @throws \Exception
     */
    public function emptyYesterdayAttachment()
    {
        try {
            $list = $this->dao->getYesterday();
            foreach ($list as $key => $item) {
                $upload = UploadService::init((int)$item['image_type']);
                if ($item['image_type'] == 1) {
                    $att_dir = $item['att_dir'];
                    if ($att_dir && strstr($att_dir, 'uploads') !== false) {
                        if (strstr($att_dir, 'http') === false)
                            $upload->delete($att_dir);
                        else {
                            $filedir = substr($att_dir, strpos($att_dir, 'uploads'));
                            if ($filedir) $upload->delete($filedir);
                        }
                    }
                } else {
                    if ($item['name']) $upload->delete($item['name']);
                }
            }
            $this->dao->delYesterday();
            return true;
        } catch (\Exception $e) {
            $this->dao->delYesterday();
            return true;
        }
    }

    /**
     * 门店图片上传
     * @param int $pid
     * @param string $file
     * @param int $relationId
     * @param int $type
     * @return mixed
     */
    public function storeUpload(int $pid, string $file, int $relationId, int $type = 2)
    {
        try {
            if ($type == 4) {
				$upload_type = 0;
			} else {
				$upload_type = (int)sys_config('upload_type', 1);
			}
            $path = make_path('attach/' . ($type == 4 ? 'supplier' : 'store'), 2, true);
            $upload = UploadService::init($upload_type);
            $res = $upload->to($path)->setAuthThumb(false)->validate()->move($file);
            if ($res === false) {
                throw new UploadException($upload->getError());
            } else {
                $fileInfo = $upload->getUploadInfo();
                //保存附件记录
                $this->saveAttachment($fileInfo, $pid, $type, $relationId, 1, $upload_type);
                return $res->filePath;
            }
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * 视频分片上传
     * @param $data
     * @param $file
     * @param int $type
     * @param int $relation_id
     * @return mixed
     */
    public function videoUpload($data, $file, int $type = 1, int $relation_id = 0)
    {
        $public_dir = app()->getRootPath() . 'public';
        $store_dir = '';
        switch ($type) {
            case 2:
                $store_dir = 'store' . DIRECTORY_SEPARATOR;
                break;
            case 4:
                $store_dir = 'supplier' . DIRECTORY_SEPARATOR;
                break;
        }
        $dir = '/uploads/video/' . $store_dir . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
        $all_dir = $public_dir . $dir;
        if (!is_dir($all_dir)) mkdir($all_dir, 0777, true);
        $filename = $all_dir . '/' . $data['filename'] . '__' . $data['chunkNumber'];
        move_uploaded_file($file->getPathName(), $filename);
        $res['code'] = 0;
        $res['msg'] = 'error';
        $res['file_path'] = '';
        if ($data['chunkNumber'] == $data['totalChunks']) {
            $blob = '';
            for ($i = 1; $i <= $data['totalChunks']; $i++) {
                $blob .= file_get_contents($all_dir . '/' . $data['filename'] . '__' . $i);
            }
            file_put_contents($all_dir . '/' . $data['filename'], $blob);
            for ($i = 1; $i <= $data['totalChunks']; $i++) {
                @unlink($all_dir . '/' . $data['filename'] . '__' . $i);
            }
            if (file_exists($all_dir . '/' . $data['filename'])) {
                $res['code'] = 2;
                $res['msg'] = 'success';
                $res['file_path'] = $dir . '/' . $data['filename'];
            }
        } else {
            if (file_exists($all_dir . '/' . $data['filename'] . '__' . $data['chunkNumber'])) {
                $res['code'] = 1;
                $res['msg'] = 'waiting';
                $res['file_path'] = '';
            }
        }
        $res['name'] = $res['dir'] = $res['file_path'];
		if (strpos($res['file_path'], 'http') === false) {
			$res['dir'] = $res['file_path'] = sys_config('site_url') . $res['file_path'];
		}
        if ($res['code'] == 2) {
            $this->saveAttachment($res, (int)($data['pid'] ?? 0), $type, $relation_id, 2);
        }
        return $res;
    }

    /**
     * 云端上传的视频保存记录
     * @param array $data
     * @param int $type
     * @param int $relation_id
     * @param int $upload_type
     * @return bool
     */
    public function saveOssVideoAttachment(array $data, int $type = 1, int $relation_id = 0, int $upload_type = 1)
    {
        $fileInfo = [];
        $fileInfo['name'] = $fileInfo['real_name'] = $data['path'];
        $fileInfo['cover_image'] = $data['cover_image'];
        if (!$fileInfo['cover_image'] && $upload_type != 1) {//云端视频
            $res = UploadService::init($upload_type)->videoCoverImage($data['path'], 'mid');
            $fileInfo['cover_image'] = $res['mid'] ?? '';
        }
        $this->saveAttachment($fileInfo, (int)$data['pid'], $type, $relation_id, 2, $upload_type);
        return true;
    }

    /**
     * 保存附件信息
     * @param $fileInfo
     * @param int $pid
     * @param int $type
     * @param int $relation_id
     * @param int $file_type
     * @param int $upload_type
     * @return bool
     */
    public function saveAttachment($fileInfo, int $pid = 0, int $type = 1, int $relation_id = 0, int $file_type = 1, int $upload_type = 1)
    {
        $fileType = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
        if ($fileInfo && !in_array($fileType, ['xlsx', 'xls'])) {
            $data['file_type'] = $file_type;
            $data['type'] = $type == 0 ? 1 : $type;
            $data['relation_id'] = $relation_id;
            $data['name'] = $fileInfo['name'];
            $data['real_name'] = $fileInfo['real_name'] ?? $fileInfo['name'] ?? '';
            $data['att_dir'] = $fileInfo['dir'] ?? $fileInfo['name'] ?? '';
            if ($data['att_dir'] && strpos($data['att_dir'], 'http') === false) {
                $data['att_dir'] = sys_config('site_url') . $data['att_dir'];
            }
            $data['satt_dir'] = $fileInfo['thumb_path'] ?? $fileInfo['cover_image'] ?? '';
            if ($file_type == 2) {
                if (!$data['satt_dir']) {//视频 默认封面
                    $data['satt_dir'] = sys_config('site_url') . '/statics/images/video_default_cover.png';
                }
                if ($data['real_name'] && strpos($data['real_name'], '/') !== false) {
                    $nameArr = explode('/', $data['real_name']);
                    $data['real_name'] = end($nameArr) ?? $data['real_name'];
                }
            }

            $data['att_size'] = $fileInfo['size'] ?? '';
            $data['att_type'] = $fileInfo['type'] ?? '';
            $data['image_type'] = $upload_type;
            $data['module_type'] = 1;
            $data['time'] = $fileInfo['time'] ?? time();
            $data['pid'] = $pid;
            $this->dao->save($data);
        }
        return true;
    }
}
