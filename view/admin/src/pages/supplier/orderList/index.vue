<template>
<!-- 供应商-订单管理 -->
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="10">
      <Form
        class="formValidate mt20"
        ref="formValidate"
        :rules="ruleValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
      >
        <!-- 时间选择 -->
        <Row>


          <Col v-bind="grid">
            <FormItem label="时间选择：" >
              <Date-picker
                 :editable="false"
                :clearable="true"
                @on-change="onchangeTime"
                :value="timeVal"
                format="yyyy/MM/dd HH:mm:ss"
                type="datetimerange"
                placement="bottom-start"
                placeholder="选择时间"
                class="input-add mr20"
                :options="options"
              ></Date-picker>
            </FormItem>
          </Col>
          <!-- 供应商 -->
          <Col v-bind="grid" class="colhsh">
            <FormItem label="供应商：">
              <Select clearable v-model="formValidate.supplier_id" class="input-add"
                >
                <Option
                  v-for="item in supplierName"
                  :value="item.id"
                  :key="item.id"
                  >{{ item.supplier_name }}</Option
                >
              </Select>
            </FormItem>
          </Col>
          <!-- 订单类型 -->
          <Col v-bind="grid">
            <FormItem label="订单类型：">
              <Select clearable v-model="formValidate.type" class="input-add"
                >
                <Option
                  v-for="item in typeList"
                  :value="item.val"
                  :key="item.label"
                  >{{ item.label }}</Option
                >
              </Select>
            </FormItem>
          </Col>
        </Row>
        <Row>
          <!-- 支付方式 -->
          <!-- <Row> -->
          <!-- <Col span="8"> -->
          <Col v-bind="grid">
            <FormItem label="订单搜索：">
                 <Input
                  v-model="formValidate.real_name"
                  placeholder="请输入"
                  element-id="name"
                  clearable
                  class="input-add"
									maxlength="20"
              >
                <Select
                    v-model="formValidate.field_key"
                    slot="prepend"
                    style="width:70px;"
                    default-label="全部"
                >
                  <Option value="all">全部</Option>
                  <Option value="order_id">订单号</Option>
                  <Option value="uid">用户UID</Option>
                  <Option value="real_name">用户姓名</Option>
                  <Option value="user_phone">用户电话</Option>
                  <Option value="title">商品名称</Option>
                  <Option value="total_num">商品件数</Option>
                </Select>
              </Input>

            </FormItem>
              </Select>
            </FormItem>
          </Col>
          <!-- 订单状态 -->
          <!-- <Col span="6"> -->
          <Col v-bind="grid">
            <FormItem label="订单状态：">
              <Select clearable v-model="formValidate.status" class="input-add"
                >
<!--                 <Option value="" >全部</Option>-->
                <Option value="0">未支付</Option>
                <Option value="1">未发货</Option>
                <Option value="2">待收货</Option>
                <Option value="3">待评价</Option>
                <Option value="4">交易完成</Option>
                <Option value="-4">已删除</Option>
              </Select>
            </FormItem>
          </Col>
          <!-- 订单搜索 -->
          <!-- <Col span="10"> -->
          <Col span="10">
             <FormItem label="支付方式：" prop="real_name" label-for="real_name">
              <Select clearable v-model="formValidate.pay_type" class="input-add"
                >
                 <Option
                  v-for="item in payList"
                  :value="item.val"
                  :key="item.val"
                  >{{ item.label }}</Option
                >
              </Select>
                 <Button
                type="primary"
                @click="getList()"
                class="btn"
                >查询</Button
              >

              <Button class="clickBtn" @click="reset">重置</Button>
            </FormItem>
          </Col>
          <Col> </Col>
        </Row>
      </Form>
    </Card>
    <!-- 表格 -->
    <Card :bordered="false" dis-hover class="ivu-mt" >
      <tabList :orderList="orderList" :columns="columns" :page="page" @getList = "getList"   @changeGetTabs="changeGetTabs" />
    </Card>
  </div>
</template>
<script>
import tabList from '../components/tableList.vue'
import expandRow from "@/pages/order/orderList/components/tableExpand.vue";
import { mapState, mapMutations } from 'vuex'
import timeOptions from '@/utils/timeOptions'
import { getSupplierList, getList } from '@/api/supplier'


