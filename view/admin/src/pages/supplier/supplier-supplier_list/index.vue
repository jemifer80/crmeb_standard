<template>
<!-- 供应商-供应商列表 -->
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <Form
        inline
        class="formValidate mt20"
        :label-width="80"
        @submit.native.prevent
      >
        <FormItem label="搜索：" label-for="store_name">
          <Input
            placeholder="请输入供应商名称"
            v-model="keywords"
           class="input-add"
          />
          <Button type="primary" @click="getSupplier()">查询</Button>
        </FormItem>
      </Form>
    </Card>

    <Card :bordered="false" dis-hover class="ivu-mt">
      <!-- 操作 -->
      <Button type="primary" class="btn" @click="addSupplier"
        >添加供应商</Button
      >
      <!-- 表格 -->
      <Table
        :columns="columns"
        :data="tabList"
        ref="table"
        highlight-row
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
        class="orderData ivu-mt"
      >
        <!-- 状态 -->
        <template slot-scope="{ row, index }" slot="is_show">
          <i-switch
            v-model="row.is_show"
            @on-change="onchangeIsShow(row)"
            :true-value="1"
            :false-value="0"
            size="large"
          >
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </i-switch>
        </template>

        <!-- 操作 -->
        <template slot-scope="{ row, index }" slot="action">
          <a @click="goSupplier(row)">进入</a>
          <Divider type="vertical" />
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除供应商')" class="del">删除</a>
        </template>
        <!-- no-data-text="暂无数据" highlight-row -->
        <!-- no-filtered-data-text="暂无筛选结果" -->
      </Table>
      <div>
        <!-- 分页 -->
        <div class="acea-row row-right page">
          <Page
            :total="page.total"
            :current="page.pageNum"
            show-elevator
            show-total
            @on-change="pageChange"
            :page-size="page.pageSize"
            @on-page-size-change="limitChange"
            show-sizer
          />
        </div>
      </div>
    </Card>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import util from '@/libs/util'
import Setting from '@/setting'
import { supplierList, putSupplierStatus, supplierLogin } from '@/api/supplier'
import Template from '../../setting/devise/template.vue'
export default {
  name: '',
  components: { Template },
  props: {},
  data() {
    return {
      keywords: '',
      BaseURL: Setting.apiBaseURL.replace(/adminapi/, 'supplier/home/'),
      formValidate: {
        status: '',
        extract_type: '',
        nireid: '',
        data: 'thirtyday',
        page: 1,
        limit: 20,
      },
      loading: false,
      page: {
        total: 0, // 总条数
        pageNum: 1, // 当前页
        pageSize: 10, // 每页显示条数
      },
      columns: [
        {
          title: 'ID',
          key: 'id',
          minWidth: 80,
        },
        {
          title: '供应商',
          minWidth: 100,
          key: 'supplier_name',
        },
        {
          title: '联系人姓名',
          key: 'name',
          minWidth: 100,
        },
        {
          title: '联系方式',
          key: 'phone',
          minWidth: 100,
        },
        {
          title: '供应商状态',
          slot: 'is_show',
          minWidth: 100,
        },
        {
          title: '创建时间',
          key: '_add_time',
          minWidth: 100,
        },
        {
          title: '备注',
          key: 'mark',
          minWidth: 100,
        },
        {
          title: '排序',
          key: 'sort',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          minWidth: 150,
        },
      ],
      tabList: [],
    }
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right'
    },
  },
  watch: {},
  created() {
    this.getSupplier()
  },
  mounted() {},
  methods: {
    addSupplier() {
      this.$router.push({ path: '/admin/supplier/supplierAdd' })
    },

    pageChange(index) {
      this.page.pageNum = index
      this.getSupplier()
    },

    limitChange(limit) {
      this.page.pageSize = limit
      this.getSupplier()
    },

    // 获取供应商列表
    getSupplier() {
      let data = {
        keywords: this.keywords,
        page: this.page.pageNum, // 当前页
        limit: this.page.pageSize, // 每页显示条数
      }
      supplierList(data)
        .then(async (res) => {
          this.tabList = res.data.list
          if (res.data.status == 200) {
          }
          this.page.total = res.data.count
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },

    // 编辑
    edit(row) {
      this.$router.push({ path: '/admin/supplier/supplierAdd/' + row.id })
    },

    // 修改状态
    onchangeIsShow(row) {
      putSupplierStatus(row.id, row.is_show)
        .then(async (res) => {
          this.$Message.success(res.msg)
        })
        .catch((res) => {
          this.$$Message.error(res.msg)
        })
    },

    // 进入供应商
    goSupplier(row) {
      supplierLogin(row.id)
        .then(async (res) => {
          let data = res.data
          let expires = data.expires_time
          util.cookies.setSupplier('token', data.token, {
            expires: expires,
          })
          util.cookies.setSupplier('uuid', data.user_info.id, {
            expires: expires,
          })
          util.cookies.setSupplier('expires_time', expires, {
            expires: expires,
          })
          let storage = window.localStorage
          storage.setItem('menuListSupplier', JSON.stringify(data.menus))
          storage.setItem(
            'uniqueAuthSupplier',
            JSON.stringify(data.unique_auth)
          )
          let userInfoSupplier = {
            account: data.user_info.account,
            head_pic: data.user_info.avatar,
            logo: data.logo,
            logoSmall: data.logo_square,
            version: data.version,
          }
          storage.setItem('userInfoSupplier', JSON.stringify(userInfoSupplier))
          window.open(this.BaseURL)
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },

    // 删除
    del(row, tit) {
      let data = {
        ids: row.id,
      }
      let delfromData = {
        title: tit,
        num: 0,
        url: `/supplier/supplier/${data.ids}`,
        method: 'DELETE',
        ids: data,
      }
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg)
          this.getSupplier()
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
  },
}
</script>
<style scoped lang="less">
.input-add {
 width: 250px;
 margin-right: 14px
}
.btn {
  margin: 0 0 10px 0px;
}
.del {
  margin-left: 10px;
}
// .card {
//   margin-top: 15px;
// }
.ivu-form-inline {
  padding-top: 20px;
}
</style>