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

namespace crmeb\form\components;


use crmeb\form\BaseComponent;
use crmeb\form\BuildInterface;

/**
 * 选择地址
 * Class Addres
 * @package crmeb\form\components
 */
class Address extends BaseComponent implements BuildInterface
{

    const NAME = 'address';

    /**
     * @var string[]
     */
    protected $rule = [
        'title' => '',
        'field' => '',
        'value' => '',
        'info' => '',
    ];

    /**
     * Map constructor.
     * @param string $field
     * @param string $title
     * @param array $value
     */
    public function __construct(string $field, string $title, array $value = null)
    {
        $this->rule['field'] = $field;
        $this->rule['title'] = $title;
        $this->rule['value'] = empty($value) ? '' : $value;
    }

    /**
     * @param string $info
     * @return $this
     */
    public function info(string $info)
    {
        $this->rule['info'] = $info;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function toArray(): array
    {
        $this->rule['name'] = self::NAME;
        $this->before();
        return $this->rule;
    }
}
