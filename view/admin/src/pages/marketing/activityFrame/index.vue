<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <div class="new_card_pd">
        <!-- 查询条件 -->
        <Form
          ref="formValidate"
          inline
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
        >
          <FormItem label="活动搜索：">
            <Input
              placeholder="请输入活动名称/ID"
              v-model="formValidate.name"
              class="input-width"
            />
          </FormItem>
          <FormItem label="活动状态：">
            <Select v-model="formValidate.status" clearable class="input-add">
              <Option
                v-for="(item, index) in list"
                :value="item.status"
                :key="index"
                >{{ item.title }}</Option
              >
            </Select>
          </FormItem>
          <FormItem label="活动时间：">
            <DatePicker
              :editable="false"
              @on-change="dataTime"
              :value="dataTimeVal"
              format="yyyy/MM/dd"
              type="datetimerange"
              placement="bottom-start"
              placeholder="活动时间"
              :options="options"
              class="input-add"
            ></DatePicker>
          </FormItem>
          <div style="display: inline-block;">
            <FormItem label="创建时间：">
              <DatePicker
                      :editable="false"
                      @on-change="createTime"
                      :value="timeVal"
                      format="yyyy/MM/dd"
                      type="datetimerange"
                      placement="bottom-start"
                      placeholder="创建时间"
                      :options="options"
                      class="input-add"
              ></DatePicker>
            </FormItem>
            <Button type="primary" @click="selChange" class="mr14 mt1">查询</Button>
            <Button class="mt1" @click="reset()">重置</Button>
          </div>
        </Form>
      </div>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <!-- 操作 -->
      <Button
        v-auth="['marketing-store_seckill-create']"
        type="primary"
        @click="add"
        class="mr10"
        >添加活动边框</Button
      >
      <!-- 表格 -->
      <Table
        ref="table"
        :columns="columns"
        :data="dataList"
        class="ivu-mt"
        :loading="loading"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="time">
          <div>{{row.start_time}} 至 {{row.stop_time}}</div>
        </template>
        <template slot-scope="{ row, index }" slot="start_status">
          <Tag color="red" size="medium" v-show="row.start_status === 0">未开始</Tag>
          <Tag color="green" size="medium" v-show="row.start_status === 1">进行中</Tag>
          <Tag color="default" size="medium" v-show="row.start_status === -1">已结束</Tag>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <i-switch v-model="row.status" :true-value="1" :false-value="0" size="large" @on-change="onchangeIsShow(row)">
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row.id)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row,'删除活动边框',index)">删除</a>
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
  </div>
</template>

<script>
import { mapState } from "vuex";
import { activityFrameList,activityFrameStatus } from "@/api/marketing";
import timeOptions from "@/utils/timeOptions";
export default {
  data() {
    return {
      options: timeOptions,
      timeVal: [],
      dataTimeVal: [],
      formValidate: {
        name: "",
        status: "",
        time: "",
        create_time: "",
        page: 1,
        limit: 15,
      },
      list: [
        {status:0,title:'未开始'},
        {status:1,title:'进行中'},
        {status:-1,title:'已结束'}
      ],
      columns: [
        {
          title: "ID",
          key: "id",
          width: 80,
        },
        {
          title: "活动名称",
          key: "name",
          minWidth: 170,
        },
        {
          title: "活动时间",
          slot: "time",
          minWidth: 260,
        },
        {
          title: "参与商品数",
          key: "product_count",
          minWidth: 100,
        },
        {
          title: "活动状态",
          slot: "start_status",
          minWidth: 100,
        },
        {
          title: "是否开启",
          slot: "status",
          minWidth: 100,
        },
        {
          title: "创建时间",
          key: "add_time",
          minWidth: 140,
        },
        {
          title: "操作",
          slot: "action",
          minWidth: 110,
          fixed: 'right'
        },
      ],
      dataList: [],
      loading: false,
      total: 0,
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
    this.activityFrameList();
  },
  methods: {
    createTime(e) {
      this.timeVal = e;
      this.formValidate.create_time = this.timeVal.join("-");
    },
    dataTime(e){
      this.dataTimeVal = e;
      this.formValidate.time = this.dataTimeVal.join("-");
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/activity_frame/del/${row.id}`,
        method: "DELETE",
        ids: "",
      };
      this.$modalSure(delfromData).then((res) => {
        this.$Message.success(res.msg);
        this.activityFrameList();
      }).catch((res) => {
        this.$Message.error(res.msg);
      });
    },
    onchangeIsShow (row) {
      activityFrameStatus(row.id,row.status).then(res=>{
        this.$Message.success(res.msg);
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    //修改
    edit(id){
      console.log('rrr',id);
      this.$router.push({ path: "/admin/marketing/activity_frame/create/" + id });
    },
    //添加
    add() {
      this.$router.push({ path: "/admin/marketing/activity_frame/create/" + 0 });
    },
    // 边框列表
    activityFrameList() {
      activityFrameList(this.formValidate).then((res) => {
        this.dataList = res.data.list;
        this.total = res.data.count;
      });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.activityFrameList();
    },
    // 查询
    selChange() {
      this.formValidate.page = 1;
      this.activityFrameList();
    },
    reset() {
      this.formValidate = {
        name: "",
        status: "",
        time: "",
        create_time: "",
        page: 1
      };
      this.timeVal = [];
      this.dataTimeVal = [];
      this.activityFrameList();
    },
  },
};
</script>

<style lang="stylus" scoped>
  .input-width{
    width 250px;
  }
</style>