export default {
  name: '',
  components: {
    tabList,
    expandRow
  },
  props: {},
  data() {
    return {
      options: timeOptions,
      ruleValidate: {},
      formValidate: {
        status: '', // 订单状态
        pay_type: '', // 支付方式
        data: '', // 时间
        field_key: '', //订单搜索
        real_name: '', // 订单搜索内容
        type: '', // 订单类型
        supplier_id: '', // 供应商id
      },
      page: {
        total: 0, // 总条数
        pageNum: 1, // 当前页
        pageSize: 10, // 每页显示条数
      },
      grid: {
        xl: 7,
        lg: 12,
        md: 24,
        sm: 24,
        xs: 24,
      },
      timeVal: [],
      supplierName: [],
      typeList: [
        // { label: '全部订单', val: '' },
        { label: '普通订单', val: '0' },
        { label: '秒杀订单', val: '1' },
        { label: '拼团订单', val: '3' },
        { label: '砍价订单', val: '2' },
        { label: '预售商品', val: '8' },
      ],
      payList: [
        // { label: '全部', val: '' },
        { label: '微信支付', val: '1' },
        { label: '支付宝支付', val: '4' },
        { label: '余额支付', val: '2' },
        { label: '线下支付', val: '3' },
      ],
      cityList: [
        {
          value: 'beijing',
          label: '北京市',
        },
        {
          value: 'shanghai',
          label: '上海市',
        },
        {
          value: 'shenzhen',
          label: '深圳市',
        },
        {
          value: 'hangzhou',
          label: '杭州市',
        },
      ],
      orderList: [],

      columns: [
          {
          type: "expand",
          width: 30,
          render: (h, params) => {
            return h(expandRow, {
              props: {
                row: params.row,
              },
            });
          },
        },
        // {
        //   type: 'selection',
        //   width: 60,
        //   align: 'center'
        // },
        {
          title: '订单号',
          align: 'center',
          slot: 'order_id',
          minWidth: 180,
        },
        {
          title: '商品信息',
          slot: 'info',
          minWidth: 330,
            align: 'left',
        },
        {
          title: '供应商名称',
          key: 'supplier_name',
          minWidth: 80,
        },
        {
          title: '用户信息',
          slot: 'nickname',
          minWidth: 100,
        },
        {
          title: '实际支付',
          key: 'pay_price',
          minWidth: 70,
        },
        {
          title: '支付方式',
          key: 'pay_type_name',
          minWidth: 110,
        },
        {
          title: '支付时间',
          key: '_pay_time',
          minWidth: 150,
        },
        {
          title: '订单类型',
          key: 'pink_name',
          minWidth: 120,
        },
        {
          title: '订单状态',

          slot: 'statusName',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 170,
          align: 'left',
        },
      ],
    }
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 96
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right'
    },
  },
  watch: {},
  created() {
    this.getSupplierList()
    this.getList()
  },
  mounted() {},
  methods: {

    // 选择时间
    onchangeTime(e) {
      if (e[1].slice(-8) === '00:00:00') {
        e[1] = e[1].slice(0, -8) + '23:59:59'
        this.timeVal = e
      } else {
        this.timeVal = e
      }
      this.formValidate.data = this.timeVal[0] ? this.timeVal.join('-') : ''
    },
    changeGetTabs() {
      this.$refs.table.getTabs()
    },

    // 获取供应商内容
    getSupplierList() {
      getSupplierList()
        .then(async (res) => {
          this.supplierName = res.data
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },

    // 获取订单列表
    getList() {
      let data = {
        status: this.formValidate.status,
        pay_type: this.formValidate.pay_type,
        data: this.formValidate.data,
        field_key: this.formValidate.field_key,
        real_name: this.formValidate.real_name,
        supplier_id: this.formValidate.supplier_id,
        type: this.formValidate.type,
        page: this.page.pageNum,
        limit: this.page.pageSize,
      }

      getList(data)
        .then(async (res) => {
          this.orderList = res.data.data
          this.page.total = res.data.count
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },

    // 重置
    reset() {
      this.formValidate = {}
      this.formValidate.data = ''
      this.timeVal = []
      this.getList()
    },
  },
}
</script>
<style scoped lang="less">
.input-add {
 width: 250px
}
.btn {
  margin-left: 10px;
}
.clickBtn {
  margin-left: 5px;
  font-size: 24px;
  font-weight: 500;
  color: #666666;
}
// .colhsh {
//   margin-left: -20px;
// }
</style>
