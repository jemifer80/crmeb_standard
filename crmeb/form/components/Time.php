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
 * 时间组件
 * Class Time
 * @package crmeb\form\components
 * @method Time info(string $info) 设置info
 * @method Time value($value) 设置value
 * @method Time type(string $type) 设置type
 * @method Time placeholder(string $placeholder) 设置placeholder
 */
class Time extends BaseComponent implements BuildInterface
{

    const NAME = 'time';

    /**
     * @var string[]
     */
    protected $rule = [
        'title' => '',
        'value' => '',
        'field' => '',
        'info' => '',
        'placeholder' => '',
        'format' => 'HH:mm:ss',
        'type' => 'timerange'
    ];

    /**
     * Time constructor.
     * @param string $field
     * @param string $title
     * @param array|null $value
     */
    public function __construct(string $field, string $title, array $value = null)
    {
        $this->rule['field'] = $field;
        $this->rule['title'] = $title;
        $this->rule['value'] = empty($value) ? '' : $value;
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

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, ['title', 'field', 'disabled', 'copyText'])) {
            return $this;
        }
        $keys = array_keys($this->rule);
        if (in_array($name, $keys)) {
            $this->rule[$name] = $arguments[0] ?? null;
        }
        return $this;
    }
}
