<template>
<!-- 供应商-订单统计 -->
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <!-- <div class="new_card_pd"> -->
        <Form
          class="formValidate mt20 card"
          ref="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          :model="formValidate"
          inline
        >
          <FormItem label="时间选择：">
            <DatePicker
              :editable="false"
              :clearable="false"
              @on-change="onchangeTime"
              :value="timeVal"
              format="yyyy/MM/dd"
              type="datetimerange"
              placement="bottom-start"
              placeholder="自定义时间"
               class="input-add"
              :options="options"
            ></DatePicker>
          </FormItem>

          <!-- 供应商 -->

          <FormItem label="供应商：">
            <Select
              v-model="formValidate.supplier_id"
              @on-change="selectChange"
               class="input-add"
            >
              <Option
                v-for="item in supplierName"
                :value="item.id"
                :key="item.id"
                >{{ item.supplier_name }}</Option
              >
            </Select>
          </FormItem>
        </Form>
      <!-- </div> -->
    </Card>
    <Cardbox :cardLists="cardLists"></Cardbox>
    <!-- 趋势图 -->
    <Card  :bordered="false" dis-hover class="ivu-mt">
      <h3>营业趋势</h3>
      <Echarts
        :styles="style"
         height="100%" 
      width="100%" 
        :option-data="optionData"
     v-if="optionData"
     
      ></Echarts> 
    </Card>
    <!-- 订单来源 -->
    <!-- <div class="code-row-bg"> -->
      <Row :gutter="24"  class="ivu-mt">
        <Col :xl="12" :lg="12" :md="24" :sm="24" :xs="24">
      <Card :bordered="false" dis-hover class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <h3 class="header-title">订单来源统计</h3>
          <div class="change-style">
            <div
              class="change_type"
              :class="{ active: active == 1 }"
              @click="one(1)"
            >
              <Icon type="ios-pie" />
              
          
            </div>
            <div
              class="change_type"
              :class="{ active: active == 2 }"
              @click="one(2)"
            >
              <Icon type="ios-apps" />
            </div>
          </div>
        </div>
        <div class="ech-box">
          <echarts-from
            :infoList="infoList"
            echartsTitle="circle"
            :styles="styles"
            v-if="active == 1"
          ></echarts-from>
          <Table
            v-else
            ref="selection"
            :columns="columns"
            :data="tabList"
            :loading="loading"
            height="400"
            no-data-text="暂无数据"
            highlight-row
            no-filtered-data-text="暂无筛选结果"
          >
            <template slot-scope="{ row }" slot="percent">
              <div class="percent-box">
                <div class="line">
                  <div class="bg"></div>
                  <div
                    class="percent"
                    :style="'width:' + row.percent + '%;'"
                  ></div>
                </div>
                <div class="num">{{ row.percent }}%</div>
              </div>
            </template>
          </Table>
        </div>
      </Card>
      </Col>
      <!-- 订单类型 -->
         <Col :xl="12" :lg="12" :md="24" :sm="24" :xs="24">
      <Card :bordered="false" dis-hover class="ivu-mt">

        <div class="acea-row row-between-wrapper">
          <h3 class="header-title">订单类型分析</h3>
          <div class="change-style">
            <div
              class="change_type"
              :class="{ active: active2 == 3 }"
              @click="activeTwo(3)"
            >
              <Icon type="ios-pie" />
            </div>

            <div
              class="change_type"
              :class="{ active: active2 == 4 }"
              @click="activeTwo(4)"
            >
              <Icon type="ios-apps" />
            </div>
          </div>
        </div>
        <div class="ech-box">
          <echarts-from
            ref="visitChart"
            :infoList="infoList2"
            echartsTitle="circle"
            :styles="styles"
            v-if="active2 == 3"
          ></echarts-from>
          <Table
            v-else
            ref="selection"
            :columns="columns"
            :data="tabList2"
            no-data-text="暂无数据"
            :loading="loading"
            height="400"
            highlight-row
            no-filtered-data-text="暂无筛选结果"
          >
            <template slot-scope="{ row }" slot="percent">
              <div class="percent-box">
                <div class="line">
                  <div class="bg"></div>
                  <div
                    class="percent"
                    :style="'width:' + row.percent + '%;'"
                  ></div>
                </div>
                <div class="num">{{ row.percent }}%</div>
              </div>
            </template>
          </Table>
        </div>
      </Card>
       </Col>
      </Row>
    <!-- 供应商数据统计 -->
    <Card :bordered="false" dis-hover class="ivu-mt">
      <h3 class="supplier">供应商数据统计</h3>
      <Table
        ref="selection"
        :columns="supplierColumns"
        :data="supplierList"
        no-data-text="暂无数据"
        highlight-row
        :loading="loading"
     
        no-filtered-data-text="暂无筛选结果"
      >
      </Table>
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
    </Card>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import Cardbox from '@/components/cards/cards.vue'
