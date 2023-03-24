<template>
    <div>
        <!--头部标题-->
        <div class="i-layout-page-header">
            <PageHeader class="product_tabs" hidden-breadcrumb>
                <div slot="title" class="acea-row row-middle">
                    <router-link :to="{ path: '/admin/marketing/short_video/index' }">
                        <div class="font-sm after-line">
                            <span class="iconfont iconfanhui"></span>
                            <span class="pl10">返回</span>
                        </div>
                    </router-link>
                    <span v-text="$route.params.id ? '编辑短视频' : '添加短视频'" class="mr20 ml16"></span>
                </div>
            </PageHeader>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt mb79">
            <!-- Tab栏切换 -->
            <div class="new_tab">
                <Tabs v-model="currentTab">
                    <TabPane
                            :label="item.name"
                            :name="item.type"
                            v-for="(item, index) in headeNum"
                            :key="index"
                    />
                </Tabs>
            </div>
            <div class="Button" v-if="currentTab === '2'">
                <Button type="primary" class="bnt mr15" @click="addGoods">添加商品</Button>
                <Tooltip content="本页至少选中一项" :disabled="!!formSelection.length">
                    <Button
                            class="bnt mr15"
                            :disabled="!formSelection.length"
                            @click="batchDel"
                    >批量删除</Button
                    >
                </Tooltip>
            </div>
            <Form
                    class="formValidate mt20"
                    ref="formValidate"
                    :rules="ruleValidate"
                    :model="formValidate"
                    :label-width="labelWidth"
                    :label-position="labelPosition"
                    @submit.native.prevent
            >
                <Row :gutter="24" type="flex" v-show="currentTab === '1'">
                    <Col span="24">
                        <FormItem label="视频简介：" prop="desc">
                            <Input
                                    v-model="formValidate.desc"
                                    type="textarea"
                                    :rows="3"
                                    placeholder="请输入视频简介"
                                    maxlength="220"
                                    show-word-limit
                                    v-width="'50%'"
                            />
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="上传视频：" prop="video_url">
                            <Button @click="modalPicTap('video')">上传视频</Button>
                            <div class="tips">建议时长：9～30秒，视频宽高比9:16（不建议本地储存）</div>
                            <div class="iview-video-style" v-if="formValidate.video_url">
                                <video
                                        class="video-style"
                                        :src="formValidate.video_url"
                                        controls="controls"
                                >
                                </video>
                                <div class="mark"></div>
                                <Icon
                                        type="ios-trash-outline"
                                        class="iconv"
                                        @click="delVideo"
                                />
                            </div>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="封面图：" prop="image">
                            <div class="pictrueBox">
                                <div class="pictrue" v-if="formValidate.image">
                                    <img v-lazy="formValidate.image" />
                                    <Button
                                            shape="circle"
                                            icon="md-close"
                                            @click.native="handleRemove"
                                            class="btndel"
                                    ></Button>
                                </div>
                                <div class="upLoad acea-row row-center-wrapper" @click="modalPicTap('image')" v-else>
                                    <Input v-model="formValidate.image" class="input-display"></Input>
                                    <Icon type="ios-add" size="26" />
                                </div>
                            </div>
                            <div class="tips">建议尺寸：226 * 300px</div>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="排序：">
                            <InputNumber
                                    v-model="formValidate.sort"
                                    :min="0"
                                    :max="99999999"
                                    v-width="'50%'"
                            ></InputNumber>
                        </FormItem>
                    </Col>
                </Row>
                <div v-show="currentTab === '2'">
                    <Table
                            :columns="columns"
                            :data="tableData"
                            @on-selection-change="selectChange"
                            highlight-row
                            no-userFrom-text="暂无数据"
                            no-filtered-userFrom-text="暂无筛选结果"
                            class="ivu-mt"
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
                        <template slot-scope="{ row, index }" slot="action">
                            <a @click="del(row)">删除</a>
                        </template>
                    </Table>
                </div>
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
                    >保存</Button
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
                title="上传商品图"
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
        <Modal v-model="modals" title="商品列表" footerHide  class="paymentFooter" scrollable width="900" @on-cancel="cancel">
            <goods-list ref="goodslist" :ischeckbox="true" :isdiy="true"  @getProductId="getProductId" v-if="modals"></goods-list>
        </Modal>
    </div>
