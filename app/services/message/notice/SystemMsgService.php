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
namespace app\services\message\notice;

use app\jobs\notice\SystemMsgJob;
use app\services\message\NoticeService;
use app\services\message\service\StoreServiceServices;
use app\services\store\SystemStoreStaffServices;
use think\facade\Log;

/**
 * 短信发送消息列表
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2021/9/22 1:23 PM
 */
class SystemMsgService extends NoticeService
{
    /**
     * 判断是否开启权限
     * @var bool
     */
    private $isopend = true;

    /**
     * 是否开启权限
     * @param string $mark
     * @return $this
     */
    public function isOpen(string $mark)
    {
        $this->isopend = isset($this->noticeInfo['is_system']) && $this->noticeInfo['is_system'] === 1;
        return $this;
    }

    /**
     * 发送消息
     * @param $uid uid
     * @param array $data 模板内容
     */
    public function sendMsg(int $uid, $data)
    {
        try {
            $this->isopend = isset($this->noticeInfo['is_system']) && $this->noticeInfo['is_system'] === 1;
            if ($this->isopend && $uid) {
				//放入队列执行
				SystemMsgJob::dispatch([$uid, $this->noticeInfo, $data]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }
    }

    /**
     * 给客服发站内信
     * @param array $notceinfo
     * @param array $data
     */
    public function kefuSystemSend($data, $store_id = 0)
    {
        if ($store_id != 0) {
            /** @var SystemStoreStaffServices $systemStoreStaffServices */
            $systemStoreStaffServices = app()->make(SystemStoreStaffServices::class);
            $adminList = $systemStoreStaffServices->getNotifyStoreStaffList($store_id);
        } else {
            /** @var StoreServiceServices $StoreServiceServices */
            $StoreServiceServices = app()->make(StoreServiceServices::class);
            $adminList = $StoreServiceServices->getStoreServiceOrderNotice();
        }
        try {
            if ($this->isopend && $adminList) {
                foreach ($adminList as $key => $item) {
					$uid = $item['uid'] ?? 0;
					$data['admin_name'] = $item['staff_name'] ?? ($item['nickname'] ?? '');
					//放入队列执行
					SystemMsgJob::dispatch([$uid, $this->noticeInfo, $data, 2]);
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }
    }


}
