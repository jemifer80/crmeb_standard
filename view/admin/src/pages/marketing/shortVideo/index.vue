<template>
    <!-- 营销-短视频列表 -->
    <div>
        <Card :bordered="false" dis-hover class="ivu-mt" :padding= "0">
            <div class="new_card_pd">
                <!-- 查询条件 -->
                <Form
                        ref="tableFrom"
                        :model="tableFrom"
                        :label-width="labelWidth"
                        :label-position="labelPosition"
                        inline
                        @submit.native.prevent
                >
                    <FormItem label="时间筛选：">
                        <DatePicker
                                :editable="false"
                                :clearable="true"
                                @on-change="onchangeTime"
                                :value="timeVal"
                                format="yyyy/MM/dd"
                                type="datetimerange"
                                placement="bottom-start"
                                placeholder="自定义时间"
                                class="input-add"
                                :options="options"></DatePicker>
                    </FormItem>
                    <FormItem label="视频搜索：" label-for="store_name">
                        <Input
                                placeholder="请输入视频简介/ID"
                                v-model="tableFrom.keyword"
                                @on-search="userSearchs"
                                class="input-add mr14"
                        />
                        <Button type="primary" @click="userSearchs()" class="mr14">查询</Button>
                        <Button @click="reset">重置</Button>
                    </FormItem>
                </Form>
            </div>
        </Card>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <!-- Tab栏切换 -->
            <!-- <Tabs @on-click="onClickTab">
                <TabPane
                        :label="item.name"
                        :name="item.type"
                        v-for="(item, index) in headeNum"
                        :key="index"
                />
            </Tabs> -->
            <!-- 操作 -->
            <Button
                    type="primary"
                    @click="add"
                    class="mr10"
            >添加视频</Button>
            <!-- 视频-表格 -->
            <Table
                    :columns="columns1"
                    :data="tableList"
                    :loading="loading"
                    highlight-row
                    no-userFrom-text="暂无数据"
                    no-filtered-userFrom-text="暂无筛选结果"
                    class="ivu-mt"
            >
                <template slot-scope="{ row }" slot="image">
                    <viewer>
                        <div class="tabBox_img">
                            <img v-lazy="row.image" />
                        </div>
                    </viewer>
                </template>
                <template slot-scope="{ row }" slot="desc">
                    <Tooltip max-width="200" placement="bottom">
                        <div class="line2">{{row.desc}}</div>
                        <p slot="content">{{row.desc}}</p>
                    </Tooltip>
                </template>
                <template slot-scope="{ row }" slot="is_verify">
                    <div v-if="row.type==1">
                        <Tag color="orange" size="medium" v-show="row.is_verify == 0">待审核</Tag>
                        <Tag color="green" size="medium" v-show="row.is_verify == 1">审核通过</Tag>
                        <Tag color="red" size="medium" v-show="row.is_verify == -1">审核未通过</Tag>
                        <Tag color="red" size="medium" v-show="row.is_verify == -2">已强制下架</Tag>
                    </div>
                    <div v-else>-</div>
                </template>
                <template slot-scope="{ row }" slot="is_show">
                    <i-switch
                            v-model="row.is_show"
                            :value="row.is_show"
                            :true-value="1"
                            :false-value="0"
                            :disabled="row.is_verify == -2?true:false"
                            @on-change="onchangeIsShow(row)"
                            size="large"
                    >
                        <span slot="open">开启</span>
                        <span slot="close">关闭</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row }" slot="is_recommend">
                    <i-switch
                            v-model="row.is_recommend"
                            :value="row.is_recommend"
                            :true-value="1"
                            :false-value="0"
                            :disabled="row.is_verify == -2?true:false"
                            @on-change="onchangeIsRecommend(row)"
                            size="large"
                    >
                        <span slot="open">开启</span>
                        <span slot="close">关闭</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="seeInfo(row)">详情</a>
                    <Divider type="vertical" />
                    <a v-if="row.type == 0" @click="edit(row)">编辑</a>
                    <Divider v-if="row.type == 0" type="vertical" />
                    <a v-if="row.is_verify == 0" @click="verify(row)">审核</a>
                    <Divider v-if="row.is_verify == 0" type="vertical" />
                    <template>
                        <Dropdown @on-click="changeMenu(row,$event,index)" :transfer="true">
                            <a href="javascript:void(0)">
                                更多
                                <Icon type="ios-arrow-down"></Icon>
                            </a>
                            <DropdownMenu slot="list">
                                <DropdownItem name="2" v-if="row.type == 1 && row.is_verify == 1">强制下架</DropdownItem>
                                <DropdownItem name="3" v-if="row.is_verify == 1">查看评论</DropdownItem>
                                <DropdownItem name="4">删除</DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </template>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page
                        :total="total"
                        :current="tableFrom.page"
                        show-elevator
                        show-total
                        @on-change="pageChange"
                        :page-size="tableFrom.limit"
                />
            </div>
            <video-details ref="detailss" @verifyFun="verifyFun" @forceFun="forceFun"></video-details>
        </Card>
    </div>
