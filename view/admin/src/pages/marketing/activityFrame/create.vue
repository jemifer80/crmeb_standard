<template>
  <div>
    <div class="i-layout-page-header">
      <PageHeader class="product_tabs" hidden-breadcrumb>
        <div slot="title">
          <router-link :to="{ path: '/admin/marketing/activity_frame' }">
            <div class="font-sm after-line">
              <span class="iconfont iconfanhui"></span>
              <span class="pl10">返回</span>
            </div>
          </router-link>
          <span
            v-text="$route.params.id ? '编辑活动边框' : '添加活动边框'"
            class="mr20 ml16"
          ></span>
        </div>
      </PageHeader>
    </div>
    <Card :bordered="false" :padding="0" dis-hover>
      <Form ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="109">
        <Tabs v-model="currentTab">
          <TabPane label="基础设置" name="1">
            <FormItem label="活动名称：" prop="name">
              <Row>
                <Col>
                  <Input
                    v-model="formValidate.name"
                    placeholder="请输入活动名称"
                    class="w_input"
                  ></Input>
                </Col>
              </Row>
            </FormItem>
            <FormItem label="活动时间：" required>
              <Row>
                <Col>
                  <DatePicker type="datetime" @on-change="onchangeStart" :options="startPickOptions" :value="startTime" v-model="startTime" style="width: 210px" placeholder="开始时间"></DatePicker>
                </Col>
                <Col class="ml18 mr18" style="text-align: center">-</Col>
                <Col>
                  <DatePicker type="datetime" @on-change="onchangeEnd" :options="endPickOptions" v-model="endTime" style="width: 210px" placeholder="结束时间"></DatePicker>
                </Col>
              </Row>
              <Row>
                <Col class="tips">设置活动开始时间与结束时间</Col>
              </Row>
            </FormItem>
            <FormItem label="上传活动图：" prop="image">
              <Row>
                <Col span="24">
                  <div v-if="formValidate.image" class="upload-list">
                    <div class="upload-item">
                      <img v-lazy="formValidate.image" />
                      <Button
                        shape="circle"
                        icon="ios-close"
                        @click="delImage"
                      ></Button>
                    </div>
                  </div>
                  <Button
                    v-else
                    class="upload-select"
                    type="dashed"
                    icon="ios-add"
                    @click="modalPicTap('dan', 'image', 1)"
                  ></Button>
                </Col>
              </Row>
              <Row>
                <Col class="tips">
                  建议上传大小：宽750px，高750px
                  <Poptip placement="bottom" trigger="hover" width="256" transfer padding="8px">
                    <a>查看示例</a>
                    <div class="exampleImg" slot="content">
                      <img
                              :src="`${baseURL}/statics/system/activityFrame.png`"
                              alt=""
                      />
                    </div>
                  </Poptip>
                </Col>
              </Row>
            </FormItem>
            <FormItem label="是否开启：">
              <i-switch v-model="formValidate.status" :true-value="1" :false-value="0" size="large">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </i-switch>
            </FormItem>
          </TabPane>
          <TabPane label="添加商品" name="2">
            <FormItem :label-width="20" style="margin-top: -12px;">
              <RadioGroup v-model="formValidate.product_partake_type" @on-change="goodTap">
                <Radio label="1">全部商品参与</Radio>
                <Radio label="2">指定商品参与</Radio>
                <Radio label="4">指定品牌参与</Radio>
                <Radio label="5">指定商品标签参与</Radio>
              </RadioGroup>
            </FormItem>
            <Row
              v-if="formValidate.product_partake_type === '2'"
              type="flex"
              justify="space-between"
            >
              <Col>
                <Button type="primary" @click="addGoods">添加商品</Button>
              </Col>
              <!--<Col>-->
                <!--<FormItem label="商品搜索：">-->
                  <!--<Row>-->
                    <!--<Col><Input placeholder="请输入商品名称/ID"></Input></Col>-->
                    <!--<Col><Button type="primary">查询</Button></Col>-->
                  <!--</Row>-->
                <!--</FormItem>-->
              <!--</Col>-->
            </Row>
            <FormItem
              v-if="formValidate.product_partake_type === '4'"
              label="选择品牌："
              required
            >
              <el-cascader
                      placeholder="请选择商品品牌"
                      class="w_input"
                      size="mini"
                      v-model="formValidate.brand_id"
                      :options="brandData"
                      :props="props"
                      filterable
                      clearable>
              </el-cascader>
            </FormItem>
            <FormItem
              v-if="formValidate.product_partake_type === '5'"
              label="选择标签："
              required
            >
              <Row>
                <Col span="11">
                  <div class="select-tag" @click="openStoreLabel">
                    <div v-if="storeDataLabel.length">
                      <Tag
                        v-for="item in storeDataLabel"
                        :key="item.id"
                        closable
                        @on-close="closeLabel(item)"
                        >{{ item.label_name }}</Tag
                      >
                    </div>
                    <span v-else class="placeholder">请选择</span>
                    <Icon type="ios-arrow-down" />
                  </div>
                </Col>
              </Row>
            </FormItem>
            <Table
                    v-if="formValidate.product_partake_type === '2'"
                    :columns="columns"
                    :data="tableData"
            >
              <template slot-scope="{ row, index }" slot="goodInfo">
                <div class="imgPic acea-row row-middle">
                  <viewer>
                    <div class="pictrue"><img v-lazy="row.image" /></div>
                  </viewer>
                  <div class="info">
                    <Tooltip max-width="200" placement="bottom" transfer>
                      <span class="line2">{{ row.store_name }}</span>
                      <p slot="content">{{ row.store_name }}</p>
                    </Tooltip>
                  </div>
                </div>
              </template>
              <template slot-scope="{ row, index }" slot="action">
                <a @click="del(index)">删除</a>
              </template>
            </Table>
          </TabPane>
        </Tabs>
      </Form>
    </Card>
    <Card :bordered="false" dis-hover class="fixed-card" :style="{left: `${!menuCollapse?'200px':isMobile?'0':'80px'}`}">
      <Form>
        <FormItem>
          <Button
                  v-if="currentTab !== '1'"
                  @click="upTab"
                  style="margin-right:10px"
          >上一步</Button>
          <Button
                  type="primary"
                  class="submission"
                  v-if="currentTab !== '2'"
                  @click="downTab('formValidate')"
          >下一步</Button
          >
          <Button
                  v-else
                  type="primary"
                  class="submission"
                  @click="handleSubmit('formValidate')"
          >保存并发布</Button
          >
        </FormItem>
      </Form>
    </Card>
    <Modal
      v-model="modalPic"
      width="960px"
      scrollable
      footer-hide
      closable
      title="上传活动图"
      :mask-closable="false"
    >
      <uploadPictures
        isChoice="单选"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
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
      <storeLabelList
        ref="storeLabel"
        @activeData="activeStoreData"
        @close="storeLabelClose"
      ></storeLabelList>
    </Modal>
    <Modal v-model="modals" title="商品列表" footerHide  class="paymentFooter" scrollable width="900" @on-cancel="cancel">
      <goods-list ref="goodslist" :ischeckbox="true" :isdiy="true"  @getProductId="getProductId" v-if="modals"></goods-list>
    </Modal>
  </div>
