<template>
    <!--门店视频详情-->
    <div>
        <Drawer
                :closable="false"
                width="1000"
                class-name="order_box"
                v-model="modals"
                :styles="{ padding: 0 }"
        >
            <div class="header acea-row row-between-wrapper">
                <div class="acea-row row-middle">
                    <div class="pictrue">
                        <img src="@/assets/images/video-img.png">
                    </div>
                    <div class="text">
                        <div class="name">短视频</div>
                        <div class="info">商户名称：{{formValidate.type_name || '-'}}</div>
                    </div>
                </div>
                <Button type="primary" v-if="formValidate.is_verify == 0" @click="verify">审核</Button>
                <Button type="error" v-if="formValidate.type == 1 && formValidate.is_verify == 1" @click="force">强制下架</Button>
            </div>
            <Tabs v-model="activeName">
                <TabPane label="基础信息" name="detail"></TabPane>
                <TabPane label="关联商品" name="product"></TabPane>
            </Tabs>
            <Form
                    ref="formValidate"
                    :model="formValidate"
                    :label-width="labelWidth"
                    :label-position="labelPosition"
                    @submit.native.prevent
            >
                <Row v-show="activeName === 'detail'">
                    <Col span="24">
                        <FormItem label="视频简介：" prop="desc">
                            <Input
                                    v-model="formValidate.desc"
                                    type="textarea"
                                    :rows="3"
                                    placeholder="请输入视频简介"
                                    v-width="'50%'"
                                    readonly
                            />
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="上传视频：" prop="video_url">
                            <div class="iview-video-style" v-if="formValidate.video_url">
                                <video
                                        class="video-style"
                                        :src="formValidate.video_url"
                                        controls="controls"
                                >
                                </video>
                            </div>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="封面图：" prop="image">
                            <div class="pictrueBox">
                                <div class="pictrue">
                                    <img v-lazy="formValidate.image" />
                                </div>
                            </div>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="排序：">
                            <InputNumber
                                    v-model="formValidate.sort"
                                    :min="0"
                                    :max="99999999"
                                    v-width="'50%'"
                                    readonly
                            ></InputNumber>
                        </FormItem>
                    </Col>
                </Row>
                <div v-show="activeName === 'product'" class="margins">
                    <Table
                            :columns="columns"
                            :data="tableData"
                            highlight-row
                            no-userFrom-text="暂无数据"
                            no-filtered-userFrom-text="暂无筛选结果"
                    >
                        <template slot-scope="{ row }" slot="info">
                            <div class="imgPic acea-row row-middle">
                                <viewer>
                                    <div class="pictrue"><img v-lazy="row.image" /></div>
                                </viewer>
                                <div class="info">
                                    <Tooltip max-width="200" placement="bottom" transfer>
                                        <span class="line2">{{ row.store_name }}{{row.suk}}</span>
                                        <p slot="content">{{ row.store_name }}{{row.suk}}</p>
                                    </Tooltip>
                                </div>
                            </div>
                        </template>
                    </Table>
                </div>
            </Form>
        </Drawer>
    </div>
</template>
<script>
    import { mapState } from "vuex";
    import { videoInfo } from "@/api/marketing";
    export default {
        name: "videoDetails",
        computed: {
            ...mapState("admin/layout", ["isMobile"]),
            labelWidth() {
                return this.isMobile ? undefined : 90;
            },
            labelPosition() {
                return this.isMobile ? "top" : "right";
            }
        },
        data() {
            return {
                modals:false,
                activeName:'detail',
                formValidate:{},
                tableData:[],
                columns: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 60
                    },
                    {
                        title: "商品信息",
                        slot: "info",
                        minWidth: 180,
                    },
                    {
                        title: "商品分类",
                        key: "cate_name",
                        minWidth: 180,
                    },
                    {
                        title: "售价",
                        key: "price",
                        minWidth: 180,
                    },
                    {
                        title: "库存",
                        key: "stock",
                        minWidth: 180,
                    }
                ],
            };
        },
        created() {},
        methods: {
            verify(){
                this.$emit('verifyFun',this.formValidate);
            },
            force(){
                this.$emit('forceFun',this.formValidate);
            },
            //视频详情
            getInfo(id){
                videoInfo(id).then(res=>{
                    this.formValidate = res.data;
                    this.tableData = res.data.productInfo;
                }).catch(err=>{
                    this.$Message.error(err.msg)
                })
            },
        }
    };
