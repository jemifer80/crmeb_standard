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
declare (strict_types=1);

namespace app\services\system\attachment;

use app\services\BaseServices;
use app\dao\system\attachment\SystemAttachmentCategoryDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 *
 * Class SystemAttachmentCategoryServices
 * @package app\services\attachment
 * @mixin SystemAttachmentCategoryDao
 */
class SystemAttachmentCategoryServices extends BaseServices
{

    /**
     * SystemAttachmentCategoryServices constructor.
     * @param SystemAttachmentCategoryDao $dao
     */
    public function __construct(SystemAttachmentCategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取分类列表
     * @param array $where
     * @return array
     */
    public function getAll(array $where)
    {
        $list = $this->dao->getList($where);
        foreach ($list as &$item) {
            $item['title'] = $item['name'];
            $item['children'] = [];
            if ($where['name'] == '' && $this->dao->count(['pid' => $item['id'],'file_type'=>$where['file_type']])) $item['loading'] = false;
        }
        return compact('list');
    }

    /**
     * 格式化列表
     * @param $menusList
     * @param int $pid
     * @param array $navList
     * @return array
     */
    public function tidyMenuTier($menusList, $pid = 0, $navList = [])
    {
        foreach ($menusList as $k => $menu) {
            $menu['title'] = $menu['name'];
            if ($menu['pid'] == $pid) {
                unset($menusList[$k]);
                $menu['children'] = $this->tidyMenuTier($menusList, $menu['id']);
                if ($menu['children']) $menu['expand'] = true;
                $navList[] = $menu;
            }
        }
        return $navList;
    }

    /**
 	* 创建新增表单
	* @param $pid
	* @param int $type
	* @param int $relationId
	* @param int $file_type
	* @return mixed
	*/
    public function createForm($pid, int $type = 1, int $relationId = 0, int $file_type = 1)
    {
        return create_form('添加分类', $this->form(['pid' => $pid], $type, $relationId, $file_type), Url::buildUrl('/file/category'), 'POST');
    }

    /**
 	* 创建编辑表单
	* @param int $id
	* @param int $type
	* @param int $relationId
	* @param int $file_type
	* @return mixed
	* @throws \think\db\exception\DataNotFoundException
	* @throws \think\db\exception\DbException
	* @throws \think\db\exception\ModelNotFoundException
	 */
    public function editForm(int $id, int $type = 1, int $relationId = 0, int $file_type = 1)
    {
        $info = $this->dao->get($id);
        return create_form('编辑分类', $this->form($info, $type, $relationId, $file_type), Url::buildUrl('/file/category/' . $id), 'PUT');
    }

    /**
 	* 生成表单参数
	* @param $info
	* @param int $type
	* @param int $relationId
	* @param int $file_type
	* @return array
	*/
    public function form($info = [], int $type = 1, int $relationId = 0, int $file_type = 1)
    {
        return [
			Form::hidden('file_type', $file_type),
            Form::select('pid', '上级分类', (int)($info['pid'] ?? ''))->setOptions($this->getCateList(['pid' => 0, 'type' => $type, 'relation_id' => $relationId, 'file_type' => $file_type]))->filterable(true),
            Form::input('name', '分类名称', $info['name'] ?? '')->maxlength(20),
        ];
    }

    /**
     * 获取分类列表（添加修改）
     * @param array $where
     * @return mixed
     */
    public function getCateList(array $where)
    {
        $list = $this->dao->getList($where);
        $options = [['value' => 0, 'label' => '所有分类']];
        foreach ($list as $id => $cateName) {
            $options[] = ['label' => $cateName['name'], 'value' => $cateName['id']];
        }
        return $options;
    }

    /**
     * 保存新建的资源
     * @param array $data
     */
    public function save(array $data)
    {
        if ($this->dao->getOne(['name' => $data['name'], 'relation_id' => $data['relation_id'] ?? 0])) {
            throw new AdminException('该分类已经存在');
        }
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException('新增失败！');
        return $res;
    }

    /**
     * 保存修改的资源
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data)
    {
        $attachment = $this->dao->getOne(['name' => $data['name'], 'relation_id' => $data['relation_id'] ?? 0]);
        if ($attachment && $attachment['id'] != $id) {
            throw new AdminException('该分类已经存在');
        }
        $res = $this->dao->update($id, $data);
        if (!$res) throw new AdminException('编辑失败！');
    }

    /**
     * 删除分类
     * @param int $id
     */
    public function del(int $id)
    {
        $count = $this->dao->getCount(['pid' => $id]);
        if ($count) {
            throw new AdminException('请先删除下级分类！');
        } else {
            $res = $this->dao->delete($id);
            if (!$res) throw new AdminException('请先删除下级分类！');
        }
    }


    /**
     * 获取一条数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne($where)
    {
        return $this->dao->getOne($where);
    }
}
