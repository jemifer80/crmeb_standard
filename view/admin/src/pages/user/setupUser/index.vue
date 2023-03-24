<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Tabs v-model="tabVal" @on-click="tabChange">
        <!-- ---------------------------------基础信息-------------------------------- -->
        <TabPane label="基础信息" name="basic">
          <Form :model="basicsForm" :label-width="120">
            <Row :gutter="24" type="flex">
              <Col span="24">
                <div class="basics">用户设置</div>
              </Col>
              <Col span="24" class="mt10">
                <FormItem label="用户默认头像：">
                  <div
                    class="uploadPictrue"
                    v-if="authorizedPicture"
                    @click="modalPicTap('单选')"
                  >
                    <img v-lazy="authorizedPicture" />
                  </div>
                  <div
                    class="uploadPictrue"
                    @click="modalPicTap('单选')"
                    v-else
                  >
                    <span class="iconfont iconshangpinshuliang-jia"></span>
                  </div>
                  <div class="upload-text">建议尺寸：120*120px</div>
                </FormItem>
              </Col>
              <Col span="14" class="mt10">
                <FormItem label="用户信息设置：">
                  <!-- 用户表格 -->
                  <Table
                    :data="listOne"
                    :columns="columns"
                    ref="table"
                    class="mt25 goods"
                    highlight-row
                    :draggable="true"
                    @on-drag-drop="onDragDrop"
                  >
                    <template slot-scope="{ row, index }" slot="drag">
                      <div class="iconfont icondrag"></div>
                    </template>
                    <!-- 使用 -->
                    <template slot-scope="{ row, index }" slot="use">
                      <Checkbox
                        v-model="listOne[index].use"
                        :true-value="1"
                        :false-value="0"
                      ></Checkbox>
                    </template>
                    <!-- 必填 -->
                    <template slot-scope="{ row, index }" slot="required">
                      <Checkbox
                        v-model="listOne[index].required"
                        :disabled="listOne[index].use == 0"
                        :true-value="1"
                        :false-value="0"
                      ></Checkbox>
                    </template>
                    <!-- 用户端展示 -->
                    <template slot-scope="{ row, index }" slot="user_show">
                      <Checkbox
                        v-model="listOne[index].user_show"
                        :disabled="listOne[index].use == 0"
                        :true-value="1"
                        :false-value="0"
                      ></Checkbox>
                    </template>

                    <template slot-scope="{ row, index }" slot="action">
                      <a @click="delInfo(index)" v-if="!listOne[index].param">删除</a>
                    </template>
                  </Table>
                  <div class="upload-text goods">
                    开启使用后，后台添加用户时可填写此信息；开启必填后，后台添加用户时此信息必须填写；开启用户端展示后，在商城用户个人信息中展示
                  </div>
                  <div class="addInfo" @click="addModel = true">新增信息</div>
                  <div class="subBtn mt20" @click="handleSubmit('basic')">
                    保存
                  </div>
                </FormItem>
              </Col>
            </Row>
          </Form>
        </TabPane>
        <!-- ---------------------------------登录注册-------------------------------- -->
        <TabPane label="登录注册" name="register">
          <Alert type="warning" show-icon v-if="loginForm.register_notice">{{loginForm.register_notice}}</Alert>
          <Form :model="loginForm" :label-width="120">
            <Row :gutter="24" type="flex">
              <Col span="24">
                <div class="basics">登录设置</div>
              </Col>
              <Col span="24" class="mt10">
                <FormItem label="强制手机号绑定：">
                  <Switch
                    size="large"
                    v-model="loginForm.store_user_mobile"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">商城登录时强制手机号登陆/绑定</div>
                </FormItem>
              </Col>
              <Col span="24">
                <div class="basics">注册有礼</div>
              </Col>
              <Col span="24" class="mt10">
                <FormItem label="注册有礼启用：">
                  <Switch
                    size="large"
                    v-model="loginForm.newcomer_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">新用户注册后，会给用户赠送礼品</div>
                </FormItem>
              </Col>
              <Col span="24" v-if="loginForm.newcomer_status === 1">
                <FormItem
                  label="是否限时："
                  v-if="loginForm.newcomer_status === 1"
                >
                  <RadioGroup v-model="loginForm.newcomer_limit_status">
                    <Radio :label="0">
                      <span>不限时</span>
                    </Radio>
                    <Radio :label="1">
                      <span>限时</span>
                    </Radio>
                  </RadioGroup>
                  <div class="upload-text">新人注册活动的时间设置</div>
                  <div
                    class="mt10"
                    v-if="loginForm.newcomer_limit_status == 1"
                  >
                    <Input
                      v-model="loginForm.newcomer_limit_time"
                      placeholder="请输入限时天数"
                      class="inputw"
                    ></Input>
                    <span
                      class="span-text"
                      v-if="loginForm.newcomer_limit_status == 1"
                    >
                      天
                    </span>
                  </div>
                </FormItem>
              </Col>
              <Col span="24" class="mt10">
                <FormItem
                  label="赠送积分："
                  v-if="loginForm.newcomer_status === 1"
                >
                  <Switch
                    size="large"
                    v-model="loginForm.register_integral_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">用户注册后即赠送一定数额的积分</div>
                  <Input
                    v-model="loginForm.register_give_integral"
                    placeholder="请输入赠送积分"
                    class="inputw mt10"
                    v-if="loginForm.register_integral_status === 1"
                  ></Input>
                  <span
                    class="span-text"
                    v-if="loginForm.register_integral_status === 1"
                  >
                    积分
                  </span>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="loginForm.newcomer_status === 1"
              >
                <FormItem
                  label="赠送余额："
                  v-if="loginForm.newcomer_status === 1"
                >
                  <Switch
                    size="large"
                    v-model="loginForm.register_money_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">
                    用户注册后即赠送一定数额的储值余额
                  </div>
                  <Input
                    v-model="loginForm.register_give_money"
                    placeholder="请输入赠送余额"
                    class="inputw mt10"
                    v-if="loginForm.register_money_status === 1"
                  ></Input>
                  <span
                    class="span-text"
                    v-if="loginForm.register_money_status === 1"
                  >
                    元
                  </span>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="loginForm.newcomer_status === 1"
              >
                <div
                  class="item"
                  v-for="(item, indexw) in promotionsData"
                  :key="indexw"
                >
                  <!-- ----赠送优惠券------ -->
                  <FormItem
                    label="赠送优惠券："
                    v-if="loginForm.newcomer_status === 1"
                  >
                    <Switch
                      size="large"
                      v-model="loginForm.register_coupon_status"
                      :true-value="1"
                      :false-value="0"
                    >
                      <span slot="open">开启</span>
                      <span slot="close">关闭</span>
                    </Switch>
                    <div class="upload-text">用户注册后即赠送优惠券</div>
                    <Table
                      border
                      :columns="columns1"
                      :data="item.giveCoupon"
                      ref="table"
                      class="table mt10"
                      width="700"
                      v-if="
                        loginForm.register_coupon_status === 1 &&
                        item.giveCoupon.length > 0
                      "
                    >
                      <template slot-scope="{ row }" slot="coupon_price">
                        <span v-if="row.coupon_type == 1">
                          {{ row.coupon_price }}元
                        </span>
                        <span v-if="row.coupon_type == 2">
                          {{ parseFloat(row.coupon_price) / 10 }}折（{{
                            row.coupon_price.toString().split('.')[0]
                          }}%）
                        </span>
                      </template>
                      <template slot-scope="{ row }" slot="coupon_type">
                        <span v-if="row.coupon_type === 1">满减券</span>
                        <span v-else>折扣券</span>
                      </template>
                      <template slot-scope="{ row, index }" slot="status">
                        <a @click="delCoupon(index, indexw)">删除</a>
                      </template>
                    </Table>
                    <div
                      class="add-coupon"
                      @click="addCoupon(indexw)"
                      v-if="loginForm.register_coupon_status === 1"
                    >
                      + 添加优惠券
                    </div>
                  </FormItem>
                </div>
              </Col>
              <!-- ----赠送优惠券------ -->
              <Col
                span="24"
                class="mt10"
                v-if="loginForm.newcomer_status === 1"
              >
                <FormItem label="首单优惠：">
                  <Switch
                    size="large"
                    v-model="loginForm.first_order_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">
                    新用户下单时可享折扣，折扣仅对商品打折，运费无折扣
                  </div>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="
                  loginForm.newcomer_status === 1 &&
                  loginForm.first_order_status === 1
                "
              >
                <FormItem label="折扣力度：">
                  <Input
                    v-model="loginForm.first_order_discount"
                    placeholder="请输入折扣力度"
                    class="inputw"
                  >
                  </Input>
                  <span class="span-text">%</span>
                  <div class="upload-text">
                    折扣力度为：0-100%，1折为10%
                  </div>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="
                  loginForm.newcomer_status === 1 &&
                  loginForm.first_order_status === 1
                "
              >
                <FormItem label="折扣上限：">
                  <Input
                    v-model="loginForm.first_order_discount_limit"
                    placeholder="请输入折扣上限"
                    class="inputw"
                  >
                  </Input>
                  <span class="span-text">元</span>
                  <div class="upload-text">
                    首单优惠最高金额，单位：元
                  </div>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="loginForm.newcomer_status === 1"
              >
                <FormItem label="新人专享价：">
                  <Switch
                    size="large"
                    v-model="loginForm.register_price_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">
                    新用户可购买一件新人商品，购买后移动端不再显示新人专区
                  </div>

                  <!-- ----添加商品----- -->
                  <vxe-table
                    border="inner"
                    ref="xTree"
                    :column-config="{ resizable: true }"
                    row-id="id"
                    :tree-config="{ children: 'attrValue', reserve: true }"
                    :data="tableData"
                    @checkbox-all="selectAll"
                    @checkbox-change="selectAll"
                    class="goods mt10"
                    :header-cell-style="{
                      background: '#F7F7F7',
                      height: '40px',
                    }"
                    v-if="loginForm.register_price_status === 1"
                  >
                    <vxe-column
                      type="checkbox"
                      title="多选"
                      width="90"
                      tree-node
                    ></vxe-column>
                    <vxe-column
                      field="id"
                      title="ID"
                      min-width="80"
                    ></vxe-column>
                    <vxe-column field="info" title="商品信息" min-width="200">
                      <template v-slot="{ row }">
                        <div class="imgPic acea-row row-middle">
                          <viewer>
                            <div class="pictrue">
                              <img v-lazy="row.image" />
                            </div>
                          </viewer>
                          <div class="info">
                            <Tooltip
                              max-width="200"
                              placement="bottom"
                              transfer>
                              <span class="line2">{{ row.store_name }}{{ row.suk }}</span>
                              <p slot="content">{{ row.store_name }}{{ row.suk }}</p>
                            </Tooltip>
                          </div>
                        </div>
                      </template>
                    </vxe-column>

                    <vxe-column
                      field="price"
                      title="售价"
                      min-width="80"
                    ></vxe-column>

                    <vxe-column
                      field="stock"
                      title="库存"
                      min-width="80"
                    ></vxe-column>

                    <!-- 活动价 -->
                    <vxe-column field="date" title="活动价" min-width="200">
                      <template v-slot="{ row }">
                        <Input
                          v-model="row.ativity_price"
                          :border="false"
                          placeholder="请输入活动价"
                          @on-change="inputChange(row)"
                        />
                      </template>
                    </vxe-column>
                    <!-- 活动价 -->
                    <vxe-column
                      field="date"
                      title="操作"
                      min-width="50"
                      fixed="right"
                      align="center"
                    >
                      <template v-slot="{ row }">
                        <a @click="del(row)">删除</a>
                      </template>
                    </vxe-column>
                  </vxe-table>
                  <div
                    class="add-goods"
                    v-if="loginForm.register_price_status === 1"
                  >
                    <Button @click="addGoods">添加商品</Button>
                    <Button @click="activityShowFn" class="goods-btn">
                      设置活动价
                    </Button>
                    <Button @click="delAll">批量删除</Button>
                  </div>
                  <!-- ----添加商品----- -->
                </FormItem>
              </Col>
              <Col span="24" class="mt10">
                <FormItem
                  label="规则详情："
                  v-if="loginForm.newcomer_status === 1"
                >
                  <WangEditor
                    class="goods"
                    :content="loginForm.newcomer_agreement"
                    @editorContent="getEditorContent"
                  ></WangEditor>
                </FormItem>
              </Col>
              <Col>
                <FormItem>
                  <div
                    class="subBtn"
                    style="margin-top: 0px;"
                    @click="handleSubmit('register')"
                  >
                    保存
                  </div>
                </FormItem>
              </Col>
            </Row>
          </Form>
        </TabPane>
        <!-- ---------------------------------会员等级-------------------------------- -->
        <TabPane label="会员等级" name="level">
          <Form :model="vipForm" :label-width="120">
            <Row :gutter="24" type="flex">
              <Col span="24">
                <div class="basics">基础设置</div>
              </Col>
              <Col span="24" class="mt10">
                <FormItem label="会员等级启用：">
                  <Switch
                    size="large"
                    v-model="vipForm.member_func_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">
                    开启会员等级后，可以获得经验值
                  </div>
                </FormItem>
              </Col>
              <Col
                span="24"
                v-if="vipForm.member_func_status === 1"
              >
                <FormItem label="下单获得经验：">
                  <Input
                    v-model="vipForm.order_give_exp"
                    placeholder="请输入获得经验值"
                    class="inputw"
                  />
                  <div class="upload-text">
                    用户实际支付1元，可以获得多少经验值
                  </div>
                </FormItem>
              </Col>
              <Col
                span="24"
                v-if="vipForm.member_func_status === 1"
              >
                <FormItem label="签到获得经验：">
                  <Input
                    v-model="vipForm.sign_give_exp"
                    placeholder="请输入签到获得经验值"
                    class="inputw"
                  />
                  <div class="upload-text">用户签到一次，赠送多少经验值</div>
                </FormItem>
              </Col>
              <Col
                span="24"
                v-if="vipForm.member_func_status === 1"
              >
                <FormItem label="邀请新用户获得经验：">
                  <Input
                    v-model="vipForm.invite_user_exp"
                    placeholder="请输入获取新用户获得经验值"
                    class="inputw"
                  />
                  <div class="upload-text">
                    邀请一个新用户注册，赠送多少经验值
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <div class="basics">激活有礼</div>
              </Col>
              <Col span="24" class="mt10">
                <FormItem label="会员卡激活：">
                  <Switch
                    size="large"
                    v-model="vipForm.level_activate_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">
                    开启后用户等级功能不能直接使用，需要用户填写信息，激活后才能使用用户等级
                  </div>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="vipForm.level_activate_status === 1"
              >
                <FormItem label="会员卡信息：">
                  <Table
                    :columns="columns3"
                    :data="listVip"
                    ref="table"
                    class="mt10 mb10 goods"
                    :loading="loading"
                    highlight-row
                    no-userFrom-text="暂无数据"
                    no-filtered-userFrom-text="暂无筛选结果"
                    v-if="listVip.length > 0"
                  >
                    <!-- 必填 -->
                    <template slot-scope="{ row, index }" slot="required">
                      <Checkbox v-model="listVip[index].required" @on-change="tapCheckbox"></Checkbox>
                    </template>
                    <template slot-scope="{ row, index }" slot="action">
                      <a @click="delVip(row, index)">删除</a>
                    </template>
                  </Table>
                  <Button @click="informationTap">
                    选择信息
                  </Button>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="vipForm.level_activate_status === 1"
              >
                <FormItem label="赠送积分：">
                  <Switch
                    size="large"
                    v-model="vipForm.level_integral_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">
                    用户激活会员卡后即赠送一定数额的积分
                  </div>
                  <Input
                    v-model="vipForm.level_give_integral"
                    placeholder="请输入赠送的积分"
                    class="inputw mt10"
                    v-if="vipForm.level_integral_status === 1"
                  ></Input>
                  <span
                    class="span-text"
                    v-if="vipForm.level_integral_status === 1"
                  >
                    积分
                  </span>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="vipForm.level_activate_status === 1"
              >
                <FormItem label="赠送余额：">
                  <Switch
                    size="large"
                    v-model="vipForm.level_money_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">
                    用户激活会员卡后即赠送一定数额的储值余额
                  </div>
                  <Input
                    v-model="vipForm.level_give_money"
                    placeholder="请输入赠送的余额"
                    class="inputw mt10"
                    v-if="vipForm.level_money_status === 1"
                  />
                  <span
                    class="span-text"
                    v-if="vipForm.level_money_status === 1"
                  >
                    元
                  </span>
                </FormItem>
              </Col>
              <Col
                span="24"
                class="mt10"
                v-if="vipForm.level_activate_status === 1"
              >
                <FormItem label="赠送优惠券：">
                  <Switch
                    size="large"
                    v-model="vipForm.level_coupon_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                  <div class="upload-text">
                    用户激活会员卡后即赠送优惠券
                  </div>

                  <div
                    class="item"
                    v-for="(item, indexw) in promotionsData"
                    :key="indexw"
                  >
                    <div
                      class="add-coupon"
                      @click="addCoupon(indexw)"
                      v-if="vipForm.level_coupon_status === 1"
                    >
                      + 添加优惠券
                    </div>
                    <Table
                      border
                      :columns="columns1"
                      :data="vipCopon"
                      ref="table"
                      class="table"
                      width="700"
                      v-if="
                        vipCopon.length > 0 && vipForm.level_coupon_status === 1
                      "
                    >
                      <template slot-scope="{ row }" slot="coupon_price">
                        <span v-if="row.coupon_type == 1">
                          {{ row.coupon_price }}元
                        </span>
                        <span v-if="row.coupon_type == 2">
                          {{ parseFloat(row.coupon_price) / 10 }}折（{{
                            row.coupon_price.toString().split('.')[0]
                          }}%）
                        </span>
                      </template>
                      <template slot-scope="{ row }" slot="coupon_type">
                        <span v-if="row.coupon_type === 1">满减券</span>
                        <span v-else>折扣券</span>
                      </template>
                      <template slot-scope="{ row, index }" slot="status">
                        <a @click="delCoupon(index, indexw)">删除</a>
                      </template>
                    </Table>
                  </div>
                </FormItem>
              </Col>
              <Col>
                <FormItem>
                  <div class="subBtn mt10" @click="handleSubmit('level')">
                    保存
                  </div>
                </FormItem>
              </Col>
            </Row>
          </Form>
        </TabPane>
        <!-- ---------------------------------付费会员-------------------------------- -->
        <TabPane label="付费会员" name="svip">
          <Form :model="basicsForm" :label-width="120">
            <Row :gutter="24" type="flex">
              <Col span="24" class="mt10">
                <FormItem label="付费会员启用：">
                  <Switch
                    size="large"
                    v-model="member_card_status"
                    :true-value="1"
                    :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                </FormItem>
                <FormItem label="付费会员价展示：" v-if="member_card_status == 1">
                  <Switch
                      size="large"
                      v-model="svip_price_status"
                      :true-value="1"
                      :false-value="0"
                  >
                    <span slot="open">开启</span>
                    <span slot="close">关闭</span>
                  </Switch>
                </FormItem>
              </Col>
              <Col span="24" class="mt10">
                <FormItem>
                  <div
                    style="margin-top: 0px;"
                    class="subBtn"
                    @click="handleSubmit('svip')"
                  >
                    保存
                  </div>
                </FormItem>
              </Col>
            </Row>
          </Form>
        </TabPane>
      </Tabs>
    </Card>
    <!-- 选择图片弹窗 -->
    <Modal
      v-model="modalPic"
      width="960px"
      scrollable
      footer-hide
      closable
      title="上传用户图片"
      :mask-closable="false"
      :z-index="1"
    >
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </Modal>
    <!-- 新增信息 -->
    <Modal
      v-model="addModel"
      title="新增信息"
      class-name="vertical-center-modal"
      scrollable
      @on-cancel="cancelSubmit"
    >
      <Form
        ref="formValidate"
        :model="formItem"
        :rules="ruleValidate"
        :label-width="90"
      >
        <Row>
          <Col>
            <FormItem label="信息名称：" prop="info">
              <Input
                v-model="formItem.info"
                placeholder="请输入信息名称"
                style="width: 300px;"
              />
            </FormItem>
          </Col>
          <Col>
            <FormItem label="信息格式 ：" prop="format">
              <Select v-model="formItem.format" style="width: 300px;">
                <Option
                  v-for="item in formatList"
                  :value="item.value"
                  :key="item.value"
                >
                  {{ item.label }}
                </Option>
              </Select>
            </FormItem>
          </Col>
          <Col>
            <FormItem
              label="单选项 ："
              prop="singlearr"
              v-if="formItem.format === 'radio'"
            >
              <div class="arrbox">
                <Tag
                  @on-close="handleClose"
                  :name="item"
                  :closable="true"
                  v-for="(item, index) in formItem.singlearr"
                  :key="index"
                >
                  {{ item }}
                </Tag>
                <input
                  class="arrbox_ip percentage9"
                  v-model="formItem.single"
                  placeholder="请输入选项，回车确认"
                  @keyup.enter="addlabel"
                />
              </div>
            </FormItem>
          </Col>
          <Col>
            <FormItem label="提示文案：" prop="tip">
              <Input
                v-model="formItem.tip"
                placeholder="请输入提示文案"
                style="width: 300px;"
              />
            </FormItem>
          </Col>
        </Row>
      </Form>

      <div slot="footer" class="acea-row row-right">
        <Button @click="cancelSubmit">取消</Button>
        <Button type="primary" @click="addSubmit">提交</Button>
      </div>
    </Modal>
    <!-- 添加优惠券 -->
    <coupon-list
      ref="couponTemplates"
      @getCouponList="getCouponList"
      :discount="true"
    ></coupon-list>
    <!-- 添加商品 -->
    <Modal
      v-model="modals"
      title="商品列表"
      footerHide
      class="paymentFooter"
      scrollable
      width="900"
    >
      <goods-list
        ref="goodslist"
        :ischeckbox="true"
        :isdiy="true"
        @getProductId="getProductId"
        v-if="modals"
      ></goods-list>
    </Modal>
    <!-- 选择信息 -->

    <information
      ref="information"
      @getInfoList="getInfoList"
      :listOne="listOne"
    ></information>

    <!-- 设置活动价 -->
    <Modal
      v-model="activityShow"
      title="设置"
      class="paymentFooter"
      width="600"
      :closable="false"
      :mask-closable="false"
      footer-hide
    >
      <Form :model="formActive" :rules="ruleActive" ref="activityShow" :label-width="100">
        <FormItem label="设置活动价：" prop="activeInput">
          <InputNumber
            v-model="formActive.activeInput"
            placeholder="请输入活动价格"
            class="inputw"
            :min="0"
          >
          </InputNumber>
        </FormItem>
        <div class="acea-row row-right">
          <Button @click="cancel('activityShow')">取消</Button>
          <Button class="ml15 mr5" type="primary" @click="ok('activityShow')">提交</Button>
        </div>
      </Form>
    </Modal>
  </div>
