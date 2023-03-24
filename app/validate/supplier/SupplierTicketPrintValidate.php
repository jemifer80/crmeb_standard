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
namespace app\validate\supplier;

use think\Validate;

class SupplierTicketPrintValidate extends Validate
{

    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'develop_id' => 'require|gt:0',
        'api_key' => 'require|max:100',
        'client_id' => 'require|max:100',
        'terminal_number' => 'require|max:100',
        'status' => 'require|number',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'develop_id.require' => '请填写用户ID',
        'develop_id.gt' => '用户ID参数错误',
        'api_key.require' => '请填写秘钥',
        'api_key.max' => '秘钥最多不能超过100个字符',
        'client_id.require' => '请填写应用ID',
        'client_id.max' => '应用ID最多不能超过100个字符',
        'terminal_number.require' => '请填写终端号',
        'terminal_number.max' => '终端号最多不能超过100个字符',
        'status.require' => '开关',
        'status.number' => '开关',
    ];

    protected $scene = [
        'update' => ['develop_id', 'api_key', 'client_id', 'terminal_number', 'status'],
    ];
}