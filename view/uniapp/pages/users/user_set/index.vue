<template>
	<!-- 设置 -->
	<view class="userSet">
		<navigator url="/pages/users/user_info/index" hover-class="none" class="userInfo acea-row row-between-wrapper">
			<view class="picTxt acea-row row-middle">
				<view class="pictrue">
					<image :src="userInfo.avatar"></image>
				</view>
				<view class="text">
					<view class="name line1">{{userInfo.nickname}}</view>
					<view class="info">ID：{{userInfo.uid}}</view>
				</view>
			</view>
			<view class="iconfont icon-xiangyou"></view>
		</navigator>
		<view class="list">
			<!-- #ifdef H5 -->
			<view class="item acea-row row-between-wrapper" v-if="userInfo.phone && !this.$wechat.isWeixin()">
				<view>密码</view>
				<navigator url="/pages/users/user_pwd_edit/index" hover-class="none" class="input grab">
					点击修改密码<text class="iconfont icon-xiangyou"></text>
				</navigator>
			</view>
			<!-- #endif -->

			<!-- #ifdef APP-PLUS -->
			<view class="item acea-row row-between-wrapper" v-if="userInfo.phone">
				<view>密码</view>
				<navigator url="/pages/users/user_pwd_edit/index" hover-class="none" class="grab">
					点击修改密码<text class="iconfont icon-xiangyou"></text>
				</navigator>
			</view>
			<!-- #endif -->
			<view class="item acea-row row-between-wrapper" v-if="userInfo.phone">
				<view>更换手机号码</view>
				<navigator url="/pages/users/user_phone/index?type=1" hover-class="none" class="grab">
					点击更换手机号码<text class="iconfont icon-xiangyou"></text>
				</navigator>
			</view>
		</view>
		<view class="list">
			<view class="item acea-row row-between-wrapper">
				<view>地址管理</view>
				<navigator url="/pages/users/user_address_list/index" hover-class="none" class="grab">
					点击前往<text class="iconfont icon-xiangyou"></text>
				</navigator>
			</view>
			<view class="item acea-row row-between-wrapper">
				<view>发票管理</view>
				<navigator url="/pages/users/user_invoice_list/index" hover-class="none" class="grab">
					点击前往<text class="iconfont icon-xiangyou"></text>
				</navigator>
			</view>
		</view>
		<view class="list">
			<view class='item acea-row row-between-wrapper'>
				<view>移动网络下视频自动播放</view>
				<switch :checked="autoplay" @change="autoplayChange" />
			</view>
			<!-- #ifdef MP -->
			<view class='item acea-row row-between-wrapper'>
				<view>权限设置</view>
				<view class="input grab" @click="Setting">
					点击管理<text class="iconfont icon-xiangyou"></text>
				</view>
			</view>
			<!-- #endif -->
			<view class="item acea-row row-between-wrapper">
				<view>账号注销</view>
				<navigator url="/pages/users/user_cancellation/index" hover-class="none" class="input grab">
					注销后无法恢复<text class="iconfont icon-xiangyou"></text>
				</navigator>
			</view>
		</view>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->	
	</view>
</template>
<style lang="scss">
	.userSet {
		.userInfo {
			margin-top: 20rpx;
			background-color: #fff;
			padding: 0 30rpx;
			height: 144rpx;

			.iconfont {
				font-size: 30rpx;
				color: #868686;
			}

			.picTxt {
				.text {
					width: 524rpx;
					margin-left: 30rpx;
					font-weight: 400;

					.name {
						font-size: 32rpx;
						color: #333;
					}

					.info {
						font-size: 24rpx;
						color: #999;
						margin-top: 5rpx;
					}
				}

				.pictrue {
					width: 88rpx;
					height: 88rpx;

					image {
						width: 100%;
						height: 100%;
						border: 1px solid #eee;
						border-radius: 50%;
					}
				}
			}
		}
		.list{
			background-color: #fff;
			margin-top: 20rpx;
			.item{
				padding: 30rpx 30rpx 30rpx 0;
				border-bottom: 1rpx solid #f2f2f2;
				margin-left: 30rpx;
				font-size: 32rpx;
				color: #333;
				.grab{
					color: #ccc;
					.iconfont{
						font-size: 30rpx;
						color: #868686;
						margin-left: 6rpx;
					}
				}
				
				/deep/.uni-switch-input {
					width: 84rpx;
					height: 48rpx;
					margin: -8rpx 0;
					
					&::before {
						width: 80rpx;
						height: 44rpx;
					}
					
					&::after {
						width: 44rpx;
						height: 44rpx;
					}
				}
			}
		}
	}
</style>
<script>
	import {
		getUserInfo
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters,
		mapMutations
	} from "vuex";
	export default {
		computed: mapGetters(['isLogin']),
		data() {
			return {
				userInfo:{},
				isShowAuth: false,
				autoplay: this.$store.state.app.autoplay
			}
		},
		onLoad() {},
		onShow() {
			if (this.isLogin) {
				this.getUserInfo();
			} else {
				//#ifndef MP
				toLogin();
				//#endif
				//#ifdef MP
				this.isShowAuth = true;
				//#endif
			}
		},
		methods: {
			...mapMutations(['SET_AUTOPLAY']),
			/**
			 * 小程序设置
			 */
			Setting: function() {
				uni.openSetting({
					success: function(res) {
					}
				});
			},
			onLoadFun(){
				this.getUserInfo();
				this.isShowAuth = false
			},
			// 授权关闭
			authColse: function(e) {
			  this.isShowAuth = e
			},
			getUserInfo(){
				let that = this;
				getUserInfo().then(res => {
					that.userInfo = res.data;
				});
			},
			autoplayChange(event) {
				this.SET_AUTOPLAY(event.detail.value);
			}
		}
	}
</script>
