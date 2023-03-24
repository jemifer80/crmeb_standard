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

namespace app\services\activity\live;


use app\dao\activity\live\LiveRoomDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\DownloadImageService;
use crmeb\services\wechat\MiniProgram;
use think\facade\Log;

/**
 * 直播间
 * Class LiveRoomServices
 * @package app\services\activity\live
 * @mixin LiveRoomDao
 */
class LiveRoomServices extends BaseServices
{
    /**
     * LiveRoomServices constructor.
     * @param LiveRoomDao $dao
     */
    public function __construct(LiveRoomDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where)
    {
        $where['is_del'] = 0;
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', [], $page, $limit);
        $count = $this->dao->count($where);
        return compact('count', 'list');
    }

    public function userList(array $where)
    {
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', ['roomGoods.goods', 'anchor'], $page, $limit);
        foreach ($list as &$item) {
            $item['roomid'] = $item['room_id'];
            $item['goods'] = [];
            $item['show_time'] = date('m/d H:i', strtotime($item['start_time']));
            if (isset($item['roomGoods']) && $item['roomGoods']) {
                $item['goods'] = array_column($item['roomGoods'], 'goods');
            }
            if (in_array($item['live_status'], [105, 106])) {
                $item['live_status'] = 101;
            }
            if (in_array($item['live_status'], [104, 107])) {
                $item['live_status'] = 103;
            }
            unset($item['roomGoods']);
        }
        return $list;
    }

    public function getPlaybacks(int $id)
    {
        $room = $this->dao->get(['id' => $id, 'is_del' => 0]);
        if (!$room) {
            throw new AdminException('数据不存在');
        }
        [$page, $limit] = $this->getPageValue();
        return MiniProgram::getLivePlayback($room['room_id'], $page, $limit);
    }

    public function add(array $data)
    {
        /** @var LiveAnchorServices $anchorServices */
        $anchorServices = app()->make(LiveAnchorServices::class);
        $anchor = $anchorServices->get(['wechat' => $data['anchor_wechat']]);
        if (!$anchor) {
            throw new AdminException('该主播不存在');
        }
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $time = time() + 600;
        $time6 = time() + 180 * 24 * 3600;
        if ($data['start_time'] < $time || $data['start_time'] > $time6) {
            throw new AdminException('开播时间需要在当前时间的10分钟后 并且 开始时间不能在 6 个月后');
        }
        $t = $data['end_time'] - $data['start_time'];
        if ($t < 1800 || $t > 24 * 3600) {
            throw new AdminException('开播时间和结束时间间隔不得短于30分钟，不得超过24小时');
        }
        $data['anchor_name'] = $data['anchor_name'] ?? $anchor['name'];
        $data['add_time'] = time();
        $wxRoom = $this->wxCreate($data);
        $data['room_id'] = $wxRoom['roomId'];
        $data['status'] = 2;

        if (!$this->dao->save($data)) {
            throw new AdminException('添加直播间数据失败');
        }

        return true;
    }


    public function apply($id, $status, $msg = '')
    {
        $room = $this->dao->get($id);
        if (!$room) {
            throw new AdminException('数据不存在');
        }
        $room->status = $status;
        if ($status == -1)
            $room->error_msg = $msg;
        else {
            $room->room_id = $this->wxCreate($room)['roomId'];
            $room->status = 2;
        }
        $room->save();
    }

    public function wxCreate($room)
    {
        try {
            $coverImg = root_path() . 'public' . app()->make(DownloadImageService::class)->downloadImage($room['cover_img'])['path'];
            $shareImg = root_path() . 'public' . app()->make(DownloadImageService::class)->downloadImage($room['share_img'])['path'];
        } catch (\Throwable $e) {
            Log::error('添加直播间封面图出错误，原因：' . $e->getMessage());
            $coverImg = root_path() . 'public' . $room['cover_img'];
            $shareImg = root_path() . 'public' . $room['share_img'];
        }
        $data = [
            'startTime' => is_string($room['start_time']) ? strtotime($room['start_time']) : $room['start_time'],
            'endTime' => is_string($room['end_time']) ? strtotime($room['end_time']) : $room['end_time'],
            'name' => $room['name'],
            'anchorName' => $room['anchor_name'],
            'anchorWechat' => $room['anchor_wechat'],
            'screenType' => $room['screen_type'],
            'closeGoods' => $room['close_goods'] == 1 ? 0 : 1,
            'closeLike' => $room['close_like'] == 1 ? 0 : 1,
            'closeComment' => $room['close_comment'] == 1 ? 0 : 1,
            'closeReplay' => $room['replay_status'] == 1 ? 0 : 1,
            'type' => $room['type'],
            'coverImg' => MiniProgram::temporaryUpload($coverImg)->media_id,
            'shareImg' => MiniProgram::temporaryUpload($shareImg)->media_id,
            'closeKf' => 1
        ];
        $data['feedsImg'] = $data['coverImg'];
        @unlink($coverImg);
        @unlink($shareImg);
        return MiniProgram::createLiveRoom($data);
    }

