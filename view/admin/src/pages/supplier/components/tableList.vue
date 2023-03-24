<template>
  <div>
    <Table
      :columns="columns"
      :data="orderList"
      ref="table"
      highlight-row
      no-data-text="暂无数据"
      no-filtered-data-text="暂无筛选结果"
      class="orderData ivu-mt"
    >
      <!-- 订单号 -->
      <template slot-scope="{ row }" slot="order_id">
        <Tooltip
          theme="dark"
          max-width="300"
          :delay="600"
          content="用户已删除"
          v-if="row.is_del === 1 && row.delete_time == null"
        >
          <span style="color: #ed4014; display: block">{{ row.order_id }}</span>
        </Tooltip>
        <span
          @click="changeMenu(row, '2')"
          v-else
          style="color: #2d8cf0; display: block; cursor: pointer"
          >{{ row.order_id }}</span
        >
      </template>
      <!-- 商品信息 -->
      <template slot-scope="{ row }" slot="info">
        <Tooltip theme="dark" max-width="300" :delay="600">
          <div class="tabBox" v-for="(val, i) in row._info" :key="i">
            <div class="tabBox_img" v-viewer>
              <img
                v-lazy="
                  val.cart_info.productInfo.attrInfo
                    ? val.cart_info.productInfo.attrInfo.image
                    : val.cart_info.productInfo.image
                "
              />
            </div>
            <span class="tabBox_tit line1">
              <span class="font-color-red" v-if="val.cart_info.is_gift"
                >赠品</span
              >
              {{ val.cart_info.productInfo.store_name + ' | ' }}
              {{val.cart_info.productInfo.attrInfo ? val.cart_info.productInfo.attrInfo.suk : ''}}
            </span>
          </div>
          <div slot="content">
            <div v-for="(val, i) in row._info" :key="i">
              <p class="font-color-red" v-if="val.cart_info.is_gift">赠品</p>
              <p>{{ val.cart_info.productInfo.store_name }}</p>
              <p>{{ val.cart_info.productInfo.attrInfo ? val.cart_info.productInfo.attrInfo.suk : ''}}</p>
              <p class="tabBox_pice">{{'￥' + val.cart_info.sum_price + ' x ' + val.cart_info.cart_num}}</p>
            </div>
          </div>
        </Tooltip>
      </template>
      <!-- 用户信息 -->
      <template slot-scope="{ row }" slot="nickname">
        <a @click="showUserInfo(row)">{{ row.nickname }}</a>
        <span style="color: #ed4014" v-if="row.delete_time != null">
          (已注销)</span
        >
      </template>
      <!-- 订单列表状态 -->
      <template slot-scope="{ row }" slot="statusName">
        <Tag color="default" size="medium" v-show="row.status == 3">{{
          row.status_name.status_name
        }}</Tag>
        <Tag color="orange" size="medium" v-show="row.status == 4">{{
          row.status_name.status_name
        }}</Tag>
        <Tag
          color="orange"
          size="medium"
          v-show="row.status == 1 || row.status == 2"
          >{{ row.status_name.status_name }}</Tag
        >
        <Tag color="red" size="medium" v-show="row.status == 0">{{
          row.status_name.status_name
        }}</Tag>
        <Tag
          color="orange"
          size="medium"
          v-if="!row.is_all_refund && row.refund.length"
          >部分退款中</Tag
        >
        <Tag
          color="orange"
          size="medium"
          v-if="row.is_all_refund && row.refund.length && row.refund_type != 6"
          >退款中</Tag
        >
        <div class="pictrue-box" size="medium" v-if="row.status_name.pics">
          <div
            v-viewer
            v-for="(item, index) in row.status_name.pics || []"
            :key="index"
          >
            <img class="pictrue mr10" v-lazy="item" :src="item" />
          </div>
        </div>
      </template>
      <!-- 订单状态 -->
      <!-- 退款状态 -->
      <template slot-scope="{ row }" slot="refund_type">
        <Tag color="blue" size="medium" v-if="row.refund_type == 1">仅退款</Tag>
        <Tag color="blue" size="medium" v-if="row.refund_type == 2"
          >退货退款</Tag
        >
        <Tag color="red" size="medium" v-if="row.refund_type == 3"
          >拒绝退款</Tag
        >
        <Tag color="blue" size="medium" v-if="row.refund_type == 4"
          >商品待退货</Tag
        >
        <Tag color="blue" size="medium" v-if="row.refund_type == 5"
          >退货待收货</Tag
        >
        <Tag color="green" size="medium" v-if="row.refund_type == 6"
          >已退款</Tag
        >
      </template>
      <template slot="refundStatus">
        <Tag type="border" color="red">仅退款</Tag>
      </template>

      <!-- 操作 -->
      <template slot-scope="{ row }" slot="action">
        <a @click="changeMenu(row, '2')">详情</a>
        <Divider
          type="vertical"
          v-if="row.status_name.status_name === '未发货'"
        />
        <a
          @click="btnClick(row)"
          v-if="row.status_name.status_name === '未发货'"
          >提醒发货</a
        >
        <!-- <a
          @click="distributionInfo(row)"
          v-if="row.status_name.status_name === '未发货'"
          >打印</a
        >
        <a
          @click="btnClick(row)"
          v-if="row.status_name.status_name === '未发货'"
          >配货单</a
        > -->

      </template>

      <template slot-scope="{ row }" slot="refund_action">
        <a>立即退款</a>
        <Divider type="vertical" />
        <a @click="actionFn(row)">详情</a>
      </template>
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
    <!-- 详情 -->
    <Supplierdetails
      ref="detailss"
      :orderDatalist="orderDatalist"
      :orderId="orderId"
      :row-active="rowActive"
      :openErp="openErp"
      :formType="1"
    />
    <!-- 分配 -->
    <Distribution ref="distshow"></Distribution>
    <!-- 发送货 -->
    <order-send ref="send"></order-send>
    <!-- 备注 -->
    <order-remark
      ref="remarks"
      :orderId="orderId"
      @submitFail="submitFail"
    ></order-remark>
    <!-- 编辑 退款 退积分 不退款-->
    <!-- <edit-from ref="edits"></edit-from> -->
    <!-- 会员详情-->
    <user-details ref="userDetails" fromType="order"></user-details>
    <!-- 记录 -->
    <order-record ref="record"></order-record>
  </div>