</template>
<script>
import uploadPictures from '@/components/uploadPictures'
import couponList from '@/components/couponList'
import goodsList from '@/components/goodsList'
import information from '@/components/information'
import WangEditor from '@/components/wangEditor/index.vue'
import { settingUser, setUser } from '@/api/user.js'
export default {
  name: 'setupUser',
  components: {
    uploadPictures,
    couponList,
    goodsList,
    WangEditor,
    information,
  },
  props: {},
  data() {
    const validatorSingle = (rule, value, callback)=>{
      if(value.length<2){
        callback(new Error('单选项最少输入2个'));
      }else{
        callback();
      }
    };
    const validatorActive = (rule, value, callback)=>{
      if(value===""||value == null || value<0){
        callback(new Error('活动价不能为空'));
      }else{
        callback();
      }
    };
    return {
      tabVal: 'basic',
      paySwitch: 1,
      phoneSwitch: 1,
      indexCoupon: 0,
      val: '',
      formActive:{
        activeInput: 0
      },
      basicsForm: {},
      selectArr: [],
      value: '',
      formItem: {
        info: '',
        format: '',
        tip: '',
        single: '',
        singlearr: [],
      },
      activityShow: false,
      isChoice: '单选',
      modalPic: false,
      loading: false,
      addModel: false,
      inputShow: false,
      modals: false, // 添加商品弹窗
      authorizedPicture: '', // 图片
      //选中的数组
      ids: [],
      // 基础信息表单
      // basicsForm: {
      //   h5_avatar: 'jjj',
      //   format: '',
      // },
      avatar: {},

      member_card_status:1,
      svip_price_status: 0,
      // 登录注册表单
      loginForm: {
        newcomer_status: '1',
        store_user_mobile: '', // 手机号强制开启
        newcomer_limit_status: '', // 是否限时
        newcomer_limit_time: '', // 限时时间
        register_integral_status: '', // 赠送积分开启或者关闭 1开启0关闭
        register_give_integral: '', // 赠送积分数量
        register_money_status: '', // 赠送余额开启
        register_give_money: '', // 赠送余额数量
        register_coupon_status: '', // 赠送优惠券开启
        register_give_coupon: [], // 赠送优惠券数量
        first_order_status: '', // 首单优惠开启
        first_order_discount: '', // 首单优惠折扣
        first_order_discount_limit: '', // 首单优惠折扣上限
        register_price_status: '', // 新人专享价开启
        product: [],
        newcomer_agreement: '',
        register_notice: ''
      },
      newcomer_agreement: '',
      product_list: [],
      // 会员等级表单
      vipForm: {
        member_func_status: 0, // 等级启用
        sign_give_exp: '', //签到赠送
        order_give_exp: '', // 下单赠送
        invite_user_exp: '', // 邀请新用户
        level_activate_status: 1, // 会员卡激活开启 1开启 0 关闭
        level_extend_info: [], // 会员卡信息
        level_integral_status: 1, // 赠送积分开启
        level_give_integral: 8, // 赠送积分数量
        level_money_status: 1, // 赠送余额开启
        level_give_money: 15, // 赠送余额数量
        level_coupon_status: 1, // 赠送优惠券开启
        level_give_coupon: [], // 赠送优惠券数量
      },
      isShow: false,

      formatList: [
        {
          value: 'text',
          label: '文本',
        },
        {
          value: 'num',
          label: '数字',
        },
        {
          value: 'date',
          label: '日期',
        },
        {
          value: 'radio',
          label: '单选项',
        },
        {
          value: 'id',
          label: '身份证',
        },
        {
          value: 'mail',
          label: '邮件',
        },
        {
          value: 'phone',
          label: '手机号',
        },
        {
          value: 'address',
          label: '地址',
        },
      ],
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      columns: [
        {
          title: '',
          slot: 'drag',
          width: 50,
        },
        {
          title: '信息',
          key: 'info',
          width: 120,
        },
        {
          title: '使用',
          slot: 'use',
          width: 70,
        },
        {
          title: '必填',
          slot: 'required',
          width: 70,
        },
        {
          title: '用户端展示',
          slot: 'user_show',
          minWidth: 70,
        },
        {
          title: '信息格式',
          key: 'label',
          minWidth: 120,
        },
        {
          title: '提示信息',
          key: 'tip',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          minWidth: 80,
        },
      ],
      columns3: [
        {
          title: '信息',
          key: 'info',
          width: 120,
        },

        {
          title: '必填',
          slot: 'required',
          width: 70,
        },

        {
          title: '信息格式',
          key: 'label',
          minWidth: 120,
        },
        {
          title: '提示信息',
          key: 'tip',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          minWidth: 80,
        },
      ],
      listOne: [],
      listVip: [],
      promotionsData: [
        {
          threshold: 0,
          give_integral: 0,
          checkIntegral: false,
          checkCoupon: false,
          checkGoods: false,
          giveProducts: [],
          giveCoupon: [],
        },
      ],
      tableData: [],
      columns1: [
        {
          title: '优惠券名称',
          key: 'title',
          minWidth: 150,
        },
        {
          title: '类型',
          slot: 'coupon_type',
          minWidth: 80,
        },
        {
          title: '面值',
          slot: 'coupon_price',
          minWidth: 100,
        },
        {
          title: '最低消费额',
          key: 'use_min_price',
          minWidth: 100,
        },
        // {
        //   title: '限量',
        //   key: 'limit_num',
        //   width: 120,
        //   render: (h, params) => {
        //     return h('div', [
        //       h('InputNumber', {
        //         props: {
        //           value: params.row.limit_num,
        //           placeholder: '请输入',
        //           precision: 0,
        //           min: 0,
        //         },
        //         on: {
        //           'on-change': (e) => {
        //             params.row.limit_num = e
        //             this.promotionsData[params.row.indexCoupon].giveCoupon[
        //               params.index
        //             ].limit_num = e
        //           },
        //         },
        //       }),
        //     ])
        //   },
        // },
        {
          title: '操作',
          slot: 'status',
          align: 'center',
          minWidth: 80,
        },
      ],
      ruleValidate: {
        info: [
          {
            required: true,
            message: '信息名称不能为空',
            trigger: 'blur',
          },
        ],
        format: [
          {
            required: true,
            message: '信息格式不能为空',
            trigger: 'blur',
          },
        ],
        tip: [
          {
            required: true,
            message: '信息文案不能为空',
            trigger: 'blur',
          },
        ],
        singlearr: [
          { required: true, validator:validatorSingle,type: 'array', trigger: 'blur' },
        ],
      },
      ruleActive:{
        activeInput: [
          {
            required: true,
            validator:validatorActive,
            trigger: 'blur'
          },
        ]
      },
      couponType: 0,
      vipCopon: [],
    }
  },
  computed: {},
  created() {
    this.settingUser()
  },
  mounted() {},

  methods: {
    tapCheckbox(e){
      console.log('sdsdsd',e)
    },
    informationTap(){
      this.$refs.information.isShow = true;
    },
    onDragDrop(first, end) {
      //转成int型，方便后续使用
      first = parseInt(first)
      end = parseInt(end)

      let tmp = this.listOne[first]

      if(first < end) {
        for(var i=first+1; i<=end; i++) {
          this.listOne.splice(i-1,1,this.listOne[i])
        }
        this.listOne.splice(end,1,tmp)
      }

      if(first > end) {
        for(var i=first; i>end; i--) {
          this.listOne.splice(i, 1, this.listOne[i-1])
        }
        this.listOne.splice(end, 1, tmp)
      }

      //重置排序值
      let index = 1
      this.listOne.forEach(e => {
        e.sort = index
        index++
      })
      //排序值重置后，向后台发送请求，更新数据库中数据的排序，这里就不写了
      //axios

      console.log(JSON.stringify(this.listOne))
    },
    // 获取用户配置
    settingUser() {
      settingUser(this.tabVal).then((res) => {
        if (this.tabVal === 'basic') {
          this.authorizedPicture = res.data.h5_avatar
          this.listOne = res.data.user_extend_info
        }
        if (this.tabVal === 'register') {
          this.loginForm = res.data
          this.promotionsData[0].giveCoupon = res.data.register_give_coupon

          // 添加活动价格
          const addKey = (uni) =>
            uni.map((item) => ({
              ...item,
              ativity_price: item.price,
              id: item.product_id,
              attrValue: item.attrValue ? addKey(item.attrValue) : [],
            }))
          this.tableData = addKey(res.data.product)
        }
        if (this.tabVal === 'level') {
          this.vipForm = res.data
          this.vipCopon = res.data.level_give_coupon
          res.data.level_extend_info.forEach(item=>{
            if(item.required==1 || item.required==true){
              item.required = true
            }else{
              item.required = false
            }
          });
          this.listVip = res.data.level_extend_info
        }
        if (this.tabVal === 'svip') {
          this.member_card_status = res.data.member_card_status;
          this.svip_price_status = res.data.svip_price_status
        }
      })
    },

    //全选
    selectAll(row) {
      this.selectArr = row.records
    },

    // 批量设置活动价
    activityShowFn() {
      if (this.selectArr.length === 0) {
        this.$Message.error('请先选择设置活动价的商品！')
      } else {
        this.activityShow = true
      }
    },

    cancel(name){
      this.activityShow = false
      this.$refs[name].resetFields();
    },

    ok(name) {
      this.$refs[name].validate((valid)=>{
        if(valid){
          this.selectArr.forEach((item) => {
            item.ativity_price = this.formActive.activeInput
          });
          this.activityShow = false
          this.$refs[name].resetFields();
        }
      });
    },

    // 批量删除商品
    delAll() {
      if (this.selectArr.length === 0) {
        this.$Message.error('请先选择删除的商品！')
      } else {
        this.$Modal.confirm({
          title: '删除确认',
          content: '您确认要删除这些商品？',
          onOk: () => {
            this.selectArr.forEach((row) => {
              this.tableData.forEach((i, index) => {
                if (row.id == i.id) {
                  this.tableData.splice(index, 1)
                } else {
                  i.attrValue.forEach((j, indexn) => {
                    if (row.id == j.id) {
                      if (i.attrValue.length == 1) {
                        this.tableData.splice(index, 1)
                      } else {
                        i.attrValue.splice(indexn, 1)
                      }
                    }
                  })
                }
              })
            })
          },
        })
      }
    },

    // 切换tabs
    tabChange() {
      this.settingUser()
    },

    // 选中图片
    getPic(pc) {
      this.authorizedPicture = pc.att_dir
      this.modalPic = false
    },

    // 选择图片
    modalPicTap() {
      this.modalPic = true
    },

    // 取消新增信息
    cancelSubmit() {
      this.formItem = {
        info: '',
        format: '',
        tip: '',
        single: '',
        singlearr: [],
      }
      this.addModel = false
      this.$refs.formValidate.resetFields()
    },
    // 提交信息
    addSubmit() {
      this.$refs.formValidate.validate((valid) => {
        let obj = {
          ...this.formItem,
          required: 0,
          use: 0,
          user_show: 0,
          label: ''
        };
        switch (obj.format) {
          case 'text':
            obj.label = '文本';
            break;
          case 'num':
            obj.label = '数字';
            break;
          case 'date':
            obj.label = '日期';
            break;
          case 'radio':
            obj.label = '单选项';
            break;
          case 'id':
            obj.label = '身份证';
            break;
          case 'mail':
            obj.label = '邮件';
            break;
          case 'phone':
            obj.label = '手机号';
            break;
          case 'address':
            obj.label = '地址';
            break;
        }
        let labelName = [];
        this.listOne.forEach(item=>{
          labelName.push(item.info)
        });
        if (valid) {
          if(labelName.indexOf(obj.info) == -1){
            this.listOne.push(obj)
            this.cancelSubmit();
          }else{
            this.$Message.error('该信息已经添加过')
          }
        }
      })
    },
    // 信息删除
    delInfo(index) {
      this.listOne.splice(index, 1)
    },
    delVip(row, index) {
      this.listVip.splice(index, 1)
    },

    // 输入后回车
    addlabel() {
      if (!this.formItem.single) {
        return
      }
      let count = this.formItem.singlearr.indexOf(this.formItem.single)
      if (count === -1) {
        this.formItem.singlearr.push(this.formItem.single)
      }
      this.formItem.single = ''
    },

    // 表单提交
    handleSubmit(val) {
      switch (val) {
        case 'basic':
          let data = {
            h5_avatar: this.authorizedPicture,
            user_extend_info: this.listOne,
          }
          setUser(val, data).then((res) => {
            this.$Message.success(res.msg)
          })
          break
        case 'register':
          this.product_list = []
          this.tableData.forEach((item) => {
            let obj = {
              product_id: item.id,
              price: item.ativity_price,
              attr: [],
            }

            if (item.attrValue.length) {
              item.attrValue.forEach((j) => {
                let newAttr = { unique: j.unique, price: j.ativity_price }
                obj.attr.push(newAttr)
              })
            }
            this.product_list.push(obj)
          })
          let ids = this.promotionsData[0].giveCoupon.map((item) => item.id)
          this.loginForm.register_give_coupon = Array.from(new Set(ids))
          this.loginForm.product = this.product_list
          this.loginForm.newcomer_agreement = this.newcomer_agreement
          setUser(val, this.loginForm).then((res) => {
            this.$Message.success(res.msg)
          }).catch(err=>{
            this.$Message.error(err.msg)
          })
          break
        case 'level':
          let arrIds = this.vipCopon.map((item) => item.id)
          this.vipForm.level_give_coupon = Array.from(new Set(arrIds))
          this.vipForm.level_extend_info = this.listVip
          setUser(val, this.vipForm).then((res) => {
            this.$Message.success(res.msg)
          }).catch(err=>{
            this.$Message.error(err.msg)
          });
          break
        case 'svip':
          let vipData = {
            member_card_status: this.member_card_status,
            svip_price_status: this.svip_price_status,
          }
          setUser(val, vipData).then((res) => {
            this.$Message.success(res.msg)
          })
          break
      }
    },

    // 添加优惠券
    addCoupon(index) {
      this.indexCoupon = index
      this.$refs.couponTemplates.isTemplate = true
      this.$refs.couponTemplates.tableList()
    },
    handleClose(event, name) {
      const index = this.formItem.singlearr.indexOf(name)
      this.formItem.singlearr.splice(index, 1)
    },

    // 优惠卷表格
    getCouponList(data) {
      let indexCoupon = this.indexCoupon
      this.$refs.couponTemplates.isTemplate = false
      data.forEach((j) => {
        j.limit_num = 0
        j.indexCoupon = indexCoupon
      })
      let list = this.promotionsData[indexCoupon].giveCoupon.concat(data)
      let uni = this.unique(list)
      if (this.tabVal === 'register') {
        this.promotionsData[indexCoupon].giveCoupon = uni
      } else {
        this.vipCopon = uni
      }
    },

    // 删除优惠券
    delCoupon(index, indexw) {
      if (this.tabVal === 'level') {
        this.vipCopon.splice(index, 1)
      }
      this.promotionsData[indexw].giveCoupon.splice(index, 1)
    },

    // 添加商品
    addGoods(index) {
      this.modals = true
    },

    // 设置活动价格
    inputChange(row) {
      if (row.attrValue.length > 0) {
        row.attrValue.forEach((item) => {
          item.ativity_price = row.ativity_price
        })
      }
    },

    // 删除商品
    del(row) {
      this.tableData.forEach((i, index) => {
        if (row.id == i.id) {
          return this.tableData.splice(index, 1)
        } else {
          i.attrValue.forEach((j, indexn) => {
            if (row.id == j.id) {
              if (i.attrValue.length == 1) {
                return this.tableData.splice(index, 1)
              } else {
                return i.attrValue.splice(indexn, 1)
              }
            }
          })
        }
      })
    },

    // 对象数组去重
    unique(arr) {
      const res = new Map()
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1))
    },

    // 添加商品
    getProductId(data) {
      this.modals = false
      let list = this.tableData.concat(data)
      let uni = this.unique(list)
      uni.forEach((i) => {
        i.attrValue.forEach((j) => {
          j.cate_name = i.cate_name
          j.store_label = i.store_label
        })
      })

      // 添加活动价格
      const addKey = (uni) =>
        uni.map((item) => ({
          ...item,
          ativity_price: '',
          attrValue: item.attrValue ? addKey(item.attrValue) : [], // 这里要判断原数据有没有子级如果没有判断会报错
        }))
      this.tableData = addKey(uni)
    },

    // 选择信息
    getInfoList(data) {
      let list = this.listVip.concat(data)
      let uni = this.uniqueVip(list)
      uni.forEach(item=>{
        if(item.required==1 || item.required==true){
          item.required = true
        }else{
          item.required = false
        }
      });
      this.listVip = uni
      this.$refs.information.isShow = false
    },

    // 对象数组去重；
    uniqueVip(arr) {
      const res = new Map()
      return arr.filter((arr) => !res.has(arr.info) && res.set(arr.info, 1))
    },

    // 规则详情
    getEditorContent(data) {
      this.newcomer_agreement = data;
    }
  },
}
</script>
<style scoped lang="stylus">
.span-text {
 margin-left:8px;
 font-size: 12px;

}
.goods /deep/.ivu-table-cell{
  line-height 21px !important
}
.goods{
  .icondrag{
    color #ccc
  }
}
/deep/.ivu-form-item-label {
  font-size: 12px;
  font-weight: 400;
  color: #333333;
}
/deep/.ivu-input-group-append {
 font-size: 12px;
}
/deep/ .vxe-table--render-default .vxe-table--header {
 font-size: 12px;
}

