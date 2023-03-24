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

use think\exception\ValidateException;
use think\facade\App;
use app\controller\admin\AuthController;
use app\services\system\log\SystemFileServices;

/**
 * 文件校验控制器
 * Class SystemFile
 * @package app\admin\controller\system
 *
 */
class SystemFile extends AuthController
{
    /**
     * 构造方法
     * SystemFile constructor.
     * @param App $app
     * @param SystemFileServices $services
     */
    public function __construct(App $app, SystemFileServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
        //生产模式下不允许清除数据
        if (!env('APP_DEBUG', false)) {
            throw new ValidateException('生产模式下，禁止操作');
        }
    }

    /**
     * 文件校验记录
     * @return mixed
     */
    public function index()
    {
        return $this->success(['list' => $this->services->getFileList()]);
    }

    //打开目录
    public function opendir()
    {
//        return $this->success($this->services->opendir());
    }

    //读取文件
    public function openfile()
    {
//        $file = $this->request->param('filepath');
//        if (empty($file)) return $this->fail('出现错误');
//        return $this->success($this->services->openfile($file));
    }

    //保存文件
    public function savefile()
    {
//        $comment = $this->request->param('comment');
//        $filepath = $this->request->param('filepath');
//        if(empty($comment) || empty($filepath)){
//            return $this->fail('出现错误');
//        }
//        $res = $this->services->savefile($filepath,$comment);
//        if ($res) {
        return $this->success('保存成功!');
//        } else {
//            return $this->fail('保存失败');
//        }
    }
}
