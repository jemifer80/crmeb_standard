<template>
<!-- 用户-用户列表 -->
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <div class="padding-add">
        <!-- 筛选条件 -->
        <Form
          ref="userFrom"
          :model="userFrom"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
        >
          <Row :gutter="24">
            <Col span="18">
              <Row>
                <Col span="24">
                  <Row>
                    <Col>
                      <FormItem label="用户搜索：" label-for="nickname">
                        <Input
                          v-model="userFrom.nickname"
                          placeholder="请输入"
                          element-id="nickname"
                          clearable
                          class="input-add"
                        >
                          <Select
                            v-model="field_key"
                            slot="prepend"
                            style="width: 80px"
                          >
                            <Option value="all">全部</Option>
                            <Option value="uid">UID</Option>
                            <Option value="phone">手机号</Option>
                            <Option value="merchant_name">商户名称</Option>
                            <!--
                            <Option value="nickname">用户昵称</Option>-->
                          </Select>
                        </Input>
                      </FormItem>
                    </Col>
                  </Row>
                </Col>
              </Row>
            </Col>
            <template v-if="collapse">
              <Col span="18">
                <Row>
                  <Col>
                    <FormItem label="用户分组：" label-for="group_id">
                      <Select
                        v-model="userFrom.group_id"
                        placeholder="请选择"
                        element-id="group_id"
                        clearable
                         class="input-add"
                      >
<!--                    <Option value="">全部</Option>-->
                        <Option
                          :value="item.id"
                          v-for="(item, index) in groupList"
                          :key="index"
                          >{{ item.group_name }}</Option
                        >
                      </Select>
                    </FormItem>
                  </Col>
                  <Col>
                    <FormItem label="用户标签：" label-for="label_id">
                      <div
                        class="labelInput acea-row row-between-wrapper input-add"
                        @click="openLabelList"
                      >
                        <div>
                          <div v-if="dataLabel.length">
                            <Tag
                              closable
                              v-for="(item, index) in dataLabel"
                              :key="index"
                              @on-close="closeLabel(item)"
                              >{{ item.label_name }}</Tag
                            >
                          </div>
                          <span class="span" v-else>请选择</span>
                        </div>
                        <div class="iconfont iconxiayi"></div>
                      </div>
                    </FormItem>
                  </Col>
                  <!-- <Col>
                    <FormItem label="性别：" label-for="sex">
                      <Select
                        v-model="userFrom.sex"
                        placeholder="请选择"
                        clearable
                         class="input-add"
                      >
                     <Option value="">全部</Option>
                        <Option value="1">男</Option>
                        <Option value="2">女</Option>
                        <Option value="0">未知</Option>
                      </Select>
                    </FormItem>
                  </Col>--> 
                </Row>
              </Col>
              <Col span="18">
                <Row>
                  <Col>
                    <FormItem label="会员等级：" label-for="level">
                      <Select
                        v-model="userFrom.level"
                        placeholder="请选择"
                        element-id="level"
                        clearable
                        class="input-add"
                      >
<!--                        <Option value="">全部</Option>-->
                        <Option
                          :value="item.id"
                          v-for="(item, index) in levelList"
                          :key="index"
                          >{{ item.name }}</Option
                        >
                      </Select>
                    </FormItem>
                  </Col>
               <!-- <Col>
                    <FormItem label="付费会员：" label-for="isMember">
                      <Select
                        v-model="userFrom.isMember"
                        placeholder="请选择"
                        clearable
                         class="input-add"
                      >
                       <Option value="">全部</Option>
                        <Option value="1">是</Option>
                        <Option value="0">否</Option>
                      </Select>
                    </FormItem>
                  </Col>
                  <Col>
                    <FormItem label="身份：">
                      <Select
                        v-model="userFrom.is_promoter"
                        placeholder="请选择"
                        clearable
                        class="input-add"
                      >
                      <Option value="">全部</Option>
                        <Option value="1">推广员</Option>
                        <Option value="0">普通用户</Option>
                      </Select>
                    </FormItem>
                  </Col>-->
                </Row>
              </Col>

              <Col span="18">
                <Row>
                  <Col class="dateMedia">
                    <FormItem label="访问时间：" label-for="user_time">
                      <DatePicker
                        :editable="false"
                        @on-change="onchangeTime"
                        :value="timeVal"
                        format="yyyy/MM/dd"
                        type="daterange"
                        placement="bottom-start"
                        placeholder="自定义时间"
                        :options="options"
                         class="input-add"
                      ></DatePicker>
                    </FormItem>
                  </Col>
                  <Col>
                    <FormItem label="访问情况：" label-for="user_time_type">
                      <Select
                        v-model="userFrom.user_time_type"
                        placeholder="请选择"
                        element-id="user_time_type"
                        clearable
                         class="input-add"
                      >
<!--                        <Option value="all">全部</Option>-->
                        <Option value="visitno">时间段未访问</Option>
                        <Option value="visit">时间段访问过</Option>
                        <Option value="add_time">首次访问</Option>
                      </Select>
                    </FormItem>
                  </Col>
                   <Col>
                    <FormItem label="下单次数：" label-for="pay_count">
                      <Select
                        v-model="userFrom.pay_count"
                        placeholder="请选择"
                        element-id="pay_count"
                        clearable
                         class="input-add"
                      >