import Echarts from '@/components/echartsNew/index.vue'
import echartsFrom from '@/components/echarts/index'
import {
  homeHeader,
  getSupplierList,
  homeOrder,
  orderChannel,
  orderType,
  homeSupplier,
} from '@/api/supplier'
import { formatDate } from '@/utils/validate'
export default {
  name: '',
  components: {
    Cardbox,
    Echarts,
    echartsFrom,
  },
  props: {},
  data() {
    return {
      active: 1,
      active2: 3,

      options: {
        shortcuts: [
          {
            text: '今天',
            value() {
              const end = new Date()
              const start = new Date()
              start.setTime(
                new Date(
                  new Date().getFullYear(),
                  new Date().getMonth(),
                  new Date().getDate()
                )
              )
              return [start, end]
            },
          },
          {
            text: '昨天',
            value() {
              const end = new Date()
              const start = new Date()
              start.setTime(
                start.setTime(
                  new Date(
                    new Date().getFullYear(),
                    new Date().getMonth(),
                    new Date().getDate() - 1
                  )
                )
              )
              end.setTime(
                end.setTime(
                  new Date(
                    new Date().getFullYear(),
                    new Date().getMonth(),
                    new Date().getDate() - 1
                  )
                )
              )
              return [start, end]
            },
          },
          {
            text: '最近7天',
            value() {
              const end = new Date()
              const start = new Date()
              start.setTime(
                start.setTime(
                  new Date(
                    new Date().getFullYear(),
                    new Date().getMonth(),
                    new Date().getDate() - 6
                  )
                )
              )
              return [start, end]
            },
          },
          {
            text: '最近30天',
            value() {
              const end = new Date()
              const start = new Date()
              start.setTime(
                start.setTime(
                  new Date(
                    new Date().getFullYear(),
                    new Date().getMonth(),
                    new Date().getDate() - 29
                  )
                )
              )
              return [start, end]
            },
          },
          {
            text: '本月',
            value() {
              const end = new Date()
              const start = new Date()
              start.setTime(
                start.setTime(
                  new Date(new Date().getFullYear(), new Date().getMonth(), 1)
                )
              )
              return [start, end]
            },
          },
          {
            text: '本年',
            value() {
              const end = new Date()
              const start = new Date()
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), 0, 1))
              )
              return [start, end]
            },
          },
        ],
      },
      formValidate: {
        supplier_id: '',
        data: '',
      },
      timeVal: [],
      optionData: {}, // 趋势图
      infoList: {}, //  订单来源
      infoList2: {}, // 订单类型
      loading: false,
      loading2: false,
      style: { height: '400px' },
      styles: { height: '400px' },
      columns: [
        {
          title: '序号',
          type: 'index',
          width: 60,
          align: 'center',
        },
        {
          title: '来源',
          key: 'name',
          minWidth: 80,
          align: 'center',
        },
        {
          title: '金额',
          width: 100,
          key: 'value',
          align: 'center',
        },
        {
          title: '占比率',
          slot: 'percent',
          minWidth: 180,
          align: 'center',
        },
      ],
      tabList: [],
      tabList2: [],
      cardLists:[],
      supplierColumns: [
        {
          title: '供应商信息',
          key: 'supplier_name',
          align: 'left',
        },
        {
          title: '订单金额',
          key: 'order_price',
          align: 'left',
        },
        {
          title: '订单数',
          key: 'order_count',
          align: 'left',
        },
        {
          title: '退款金额',
          key: 'refund_order_price',
          align: 'left',
        },
        {
          title: '退款订单数',
          key: 'refund_order_count',
          align: 'left',
        },
      ],
      supplierName: [],
      supplierList: [],
      page: {
        total: 0, // 总条数
        pageNum: 1, // 当前页
        pageSize: 10, // 每页显示条数
      },
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
    this.homeHeader()
    this.getSupplierList()
    this.homeOrder()
    this.orderChannel()
    this.orderType()
    this.homeSupplier()
    const end = new Date()
    const start = new Date()
    start.setTime(
      start.setTime(
        new Date(
          new Date().getFullYear(),
          new Date().getMonth(),
          new Date().getDate() - 29
        )
      )
    )
    this.timeVal = [start, end]

    this.formValidate.data =
      formatDate(start, 'yyyy/MM/dd') + '-' + formatDate(end, 'yyyy/MM/dd')
  },
  mounted() {},
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.formValidate.data = this.timeVal.join('-')
      this.homeHeader()
      this.homeOrder()
      this.orderChannel()
      this.orderType()
      this.homeSupplier()
    },
    // 选择供应商
    selectChange() {
      this.homeHeader()
      this.homeOrder()
      this.orderChannel()
      this.orderType()
      this.homeSupplier()
    },
    // 分页方法
    pageChange(index) {
      this.page.pageNum = index
      thie.homeSupplier()
    },
    limitChange(limit) {
      this.page.pageSize = limit
      this.homeSupplier()
    },
    one(num) {
      this.active = num
    },
    activeTwo(num) {
      this.active2 = num
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
    // 首页数据
    homeHeader() {
      homeHeader(this.formValidate)
        .then(async (res) => {
          let arr = ['pay_price', 'pay_count', 'refund_price', 'refund_count']
          this.cardLists.map((i, index) => {
            i.count = res.data[arr[index]]
          })
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
    // 趋势图
    homeOrder() {
      homeOrder(this.formValidate)
        .then(async (res) => {
          let legend = res.data.series.map((item) => {
            return item.name
          })
          let xAxis = res.data.xAxis
          let col = ['#5B8FF9', '#5AD8A6', '#FFAB2B', '#5D7092']
          let series = []
          res.data.series.map((item, index) => {
            series.push({
              name: item.name,
              type: 'line',
              data: item.data,
              itemStyle: {
                normal: {
                  color: col[index],
                },
              },
              smooth: 0,
            })
          })
          this.optionData = {
            tooltip: {
              trigger: 'axis',
              axisPointer: {
                type: 'cross',
                label: {
                  backgroundColor: '#6a7985',
                },
              },
            },
            legend: {
              x: 'center',
              data: legend,
            },
            grid: {
              left: '3%',
              right: '4%',
              bottom: '3%',
              containLabel: true,
            },
            toolbox: {
            	show: true,
								right: '2%',
              feature: {
              
                saveAsImage: {
                  name: '营业趋势_'+formatDate(new Date(Number(new Date().getTime())), 'yyyyMMddhhmmss')
                }
              },
            },
            xAxis: {
              type: 'category',
              boundaryGap: true,

              axisLabel: {
                interval: 0,
                rotate: 40,
                textStyle: {
                  color: '#000000',
                },
              },
              data: xAxis,
            },
            yAxis: {
              type: 'value',
              axisLine: {
                show: false,
              },
              axisTick: {
                show: false,
              },
              axisLabel: {
                textStyle: {
                  color: '#7F8B9C',
                },
              },
              splitLine: {
                show: true,
                lineStyle: {
                  color: '#F5F7F9',
                },
              },
            },
            series: series,
          }
          this.spinShow = false
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
    // 订单来源
    orderChannel() {
      this.loading = true
      orderChannel(this.formValidate)
        .then(async (res) => {
          this.infoList = res.data
          this.tabList = res.data.list
          this.loading = false
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
    // 订单类型
    orderType() {
      this.loading2 = true
      orderType(this.formValidate)
        .then(async (res) => {
          this.infoList2 = res.data
          this.tabList2 = res.data.list
          this.loading2 = false
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
    // 供应商数据
    homeSupplier() {
      homeSupplier(this.formValidate)
        .then(async (res) => {
          this.supplierList = res.data
          this.page.total =  this.supplierList.length
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
}
.code-row-bg {
  display: flex;
  flex-wrap: nowrap;
  height: 450px;
  width: 100%;
 
}
.code-row-bg .ivu-mt {
  width: 100%;
  margin: 0 5px;
}
.ech-box {
  margin-top: 20px;
    // height: 600px;


}
.percent-box {
  display: flex;
  align-items: center;
  padding-right: 24px;
}
.line {
  width: 100%;
  position: relative;
}
.bg {
  position: absolute;
  width: 100%;
  height: 8px;
  border-radius: 8px;
  background-color: #f2f2f2;
}
.percent {
  position: absolute;
  border-radius: 5px;
  height: 8px;
  background-color: #1890ff;
  z-index: 9999;
}
.num {
  white-space: nowrap;
  margin: 0 10px;
  width: 15px;
}
.change-style {
  display: flex;
  .change_type {
    text-align: center;
    width: 54px;
    height: 32px;
    font-size: 20px;
   border-radius: 1px;
    border: 1px solid rgba(0, 0, 0, 0.15);
  }
}
.active {
  color: #1890ff;
border-radius: 1px;
  // border: 2px solid #1890ff !important;
box-shadow: 0px 0px 1px 1px #1890ff;
}
.supplier {
  margin-bottom: 10px;
}

.new_card_pd {

  
  display: flex;
  //  padding: 20px 20px 0;
  margin-top: 90px;
}
.card {
padding-top: 20px;
}

h3 {
 
height: 12px;
line-height: 10px;
padding: 6px 10px 15px 10px;
border-left: 3px solid #1890ff;
}
</style>