    public function isShow(int $id, $is_show)
    {
        $this->dao->update($id, ['is_show' => $is_show]);
        return $is_show == 1 ? '显示成功' : '隐藏成功';
    }

    public function delete(int $id)
    {
        $room = $this->dao->get(['id' => $id, 'is_del' => 0]);
        if ($room) {
            if (!$this->dao->update($id, ['is_del' => 1])) {
                throw new AdminException('删除失败');
            }
            /** @var LiveRoomGoodsServices $liveRoomGoods */
            $liveRoomGoods = app()->make(LiveRoomGoodsServices::class);
            $liveRoomGoods->delete(['live_room_id' => $id]);
        }
        return true;
    }

    public function mark($id, $mark)
    {
        return $this->dao->update($id, compact('mark'));
    }

    /**
     * 直播间添加商品
     * @param $room_id
     * @param array $ids
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function exportGoods(int $room_id, array $ids)
    {
        $liveGoodsServices = app()->make(LiveGoodsServices::class);
        if (count($ids) != count($goods = $liveGoodsServices->goodsList($ids)))
            throw new AdminException('请选择正确的直播商品');
        if (!$room = $this->dao->validRoom($room_id))
            throw new AdminException('直播间状态有误');
        $data = [];
        /** @var LiveRoomGoodsServices $liveRoomGoodsServices */
        $liveRoomGoodsServices = app()->make(LiveRoomGoodsServices::class);
        //查询已经关联的
        $roomGoods = $liveRoomGoodsServices->getColumn(['live_room_id' => $room_id], 'live_goods_id', 'Live_goods_id');
        $goods_ids = [];
        foreach ($goods as $key => $item) {
            if (isset($roomGoods[$item['id']])) {
                unset($goods[$key]);
            } else {
                $goods_ids[] = $item['goods_id'];
                $data[] = [
                    'live_room_id' => $room_id,
                    'live_goods_id' => $item['id']
                ];
            }
        }
        if ($goods_ids) {
            $liveRoomGoodsServices->saveAll($data);
            return MiniProgram::roomAddGoods($room['room_id'], $goods_ids);
        }
        return true;
    }

    /**
     * 同步直播间状态
     * @return bool
     */
    public function syncRoomStatus()
    {
        $start = 1;
        $limit = 50;
        $data = $dataAll = [];
        $rooms = $this->dao->getColumn([], 'id,room_id,live_status', 'room_id');
        do {
            $wxRooms = MiniProgram::getLiveInfo($start, $limit);
            foreach ($wxRooms as $room) {
                if ($rooms && isset($rooms[$room['roomid']])) {
                    if ($room['live_status'] != $rooms[$room['roomid']]['live_status']) {
                        $this->dao->update($rooms[$room['roomid']]['id'], ['live_status' => $room['live_status']]);
                    }
                } else {
                    $data['name'] = $room['name'];
                    $data['room_id'] = $room['roomid'];
                    $data['cover_img'] = $room['cover_img'];
                    $data['share_img'] = $room['share_img'];
                    $data['live_status'] = $room['live_status'];
                    $data['start_time'] = $room['start_time'];
                    $data['end_time'] = $room['end_time'];
                    $data['anchor_name'] = $room['anchor_name'];
                    $dataAll[] = $data;
                }
            }
            $start++;
        } while (count($wxRooms) >= $limit);
        if ($dataAll) {
            $this->dao->saveAll($dataAll);
        }
        return true;
    }


}
