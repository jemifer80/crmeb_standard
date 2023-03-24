<template>
  <view class="record">
    <!-- #ifdef H5 -->
    <view class="top_nav acea-row row-around">
      <text :class="active == 0 ? 'on' : ''" @click="switchTab(0)">购买记录</text>
      <text :class="active == 1 ? 'on' : ''" @click="switchTab(1)">浏览记录</text>
    </view>
    <view class="search acea-row row-center row-middle">
      <input type="text" v-model="keywords" class="search_input" placeholder="搜索商品" placeholder-class="text_gray"
        @blur="search()">
    </view>
    <Loading :loaded="loaded" :loading="loading"></Loading>
    <view class="goods_list" v-if="goodsList.length">
      <view class="item acea-row" v-for="(item,index) in goodsList" :key="index">
        <view class="picture">
          <image :src="item.image" mode="aspectFit"></image>
        </view>
        <view class="goods_info acea-row row-column row-between">
          <view class="name line2">{{item.store_name}}</view>
          <view class="stork acea-row row-between">
            <text>库存 {{item.stock}}</text>
            <text>销量 {{item.sales}}</text>
            <text class="pushFn" @click="pushFn(item)">推送</text>
          </view>
        </view>
        <view class="price_info acea-row row-column row-between">
          <text class="price">￥{{item.price}}</text>
        </view>
      </view>
    </view>
    <view class="empty-box" v-else>
      <image src="../static/shop_record.png" v-if="active == 0"></image>
      <image src="../static/view_record.png" v-if="active == 1"></image>
      <view>暂无 {{active ? '浏览' : '购买'}}记录</view>
    </view>
    <view class="ht100"></view>
    <tNav :active="2"></tNav>
    <!-- #endif -->
  </view>
</template>
<script>
  // #ifdef H5
  import tNav from '../components/tabNav.vue';
  import Loading from '@/components/Loading/index.vue';
  import {
    initWxConfig,
    initAgentConfig,
  } from "@/libs/work.js";
  import {
    getWorkCartList,
    getWorkVisitInfo,
    getWorkAgentConfig
  } from "@/api/work.js"
  // import {
  //   wx
  // } from "@/utils/agent.js"
  export default {
    data() {
      return {
        keywords: "",
        userId: "",
        loaded: false,
        loading: false, //是否加载中
        loadend: false, //是否加载完毕
        loadTitle: '加载更多', //提示语
        goodsList: [], //商品数组
        active: 0, //选项状态
        page: 1,
        limit: 10,
      }
    },
    components: {
      Loading,
      tNav
    },
    onLoad() {
      this.userId = this.$Cache.get('work_user_id')
      this.getList();
    },
    methods: {
      // 推送到商品详情
      pushFn(item) {
        getWorkAgentConfig(location.href).then(res => {
          jWeixin.agentConfig({
            corpid: res.data.corpid, // 必填，企业微信的corpid，必须与当前登录的企业一致
            agentid: res.data.agentid, // 必填，企业微信的应用id （e.g. 1000247）
            timestamp: res.data.timestamp, // 必填，生成签名的时间戳
            nonceStr: res.data.nonceStr, // 必填，生成签名的随机串
            signature: res.data.signature, // 必填，签名，见附录-JS-SDK使用权限签名算法
            jsApiList: ["getCurExternalContact", "getCurExternalChat", "getContext", "chooseImage", "sendChatMessage", ], 
            success: function(res) {
              console.log(res)
            },
          });
          jWeixin.invoke('sendChatMessage', {
            msgtype: "news", //消息类型，必填
            enterChat: true, //为true时表示发送完成之后顺便进入会话，仅移动端3.1.10及以上版本支持该字段
            news: {
              link: location.protocol + '://' + location.host + "/pages/goods_details/index?id=" + item.id, //H5消息页面url 必填
              title: item.store_name, //H5消息标题
              desc: "", //H5消息摘要
              imgUrl: item.image, //H5消息封面图片URL
            },
          }, function(res) {
            if (res.err_msg == 'sendChatMessage:ok') {
              console.log('发送成功', res)
              //发送成功
            } else {
              //发送失败
              console.log('发送失败', res)
            }
          })
        })
      },
      switchTab(index) {
        if (this.loading) return
        if (index === this.active) return;
        this.active = index;
        this.loadend = false;
        this.page = 1;
        this.$set(this, 'goodsList', []);
        this.getList();
      },
      search() {
        if (this.loading) return
        this.loadend = false;
        this.page = 1;
        this.$set(this, 'goodsList', []);
        this.getList();
      },
      getList() {
        let that = this;
        let params = {};
        if (that.loadend) return;
        if (that.loading) return;
        that.loading = true;
        that.loadTitle = '加载更多';
        params = {
          userid: that.userId,
          store_name: that.keywords,
          page: that.page,
          limit: that.limit,
        }
        if (that.active == 0) {
          getWorkCartList(params).then(res => {
            let list = res.data || [];
            let loadend = list.length < that.limit;
            that.goodsList = that.$util.SplitArray(list, that.goodsList);
            that.$set(that, 'goodsList', that.goodsList);
            that.loadend = loadend;
            that.loading = false;
            that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
            that.page = that.page + 1;
          }).catch(err => {
            return that.$util.Tips({
              title: err
            });
          })
        } else if (that.active == 1) {
          getWorkVisitInfo(params).then(res => {
            let list = res.data || [];
            let loadend = list.length < that.limit;
            that.goodsList = that.$util.SplitArray(list, that.goodsList);
            that.$set(that, 'goodsList', that.goodsList);
            that.loadend = loadend;
            that.loading = false;
            that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
            that.page = that.page + 1;
          }).catch(err => {
            return that.$util.Tips({
              title: err
            });
          })
        }
      }
    },
    onReachBottom: function() {
      this.getList();
    }
  }
  // #endif