<!--                    <Option value="">全部</Option>-->
                        <Option value="-1">0次</Option>
                        <Option value="0">1次以上</Option>
                        <Option value="1">2次以上</Option>
                        <Option value="2">3次以上</Option>
                        <Option value="3">4次以上</Option>
                        <Option value="4">5次以上</Option>
                      </Select>
                    </FormItem>
                  </Col>
                   <!-- 用户区域 -->
                  <Col>
                    <FormItem label="用户区域：" label-for="city_id">
                      <Select
                        v-model="userFrom.city_id"
                        placeholder="请选择"
                        element-id="city_id"
                        clearable
                         class="input-add"
                      >
                     <Option value="117932">中山区</Option>
                     <Option value="117997">西岗区</Option>
                     <Option value="118050">沙河口区</Option>
                     <Option value="119090">沙河口区Ⅱ</Option>
                     <Option value="119126">沙河口区III</Option>
                     <Option value="118150">甘井子区</Option>
                     <Option value="118405">开发区</Option>
                     <Option value="118523">高新园区</Option>
                        <!--
                        <Option
                          :value="item.id"
                          v-for="(item, index) in cityList"
                          :key="index"
                          >{{ item.name }}</Option
                        >
                        -->
                      </Select>
                    </FormItem>
                  </Col>

                  <Col class="dateMedia">
                    <FormItem label="下单时间：" label-for="payment_time">
                      <DatePicker
                        :editable="false"
                        @on-change="onchangePaymentTime"
                        :value="paymentTimeVal"
                        format="yyyy/MM/dd"
                        type="daterange"
                        placement="bottom-start"
                        placeholder="自定义时间"
                        :options="options"
                         class="input-add"
                      ></DatePicker>
                    </FormItem>
                  </Col>                  
           
                </Row>
              </Col>     

            </template>
            <Col span="6" class="ivu-text-right userFrom">
              <FormItem>
                <Button
                  type="primary"
                  label="default"
                  class="mr15"
                  @click="userSearchs"
                  >搜索</Button
                >
                <Button class="ResetSearch" @click="reset('userFrom')"
                  >重置</Button
                >
                <a v-font="14" class="ivu-ml-8" @click="collapse = !collapse">
                  <template v-if="!collapse">
                    展开 <Icon type="ios-arrow-down" />
                  </template>
                  <template v-else>
                    收起 <Icon type="ios-arrow-up" />
                  </template>
                </a>
              </FormItem>
            </Col>
          </Row>
        </Form>
      </div>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt listbox">
      <div class="new_tab">
        <!-- Tab栏切换 -->
        <Tabs @on-click="onClickTab">
          <TabPane
            :label="item.name"
            :name="item.type"
            v-for="(item, index) in headeNum"
            :key="index"
          />
        </Tabs>
      </div>
      <Row type="flex" justify="space-between">
        <!-- 相关操作 -->
        <Col span="24">
          <Button
            v-auth="['admin-user-save']"
            type="primary"
            class="mr20"
            @click="save"
            >添加用户</Button
          >
          <Tooltip
            content="本页至少选中一项"
            :disabled="!!checkUidList.length && isAll==0"
          >
            <Button
              v-auth="['admin-user-coupon']"
              type="primary"
              class="mr20"
              :disabled="!checkUidList.length && isAll==0"
              @click="onSend"
              >发送优惠券</Button
            >
          </Tooltip>
          <Button
            v-auth="['admin-wechat-news']"
            class="greens mr20"
            size="default"
            @click="onSendPic"
            v-if="userFrom.user_type === 'wechat'"
          >
            <Icon type="md-list"></Icon>
            发送图文消息
          </Button>
          <Tooltip
            content="本页至少选中一项"
            :disabled="!!checkUidList.length && isAll==0"
          >
            <Button
              v-auth="['admin-user-set_label']"
              class="mr20"
              :disabled="!checkUidList.length && isAll==0"
              @click="setBatch"
              >批量设置</Button
            >
          </Tooltip>
        </Col>
      </Row>
      <!-- 用户列表表格 -->
      <vxe-table
          ref="xTable"
          class="mt25"
          :loading="loading"
          row-id="uid"
          :expand-config="{accordion: true}"
          :checkbox-config="{reserve: true}"
          @checkbox-all="checkboxAll"
          @checkbox-change="checkboxItem"
          :data="userLists">
        <vxe-column type="" width="0"></vxe-column>
        <vxe-column type="expand" width="35">
          <template #content="{ row }">
            <div class="tdinfo">
              <Row class="expand-row">
                <Col span="6">
                  <span class="expand-key">首次访问：</span>
                  <span class="expand-value"> {{row.add_time | formatDate}}</span>
                </Col>
                <Col span="6">
                  <span class="expand-key">近次访问：</span>
                  <span class="expand-value">{{row.last_time  | formatDate}}</span>
                </Col>
                <Col span="6">
                  <span class="expand-key">身份证号：</span>
                  <span class="expand-value">{{row.card_id}}</span>
                </Col>
                <Col span="6">
                  <span class="expand-key">真实姓名：</span>
                  <span class="expand-value">{{row.real_name}}</span>
                </Col>
                <!-- <Col span="6">
                    <span class="expand-key">手机号：</span>
                    <span class="expand-value">{{row.phone}}</span>
                </Col> -->
              </Row>
              <Row class="expand-row">
                <!-- <Col span="6">
                    <span class="expand-key">真实姓名：</span>
                    <span class="expand-value">{{row.real_name}}</span>
                </Col> -->
                <Col span="6">
                  <span class="expand-key">标签：</span>
                  <span class="expand-value">{{row.labels}}</span>
                </Col>
                <!--
                <Col span="6">
                  <span class="expand-key">生日：</span>
                  <span class="expand-value">{{row.birthday}}</span>
                </Col>-->
                <Col span="6">
                  <span class="expand-key">地址：</span>
                  <span class="expand-value">{{row.addres}}</span>
                </Col>
              </Row>
              <Row class="expand-row">
                <Col span="6">
                  <span class="expand-key">备注：</span>
                  <span class="expand-value">{{row.mark}}</span>
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
        <vxe-column field="uid" title="UID" width="60"></vxe-column>
        <vxe-column field="avatars" title="头像" width="50">
          <template v-slot="{ row }">
            <viewer>
              <div class="tabBox_img">
                <img v-lazy="row.avatar" />
              </div>
            </viewer>
          </template>
        </vxe-column>
        <vxe-column field="nickname" title="昵称" min-width="150">
          <template v-slot="{ row }">
            <div class="acea-row">
              <Icon
                  type="md-male"
                  v-show="row.sex === '男'"
                  color="#2db7f5"
                  size="15"
                  class="mr5"
              />
              <Icon
                  type="md-female"
                  v-show="row.sex === '女'"
                  color="#ed4014"
                  size="15"
                  class="mr5"
              />
              <div v-if="row.delete_time != null" style="color:#ed4014;">{{row.nickname}} (已注销)</div>
              <div v-else v-text="row.nickname"></div>
            </div>
          </template>
        </vxe-column>
        <!-- 追加 商户名称 -->
        <vxe-column field="merchant_name" title="商户名称" min-width="110"></vxe-column>
        <!-- 追加 业务员 -->
        <vxe-column field="salesman_id" title="业务员" min-width="110">
          
          <template v-slot="{ row }">
            <div>{{ row.salesman_id == 0 ? "未绑定" : row.salesman_id }}</div>
          </template>
        </vxe-column>
        <!-- 追加 订单总数 -->
        <vxe-column field="total_order" title="订单总数" min-width="110"></vxe-column>         
        <!-- 追加 消费总金额 -->
        <vxe-column field="total_amount" title="消费总金额" min-width="110"></vxe-column> 
        <vxe-column field="level" title="用户等级" min-width="90"></vxe-column>
        <vxe-column field="group_id" title="分组" min-width="100"></vxe-column>
        <vxe-column field="phone" title="手机号" min-width="110"></vxe-column>
           <!-- 
                <vxe-column field="isMember" title="付费会员" min-width="90">
          <template v-slot="{ row }">
            <div>{{ row.isMember ? "是" : "否" }}</div>
          </template>
        </vxe-column>
            <vxe-column field="user_type" title="用户类型" min-width="100"></vxe-column>
   <vxe-column field="spread_uid_nickname" title="推荐人" min-width="100"></vxe-column>-->
        <vxe-column field="now_money" title="余额" min-width="100"></vxe-column>
        <vxe-column field="action" title="操作" align="center" width="180" fixed="right">
          <template v-slot="{ row }">
            <span v-if="row.delete_time != null" style="color: #c5c8ce;">编辑</span>
            <a v-else @click="edit(row)">编辑</a>
            <Divider type="vertical" v-if="row.is_extend_info"/>
            <a @click="extendInfo(row)" v-if="row.is_extend_info">信息补充</a>
            <Divider type="vertical" />
            <a @click="changeMenu(row, '1')">详情</a>
          </template>
        </vxe-column>
      </vxe-table>
      <vxe-pager class="mt20" border size="medium" :page-size="userFrom.limit" :current-page="userFrom.page" :total="total"
                 :layouts="['PrevPage', 'JumpNumber', 'NextPage', 'FullJump', 'Total']" @page-change="pageChange">
      </vxe-pager>
    </Card>
    <!-- 用户标签 -->
    <Modal
      v-model="labelListShow"
      scrollable
      title="请选择用户标签"
      :closable="false"
      width="320"
      :footer-hide="true"
      :mask-closable="false"
    >
      <labelList
        ref="labelList"
        @activeData="activeData"
        @close="labelListClose"
      ></labelList>
    </Modal>
    <!-- 编辑表单 积分余额-->
    <edit-from
      ref="edits"
      :FromData="FromData"
	  :userEdit="1"
      @submitFail="submitFail"
    ></edit-from>
    <!-- 发送优惠券-->
    <send-from
      ref="sends"
      :is-all="isAll"
      :where="userFrom"
      :userIds="checkUidList.join(',')"
    ></send-from>
    <!-- 会员详情-->
    <user-details ref="userDetails" :group-list="groupList"></user-details>
    <!--发送图文消息 -->
    <Modal
      v-model="modal13"
      scrollable
      title="发送消息"
      width="1200"
      height="800"
      footer-hide
      class="modelBox"
    >
      <news-category
        v-if="modal13"
        :isShowSend="isShowSend"
        :userIds="user_ids"
        :scrollerHeight="scrollerHeight"
        :contentTop="contentTop"
        :contentWidth="contentWidth"
        :maxCols="maxCols"
      ></news-category>
    </Modal>
    <!--修改推广人-->
    <Modal
      v-model="promoterShow"
      scrollable
      title="修改推广人"
      class="order_box"
      :closable="false"
    >
      <Form
        ref="formInline"
        :model="formInline"
        :label-width="100"
        @submit.native.prevent
      >
        <FormItem label="用户头像：" prop="image">
          <div class="picBox" @click="customer">
            <div class="pictrue" v-if="formInline.image">
              <img v-lazy="formInline.image" />
            </div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <Icon type="ios-camera-outline" size="26" />
            </div>
          </div>
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="putSend('formInline')">提交</Button>
        <Button @click="cancel('formInline')">取消</Button>
      </div>
    </Modal>
    <Modal
      v-model="customerShow"
      scrollable
      title="请选择商城用户"
      :closable="false"
      width="900"
    >
      <customerInfo
        v-if="customerShow"
        @imageObject="imageObject"
      ></customerInfo>
    </Modal>
    <Modal
      v-model="labelShow"
      scrollable
      title="请选择用户标签"
      :closable="false"
      width="320"
      :footer-hide="true"
    >
      <userLabel :uid="labelActive.uid" @close="labelClose"></userLabel>
    </Modal>
    <!-- 批量设置 -->
    <Modal v-model="batchModal" title="批量设置" width="750" class-name="batch-modal" @on-visible-change="batchVisibleChange">
      <Alert show-icon>每次只能修改一项，如需修改多项，请多次操作。</Alert>
      <Row type="flex" align="middle">
        <Col span="4">
          <Menu :active-name="menuActive" width="auto" @on-select="menuSelect">
            <MenuItem :name="1">用户分组</MenuItem>
            <MenuItem :name="2">用户标签</MenuItem>
            <MenuItem :name="3">用户等级</MenuItem>
            <MenuItem :name="4">积分余额</MenuItem>
            <MenuItem :name="5">赠送会员</MenuItem>
            <MenuItem :name="6">上级推广人</MenuItem>
          </Menu>
        </Col>
        <Col span="20">
          <Form :model="batchData" :label-width="122">
            <FormItem v-if="menuActive === 1" label="用户分组：">
              <Select v-model="batchData.group_id">
                <Option v-for="item in groupList" :key="item.id" :value="item.id">{{ item.group_name }}</Option>
              </Select>
            </FormItem>
            <FormItem v-if="menuActive === 2" label="用户标签：">
              <div class="select-tag" @click="openLabelList">
                <div v-if="batchLabel.length">
                  <Tag v-for="item in batchLabel" :key="item.id" closable @on-close="tagClose(item.id)">{{ item.label_name }}</Tag>
                </div>
                <span v-else class="placeholder">请选择</span>
                <Icon type="ios-arrow-down" />
              </div>
            </FormItem>
            <FormItem v-if="menuActive === 3" label="用户等级：">
              <Select v-model="batchData.level_id">
                <Option v-for="item in levelList" :key="item.id" :value="item.id">{{ item.name }}</Option>
              </Select>
            </FormItem>
            
            <FormItem v-if="menuActive === 4" label="修改余额：">
              <RadioGroup v-model="batchData.money_status">
                <Radio :label="1">增加</Radio>
                <Radio :label="2">减少</Radio>
              </RadioGroup>
            </FormItem>
            <FormItem v-if="menuActive === 4" label="余额：">
              <InputNumber v-model="batchData.money" :min="0" :max="999999"></InputNumber>
            </FormItem>
            <FormItem v-if="menuActive === 4" label="修改积分：">
              <RadioGroup v-model="batchData.integration_status">
                <Radio :label="1">增加</Radio>
                <Radio :label="2">减少</Radio>
              </RadioGroup>
            </FormItem>
            <FormItem v-if="menuActive === 4" label="积分：">
              <InputNumber v-model="batchData.integration" :min="0" :max="999999"></InputNumber>
            </FormItem>
            <FormItem v-if="menuActive === 5" label="修改时长：">
              <RadioGroup v-model="batchData.days_status">
                <Radio :label="1">增加</Radio>
                <Radio :label="2">减少</Radio>
              </RadioGroup>
            </FormItem>
            <FormItem v-if="menuActive === 5" label="修改时长(天)：">
              <InputNumber v-model="batchData.day" :min="0" :max="999999"></InputNumber>
            </FormItem>
            <FormItem v-if="menuActive === 6" label="上级推广员：">
              <Input :value="batchData.spread_uid" placeholder="请选择" icon="ios-arrow-down" @on-click="customer" @on-focus="customer"></Input>
            </FormItem>
          </Form>
        </Col>
      </Row>
      <div slot="footer">
        <Button @click="cancelBatch">取消</Button>
        <Button type="primary" @click="saveBatch">保存</Button>
      </div>
    </Modal>
  </div>
