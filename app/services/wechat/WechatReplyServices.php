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

namespace app\services\wechat;

use app\services\BaseServices;
use app\dao\wechat\WechatReplyDao;
use app\services\kefu\KefuServices;
use crmeb\exceptions\AdminException;
use crmeb\services\wechat\ErrorMessage;
use crmeb\services\wechat\Messages;
use crmeb\services\wechat\OfficialAccount;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Transfer;
use EasyWeChat\Kernel\Messages\Voice;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\Model;

/**
 *
 * Class UserWechatuserServices
 * @package app\services\user
 * @mixin WechatReplyDao
 */
class WechatReplyServices extends BaseServices
{

    /**
     * UserWechatuserServices constructor.
     * @param WechatReplyDao $dao
     */
    public function __construct(WechatReplyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 消息类型
     * @return string[]
     * @var string[]
     */
    public function replyType()
    {
        return ['text', 'image', 'news', 'voice'];
    }

    /**
     * 自定义简单查询总数
     * @param array $where
     * @return int
     */
    public function getCount(array $where): int
    {
        return $this->dao->getCount($where);
    }

    /**
     * 复杂条件搜索列表
     * @param array $where
     * @param string $field
     * @return array
     */
    public function getWhereUserList(array $where, string $field): array
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getListByModel($where, $field, $page, $limit);
        $count = $this->dao->getCountByWhere($where);
        return [$list, $count];
    }