</script>

<style lang="scss">
  /* #ifdef H5 */
  .top_nav {
    height: 80rpx;
    line-height: 80rpx;
    background: #FFFFFF;
    font-size: 28rpx;
    font-family: PingFangSC-Regular, PingFang SC;
    font-weight: 400;
    color: #000000;

    .on {
      font-size: 28rpx;
      font-family: PingFangSC-Semibold, PingFang SC;
      font-weight: 600;
      color: #1890FF;
    }
  }

  .search {
    border-top: 1px solid #f5f5f5;
    height: 100rpx;
    background: #FFFFFF;

    .search_input {
      width: 690rpx;
      height: 60rpx;
      background: #F5F6F9;
      border-radius: 34rpx;
      padding-left: 20rpx;
    }
  }

  .text_gray {
    font-size: 28rpx;
    font-family: PingFangSC-Regular, PingFang SC;
    font-weight: 400;
    color: #CCCCCC;
    text-align: center;
  }

  .goods_list {
    .item {
      position: relative;
      width: 710rpx;
      height: 192rpx;
      border-radius: 12rpx;
      margin: 20rpx auto 0;
      padding: 26rpx 24rpx 26rpx;
      box-sizing: border-box;
      background: #FFFFFF;

      .pushFn {
        position: absolute;
        bottom: 28rpx;
        right: 24rpx;
        width: 112rpx;
        height: 52rpx;
        border-radius: 28rpx;
        border: 2rpx solid #1890FF;
        font-size: 26rpx;
        font-weight: 400;
        line-height: 52rpx;
        text-align: center;
        color: #1890FF;
      }

      .picture {
        width: 140rpx;
        height: 140rpx;
        border-radius: 8rpx;

        image {
          width: 100%;
          height: 100%;
        }
      }

      .goods_info {
        width: 280rpx;
        margin-left: 22rpx;

        .name {
          height: 80rpx;
          line-height: 40rpx;
          font-size: 28rpx;
          color: rgba(0, 0, 0, 0.85);
        }

        .stork {

          font-size: 24rpx;
          font-weight: 400;
          color: #666666;

        }
      }

      .price_info {
        margin-left: 80rpx;

        .price {
          font-size: 28rpx;
          font-weight: 500;
          color: #E93323;
        }

        .send_btn {
          width: 112rpx;
          height: 52rpx;
          display: inline-block;
          margin-left: 20rpx;
          line-height: 52rpx;
          text-align: center;
          border-radius: 28rpx;
          border: 1px solid #1890FF;
          font-size: 26rpx;
          font-weight: 400;
          color: #1890FF;
        }
      }
    }
  }

  .empty-box {
    height: 600rpx;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    image {
      width: 390rpx;
      height: 264rpx;
    }
  }

  .ht100 {
    height: 120rpx;
  }

  /* #endif */
</style>
