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

const pre = 'order_';

export default {
	path: '/supplier/order/',
	name: 'order',
	header: 'order',
	meta: {
	    // 授权标识
	    auth: ['supplier-order']
	},
	redirect: {
		name: `${pre}list`
	},
	component: BasicLayout,
	children: [
		{
			path: 'list',
			name: `${pre}list`,
			meta: {
				auth: ['supplier-order-list'],
				title: '订单列表'
			},
			component: () => import('@/pages/order/orderList/index')
		},
		{
			path: 'refund',
			name: `${pre}refund`,
			meta: {
				auth: ['supplier-order-refund'],
				title: '售后退款'
			},
			component: () => import('@/pages/order/refund/index')
		}
	]
};