.inputw {
  width: 460px;
}

.pay {
  width: 100%;
  height: 100%;
}

.basics {
  width: 76px;
  height: 16px;
  text-align: center;
  margin-top: 10px;
  border-left: 2px solid #2D8CF0;
  line-height: 16px;
  font-size: 14px;
  font-weight: 600;
  color: #333333;
}
.subBtn {
width: 54px;
height: 32px;
background: #2D8CF0;
border-radius: 4px;
font-size: 12px;
font-weight: 500;
color: #FFFFFF;
text-align:center;
line-height 32px;
  cursor:pointer;
}

.arrbox {
  background-color: white;
  font-size: 12px;
  border: 1px solid #dcdee2;
  border-radius: 6px;
  margin-bottom: 0px;
  padding: 0 5px;
  text-align: left;
  box-sizing: border-box;
  width: 300px;
}

.arrbox_ip {
  font-size: 12px;
  border: none;
  box-shadow: none;
  outline: none;
  background-color: transparent;
  padding: 0;
  margin: 0;
  width: auto !important;
  max-width: inherit;
  min-width: 80px;
  vertical-align: top;
  height: 30px;
  color: #34495e;
  margin: 2px;
  line-height: 30px;
}

.vertical-center-modal {
  display: flex;
  align-items: center;
  justify-content: center;
}

