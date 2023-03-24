<template>
<!-- 商品-商品评论 -->
  <div class="article-manager">
		<div class="i-layout-page-header" v-if="$route.params.id">
		  <PageHeader class="product_tabs" hidden-breadcrumb>
		    <div slot="title" class="acea-row row-middle">
		      <router-link :to="{ path: '/admin/product/product_list' }">
			<div class="font-sm after-line">
				<span class="iconfont iconfanhui"></span>
				<span class="pl10">返回</span>
			</div>
		  </router-link>
		      <span v-text="'商品评论'" class="mr20 ml16"></span>
		    </div>
		  </PageHeader>
		</div>
     <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
       <div class="new_card_pd">
        <!-- 筛选条件 -->
         <Form
        ref="formValidate"
        inline
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
            <FormItem label="时间选择：">
              <DatePicker
                :editable="false"
                @on-change="onchangeTime"
                :value="timeVal"
                format="yyyy/MM/dd"
               type="datetimerange"
              placement="bottom-start"
              placeholder="自定义时间"
              class="input-width"
              :options="options"
              ></DatePicker>
            </FormItem>

            <FormItem label="评价状态：">
              <Select
                v-model="formValidate.is_reply"
                placeholder="请选择"
                clearable
                @on-change="userSearchs"
                class="input-add"
              >
                <Option value="1">已回复</Option>
                <Option value="0">待回复</Option>
              </Select>
            </FormItem>

            <FormItem label="商品信息：" label-for="store_name">
              <Input
                size="default"
                enter-button
                placeholder="请输入商品ID或者商品信息"
                clearable
                v-model="formValidate.store_name"
                class="input-add"
              />
            </FormItem>

            <FormItem label="用户名称：" label-for="account">
              <Input
                size="default"
                enter-button
                placeholder="请输入"
                clearable
                v-model="formValidate.account"
                class="input-add"
              />
              <Button type="primary" @click="userSearchs">查询</Button>
            </FormItem>

      </Form>
       </div>
     </Card>

    <Card :bordered="false" dis-hover class="ivu-mt">
      <!-- 相关操作 -->
      <Row type="flex">
        <Col v-bind="grid">
          <Button
            v-auth="['product-reply-save_fictitious_reply']"
            type="primary"
            @click="add"
            >添加虚拟评论</Button
          >
        </Col>
      </Row>
      <!-- 商品评论表格 -->
      <Table
        ref="table"
        :columns="columns"
        :data="tableList"
        class="ivu-mt"
        :loading="loading"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="info">
          <div class="imgPic acea-row row-middle">
            <viewer>
              <div class="pictrue"><img v-lazy="row.image" /></div>
            </viewer>
            <div class="info line2">{{ row.store_name }}</div>
          </div>
        </template>
        <template slot-scope="{ row }" slot="content">
          <div class="mb5 content_font">{{ row.comment }}</div>
          <viewer>
            <div
              class="pictrue mr10"
              v-for="(item, index) in row.pics || []"
              :key="index"
            >
              <img v-lazy="item" />
            </div>
          </viewer>
        </template>
        <template slot-scope="{ row }" slot="reply">
          <Tooltip max-width="200" placement="bottom">
            <span class="line2">{{row.replyComment?row.replyComment.content:''}}</span>
            <p slot="content">{{row.replyComment?row.replyComment.content:''}}</p>
          </Tooltip>
        </template>
        <template slot-scope="{ row, index }" slot="action">
					<a @click="seeReply(row)">查看</a>
					<Divider type="vertical" />
          <a @click="reply(row)">回复</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除评论', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>
    <Modal v-model="modals" scrollable title="回复内容" closable>
      <Form
        ref="contents"
        :model="contents"
        :rules="ruleInline"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <FormItem prop="content">
          <Input
            v-model="contents.content"
            type="textarea"
            :rows="4"
            placeholder="请输入回复内容"
          />
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="oks">确定</Button>
        <Button @click="cancels">取消</Button>
      </div>
    </Modal>
	<!-- 	<storeList ref="store" @getStoreId="getStoreId" @getUserInfo='getUserInfo'></storeList> -->
		<replyList ref="reply"></replyList>
    <addReply
      :visible.sync="replyModal"
      :goods="goodsData"
      :attr="attrData"
      :avatar="avatarData"
      :picture="pictureData"
      @callGoods="callGoods"
      @callAttr="callAttr"
      @callPicture="callPicture"
      @removePicture="removePicture"
    ></addReply>
    <Modal v-model="goodsModal" title="选择商品" width="960" scrollable footer-hide>
      <goodsList v-if="replyModal" :goods-type="1" @getProductId="getProductId"></goodsList>
    </Modal>
    <Modal v-model="attrModal" title="选择商品规格" width="960" scrollable footer-hide>
      <Table :columns="tableColumns" :data="goodsData.attrValue" height="500">
        <template slot-scope="{ row, index }" slot="image">
          <div class="product-data">
            <img class="image" :src="row.image" />
          </div>
        </template>
      </Table>
    </Modal>
    <Modal
        v-model="pictureModal"
        width="960px"
        scrollable
        footer-hide
        closable
        title="上传商品图"
        :mask-closable="false"
        :z-index="1"
      >
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          @getPicD="getPicD"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="pictureModal"
        ></uploadPictures>
      </Modal>
  </div>
