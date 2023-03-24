<template>
	<view :style="colorStyle">
		<view class="ChangePassword">
			<form @submit="editPwd">
				<view class="phone">当前手机号：{{phone}}</view>
				<view class="list">
					<view class="item">
						<input type='password' placeholder='设置新密码' placeholder-class='placeholder' name="password" :value="password"></input>
					</view>
					<view class="item">
						<input type='password' placeholder='确认新密码' placeholder-class='placeholder' name="qr_password" :value="qr_password"></input>
					</view>
					<view class="item acea-row row-between-wrapper">
						<input type='number' placeholder='填写验证码' placeholder-class='placeholder' class="codeIput" name="captcha" :value="captcha"></input>
						<button class="code font-num" :class="disabled === true ? 'on' : ''" :disabled='disabled' @click="code">
							{{ text }}
						</button>
					</view>
				</view>
				<button form-type="submit" class="confirmBnt bg-color">确认修改</button>
			</form>
		</view>
		<Verify @success="success" :captchaType="'blockPuzzle'" :imgSize="{ width: '330px', height: '155px' }"
			ref="verify"></Verify>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->	
	</view>
</template>

<script>
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import {
		phoneRegisterReset,
		registerVerify,
		verifyCode
	} from '@/api/api.js';
	import {
		getUserInfo,
		getCodeApi
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import colors from '@/mixins/color.js';
	import Verify from '@/components/verify/verify.vue';
	export default {
		mixins: [sendVerifyCode,colors],
		components: {
			Verify
		},
		data() {
			return {
				userInfo: {},
				phone: '',
				password: '',
				qr_password: '',
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false ,//是否隐藏授权
				key: '',
			};
		},
		computed: mapGetters(['isLogin']),
		watch:{
			isLogin:{
				handler:function(newV,oldV){
					if(newV){
						//#ifndef MP
						this.getUserInfo();
						this.getVerifyCode();
						//#endif
					}
				},
				deep:true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.getUserInfo();
				this.getVerifyCode();
			} else {
				//#ifndef MP
				toLogin();
				//#endif
				//#ifdef MP
				this.isShowAuth = true;
				//#endif
			}
		},
		onShow() {
			uni.removeStorageSync('form_type_cart');
		},
		methods: {
			/**
			 * 授权回调
			 */
			onLoadFun: function(e) {
				this.getUserInfo();
				this.getVerifyCode();
				this.isShowAuth = false;
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			getVerifyCode(){
				verifyCode().then(res=>{
					this.$set(this, 'key', res.data.key)
				});
			},
			/**
			 * 获取个人用户信息
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					let tel = res.data.phone;
					let phone = tel.substr(0, 3) + "****" + tel.substr(7);
					that.$set(that, 'userInfo', res.data);
					that.phone = phone;
				});
			},
			success(data) {
				console.log(data,'data');
				this.$refs.verify.hide()
				getCodeApi()
					.then(res => {
						this.keyCode = res.data.key;
						this.getCode(data);
					})
					.catch(res => {
						this.$util.Tips({
							title: res
						});
					});
			},
			/**
			 * 发送验证码
			 * 
			 */
			code(data) {
				let that = this;
				if (!that.userInfo.phone) return that.$util.Tips({
					title: '手机号码不存在,无法发送验证码！'
				});
				this.$refs.verify.show()
			},
			async getCode(data){
				console.log('data-------',data);
				let that = this;
				await registerVerify({
						phone: that.userInfo.phone,
						type: 'reset', 
						key: that.key,
						captchaType: 'blockPuzzle',
						captchaVerification: data.captchaVerification,
					})
					.then(res => {
						that.$util.Tips({
							title: res.msg
						});
						that.sendCode();
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						});
					});
			},
			/**
			 * H5登录 修改密码
			 * 
			 */
			editPwd: function(e) {
				let that = this,
					password = e.detail.value.password,
					qr_password = e.detail.value.qr_password,
					captcha = e.detail.value.captcha;
				if (!password) return that.$util.Tips({
					title: '请输入新密码'
				});
				if (qr_password != password) return that.$util.Tips({
					title: '两次输入的密码不一致！'
				});
				if (!captcha) return that.$util.Tips({
					title: '请输入验证码'
				});
				phoneRegisterReset({
					account: that.userInfo.phone,
					captcha: captcha,
					password: password
				}).then(res => {
					uni.reLaunch({
						url: '/pages/users/login/index'
					})
					// return that.$util.Tips({
					// 	title: res.msg
					// }, {
					// 	tab: 3,
					// 	url: 1
					// });
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
				});
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #fff !important;
	}

	.ChangePassword .phone {
		font-size: 32rpx;
		font-weight: bold;
		text-align: center;
		margin-top: 55rpx;
	}

	.ChangePassword .list {
		width: 580rpx;
		margin: 53rpx auto 0 auto;
	}

	.ChangePassword .list .item {
		width: 100%;
		height: 110rpx;
		border-bottom: 2rpx solid #f0f0f0;
	}

	.ChangePassword .list .item input {
		width: 100%;
		height: 100%;
		font-size: 32rpx;
	}

	.ChangePassword .list .item .placeholder {
		color: #b9b9bc;
	}

	.ChangePassword .list .item input.codeIput {
		width: 340rpx;
	}

	.ChangePassword .list .item .code {
		font-size: 32rpx;
		background-color: #fff;
	}

	.ChangePassword .list .item .code.on {
		color: #b9b9bc !important;
	}

	.ChangePassword .confirmBnt {
		font-size: 32rpx;
		width: 580rpx;
		height: 90rpx;
		border-radius: 45rpx;
		color: #fff;
		margin: 92rpx auto 0 auto;
		text-align: center;
		line-height: 90rpx;
	}
</style>