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
namespace app\controller\admin\v1\setting;

use app\controller\admin\AuthController;
use app\services\other\SystemCityServices;
use think\facade\App;
use crmeb\services\{CacheService};


/**
 * 城市数据
 * Class SystemCity
 * @package app\controller\admin\v1\setting
 */
class SystemCity extends AuthController
{
    /**
     * 构造方法
     * SystemCity constructor.
     * @param App $app
     * @param SystemStoreServices $services
     */
    public function __construct(App $app, SystemCityServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 城市列表
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $where = $this->request->getMore([
            [['parent_id', 'd'], 0]
        ]);
        return $this->success($this->services->getCityList($where));
    }

    /**
     * 添加城市
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        [$parentId] = $this->request->getMore([
            [['parent_id', 'd'], 0]
        ], true);
        return $this->success($this->services->createCityForm($parentId));
    }

    /**
     * 保存
     */
    public function save()
    {
        $data = $this->request->postMore([
            [['id', 'd'], 0],
            [['name', 's'], ''],
            [['merger_name', 's'], ''],
            [['area_code', 's'], ''],
            [['lng', 's'], ''],
            [['lat', 's'], ''],
            [['level', 'd'], 0],
            [['parent_id', 'd'], 0],
        ]);
        $this->validate($data, \app\validate\admin\setting\SystemCityValidate::class, 'save');
        if ($data['parent_id'] == 0) {
            $data['merger_name'] = $data['name'];
        } else {
            $data['merger_name'] = $this->services->value(['id' => $data['parent_id']], 'name') . ',' . $data['name'];
        }
        CacheService::delete($this->services->tree_city_key);
        if ($data['id'] == 0) {
            unset($data['id']);
            $data['level'] = $data['level'] + 1;
            $data['city_id'] = intval($this->services->getCityIdMax() + 1);
            $this->services->save($data);
            return $this->success('添加城市成功!');
        } else {
            unset($data['level']);
            unset($data['parent_id']);
            $this->services->update($data['id'], $data);
            return $this->success('修改城市成功!');
        }
    }

    /**
     * 修改城市
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        [$id] = $this->request->getMore([
            [['id', 'd'], 0]
        ], true);
        return $this->success($this->services->updateCityForm($id));
    }

    /**
     * 删除城市
     * @throws \Exception
     */
    public function delete()
    {
        [$id] = $this->request->getMore([
            [['city_id', 'd'], 0]
        ], true);
        $this->services->deleteCity($id);
        CacheService::delete($this->services->tree_city_key);
        return $this->success('删除成功!');
    }

    /**
     * 清除城市缓存
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function clean_cache()
    {
        CacheService::delete($this->services->tree_city_key);
        CacheService::delete('CITY_LIST');
        return $this->success('清除成功!');
    }
}
