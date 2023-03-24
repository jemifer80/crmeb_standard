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
namespace app\controller\supplier;


use crmeb\basic\BaseController;

/**
 * 基类 所有控制器继承的类
 * Class AuthController
 * @package app\controller\admin
 * @method success($msg = 'ok', array $data = [])
 * @method fail($msg = 'error', array $data = [])
 */
class AuthController extends BaseController
{

    /**
     * 当前登录供应商ID
     * @var
     */
    protected $supplierId;

    /**
     * 当前登录供应商信息
     * @var
     */
    protected $supplierInfo;

    /**
     * 当前管理员权限
     * @var array
     */
    protected $auth = [];


    /**
     * 初始化
     */
    protected function initialize()
    {
        $this->supplierId = $this->request->hasMacro('supplierId') ? $this->request->supplierId() : 0;
        $this->supplierInfo = $this->request->hasMacro('supplierInfo') ? $this->request->supplierInfo() : [];
        $this->auth = $this->supplierInfo['rule'] ?? [];
    }

}
