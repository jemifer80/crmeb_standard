<template>
  <!-- 商品-商品列表 -->
  <div class="article-manager">
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <div class="new_card_pd">
        <Alert
          v-if="alertShow"
          banner
          closable
          type="warning"
          class="ivu-mt"
          @on-close="closeAlert"
        >
          <router-link to="/admin/product/add_product/0"
            >您有未完成的商品添加操作，点击此处可继续添加操作？</router-link
          >
        </Alert>
        <Alert show-icon closable>
          温馨提示：1.新增商品时可选统一规格或者多规格，满足商品不同销售属性场景；2.商品销售状态分为销售中且库存足够时才可下单购买
        </Alert>
        <!-- 条件筛选 -->
        <Form
          ref="artFrom"
          inline
          :model="artFrom"
          :label-width="96"
          :label-position="labelPosition"
          @submit.native.prevent
        >
          <FormItem label="商品分类：" prop="cate_id">
            <el-cascader
              placeholder="请选择商品分类"
              class="input-add"
              size="mini"
              v-model="artFrom.cate_id"
              :options="data1"
              :props="props"
              @on-change="cascaderSearchs"
              filterable
              clearable
            >
            </el-cascader>
          </FormItem>
          <FormItem label="供应商：" prop="pid" label-for="pid">
            <Select
              v-model="artFrom.supplier_id"
              @on-change="userSearchs"
              clearable
              class="input-add"
            >
              <Option
                v-for="item in supplierList"
                :value="item.id"
                :key="item.id"
                >{{ item.supplier_name }}</Option
              >
            </Select>
          </FormItem>
          <FormItem label="商品品牌：" prop="brand_id">
            <Cascader
                :data="brandData"
                placeholder="请选择商品品牌"
                change-on-select
                v-model="artFrom.brand_id"
                filterable
                class="input-add"
                @on-change="userSearchs"
            ></Cascader>
          </FormItem>
          <FormItem label="商品标签：" prop="store_label_id" class="labelClass">
            <div class="acea-row row-middle" style="margin-right: 14px;">
              <div class="labelInput acea-row row-between-wrapper" @click="openGoodsLabel">
                <div style="width: 90%;">
                  <div v-if="goodsDataLabel.length">
                    <Tag closable v-for="(item,index) in goodsDataLabel" :key="index" @on-close="closeStoreLabel(item)">{{item.label_name}}</Tag>
                  </div>
                  <span class="span" v-else>选择商品标签</span>
                </div>
                <div class="iconfont iconxiayi"></div>
              </div>
            </div>
          </FormItem>
          <FormItem label="商品搜索：" label-for="store_name">
            <Input
              placeholder="请输入商品名称,关键字,ID"
              v-model="artFrom.store_name"
              class="input-add"
            />
            <Button type="primary" @click="userSearchs()">查询</Button>
          </FormItem>
        </Form>
      </div>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <!-- 相关操作 -->
      <div class="new_tab">
        <Tabs v-model="artFrom.type" @on-click="onClickTab">
          <TabPane
            :label="item.name + ' (' + item.count + ')'"
            :name="item.type.toString()"
            v-for="(item, index) in headeNum"
            :key="index"
          />
        </Tabs>
      </div>
      <div class="acea-row row-between-wrapper">
        <div class="Button">
          <router-link
            v-auth="['product-product-save']"
            :to="'/admin/product/add_product'"
            ><Button type="primary" class="bnt mr15"
              >添加商品</Button
            ></router-link
          >
          <Button
            v-auth="['product-crawl-save']"
            type="success"
            class="bnt mr15"
            @click="onCopy"
            >商品采集</Button
          >
          <Upload
            ref="upload"
            v-if="openErp"
            :show-upload-list="false"
            :action="erpUrl"
            :before-upload="beforeUpload"
            :headers="header"
            :on-success="upFile"
            :format="['xlsx']"
            :on-format-error="handleFormatError"
          >
            <Button>导入ERP商品</Button>
          </Upload>
          <!-- <Tooltip
            content="本页至少选中一项"
            :disabled="!!checkUidList.length && isAll==0"
          >
            <Button
              v-auth="['product-product-set_delivery_type']"
              class="bnt mr15"
              :disabled="!checkUidList.length && isAll==0"
              @click="deliveryType"
              >配送方式</Button
            >
          </Tooltip> -->
          <Tooltip
            content="本页至少选中一项"
            :disabled="!!checkUidList.length && isAll==0"
          >
            <Button
              v-auth="['product-product-product_show']"
              class="bnt mr15"
              :disabled="!checkUidList.length && isAll==0"
              @click="onDismount"
              v-show="artFrom.type === '1'"
              >批量下架</Button
            >
          </Tooltip>
          <Tooltip
            content="本页至少选中一项"
            :disabled="!!checkUidList.length && isAll==0"
          >
            <Button
              v-auth="['product-product-product_show']"
              class="bnt mr15"
              :disabled="!checkUidList.length && isAll==0"
              @click="onShelves"
              v-show="artFrom.type === '2'"
              >批量上架</Button
            >
          </Tooltip>
          <Tooltip
            content="本页至少选中一项"
            :disabled="!!checkUidList.length && isAll==0"
          >
            <Button
              v-auth="['export-storeProduct']"
              class="export mr15"
              :disabled="!checkUidList.length && isAll==0"
              @click="exports"
              >导出</Button
            >
          </Tooltip>
          <Tooltip
            content="本页至少选中一项"
            :disabled="!!checkUidList.length && isAll==0"
          >
            <Button
              v-auth="['product-product-product_show']"
              class="bnt"
              :disabled="!checkUidList.length && isAll==0"
              @click="openBatch"
              >批量设置</Button
            >
          </Tooltip>
        </div>
        <div>
          <Button v-if="openErp" class="bnt mr15" @click="frontDownload"
            >下载erp商品模板</Button
          >
        </div>
      </div>
      <!-- 商品列表表格 -->
      <vxe-table
          ref="xTable"
          class="mt25"
          :loading="loading"
          row-id="id"
          :expand-config="{accordion: true}"
          :checkbox-config="{reserve: true}"
          @checkbox-all="checkboxAll"
          @checkbox-change="checkboxItem"
          :data="tableList">
        <vxe-column type="" width="0" v-show="artFrom.type == 1 || artFrom.type == 2"></vxe-column>
        <vxe-column type="expand" width="35" v-if="artFrom.type == 1 || artFrom.type == 2">
          <template #content="{ row }">
            <div class="tdinfo">
              <Row class="expand-row">
                <Col span="8">
                  <span class="expand-key">商品分类：</span>
                  <span class="expand-value">{{ row.cate_name }}</span>
                </Col>
                <Col span="8">
                  <span class="expand-key">商品市场价格：</span>
                  <span class="expand-value">{{ row.ot_price }}</span>
                </Col>
                <Col span="8">
                  <span class="expand-key">成本价：</span>
                  <span class="expand-value">{{ row.cost }}</span>
                </Col>
              </Row>
              <Row class="expand-row">
                <Col span="8">
                  <span class="expand-key">收藏：</span>
                  <span class="expand-value">{{ row.collect }}</span>
                </Col>
                <Col span="8">
                  <span class="expand-key">虚拟销量：</span>
                  <span class="expand-value">{{ row.ficti }} {{name}}</span>
                </Col>
                <Col span="8" v-show="row.is_verify === -1">
                  <span class="expand-key">审核未通过原因：</span>
                  <span class="expand-value">{{ row.refusal }}</span>
                </Col>
                <Col span="8" v-show="row.is_verify === -2">
                  <span class="expand-key">强制下架原因：</span>
                  <span class="expand-value">{{ row.refusal }}</span>
                </Col>
              </Row>
            </div>
          </template>
        </vxe-column>
        <vxe-column type="checkbox" width="100">
          <template #header>
            <div>
              <Dropdown transfer @on-click="allPages">
                <a href="javascript:void(0)" class="acea-row row-middle">
                  <span>全选({{isAll==1?(total-checkUidList.length):checkUidList.length}})</span>
                  <Icon type="ios-arrow-down"></Icon>
                </a>
                <template #list>
                  <DropdownMenu>
                    <DropdownItem name="0">当前页</DropdownItem>
                    <DropdownItem name="1">所有页</DropdownItem>
                  </DropdownMenu>
                </template>
              </Dropdown>
            </div>
          </template>
        </vxe-column>
        <vxe-column field="id" title="商品ID" width="60"></vxe-column>
        <vxe-column field="image" title="商品图" width="60">
          <template v-slot="{ row }">
            <viewer>
              <div class="tabBox_img">
                <img v-lazy="row.image" />
              </div>
            </viewer>
          </template>
        </vxe-column>
        <vxe-column field="store_name" title="商品名称" min-width="250">
          <template v-slot="{ row }">
            <Tooltip
                theme="dark"
                max-width="300"
                :delay="600"
                :content="row.store_name"
            >
              <div class="line2">{{ row.store_name }}</div>
            </Tooltip>
          </template>
        </vxe-column>
        <vxe-column field="plate_name" title="商品来源" min-width="100"></vxe-column>
        <vxe-column field="product_type" title="商品类型" min-width="100">
          <template v-slot="{ row }">
            <span v-if="row.product_type == 0">普通商品</span>
            <span v-if="row.product_type == 1">卡密商品</span>
            <span v-if="row.product_type == 3">虚拟商品</span>
          </template>
        </vxe-column>
        <vxe-column field="price" title="商品售价" min-width="90"></vxe-column>
        <vxe-column field="sales" title="销量" min-width="90"></vxe-column>
        <vxe-column field="stock" title="库存" min-width="80" v-show="artFrom.type != 6"></vxe-column>
        <vxe-column field="product_award" title="单品提成" min-width="70"></vxe-column>
        <vxe-column field="sort" title="排序" min-width="70"></vxe-column>
        <vxe-column field="state" title="状态" min-width="120">
          <template v-slot="{ row }">
            <i-switch
                v-model="row.is_show"
                :value="row.is_show"
                :true-value="1"
                :false-value="0"
                :disabled="artFrom.type == 6 ? true : false"
                @on-change="changeSwitch(row)"
                size="large"
            >
              <span slot="open">上架</span>
              <span slot="close">下架</span>
            </i-switch>
            <div v-if="row.auto_off_time" class="style-add">
              定时下架：<br />{{ row.auto_off_time | timeFormat }}
            </div>
          </template>
        </vxe-column>
        <vxe-column field="action" title="操作" align="center" width="220" fixed="right">
          <template v-slot="{ row }">
            <a @click="details(row.id)">详情</a>
            <Divider type="vertical" />
            <a @click="edit(row)">编辑</a>
            <Divider type="vertical" />
            <a @click="lookGoods(row.id)">预览</a>
            <Divider type="vertical" />
            <a v-if="artFrom.type === '0'" @click="auditGoods(row)">审核</a>
            <Divider v-if="artFrom.type === '0'" type="vertical" />
            <template>
              <Dropdown
                  @on-click="changeMenu(row, $event, index)"
                  :transfer="true"
              >
                <a href="javascript:void(0)" class="acea-row row-middle">
                  <span>更多</span>
                  <Icon type="ios-arrow-down"></Icon>
                </a>
                <DropdownMenu slot="list">
                  <DropdownItem name="1">查看评论</DropdownItem>
                  <DropdownItem name="2" v-if="artFrom.type === '6'"
                  >恢复商品</DropdownItem
                  >
                  <DropdownItem name="3"  v-if="artFrom.type !== '6' && row.type==0">移到回收站</DropdownItem>
                  <DropdownItem
                      name="4"
                      v-if="row.product_type != 1 && openErp == false"
                  >库存管理</DropdownItem
                  >
                  <DropdownItem name="6">复制商品</DropdownItem>
                </DropdownMenu>
              </Dropdown>
            </template>
          </template>
        </vxe-column>
      </vxe-table>
      <vxe-pager class="mt20" border size="medium" :page-size="artFrom.limit" :current-page="artFrom.page" :total="total"
                 :layouts="['PrevPage', 'JumpNumber', 'NextPage', 'FullJump', 'Total']" @page-change="pageChange">
      </vxe-pager>
