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

namespace app\jobs\activity;

use app\services\activity\video\VideoServices;use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 营销：短视频
 * Class VideoJob
 * @package app\jobs\activity
 */
class VideoJob extends BaseJobs
{

    use QueueTrait;

    /**
 	* 增加短视频浏览播放量
	* @param array $ids
	* @param int $num
	* @return bool
	*/
    public function setVideoPlayNum(array $ids, int $uid = 0, int $num = 1)
    {
		if (!$ids) {
			return true;
		}
        try {
			/** @var VideoServices $videoServices */
            $videoServices = app()->make(VideoServices::class);
			foreach ($ids as $id) {
				$videoServices->userRelationVideo($uid, (int)$id, 'play', $num);
			}
        } catch (\Throwable $e) {
            response_log_write([
                'message' => '增加短视频浏览播放量失败,失败原因:' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        return true;
    }
}