</template>
<script>
    import { mapState } from "vuex";
    import uploadPictures from "@/components/uploadPictures";
    import { videoInfo, videoSave } from "@/api/marketing";
    import goodsList from '@/components/goodsList';
    export default {
        name: "create",
        components:{
            uploadPictures,
            goodsList
        },
        data() {
            const validateImage = (rule, value, callback) =>{
                if(!value){
                    return callback(new Error('请上传视频封面图'))
                }else{
                    callback();
                }
            };
            const validateVideo = (rule, value, callback) => {
                if(!value){
                    return callback(new Error('请上传视频'))
                }else{
                    callback();
                }
            };
            return {
                currentTab: "1",
                modals:false,
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
                headeNum: [
                    { type: "1", name: "基础设置" },
                    { type: "2", name: "关联商品" }
                ],
                formValidate:{
                    desc:'',
                    image:'',
                    sort:0,
                    video_url:'',
                    product_id:[]
                },
                modalPic: false,
                isChoice: "单选",
                ruleValidate: {
                    desc: [
                        { required: true, message: '请输入视频简介', trigger: 'blur' }
                    ],
                    video_url: [
                        {
                            required: true,
                            message: "请上传视频",
                            validator: validateVideo,
                            trigger: "change",
                        }
                    ],
                    image: [
                        {
                            required: true,
                            validator: validateImage,
                            trigger: 'change',
                        }
                    ]
                },
                columns: [
                    {
                        type: 'selection',
                        width: 60,
                        align: 'center'
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
                    },
                    {
                        title: "操作",
                        slot: "action",
                        fixed: "right",
                        width: 100,
                    }
                ],
                tableData:[],
                id:0,
                formSelection:[],
                typeTit:''
            };
        },
        computed: {
            ...mapState("admin/layout", ["isMobile","menuCollapse"]),
            labelWidth() {
                return this.isMobile ? undefined : 90;
            },
            labelPosition() {
                return this.isMobile ? "top" : "right";
            }
        },
        created() {
            this.id = this.$route.params.id || 0;
            if(this.id){
                this.getInfo();
            }
        },
        methods: {
            delVideo(){
                let that = this;
                that.$set(that.formValidate, "video_url", "");
                this.$refs.formValidate.validateField('video_url');
            },
            del(row){
                this.tableData.forEach((item,index)=>{
                    if(row.id === item.id){
                        return this.tableData.splice(index, 1)
                    }
                })
            },
            batchDel(){
                for(var i=0;i<this.formSelection.length;i++){
                    for(var j=0;j<this.tableData.length;j++){
                        if(this.tableData[j].id===this.formSelection[i].id){
                            this.tableData.splice(j,1);
                            j--;
                        }
                    }
                }
            },
            selectChange(data){
                this.formSelection = data;
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
                this.tableData = this.unique(list);
            },
            //视频详情
            getInfo(){
                videoInfo(this.id).then(res=>{
                    this.formValidate = res.data;
                    this.tableData = res.data.productInfo;
                }).catch(err=>{
                    this.$Message.error(err.msg)
                })
            },
            upTab(){
                this.currentTab =  '1';
            },
            downTab(name){
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        this.currentTab =  '2';
                    }else{
                        this.$Message.warning("请完善数据");
                    }
                })
            },
            //保存视频
            handleSubmit(name){
                this.$refs[name].validate((valid) => {
                    if (valid) {

                        let product_id = [];
                        this.tableData.forEach(item=>{
                            product_id.push(item.id)
                        });
                        this.formValidate.product_id = product_id;
                        videoSave(this.formValidate,this.id).then(res=>{
                            this.$router.push({ path: "/admin/marketing/short_video/index" });
                            this.$Message.success(res.msg)
                        }).catch(err=>{
                            this.$Message.error(err.msg)
                        })
                    }else{
                        this.$Message.warning("请完善数据");
                    }
                })
            },
            // 点击商品图
            modalPicTap(type) {
                this.typeTit = type;
                this.modalPic = true;
            },
            // 获取单张图片信息
            getPic(pc) {
                this.modalPic = false;
                if(this.typeTit == 'image'){
                    this.formValidate.image = pc.att_dir;
                    this.$refs.formValidate.validateField('image');
                }else{
                    this.formValidate.video_url = pc.att_dir;
                    this.$refs.formValidate.validateField('video_url');
                }
            },
            handleRemove() {
                this.formValidate.image = '';
                this.$refs.formValidate.validateField('image');
            },
        }
    };
</script>
<style scoped lang="less">
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
    .tips{
        font-size: 12px;
        font-weight: 400;
        color: #ccc;
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
    .fixed-card {
        position: fixed;
        right: 0;
        bottom: 0;
        left: 200px;
        z-index: 20;
        box-shadow: 0 -1px 2px rgb(240, 240, 240);

        /deep/ .ivu-card-body {
            padding: 15px 16px 14px;
        }

        .ivu-form-item {
            margin-bottom: 0!important;
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
</style>