</template>

<script>
import { formatDate } from '@/utils/validate';
import userLabel from "../../../components/userLabel";
import labelList from "@/components/labelList";
import { mapState } from "vuex";
import expandRow from "./tableExpand.vue";
import {
  userList,
  getUserData,
  isShowApi,
  editOtherApi,
  giveLevelApi,
  userSetGroup,
  userGroupApi,
  levelListApi,
  userSetLabelApi,
  userLabelApi,
  userSynchro,
  getUserSaveForm,
  giveLevelTimeApi,
  extendInfo,
  batchProcess,
  cityListApi
} from "@/api/user";
import { agentSpreadApi } from "@/api/agent";
import editFrom from "../../../components/from/from";
import sendFrom from "@/components/sendCoupons/index";
import userDetails from "./handle/userDetails";
import newsCategory from "@/components/newsCategory/index";
import city from "@/utils/city";
import customerInfo from "@/components/customerInfo";
export default {
  name: "user_list",
  filters: {
    formatDate (time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    }
  },
  components: {
    expandRow,
    editFrom,
    sendFrom,
    userDetails,
    newsCategory,
    customerInfo,
    userLabel,
    labelList,
  },
  data() {
    return {
      dataLabel: [],
      labelListShow: false,
      labelShow: false,
      customerShow: false,
      promoterShow: false,
      labelActive: {
        uid: 0,
      },
      formInline: {
        uid: 0,
        spread_uid: 0,
        image: "",
      },
      options: {
        shortcuts: [
          {
            text: "今天",
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                new Date(
                  new Date().getFullYear(),
                  new Date().getMonth(),
                  new Date().getDate()
                )
              );
              return [start, end];
            },
          },
          {
            text: "昨天",
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(
                  new Date(
                    new Date().getFullYear(),
                    new Date().getMonth(),
                    new Date().getDate() - 1
                  )
                )
              );
              end.setTime(
                end.setTime(
                  new Date(
                    new Date().getFullYear(),
                    new Date().getMonth(),
                    new Date().getDate() - 1
                  )
                )
              );
              return [start, end];
            },
          },
          {
            text: "最近7天",
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(
                  new Date(
                    new Date().getFullYear(),
                    new Date().getMonth(),
                    new Date().getDate() - 6
                  )
                )
              );
              return [start, end];
            },
          },
          {
            text: "最近30天",
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(
                  new Date(
                    new Date().getFullYear(),
                    new Date().getMonth(),
                    new Date().getDate() - 29
                  )
                )
              );
              return [start, end];
            },
          },
          {
            text: "本月",
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(
                  new Date(new Date().getFullYear(), new Date().getMonth(), 1)
                )
              );
              return [start, end];
            },
          },
          {
            text: "本年",
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), 0, 1))
              );
              return [start, end];
            },
          },
        ],
      },
      collapse: false,
      headeNum: [
        { type: "", name: "全部" },
        { type: "wechat", name: "微信公众号" },
        { type: "routine", name: "微信小程序" },
        { type: "h5", name: "H5" },
        { type: "pc", name: "PC" },
        { type: "app", name: "APP" },
      ],
      address: [],
      addresData: city,
      isShowSend: true,
      modal13: false,
      maxCols: 4,
      scrollerHeight: "600",
      contentTop: "130",
      contentWidth: "98%",
      // grid: {
      //   xl: 8,
      //   lg: 8,
      //   md: 12,
      //   sm: 24,
      //   xs: 24,
      // },
      grid2: {
        xl: 18,
        lg: 16,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      total: 0,
      userFrom: {
        label_id: "",
        user_type: "",
        status: "",
        sex: "",
        is_promoter: "",
        country: "",
        isMember: "",
        pay_count: "",
        user_time_type: "",
        user_time: "",
        nickname: "",
        province: "",
        city: "",
        page: 1,
        limit: 15,
        level: "",
        group_id: "",
        field_key: "",
        merchant_name: "",
        salesman_id : "",
        city_id:"",
        total_amount:"",
        payment_time:"",
      },
      field_key: "",
      level: "",
      group_id: "",
      label_id: "",
      city_id:"",
      user_time_type: "",
      pay_count: "",
      userLists: [],
      FromData: null,
      selectionList: [],
      user_ids: "",
      selectedData: [],
      timeVal: [],
      array_ids: [],
      groupList: [],
      levelList: [],
      citylList: [],
      labelFrom: {
        page: 1,
        limit: "",
      },
      labelLists: [],
      display: "none",
      checkBox: false,
      selectionCopy: [],
      isCheckBox: false,
      isAll: 0,
      userId: 0,
      checkUidList:[],
      batchModal: false,
      menuActive: 1,
      batchLabel: [],
      batchData: {
        group_id: 0,
        label_id: [],
        level_id: 0,
        city_id:0,
        money_status: 0,
        money: 0,
        integration_status: 0,
        integration: 0,
        days_status: 1,
        day: 0,
        spread_uid: '',
      },
    };
  },
  watch: {
    selectionList(value) {
      let arr = value.map((item) => item.uid);
      this.array_ids = arr;
      this.user_ids = arr.join();
    },
    userLists: {
      deep: true,
      handler(value) {
        value.forEach((item) => {
          this.selectionList.forEach((itm) => {
            if (itm.uid === item.uid) {
              item.checkBox = true;
            }
          });
        });
        const arr = this.userLists.filter((item) => item.checkBox);
        if (this.userLists.length) {
          this.checkBox = this.userLists.length === arr.length;
        } else {
          this.checkBox = false;
        }
      },
    },
  },
  computed: {
    ...mapState("admin/layout", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 100;
    },
    labelPosition() {
      return this.isMobile ? "top" : "right";
    },
  },
  created() {
    this.getList();
  },
  mounted() {
    this.userGroup();
    this.levelLists();
    this.groupLists();
    this.cityLists();
  },
  methods: {
    checkboxItem(e){
      let uid = parseInt(e.rowid);
      let index = this.checkUidList.indexOf(uid);
      if(index !== -1 && this.isAll==0){
        this.checkUidList = this.checkUidList.filter((item)=> item !== uid);
      }else{
        this.checkUidList.push(uid);
      }
    },
    checkboxAll(){
      // 获取选中当前值
      let obj2 = this.$refs.xTable.getCheckboxRecords(true);
      // 获取之前选中值
      let obj = this.$refs.xTable.getCheckboxReserveRecords(true);
      obj = obj.concat(obj2);
      let uids = [];
      obj.forEach((item)=>{
        uids.push(parseInt(item.uid))
      })
      this.checkUidList = uids;
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
    closeLabel(label) {
      let index = this.dataLabel.indexOf(
        this.dataLabel.filter((d) => d.id == label.id)[0]
      );
      this.dataLabel.splice(index, 1);
    },
    activeData(dataLabel) {
      this.labelListShow = false;
      if (this.batchModal && this.menuActive === 2) {
        this.batchLabel = dataLabel;
        this.batchData.label_id = dataLabel.map(item => item.id);
      } else {
        this.dataLabel = dataLabel;
      }
    },
    openLabelList(row) {
      this.labelListShow = true;
      let data = JSON.parse(JSON.stringify(this.dataLabel));
      if (this.batchModal && this.menuActive === 2) {
        data = JSON.parse(JSON.stringify(this.batchLabel));
      }
      this.$refs.labelList.userLabel(data);
    },
    // 标签弹窗关闭
    labelListClose() {
      this.labelListShow = false;
    },
    // 标签弹窗关闭
    labelClose(e) {
      if (!e) {
        this.getList();
      }
      this.labelShow = false;
      this.labelActive.uid = 0;
    },
    // 提交
    putSend(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          if (!this.formInline.spread_uid) {
            return this.$Message.error("请上传用户");
          }
          agentSpreadApi(this.formInline)
            .then((res) => {
              this.promoterShow = false;
              this.$Message.success(res.msg);
              this.getList();
              this.$refs[name].resetFields();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        }
      });
    },
    save() {
      this.$modalForm(getUserSaveForm()).then(() => this.getList());
			// getUserSaveForm().then(async (res) => {
			// 	if(res.data.status === false){
			// 		return this.$authLapse(res.data);
			// 	}
			// 	this.FromData = res.data;
			// 	this.$refs.edits.modals = true;
			// }).catch(err=>{
			// 	this.$Message.error(err.msg);
			// })
    },
    synchro() {
      userSynchro()
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    // 分组列表
    groupLists() {
      this.loading = true;
      userLabelApi(this.labelFrom)
        .then(async (res) => {
          let data = res.data;
          this.labelLists = data.list;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    onClickTab(type) {
      this.isAll = 0;
      this.isCheckBox = false;
      this.$refs.xTable.setAllCheckboxRow(false);
      this.checkUidList = [];
      this.userFrom.page = 1;
      this.userFrom.user_type = type;
      this.getList();
    },
    userGroup() {
      let data = {
        page: 1,
        limit: "",
      };
      userGroupApi(data).then((res) => {
        this.groupList = res.data.list;
      });
    },
    levelLists() {
      let data = {
        page: 1,
        limit: "",
        title: "",
        is_show: 1,
      };
      levelListApi(data).then((res) => {
        this.levelList = res.data.list;
      });
    },
    cityLists() {
      let data = {
        page: 1,
        limit: "",
        title: "",
        is_show: 1,
      };
      cityListApi(data).then((res) => {
        this.cityList = res.data.list;
      });
    },

    // 批量设置分组；
    setGroup() {
      if (this.selectionList.length === 0) {
        this.$Message.warning("请选择要设置分组的用户");
      } else {
        let uids = {
          all: this.isAll,
          uids: this.array_ids
        };
        if (this.isAll == 1) {
          uids.where = this.userFrom;
          uids.where = {
            city: this.userFrom.city,
            country: this.userFrom.country,
            field_key: this.userFrom.field_key,
            group_id: this.userFrom.group_id,
            isMember: this.userFrom.isMember,
            is_promoter: this.userFrom.is_promoter,
            label_id: this.userFrom.label_id,
            level: this.userFrom.level,
            nickname: this.userFrom.nickname,
            pay_count: this.userFrom.pay_count,
            province: this.userFrom.province,
            sex: this.userFrom.sex,
            status: this.userFrom.status,
            user_time: this.userFrom.user_time,
            user_time_type: this.userFrom.user_time_type,
            user_type: this.userFrom.user_type,
            payment_time: this.userFrom.payment_time,
            //merchant_name: this.userFrom.merchant_name,
            //salesman_id: this.userFrom.salesman_id,
          };
        }
        this.$modalForm(userSetGroup(uids)).then(() => this.getList());
      }
    },
    // 批量设置标签；
    setLabel() {
      if (this.selectionList.length === 0) {
        this.$Message.warning("请选择要设置标签的用户");
      } else {
        let uids = {
          all: this.isAll,
          uids: this.array_ids
        };
        if (this.isAll == 1) {
          uids.where = {
            city: this.userFrom.city,
            country: this.userFrom.country,
            field_key: this.userFrom.field_key,
            group_id: this.userFrom.group_id,
            isMember: this.userFrom.isMember,
            is_promoter: this.userFrom.is_promoter,
            label_id: this.userFrom.label_id,
            level: this.userFrom.level,
            nickname: this.userFrom.nickname,
            pay_count: this.userFrom.pay_count,
            province: this.userFrom.province,
            sex: this.userFrom.sex,
            status: this.userFrom.status,
            user_time: this.userFrom.user_time,
            user_time_type: this.userFrom.user_time_type,
            user_type: this.userFrom.user_type,
            //merchant_name: this.userFrom.merchant_name,
            //salesman_id: this.userFrom.salesman_id,            
          };
        }
        this.labelShow = true;
        this.labelActive.uid = uids;
        // this.$modalForm(userSetLabelApi(uids)).then(() => this.getList());
      }
    },
    // 是否为付费会员；
    changeMember() {
      this.userFrom.page = 1;
      this.getList();
    },
    // 选择国家
    changeCountry() {
      if (this.userFrom.country === "abroad" || !this.userFrom.country) {
        this.selectedData = [];
        this.userFrom.province = "";
        this.userFrom.city = "";
        this.address = [];
      }
    },
    // 选择地址
    handleChange(value, selectedData) {
      this.selectedData = selectedData.map((o) => o.label);
      this.userFrom.province = this.selectedData[0];
      this.userFrom.city = this.selectedData[1];
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.userFrom.user_time = this.timeVal.join("-");
    },
    //下单日期
    onchangePaymentTime(e) {
      this.paymentTimeVal = e;
      this.userFrom.payment_time = this.paymentTimeVal.join("-");
    },    
    // 操作
    changeMenu(row, name, index) {
      this.userId = row.uid;
      let uid = [];
      uid.push(row.uid);
      let uids = { uids: uid };
      switch (name) {
        case "1":
          this.$refs.userDetails.modals = true;
          this.$refs.userDetails.activeName = "info";
          this.$refs.userDetails.getDetails(row.uid);
          break;
        case "2":
          this.getOtherFrom(row.uid);
          break;
        case "3":
          // this.giveLevel(row.uid);
          this.giveLevelTime(row.uid);
          break;
        case "4":
          this.del(
            row,
            "清除 【 " + row.nickname + " 】的会员等级",
            index,
            "user"
          );
          break;
        case "5":
          this.$modalForm(userSetGroup(uids)).then(() =>
            this.$refs.sends.getList()
          );
          break;
        case "6":
          this.openLabel(row);
          // this.$modalForm(userSetLabelApi(uids)).then(() => this.$refs.sends.getList());
          break;
        case "7":
          this.editS(row);
          break;
        default:
          this.del(
            row,
            "解除【 " + row.nickname + " 】的上级推广人",
            index,
            "tuiguang"
          );
          break;
        // this.del(row, '清除 【 ' + row.nickname + ' 】的会员等级', index)
      }
    },
    openLabel(row) {
      this.labelShow = true;
      this.labelActive.uid = row.uid;
    },
    editS(row) {
      this.promoterShow = true;
      this.formInline.uid = row.uid;
    },
    customer() {
      this.customerShow = true;
    },
    imageObject(e) {
      this.customerShow = false;
      if (this.batchModal && this.menuActive === 6) {
        this.batchData.spread_uid = e.uid;
      } else {
        this.formInline.spread_uid = e.uid;
        this.formInline.image = e.image;
      }
    },
    cancel(name) {
      this.promoterShow = false;
      this.$refs[name].resetFields();
    },
    // 赠送会员等级
    giveLevel(id) {
      giveLevelApi(id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 赠送会员等级
    giveLevelTime(id) {
      giveLevelTimeApi(id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 删除
    del(row, tit, num, name) {
      let delfromData = {
        title: tit,
        num: num,
        url:
          name === "user"
            ? `user/del_level/${row.uid}`
            : `agent/stair/delete_spread/${row.uid}`,
        method: name === "user" ? "DELETE" : "PUT",
        // url: `user/del_level/${row.uid}`,
        // method: 'DELETE',
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 清除会员删除成功
    submitModel() {
      this.getList();
    },
    // 会员列表
    getList() {
      this.loading = true;
      let activeIds = [];
      this.dataLabel.forEach((item) => {
        activeIds.push(item.id);
      });
      this.userFrom.label_id = activeIds.join(",") || "";
      this.userFrom.user_type = this.userFrom.user_type || "";
      this.userFrom.status = this.userFrom.status || "";
      this.userFrom.sex = this.userFrom.sex || "";
      //商户名称
      this.userFrom.merchant_name = this.userFrom.merchant_name || "";
      //业务员
      this.userFrom.salesman_id = this.userFrom.salesman_id || "";
      //总订单数
      this.userFrom.total_order = this.userFrom.total_order || "";      
      //总消费总额
      this.userFrom.total_amount = this.userFrom.total_amount || "";    
      this.userFrom.is_promoter = this.userFrom.is_promoter || "";
      this.userFrom.country = this.userFrom.country || "";
      this.userFrom.user_time_type = this.userFrom.user_time_type || "";
      this.userFrom.pay_count = this.userFrom.pay_count || "";
      // this.userFrom.label_id = this.userFrom.label_id || "";
      this.userFrom.field_key = this.field_key === "all" ? "" : this.field_key;
      this.userFrom.level =
        this.userFrom.level === "all" ? "" : this.userFrom.level;
      this.userFrom.group_id =
        this.userFrom.group_id === "all" ? "" : this.userFrom.group_id;
      userList(this.userFrom)
        .then(async (res) => {
          let data = res.data;
          data.list.forEach((item) => {
            item.checkBox = false;
          });
          this.userLists = data.list;
          this.total = data.count;
          this.loading = false;
          this.$nextTick(function(){
            if (this.isAll == 1) {
              this.selectionList = this.userLists;
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
    pageChange({ currentPage, pageSize }) {
      this.userFrom.page = currentPage;
      this.userFrom.limit = pageSize;
      this.getList();
    },
    // pageChange(index) {
    //   this.userFrom.page = index;
    //   this.getList();
    // },
    // 搜索
    userSearchs() {
      if (this.userFrom.user_time_type && !this.timeVal.length) {
        return this.$Message.error("请选择访问时间");
      }
      if (this.timeVal.length && !this.userFrom.user_time_type) {
        return this.$Message.error("请选择访问情况");
      }
      this.isAll = 0;
      this.$refs.xTable.setAllCheckboxRow(false);
      this.checkUidList = [];
      this.userFrom.page = 1;
      this.selectionList = [];
      this.getList();
    },
    // 重置
    reset(name) {
      this.userFrom = {
        user_type: "",
        status: "",
        sex: "",
        is_promoter: "",
        country: "",
        pay_count: "",
        user_time_type: "",
        user_time: "",
        nickname: "",
        merchant_name: "",
        salesman_id : "",
        field_key: "",
        level: "",
        group_id: "",
        label_id: "",
        page: 1, // 当前页
        limit: 20, // 每页显示条数
      };
      this.field_key = "";
      this.level = "";
      this.group_id = "";
      this.label_id = "";
      this.user_time_type = "";
      this.pay_count = "";
      this.timeVal = [];
      this.selectionList = [];
      this.dataLabel = [];
      this.city_id = "";
      this.getList();
    },
    // 获取编辑表单数据
    getUserFrom(id) {
    this.$modalForm(getUserData(id)).then(() => this.getList());
      // getUserData(id)
      //   .then(async (res) => {
      //     if (res.data.status === false) {
      //       return this.$authLapse(res.data);
      //     }
      //     this.FromData = res.data;
      //     this.$refs.edits.modals = true;
      //   })
      //   .catch((res) => {
      //     this.$Message.error(res.msg);
      //   });
    },
    // 获取积分余额表单
    getOtherFrom(id) {
      editOtherApi(id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          res.data.rules[1].props.max = 999999;
          res.data.rules[1].props.precision = 0;
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修改状态
    onchangeIsShow(row) {
      let data = {
        id: row.uid,
        status: row.status,
      };
      isShowApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 点击发送优惠券
    onSend() {
      if (this.checkUidList.length === 0 && this.isAll==0) {
        return this.$Message.warning("请选择要发送优惠券的用户");
      }
      this.$refs.sends.modals = true;
      this.$refs.sends.getList();
    },
    // 发送图文消息
    onSendPic() {
      if (this.selectionList.length === 0) {
        this.$Message.warning("请选择要发送图文消息的用户");
      } else {
        this.modal13 = true;
      }
    },
    // 编辑
    edit(row) {
      this.getUserFrom(row.uid);
      // this.$modalForm(getUserSaveForm(row.uid)).then(() => this.getList());
    },
    //信息补充
    extendInfo(row) {
      // this.$modalForm(extendInfo(row.uid)).then(() => this.getList());
      extendInfo(row.uid).then(async (res) => {
      	if(res.data.status === false){
      		return this.$authLapse(res.data);
      	}
      	this.FromData = res.data;
      	this.$refs.edits.modals = true;
        this.getList()
      }).catch(err=>{
      	this.$Message.error(err.msg);
      })
    },
    // 修改成功
    submitFail(p) {
      this.getList();
      if (this.$refs.userDetails.modals) {
        this.$refs.userDetails.getDetails(this.userId);
      }
    },
    // 排序
    // sortChanged(e) {
    //   this.userFrom[e.key] = e.order;
    //   this.getList();
    // },
    // onSelectCancel(selection, row) {},
    menuSelect(name) {
      this.menuActive = name;
    },
    setBatch() {
      this.batchModal = true;
    },
    tagClose(id) {
      let index = this.batchLabel.findIndex(item => item.id === id);
      this.batchLabel.splice(index, 1);
    },
    cancelBatch() {
      this.batchModal = false;
    },
    // 保存批量操作
    saveBatch() {
      batchProcess({
        type: this.menuActive,
        uids: this.checkUidList,
        all: this.isAll,
        where: this.userFrom,
        data: this.batchData
      }).then(res => {
        this.$Message.success(res.msg);
        this.batchModal = false;
      }).catch(res => {
        this.$Message.error(res.msg);
      });
    },
    batchVisibleChange() {
      this.batchData = {
        group_id: 0,
        label_id: [],
        level_id: 0,
        money_status: 0,
        money: 0,
        integration_status: 0,
        integration: 0,
        days_status: 1,
        day: 0,
        spread_uid: '',
      };
      this.batchLabel = [];
      this.menuActive = 1;
    }
  },
};
</script>

<style scoped lang="stylus">
/deep/.ivu-dropdown-item{
  font-size: 12px!important;
}
/deep/.vxe-table--render-default .vxe-cell{
  font-size: 12px;
}
.expand-row{
  margin-bottom: 16px;
  font-size: 12px;
}
.tdinfo {
  margin-left: 88px;
  margin-top: 15px;
}
.padding-add {
 padding: 20px 20px 0;
}
.input-add {
 max-width:250px;
}
.labelInput {
  max-width:250px;
  border: 1px solid #dcdee2;
  padding: 0 5px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;

  .span {
    color: #c5c8ce;
  }

  .iconxiayi {
    font-size: 12px;
  }
}

.picBox {
  display: inline-block;
  cursor: pointer;

  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }

  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }
}

.userFrom {
  >>> .ivu-form-item-content {
    margin-left: 0px !important;
  }
}

.userAlert {
  margin-top: 20px;
}

.userI {
  color: #1890FF;
  font-style: normal;
}

img {
  height: 36px;
  display: block;
}

.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}

.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}

.modelBox {
  >>> .ivu-modal-body {
    padding: 0 16px 16px 16px !important;
  }
}

.vipName {
  color: #dab176;
}

.listbox {
  >>>.ivu-divider-horizontal {
    margin: 0 !important;
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
.pane_pd{
  padding:4px 16px 20px !important;
  font-weight: 500;
}
.new_tab {
  >>>.ivu-tabs-nav .ivu-tabs-tab{
      padding:4px 16px 20px !important;
      font-weight: 500;
  }
}
.dateMedia{
	/deep/.ivu-form-item-content{
		max-width 250px;
		/deep/.ivu-date-picker{
			width 100%;
		}
	}
}
.select-tag{
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
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-left: 4px;
    padding-right: 22px;
  }
}

>>> .batch-modal {
  .ivu-modal-body {
    padding: 0;
  }

  .ivu-alert {
    margin: 12px 24px;
  }

  .ivu-col-span-4 {
    flex: none;
    width: 130px;
  }

  .ivu-col-span-20 {
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

  .ivu-menu-item {
    padding-right: 0;
  }
}
</style>
