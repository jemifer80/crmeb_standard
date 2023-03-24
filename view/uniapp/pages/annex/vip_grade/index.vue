<template>
	<view class="vipGrade">
		<view class="headerBg">
			<view class="header">
				<view class="top acea-row row-between-wrapper">
					<view class="acea-row row-middle">
						<view class="pictrue">
							<image :src="user_info.avatar"></image>
						</view>
						<view>
							<view class="acea-row row-middle">
								<view class="nickname line1">{{user_info.nickname}}</view>
								<view class="name" v-if="level_info.name">{{level_info.name}}</view>
								<image :src="level_info.icon" class="levelImage" v-if="level_info.icon"></image>
							</view>
							<view class="acea-row row-middle">
								<view class="progress">
									<view class='bg-reds' :style="'width:'+(level_info.exp>level_info.next_exp?100:$util.$h.Div(parseInt(level_info.exp), level_info.next_exp)>=5?$util.$h.Div(parseInt(level_info.exp), level_info.next_exp):5)+'%;'"></view>
								</view>
								<view class="percent">{{level_info.exp?level_info.exp.split('.')[0]:0}}/{{level_info.next_exp || 0}}</view>
							</view>
						</view>
					</view>
					<view class="code" @click="tapQrCode">
						<view class="iconfont icon-erweima3"></view>
						<view>会员码</view>
					</view>
				</view>
				<view class="bottom acea-row row-middle">
					<view class="item">
						<view>积分</view>
						<view class="num">{{user_info.integral || 0}}</view>
					</view>
					<view class="item">
						<view>余额</view>
						<view class="num" v-if="user_info.now_money">{{user_info.now_money.split('.')[0] || 0}}<text class="numSp" v-if="user_info.now_money.split('.')[1]>0">.{{user_info.now_money.split('.')[1]}}</text></view>
						<view class="num" v-else>0</view>
					</view>
					<view class="item">
						<view>优惠券</view>
						<view class="num">{{user_info.couponCount || 0}}</view>
					</view>
					<view class="item">
						<view>折扣</view>
						<view class="num">{{level_info.discount/10 || 0}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="equity">
			<view class="title acea-row row-between-wrapper">
				<view>{{level_info.name || ''}}会员尊享权益</view>
				<view class="more" @click="more">查看更多<text class="iconfont icon-jinru2"></text></view>
			</view>
			<view class="list acea-row row-around row-middle">
				<view class="item">
					<view class="pictrue">
						<image :src="imgHost+'/statics/images/userVip1.png'"></image>
					</view>
					<view>购物折扣</view>
				</view>
				<view class="item">
					<view class="pictrue">
						<image :src="imgHost+'/statics/images/userVip2.png'"></image>
					</view>
					<view>专属徽章</view>
				</view>
				<view class="item">
					<view class="pictrue">
						<image :src="imgHost+'/statics/images/userVip3.png'"></image>
					</view>
					<view>经验累积</view>
				</view>
				<view class="item">
					<view class="pictrue">
						<image :src="imgHost+'/statics/images/userVip4.png'"></image>
					</view>
					<view>尊享客服</view>
				</view>
			</view>
		</view>
		<view class="task">
			<view class="title acea-row row-between-wrapper">
				<view>成长任务</view>
				<view class="more" @click="more">查看更多<text class="iconfont icon-jinru2"></text></view>
			</view>
			<view class="list">
				<view class="item acea-row row-between-wrapper">
					<view class="acea-row row-middle">
						<view class="pictrue acea-row row-center-wrapper">
							<text class="iconfont icon-meiriqiandao"></text>
						</view>
						<view class="txt">
							<view>每日签到</view>
							<view class="exp acea-row row-middle"><image src="../static/exp.png"></image>经验值+{{task_info.sign}}</view>
						</view>
					</view>
					<navigator class="bnt acea-row row-center-wrapper" url="/pages/users/user_sgin/index" hover-class="none">去完成</navigator>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view class="acea-row row-middle">
						<view class="pictrue acea-row row-center-wrapper">
							<text class="iconfont icon-goumaishangpin"></text>
						</view>
						<view class="txt">
							<view>购买商品</view>
							<view class="exp acea-row row-middle"><image src="../static/exp.png"></image>经验值+{{task_info.order}}</view>
						</view>
					</view>
					<navigator class="bnt acea-row row-center-wrapper" open-type="switchTab" url="/pages/goods_cate/goods_cate"
						hover-class="none">去完成</navigator>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view class="acea-row row-middle">
						<view class="pictrue acea-row row-center-wrapper">
							<text class="iconfont icon-yaoqinghaoyou2"></text>
						</view>
						<view class="txt">
							<view>邀请好友</view>
							<view class="exp acea-row row-middle"><image src="../static/exp.png"></image>经验值+{{task_info.invite}}</view>
						</view>
					</view>
					<navigator class="bnt acea-row row-center-wrapper" url="/pages/users/user_spread_code/index" hover-class="none">去完成
					</navigator>
				</view>
			</view>
		</view>
		<view class="task on">
			<view class="title acea-row row-between-wrapper">
				<view>消费记录</view>
				<view class="more" @click="record">查看更多<text class="iconfont icon-jinru2"></text></view>
			</view>
			<view class="list">
				<view class="item acea-row row-between row-top" v-for="(item,index) in list" :key="index">
					<view class="acea-row row-top">
						<text class="icon iconfont icon-xiaofeijilu1" v-if="item.type==1"></text>
						<text class="icon iconfont icon-fufeihuiyuan1" v-else-if="item.type==7"></text>
						<text class="icon iconfont icon-xiaofeijilu-rongcuo" v-else></text>
						<view class="txt">
							<view class="line1">{{item.title}}</view>
							<view class="exp record">{{item.type_name}}</view>
							<view class="time">{{item.day}}</view>
						</view>
					</view>
					<view class="num">-{{item.price}}</view>
				</view>
			</view>
		</view>
		<view class="codePopup" v-show="isCode">
		  <view class="header acea-row row-between-wrapper">
		    <view class="title" :class="{'on': codeIndex == index,'onLeft':codeIndex == 1}"
		      v-for="(item, index) in codeList" :key="index" @click="tapCode(index)">{{item.name}}</view>
		  </view>
		  <view>
		    <view class="acea-row row-center-wrapper">
		      <w-barcode :options="config.bar"></w-barcode>
		    </view>
		    <view class="acea-row row-center-wrapper" style="margin-top: 35rpx;">
		      <w-qrcode :options="config.qrc" @generate="hello"></w-qrcode>
		    </view>
		    <view class="codeNum">{{config.bar.code}}</view>
		    <view class="tip">如遇到扫码失败请将屏幕调至最亮重新扫码</view>
		  </view>
		  <view class="iconfont icon-guanbi2" @click="closeCode"></view>
		</view>
		<view class="mark" v-if="isCode"></view>
		<!-- #ifdef MP -->
		  <authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
	  HTTP_REQUEST_URL
	} from '@/config/app';
	import {
	  getlevelInfo,
		moneyList,
		getRandCode
	} from '@/api/user.js';
	import {
		mapGetters
	} from 'vuex';
	import {
		toLogin
	} from '@/libs/login.js';
	export default {
		computed: mapGetters(['isLogin']),
		data(){
			return {
				config: {
				  bar: {
				    code: '',
				    color: ['#000'],
				    bgColor: '#FFFFFF', // 背景色
				    width: 480, // 宽度
				    height: 110 // 高度
				  },
				  qrc: {
				    code: '',
				    size: 380, // 二维码大小
				    level: 3, //等级 0～4
				    bgColor: '#FFFFFF', //二维码背景色 默认白色
				    border: {
				      color: ['#eee', '#eee'], //边框颜色支持渐变色
				      lineWidth: 3, //边框宽度
				    },
				    // img: '/static/logo.png', //图片
				    // iconSize: 40, //二维码图标的大小
				    color: ['#333', '#333'], //边框颜色支持渐变色
				  }
				},
				codeList: [{
				  name: '会员码'
				}, {
				  name: '付款码'
				}],
				codeIndex: 0,
				isCode: false,
				imgHost: HTTP_REQUEST_URL,
				level_info:{},
				user_info:{},
				task_info:{},
				list:[],
				isShowAuth:false
			}
		},
		onLoad(){
			this.levelInfo();
			this.getUserBillList();
		},
		onReady(){
		},
		onShow(){
		},
		methods:{
			/**
			 * 授权回调
			 */
			onLoadFun: function() {
				this.isShowAuth = false;
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			more(){
				uni.navigateTo({
					url: '/pages/users/user_vip/index'
				})
			},
			record(){
				uni.navigateTo({
					url: '/pages/annex/record_list/index'
				})
			},
			hello(res) {
			  // console.log(321,res)
			},
			getCode() {
			  getRandCode().then(res => {
			    let code = res.data.code;
			    this.config.bar.code = code;
			    this.config.qrc.code = code;
			  }).catch(err => {
			    return this.$util.Tips(err);
			  })
			},
			tapQrCode() {
				if(this.isLogin){
					this.isCode = true;
					this.codeIndex = 0;
					this.$nextTick(function() {
					  let code = this.user_info.bar_code;
					  this.config.bar.code = code;
					  this.config.qrc.code = code;
					})
				}else{
					// #ifndef MP
					toLogin()
					// #endif
					// #ifdef MP
					this.isShowAuth = true;
					// #endif
				}
			},
			closeCode() {
			  this.isCode = false
			},
			tapCode(index) {
			  this.codeIndex = index;
			  if (index == 1) {
			    this.getCode();
			  } else {
			    let code = this.user_info.bar_code;
			    this.config.bar.code = code;
			    this.config.qrc.code = code;
			  }
			},
			levelInfo(){
				getlevelInfo().then(res=>{
					this.user_info = res.data.user;
					this.task_info = res.data.task;
					this.level_info = res.data.level_info;
					res.data.level_list.forEach(item=>{
						if(item.name === res.data.level_info.name){
							this.level_info.next_exp = item.next_exp_num
						}
					})
				}).catch(err=>{
					this.$util.Tips({
						title: err
					})
				})
			},
			getUserBillList(){
				moneyList({
					page: 1,
					limit: 5
				},9).then(res=>{
					this.list = res.data.list;
				}).catch(err=>{
					this.$util.Tips({
						title: err
					})
				})
			}
		}
	}
</script>

<style lang="scss">
	.vipGrade{
		.mark {
		  position: fixed;
		  top: 0;
		  left: 0;
		  bottom: 0;
		  right: 0;
		  background: rgba(0, 0, 0, 0.5);
		  z-index: 50;
		}
		.codePopup .header .title.on{
			background-color: #F7B942 !important;
		}
		.headerBg{
			background: url('../static/big-bg.png') no-repeat;
			background-size: 100% 100%;
			width: 100%;
			height: 276rpx;
			padding-top: 1rpx;
			.header{
				width: 690rpx;
				height: 318rpx;
				background: url('../static/grade-bg.png') no-repeat;
				background-size: 100% 100%;
				margin: 18rpx auto 0 auto;
				padding-top: 40rpx;
				.top{
					margin: 0 30rpx 70rpx 30rpx;
					.progress{
						overflow: hidden;
						background-color: #EEEEEE;
						width: 200rpx;
						height: 6rpx;
						border-radius: 7rpx;
						position: relative;
						margin-right: 6rpx;
						.bg-reds{
							width: 0;
							height: 100%;
							transition: width 0.6s ease;
							background: linear-gradient(90deg, rgba(233, 51, 35, 1) 0%, rgba(255, 137, 51, 1) 100%);
						}
					}
					.percent{
						font-size: 20rpx;
						color: #463B26;
						margin-left: 12rpx;
					}
					.code{
						color: #333333;
						font-size: 20rpx;
						text-align: center;
						.icon-erweima3{
							margin-bottom: 6rpx;
						}
					}
					.pictrue{
						width: 80rpx;
						height: 80rpx;
						border: 2rpx solid #9A8661;
						border-radius: 50%;
						margin-right: 22rpx;
						image{
							width: 100%;
							height: 100%;
							border-radius: 50%;
						}
					}
					.nickname{
						font-size: 30rpx;
						font-weight: 600;
						color: #333333;
						max-width: 220rpx;
					}
					.name{
						font-size: 20rpx;
						color: #E8C891;
						font-weight: 500;
						background: #333333;
						border-radius: 6rpx;
						padding: 0 6rpx;
						margin-left: 12rpx;
					}
					.levelImage{
						width: 30rpx;
						height: 30rpx;
						margin-left: 12rpx;
					}
				}
				.bottom{
					padding-left: 30rpx;
					.item{
						flex: 1;
						padding: 0 10rpx;
						color: #333333;
						font-size: 24rpx;
						.num{
							font-weight: 600;
							font-size: 36rpx;
							margin-top: 6rpx;
							.numSp{
								font-size: 26rpx;
							}
						}
					}
				}
			}
		}
		.equity{
			margin: 82rpx auto 0 auto;
			width: 690rpx;
			height: 300rpx;
			background: #FFFFFF;
			border-radius: 14rpx;
			.title{
				padding: 28rpx 24rpx 0 24rpx;
				font-weight: 600;
				color: #333333;
				font-size: 34rpx;
				.more{
					font-weight: 400;
					color: #666666;
					font-size: 24rpx;
					.iconfont{
						font-size: 20rpx;
					}
				}
			}
			.list{
				margin-top: 44rpx;
				.item{
					color: #282828;
					font-size: 26rpx;
					.pictrue{
						width: 90rpx;
						height: 90rpx;
						border-radius: 50%;
						margin-bottom: 12rpx;
						image{
							width: 100%;
							height: 100%;
							border-radius: 50%;
						}
					}
				}
			}
		}
		.task{
			width: 690rpx;
			height: 524rpx;
			background: #FFFFFF;
			border-radius: 14rpx;
			margin: 20rpx auto 0 auto;
			&.on{
				height: unset;
			}
			.title{
				padding: 34rpx 24rpx 46rpx 24rpx;
				font-weight: 600;
				color: #333333;
				line-height: 30rpx;
				.more{
					font-weight: 400;
					color: #666666;
					font-size: 24rpx;
					.iconfont{
						font-size: 20rpx;
					}
				}
			}
			.list{
				.item{
					.icon{
						font-size: 72rpx;
						color: #E7C993;
					}
					.num{
						color: #282828;
						font-size: 32rpx;
						font-weight: 600;
					}
					padding: 0 24rpx 26rpx 24rpx;
					position: relative;
					margin-bottom: 34rpx;
					.pictrue{
						width: 76rpx;
						height: 76rpx;
						background-color: #FDF8EE;
						border-radius: 50%;
						.iconfont{
							color: #F7B942;
							font-size: 40rpx;
						}
					}
					.txt{
						font-weight: 400;
						color: #282828;
						font-size: 28rpx;
						margin-left: 24rpx;
						.line1{
							width: 320rpx;
						}
						.exp{
							color: #999999;
							font-size: 22rpx;
							margin-top: 2rpx;
							&.record{
								margin-top: 8rpx;
							}
							image{
								color: #999999;
								margin-right: 10rpx;
								width: 22rpx;
								height: 22rpx;
							}
						}
						.time{
							color: #999999;
							font-size: 22rpx;
							margin-top: 8rpx;
						}
					}
					.bnt{
						width: 112rpx;
						height: 44rpx;
						background: #F4DBAB;
						border-radius: 26rpx;
						color: #755214;
						font-size: 24rpx;
					}
					&::before{
						position: absolute;
						content: '';
						width: 542rpx;
						height: 1px;
						background: #EEEEEE;
						bottom: 0rpx;
						right: 24rpx;
					}
				}
			}
		}
	}
</style>
