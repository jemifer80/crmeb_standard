<template>
	<view :style="colorStyle">
		<view class='order-submission'>
			<view class="allAddress" :style="store_self_mention ? '':'padding-top:10rpx'" v-if="!product_type">
				<view class='address acea-row row-between-wrapper' @tap='onAddress' v-if='shippingType == 0'>
					<view class='addressCon' v-if="addressInfo.real_name">
						<view class='name'>{{addressInfo.real_name}}
							<text class='phone'>{{addressInfo.phone}}</text>
						</view>
						<view class="line1">
							<text class='default font-num'
								v-if="addressInfo.is_default">[默认]</text>{{addressInfo.province}}{{addressInfo.city}}{{addressInfo.district}}{{addressInfo.detail}}
						</view>
						<!-- <view class='setaddress'>设置收货地址</view> -->
					</view>
					<view class='addressCon' v-else>
						<view class='setaddress'>设置收货地址</view>
					</view>
					<view class='iconfont icon-jiantou'></view>
				</view>
				<view class='line'>
					<image src='/static/images/line.jpg'></image>
				</view>
			</view>
			<view class="orderGoods" :class="product_type?'on':''">
				<view class='total'>共{{resData.num}}件商品</view>
				<view class='goodWrapper'>
					<view class='item acea-row row-between-wrapper' @click="jumpCon(cartInfo.product_id)">
						<view class='pictrue'>
							<image :src='cartInfo.image'></image>
						</view>
						<view class='text'>
							<view class='acea-row row-between-wrapper'>
								<view class='name line1'>{{cartInfo.store_name}}</view>
								<view class='num'>x {{resData.num}}</view>
							</view>
							<view class='attr line1' v-if="cartInfo.attrInfo">{{cartInfo.attrInfo.suk}}
							</view>
							<view class='money font-color' v-if="cartInfo.attrInfo">
								<text v-if="parseFloat(cartInfo.attrInfo.integral)">{{cartInfo.attrInfo.integral}}积分</text>
								<text v-if="parseFloat(cartInfo.attrInfo.integral) && parseFloat(cartInfo.attrInfo.price)">+</text>
								<text v-if="parseFloat(cartInfo.attrInfo.price)">{{cartInfo.attrInfo.price}}元</text>
							</view>
							<view class='money font-color' v-else>
								<text v-if="parseFloat(cartInfo.integral)">{{cartInfo.integral}}积分</text>
								<text v-if="parseFloat(cartInfo.integral) && parseFloat(cartInfo.price)">+</text>
								<text v-if="parseFloat(cartInfo.price)">{{cartInfo.price}}元</text>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class='wrapper' v-if="confirm.length">
				<view class='item acea-row row-between-wrapper' v-for="(item,index) in confirm" :key="index">
					<view class="name">
					<span class="asterisk" v-if="item.status">*</span>	
					{{ item.title }}</view>
					<!-- text -->
					<view v-if="item.label=='text'" class="discount">
						<input type="text" :placeholder="'请填写'+item.title" placeholder-class="placeholder" v-model="item.value" />
					</view>
					<!-- number -->
					<view v-if="item.label=='number'" class="discount">
						<input type="number" :placeholder="'请填写'+item.title" placeholder-class="placeholder" v-model="item.value" />
					</view>
					<!-- email -->
					<view v-if="item.label=='email'" class="discount">
						<input type="text" :placeholder="'请填写'+item.title" placeholder-class="placeholder" v-model="item.value" />
					</view>
					<!-- data -->
					<view v-if="item.label=='data'" class="discount">
						<picker mode="date" :value="item.value" @change="bindDateChange($event,index)">
							<view class="acea-row row-between-wrapper">
								<view v-if="item.value == ''">请选择{{item.title}}</view>
								<view v-else>{{item.value}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
					</view>
					<!-- time -->
					<view v-if="item.label=='time'" class="discount">
						<picker mode="time" :value="item.value"
							@change="bindTimeChange($event,index)" :placeholder="'请填写'+item.title" >
							<view class="acea-row row-between-wrapper">
								<view v-if="item.value == ''">请选择{{item.title}}</view>
								<view v-else>{{item.value}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
					</view>
					<!-- id -->
					<view v-if="item.label=='id'" class="discount">
						<input type="idcard" :placeholder="'请填写'+item.title" placeholder-class="placeholder" v-model="item.value" />
					</view>
					<!-- phone -->
					<view v-if="item.label=='phone'" class="discount">
						<input type="tel" :placeholder="'请填写'+item.title" placeholder-class="placeholder" v-model="item.value" />
					</view>
					<!-- img -->
					<view v-if="item.label=='img'" class="confirmImg">
						<view class='upload acea-row row-middle'>
							<view class='pictrue' v-for="(items,indexs) in item.value" :key="indexs">
								<image :src='items' mode="aspectFill"></image>
								<view class='iconfont icon-guanbi1 font-num' @tap='DelPic(index,indexs)'></view>
							</view>
							<view class='pictrue acea-row row-center-wrapper row-column' @tap='uploadpic(index)'
								v-if="item.value.length < 8">
								<text class='iconfont icon-icon25201'></text>
								<view>上传图片</view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class='wrapper'>
				<view class='item acea-row row-between-wrapper'>
					<view>可用积分</view>
					<view class='discount'>{{resData.integral}}
					</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>抵扣积分</view>
					<view class='discount'>{{resData.total_integral}}
					</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>快递费用</view>
					<view class='discount'>免运费
					</view>
				</view>
				<view class='item' v-if="textareaStatus">
					<view>备注信息</view>
					<textarea placeholder-class='placeholder' placeholder="请添加备注（150字以内）" v-if="!coupon.coupon"
						@input='bindHideKeyboard' :value="mark" :maxlength="150" name="mark">
						</textarea>
				</view>
			</view>
			<view style='height:120rpx;'></view>
			<view class='footer acea-row row-between-wrapper'>
				<view>合计：
					<text class='font-color' v-if="parseFloat(resData.total_integral)">{{resData.total_integral || 0}}积分</text>
					<text class="font-color" v-if="parseFloat(resData.total_integral) && parseFloat(resData.total_price)">+</text>
					<text class="font-color" v-if="parseFloat(resData.total_price)">{{resData.total_price || 0}}元</text>
				</view>
				<view class='settlement' style='z-index:100' @tap="goPay">立即兑换</view>
			</view>
		</view>
		<view class="alipaysubmit" v-html="formContent"></view>
		<couponListWindow :coupon='coupon' @ChangCouponsClone="ChangCouponsClone" :openType='openType' :cartId='cartId'
			@ChangCoupons="ChangCoupons"></couponListWindow>
		<addressWindow ref="addressWindow" @changeTextareaStatus="changeTextareaStatus" :news='news' :address='address'
			:pagesUrl="pagesUrl" @OnChangeAddress="OnChangeAddress" @changeClose="changeClose"></addressWindow>
		<home v-show="!invShow && navigation"></home>
		<payment :payMode="cartArr" :pay_close="pay_close" :isCall="true" :totalPrice="totalPrice.toString()"
			@changePayType="changePayType" @onChangeFun="onChangeFun"></payment>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>
<script>
	import {
		openPaySubscribe
	} from '@/utils/SubscribeMessage.js';
	import {
		integralOrderConfirm,
		integralOrderCreate,
	} from '@/api/activity.js';
	import {
		getAddressDefault,
		getAddressDetail,
		invoiceList,
		invoiceOrder
	} from '@/api/user.js';
	import {
		storeListApi
	} from '@/api/store.js';
	import {
		CACHE_LONGITUDE,
		CACHE_LATITUDE
	} from '@/config/cache.js';
	import couponListWindow from '@/components/couponListWindow';
	import addressWindow from '@/components/addressWindow';
	import orderGoods from '@/components/orderGoods';
	import home from '@/components/home';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import payment from '@/components/payment';
	import colors from "@/mixins/color";
	export default {
		components: {
			payment,
			couponListWindow,
			addressWindow,
			orderGoods,
			home
		},
		mixins: [colors],
		data() {
			return {
				confirm:[],
				textareaStatus: true,
				//支付方式
				cartArr: [{
						"name": "微信支付",
						"icon": "icon-weixin2",
						value: 'weixin',
						title: '使用微信快捷支付',
						payStatus: 1,
					},
					{
						"name": "支付宝支付",
						"icon": "icon-zhifubao",
						value: 'alipay',
						title: '使用线上支付宝支付',
						payStatus: 1,
					},
					{
						"name": "余额支付",
						"icon": "icon-yuezhifu",
						value: 'yue',
						title: '可用余额:',
						payStatus: 1,
					},
					// {
					// 	"name": "线下支付",
					// 	"icon": "icon-yuezhifu1",
					// 	value: 'offline',
					// 	title: '选择线下付款方式',
					// 	payStatus: 2,
					// }
				],
				formContent: '',
				payType: 'weixin', //支付方式
				openType: 1, //优惠券打开方式 1=使用
				active: 0, //支付方式切换
				coupon: {
					coupon: false,
					list: [],
					statusTile: '立即使用'
				}, //优惠券组件
				address: {
					address: false
				}, //地址组件
				addressInfo: {}, //地址信息
				pinkId: 0, //拼团id
				addressId: 0, //地址id
				couponId: 0, //优惠券id
				cartId: '', //购物车id
				BargainId: 0,
				combinationId: 0,
				seckillId: 0,
				userInfo: {}, //用户信息
				mark: '', //备注信息
				couponTitle: '请选择', //优惠券
				coupon_price: 0, //优惠券抵扣金额
				useIntegral: false, //是否使用积分
				integral_price: 0, //积分抵扣金额
				integral: 0,
				ChangePrice: 0, //使用积分抵扣变动后的金额
				formIds: [], //收集formid
				status: 0,
				is_address: false,
				toPay: false, //修复进入支付时页面隐藏从新刷新页面
				shippingType: 0,
				system_store: {},
				storePostage: 0,
				contacts: '',
				contactsTel: '',
				mydata: {},
				storeList: [],
				store_self_mention: 0,
				cartInfo: {},
				priceGroup: {},
				animated: false,
				totalPrice: 0,
				integralRatio: "0",
				pagesUrl: "",
				orderKey: "",
				// usableCoupon: {},
				offlinePostage: "",
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				from: '',
				news: 1,
				invTitle: '不开发票',
				special_invoice: false,
				invoice_func: false,
				header_type: '',
				invShow: false,
				invList: [],
				invChecked: '',
				urlQuery: '',
				pay_close: false,
				resData: {},
				product_type:1,
				newImg: []
			};
		},
		computed: mapGetters(['isLogin']),
		onLoad: function(options) {
			// #ifdef H5
			this.from = this.$wechat.isWeixin() ? 'weixin' : 'weixinh5'
			// #endif
			// #ifdef MP
			this.from = 'routine'
			// #endif
			if (!options.unique) return this.$util.Tips({
				title: '请选择要购买的商品'
			}, {
				tab: 3,
				url: 1
			});
			this.unique = options.unique
			this.num = options.num
			this.couponId = options.couponId || 0;
			this.pinkId = options.pinkId ? parseInt(options.pinkId) : 0;
			this.addressId = options.addressId || 0;
			this.cartId = options.cartId;
			this.is_address = options.is_address ? true : false;
			this.news = !options.new || options.new === '0' ? 0 : 1;
			this.invChecked = options.invoice_id || '';
			this.header_type = options.header_type || '1';
			this.couponTitle = options.couponTitle || '请选择'
			// #ifndef APP-PLUS
			this.textareaStatus = true;
			// #endif
			if (this.isLogin && this.toPay == false) {
				this.getFun();
			} else {
				//#ifndef MP
				toLogin();
				//#endif
				//#ifdef MP
				this.isShowAuth = true;
				//#endif
			}
		},
		/**
		 * 生命周期函数--监听页面显示
		 */
		onShow: function() {
			uni.removeStorageSync('form_type_cart');
			let _this = this
			uni.$on("handClick", res => {
				if (res) {
					_this.system_store = res.address
				}
				// 清除监听
				uni.$off('handClick');
			})
		},
		methods: {
			getFun(){
				this.getaddressInfo();
				this.getConfirm();
				this.$nextTick(function() {
					this.$refs.addressWindow.getAddressList();
				})
			},
			onLoadFun(){
				this.getFun();
				this.isShowAuth = false
			},
			// 授权关闭
			authColse: function(e) {
			  this.isShowAuth = e
			},
			jumpCon: function(id) {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${id}`
				})
			},
			/**
			 * 删除图片
			 * 
			 */
			DelPic: function(index, indexs) {
				let that = this,
					pic = this.confirm[index].value;
				that.confirm[index].value.splice(indexs, 1);
				that.$set(that.confirm[index], 'value', that.confirm[index].value);
			},
			
			/**
			 * 上传文件
			 * 
			 */
			uploadpic: function(index) {
				let that = this;
				this.$util.uploadImageOne('upload/image', function(res) {
					that.newImg.push(res.data.url);
					that.$set(that.confirm[index], 'value', that.newImg);
				});
			},
			getInvoiceList() {
				uni.showLoading({
					title: '正在加载…'
				})
				invoiceList().then(res => {
					uni.hideLoading();
					this.invList = res.data.map(item => {
						item.id = item.id.toString();
						return item;
					});
					const result = this.invList.find(item => item.id == this.invChecked);
					if (result) {
						let name = '';
						name += result.header_type === 1 ? '个人' : '企业';
						name += result.type === 1 ? '普通' : '专用';
						name += '发票';
						this.invTitle = name;
					}
				}).catch(err => {
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			},
			/**
			 * 事件回调
			 *
			 */
			onChangeFun: function(e) {
				let opt = e;
				let action = opt.action || null;
				let value = opt.value != undefined ? opt.value : null;
				action && this[action] && this[action](value);
			},
			payClose: function() {
				this.pay_close = false;
			},
			payCheck(type) {
				this.payType = type;
				this.SubOrder();
			},
			goPay(){
				let that = this;
				if (!that.addressId && !that.product_type) {
					return that.$util.Tips({
						title: '请选择收货地址'
					});
				}
				if (parseFloat(that.resData.integral) < parseFloat(that.resData.total_integral))
					return that.$util.Tips({
						title: '积分不足！'
					});
				if(parseFloat(this.resData.total_price)){
					this.pay_close = true;
				}
				if(!parseFloat(this.resData.total_price)){
					this.SubOrder();
				}
			},
			SubOrder() {
				let that = this
				if(parseFloat(this.resData.total_price)){
					if (!that.payType) return that.$util.Tips({
						title: '请选择支付方式'
					});
				}
				for (var i = 0; i < that.confirm.length; i++) {
					let data = that.confirm[i]
					if (data.status || (data.label !== 'img' && data.value && data.value.trim())) {
						if (data.label === 'text' || data.label === 'data' || data.label === 'time') {
							if (!data.value || (data.value && !data.value.trim())) {
								return that.$util.Tips({
									title: `请填写${data.title}`
								});
							}
						}
						if (data.label === 'number') {
							if (data.value <= 0) {
								return that.$util.Tips({
									title: `请填写大于0的${data.title}`
								});
							}
						}
						if (data.label === 'email') {
							if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(data.value)) {
								return that.$util.Tips({
									title: `请填写正确的${data.title}`
								});
							}
						}
						if (data.label === 'phone') {
							if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(data.value)) {
								return that.$util.Tips({
									title: `请填写正确的${data.title}`
								});
							}
						}
						
						if (data.label === 'id') {
							if (!/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i.test(data.value)) {
								return that.$util.Tips({
									title: `请填写正确的${data.title}`
								});
							}
						}
						
						if (data.label === 'img') {
							if (!data.value.length) {
								return that.$util.Tips({
									title: `请上传${data.title}`
								});
							}
						}
					}
				}	
				let data = {
					custom_form: that.confirm,
					addressId: that.addressId,
					mark: that.mark,
					unique: this.cartInfo.unique,
					num: this.resData.num,
					'from': that.from,
					payType: that.payType,
					// #ifdef H5
					quitUrl: location.protocol + '//' + location.hostname +
						'/pages/points_mall/integral_order_status'
					// #endif
					// #ifdef APP-PLUS
					quitUrl: '/pages/points_mall/integral_order_details'
					// #endif
				}
				if(parseFloat(this.resData.total_price)){
					if (data.payType == 'yue' && parseFloat(that.resData.now_money) < parseFloat(that.totalPrice))
						return that.$util.Tips({
							title: '余额不足！'
						});
					uni.showLoading({
						title: '订单支付中'
					});
					// #ifdef MP
					openPaySubscribe().then(() => {
						that.payment(data);
					});
					// #endif
					// #ifndef MP
					that.payment(data);
					// #endif
				}else{
					integralOrderCreate(data).then(res => {
						uni.redirectTo({
							url: `/pages/points_mall/integral_order_status?order_id=${res.data.result.orderId}`
						})
					}).catch(err => {
						uni.hideLoading();
						return that.$util.Tips({
							title: err
						});
					});
				}
			},
			payment: function(data) {
				let that = this;
				integralOrderCreate(data).then(res => {
					let status = res.data.status,
						orderId = res.data.result.orderId,
						jsConfig = res.data.result.jsConfig,
						goPages = '/pages/points_mall/integral_order_status?order_id=' + orderId
					switch (status) {
						case 'ORDER_EXIST':
						case 'EXTEND_ORDER':
						case 'PAY_ERROR':
							uni.hideLoading();
							return that.$util.Tips({
								title: res.msg
							}, {
								tab: 5,
								url: goPages
							});
							break;
						case 'SUCCESS':
							uni.hideLoading();
							if (that.BargainId || that.combinationId || that.pinkId || that.seckillId || that
								.discountId)
								return that.$util.Tips({
									title: res.msg,
									icon: 'success'
								}, {
									tab: 4,
									url: goPages
								});
							return that.$util.Tips({
								title: res.msg,
								icon: 'success'
							}, {
								tab: 5,
								url: goPages
							});
							break;
						case 'WECHAT_PAY':
							that.toPay = true;
							// #ifdef MP
							/* that.toPay = true; */
							uni.requestPayment({
								timeStamp: jsConfig.timestamp,
								nonceStr: jsConfig.nonceStr,
								package: jsConfig.package,
								signType: jsConfig.signType,
								paySign: jsConfig.paySign,
								success: function(res) {
									uni.hideLoading();
									if (that.BargainId || that.combinationId || that.pinkId || that
										.seckillId || that.discountId)
										return that.$util.Tips({
											title: '支付成功',
											icon: 'success'
										}, {
											tab: 4,
											url: goPages
										});
									return that.$util.Tips({
										title: '支付成功',
										icon: 'success'
									}, {
										tab: 5,
										url: goPages
									});
								},
								fail: function(e) {
									uni.hideLoading();
									return that.$util.Tips({
										title: '取消支付'
									}, {
										tab: 5,
										url: goPages + '&status=2'
									});
								},
								complete: function(e) {
									uni.hideLoading();
									//关闭当前页面跳转至订单状态
									if (res.errMsg == 'requestPayment:cancel') return that.$util
										.Tips({
											title: '取消支付'
										}, {
											tab: 5,
											url: goPages + '&status=2'
										});
								},
							})
							// #endif
							// #ifdef H5
							this.$wechat.pay(res.data.result.jsConfig).then(res => {
								return that.$util.Tips({
									title: '支付成功',
									icon: 'success'
								}, {
									tab: 5,
									url: goPages
								});
							}).catch(res => {
								if (!this.$wechat.isWeixin()) {
									uni.redirectTo({
										url: goPages +
											'&msg=支付失败&status=2'
									})
								}
								if (res.errMsg == 'chooseWXPay:cancel') return that.$util.Tips({
									title: '取消支付'
								}, {
									tab: 5,
									url: goPages + '&status=2'
								});
							})
							// #endif
							// #ifdef APP-PLUS
							uni.requestPayment({
								provider: 'wxpay',
								orderInfo: jsConfig,
								success: (e) => {
									let url = goPages;
									uni.showToast({
										title: "支付成功"
									})
									setTimeout(res => {
										uni.redirectTo({
											url: url
										})
									}, 2000)
								},
								fail: (e) => {
									let url = '/pages/points_mall/integral_order_status?order_id=' + orderId +
										'&msg=支付失败';
									uni.showModal({
										content: "支付失败",
										showCancel: false,
										success: function(res) {
											if (res.confirm) {
												uni.redirectTo({
													url: url
												})
											} else if (res.cancel) {
												console.log('用户点击取消');
											}
										}
									})
								},
								complete: () => {
									uni.hideLoading();
								},
							});
							// #endif
							break;
						case 'PAY_DEFICIENCY':
							uni.hideLoading();
							//余额不足
							return that.$util.Tips({
								title: res.msg
							}, {
								tab: 5,
								url: goPages + '&status=1'
							});
							break;
						case "WECHAT_H5_PAY": 
							uni.hideLoading();
							that.$util.Tips({
								title: '订单创建成功!'
							}, {
								tab: 4,
								url: goPages + '&status=0'
							});
							setTimeout(() => {
								location.href = res.data.result.jsConfig.mweb_url;
							}, 2000);
							break;
			
						case 'ALIPAY_PAY':
							//#ifdef H5
							if (this.from === 'weixin') {
								uni.redirectTo({
									url: `/pages/users/alipay_invoke/index?id=${orderId}&pay_key=${res.data.result.pay_key}`
								});
							} else {
								uni.hideLoading();
								that.formContent = res.data.result.jsConfig;
								that.$nextTick(() => {
									document.getElementById('alipaysubmit').submit();
								})
							}
							//#endif
							// #ifdef MP
							uni.navigateTo({
								url: `/pages/users/alipay_invoke/index?id=${orderId}&link=${jsConfig.qrCode}`
							});
							// #endif
							// #ifdef APP-PLUS
							uni.requestPayment({
								provider: 'alipay',
								orderInfo: jsConfig,
								success: (e) => {
									uni.showToast({
										title: "支付成功"
									})
									let url = '/pages/points_mall/integral_order_status?order_id=' + orderId +
										'&msg=支付成功';
									setTimeout(res => {
										uni.redirectTo({
											url: url
										})
									}, 2000)
			
								},
								fail: (e) => {
									let url = '/pages/points_mall/integral_order_status?order_id=' + orderId +
										'&msg=支付失败';
									uni.showModal({
										content: "支付失败",
										showCancel: false,
										success: function(res) {
											if (res.confirm) {
												uni.redirectTo({
													url: url
												})
											} else if (res.cancel) {
												console.log('用户点击取消');
											}
										}
									})
								},
								complete: () => {
									uni.hideLoading();
								},
							});
							// #endif
							break;
					}
				}).catch(err => {
					uni.hideLoading();
					return that.$util.Tips({
						title: err
					});
				});
			},
			// 关闭地址弹窗；
			changeClose: function() {
				this.$set(this.address, 'address', false);
			},
			changePayType(type) {
				this.payType = type
			},
			computedPrice: function() {
				let shippingType = this.shippingType;
				postOrderComputed(this.orderKey, {
					addressId: this.addressId,
					useIntegral: this.useIntegral ? 1 : 0,
					couponId: this.couponId,
					shipping_type: parseInt(shippingType) + 1,
					payType: this.payType
				}).then(res => {
					let result = res.data.result;
					if (result) {
						this.totalPrice = result.pay_price;
						this.integral_price = result.deduction_price;
						this.coupon_price = result.coupon_price;
						this.integral = this.useIntegral ? result.SurplusIntegral : this.userInfo.integral;
						this.$set(this.priceGroup, 'storePostage', shippingType == 1 ? 0 : result.pay_postage);
						this.$set(this.priceGroup, 'storePostageDiscount', result.storePostageDiscount);
					}
				})
			},
			ChangCouponsClone: function() {
				this.$set(this.coupon, 'coupon', false);
			},
			changeTextareaStatus: function() {
				for (let i = 0, len = this.coupon.list.length; i < len; i++) {
					this.coupon.list[i].use_title = '';
					this.coupon.list[i].is_use = 0;
				}
				this.textareaStatus = true;
				this.status = 0;
				this.$set(this.coupon, 'list', this.coupon.list);
			},
			/**
			 * 选择地址后改变事件
			 * @param object e
			 */
			OnChangeAddress: function(e) {
				this.textareaStatus = true;
				this.addressId = e;
				this.address.address = false;
				this.getaddressInfo();
			},
			bindHideKeyboard: function(e) {
				this.mark = e.detail.value;
			},
			/**
			 * 获取当前订单详细信息
			 * 
			 */
			getConfirm: function() {
				let that = this;
				// return;
				integralOrderConfirm({
					unique: this.unique,
					num: this.num
				}).then(res => {
					that.$set(that, 'confirm', res.data.custom_form);
					that.$set(that, 'product_type', parseInt(res.data.productInfo.product_type));
					that.$set(that, 'resData', res.data);
					that.$set(that, 'cartInfo', res.data.productInfo);
					that.$set(that, 'totalPrice', res.data.total_price);
					//微信支付是否开启
					that.cartArr[0].payStatus = res.data.pay_weixin_open || 0
					//支付宝是否开启
					that.cartArr[1].payStatus = res.data.ali_pay_status || 0;
					//#ifdef MP
					that.cartArr[1].payStatus = 0;
					//#endif
					that.cartArr[2].number = res.data.now_money;
					that.cartArr[2].payStatus = res.data.yue_pay_status == 1 ? res.data.yue_pay_status : 0
					if (res.data.offline_pay_status == 2) {
						that.cartArr[3].payStatus = 0
					} else {
						that.cartArr[3].payStatus = 1
					}
				}).catch(err => {
					return this.$util.Tips({
						title: err
					});
				});
			},
			bindDateChange: function(e, index) {
				this.confirm[index].value = e.target.value
			},
			bindTimeChange: function(e, index) {
				this.confirm[index].value = e.target.value
			},
			/*
			 * 提取砍价和拼团id
			 */
			getBargainId: function() {
				let that = this;
				let cartINfo = that.cartInfo;
				let BargainId = 0;
				let combinationId = 0;
				cartINfo.forEach(function(value, index, cartINfo) {
					BargainId = cartINfo[index].bargain_id,
						combinationId = cartINfo[index].combination_id
				})
				that.$set(that, 'BargainId', parseInt(BargainId));
				that.$set(that, 'combinationId', parseInt(combinationId));
				if (that.cartArr.length == 3 && (BargainId || combinationId || that.seckillId)) {
					that.cartArr[2].payStatus = 0;
					that.$set(that, 'cartArr', that.cartArr);
				}
			},
			/*
			 * 获取默认收货地址或者获取某条地址信息
			 */
			getaddressInfo: function() {
				let that = this;
				if (that.addressId) {
					getAddressDetail(that.addressId).then(res => {
						res.data.is_default = parseInt(res.data.is_default);
						that.addressInfo = res.data || {};
						that.addressId = res.data.id || 0;
						that.address.addressId = res.data.id || 0;
					})
				} else {
					getAddressDefault().then(res => {
						res.data.is_default = parseInt(res.data.is_default);
						that.addressInfo = res.data || {};
						that.addressId = res.data.id || 0;
						that.address.addressId = res.data.id || 0;
					})
				}
			},
			couponTap: function() {
				this.coupon.coupon = true;
				this.coupon.list.forEach((item, index) => {
					if (item.id == this.couponId) {
						item.is_use = 1
					} else {
						item.is_use = 0
					}
				})
				this.$set(this.coupon, 'list', this.coupon.list);
			},
			car: function() {
				let that = this;
				that.animated = false;
			},
			onAddress: function() {
				let that = this;
				that.textareaStatus = false;
				that.address.address = true;
				that.pagesUrl = `/pages/points_mall/user_address?unique=${this.unique}&num=${this.num}`
			},
			clickTextArea() {
				this.$nextTick(() => {
					this.$refs.getFocus.focus()
				})
			},
		}
	}
</script>

<style lang="scss" scoped>
	/deep/uni-checkbox[disabled] .uni-checkbox-input {
		background-color: #eee;
	}
	
	.confirmImg{
		width: 100%;
	}
	
	.confirmImg .upload {
		padding-bottom: 36rpx;
	}
	
	.confirmImg .upload .pictrue {
		margin: 22rpx 23rpx 0 0;
		width: 156rpx;
		height: 156rpx;
		position: relative;
		font-size: 24rpx;
		color: #bbb;
	}
	
	.confirmImg .upload .pictrue:nth-of-type(4n) {
		margin-right: 0;
	}
	
	.confirmImg .upload .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 3rpx;
	}
	
	.confirmImg .upload .pictrue .icon-guanbi1 {
		position: absolute;
		font-size: 45rpx;
		top: -10rpx;
		right: -10rpx;
	}
	
	.confirmImg .upload .pictrue .icon-icon25201 {
		color: #bfbfbf;
		font-size: 50rpx;
	}
	
	.confirmImg .upload .pictrue:nth-last-child(1) {
		border: 1rpx solid #ddd;
		box-sizing: border-box;
	}
	
	.goodWrapper{
		padding: 0 30rpx;
		.item{
			padding: 20rpx 0;
		}
	}

	.alipaysubmit {
		display: none;
	}

	.order-submission .line {
		width: 100%;
		height: 3rpx;
	}

	.order-submission .line image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.order-submission .address {
		padding: 28rpx 30rpx;
		background-color: #fff;
		box-sizing: border-box;
	}

	.order-submission .address .addressCon {
		width: 610rpx;
		font-size: 26rpx;
		color: #666;
	}

	.order-submission .address .addressCon .name {
		font-size: 30rpx;
		color: #282828;
		font-weight: bold;
		margin-bottom: 10rpx;
	}

	.order-submission .address .addressCon .name .phone {
		margin-left: 50rpx;
	}

	.order-submission .address .addressCon .default {
		margin-right: 12rpx;
	}

	.order-submission .address .addressCon .setaddress {
		color: #333;
		font-size: 28rpx;
	}

	.order-submission .address .iconfont {
		font-size: 35rpx;
		color: #707070;
	}

	.order-submission .allAddress {
		width: 100%;
		background: linear-gradient(to bottom, var(--view-theme) 0%, #f5f5f5 100%);
		padding-top: 100rpx;
	}

	.order-submission .allAddress .nav {
		width: 710rpx;
		margin: 0 auto;
	}

	.order-submission .allAddress .nav .item {
		width: 355rpx;
	}

	.order-submission .allAddress .nav .item.on {
		position: relative;
		width: 250rpx;
	}

	.order-submission .allAddress .nav .item.on::before {
		position: absolute;
		bottom: 0;
		content: "快递配送";
		font-size: 28rpx;
		display: block;
		height: 0;
		width: 336rpx;
		border-width: 0 20rpx 80rpx 0;
		border-style: none solid solid;
		border-color: transparent transparent #fff;
		z-index: 2;
		border-radius: 7rpx 30rpx 0 0;
		text-align: center;
		line-height: 80rpx;
	}

	.order-submission .allAddress .nav .item:nth-of-type(2).on::before {
		content: "到店自提";
		border-width: 0 0 80rpx 20rpx;
		border-radius: 30rpx 7rpx 0 0;
	}

	.order-submission .allAddress .nav .item.on2 {
		position: relative;
	}

	.order-submission .allAddress .nav .item.on2::before {
		position: absolute;
		bottom: 0;
		content: "到店自提";
		font-size: 28rpx;
		display: block;
		height: 0;
		width: 400rpx;
		border-width: 0 0 60rpx 60rpx;
		border-style: none solid solid;
		border-color: transparent transparent #f7c1bd;
		border-radius: 40rpx 6rpx 0 0;
		text-align: center;
		line-height: 60rpx;
	}

	.order-submission .allAddress .nav .item:nth-of-type(1).on2::before {
		content: "快递配送";
		border-width: 0 60rpx 60rpx 0;
		border-radius: 6rpx 40rpx 0 0;
	}

	.order-submission .allAddress .address {
		width: 710rpx;
		height: 150rpx;
		margin: 0 auto;
	}

	.order-submission .allAddress .line {
		width: 710rpx;
		margin: 0 auto;
	}

	.order-submission .wrapper .item .discount .placeholder {
		color: #ccc;
	}

	.placeholder-textarea {
		position: relative;

		.placeholder {
			position: absolute;
			color: #ccc;
			top: 26rpx;
			left: 30rpx;
		}
	}

	.order-submission .wrapper {
		background-color: #fff;
		margin-top: 13rpx;
	}
	
	.order-submission .wrapper .item .name{
		position: relative;
	}
	
	.order-submission .wrapper .item .asterisk{
		position: absolute;
		color:red;
		left:-15rpx
	}

	.order-submission .wrapper .item {
		padding: 27rpx 30rpx;
		font-size: 30rpx;
		color: #282828;
		border-bottom: 1px solid #f0f0f0;
	}

	.order-submission .wrapper .item .discount {
		font-size: 30rpx;
		color: #999;
	}

	.order-submission .wrapper .item .discount input {
		text-align: end;
	}

	.order-submission .wrapper .item .discount .iconfont {
		color: #515151;
		font-size: 30rpx;
		margin-left: 15rpx;
	}

	.order-submission .wrapper .item .discount .num {
		font-size: 32rpx;
		margin-right: 20rpx;
	}

	.order-submission .wrapper .item .shipping {
		font-size: 30rpx;
		color: #999;
		position: relative;
		padding-right: 58rpx;
	}

	.order-submission .wrapper .item .shipping .iconfont {
		font-size: 35rpx;
		color: #707070;
		position: absolute;
		right: 0;
		top: 50%;
		transform: translateY(-50%);
		margin-left: 30rpx;
	}

	.order-submission .wrapper .item textarea {
		background-color: #f9f9f9;
		width: 690rpx;
		height: 140rpx;
		border-radius: 3rpx;
		margin-top: 30rpx;
		padding: 25rpx 28rpx;
		box-sizing: border-box;
	}

	.order-submission .wrapper .item .placeholder {
		color: #ccc;
	}

	.order-submission .wrapper .item .list {
		margin-top: 35rpx;
	}


	.order-submission .moneyList {
		margin-top: 12rpx;
		background-color: #fff;
		padding: 30rpx;
	}

	.order-submission .moneyList .item {
		font-size: 28rpx;
		color: #282828;
	}

	.order-submission .moneyList .item~.item {
		margin-top: 20rpx;
	}

	.order-submission .moneyList .item .money {
		color: #868686;
	}

	.order-submission .footer {
		width: 100%;
		height: 100rpx;
		background-color: #fff;
		padding: 0 30rpx;
		font-size: 28rpx;
		color: #333;
		box-sizing: border-box;
		position: fixed;
		bottom: 0;
		left: 0;
		height: calc(100rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		padding-bottom: constant(safe-area-inset-bottom); ///兼容 IOS<11.2/
		padding-bottom: env(safe-area-inset-bottom); ///兼容 IOS>11.2/
	}

	.order-submission .footer .settlement {
		font-size: 30rpx;
		color: #fff;
		width: 240rpx;
		height: 70rpx;
		background-color: var(--view-theme);
		border-radius: 50rpx;
		text-align: center;
		line-height: 70rpx;
	}

	.footer .transparent {
		opacity: 0
	}

	.orderGoods {
		background-color: #fff;
		margin-top: 12rpx;
		&.on{
			margin-top: 0;
		}
	}

	.orderGoods .total {
		width: 100%;
		height: 86rpx;
		padding: 0 30rpx;
		border-bottom: 2rpx solid #f0f0f0;
		font-size: 30rpx;
		color: #282828;
		line-height: 86rpx;
		box-sizing: border-box;
	}

	.pictrue image {
		width: 130rpx;
		height: 130rpx;
	}
</style>
