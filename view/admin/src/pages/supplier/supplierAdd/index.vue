<template>
<!-- 添加供应商 -->
  <div class="article-manager video-icon form-submit" id="shopp-manager">
    <div class="i-layout-page-header">
      <PageHeader class="product_tabs" hidden-breadcrumb>
        <div slot="title" class="acea-row row-middle">
          <router-link :to="{ path: '/admin/supplier/menu/list' }">
            <div class="font-sm after-line">
              <span class="iconfont iconfanhui"></span>
              <span class="pl10">返回</span>
            </div>
          </router-link>
          <span
            v-text="$route.params.id ? '编辑供应商' : '添加供应商'"
            class="mr20 ml16"
          ></span>
        </div>
      </PageHeader>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        class="formValidate mt20"
        ref="formValidate"
        :rules="ruleValidate"
        :model="formValidate"
        :label-width="width"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row :gutter="24" type="flex">
          <Col span="24">
            <FormItem label="供应商名称：" prop="supplier_name">
              <Input
                v-model="formValidate.supplier_name"
                placeholder="请输入供应商名称"
             v-width="460"
              />
            </FormItem>
          </Col>

          <!-- <Row :gutter="24" type="flex"> -->
          <Col span="24">
            <FormItem label="联系人姓名：" prop="name">
              <Input
                v-model="formValidate.name"
                placeholder="请输入联系人姓名"
          v-width="460"
              />
            </FormItem>
          </Col>
          <!-- </Row> -->

          <Col span="24">
            <FormItem label="联系电话：" prop="phone">
              <Input
                v-model="formValidate.phone"
                placeholder="请输入联系电话"
               v-width="460"
              />
            </FormItem>
          </Col>

      
          <Col span="24">
            <FormItem label="省市区地址" prop="addressSelect">
              <Cascader
                :data="addresData"
                :load-data="loadData"
                v-model="formValidate.addressSelect"
                @on-change="addchack"
                v-width="460"
              ></Cascader>
            </FormItem>
          </Col>
              <Col span="24">
            <FormItem label="供应商地址：" prop="detailed_address">
              <Input
                v-model="formValidate.detailed_address"
                placeholder="请输入供应商地址"
             v-width="460"
              />
            </FormItem>
          </Col>

          <Col span="24">
            <FormItem label="供应商邮箱：" prop="email">
              <Input
                v-model="formValidate.email"
                placeholder="请输入供应商邮箱"
               v-width="460"
              />
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="备注：" prop="mark">
              <Input
                type="textarea"
                v-model="formValidate.mark"
                placeholder="请输入..."
              v-width="460"
              />
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="供应商登录用户名：" prop="account">
              <Input
                v-model="formValidate.account"
                placeholder="请输入登录名称"
               v-width="460"
              />
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="供应商登录密码 ：" prop="pwd">
              <Input
                v-model="formValidate.pwd"
                placeholder="请输入登录密码"
              v-width="460"
              />
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="确认登录密码 ：" prop="conf_pwd">
              <Input
                v-model="formValidate.conf_pwd"
                placeholder="请确认登录密码"
            v-width="460"
              />
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="排序：">
              <InputNumber
               v-width="460"
                :min="0"
                :max="999999"
                v-model="formValidate.sort"
                placeholder="请输入排序"
              />
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="是否开启：">
              <i-switch
                v-model="formValidate.is_show"
                :true-value="1"
                :false-value="0"
                size="large"
              >
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </i-switch>
            </FormItem>
          </Col>
        </Row>
      </Form>
    </Card>
    <Card
      :bordered="false"
      dis-hover
      class="fixed-card"
      :style="{ left: `${!menuCollapse ? '200px' : isMobile ? '0' : '80px'}` }"
    >
      <Form>
        <FormItem>
          <Button
            type="primary"
            class="submission"
            @click="handleSubmit('formValidate')"
            >保存</Button
          >
        </FormItem>
      </Form>
    </Card>
  </div>
