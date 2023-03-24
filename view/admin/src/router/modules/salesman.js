import BasicLayout from '@/layouts/basic-layout';

const pre = 'salesman_';

export default {
  path: '/admin/salesman',
  name: 'salesman',
  header: 'salesman',
  meta: {
    title: '业务员',
    auth: ['admin-salesman'],
  },
  redirect: {
    name: `${pre}list`,
  },
  component: BasicLayout,
  children: [
    {
      path: 'list',
      name: `${pre}list`,
      meta: {
        title: '业务员列表',
        auth: ['admin-salesman-list'],
      },
      component: () => import('@/pages/salesman/salesmanList/index'),
    }
  ]
};