    /**
     * @param $key
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getDataByKey(string $key)
    {
        /** @var WechatKeyServices $services */
        $services = app()->make(WechatKeyServices::class);
        $data = $services->getOne(['keys' => $key]);
        $resdata = $this->dao->getOne(['id' => $data['reply_id'] ?? '']);
        $resdata['data'] = isset($resdata['data']) ? json_decode($resdata['data'], true) : [];
        $resdata['key'] = $key;
        return $resdata;
    }

    /**
     * @param $data
     * @param $key
     * @param $type
     * @param int $status
     * @return bool
     */
    public function redact($data, $id, $key, $type, $status = 1)
    {
        $method = 'tidy' . ucfirst($type);
        if ($id == 'undefined') {
            $id = 0;
        }
        if ((!isset($data['content']) && !isset($data['src'])) || (isset($data['content']) && $data['content'] == '' && isset($data['src']) && $data['src'] == '')) $data = $data['list'][0] ?? [];
        try {
            $res = $this->{$method}($data, $id);
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage());
        }
        if (!$res) return false;
        $arr = [];
        /** @var WechatKeyServices $keyServices */
        $keyServices = app()->make(WechatKeyServices::class);
        $count = $this->dao->getCount(['id' => $id]);
        if ($count) {
            $keyServices->delete($id, 'reply_id');
            $insertData = explode(',', $key);
            foreach ($insertData as $k => $v) {
                $arr[$k]['keys'] = $v;
                $arr[$k]['reply_id'] = $id;
            }
            $res = $this->dao->update($id, ['type' => $type, 'data' => json_encode($res), 'status' => $status], 'id');
            $res1 = $keyServices->saveAll($arr);
            if (!$res || !$res1) {
                throw new AdminException('保存失败!');
            }
        } else {
            $reply = $this->dao->save([
                'type' => $type,
                'data' => json_encode($res),
                'status' => $status,
            ]);
            $insertData = explode(',', $key);
            foreach ($insertData as $k => $v) {
                $arr[$k]['keys'] = $v;
                $arr[$k]['reply_id'] = $reply->id;
            }
            $res = $keyServices->saveAll($arr);
            if (!$res) throw new AdminException('保存失败!');
        }
        return true;
    }

    /**
     * 获取所有关键字
     * @param array $where
     * @return array
     */
    public function getKeyAll($where = array())
    {
        /** @var WechatReplyKeyServices $replyKeyServices */
        $replyKeyServices = app()->make(WechatReplyKeyServices::class);
        $data = $replyKeyServices->getReplyKeyAll($where);
        /** @var WechatKeyServices $keyServices */
        $keyServices = app()->make(WechatKeyServices::class);
        foreach ($data['list'] as &$item) {
            if ($item['data']) $item['data'] = json_decode($item['data'], true);
            switch ($item['type']) {
                case 'text':
                    $item['typeName'] = '文字消息';
                    break;
                case  'image':
                    $item['typeName'] = '图片消息';
                    break;
                case 'news':
                    $item['typeName'] = '图文消息';
                    break;
                case 'voice':
                    $item['typeName'] = '声音消息';
                    break;
            }
            $keys = $keyServices->getColumn(['reply_id' => $item['id']], 'keys');
            $item['key'] = implode(',', $keys);
        }
        return $data;
    }

    /**
     * 查询一条
     * @param int $id
     * @return array|null|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getKeyInfo(int $id)
    {
        $resdata = $this->dao->getOne(['id' => $id]);
        /** @var WechatKeyServices $keyServices */
        $keyServices = app()->make(WechatKeyServices::class);
        $keys = $keyServices->getColumn(['reply_id' => $resdata['id']], 'keys');
        $resdata['data'] = $resdata['data'] ? json_decode($resdata['data'], true) : [];
        $resdata['key'] = implode(',', $keys);
        return $resdata;
    }

    /**
     * 整理文本输入的消息
     * @param $data
     * @param $key
     * @return array|bool
     */
    public function tidyText($data, $id)
    {
        $res = [];
        if (!isset($data['content']) || $data['content'] == '') {
            throw new AdminException('请输入回复信息内容');
        }
        $res['content'] = $data['content'];
        return $res;
    }

    /**
     * 整理图片资源
     * @param $data
     * @param $key
     * @return array|bool|mixed
     */
    public function tidyImage($data, $id)
    {
        if (!isset($data['src']) || $data['src'] == '') {
            throw new AdminException('请上传回复的图片');
        }
        $reply = $this->dao->get((int)$id);
        if ($reply) $reply['data'] = json_decode($reply['data'], true);
        if ($reply && isset($reply['data']['src']) && $reply['data']['src'] == $data['src']) {
            $res = $reply['data'];
        } else {
            $res = [];
            // 图片转media
            $res['src'] = $data['src'];
            try {
                $material = OfficialAccount::temporaryUpload(url_to_path($data['src']));
            } catch (\Throwable $e) {
                throw new ValidateException(ErrorMessage::getMessage($e->getMessage()));
            }
            $res['media_id'] = $material->media_id;
            $dataEvent = ['type' => 'image', 'media_id' => $material->media_id, 'path' => $res['src'], 'url' => $material->url ?? ''];
            /** @var WechatMediaServices $mateServices */
            $mateServices = app()->make(WechatMediaServices::class);
            $mateServices->save($dataEvent);
        }
        return $res;
    }

    /**
     * 整理声音资源
     * @param $data
     * @param $key
     * @return array|bool|mixed
     */
    public function tidyVoice($data, $id)
    {
        if (!isset($data['src']) || $data['src'] == '') {
            throw new AdminException('请上传回复的声音');
        }
        $reply = $this->dao->get((int)$id);
        if ($reply) $reply['data'] = json_decode($reply['data'], true);
        if ($reply && isset($reply['data']['src']) && $reply['data']['src'] == $data['src']) {
            $res = $reply['data'];
        } else {
            $res = [];
            // 声音转media
            $res['src'] = $data['src'];
            try {
                $material = OfficialAccount::materialService()->uploadVoice(url_to_path($data['src']));
            } catch (\Throwable $e) {
                throw new ValidateException(ErrorMessage::getMessage($e->getMessage()));
            }
            $res['media_id'] = $material->media_id;
            $dataEvent = ['media_id' => $material->media_id, 'path' => $res['src'], 'type' => 'voice'];
            /** @var WechatMediaServices $mateServices */
            $mateServices = app()->make(WechatMediaServices::class);
            $mateServices->save($dataEvent);
        }
        return $res;
    }

    /**
     * 整理图文资源
     * @param $data
     * @param $key
     * @return bool
     */
    public function tidyNews($data, $id = 0)
    {
//        $data = $data['list'][0];
        if (!count($data)) {
            throw new AdminException('请选择图文消息');
        }
        $siteUrl = sys_config('site_url');
        if (empty($data['url'])) $data['url'] = $siteUrl . '/pages/extension/news_details/index?id=' . $data['id'];
        if (count($data['image_input'])) $data['image'] = $data['image_input'][0];
        return $data;
    }

    /**
     * 获取关键字
     * @param $key
     * @param string $openId
     * @return array|Image|News|Text|Transfer|Voice
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function reply($key, string $openId = '')
    {
        $res = $this->dao->getKey($key);
        if (empty($res)) {
            /** @var KefuServices $services */
            $services = app()->make(KefuServices::class);
            $services->replyTransferService($key, $openId);
            return Messages::transfer();
        }
        return $this->replyDataByMessage($res->toArray());
    }

    /**
     * 根据关键字内容返回对应的内容
     * @param array $res
     * @return array|Image|News|Text|Voice
     */
    public function replyDataByMessage(array $res)
    {
        $res['data'] = json_decode($res['data'], true);
        if ($res['type'] == 'text') {
            return Messages::textMessage($res['data']['content']);
        } else if ($res['type'] == 'image') {
            return Messages::imageMessage($res['data']['media_id']);
        } else if ($res['type'] == 'news') {
            $title = $res['data']['title'] ?? '';
            $image = $res['data']['image'] ?? '';
            $description = $res['data']['synopsis'] ?? '';
            $url = $res['data']['url'] ?? '';
            return Messages::newsMessage($title, $description, $url, $image);
        } else if ($res['type'] == 'voice') {
            return Messages::voiceMessage($res['data']['media_id']);
        }
    }
}