</template>
<script>
import { mapState, mapMutations } from 'vuex'
import { getSupplier, addSupplier, putSupplier } from '@/api/supplier'
import { cityApi } from '@/api/store'
export default {
  name: '',
  components: {},
  props: {},
  data() {
    return {
      width: 150,
      addresData: [],
      formValidate: {
        supplier_name: '',
        name: '',
        phone: '',
        detailed_address: '',
        address: '',
        addressSelect: [],
        email: '',
        mark: '',
        account: '',
        pwd: '',
        conf_pwd: '',
        is_show: 0,
        province: 0,
        city: 0,
        area: 0,
        street: 0,
      },
      ruleValidate: {
        supplier_name: [
          {
            required: true,
            message: '供应商不能为空',
            trigger: 'blur',
          },
        ],
        phone: [
          { required: true, message: '联系电话不能为空', trigger: 'blur' },
          {
            pattern: /^1[3456789]\d{9}$/,
            message: '手机号码格式不正确',
            trigger: 'blur',
          },
        ],
        account: [
          {
            required: true,
            message: '请输入用户名',
            trigger: 'blur',
          },
        ],
        address: [
          {
            required: true,
            message: '请填写具体地址',
            trigger: 'blur',
          },
        ],
        addressSelect: [
          {
            required: true,
            message: '请选择省市区',
            trigger: 'blur',
          },
        ],
        pwd: [
          {
            required: true,
            message: '请输入密码',
            trigger: 'blur',
          },
        ],
        conf_pwd: [
          {
            required: true,
            message: '密码不能为空',
            trigger: 'blur',
          },
        ],
      },
    }
  },
  computed: {
    ...mapState('admin/layout', ['isMobile', 'menuCollapse']),
    labelPosition() {
      return this.isMobile ? 'top' : 'right'
    },
  },
  watch: {},
  created() {
    let data = { pid: 0 }
    this.cityInfo(data)
    if (this.$route.params.id) {
      this.getSupplier()
    }
  },
  mounted() {},
  methods: {
    // 获取省市区
    cityInfo(data) {
      cityApi(data).then((res) => {
        this.addresData = res.data
      })
    },
    loadData(item, callback) {
      item.loading = true
      cityApi({ pid: item.value }).then((res) => {
        item.children = res.data
        item.loading = false
        callback()
      })
    },
    // 选择省市区
    addchack(e, selectedData) {
      e.forEach((i, index) => {
        if (index == 0) {
          this.formValidate.province = i
        } else if (index == 1) {
          this.formValidate.city = i
        } else if (index == 2) {
          this.formValidate.area = i
        } else {
          this.formValidate.street = i
        }
      })
      (this.formValidate.addressSelect)
      this.formValidate.address = selectedData.map((o) => o.label).join('')
    },
    // 添加供应商
    handleSubmit() {
      if (this.$route.params.id) {
        putSupplier(this.$route.params.id,this.formValidate)
          .then(async (res) => {
               this.$Message.success(res.msg)
                   this.$router.push({ path: '/admin/supplier/menu/list' })
          })
          .catch((res) => {
            this.$Message.error(res.msg)
          })
      } else {
        addSupplier(this.formValidate)
          .then(async (res) => {
            this.$Message.success(res.msg)
            this.$router.push({ path: '/admin/supplier/menu/list' })
          })
          .catch((res) => {
            this.$Message.error(res.msg)
            
          })
      }
    },
    // 获取供应商信息
    getSupplier() {
      getSupplier(this.$route.params.id)
        .then(async (res) => {
          this.formValidate = res.data
          let addressSelect = []
          if (res.data.province) {
            addressSelect.push(res.data.province)
          }
          if (res.data.city) {
            addressSelect.push(res.data.city)
          }
          if (res.data.area) {
            addressSelect.push(res.data.area)
          }
          if (res.data.street) {
            addressSelect.push(res.data.street)
          }
          this.formValidate.addressSelect = addressSelect
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
  },
}
</script>
<style scoped lang="less">
.fixed-card {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 200px;
  z-index: 99;
  box-shadow: 0 -1px 2px rgb(240, 240, 240);
}
.submission {
  margin-left: 50%;
}
</style>