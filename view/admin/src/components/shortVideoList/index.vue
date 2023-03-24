<template>
    <div class="goodList">
        <Form
                ref="formValidate"
                :model="formValidate"
                :label-width="labelWidth"
                :label-position="labelPosition"
                inline
                class="tabform"
        >
            <FormItem label="视频搜索：" label-for="store_name">
                <Input
                        placeholder="请输入视频简介/ID"
                        v-model="formValidate.keyword"
                        class="input-add mr14"
                />
                <Button type="primary" @click="userSearchs()">查询</Button>
            </FormItem>
        </Form>
        <Table
                ref="table"
                no-data-text="暂无数据"
                no-filtered-data-text="暂无筛选结果"
                :columns="columns"
                :data="tableList"
                :loading="loading"
                class="mr-20"
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
                    <span class="line2">{{row.desc}}</span>
                    <p slot="content">{{row.desc}}</p>
                </Tooltip>
            </template>
        </Table>
        <div class="acea-row row-right page">
            <Page
                    :total="total"
                    show-elevator
                    show-total
                    @on-change="pageChange"
                    :page-size="formValidate.limit"
            />
        </div>
    </div>
</template>

<script>
    import { mapState } from "vuex";
    import { videoList } from "@/api/marketing";
    export default {
        name: "index",
        data() {
            return {
                formValidate: {
                    page: 1,
                    limit: 10,
                    keyword: ""
                },
                total: 0,
                loading: false,
                grid: {
                    xl: 10,
                    lg: 10,
                    md: 12,
                    sm: 24,
                    xs: 24,
                },
                tableList: [],
                currentid: 0,
                videoRow: {},
                columns: [
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
                    }
                ],
                images: [],
            };
        },
        computed: {
            ...mapState("admin/layout", ["isMobile"]),
            labelWidth() {
                return this.isMobile ? undefined : 120;
            },
            labelPosition() {
                return this.isMobile ? "top" : "right";
            },
        },
        created() {
            let radio = {
                width: 60,
                align: "center",
                render: (h, params) => {
                    let id = params.row.id;
                    let flag = false;
                    if (this.currentid === id) {
                        flag = true;
                    } else {
                        flag = false;
                    }
                    let self = this;
                    return h("div", [
                        h("Radio", {
                            props: {
                                value: flag,
                            },
                            on: {
                                "on-change": () => {
                                    self.currentid = id;
                                    this.videoRow = params.row;
                                    if (this.videoRow.id) {
                                        if (this.$route.query.fodder === "video") {
                                            /* eslint-disable */
                                            let imageObject = {
                                                image: this.videoRow.image,
                                                video_id: this.videoRow.id,
                                                video_url: this.videoRow.video_url
                                            };
                                            form_create_helper.set("video", imageObject);
                                            form_create_helper.close("video");
                                        }
                                    } else {
                                        this.$Message.warning("请先选择商品");
                                    }
                                },
                            },
                        }),
                    ]);
                },
            };
            this.columns.unshift(radio);
        },
        mounted() {
            this.getList();
        },
        methods: {
            pageChange(index) {
                this.formValidate.page = index;
                this.getList();
            },
            // 列表
            getList() {
                this.loading = true;
                videoList(this.formValidate)
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
            // 表格搜索
            userSearchs() {
                this.formValidate.page = 1;
                this.getList();
            },
            clear() {
                this.videoRow.id = "";
                this.currentid = "";
            }
        },
    };
</script>

<style scoped lang="stylus">
    .footer {
        margin: 15px 0;
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

    .tabform {
        >>> .ivu-form-item {
            margin-bottom: 16px !important;
        }
    }

    .btn {
        margin-top: 20px;
        float: right;
    }

    .goodList {

    }
    .mr-20{
        margin-right:10px;
    }
</style>