<!--      <div class="acea-row row-right page">-->
<!--        <Page-->
<!--          :total="total"-->
<!--          :current="artFrom.page"-->
<!--          show-elevator-->
<!--          show-total-->
<!--          @on-change="pageChange"-->
<!--          :page-size="artFrom.limit"-->
<!--        />-->
<!--      </div>-->
      <attribute
        :attrTemplate="attrTemplate"
        v-on:changeTemplate="changeTemplate"
      ></attribute>
    </Card>
    <!-- 生成淘宝京东表单-->
    <Modal
      v-model="modals"
      :z-index="100"
      class="Box"
      scrollable
      footer-hide
      closable
      title="复制淘宝、天猫、京东、苏宁、1688"
      :mask-closable="false"
      width="1200"
      height="500"
    >
      <tao-bao ref="taobaos" v-if="modals" @on-close="onClose"></tao-bao>
    </Modal>
    <!-- 配送方式 -->
    <Modal
      v-model="modalsType"
      scrollable
      title="配送方式"
      :closable="false"
      class-name="vertical-center-modal"
    >
      <Form :label-width="90" @submit.native.prevent>
        <FormItem label="配送方式：" class="deliveryStyle" required>
          <CheckboxGroup v-model="delivery_type">
            <Checkbox label="1">快递</Checkbox>
            <!-- <Checkbox label="3">门店配送</Checkbox> -->
            <Checkbox label="2">自提</Checkbox>
          </CheckboxGroup>
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="putDelivery">提交</Button>
        <Button @click="cancelDelivery">取消</Button>
      </div>
    </Modal>
    <!-- 商品弹窗 -->
    <div v-if="isProductBox">
      <div class="bg" @click.stop="isProductBox = false"></div>
      <goodsDetail :goodsId="goodsId" :product="1"></goodsDetail>
    </div>
    <stockEdit ref="stock" @stockChange="stockChange"></stockEdit>
    <!-- 商品详情 -->
    <productDetails :visible.sync="detailsVisible" :product-id="productId" @saved="getDataList"></productDetails>
    <!-- 批量设置 -->
    <Modal v-model="batchModal" title="批量设置" width="750" class-name="batch-modal" @on-visible-change="batchVisibleChange">
      <Alert show-icon>每次只能修改一项，如需修改多项，请多次操作。</Alert>
      <Row type="flex" align="middle">
        <Col span="5">
          <Menu :active-name="menuActive" width="auto" @on-select="menuSelect">
            <MenuItem :name="1">商品分类</MenuItem>
            <MenuItem :name="2">商品标签</MenuItem>
            <MenuItem :name="3">物流设置</MenuItem>
            <MenuItem :name="8">运费设置</MenuItem>
            <MenuItem :name="4">购买即送</MenuItem>
            <MenuItem :name="5">关联用户标签</MenuItem>
            <MenuItem :name="6">活动推荐</MenuItem>
            <MenuItem :name="7">自定义留言</MenuItem>
          </Menu>
        </Col>
        <Col span="19">
          <Form :model="batchData" :label-width="122">
            <FormItem v-if="menuActive === 1" label="商品分类：">
              <el-cascader
                v-model="batchData.cate_id"
                :options="data1"
                :props="props"
                size="small"
                filterable
                clearable
                :class="{ single: !batchData.cate_id.length }"
              >
              </el-cascader>
            </FormItem>
            <FormItem v-if="menuActive === 2" label="商品标签：">
              <div class="select-tag" @click="openStoreLabel">
                <div v-if="storeDataLabel.length">
                  <Tag v-for="item in storeDataLabel" :key="item.id" closable @on-close="tagClose(item.id)">{{ item.label_name }}</Tag>
                </div>
                <span v-else class="placeholder">请选择</span>
                <Icon type="ios-arrow-down" />
              </div>
            </FormItem>
            <FormItem v-if="menuActive === 3" label="物流方式：">
              <CheckboxGroup v-model="batchData.delivery_type" size="small">
                <Checkbox :label="1">快递</Checkbox>
                <!-- <Checkbox :label="3">门店配送</Checkbox> -->
                <Checkbox :label="2">自提</Checkbox>
              </CheckboxGroup>
            </FormItem>
            <FormItem v-if="menuActive === 8" label="运费设置：">
              <RadioGroup v-model="batchData.freight">
                <Radio :label="1">包邮</Radio>
                <Radio :label="2">固定邮费</Radio>
                <Radio :label="3">运费模板</Radio>
              </RadioGroup>
            </FormItem>
            <FormItem v-if="menuActive === 8 && batchData.freight === 2">
              <div class="input-number">
                <InputNumber v-model="batchData.postage" :min="0"></InputNumber>
                <span class="suffix">元</span>
              </div>
            </FormItem>
            <FormItem v-if="menuActive === 8 && batchData.freight === 3">
              <Select v-model="batchData.temp_id">
                <Option v-for="item in templateList" :key="item.id" :value="item.id">{{ item.name }}</Option>
              </Select>
            </FormItem>
            <FormItem v-if="menuActive === 4" label="购买送积分：">
              <InputNumber v-model="batchData.give_integral" :min="0"></InputNumber>
            </FormItem>
            <FormItem v-if="menuActive === 4" label="购买送优惠券：">
              <div class="select-tag" @click="addCoupon">
                <div v-if="couponName.length">
                  <Tag v-for="item in couponName" :key="item.id" closable @on-close="handleClose(item)">{{ item.title }}</Tag>
                </div>
                <span v-else class="placeholder">请选择</span>
                <Icon type="ios-arrow-down" />
              </div>
            </FormItem>
            <FormItem v-if="menuActive === 5" label="关联用户标签：">
              <div class="select-tag" @click="openLabel">
                <div v-if="dataLabel.length">
                  <Tag v-for="item in dataLabel" :key="item.id" closable @on-close="tagClose(item.id)">{{ item.label_name }}</Tag>
                </div>
                <span v-else class="placeholder">请选择</span>
                <Icon type="ios-arrow-down" />
              </div>
            </FormItem>
            <FormItem v-if="menuActive === 6" label="商品推荐：">
              <CheckboxGroup v-model="batchData.recommend" size="small">
                <Checkbox label="is_hot">热卖单品</Checkbox>
                <Checkbox label="is_benefit">促销单品</Checkbox>
                <Checkbox label="is_best">精品推荐</Checkbox>
                <Checkbox label="is_new">首发新品</Checkbox>
                <Checkbox label="is_good">优品推荐</Checkbox>
              </CheckboxGroup>
            </FormItem>
            <FormItem v-if="menuActive === 7" label="自定义留言：">
              <i-switch v-model="customBtn" size="large" @on-change="customMessBtn">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </i-switch>
            </FormItem>
            <template v-if="menuActive === 7">
              <FormItem v-for="(item, index) in batchData.custom_form" :key="item.key">
                <Row :gutter="8">
                  <Col span="7">
                    <Input v-model="item.title" :placeholder="`留言标题${index + 1}`"></Input>
                  </Col>
                  <Col span="9">
                    <Select v-model="item.label">
                      <Option v-for="option in customList" :key="option.value" :value="option.value">{{ option.label }}</Option>
                    </Select>
                  </Col>
                  <Col span="5">
                    <Checkbox v-model="item.status" :true-value="1" :false-value="0">必填</Checkbox>
                  </Col>
                  <Col span="3">
                    <Button type="text" size="small" @click="delForm(item)">删除</Button>
                  </Col>
                </Row>
              </FormItem>
            </template>
            <FormItem v-if="menuActive === 7 && customBtn">
              <Button v-show="batchData.custom_form.length < 10" type="text" icon="md-add" size="small" @click="addForm">添加表单</Button>
              <div>用户下单时需填写的信息，最多可设置10条</div>
            </FormItem>
          </Form>
        </Col>
      </Row>
      <div slot="footer">
        <Button @click="cancelBatch">取消</Button>
        <Button type="primary" @click="saveBatch">保存</Button>
      </div>
    </Modal>
    <!-- 用户标签 -->
		<Modal
		  v-model="labelShow"
		  scrollable
		  title="请选择用户标签"
		  :closable="false"
		  width="320"
		  :footer-hide="true"
			:mask-closable="false"
		>
		  <userLabel ref="userLabel" @activeData="activeData" @close="labelClose"></userLabel>
		</Modal>
    <!-- 商品标签 -->
		<Modal
		  v-model="storeLabelShow"
		  scrollable
		  title="请选择商品标签"
		  :closable="false"
		  width="320"
		  :footer-hide="true"
			:mask-closable="false"
		>
		  <storeLabelList ref="storeLabel" @activeData="activeStoreData" @close="storeLabelClose"></storeLabelList>
		</Modal>
    <coupon-list
      ref="couponTemplates"
      @nameId="nameId"
      :couponids="coupon_ids"
      :updateIds="updateIds"
      :updateName="updateName"
    ></coupon-list>
  </div>
