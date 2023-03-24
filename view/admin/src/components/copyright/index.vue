<template>
  <!-- 底部企业logo -->
  <GlobalFooter class="i-copyright" :links="links" :copyright="copyright" />
</template>
<script>
import { getCrmebCopyRight } from '@/api/system'
export default {
  name: 'i-copyright',
  data() {
    return {
      links: [
        {
          title: '官网',
          key: '官网',
          href: 'https://www.crmeb.com',
          blankTarget: true,
        },
        {
          title: '社区',
          key: '社区',
          href: 'http://q.crmeb.com',
          blankTarget: true,
        },
        {
          title: '文档',
          key: '文档',
          href: 'http://doc.crmeb.com',
          blankTarget: true,
        },
      ],
      copyright: '',
    }
  },
  mounted() {
    this.getCopyRight()
  },
  methods: {
    //获取版权信息
    getCopyRight() {
      getCrmebCopyRight().then((res) => {
        let copyrightText = res.data.copyrightContext
          ? res.data.copyrightContext
          : 'Copyright © 2014-2022 '
        this.$store
          .dispatch('admin/db/get', {
            dbName: 'sys',
            path: 'user.info',
            user: true,
          })
          .then((data) => {
            copyrightText += (data.version && !res.data.copyrightContext) ?data.version : ''
            this.copyright = copyrightText
          })
      })
    },
  },
}
</script>
<style lang="less">
.i-copyright {
  flex: 0 0 auto;
  z-index: 1;
}
</style>
