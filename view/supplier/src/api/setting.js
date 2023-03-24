// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import request from '@/plugins/request';


/**
 * @description 获取小票
 * @param {Object} param data {Object} 传值参数
 */
export function printing () {
    return request({
        url: '/printing',
        method: 'get'
    });
}

/**
 * @description 更新小票
 * @param {Object} param data {Object} 传值参数
 */
export function putPrinting (data) {
    return request({
        url: '/printing',
        method: 'put',
        data
    });
}

/**
 * @description 供应商
 * @param {Object} param data {Object} 传值参数
 */
export function supplier () {
    return request({
        url: '/supplier',
        method: 'get'
    });
}

/**
 * @description 更新供应商
 * @param {Object} param data {Object} 传值参数
 */
export function putSupplier (data) {
    return request({
        url: '/supplier',
        method: 'put',
        data
    });
}

/**
 * @description 获取省市区街道
 */
export function cityApi (data) {
    return request({
        url: 'city',
        method: 'get',
        params: data
    });
}

/**
 * @description 管理员列表
 */
export function adminListApi () {
    return request({
        url: 'admin',
        method: 'get'
    });
}

/**
 * @description 添加管理员
 */
export function adminFromApi () {
    return request({
        url: 'admin/create',
        method: 'get'
    });
}

/**
 * @description 编辑管理员
 */
export function adminEditFromApi (id) {
    return request({
        url:`admin/${id}/edit`,
        method: 'get'
    });
}

/**
 * @description 管理员修改状态
 */
export function setShowApi (data) {
    return request({
        url: `admin/set_status/${data.id}/${data.status}`,
        method: 'put'
    });
}







