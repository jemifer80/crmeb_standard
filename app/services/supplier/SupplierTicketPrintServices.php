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
namespace app\services\supplier;

use app\dao\supplier\SupplierTicketPrintDao;
use app\services\BaseServices;
use think\exception\ValidateException;

/**
 * 小票打印
 * Class SupplierTicketPrintServices
 * @package app\services\supplier
 * @mixin SupplierTicketPrintDao
 */
class SupplierTicketPrintServices extends BaseServices
{

    /**
     * 构造方法
     * SupplierTicketPrintServices constructor.
     * @param SupplierTicketPrintDao $dao
     */
    public function __construct(SupplierTicketPrintDao $dao)
    {
        $this->dao = $dao;
    }


    /**
     * 获取打印配置
     * @param int $supplierId
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTicketInfo(int $supplierId, string $field = '*')
    {
        $info = $this->dao->getOne(['supplier_id' => $supplierId], $field);
        if ($info) {
            $data = $info->toArray();
        } else {
            $data = [
                'id' => 0,
                'supplier_id' => $supplierId,
                'develop_id' => 0,
                'api_key' => '',
                'client_id' => '',
                'terminal_number' => '',
                'status' => 0,
            ];
        }
        return $data;
    }

    /**
     * 更新打印配置
     * @param int $supplierId
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function savePrintData(int $supplierId, $data)
    {
        $info = $this->dao->getOne(['supplier_id' => $supplierId], 'id, supplier_id');
        if ($info) {
            $res = $this->dao->update($info['id'], $data);
        } else {
            $data['supplier_id'] = $supplierId;
            $res = $this->dao->save($data);
        }

        if (!$res) throw new ValidateException('保存失败！');
        return true;
    }
}