.uploadPictrue {
  width: 60px;
  height: 60px;
  background: #F5F5F5;
  border-radius: 4px;
  border: 1px dashed #DDDDDD;
  text-align: center;
  line-height: 60px;

  img {
    width: 100%;
    height: 100%;
  }
}

.upload-text {
  font-size: 12px;
  line-height: 16px;
  font-weight: 400;
  color: #CCCCCC;
  margin-top: 6px;
}

.addInfo {
  width: 78px;
  height: 32px;
  border-radius: 4px;
  border: 1px solid rgba(151, 151, 151, 0.36);
  text-align: center;
  line-height: 32rpx;
  font-size: 12px;
  font-weight: 400;
  color: rgba(0, 0, 0, 0.85);
  margin-top: 20px;
  cursor:pointer;
}

.add-coupon {
  font-size: 12px;
  font-weight: 400;
  color: #1890FF;
  cursor:pointer;
}

.imgPic {
  .info {
    width: 60%;
    margin-left: 10px;
  }

  .pictrue {
    width: 36px;
    height: 36px;
    margin: 7px 3px 0 3px;

    img {
      width: 100%;
      height: 100%;
      display: block;
    }
  }
}
.goods {
 width: 780px !important;
}
.add-goods {
margin-top: 20px;
display: flex;
.goods-btn {
 margin:0 20px;
}
.paging {
 margin-left: 170px;
}
}
.vxe-header--row {
background: pink;
}
.footer {
  width: 100%;
  height: 65px;
  background: #FFFFFF;
  position: fixed;
  right: 0;
  bottom: 0;
  left: 200px;
  z-index: 10;

  .btn {
    margin-left: 40%;
  }
}
</style>