</template>

<script>
import goodsDetail from "@/pages/kefu/pc/components/goods_detail";
import stockEdit from "../components/stockEdit.vue";
import expandRow from "./tableExpand.vue";
import productDetails from '../components/productDetails.vue';
import storeLabelList from "@/components/storeLabelList";
import userLabel from "@/components/labelList";
import couponList from "@/components/couponList";
import attribute from "./attribute";
import toExcel from "../../../utils/Excel.js";
import { mapState } from "vuex";
import taoBao from "./taoBao";
import dayjs from "dayjs";
import Setting from "@/setting";
import util from "@/libs/util";
import {
  getGoodHeade,
  getGoods,
  PostgoodsIsShow,
  treeListApi,
  productShowApi,
  productUnshowApi,
  storeProductApi,
  cascaderListApi,
  productCache,
  cacheDelete,
  setDeliveryType,
  productReviewApi,
  forcedRemovalApi,
  batchProcess,
  productGetTemplateApi,
  brandList
} from '@/api/product';
import { getSupplierList } from "@/api/supplier";
import { erpConfig, erpProduct } from "@/api/erp";
import exportExcel from "@/utils/newToExcel.js";

export default {
  name: "product_productList",
  components: { expandRow, attribute, taoBao, goodsDetail, stockEdit, productDetails, storeLabelList, userLabel, couponList },
  filters: {
    timeFormat: (value) => dayjs(value * 1000).format("YYYY-MM-DD HH:mm"),
  },
  computed: {
    ...mapState("admin/layout", ["isMobile"]),
    ...mapState("admin/userLevel", ["categoryId"]),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? "top" : "right";
    },
  },
  data() {
    return {
      supplierList: [],
      header: {}, //请求头部信息
      erpUrl: Setting.apiBaseURL + "/file/upload/1",
      template: false,
      modals: false,
      modalsType: false,
      delivery_type: [],
      grid: {
        xl: 7,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24,
      },
      // 订单列表
      orderData: {
        page: 1,
        limit: 10,
        type: 6,
        status: "",
        time: "",
        real_name: "",
        store_id: "",
      },
      artFrom: {
        page: 1,
        limit: 15,
        cate_id: "",
        type: "1",
        store_name: "",
        excel: 0,
        supplier_id: "",
        store_id:"",
        brand_id:[],
        store_label_id:[]
      },
      list: [],
      tableList: [],
      headeNum: [],
      treeSelect: [],
      isProductBox: false,
      loading: false,
      data: [],
      total: 0,
      props: { emitPath: false, multiple: true },
      attrTemplate: false,
      ids: [],
      display: "none",
      formSelection: [],
      selectionCopy: [],
      checkBox: false,
      isAll: 0,
      data1: [],
      value1: [],
      alertShow: false,
      goodsId: "",
      columns3: [],
      openErp: false,
      // activeKey:1
      productId: 0,
      detailsVisible: false,
      batchModal: false,
      menuActive: 1,
      storeLabelShow: false,
      storeDataLabel: [],
      labelShow: false,
      dataLabel: [],
      coupon_ids: [],
      updateIds: [],
      updateName: [],
      couponName: [],
      //自定义留言下拉选择
      customList: [
        {
          value: "text",
          label: "文本框",
        },
        {
          value: "number",
          label: "数字",
        },
        {
          value: "email",
          label: "邮件",
        },
        {
          value: "data",
          label: "日期",
        },
        {
          value: "time",
          label: "时间",
        },
        {
          value: "id",
          label: "身份证",
        },
        {
          value: "phone",
          label: "手机号",
        },
        {
          value: "img",
          label: "图片",
        },
      ],
      customBtn: false,
      batchData: {
        cate_id: [],
        store_label_id: [],
        delivery_type: [],
        freight: 1,
        postage: 0,
        temp_id: 0,
        give_integral: 0,
        coupon_ids: [],
        label_id: [],
        recommend: [],
        custom_form: []
      },
      templateList: [],
      brandData:[],
      goodsDataLabel:[],
      isLabel:0,
      checkUidList:[],
      isCheckBox: false
    };
  },
  watch: {
    $route() {
      if (this.$route.fullPath === "/admin/product/product_list?type=5") {
        this.getPath();
      }
    },
    formSelection(value) {
      // this.checkBox = value.length === this.tableList.length;
    },
    tableList: {
      deep: true,
      handler(value) {
        value.forEach((item) => {
          this.formSelection.forEach((itm) => {
            if (itm.id === item.id) {
              item.checkBox = true;
            }
          });
        });
        const arr = this.tableList.filter((item) => item.checkBox);
        if (this.tableList.length) {
          this.checkBox = this.tableList.length === arr.length;
        } else {
          this.checkBox = false;
        }
      },
    },
    storeDataLabel(value) {
      this.batchData.store_label_id = value.map(item => item.id);
      this.artFrom.store_label_id = value.map(item => item.id);
    },
    couponName(value) {
      this.batchData.coupon_ids = value.map(item => item.id);
    },
    dataLabel(value) {
      this.batchData.label_id = value.map(item => item.id);
    },
    'batchData.custom_form'(value){
      this.customBtn = !!value.length;
    },
    'batchData.freight'(value) {
      switch (value) {
        case 1:
          this.batchData.postage = 0;
          this.batchData.temp_id = 0;
          break;
        case 2:
          this.batchData.temp_id = 0;
          break;
        case 3:
          this.batchData.postage = 0;
          break;
      }
    }
  },
  created() {
    this.getToken();
    productCache()
      .then((res) => {
        const info = res.data.info;
        if (!Array.isArray(info)) {
          this.alertShow = true;
        }
      })
      .catch((err) => {
        this.$Message.error(err.msg);
      });
    this.getErpConfig();
    this.getBrandList();
  },
  mounted() {
    this.goodsCategory();
    this.getSupplierList();
    if (this.$route.fullPath === "/admin/product/product_list?type=5") {
      this.getPath();
    } else {
      this.getDataList();
    }
    // this.getDataList();
    this.productGetTemplate();
  },
  activated() {
    this.getDataList();
    this.goodHeade();
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (from.path.indexOf("/admin/product/add_product") != -1) {
        document.documentElement.scrollTop = to.meta.scollTopPosition;
      }
    });
  },
  beforeRouteLeave(to, from, next) {
    if (from.meta.keepAlive) {
      from.meta.scollTopPosition = document.documentElement.scrollTop;
    }
    next();
  },
  methods: {
    checkboxItem(e){
      let id = parseInt(e.rowid);
      let index = this.checkUidList.indexOf(id);
      if(index !== -1 && this.isAll==0){
        this.checkUidList = this.checkUidList.filter((item)=> item !== id);
      }else{
        this.checkUidList.push(id);
      }
    },
    checkboxAll(){
      // 获取选中当前值
      let obj2 = this.$refs.xTable.getCheckboxRecords(true);
      // 获取之前选中值
      let obj = this.$refs.xTable.getCheckboxReserveRecords(true);
      obj = obj.concat(obj2);
      let ids = [];
      obj.forEach((item)=>{
        ids.push(parseInt(item.id))
      })
      this.checkUidList = ids;
      if(!obj2.length){
        this.isCheckBox = false;
      }
    },
    allPages(e){
      this.isAll = e;
      if(e==0){
        this.$refs.xTable.toggleAllCheckboxRow();
        this.checkboxAll();
      }else{
        if(!this.isCheckBox){
          this.$refs.xTable.setAllCheckboxRow(true);
          this.isCheckBox = true;
          this.isAll = 1;
        }else{
          this.$refs.xTable.setAllCheckboxRow(false);
          this.isCheckBox = false;
          this.isAll = 0;
        }
        this.checkUidList = []
      }
    },
    closeStoreLabel(label){
      let index = this.goodsDataLabel.indexOf(this.goodsDataLabel.filter(d=>d.id == label.id)[0]);
      this.goodsDataLabel.splice(index,1);
      // 商品标签id
      let storeActiveIds = [];
      this.goodsDataLabel.forEach((item)=>{
        storeActiveIds.push(item.id)
      });
      this.artFrom.store_label_id = storeActiveIds;
      this.userSearchs();
    },
    // 品牌列表
    getBrandList(){
      brandList().then(res=>{
        this.brandData = res.data
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    //获取供应商列表；
    getSupplierList() {
      getSupplierList()
        .then(async (res) => {
          this.supplierList = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 审核
    auditGoods (row) {
        this.$modalForm(productReviewApi(row.id)).then(() => {
          this.getDataList();
          this.goodHeade();
        })
    },
    // 强制下架
    forcedRemoval (row) {
      this.$modalForm(forcedRemovalApi(row.id)).then(() => this.getDataList())
    },
    frontDownload() {
      let a = document.createElement("a"); //创建一个<a></a>标签
      a.href = "/statics/ERP商品导入模板.xlsx"; // 给a标签的href属性值加上地址，注意，这里是绝对路径，不用加 点.
      a.download = "ERP商品导入模板.xlsx"; //设置下载文件文件名，这里加上.xlsx指定文件类型，pdf文件就指定.fpd即可
      a.style.display = "none"; // 障眼法藏起来a标签
      document.body.appendChild(a); // 将a标签追加到文档对象中
      a.click(); // 模拟点击了a标签，会触发a标签的href的读取，浏览器就会自动下载了
      a.remove(); // 一次性的，用完就删除a标签
    },
    handleFormatError(file) {
      return this.$Message.error("必须上传xlsx格式文件");
    },
    // 上传头部token
    getToken() {
      this.header["Authori-zation"] = "Bearer " + util.cookies.get("token");
    },
    upFile(res) {
      erpProduct({ path: res.data.src })
        .then((res) => {
          this.$Message.success(res.msg);
          this.getDataList();
        })
        .catch((err) => {
          return this.$Message.error(err.msg);
        });
    },
    beforeUpload() {
      let promise = new Promise((resolve) => {
        this.$nextTick(function () {
          resolve(true);
        });
      });
      return promise;
    },
    //erp配置
    getErpConfig() {
      erpConfig()
        .then((res) => {
          this.openErp = res.data.open_erp;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    stockChange(stock) {
      this.tableList.forEach((item) => {
        if (this.goodsId == item.id) {
          item.stock = stock;
        }
      });
    },
    // 库存管理
    stockControl(row) {
      this.goodsId = row.id;
      this.$refs.stock.modals = true;
      this.$refs.stock.productAttrs(row);
    },
    cancelDelivery() {
      this.modalsType = false;
      this.delivery_type = [];
    },
    deliveryType() {
      this.modalsType = true;
    },
    putDelivery() {
      if (this.delivery_type.length === 0) {
        this.$Message.error("请选择要配送的商品");
      } else {
        let data = {
          all: this.isAll,
          delivery_type: this.delivery_type,
          ids:this.checkUidList
        };
        // if (this.isAll == 0) {
        //   data.ids = this.checkUidList;
        // }
        setDeliveryType(data)
          .then((res) => {
            this.$Message.success(res.msg);
            this.modalsType = false;
            this.delivery_type = [];
            this.isAll = 0;
            this.getDataList();
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      }
    },
    // 商品详情
    lookGoods(id) {
      this.goodsId = id;
      this.isProductBox = true;
    },
    closeAlert() {
      cacheDelete()
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    getPath() {
      this.columns2 = [...this.columns];
      if (name !== "1" && name !== "2") {
        this.columns2.shift();
      }
      this.artFrom.page = 1;
      this.artFrom.type = this.$route.query.type.toString();
      this.getDataList();
    },
    changeMenu(row, name, index) {
      switch (name) {
        case "1":
          this.$router.push({ path: "/admin/product/product_reply/" + row.id });
          break;
        case "2":
          this.del(row, "恢复商品", index, name);
          break;
        case "3":
          this.del(row, "移入回收站", index, name);
          break;
        case "4":
          this.stockControl(row);
          break;
        case "5":
          this.$modalForm(forcedRemovalApi(row.id)).then(() => {
            this.getDataList();
            this.goodHeade();
            })
            break;
        case "6":
          this.$router.push({ path: "/admin/product/add_product/", query: { copy: row.id } });
          break;
      }
    },
    // 数据导出；
    async exports() {
      let [th, filekey, data, fileName] = [[], [], [], ""];
      let formValidate = this.artFrom;
      let excelData = {};
      excelData.ids = this.checkUidList.join();
      if (this.isAll == 1) {
        excelData.cate_id = formValidate.cate_id;
        excelData.type = formValidate.type;
        excelData.store_name = formValidate.store_name;
      }
      excelData.page = 1;
      for (let i = 0; i < excelData.page + 1; i++) {
        let lebData = await this.getExcelData(excelData);
        if (!fileName) fileName = lebData.filename;
        if (!filekey.length) {
          filekey = lebData.filekey;
        }
        if (!th.length) th = lebData.header;
        if (lebData.export.length) {
          data = data.concat(lebData.export);
          excelData.page++;
        } else {
          exportExcel(th, filekey, fileName, data);
          return;
        }
      }
    },
    getExcelData(excelData) {
      return new Promise((resolve, reject) => {
        storeProductApi(excelData).then((res) => {
          return resolve(res.data);
        });
      });
    },
    changeTemplate(e) {
      // this.template = e;
    },
    freight() {
      this.$refs.template.isTemplate = true;
    },
    // 批量上架
    onShelves() {
      if (this.checkUidList.length === 0) {
        this.$Message.warning("请选择要上架的商品");
      } else {
        let data = {
          all: this.isAll,
          ids: this.checkUidList
        };
        if (this.isAll == 1) {
          data.where = {
            cate_id: this.artFrom.cate_id,
            excel: this.artFrom.excel,
            store_name: this.artFrom.store_name,
            type: this.artFrom.type,
          };
        }
        productShowApi(data)
          .then((res) => {
            this.$Message.success(res.msg);
            this.goodHeade();
            this.getDataList();
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      }
    },
    // 批量下架
    onDismount() {
      if (this.checkUidList.length === 0) {
        this.$Message.warning("请选择要下架的商品");
      } else {
        let data = {
          all: this.isAll,
          ids:this.checkUidList
        };
        if (this.isAll == 1) {
          data.where = {
            cate_id: this.artFrom.cate_id,
            excel: this.artFrom.excel,
            store_name: this.artFrom.store_name,
            type: this.artFrom.type,
          };
        }
        productUnshowApi(data)
          .then((res) => {
            this.$Message.success(res.msg);
            this.goodHeade();
            this.getDataList();
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      }
    },
    // 添加淘宝商品成功
    onClose() {
      this.modals = false;
    },
    // 复制淘宝
    onCopy() {
      this.$router.push({
        path: "/admin/product/add_product",
        query: { type: -1 },
      });
      // this.modals = true;
    },
    // tab选择
    onClickTab(name) {
      this.isAll = 0;
      this.isCheckBox = false;
      this.$refs.xTable.setAllCheckboxRow(false);
      this.checkUidList = [];
      this.artFrom.type = name;
      // this.columns2 = [...this.columns];
      // if (name !== "1" && name !== "2") {
      //   this.columns2.shift();
      // }
      // let obj = [...this.columns];
      // obj.shift();
      // obj.splice(8, 1);
      // this.columns3 = obj;
      // this.checkBox = false;
      this.artFrom.page = 1;
      this.getDataList();
    },
    // 下拉树
    handleCheckChange(data) {
      let value = "";
      let title = "";
      this.list = [];
      this.artFrom.cate_id = 0;
      data.forEach((item, index) => {
        value += `${item.id},`;
        title += `${item.title},`;
      });
      value = value.substring(0, value.length - 1);
      title = title.substring(0, title.length - 1);
      this.list.push({
        value,
        title,
      });
      this.artFrom.cate_id = value;
      this.getDataList();
    },
    // 获取商品表单头数量
    goodHeade() {
      // let data = {
      //   store_name: this.artFrom.store_name,
      //   cate_id: this.artFrom.cate_id || "",
      //   supplier_id: this.artFrom.supplier_id || "",
      //   store_id: this.artFrom.store_id || "",
      //   brand_id: this.artFrom.brand_id || [],
      //   store_label_id: this.artFrom.store_label_id || []
      // };
      getGoodHeade(this.artFrom)
        .then((res) => {
          this.headeNum = res.data.list;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 商品分类；
    goodsCategory() {
      // treeListApi(1).then(res => {
      //     this.treeSelect = res.data;
      // }).catch(res => {
      //     this.$Message.error(res.msg);
      // })
      cascaderListApi(1)
        .then((res) => {
          this.data1 = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 商品列表；
    getDataList() {
      this.loading = true;
      this.artFrom.cate_id = this.artFrom.cate_id || "";
      getGoods(this.artFrom)
        .then((res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = data.count;
          this.loading = false;
          this.$nextTick(function(){
            if (this.isAll == 1) {
              if(this.isCheckBox){
                this.$refs.xTable.setAllCheckboxRow(true);
              }else{
                this.$refs.xTable.setAllCheckboxRow(false);
              }
            }else{
              if(!this.checkUidList.length){
                this.$refs.xTable.setAllCheckboxRow(false);
              }
            }
          })
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(currentPage) {
      this.artFrom.page = currentPage.currentPage;
      this.getDataList();
      // this.$refs.table.clearCurrentRow();
    },
    cascaderSearchs(value, selectedData) {
      this.artFrom.cate_id = value[value.length - 1];
      this.userSearchs();
    },
    // 表格搜索
    userSearchs(e) {
      this.isAll = 0;
      this.$refs.xTable.setAllCheckboxRow(false);
      this.checkUidList = [];
      this.artFrom.page = 1;
      this.formSelection = [];
      this.goodHeade();
      this.getDataList();
    },
    // 上下架
    changeSwitch(row) {
      PostgoodsIsShow(row.id, row.is_show)
        .then((res) => {
          this.$Message.success(res.msg);
          this.goodHeade();
          this.getDataList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
          this.goodHeade();
          this.getDataList();
        });
    },
    // 数据导出；
    exportData: function () {
      let th = [
        "商品名称",
        "商品简介",
        "商品分类",
        "价格",
        "库存",
        "销量",
        "收藏人数",
      ];
      let filterVal = [
        "store_name",
        "store_info",
        "cate_name",
        "price",
        "stock",
        "sales",
        "collect",
      ];
      this.where.page = "nopage";
      getGoods(this.where).then((res) => {
        let data = res.data.map((v) => filterVal.map((k) => v[k]));
        let fileTime = Date.parse(new Date());
        let [fileName, fileType, sheetName] = [
          "商户数据_" + fileTime,
          "xlsx",
          "商户数据",
        ];
        toExcel({ th, data, fileName, fileType, sheetName });
      });
    },
    // 属性弹出；
    attrTap() {
      this.attrTemplate = true;
    },
    changeTemplate(msg) {
      this.attrTemplate = msg;
    },
    // 编辑
    edit(row) {
      this.$router.push({ path: "/admin/product/add_product/" + row.id });
    },
    // 确认
    del(row, tit, num, name) {
      let delfromData = {
        title: tit,
        num: num,
        url: `product/product/${row.id}`,
        method: "DELETE",
        ids: "",
        tips: `确定要移${ name == 2 ? '出' : '入' }回收站吗？`,
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tableList.splice(num, 1);
          this.goodHeade();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 删除成功
    // submitModel () {
    //     this.tableList.splice(this.delfromData.num, 1);
    //     this.goodHeade();
    // }
    // 商品详情
    details(id) {
      this.productId = id;
      this.detailsVisible = true;
    },
    openBatch() {
      this.isLabel = 0;
      this.batchModal = true;
    },
    menuSelect(name) {
      this.menuActive = name;
    },
    activeStoreData(storeDataLabel){
			this.storeLabelShow = false;
      if(this.isLabel){
        this.goodsDataLabel = storeDataLabel;
        // 商品标签id
        let storeActiveIds = [];
        storeDataLabel.forEach((item)=>{
          storeActiveIds.push(item.id)
        });
        this.artFrom.store_label_id = storeActiveIds;
        this.userSearchs();
      }else{
        this.storeDataLabel = storeDataLabel;
      }
		},
    // 标签弹窗关闭
		storeLabelClose() {
		  this.storeLabelShow = false;
		},
    openStoreLabel(row) {
		  this.storeLabelShow = true;
			this.$refs.storeLabel.storeLabel(JSON.parse(JSON.stringify(this.storeDataLabel)));
		},
    openGoodsLabel(row){
      this.storeLabelShow = true;
      this.$refs.storeLabel.storeLabel(JSON.parse(JSON.stringify(this.goodsDataLabel)));
      this.isLabel = 1;
    },
    tagClose(id) {
      if (this.menuActive == 2) {
        let index = this.storeDataLabel.findIndex(item => item.id === id);
        this.storeDataLabel.splice(index, 1);
      } else {
        let index = this.dataLabel.findIndex(item => item.id === id);
        this.dataLabel.splice(index, 1);
      }
    },
    activeData(dataLabel){
			this.labelShow = false;
			this.dataLabel = dataLabel;
		},
    // 标签弹窗关闭
		labelClose() {
		  this.labelShow = false;
		},
    openLabel() {
		  this.labelShow = true;
			this.$refs.userLabel.userLabel(JSON.parse(JSON.stringify(this.dataLabel)));
		},
    // 添加优惠券
    addCoupon() {
      this.$refs.couponTemplates.isTemplate = true;
      this.$refs.couponTemplates.tableList();
    },
    nameId(id, names) {
      this.coupon_ids = id;
      this.couponName = this.unique(names);
    },
    handleClose(name) {
      let index = this.couponName.indexOf(name);
      this.couponName.splice(index, 1);
      let couponIds = this.coupon_ids;
      couponIds.splice(index, 1);
      this.updateIds = couponIds;
      this.updateName = this.couponName;
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
    },
    // 添加表单
    addForm() {
      this.batchData.custom_form.push({
        key: Date.now(),
        title: '',
        label: '',
        status: 0
      });
    },
    // 删除表单
    delForm(item) {
      let index = this.batchData.custom_form.findIndex(val => val === item);
      if (index !== -1) {
        this.batchData.custom_form.splice(index, 1);
      }
    },
    cancelBatch() {
      this.batchModal = false;
    },
    saveBatch() {
      let data = {
        type: this.menuActive,
        ids: this.checkUidList,
        all: this.isAll,
        where: this.artFrom,
        data: this.batchData,
      };
      batchProcess(data).then(res => {
        this.$Message.success(res.msg);
        this.batchModal = false;
      }).catch(res => {
        this.$Message.error(res.msg);
      });
    },
    // 获取运费模板；
    productGetTemplate() {
      productGetTemplateApi().then((res) => {
        this.templateList = res.data;
      });
    },
    customMessBtn(e) {
      if (e) {
        this.addForm();
      } else {
        this.batchData.custom_form = [];
      }
    },
    batchVisibleChange() {
      this.batchData = {
        cate_id: [],
        store_label_id: [],
        delivery_type: [],
        freight: 1,
        postage: 0,
        temp_id: 0,
        give_integral: 0,
        coupon_ids: [],
        label_id: [],
        recommend: [],
        custom_form: []
      };
      this.storeDataLabel = [];
      this.couponName = [];
      this.dataLabel = [];
      this.menuActive = 1;
    }
  },
};
</script>
<style scoped lang="stylus">
/deep/.el-cascader .el-cascader__search-input{
  font-size: 12px !important;
}
/deep/.ivu-dropdown-item{
  font-size: 12px!important;
}
/deep/.vxe-table--render-default .vxe-cell{
  font-size: 12px;
}
.tdinfo{
  margin-left: 75px;
  margin-top: 16px;
}
.expand-row{
  margin-bottom: 16px;
  font-size: 12px;
}
/deep/.ivu-checkbox-wrapper{
  font-size: 12px;
}
.labelClass{
  /deep/.ivu-form-item-content{
    line-height: unset;
  }
}
.labelInput{
  border: 1px solid #dcdee2;
  width :250px;
  padding: 0 5px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
.span{
  color: #c5c8ce;
}
.iconxiayi{
  font-size: 12px
}
}

.input-add {
  width: 250px;
  margin-right: 14px;
}

.style-add {
  margin-top: 10px;
  line-height: 1.2;
}

.line2 {
  max-height: 40px;
}

.bg {
  z-index: 100;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
}

.deliveryStyle /deep/.ivu-checkbox-wrapper {
  margin-right: 14px;
}

.Button /deep/.ivu-upload {
  width: 105px;
  display: inline-block;
  margin-right: 10px;
}

/deep/.ivu-modal-mask {
  z-index: 999 !important;
}

/deep/.ivu-modal-wrap {
  z-index: 999 !important;
}

/deep/.ivu-alert {
  margin-bottom: 20px;
}

.Box {
  >>> .ivu-modal-body {
    height: 700px;
    overflow: auto;
  }
}

.tabBox_img {
  width: 40px;
  height: 40px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}

/deep/.ivu-table-cell-expand-expanded {
  margin-top: -6px;
  margin-right: 33px;
  transition: none;

  .ivu-icon {
    vertical-align: 2px;
  }
}

/deep/.ivu-table-header {
  // overflow visible
}

/deep/.ivu-table th {
  overflow: visible;
}

/deep/.select-item:hover {
  background-color: #f3f3f3;
}

/deep/.select-on {
  display: block;
}

/deep/.select-item.on {
  /* background: #f3f3f3; */
}

.new_tab {
  >>>.ivu-tabs-nav .ivu-tabs-tab {
    padding: 4px 16px 20px !important;
    font-weight: 500;
  }
}
.select-tag {
  position: relative;
  min-height: 32px;
  padding: 0 24px 0 4px;
  border: 1px solid #dcdee2;
  border-radius: 4px;
  line-height: normal;
  user-select: none;
  cursor: pointer;

  &:hover {
    border-color: #57a3f3;
  }

  .ivu-icon {
    position: absolute;
    top: 50%;
    right: 8px;
    line-height: 1;
    transform: translateY(-50%);
    font-size: 14px;
    color: #808695;
    transition: all .2s ease-in-out;
  }

  .ivu-tag {
    position: relative;
    max-width: 99%;
    height: 24px;
    margin: 3px 4px 3px 0;
    line-height: 22px;
  }

  .placeholder {
    display: block;
    height: 30px;
    line-height: 30px;
    color: #c5c8ce;
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-left: 4px;
    padding-right: 22px;
  }
}
.input-number {
  position: relative;
  display: inline-block;
  vertical-align: middle;

  >>> .ivu-input-number-handler-wrap {
    right: 32px;
  }

  .ivu-input-number {
    width: 144px;
    margin-right: 32px;
  }

  .suffix {
    position: absolute;
    top: 0;
    right: 0;
    z-index: 1;
    width: 32px;
    height: 100%;
    text-align: center;
  }
}
.ivu-checkbox-wrapper, .ivu-radio-wrapper {
  margin-right: 30px;
}

>>> .batch-modal {
  .ivu-modal-body {
    padding: 0;
  }

  .ivu-alert {
    margin: 12px 24px;
  }

  .ivu-col-span-5 {
    flex: none;
    width: 130px;
  }

  .ivu-col-span-19 {
    padding-right: 37px;
  }

  .ivu-input-number {
    width: 100%;
  }

  .ivu-menu-light.ivu-menu-vertical .ivu-menu-item-active:not(.ivu-menu-submenu) {
    z-index: auto;
  }

  .ivu-menu-light.ivu-menu-vertical .ivu-menu-item-active:not(.ivu-menu-submenu):after {
    right: auto;
    left: 0;
  }

  .el-cascader {
    width: 100%;
  }

  .ivu-btn-text {
    color: #2D8CF0;
  }

  .ivu-btn-text:focus {
    box-shadow: none;
  }

  .ivu-menu-item {
    padding-right: 0;
  }
}

>>>.el-cascader {
  &.el-cascader--small {
    vertical-align: bottom;
    line-height: 30px;
  }

  &.single {
    .el-input__inner {
      height: 32px !important;
    }
  }

  .el-input__inner {
    padding-left: 7px;
    font-size: 14px;
  }

  .el-cascader__search-input {
    margin-left: 9px;
    font-size: 14px;
  }

  .el-input__suffix {
    right: 4px;
  }

  .el-input__icon {
    color: #808695;
        display: inline-block;
    font-family: "Ionicons" !important;
    speak: none;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
    text-transform: none;
    text-rendering: optimizeLegibility;
    line-height: 1;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    vertical-align: -0.125em;
    text-align: center;
    line-height: 32px;
  }

  .el-icon-arrow-down:before {
    content: "\F116";
  }
}
</style>
