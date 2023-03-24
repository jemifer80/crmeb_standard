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
namespace app\validate\admin\marketing;

use think\Validate;

class StoreIntegralValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'product_id' => 'require',
        'title' => 'require',
        'info' => 'require',
        'unit_name' => 'require',
        'image' => 'require',
        'images' => 'require',
        'description' => 'require',
        'attrs' => 'require',
        'items' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'product_id.require' => '请选择商品',
        'title.require' => '请填写商品标题',
        'info.require' => '请填写秒杀活动简介',
        'unit_name.require' => '请填写单位',
        'image.require' => '请选择商品主图',
        'images.require' => '请选择商品轮播图',
        'description.require' => '请填写积分商品详情',
        'attrs.require' => '请选择规格',
    ];

    protected $scene = [
        'save' => ['product_id', 'title', 'unit_name', 'image', 'images',  'num', 'once_num', 'sort', 'description', 'attrs', 'items'],
    ];
}
