// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import BasicLayout from '@/layouts/basic-layout';

const pre = 'supplier_';

export default {

    // 顶部导航供应商
    path: '/admin/supplier',
    name: 'supplier',
    header: 'supplier',
    // redirect: {
    //     name: `${pre}supplier`
    // },
    component: BasicLayout,
    // 供应商侧边栏
    children: [
        {
            path: 'supplier/index',
            name: `${pre}menu`,
            meta: {
                auth: ['admin-supplier-supplier-index'],
                title: '供应商菜单'
            },
            component: () => import('@/pages/supplier/supplierList/index')
        },
        {
            path: 'menu/list',
            name: `${pre}menulist`,
            meta: {
                auth: ['admin-supplier-menu-list'],
                title: '供应商管理'
            },
            component: () => import('@/pages/supplier/supplier-supplier_list/index')
        },
        {
            path: 'supplierAdd/:id?',
            name: `${pre}supplier`,
            meta: {
                auth: ['admin-supplier-supplierAdd'],
                title: '供应商添加'
            },
            component: () => import('@/pages/supplier/supplierAdd/index')
        },
        {
            path: 'orderList/index',
            name: `${pre}orderList`,
            meta: {
                auth: ['admin-supplier-orderList'],
                title: '订单列表'
            },
            component: () => import('@/pages/supplier/orderList/index')
        },
        {
            path: 'afterOrder/index',
            name: `${pre}afterOrder`,
            meta: {
                auth: ['admin-supplier-afterOrder'],
                title: '售后订单'
            },
            component: () => import('@/pages/supplier/afterOrder/index')
        },
        {
            path: 'orderStatistics/index',
            name: `${pre}orderStatistics`,
            meta: {
                auth: ['admin-supplier-supplier_list'],
                title: '订单统计'
            },
            component: () => import('@/pages/supplier/orderStatistics/index')
        },

    ]
};
