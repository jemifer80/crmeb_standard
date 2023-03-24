<template>
	<view>
		<form @submit="submitSub" :style="colorStyle">
			<view class="payment-top acea-row row-column row-center-wrapper">
				<span class="name">我的余额</span>
				<view class="pic">
					￥<span class="pic-font">{{ userinfo.now_money || 0 }}</span>
				</view>
			</view>
			<view class="payment">
				<view class="nav acea-row row-around row-middle">
					<view class="item" :class="active==index?'on':''" v-for="(item,index) in navRecharge" :key="index" @click="navRecharges(index)">{{item}}</view>
				</view>
				<view class='tip picList' v-if='!active' >
					<view class="pic-box pic-box-color acea-row row-center-wrapper row-column" :class="activePic == index ? 'pic-box-color-active' : ''"
					 v-for="(item, index) in picList" :key="index" @click="picCharge(index, item)" v-if="item.price">
						<view class="pic-number-pic">
							{{ item.price }}<span class="pic-number"> 元</span>
						</view>
						<view class="pic-number">赠送：{{ item.give_money }} 元</view>
					</view>
					<view class="pic-box pic-box-color acea-row row-center-wrapper" :class="activePic == picList.length ? 'pic-box-color-active' : ''"
					 @click="picCharge(picList.length)">
						<input type="number" placeholder="其他" v-model="money" class="pic-box-money pic-number-pic" :class="activePic == picList.length ? 'pic-box-color-active' : ''" />
					</view>
					<view class="tips-box">
						<view class="tips mt-30">注意事项：</view>
						<view class="tips-samll" v-for="item in rechargeAttention" :key="item">
							{{ item }}
						</view>
					</view>

				</view>
				<view class="tip" v-else>
					<view class='input'><text>￥</text><input @input='inputNum' :maxlength="moneyMaxLeng" placeholder="0.00" type='number' placeholder-class='placeholder' :value="number" name="number"></input></view>
					<view class="tips-title">
						<view style="font-weight: bold; font-size: 26rpx;">提示：</view>
						<view style="margin-top: 10rpx;">当前可转入佣金为 <text class='font-color'>￥{{userinfo.commissionCount || 0}}</text>,冻结佣金为<text class='font-color'>￥{{userinfo.broken_commission}}</text></view>
					</view>
					<view class="tips-box">
						<view class="tips mt-30">注意事项：</view>
						<view class="tips-samll" v-for="item in rechargeAttention" :key="item">
							{{ item }}
						</view>
					</view>
				</view>
				<button class='but bg-color' formType="submit" > {{active ? '立即转入': '立即充值' }}</button>
			</view>
		</form>
		<payment :payMode="payMode" :pay_close="pay_close" :is-call="true" @changePayType="changePayType" @onChangeFun="onChangeFun"
			:order_id="pay_order_id" :totalPrice="totalPrice"></payment>
		<home v-if="navigation"></home>
		<view v-show="false" v-html="formContent"></view>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getUserInfo,
		rechargeRoutine,
		rechargeWechat,
		getRechargeApi,
		memberCardCreate
	} from '@/api/user.js';
	import payment from '@/components/payment';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		orderOfflinePayType
	} from '@/api/order.js';
	import {
		mapGetters
	} from "vuex";
	import home from '@/components/home';
	import colors from "@/mixins/color";
	import {
		openPaySubscribe
	} from '@/utils/SubscribeMessage.js';
	export default {
		components: {
			home,
			payment
		},
		mixins:[colors],
		data() {
			let that = this;
			return {
				now_money: 0,
				navRecharge: ['账户充值', '佣金转入'],
				active: 0,
				number: '',
				userinfo: {},
				placeholder: "0.00",
				from: '',
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				picList: [],
				activePic: 0,
				money: "",
				numberPic:'',
				rechar_id:0,
				password: '',
				goodsList: [],
				pay_order_id: '',
				payMode: [{
						name: '微信支付',
						icon: 'icon-weixinzhifu',
						value: 'weixin',
						title: '微信快捷支付',
						payStatus: true
					}
					// #ifdef H5 ||APP-PLUS
					,
					{
						name: '支付宝支付',
						icon: 'icon-zhifubao',
						value: 'alipay',
						title: '支付宝支付',
						payStatus: true
					}
					// #endif
				],
				pay_close: false,
				payType: '',
				totalPrice: '0',
				formContent: '',
				// #ifdef H5
				isWeixin: this.$wechat.isWeixin(),
				// #endif
				type: '',
				rechargeAttention:[],
				moneyMaxLeng:8
			};
		},
		computed: mapGetters(['isLogin']),
		watch:{
			isLogin:{
				handler:function(newV,oldV){
					if(newV){
						//#ifndef MP
						this.getOrderPayType();
						this.getUserInfo();
						this.getRecharge();
						//#endif
					}
				},
				deep:true
			}
		},
		onLoad(options) {
			// #ifdef H5
			this.from = this.$wechat.isWeixin() ? "weixinh5" : "alipay"
			// #endif
			if (this.isLogin) {
				this.getOrderPayType();
				this.getUserInfo();
				this.getRecharge();
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
			onLunch() {
				this.getOrderPayType();
				this.getUserInfo();
				this.getRecharge();
			},
			inputNum: function(e) {
				let val = e.detail.value;
				let dot = val.indexOf('.');
				if(dot>-1){
					this.moneyMaxLeng = dot+3;
				}else{
					this.moneyMaxLeng = 8
				}
			},
			/**
			 * 选择金额
			 */
			picCharge(idx, item) {
				this.activePic = idx;
				if (item === undefined) {
					this.rechar_id = 0;
					this.numberPic = "";
				} else {
					this.money = "";
					this.rechar_id = item.id;
					this.numberPic = item.price;
				}
			},

			/**
			 * 充值额度选择
			 */
			getRecharge() {
				getRechargeApi()
					.then(res => {
						this.picList = res.data.recharge_quota;
						if (this.picList[0]) {
							this.rechar_id = this.picList[0].id;
							this.numberPic = this.picList[0].price;
						}
						this.rechargeAttention = res.data.recharge_attention || [];
					})
					.catch(res => {
						this.$util.Tips({
							title: res
						})
					});
			},
			getOrderPayType() {
				orderOfflinePayType().then(res => {
					const {
						ali_pay_status,
						pay_weixin_open
					} = res.data;
					this.payMode[0].payStatus = !!pay_weixin_open;
					// #ifdef APP-PLUS || H5
					this.payMode[1].payStatus = !!ali_pay_status;
					// #endif
				}).catch(err => {
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			},

			onLoadFun: function() {
				this.getOrderPayType();
				this.getUserInfo();
				this.getRecharge();
				this.isShowAuth = false;
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			navRecharges: function(index) {
				this.active = index;
			},
			/**
			 * 获取用户信息
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					that.$set(that, 'userinfo', res.data);
				})
			},
			changePayType: function(e) {
				this.payType = e == 'alipay' ? 'alipay' : 'weixinh5'
				console.log('eee*//',this.payType);
			},
			onChangeFun: function(e) {
				let opt = e;
				let action = opt.action || null;
				let value = opt.value != undefined ? opt.value : null;
				action && this[action] && this[action](value);
			},
			payClose: function() {
				this.pay_close = false;
			},
			payCheck: function(type) {
				this.createMemberCard(type);
			},
			createMemberCard(type) {
				uni.showLoading({
					title: '正在加载…'
				});
				// #ifdef MP
				openPaySubscribe().then(() => {
					uni.showLoading({
						title: '正在支付',
					})
					let that = this
					let money = parseFloat(this.money);
					if( this.rechar_id == 0){
						if(Number.isNaN(money)){
							return that.$util.Tips({title: '充值金额必须为数字'});
						}
						if(money <= 0){
							return that.$util.Tips({title: '充值金额不能为0'});
						}
					}else{
						// money = this.numberPic
					}
					rechargeRoutine({
						price: parseFloat(this.totalPrice),
						type: 0,
						rechar_id: this.rechar_id
					}).then(res => {
						uni.hideLoading();
						let data = res.data;
						let mp_pay_name=''
						if(uni.requestOrderPayment){
							mp_pay_name='requestOrderPayment'
						}else{
							mp_pay_name='requestPayment'
						}
						uni[mp_pay_name]({
							// #ifdef MP
							timeStamp: data.timestamp,
							nonceStr: data.nonceStr,
							package: data.package,
							signType: data.signType,
							paySign: data.paySign,
							// #endif
							// #ifdef APP-PLUS
							provider: 'wxpay',
							orderInfo: data,
							// #endif
							success: function(res) {
								that.$set(that, 'userinfo.now_money', that.$util.$h.Add(value, that.userinfo.now_money));
								return that.$util.Tips({
									title: '支付成功',
									icon: 'success'
								}, {
									tab: 5,
									url: '/pages/users/user_money/index'
								});
							},
							fail: function() {
								return that.$util.Tips({
									title: '支付失败'
								});
							},
							complete: function(res) {
								if (res.errMsg == 'requestPayment:cancel') return that.$util.Tips({
									title: '取消支付'
								});
							}
						})
					}).catch(err => {
						uni.hideLoading();
						return that.$util.Tips({
							title: err
						})
					});
				});
				// #endif
				// #ifndef MP
					let that = this
					this.pay_close = true;
					this.totalPrice = this.rechar_id == 0 ? this.money : this.numberPic;
					rechargeWechat({
						price: parseFloat(this.totalPrice),
						// #ifdef H5
						from: this.payType == '' ? 'weixinh5' : this.payType,
						// #endif
						// #ifdef APP-PLUS
						from: 'weixin',
						// #endif
						rechar_id: that.rechar_id
					}).then(res => {
						let data = res.data;
						if (data.type == "weixinh5") {
							uni.showToast({
								title: data.msg,
								success() {
									location.href = data.data.mweb_url;
								}
							});
						}else if(data.type == "alipay") {
							uni.hideLoading();
							if (this.$wechat.isWeixin()) {
								uni.navigateTo({
									url: `/pages/users/alipay_invoke/index?id=${data.data.result.order_id}&pay_key=${data.data.result.pay_key}&from=member`
								});
							} else {
								uni.hideLoading();
								this.formContent = data.data;
								this.$nextTick(() => {
									document.getElementById('alipaysubmit').submit();
								});
							}
						
						}else if(data.type == "weixin") {
							// #ifdef H5
							this.$wechat.pay(data.data).then(res => {
								this.$util.Tips({
									title: '支付成功',
									icon: 'success'
								}, {
									tab: 5,
									url: '/pages/users/user_money/index'
								});
							}).catch(err => {
								if (err.errMsg == 'chooseWXPay:cancel') {
									uni.showToast({
										title: '取消支付',
										icon: 'none'
									});
								}
							});
							// #endif
							// #ifdef APP-PLUS
							uni.requestPayment({
								provider: 'wxpay',
								orderInfo: data.data,
								success: (e) => {

									uni.showToast({
										title: "支付成功"
									})
									setTimeout(res => {
										uni.navigateBack()
									}, 2000)
								},
								fail: (e) => {
									uni.showToast({
										title: "支付失败",
										icon: 'none',
										duration: 2000
									})
								},
								complete: () => {
									uni.hideLoading();
								},
							});
							// #endif
						}
					}).catch(err=>{
						uni.hideLoading();
						return that.$util.Tips({
							title: err
						})
					})
				// #endif
			},
			/*
			 * 用户充值
			 */
			submitSub: function(e) {
				let that = this
				let value = e.detail.value.number;
				// 转入余额
				if (that.active) {
					if (parseFloat(value) < 0 || parseFloat(value) == NaN || value == undefined || value == "") {
						return that.$util.Tips({
							title: '请输入金额'
						});
					}
					uni.showModal({
						title: '转入余额',
						content: '转入余额后无法再次转出，确认是否转入余额',
						success(res) {
							if (res.confirm) {
								// #ifdef MP || APP-PLUS
								rechargeRoutine({
									price: parseFloat(value),
									type: 1
								})
								// #endif
								// #ifdef H5
								rechargeWechat({
										price: parseFloat(value),
										from: that.from,
										type: 1
									})
									// #endif
									.then(res => {
										// that.$set(that, 'userinfo.now_money', that.$util.$h.Add(value, that.userinfo.now_money))
										return that.$util.Tips({
											title: '转入成功',
											icon: 'success'
										}, {
											tab: 5,
											url: '/pages/users/user_money/index'
										});
									}).catch(err => {
										return that.$util.Tips({
											title: err
										})
									});
							} else if (res.cancel) {
								return that.$util.Tips({
									title: '已取消'
								});
							}
						},
					})
				} else {
					// #ifdef MP
					this.pay_close = true;
					this.totalPrice = this.rechar_id == 0 ? this.money : this.numberPic;
					// #endif
					// #ifndef MP
						this.pay_close = true;
						this.totalPrice = this.rechar_id == 0 ? parseFloat(this.money) : parseFloat(this.numberPic);
					// #endif
				}
			}
		}
	}
</script>

<style lang="scss">
	page {
		width: 100%;
		height: 100%;
		background-color: #fff;
	}
	.bgcolor{
		background-color: var(--view-theme)
	}
	.payment {
		position: relative;
		width: 100%;
		background-color: #fff;
		border-radius: 10rpx;
		padding-top: 25rpx;
		border-top-right-radius: 39rpx;
		border-top-left-radius: 39rpx;
	}

	.payment .nav {
		height: 75rpx;
		line-height: 75rpx;
		padding: 0 100rpx;
	}

	.payment .nav .item {
		font-size: 30rpx;
		color: #333;
	}

	.payment .nav .item.on {
		font-weight: bold;
		border-bottom: 4rpx solid var(--view-theme);
	}

	.payment .input {
		display: flex;
		align-items: center;
		justify-content: center;
		border-bottom: 1px dashed #dddddd;
		margin: 60rpx auto 0 auto;
		padding-bottom: 20rpx;
		font-size: 56rpx;
		color: #333333;
		flex-wrap: nowrap;
		
	}

	.payment .input text {
		padding-left: 106rpx;
	}

	.payment .input input {
		padding-right: 106rpx;
		width: 300rpx;
		height: 94rpx;
		text-align: center;
		font-size: 70rpx;
	}

	.payment .placeholder {
		color: #d0d0d0;
		height: 100%;
		line-height: 94rpx;
	}

	.payment .tip {
		font-size: 26rpx;
		color: #888888;
		padding: 0 30rpx;
		margin-top: 25rpx;
	}

	.payment .but {
		color: #fff;
		font-size: 30rpx;
		width: 700rpx;
		height: 86rpx;
		border-radius: 50rpx;
		margin: 46rpx auto 0 auto;
		line-height: 86rpx;
	}

	.payment-top {
		width: 100%;
		height: 350rpx;
		background-color: var(--view-theme);

		.name {
			font-size: 26rpx;
			color: rgba(255, 255, 255, 0.8);
			margin-top: -38rpx;
			margin-bottom: 30rpx;
		}

		.pic {
			font-size: 32rpx;
			color: #fff;
		}

		.pic-font {
			font-size: 78rpx;
			color: #fff;
		}
	}

	.picList {
		display: flex;
		flex-wrap: wrap;
		margin: 30rpx 0;

		.pic-box {
			width: 32%;
			height: auto;
			border-radius: 20rpx;
			margin-top: 21rpx;
			padding: 20rpx 0;
			margin-right: 12rpx;

			&:nth-child(3n) {
				margin-right: 0;
			}
		}

		.pic-box-color {
			background-color: #f4f4f4;
			color: #656565;
		}

		.pic-number {
			font-size: 22rpx;
		}

		.pic-number-pic {
			font-size: 38rpx;
			margin-right: 10rpx;
			text-align: center;
		}

		.pic-box-color-active {
			background-color: var(--view-theme) !important;
			color: #fff !important;
		}
	}
	.tips-box{
		.tips {
		  font-size: 28rpx;
		  color: #333333;
		  font-weight: 800;
		  margin-bottom: 14rpx;
			margin-top: 20rpx;
		}
		.tips-samll {
		  font-size: 24rpx;
		  color: #333333;
		  margin-bottom: 14rpx;
		}
		.tip-box {
		  margin-top: 30rpx;
		}
	}
	.tips-title{
		margin-top: 20rpx;
		font-size: 24rpx;
		color: #333;
	}
</style>
