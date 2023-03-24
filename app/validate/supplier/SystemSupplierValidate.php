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

class SystemSupplierValidate extends Validate
{

    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'supplier_name' => 'require|max:25',
        'name' => 'max:25',
        'phone' => 'require|mobile',
        'email' => 'email|max:50',
        'address' => 'max:255',
        'province' => 'gt:0',
        'city' => 'gt:0',
        'area' => 'gt:0',
        'detailed_address' => 'max:255',
        'account' => 'require|max:25',
        'mark' => 'max:255',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'supplier_name.require' => '请填写供应商名称',
        'supplier_name.max' => '供应商名称最多不能超过25个字符',
        'name.max' => '名称最多不能超过25个字符',
        'phone.require' => '请填写手机号',
        'phone.mobile' => '手机号格式不正确',
        'email.email' => '邮箱格式不正确',
        'email.max' => '邮箱最多不能超过100个字符',
        'address.max' => '供应商地址最多不能超过255个字符',
        'mark.max' => '备注最多不能超过255个字符',
        'province.gt' => '省份信息错误',
        'city.gt' => '城市信息错误',
        'area.gt' => '地区信息错误',
        'detailed_address.max' => '详细地址最多不能超过255个字符',
        'account.require' => '请填写供应商登录用户名',
        'account.max' => '供应商登录用户名最多不能超过25个字符',
    ];

    protected $scene = [
        'update' => ['supplier_name', 'name', 'phone', 'email', 'address', 'detailed_address', 'province', 'city', 'area'],
        'save' => ['supplier_name', 'name', 'phone', 'email', 'address', 'detailed_address', 'province', 'city', 'area','account','mark'],
    ];
}