</script>
<style scoped lang="less">
    .order_box{
        box-sizing: border-box;
        .header{
            margin: 25px 24px;
            .pictrue{
                width: 60px;
                height: 60px;
                img{
                    width: 100%;
                    height: 100%;
                    display: block;
                }
            }
            .text{
                margin-left: 12px;
                .name{
                    font-size: 16px;
                    font-weight: 500;
                    color: rgba(0,0,0,0.85);
                }
                .info{
                    font-size: 13px;
                    color: #606266;
                    margin-top: 3px;
                }
            }
        }
        .pictrueBox {
            display: inline-block;
            .upLoad {
                width: 58px;
                height: 58px;
                line-height: 58px;
                border: 1px dotted rgba(0, 0, 0, 0.1);
                border-radius: 4px;
                background: rgba(0, 0, 0, 0.02);
                cursor: pointer;
                .input-display {
                    display: none
                }
            }
            .pictrue {
                width: 60px;
                height: 60px;
                border: 1px dotted rgba(0, 0, 0, 0.1);
                margin-right: 15px;
                margin-bottom: 10px;
                display: inline-block;
                position: relative;
                cursor: pointer;

                img {
                    width: 100%;
                    height: 100%;
                }

                .btndel {
                    position: absolute;
                    z-index: 1;
                    width: 20px !important;
                    height: 20px !important;
                    left: 46px;
                    top: -4px;
                }
            }
        }
        .iview-video-style {
            width: 40%;
            height: 180px;
            border-radius: 10px;
            background-color: #707070;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            .video-style {
                width: 100%;
                height: 100% !important;
                border-radius: 10px;
            }
            .iconv{
                color: #fff;
                line-height: 180px;
                width: 50px;
                height: 50px;
                display: inherit;
                font-size: 26px;
                position: absolute;
                top: -74px;
                left: 50%;
                margin-left: -25px;
            }
            .mark{
                position: absolute;
                width: 100%;
                height: 30px;
                top: 0;
                background-color: rgba(0, 0, 0, 0.5);
                text-align: center;
            }
        }
        .imgPic{
            .info{
                width: 60%;
                margin-left: 10px;
            }
            .pictrue{
                height: 36px;
                margin: 7px 3px 0 3px;
                img{
                    height: 100%;
                    display: block;
                }
            }
        }
        .margins{
            margin: 0 35px;
        }
    }
    /deep/.ivu-tabs-ink-bar{
        display: none;
    }
    /deep/.ivu-tabs-bar{
        background: #F5F7FA;
        border-bottom: 0;
        margin-bottom: 0;
    }
    /deep/.ivu-tabs-nav-wrap {
        margin-bottom: 0;
    }
    /deep/.ivu-tabs-nav{
        height: 40px;
        line-height: 40px;
    }
    /deep/.ivu-tabs-nav .ivu-tabs-tab-active{
        color: rgba(0,0,0,0.85);
        font-weight: 400;
        background-color: #fff;
        &::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #1890FF;
        }
    }
    /deep/.ivu-tabs-tabpane {
        padding: 15px;

        &:first-child {
            padding: 0 25px;
        }
    }
    /deep/.ivu-tabs-nav .ivu-tabs-tab{
        padding: 7px 19px !important;
        margin-right: 0;
        line-height: 26px;
    }
    /deep/.ivu-tabs-nav-container {
        font-size: 13px;
    }
</style>