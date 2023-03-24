<template>
    <div class="newVip" :class="{pageOn:itemStyle===0}" :style="{marginLeft:prConfig+'px',marginRight:prConfig+'px',marginTop:mTop+'px',background: `linear-gradient(90deg,${bgColor[0].item} 0%,${bgColor[1].item} 100%)`}">
        <div class="header acea-row row-between-wrapper" :style="{color:textColor}">
            <div class="title" >新人专享</div>
            <div class="more">更多<span class="iconfont iconjinru"></span></div>
        </div>
        <div class="list acea-row row-middle">
            <div class="item" v-for="(item,index) in list" :key="index">
                <div class="pictrue">
                    <img :src="item.image" v-if="item.image">
                    <div v-else class="empty-box"><span class="iconfont-diy icontupian"></span></div>
                    <div class="label" :style="{color:textColor,background: `linear-gradient(90deg,${bgColor[0].item} 0%,${bgColor[1].item} 100%)`}" v-if="checkType.indexOf(0) != -1">新人价</div>
                </div>
                <div class="money" :style="{color:priceColor}">¥{{item.price}}</div>
                <div class="y_money" v-if="checkType.indexOf(1) != -1">¥{{item.ot_price}}</div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapMutations } from 'vuex'
    export default {
        name: 'home_new_vip',
        cname: '新人专享',
        configName: 'c_new_vip',
        icon: 'iconxinrenli',
        type:1,// 0 基础组件 1 营销组件 2工具组件
        defaultName:'newVip', // 外面匹配名称
        props: {
            index: {
                type: null
            },
            num: {
                type: null
            }
        },
        computed: {
            ...mapState('admin/mobildConfig', ['defaultArray'])
        },
        watch: {
            pageData: {
                handler (nVal, oVal) {
                    this.setConfig(nVal)
                },
                deep: true
            },
            num: {
                handler (nVal, oVal) {
                    let data = this.$store.state.admin.mobildConfig.defaultArray[nVal]
                    this.setConfig(data)
                },
                deep: true
            },
            'defaultArray': {
                handler (nVal, oVal) {
                    let data = this.$store.state.admin.mobildConfig.defaultArray[this.num]
                    this.setConfig(data);
                },
                deep: true
            }
        },
        data () {
            return {
                // 默认初始化数据禁止修改
                defaultConfig: {
                    name: 'newVip',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    numConfig: {
                        val: 0
                    },
                    itemSort: {
                        title: '商品排序',
                        name: 'itemSort',
                        type: 0,
                        list: [
                            {
                                val: '综合',
                                icon: 'iconComm_whole'
                            },
                            {
                                val: '销量',
                                icon: 'iconComm_number'
                            },
                            {
                                val: '价格',
                                icon: 'iconComm_Price'
                            }
                        ]
                    },
                    checkboxInfo: {
                        title: '资产信息',
                        name: 'checkboxInfo',
                        type:[0],
                        list: [
                            {
                                id:0,
                                name:'新人标签'
                            },
                            {
                                id:1,
                                name:'原价'
                            }
                        ]
                    },
                    bgColor: {
                        title: '背景颜色',
                        name: 'bgColor',
                        default: [
                            {
                                item: "#FF7931",
                            },
                            {
                                item: "#E93323",
                            },
                        ],
                        color: [
                            {
                                item: "#FF7931",
                            },
                            {
                                item: "#E93323",
                            },
                        ]
                    },
                    textColor: {
                        title: '文字颜色',
                        name: 'labelColor',
                        default: [{
                            item: '#fff'
                        }],
                        color: [
                            {
                                item: '#fff'
                            }
                        ]
                    },
                    newVipList: {
                        title:'新人专享',
                        list:[]
                    },
                    priceColor: {
                        title: '价格颜色',
                        name: 'labelColor',
                        default: [{
                            item: '#E93323'
                        }],
                        color: [
                            {
                                item: '#E93323'
                            }
                        ]
                    },
                    itemStyle: {
                        title: '背景样式',
                        name: 'itemStyle',
                        type: 0,
                        list: [
                            {
                                val: '圆角',
                                icon: 'iconPic_fillet'
                            },
                            {
                                val: '直角',
                                icon: 'iconPic_square'
                            }
                        ]
                    },
                    prConfig: {
                        title: '背景边距',
                        val: 0,
                        min: 0
                    },
                    mbCongfig: {
                        title: '页面间距',
                        val: 0,
                        min: 0
                    }
                },
                bgColor: [],
                mTop: '',
                prConfig:0,
                pageData: {},
                itemStyle: 0,
                checkType:-1,
                textColor:'',
                priceColor:'',
                list:[]
            }
        },
        mounted () {
            this.$nextTick(() => {
                this.pageData = this.$store.state.admin.mobildConfig.defaultArray[this.num]
                this.setConfig(this.pageData);
            })
        },
        methods: {
            setConfig (data) {
                if(!data) return
                if(data.mbCongfig){
                    this.bgColor = data.bgColor.color;
                    this.mTop = data.mbCongfig.val;
                    this.prConfig = data.prConfig.val;
                    this.itemStyle = data.itemStyle.type;
                    this.checkType = data.checkboxInfo.type;
                    this.textColor = data.textColor.color[0].item;
                    this.priceColor = data.priceColor.color[0].item;
                    let list = data.newVipList.list;
                    if(list.length){
                        this.list = list;
                    }else {
                        this.list = [
                            {
                                image:'',
                                price:66,
                                ot_price:166
                            },
                            {
                                image:'',
                                price:66,
                                ot_price:166
                            },
                            {
                                image:'',
                                price:66,
                                ot_price:166
                            },
                            {
                                image:'',
                                price:66,
                                ot_price:166
                            }
                        ]
                    }
                }
            }
        }
    }
</script>

<style scoped lang="less">
    .pageOn{
        border-radius: 8px;
    }
    .newVip{
        padding: 0 15px 11px 15px;
        .header{
            height: 39px;
            .title{
                font-size: 15px;
            }
            .more{
                font-size: 12px;
                .iconfont{
                    font-size: 12px;
                    margin-left: 2px;
                }
            }
        }
        .list{
            overflow: hidden;
            flex-wrap: nowrap;
            .item{
                width: 86px;
                background: #FFFFFF;
                border-radius: 6px;
                padding: 6px 6px 0 6px;
                margin-right: 6px;
                .pictrue{
                    width: 74px;
                    height: 74px;
                    position: relative;
                    img{
                        width: 100%;
                        height: 100%;
                    }
                    .label{
                        width: 49px;
                        height: 16px;
                        background: #E93323;
                        border-radius: 8px;
                        position: absolute;
                        bottom: 6px;
                        left:50%;
                        margin-left: -24.5px;
                        font-size: 12px;
                        color: #fff;
                        text-align: center;
                        line-height: 16px;
                    }
                }
                .money{
                    font-weight: 500;
                    color: #E93323;
                    font-size: 12px;
                    margin-top: 3px;
                }
                .y_money{
                    font-weight: 400;
                    color: #CCCCCC;
                    font-size: 10px;
                    text-decoration: line-through;
                }
            }
        }
    }
</style>
