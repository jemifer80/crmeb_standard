<template>
    <div class="userInfor acea-row row-between-wrapper" :class="{pageOn:itemStyle===0}" :style="{marginLeft:prConfig+'px',marginRight:prConfig+'px',marginTop:mTop+'px',background:bgColor}">
        <div class="left acea-row row-middle">
            <div class="pictrue acea-row row-center-wrapper">
                <div class="empty-box"><span class="iconfont-diy icontupian"></span></div>
            </div>
            <div class="text" :style="'color:'+textColor">
                <div>用户名称</div>
                <div class="acea-row row-middle">
                    <div class="progress">
                        <div class="bgReds" :style="'background: linear-gradient(90deg, '+ progressColor[0].item +' 0%, '+ progressColor[1].item +' 100%);'"></div>
                    </div>
                    <div class="percent">80/100</div>
                </div>
                <!--<div class="phone"><span class="iconfont iconshouji"></span>13000000000</div>-->
            </div>
        </div>
        <div class="right acea-row row-bottom" :style="'color:'+textColor">
            <div class="item" v-if="checkType.indexOf(1) != -1">
                <div class="num">20</div>
                <div>积分</div>
            </div>
            <div class="item" v-if="checkType.indexOf(2) != -1">
                <div class="num">200</div>
                <div>余额</div>
            </div>
            <div class="item" v-if="checkType.indexOf(0) != -1">
                <div class="num">2</div>
                <div>优惠券</div>
            </div>
            <div class="item" v-if="checkType.indexOf(3) != -1">
                <div class="iconfont iconerweima-xingerenzhongxin"></div>
                <div>会员码</div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapMutations } from 'vuex'
    export default {
        name: 'home_userInfor',
        cname: '用户信息',
        configName: 'c_userInfor',
        icon: 'iconyonghuxinxi',
        type:0,// 0 基础组件 1 营销组件 2工具组件
        defaultName:'userInfor', // 外面匹配名称
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
                    name: 'userInfor',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    checkboxInfo: {
                        title: '资产信息',
                        name: 'checkboxInfo',
                        type:[0,1,2,3],
                        list: [
                            {
                                id:0,
                                name:'优惠券'
                            },
                            {
                                id:1,
                                name:'积分'
                            },
                            {
                                id:2,
                                name:'余额'
                            },
                            {
                                id:3,
                                name:'会员码'
                            }
                        ]
                    },
                    textColor: {
                        title: '文字颜色',
                        name: 'textColor',
                        default: [{
                            item: '#333'
                        }],
                        color: [
                            {
                                item: '#333'
                            }
                        ]
                    },
                    progressColor: {
                        title: '进度颜色',
                        name: 'progressColor',
                        default: [
                            {
                               item: '#e93323'
                            },
                            {
                               item: '#ff8933'
                            }
                        ],
                        color: [
                            {
                                item: '#e93323'
                            },
                            {
                                item: '#ff8933'
                            }
                        ]
                    },
                    bgColor: {
                        title: '背景颜色',
                        name: 'bgColor',
                        default: [{
                            item: '#fff'
                        }],
                        color: [
                            {
                                item: '#fff'
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
                bgColor: '',
                mTop: '',
                prConfig:0,
                pageData: {},
                itemStyle: 0,
                checkType:-1,
                textColor:'',
                progressColor:''
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
                    this.bgColor = data.bgColor.color[0].item;
                    this.textColor = data.textColor.color[0].item;
                    this.progressColor = data.progressColor.color;
                    this.mTop = data.mbCongfig.val;
                    this.prConfig = data.prConfig.val;
                    this.itemStyle = data.itemStyle.type;
                    this.checkType = data.checkboxInfo.type;
                }
            }
        }
    }
</script>

<style scoped lang="less">
   .userInfor{
       padding: 14px 10px;
       &.pageOn{
           border-radius: 6px;
       }
       .right{
           .item{
               text-align: center;
               font-weight: 400;
               //color: #333;
               font-size: 10px;
               margin-left: 16px;
               .num{
                   font-size: 14px;
                   margin-bottom: 2px;
               }
           }
           .iconfont{
               font-weight: 400;
               //color: #333333;
               font-size: 15px;
               margin-bottom: 2px;
           }
       }
       .left{
           .pictrue{
               width: 45px;
               height: 45px;
               border: 1px solid #EEEEEE;
               border-radius: 50%;
               margin-right: 12px;
               .empty-box{
                   border-radius: 50%;
                   .iconfont-diy{
                       font-size: 20px;
                   }
               }
           }
           .text{
               font-weight: 400;
               color: #333333;
               font-size: 14px;
               .progress{
                   overflow: hidden;
                   background-color: #EEEEEE;
                   width: 72px;
                   height: 7px;
                   border-radius: 3px;
                   position: relative;
                   margin-right: 3px;
                   .bgReds{
                       width: 80%;
                       height: 100%;
                       transition: width 0.6s ease;
                   }
               }
               .phone{
                   font-weight: 400;
                   //color: #333;
                   font-size: 10px;
                   margin-top: 3px;
                   .iconshouji{
                       margin-right: 2px;
                       font-size: 12px;
                   }
               }
           }
       }
   }
</style>
