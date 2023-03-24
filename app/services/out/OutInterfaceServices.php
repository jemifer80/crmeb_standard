<?php

namespace app\services\out;

use app\dao\out\OutInterfaceDao;
use app\Request;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;

class OutInterfaceServices extends BaseServices
{
    public function __construct(OutInterfaceDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取接口列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function outInterfaceList()
    {
        $list = $this->dao->getInterfaceList(['is_del' => 0], 'id,pid,method,type,name,name as title');
        $data = [];
        foreach ($list as $key => $item) {
            if ($item['pid'] == 0) {
                $data[] = $item;
                unset($list[$key]);
            }
        }
        foreach ($data as &$item_p) {
            foreach ($list as $item_c) {
                if ($item_p['id'] == $item_c['pid']) {
                    $item_p['children'][] = $item_c;
                }
            }
        }
        return $data;
    }

    /**
     * 验证对外接口权限
     * @param Request $request
     * @return bool
     */
    public function verifyAuth(Request $request)
    {
        $rule = trim(strtolower($request->rule()->getRule()));
        $method = trim(strtolower($request->method()));
        $authList = $this->dao->getColumn([['id', 'in', json_decode($request->outInfo()['rules'])]], 'method,url');
        $rolesAuth = [];
        foreach ($authList as $item) {
            $rolesAuth[trim(strtolower($item['method']))][] = trim(strtolower(str_replace(' ', '', $item['url'])));
        }
        $rule = str_replace('outapi', '', $rule);
        $rule = str_replace('<', '{', $rule);
        $rule = str_replace('>', '}', $rule);
        if (in_array($rule, $rolesAuth[$method])) {
            return true;
        } else {
            throw new AuthException('暂无对应接口权限');
        }
    }

    /**
     * 对外接口文档
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function interfaceInfo($id)
    {
        if (!$id) throw new AdminException('参数错误');
        $info = $this->dao->get($id);
        if (!$info) throw new AdminException('数据不存在');
        $info = $info->toArray();
        $info['request_params'] = json_decode($info['request_params']);
        $info['return_params'] = json_decode($info['return_params']);
        $info['error_code'] = json_decode($info['error_code']);
        return $info;
    }

    /**
     * 新增对外接口文档
     * @param $id
     * @param $data
     * @return bool
     */
    public function saveInterface($id, $data)
    {
        $data['request_params'] = json_encode($data['request_params']);
        $data['return_params'] = json_encode($data['return_params']);
        $data['error_code'] = json_encode($data['error_code']);
        if ($id) {
            $res = $this->dao->update($id, $data);
        } else {
            $res = $this->dao->save($data);
        }
        if (!$res) throw new AdminException('保存失败');
        return true;
    }

    /**
     * 修改接口名称
     * @param $data
     * @return bool
     */
    public function editInterfaceName($data)
    {
        $res = $this->dao->update($data['id'], ['name' => $data['name']]);
        if (!$res) throw new AdminException('修改失败');
        return true;
    }

    /**
     * 删除接口
     * @param $id
     * @return bool
     */
    public function delInterface($id)
    {
        $res = $this->dao->update($id, ['is_del' => 1]);
        if (!$res) throw new AdminException('删除失败');
        return true;
    }
}