</template>

<script>
    import { mapState } from "vuex";
    import { videoList, videoSetStatus, videoSetRecommend, videoVerify, videoTakeDown } from "@/api/marketing";
    import { formatDate } from "@/utils/validate";
    import timeOptions from "@/utils/timeOptions";
    import videoDetails from './components/videoDetails';
    export default {
        name: "index",
        components: {
            videoDetails
        },
        filters: {
            formatDate(time) {
                if (time !== 0) {
                    let date = new Date(time * 1000);
                    return formatDate(date, "yyyy-MM-dd");
                }
            },
        },
        data() {
            return {
                timeVal: [],
                tableFrom: {
                    keyword: "",
                    data: "",
                    page: 1,
                    limit: 15,
                    is_verify: ""
                },
                options:timeOptions,
                headeNum: [
                    { type: "", name: "全部" },
                    { type: "1", name: "已通过审核" },
                    { type: "0", name: "待审核" },
                    { type: "-1", name: "审核未通过" },
                    { type: "-2", name: "强制下架" }
                ],
                loading: false,
                columns1: [
                    {
                        title: "ID",
                        key: "id",
                        width: 80,
                    },
                    {
                        title: "视频",
                        slot: "image",
                        minWidth: 90,
                    },
                    {
                        title: "视频介绍",
                        slot: "desc",
                        minWidth: 150,
                    },
                    {
                        title: "发布人",
                        key: "type_name",
                        minWidth: 150,
                    },
                    {
                        title: "浏览数",
                        key: "play_num",
                        minWidth: 100,
                    },
                    {
                        title: "评论数",
                        key: "comment_num",
                        minWidth: 100,
                    },
                    {
                        title: "收藏数",
                        key: "collect_num",
                        minWidth: 100,
                    },
                    {
                        title: "点赞数",
                        key: "like_num",
                        minWidth: 80,
                    },
                    {
                        title: "分享数",
                        key: "share_num",
                        minWidth: 80,
                    },
                    {
                        title: "关联商品",
                        key: "product_num",
                        minWidth: 80,
                    },
                    // {
                    //     title: "审核状态",
                    //     slot: "is_verify",
                    //     minWidth: 100,
                    // },
                    {
                        title: "是否展示",
                        slot: "is_show",
                        minWidth: 100,
                    },
                    {
                        title: "是否推荐",
                        slot: "is_recommend",
                        minWidth: 100,
                    },
                    {
                        title: "排序",
                        key: "sort",
                        minWidth: 80,
                    },
                    {
                        title: "创建时间",
                        key: "add_time",
                        minWidth: 150,
                    },
                    {
                        title: "操作",
                        slot: "action",
                        fixed: "right",
                        minWidth: 200,
                    }
                ],
                tableList: [],
                grid: {
                    xl: 7,
                    lg: 10,
                    md: 12,
                    sm: 24,
                    xs: 24,
                },
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
            this.getList();
        },
        methods: {
            verifyFun(row){
                this.verify(row);
            },
            forceFun(row){
                this.force(row);
            },
            // 操作
            changeMenu(row, name, index) {
                switch (name) {
                    case "2":
                        this.force(row);
                        break;
                    case "3":
                        this.$router.push({ path: "/admin/marketing/short_video/comment",query:{'id':row.id} });
                        break;
                    case "4":
                        this.del(row, '删除视频', index);
                        break;
                    default:
                        break;
                }
            },
            // 具体日期
            onchangeTime(e) {
                this.timeVal = e;
                this.tableFrom.data = this.timeVal[0] ? this.timeVal.join("-") : "";
                this.tableFrom.page = 1;
                this.getList();
            },
            onClickTab(type){
                this.tableFrom.page = 1;
                this.tableFrom.is_verify = type;
                this.getList();
            },
            reset(){
                this.tableFrom.keyword = "";
                this.tableFrom.data = "";
                this.timeVal = "";
                this.tableFrom.page = 1;
                this.tableFrom.is_verify = "";
                this.getList();
            },
            verify(row){
                let data = {
                    id: row.id
                };
                this.$Modal.confirm({
                    title:'温馨提示',
                    content:'是否要确定该视频审核通过？',
                    okText: '确定',
                    cancelText: '拒绝',
                    onOk: () => {
                        data.verify = 1;
                        videoVerify(data).then(res=>{
                            this.$Message.success(res.msg);
                            this.getList()
                        }).catch(err=>{
                            this.$Message.error(err.msg)
                        })
                    },
                    onCancel: () => {
                        data.verify = 2;
                        videoVerify(data).then(res=>{
                            this.$Message.success(res.msg);
                            this.getList()
                        }).catch(err=>{
                            this.$Message.error(err.msg)
                        })
                    }
                });
            },
            seeInfo(row){
                this.$refs.detailss.modals = true
                this.$refs.detailss.getInfo(row.id)
                this.$refs.detailss.activeName = "detail"
            },
            force(row){
                this.$Modal.confirm({
                    title:'温馨提示',
                    content:'是否确定该视频强制下架？',
                    onOk: () => {
                        videoTakeDown(row.id).then(res=>{
                            this.$Message.success(res.msg);
                            this.getList()
                        }).catch(err=>{
                            this.$message.error(err.msg)
                        })
                    },
                    onCancel: () => {
                        this.$Message.info('已取消');
                    }
                });
            },
            // 添加
            add() {
                this.$router.push({ path: "/admin/marketing/short_video/create" });
            },
            // 列表
            getList() {
                this.loading = true;
                this.tableFrom.is_verify = this.tableFrom.is_verify || "";
                videoList(this.tableFrom)
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
            // 编辑
            edit(row) {
                this.$router.push({
                    path: "/admin/marketing/short_video/create/" + row.id
                });
            },
            // 删除
            del(row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `/marketing/video/del/${row.id}`,
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
            pageChange(index) {
                this.tableFrom.page = index;
                this.getList();
            },
            // 表格搜索
            userSearchs() {
                this.tableFrom.page = 1;
                this.getList();
            },
            // 修改是否显示
            onchangeIsShow(row) {
                let data = {
                    id: row.id,
                    status: row.is_show,
                };
                videoSetStatus(data)
                    .then(async (res) => {
                        this.$Message.success(res.msg);
                        this.getList()
                    })
                    .catch((res) => {
                        this.$Message.error(res.msg);
                    });
            },
            // 修改是否推荐
            onchangeIsRecommend(row){
                let data = {
                    id: row.id,
                    recommend: row.is_recommend,
                };
                videoSetRecommend(data)
                    .then(async (res) => {
                        this.$Message.success(res.msg);
                        this.getList()
                    })
                    .catch((res) => {
                        this.$Message.error(res.msg);
                    });
            }
        },
    };
</script>

<style scoped lang="stylus">
    .line2{
        max-height 36px;
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
</style>