</template>

<script>
import { mapState } from "vuex";
import { replyListApi, setReplyApi, fictitiousReply } from "@/api/product";
import { productAttrsApi } from "@/api/marketing";
import replyList from '../components/replyList.vue';
import addReply from '../components/addReply.vue';
import goodsList from '@/components/goodsList/index';
import uploadPictures from "@/components/uploadPictures";
import timeOptions from "@/utils/timeOptions";
export default {
  name: "product_productEvaluate",
	components: {
		replyList,
    addReply,
    goodsList,
    uploadPictures
	},
  data() {
    return {
      modals: false,
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 12,
        xs: 24,
      },
      formValidate: {
        is_reply: "",
        data: "",
        store_name: "",
        account: "",
        product_id:
          this.$route.params.id === undefined ? 0 : this.$route.params.id,
        page: 1,
        limit: 15,
      },
       options: timeOptions,
      value: "45",
      tableList: [],
      total: 0,
      loading: false,
      columns: [
        {
          title: "评论ID",
          key: "id",
          width: 80,
        },
        {
          title: "商品信息",
          slot: "info",
          minWidth: 250,
        },
        {
          title: "用户名称",
          key: "nickname",
          minWidth: 150,
        },
        {
          title: "评分",
          key: "score",
          sortable: true,
          minWidth: 90,
        },
        {
          title: "评价内容",
          slot: "content",
          minWidth: 210,
        },
        {
          title: "回复内容",
          slot: "reply",
          minWidth: 250,
        },
        {
          title: "评价时间",
          key: "add_time",
          sortable: true,
          minWidth: 150,
        },
        {
          title: "操作",
          slot: "action",
          fixed: "right",
          minWidth: 150,
        },
      ],
      timeVal: [],
      contents: {
        content: "",
      },
      ruleInline: {
        content: [
          { required: true, message: "请输入回复内容", trigger: "blur" },
        ],
      },
      rows: {},
      replyModal: false,
      goodsModal: false,
      attrModal: false,
      tableColumns: [
        // {
        //   type: "selection",
        //   width: 60,
        //   align: "center",
        // },
        {
          width: 60,
          align: 'center',
          render: (h, params) => {
            return h('Radio', {
              props: {
                value: params.row.unique === this.attrData.unique
              },
              on: {
                'on-change': () => {
                  this.attrData = params.row;
                  this.attrModal = false;
                }
              }
            });
          }
        },
        {
          title: "图片",
          slot: "image",
          width: 120,
          align: "center",
        },
        {
          title: "规格",
          key: "suk",
          align: "center",
          minWidth: 120,
        },
        {
          title: "售价",
          key: "ot_price",
          align: "center",
          minWidth: 120,
        },
        {
          title: "优惠价",
          key: "price",
          align: "center",
          minWidth: 120,
        },
      ],
      tableData: [],
      goodsAddType: '',
      goodsData: {},
      attrData: {},
      avatarData: {},
      pictureData: [],
      selectProductAttrList: [],
      pictureModal: false,
      isChoice: '',
      picTit: '',
      tableIndex: 0,
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
    };
  },
  computed: {
    ...mapState("admin/layout", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 96;
    },
    labelPosition() {
      return this.isMobile ? "top" : "right";
    },
  },
  created() {
    if (this.$route.query.is_reply == 0) {
      this.formValidate.is_reply = this.$route.query.is_reply;
    }
    this.getList();
  },
  watch: {
    "$route.params.id"(to, from) {
      this.formValidate.product_id = 0;
      this.getList();
    },
    replyModal(value) {
      if (!value) {
        this.goodsData = {};
        this.attrData = {};
        this.avatarData = {};
        this.pictureData = [];
        this.getList();
      }
    }
  },
  methods: {
		// 查看评论列表
		seeReply(row){
			this.$refs.reply.modals = true;
			this.$refs.reply.getList(row.id);
		},
    // 添加虚拟评论；
    add() {
      // this.$modalForm(fictitiousReply(this.formValidate.product_id)).then(() =>
      //   this.getList()
      // );
      this.replyModal = true;
    },
    oks() {
      this.modals = true;
      this.$refs["contents"].validate((valid) => {
        if (valid) {
          setReplyApi(this.contents, this.rows.id)
            .then(async (res) => {
              this.$Message.success(res.msg);
              this.modals = false;
              this.$refs["contents"].resetFields();
              this.getList();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
    cancels() {
      this.modals = false;
      this.$refs["contents"].resetFields();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `product/reply/${row.id}`,
        method: "DELETE",
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tableList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 回复
    reply(row) {
      this.modals = true;
      this.rows = row;
			this.contents.content = row.replyComment?row.replyComment.content:'';
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      // this.formValidate.data = this.timeVal.join('-');
      this.formValidate.data = this.timeVal[0] ? this.timeVal.join("-") : "";
      this.formValidate.page = 1;
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.formValidate.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.is_reply = this.formValidate.is_reply || "";
      this.formValidate.store_name = this.formValidate.store_name || "";
      replyListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    search() {},
    callGoods() {
      this.goodsModal = true;
    },
    callAttr() {
      this.attrModal = true;
    },
    getProductId(goods) {
      this.goodsData = goods;
      this.goodsModal = false;
      this.attrData.unique = '';
    },
    getPic(pc) {
      this.avatarData = pc;
      this.pictureModal = false;
    },
    getPicD(pc) {
      let pictureData = [...this.pictureData];
      pictureData = pictureData.concat(pc);
      pictureData.sort((a, b) => a.att_id - b.att_id);
      let picture = [];
      for (let i = 0; i < pictureData.length; i++) {
        if (pictureData[i + 1] && pictureData[i].att_id != pictureData[i + 1].att_id) {
          picture.push(pictureData[i]);
        }
        if (!pictureData[i + 1]) {
          picture.push(pictureData[i]);
        }
      }
      this.pictureData = picture;
      this.pictureModal = false;
    },
    callPicture(type) {
      this.isChoice = type;
      this.pictureModal = true;
    },
    removePicture(att_id) {
      let index = this.pictureData.findIndex((item) => item.att_id === att_id);
      this.pictureData.splice(index, 1);
    },
  },
};
</script>
<style scoped lang="stylus">
.input-add {
width: 250px;
margin-right:14px;
}
.line2{
	max-height 36px;
}
.content_font {
  color: #2b85e4;
}

.search {
  >>> .ivu-form-item-content {
    margin-left: 0 !important;
  }
}

.ivu-mt .Button .bnt {
  margin-right: 6px;
}

.ivu-mt .ivu-table-row {
  font-size: 12px;
  color: rgba(0, 0, 0, 0.65);
}

.ivu-mt >>> .ivu-table-cell {
  padding: 10px 0 !important;
}

.pictrue {
  height: 40px;
	width: 40px;
  display: inline-block;
  cursor: pointer;
}

.pictrue img {
	width 100%;
  height: 100%;
  display: block;
}

.ivu-mt .imgPic .info {
  width: 60%;
  margin-left: 10px;
}

.ivu-mt .picList .pictrue {
  height: 36px;
  margin: 7px 3px 0 3px;
}

.ivu-mt .picList .pictrue img {
  height: 100%;
  display: block;
}

.product-data {
  display: flex;
  align-items: center;

  .image {
    width: 50px !important;
    height: 50px !important;
    margin-right: 10px;
  }
}
</style>