</template>

<script>
import Setting from '@/setting';
import { mapState,mapMutations } from "vuex";
import uploadPictures from "@/components/uploadPictures";
import storeLabelList from "@/components/storeLabelList";
import goodsList from '@/components/goodsList';
import { brandList } from "@/api/product";
import { activityFrameSave,activityFrameInfo } from "@/api/marketing";
export default {
  components: {
    uploadPictures,
    storeLabelList,
    goodsList
  },
  computed:{
    ...mapState("admin/layout", ["isMobile","menuCollapse"]),
    startPickOptions() {
      const that = this;
      return {
        disabledDate(time) {
          if(that.endTime) {
            return(
                    time.getTime() > new Date(that.endTime).getTime()
            )
          }
          return ''
        }
      }
    },
    endPickOptions() {
      const that = this;
      return {
        disabledDate(time) {
          if(that.startTime) {
            return(
                    time.getTime() < new Date(that.startTime).getTime()
            )
          }
          return ''
        }
      }
    }
  },
  data() {
    return {
      baseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
      currentTab:"1",
      startTime:'',
      endTime:'',
      formValidate: {
        name: "",
        section_time: [],
        image: "",
        status: 1,
        product_partake_type: "1",
        product_id: [],
        brand_id: [],
        store_label_id: []
      },
      modalPic: false,
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
      brandData: [],
      props: { emitPath: false, multiple: true },
      storeLabelShow: false,
      storeDataLabel: [],
      ruleValidate: {
        name: [
          { required: true, message: '请输入活动名称', trigger: 'blur' }
        ],
        image: [
          { required: true, message: "请上传活动图", trigger: 'change' }
        ]
      },
      disabledDate (date) {
        return date && date.valueOf() < Date.now() - 86400000;
      },
      columns: [
        {
          title: "商品信息",
          slot: "goodInfo",
          align: "left",
          minWidth: 300,
        },
        {
          title: "商品分类",
          key: "cate_name",
          align: "left",
          minWidth: 250,
        },
        {
          title: "售价",
          key: "price",
          align: "left",
          minWidth: 80,
        },
        {
          title: "库存",
          key: "stock",
          align: "left",
          minWidth: 80,
        },
        {
          title: "操作",
          slot: "action",
          align: "center",
          minWidth: 50,
        }
      ],
      tableData:[],
      modals: false,
    };
  },
  mounted(){
    this.setCopyrightShow({ value: false });
    if(this.$route.params.id != 0){
      this.getInfo();
    }
  },
  destroyed () {
    this.setCopyrightShow({ value: true });
  },
  methods: {
    ...mapMutations('admin/layout', [
      'setCopyrightShow'
    ]),
    //获取详情
    getInfo(){
      activityFrameInfo(this.$route.params.id).then(res=>{
        this.formValidate = res.data.info;
        this.startTime = res.data.info.start_time;
        this.endTime = res.data.info.stop_time;
        this.formValidate.product_partake_type = res.data.info.product_partake_type.toString();
        this.tableData = res.data.info.products;
        this.storeDataLabel = res.data.info.store_label_id;
        if(res.data.info.product_partake_type == 4){
          this.getBrandList();
        }
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    closeLabel(label){
      let index = this.storeDataLabel.indexOf(this.storeDataLabel.filter(d=>d.id == label.id)[0]);
      this.storeDataLabel.splice(index,1);
    },
    goodTap(e){
      if(e==4){
        this.getBrandList();
      }
    },
    // 品牌列表
    getBrandList(){
      brandList().then(res=>{
        this.brandData = res.data
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    del(index){
      this.tableData.splice(index, 1);
    },
    addGoods(){
      this.modals = true;
    },
    cancel () {
      this.modals = false;
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1))
    },
    getProductId (data) {
      this.modals = false;
      let list = this.tableData.concat(data);
      let uni = this.unique(list);
      this.tableData = uni;
    },
    onchangeStart(e){
      this.startTime = e;
    },
    onchangeEnd(e){
      this.endTime = e;
    },
    // 上一页：
    upTab() {
      if(this.currentTab=='2'){
        this.currentTab = (Number(this.currentTab) - 1).toString();
      }
    },
    // 下一页；
    downTab(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          if(!this.startTime || !this.endTime){
            return this.$Message.warning("请选择活动时间");
          }
          this.currentTab = '2';
        }else{
          this.$Message.warning("请完善数据");
        }
      })
    },
    handleSubmit(name){
      this.$refs[name].validate((valid) => {
        if (valid) {
          if(!this.startTime || !this.endTime){
            return this.$Message.warning("请选择活动时间");
          }
          let sectionTime = [];
          sectionTime.push(this.startTime);
          sectionTime.push(this.endTime);
          this.formValidate.section_time = sectionTime;
          if(this.formValidate.product_partake_type == '2'){
            let product_id = [];
            this.tableData.forEach((item)=>{
              product_id.push(item.id)
            });
            if(!product_id.length){
              return this.$Message.error('请添加商品');
            }
            this.formValidate.product_id = product_id;
          }
          if(this.formValidate.product_partake_type == '4' && !this.formValidate.brand_id.length){
            return this.$Message.error('请添加指定品牌');
          }
          if(this.formValidate.product_partake_type == '5'){
            let labelIds = [];
            this.storeDataLabel.forEach((item)=>{
              labelIds.push(item.id)
            });
            if(!labelIds.length){
              return this.$Message.error('请添加指定标签');
            }
            this.formValidate.store_label_id = labelIds
          }
          activityFrameSave(this.$route.params.id,this.formValidate).then(res=>{
            this.$router.push({ path: "/admin/marketing/activity_frame" });
            this.$Message.success(res.msg)
          }).catch(err=>{
            this.$Message.error(err.msg);
          })
        }else{
          this.$Message.warning("请完善数据");
        }
      })
    },
    modalPicTap() {
      this.modalPic = true;
    },
    getPic(pic) {
      this.modalPic = false;
      this.formValidate.image = pic.att_dir;
      this.$refs.formValidate.validateField('image');
    },
    delImage() {
      this.formValidate.image = "";
      this.$refs.formValidate.validateField('image');
    },
    openStoreLabel() {
      this.storeLabelShow = true;
      this.$refs.storeLabel.storeLabel(
        JSON.parse(JSON.stringify(this.storeDataLabel))
      );
    },
    activeStoreData(storeDataLabel) {
      this.storeLabelShow = false;
      this.storeDataLabel = storeDataLabel;
    },
    // 标签弹窗关闭
    storeLabelClose() {
      this.storeLabelShow = false;
    },
  },
};
</script>

<style lang="stylus" scoped>
  .ivu-table-header thead tr th{
    padding 8px 5px;
  }
  .imgPic{
    .info{
      flex 1
    }
    .pictrue{
      height: 36px;
      width 36px;
      margin-right 10px;
      img{
        height: 100%;
        width 100%;
        display: block;
      }
    }
  }

.ivu-table-wrapper{
  margin: 20px 20px 0 20px;
}

/deep/.ivu-tabs-bar{
  margin-bottom 30px!important;
}

.fixed-card {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 200px;
  z-index: 99;
  box-shadow: 0 -1px 2px rgb(240, 240, 240);

  /deep/ .ivu-card-body {
    padding: 15px 16px 14px;
  }

  .ivu-form-item {
    margin-bottom: 12px!important;
  }

  /deep/ .ivu-form-item-content {
    margin-right: 124px;
    text-align: center;
  }

  .ivu-btn {
    height: 36px;
    padding: 0 20px;
  }
}

.tips{
  color #999999;
  margin-top: 5px;
}

.w_input{
  width:460px;
}

.ivu-tabs {
  overflow: visible;
}

.upload-list {
  display: inline-block;
  margin: 0 0 -10px 0;

  .upload-item {
    position: relative;
    display: inline-block;
    width: 64px;
    height: 64px;
    border: 1px dashed #DDDDDD;
    border-radius: 4px;
    margin: 0 15px 10px 0;
  }

  img {
    width: 64px;
    height: 64px;
    border-radius: 4px;
    vertical-align: middle;
  }

  .ivu-btn {
    position: absolute;
    top: 0;
    right: 0;
    width: 20px;
    height: 20px;
    margin: -10px -10px 0 0;
  }
}

.upload-select {
  width: 64px;
  height: 64px;
  font-size: 35px !important;
  background #f5f5f5;
  color #ccc;
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
    transition: all 0.2s ease-in-out;
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

.ivu-radio-wrapper {
  margin-right: 30px;
}

.ivu-form-item+.ivu-row {
  padding: 0 20px;
}

.ivu-card {
  margin-top: 14px;

  >>>.ivu-tabs-tab {
    padding: 20px 16px;
    line-height: 1;
  }
}
</style>
