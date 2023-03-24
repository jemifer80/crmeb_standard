<template>
	<view class="userInfor acea-row row-between-wrapper" :class="{pageOn:itemStyle===0}"
		:style="{marginLeft:prConfig+'rpx',marginRight:prConfig+'rpx',marginTop:mbCongfig+'rpx',background:bgColor}" v-show="!isSortType" @click="goLogin">
		<view class="left acea-row row-middle" :style="'color:'+textColor">
			<view class="pictrue acea-row row-center-wrapper">
				<image :src="diyInfo.avatar" v-if="diyInfo.avatar && isLogin"></image>
				<image v-if="!diyInfo.avatar && isLogin" src="@/static/images/f.png"></image>
				<image v-if="!isLogin" src="@/static/images/f.png"></image>
			</view>
			<view class="text">
				<view v-if="!isLogin" class="name">点击登录</view>
				<view v-else class="name acea-row row-middle">
					<view class="nameCon line1">{{diyInfo.nickname}}</view>
					<view class="lable" v-if="diyInfo.level>0"><text class="iconfont icon-v"></text>{{diyInfo.vip_name}}</view>
				</view>
				<view class="acea-row row-middle" v-if="isLogin && diyInfo.level>0">
					<view class="progress">
						<view class='bg-reds' :style="'width:'+(diyInfo.exp>diyInfo.next_exp?100:$util.$h.Div(parseInt(diyInfo.exp), diyInfo.next_exp)>=5?$util.$h.Div(parseInt(diyInfo.exp), diyInfo.next_exp):5)+'%;background:linear-gradient(90deg, '+progressColor[0].item+' 0%,'+progressColor[1].item+' 100%);'"></view>
					</view>
					<view class="percent">{{diyInfo.exp?diyInfo.exp.split('.')[0]:0}}/{{diyInfo.next_exp || 0}}</view>
				</view>
				<view class="phone acea-row row-middle" v-if="isLogin && diyInfo.level<=0 && diyInfo.phone">
					<text class="iconfont icon-shouji2"></text>
					<text>{{diyInfo.phone}}</text>
				</view>
			</view>
		</view>
		<view class="right acea-row row-bottom" :style="'color:'+textColor">
			<view class="item" v-if="diyInfo.level>0 && checkType.indexOf(1) != -1" @click.stop="goIntegral">
				<view class="num">{{diyInfo.integral||2}}</view>
				<view>积分</view>
			</view>
			<view class="item" v-if="diyInfo.level>0 && checkType.indexOf(2) != -1" @click.stop="goMoney">
				<view class="num">{{diyInfo.now_money||6}}</view>
				<view>余额</view>
			</view>
			<view class="item" v-if="checkType.indexOf(0) != -1" @click.stop="goCoupon">
				<view class="num">{{diyInfo.coupon_num||0}}</view>
				<view>优惠券</view>
			</view>
			<view class="item" v-if="(diyInfo.level == 0 && checkType.indexOf(3) != -1) || !isLogin" @click.stop="tapQrCode">
				<view class="iconfont icon-erweima3"></view>
				<view>会员码</view>
			</view>
		</view>
		<view class="codePopup" :style="colorStyle" v-show="isCode">
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
	</view>
</template>

<script>
	import colors from "@/mixins/color";
	import {
		diyUserInfo
	} from '@/api/api.js';
	import {
		getRandCode
	} from '@/api/user.js';
	import {
		mapGetters
	} from 'vuex';
	export default {
		computed: mapGetters(['isLogin']),
		name: 'userInfor',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		mixins: [colors],
		data() {
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
				bgColor: this.dataConfig.bgColor.color[0].item,
				textColor: this.dataConfig.textColor.color[0].item,
				progressColor: this.dataConfig.progressColor.color,
				mbCongfig: this.dataConfig.mbCongfig.val*2,
				prConfig: this.dataConfig.prConfig.val*2, //背景边距
				itemStyle: this.dataConfig.itemStyle.type,
				checkType: this.dataConfig.checkboxInfo.type,
				diyInfo:{}
			}
		},
		created() {
			if (this.isLogin) {
				this.getDiyUserInfo();
			}
		},
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getDiyUserInfo();
					}
				},
				deep: true
			}
		},
		methods: {
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
				this.isCode = true;
				this.codeIndex = 0;
				this.$nextTick(function() {
				  let code = this.diyInfo.bar_code;
				  this.config.bar.code = code;
				  this.config.qrc.code = code;
				})
			},
			closeCode() {
			  this.isCode = false
			  this.isextension = false
			},
			tapCode(index) {
			  this.codeIndex = index;
			  if (index == 1) {
			    this.getCode();
			  } else {
			    let code = this.diyInfo.bar_code;
			    this.config.bar.code = code;
			    this.config.qrc.code = code;
			  }
			},
			goIntegral(){
				uni.navigateTo({
					url: '/pages/users/user_integral/index'
				})
			},
			goMoney(){
				uni.navigateTo({
					url: '/pages/users/user_money/index'
				})
			},
			goCoupon(){
				uni.navigateTo({
					url: '/pages/users/user_coupon/index'
				})
			},
			goLogin(){
				if(!this.isLogin){
					this.$emit('changeLogin');
				}
			},
			getDiyUserInfo(){
				diyUserInfo().then(res=>{
					this.diyInfo = res.data;
				}).catch(err=>{
					this.$util.Tips({
						title: err
					});
				})
			}
		}
	}
</script>

<style lang="scss">
	.userInfor {
		padding: 28rpx 20rpx;
		.mark {
		  position: fixed;
		  top: 0;
		  left: 0;
		  bottom: 0;
		  right: 0;
		  background: rgba(0, 0, 0, 0.5);
		  z-index: 50;
		}
		.codePopup .icon-guanbi2{
			margin-top: 75rpx !important;
		}
		&.pageOn {
			border-radius: 12rpx;
		}

		.right {
			.item {
				text-align: center;
				font-weight: 400;
				//color: #666666;
				font-size: 20rpx;
				margin-left: 32rpx;

				.num {
					font-size: 28rpx;
					margin-bottom: 4rpx;
				}
			}

			.iconfont {
				font-weight: 400;
				//color: #333333;
				font-size: 30rpx;
				margin-bottom: 4rpx;
			}
		}

		.left {
			.pictrue {
				width: 90rpx;
				height: 90rpx;
				border: 1px solid #EEEEEE;
				border-radius: 50%;
				margin-right: 24rpx;
				
				image{
					width: 100%;
					height: 100%;
					border-radius: 50%;
				}
			}

			.text {
				font-weight: 400;
				//color: #333333;
				font-size: 28rpx;
				.name{
					margin-bottom: 10rpx;
					.nameCon{
						max-width: 190rpx;
					}
					.lable{
						background: #FFE8CA;
						border-radius: 20rpx;
						padding:2rpx 10rpx;
						margin-left: 12rpx;
						font-size: 20rpx;
						color: #333;
						.icon-v{
							margin-right: 4rpx;
							font-size: 24rpx;
						}
					}
				}
				.progress{
					overflow: hidden;
					background-color: #EEEEEE;
					width: 144rpx;
					height: 14rpx;
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
				}

				.phone {
					font-weight: 400;
					//color: #666666;
					font-size: 20rpx;
					margin-top: 6rpx;

					.icon-shouji2 {
						margin-right: 4rpx;
						font-size: 20rpx;
					}
				}
			}
		}
	}
</style>