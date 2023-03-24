<template>
    <div class="homeComb" :class="bannerImg?'':'on'">
        <div class="bgImg">
            <img :src="bannerImg" v-if="bannerImg">
        </div>
        <div class="searchBox acea-row row-between-wrapper">
            <img :src="imgSrc" alt="" v-if="imgSrc">
            <div class="box acea-row row-between-wrapper" :class="imgSrc?'':'on'">
                <span>请输入搜索词</span>
                <span class="iconfont iconsousuo1"></span>
            </div>
        </div>
        <div class="nav acea-row row-middle" v-if="classShow">
            <div class="item" :class="index==0?'on':''" :style="'color:'+txtColor" v-for="(item,index) in navList" :key="index">
                {{item.name}}
                <span class="lines" :style="'background:'+txtColor"></span>
            </div>
            <div class="bar" :style="'background:linear-gradient(135deg, rgba(215,215,215,0) 0%,'+txtColor+'50%,rgba(215,215,215,0) 100%)'"></div>
            <div class="iconfont iconerweima" :style="'color:'+txtColor"></div>
        </div>
        <div class="banner" :class="classShow?'':'on'">
            <img :src="bannerImg" v-if="bannerImg">
            <div class="empty-box on" v-else>
                <span class="iconfont-diy icontupian"></span>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'home_comb', // 组件名称
  cname: '组合组件', // 标题名称
  icon: 'icontupianmofang2',
  defaultName: 'homeComb', // 外面匹配名称
  configName: 'c_home_comb', // 右侧配置名称
  type: 0, // 0 基础组件 1 营销组件 2工具组件
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
      handler(nVal, oVal) {
        this.setConfig(nVal);
      },
      deep: true
    },
    num: {
      handler(nVal, oVal) {
        const data = this.$store.state.admin.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true
    },
    defaultArray: {
      handler(nVal, oVal) {
        const data = this.$store.state.admin.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true
    }
  },
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        name: 'homeComb',
        timestamp: this.num,
        tabConfig: {
          title: '选择模板',
          tabVal: 0,
          type: 0,
          tabList: [
            {
              name: '搜索框',
              icon: 'iconsousuo11'
            },
            {
              name: '分类',
              icon: 'iconzuhe-fenlei'
            },
            {
              name: '轮播图',
              icon: 'icona-zuhe-banner1'
            }
          ]
        },
        logoConfig: {
          type: 1,
          header: '设置logo',
          title: '建议上传大小：宽138px，高60px',
          url: ''
        },
        numConfig: {
          placeholder: '设置搜索热词显示时间',
          title: '显示时间(s)',
          mtop: true,
          val: null
        },
        hotWords: {
          list: [
            {
              val: ''
            }
          ]
        },
        classShow: {
          title: '商品分类',
          val: true
        },
        txtColor: {
          title: '文字颜色',
          default: [
            {
              item: '#fff'
            }
          ],
          color: [
            {
              item: '#fff'
            }
          ]
        },
        classColor: {
          title: '下拉分类',
          default: [
            {
              item: '#FF448F'
            }
          ],
          color: [
            {
              item: '#FF448F'
            }
          ]
        },
        // 背景颜色
        bgColor: {
          title: '背景颜色',
          default: [
            {
              item: '#fff'
            },
            {
              item: '#fff'
            }
          ],
          color: [
            {
              item: '#fff'
            },
            {
              item: '#fff'
            }
          ]
        },
        // 图片列表
        swiperConfig: {
          title: '最多可添加10张图片，建议宽度750px；鼠标拖拽左侧圆点可调整图片 顺序',
          maxList: 10,
          list: [
            {
              img: '',
              info: [
                {
                  title: '标题',
                  value: '今日推荐',
                  tips: '选填，不超过4个字',
                  max: 4
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100
                }
              ]
            }
          ]
        }
      },
      pageData: {},
      imgSrc: '',
      classShow: true,
      txtColor: '#fff',
      bannerImg: '',
      navList: [
        {
          name: '首页'
        },
        {
          name: '精选'
        },
        {
          name: '美妆'
        },
        {
          name: '母婴'
        },
        {
          name: '饰品'
        },
        {
          name: '运动'
        },
        {
          name: '奢品'
        }
      ]
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.admin.mobildConfig.defaultArray[
        this.num
      ];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    setConfig(data) {
      if (!data) return;
      if (data.txtColor) {
        this.imgSrc = data.logoConfig.url;
        this.classShow = data.classShow.val;
        this.txtColor = data.txtColor.color[0].item;
        this.bannerImg = data.swiperConfig.list.length
          ? data.swiperConfig.list[0].img
          : '';
      }
    }
  }
};
</script>

<style scoped lang="stylus">
    .homeComb{
        width 100%;
        position relative;
        overflow hidden;
        padding-bottom 13px;
        &.on{
            background rgba(0,0,0,0.2);
        }
        .bgImg{
            position absolute;
            width 100%;
            height 100%;
            top:0;
            z-index 1;
            filter:blur(0);
            overflow hidden;
            img{
                width: 100%;
                height: 100%;
                filter: blur(15px);
                transform: scale(1.5);
            }
        }
        .searchBox{
            position relative;
            padding 9px 12px 0 12px;
            z-index 1;
            img{
                width 69px;
                height 30px;
                display inline-block;
            }
            .box{
                width: 275px;
                height: 29px;
                border-radius: 16px 16px 16px 16px;
                background-color rgba(228, 228, 228, 0.4);
                padding 0 12px;
                font-size: 12px;
                font-weight 400;
                color rgba(255, 255, 255, 0.5);
                &.on{
                    width 100%;
                }
            }
        }
        .nav{
            position relative
            z-index 1;
            padding 0 12px;
            width 100%;
            box-sizing border-box;
            height 42px;
            .iconfont{
                font-size 14px;
                color #fff;
            }
            .bar{
                width: 1px;
                height: 15px;
                background: linear-gradient(135deg, rgba(215,215,215,0) 0%, #fff 50%, rgba(215,215,215,0) 100%);
                margin 0 5px;
            }
            .item{
                font-weight: 400;
                color: #FFFFFF;
                font-size: 15px;
                position relative
                &~.item{
                    margin-left 19px;
                }
                &.on{
                    font-size 16px;
                    .lines{
                        position absolute;
                        width: 10px;
                        height: 2px;
                        background: #FFFFFF;
                        transform: translateX(-50%);
                        left:50%;
                        bottom 0
                    }
                }
            }
        }
        .banner{
            width 351px;
            height 146px;
            position relative
            z-index 1;
            border-radius 6px;
            margin 0 auto;
            &.on{
                margin-top 15px;
            }
            img{
                width 100%;
                height 100%;
                border-radius 6px;
            }
        }
    }
</style>
