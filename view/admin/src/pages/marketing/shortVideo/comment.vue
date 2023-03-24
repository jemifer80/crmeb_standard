<template>
    <!-- 商品-商品评论 -->
    <div class="article-manager">
        <div class="i-layout-page-header" v-if="$route.query.id">
            <PageHeader class="product_tabs" hidden-breadcrumb>
                <div slot="title" class="acea-row row-middle">
                    <router-link :to="{ path: '/admin/marketing/short_video/index' }">
                        <div class="font-sm after-line">
                            <span class="iconfont iconfanhui"></span>
                            <span class="pl10">返回</span>
                        </div>
                    </router-link>
                    <span v-text="'视频评论'" class="mr20 ml16"></span>
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

                    <FormItem label="视频信息：" label-for="keyword">
                        <Input
                                size="default"
                                enter-button
                                placeholder="请输入视频信息"
                                clearable
                                v-model="formValidate.keyword"
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
                            <div class="pictrue"><img v-lazy="row.video.image" /></div>
                        </viewer>
                        <div class="info line2">{{ row.video.desc }}</div>
                    </div>
                </template>
                <template slot-scope="{ row }" slot="reply">
                    <Tooltip max-width="200" placement="bottom">
                        <span class="line2">{{row.reply}}</span>
                        <p slot="content">{{row.reply}}</p>
                    </Tooltip>
                </template>
                <template slot-scope="{ row }" slot="add_time">
                    {{row.add_time | formatDate}}
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
        <replyList ref="reply" :fromType="1"></replyList>
    </div>
</template>

<script>
    import { mapState } from "vuex";
    import { videoComment, videoReply, fictitiousReply } from "@/api/marketing";
    import replyList from '../../product/components/replyList.vue';
    import timeOptions from "@/utils/timeOptions";
    import { formatDate } from '@/utils/validate';
    export default {
        name: "product_productEvaluate",
        filters: {
            formatDate (time) {
                if (time !== 0) {
                    let date = new Date(time * 1000);
                    return formatDate(date, 'yyyy-MM-dd hh:mm:ss');
                }
            }
        },
        components: {
            replyList
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
                    keyword: "",
                    video_id: this.$route.query.id === undefined ? 0 : this.$route.query.id,
                    page: 1,
                    limit: 15,
                },
                options: timeOptions,
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
                        title: "视频信息",
                        slot: "info",
                        minWidth: 250,
                    },
                    {
                        title: "用户名称",
                        key: "nickname",
                        minWidth: 100,
                    },
                    {
                        title: "评价内容",
                        key: "content",
                        minWidth: 150,
                    },
                    {
                        title: "回复内容",
                        slot: "reply",
                        minWidth: 150,
                    },
                    {
                        title: "评价时间",
                        slot: "add_time",
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
            // 查看评论列表
            seeReply(row){
                this.$refs.reply.modals = true;
                this.$refs.reply.getList(row.id);
            },
            // 添加虚拟评论；
            add() {
                this.$modalForm(fictitiousReply(this.formValidate.video_id)).then(() =>
                    this.getList()
                );
            },
            oks() {
                this.modals = true;
                this.$refs["contents"].validate((valid) => {
                    if (valid) {
                        videoReply(this.contents, this.rows.id)
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
                    url: `/marketing/video/comment/${row.id}`,
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
                this.contents.content = row.reply;
            },
            // 具体日期
            onchangeTime(e) {
                this.timeVal = e;
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
                this.formValidate.keyword = this.formValidate.keyword || "";
                videoComment(this.formValidate)
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
            }
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
</style>
