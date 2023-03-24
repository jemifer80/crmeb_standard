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

class StoreBargainValidate extends Validate
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
        'images' => 'require',
        'section_time' => 'require',
        'num' => 'require|gt:0',
        'temp_id' => 'require',
        'description' => 'require',
        'attrs' => 'require',
        'items' => 'require',
        'bargain_num'=>'require|gt:0',
        'people_num'=>'require|gt:1',
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
        'info.require' => '请填写砍价活动简介',
        'unit_name.require' => '请填写单位',
        'images.require' => '请选择商品轮播图',
        'section_time.require' => '请选择时间段',
        'num.require' => '请填写购买数量限制',
        'num.gt' => '购买数量限制必须大于0',
        'bargain_num.require' => '请填写帮砍次数',
        'bargain_num.gt' => '帮砍次数必须大于0',
        'people_num.require' => '请填写砍价人数',
        'people_num.gt' => '砍价人数必须大于1',
        'temp_id.require' => '请选择运费模板',
        'description.require' => '请填写砍价商品详情',
        'attrs.require' => '请选择规格',
    ];

    protected $scene = [
        'save' => ['product_id', 'title', 'info', 'unit_name', 'image', 'images', 'give_integral', 'section_time', 'is_hot', 'status', 'num', 'bargain_num', 'people_num', 'temp_id', 'sort', 'description', 'attrs', 'items'],
    ];
}
