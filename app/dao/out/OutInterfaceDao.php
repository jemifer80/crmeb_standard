<?php

namespace app\dao\out;

use app\dao\BaseDao;
use app\model\out\OutInterface;

class OutInterfaceDao extends BaseDao
{
    protected function setModel(): string
    {
        return OutInterface::class;
    }

    public function getInterfaceList($where, $field)
    {
        return $this->getModel()->where($where)->field($field)->select()->toArray();
    }
}