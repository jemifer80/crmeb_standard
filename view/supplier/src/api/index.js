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
 * @description 首页头部
 */
export function headerApi (data) {
    return request({
        url: 'home/header',
        method: 'get',
		params:data

    });
}

/**
 * @description 营业额趋势
 */
export function operateApi (data) {
    return request({
        url: 'home/order',
        method: 'get',
        params: data
    });
}

/**
 * @description 订单来源图表
 */
export function orderChannel (data) {
    return request({
        url: 'home/order_channel',
        method: 'get',
        params: data
    });
}

/**
 * @description 订单来源图表
 */
export function orderType (data) {
    return request({
        url: 'home/order_type',
        method: 'get',
        params: data
    });
}