</template>
<script>
import Template from '../../setting/devise/template.vue'
import Supplierdetails from './supplierDetails.vue'
import orderSend from '@/pages/order/orderList/handle/orderSend.vue'
import orderRecord from '@/pages/order/orderList/handle/orderRecord.vue'
import orderRemark from '@/pages/order/orderList/handle/orderRemark.vue'
import Distribution from '@/pages/order/orderList/components/distribution.vue'
import expandRow from "@/pages/order/orderList/components/tableExpand.vue";
// import editFrom from '@/components/from/from'
import userDetails from '@/pages/user/list/handle/userDetails'
import { orderInfo, getRefundFrom, deliverRemind } from '@/api/supplier'
import { erpConfig } from '@/api/erp'
export default {
  name: '',

  components: {
    Template,
    Supplierdetails,
    orderSend,
    orderRemark,
    Distribution,
    // editFrom,
    expandRow,
    userDetails,
    orderRecord,
  },
  props: {
    orderList: {
      type: Array,
      default: [],
    },
    columns: {
      type: Array,
      default: [],
    },
    page: {
      type: Object,
      default: {},
    },
  },
  data() {
    return {
      delfromData: {},
      FromData: {},
      rowActive: {},
      orderDatalist: null,
      orderId: 0,
      remind: 1,
      openErp: false,
    }
  },
  computed: {},
  watch: {},
  created() {
    this.getErpConfig()
  },
  mounted() {},
  methods: {
    pageChange(index) {
      this.page.pageNum = index
      this.$emit('getList')
    },
    limitChange(limit) {
      this.page.pageSize = limit
      this.$emit('getList')
    },

    //erp配置
    getErpConfig() {
      erpConfig()
        .then((res) => {
          this.openErp = res.data.open_erp
        })
        .catch((err) => {
          this.$Message.error(err.msg)
        })
    },

    // 修改成功
    submitFail(type) {
      this.status = 0
      this.$emit('getList')
      if (this.orderConNum != 1) {
        this.getData(this.orderId, 1)
      } else {
        this.$refs.detailss.getSplitOrder(this.orderConId)
      }
      if (type) {
        this.$emit('changeGetTabs')
      }
    },

    showUserInfo(row) {
      this.$refs.userDetails.modals = true
      this.$refs.userDetails.activeName = 'info'
      this.$refs.userDetails.getDetails(row.uid)
    },

    // 提醒发货
    btnClick(row) {
      let data = {
        supplier_id: row.supplier_id,
        id: row.id,
      }
      deliverRemind(data)
        .then(async (res) => {
          this.$Message.success(res.msg)
          this.$emit('getList')
        })
        .catch((res) => {
          this.$Message.error(res.msg)
          if (res.status == '400') {
            row.remind = 2
          }
        })
    },
    
    // 打印
    distributionInfo(id) {
      distributionInfo(id).then(async(res)=>{

      }).catch()

    },

    // 小票打印
    changeMenu(row, name, num) {
      this.orderId = row.id
      this.orderConId = row.pid>0?row.pid:row.id;
      this.orderConNum = num
      switch (name) {
        // 立即支付
        case '1':
          this.delfromData = {
            title: '修改立即支付',
            url: `/supplier/order/pay_offline/${row.id}`,
            method: 'post',
            ids: '',
          }
          this.$modalSure(this.delfromData)
            .then((res) => {
              this.$Message.success(res.msg)
              // this.$emit("changeGetTabs");
              this.getData(row.id, 1)
              this.$emit('getList')
            })
            .catch((res) => {
              this.$Message.error(res.msg)
            })
          break

        // 详情
        case '2':
          this.rowActive = row
          this.getData(row.id)
          break

        // 订单记录
        case '3':
          this.$refs.record.modals = true
          this.$refs.record.getList(row.id)
          break

        // 订单备注
        case '4':
          this.$refs.remarks.formValidate.remark = row.remark
          this.$refs.remarks.modals = true
           this.$emit('getList')
          break

        // 立即退款
        case '5':
          this.getOnlyRefundData(row.id, row.refund_type)
          break

        // 同意退货
        case '55':
          this.getRefundData(row.id, row.refund_type)
          break

        // 打印
        case '7':
          this.distributionInfo(row.id)
          break
          
        // 配货单
        case '9':
          this.getRefundData(row.id, row.refund_type)
          break

        // 已收货
        case '8':
          this.delfromData = {
            title: '修改确认收货',
            url: `/supplier/order/take/${row.id}`,
            method: 'put',
            ids: '',
          }
          this.$modalSure(this.delfromData)
            .then((res) => {
              this.$Message.success(res.msg)
              this.$emit('changeGetTabs')
              this.$emit('getList')
              if (num) {
                this.$refs.detailss.getSplitOrder(row.pid)
              } else {
                this.getData(row.id, 1)
              }
            })
            .catch((res) => {
              this.$Message.error(res.msg)
            })
          // this.modalTitleSs = '修改确认收货';
          break
        case "12":
          let pathInfo = this.$router.resolve({
            path:'/admin/supplier/order/distribution',
            query:{
              id:row.id,
              status: 1
            }
          });
          window.open(pathInfo.href, '_blank');
          break;
        // 小票打印
        case '10':
          this.delfromData = {
            title: '立即打印订单',
            info: '您确认打印此订单吗?',
            url: `/supplier/order/print/${row.id}`,
            method: 'get',
            ids: '',
          }
          this.$modalSure(this.delfromData)
            .then((res) => {
              this.$Message.success(res.msg)
              this.$emit('getList')
            })
            .catch((res) => {
              this.$Message.error(res.msg)
            })
          break
      }
    },

    // 仅退款
    getOnlyRefundData(id, refund_type) {
      this.$modalForm(getRefundFrom(id)).then(() => {
        this.$emit('getList')
        this.$emit('changeGetTabs')
        this.$refs.detailss.modals = false
      })
    },

    // 退货退款
    getRefundData(id, refund_type) {
      this.delfromData = {
        title: '是否立即退货退款',
        url: `/supplier/refund/agree/${id}`,
        method: 'get',
      }
      this.$modalSure(this.delfromData)
        .then((res) => {
          this.$Message.success(res.msg)
          this.$emit('getList')
          this.$emit('changeGetTabs')
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },

    // 获取详情表单数据
    getData(id, type) {
      orderInfo(id)
        .then(async (res) => {
          if (!type) {
            this.$refs.detailss.modals = true
          }
          this.$refs.detailss.activeName = 'detail'
          this.orderDatalist = res.data
          if (this.orderDatalist.orderInfo.refund_reason_wap_img) {
            try {
              this.orderDatalist.orderInfo.refund_reason_wap_img = JSON.parse(
                this.orderDatalist.orderInfo.refund_reason_wap_img
              )
            } catch (e) {
              this.orderDatalist.orderInfo.refund_reason_wap_img = []
            }
          }
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
  },
}
</script>
<style scoped lang="less">
.tabBox {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;

  .tabBox_img {
    width: 30px;
    height: 30px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  .tabBox_tit {
    width: 290px;
    height: 30px;
    line-height: 30px;
    font-size: 12px !important;
    margin: 0 2px 0 10px;
    letter-spacing: 1px;
    box-sizing: border-box;
  }
}
</style>