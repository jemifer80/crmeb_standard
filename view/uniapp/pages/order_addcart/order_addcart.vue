<template>
  <!-- 购物车模块 -->
	<view :style="colorStyle">
		<!-- #ifdef MP -->
		<view class="sys-head" :style="{height:sysHeight + 'px'}"></view>
		<view class="sys-title" :style="{top:sysHeight + 'px'}">购物车</view>
		<!-- #endif -->
		<view class='shoppingCart copy-data' >
			<!-- #ifdef MP -->
			<view class='labelNav acea-row row-around row-middle' :style="{top:(sysHeight+40) + 'px'}">
			<!-- #endif -->
			<!-- #ifdef APP-PLUS || H5 -->
			<view class='labelNav acea-row row-around row-middle'>
			<!-- #endif -->
				<view class='item'><text class='iconfont icon-xuanzhong'></text>100%正品保证</view>
				<view class='item'><text class='iconfont icon-xuanzhong'></text>所有商品精挑细选</view>
				<view class='item'><text class='iconfont icon-xuanzhong'></text>售后无忧</view>
			</view>
			<!-- #ifdef MP -->
			<view class='nav acea-row row-between-wrapper' :style="{top:(sysHeight+78) + 'px'}">
			<!-- #endif -->
			<!-- #ifdef APP-PLUS || H5 -->
			<view class='nav acea-row row-between-wrapper' style="top:38px;">
			<!-- #endif -->
				<view><text class='num font-num'>{{cartNum || 0}}</text>购物数量</view>
				<view v-if="cartList.valid.length > 0 || cartList.invalid.length > 0"
					class='administrate acea-row row-center-wrapper' @click='manage'>{{ footerswitch ? '管理' : '取消'}}
				</view>
			</view>
			<view v-if="cartList.valid.length > 0 || cartList.invalid.length > 0">
			<!-- #ifdef MP -->
			<view :style="{height: (sysHeight+130) + 'px'}"></view>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS || H5 -->
			<view style="height:90px;"></view>
			<!-- #endif -->
				<view class='list'>
					<checkbox-group @change="checkboxChange">
					<view class="title acea-row row-between-wrapper" v-if="cartList.valid.length && Object.prototype.toString.call(discountInfo.coupon) === '[object Object]'">
						<view style="width:530rpx;"><text class="label">优惠券</text>最高可优惠{{discountInfo.deduction.coupon_price}}元</view>
						<view class="font-color" @click="couponTap">点击领用<text class="iconfont icon-jinru2"></text></view>
					</view>	
					<view v-for="(j,jindex) in cartList.valid" :key="jindex">
						<view class="title acea-row row-between-wrapper" v-for="(p,proIndex) in j.promotions"
							:key="proIndex">
							<view style="width:530rpx;"><text class="label">{{p.title}}</text>{{p.desc}}<text v-if="p.differ_threshold>0">,还差{{p.differ_threshold}}{{p.threshold_type==1?'元':'件'}}</text></view>
							<view class="font-color" @click="goCollect(p)">{{p.is_valid == 1?'去逛逛':'去凑单'}}<text
									class="iconfont icon-jinru2"></text></view>
						</view>
						<!-- <checkbox-group @change="checkboxChange"> -->
							<block v-for="(item,index) in j.valid" :key="index">
								<view class='item acea-row row-between-wrapper'>
									<!-- #ifndef MP -->
									<checkbox :value="(item.id).toString()" :checked="item.checked"
										:disabled="(!item.attrStatus || item.is_gift?true:false) && footerswitch" />
									<!-- <checkbox :value="(item.id).toString()" :checked="item.checked" :disabled="item.attrStatus?false:true" /> -->
									<!-- #endif -->
									<!-- #ifdef MP -->
									<checkbox :value="item.id" :checked="item.checked"
										:disabled="(!item.attrStatus || item.is_gift?true:false) && footerswitch" />
									<!-- #endif -->
									<view class='picTxt acea-row row-between-wrapper'>
										<navigator class='pictrue' :url='"/pages/goods_details/index?id="+item.product_id' hover-class='none'>
											<image v-if="item.productInfo.attrInfo" :src='item.productInfo.attrInfo.image'>
											</image>
											<image v-else :src='item.productInfo.image'></image>
										</navigator>
										<view class='text'>
											<view class='line1' :class="item.attrStatus?'':'reColor'">
												{{item.productInfo.store_name}}
											</view>
											<view class='infor line1' v-if="item.productInfo.attrInfo && item.productInfo.spec_type && !item.is_gift" @click.stop="cartAttr(item)">
												<text class="name line1">属性：{{item.productInfo.attrInfo.suk}}</text>
												<text class="iconfont icon-xiangxia"></text>
											</view>
											<view class='infor line1' v-else>
												<text class="name line1">属性：{{item.productInfo.attrInfo.suk}}</text>
											</view>
											<view class='money' v-if="item.attrStatus && !item.is_gift">￥{{item.sum_price}}</view>
											<view class="isGift" v-if="item.is_gift">赠品</view>
											<view class="reElection acea-row row-between-wrapper" v-if="!item.attrStatus">
												<view class="titles">请重新选择商品规格</view>
												<view class="reBnt cart-color acea-row row-center-wrapper"
													@click.stop="reElection(item)">重选</view>
											</view>
										</view>
										<view class='carnum acea-row row-center-wrapper' v-if="item.attrStatus && !item.is_gift">
											<view class="reduce" :class="item.numSub ? 'on' : ''"
												@click.stop='subCart(jindex,index)'>-</view>
											<view class='num'>{{(item.productInfo.limit_num>0 && item.cart_num>=item.productInfo.limit_num)?item.productInfo.limit_num:item.cart_num}}</view>
											<!-- <view class="num">
												<input type="number" v-model="item.cart_num" @click.stop @input="iptCartNum(index)" @blur="blurInput(index)"/>
											</view> -->
											<view class="plus" :class="(item.numAdd || (item.productInfo.limit_num>0 && item.cart_num>=item.productInfo.limit_num)) ? 'on' : ''"
												@click.stop='addCart(jindex,index,item)'>+</view>
										</view>
									</view>
								  <view class="evaluate" v-if="item.attrStatus && !item.is_gift && item.sum_price != item.truePrice">预估到手价<text class="num">￥{{item.truePrice}}</text></view>
								</view>
							</block>
						<!-- </checkbox-group> -->
					</view>
					</checkbox-group>
				</view>
				<view class='invalidGoods' v-if="cartList.invalid.length > 0">
					<view class='goodsNav acea-row row-between-wrapper'>
						<view @click='goodsOpen'><text class='iconfont'
								:class='goodsHidden==true?"icon-xiangxia":"icon-xiangshang"'></text>失效商品</view>
						<view class='del' @click='unsetCart'><text class='iconfont icon-shanchu1'></text>清空</view>
					</view>
					<view class='goodsList' :hidden='goodsHidden'>
						<block v-for="(item,index) in cartList.invalid" :key='index'>
							<view class='item acea-row row-between-wrapper'>
								<view class='invalid'>失效</view>
								<view class='pictrue'>
									<image v-if="item.productInfo.attrInfo" :src='item.productInfo.attrInfo.image'>
									</image>
									<image v-else :src='item.productInfo.image'></image>
								</view>
								<view class='text acea-row row-column-between'>
									<view class='line1 name'>{{item.productInfo.store_name}}</view>
									<view class='infor line1' v-if="item.productInfo.attrInfo">
										属性：{{item.productInfo.attrInfo.suk}}</view>
									<view class='acea-row row-between-wrapper'>
										<!-- <view>￥{{item.truePrice}}</view> -->
										<view class='end'>{{item.invalid_desc || '该商品已失效'}}</view>
									</view>
								</view>
							</view>
						</block>
					</view>
				</view>
				<!-- <view class='loadingicon acea-row row-center-wrapper' v-if="cartList.invalid.length && loadend">
					<text class='loading iconfont icon-jiazai'
						:hidden='loadingInvalid==false'></text>{{loadTitleInvalid}}
				</view> -->
			</view>
			<view class='noCart' v-if="cartList.valid.length == 0 && cartList.invalid.length == 0 && loadend">
				<view class='pictrue'>
					<image :src="imgHost + '/statics/images/no-thing.png'"></image>
					<view>暂无商品，去添加点什么吧</view>
				</view>
        <!-- 热门推荐显示 -->
				<recommend :hostProduct='hostProduct'></recommend>
			</view>
			<view v-if="cartList.valid.length == 0 && cartList.invalid.length == 0 && loadend" style='height:30rpx;color: #F5F5F5;'></view>
			<view v-else style='height:190rpx;color: #F5F5F5;'></view>
			<view class="tips acea-row row-middle" :class="isFooter?'':'on'" v-if="isTips"><text class="iconfont icon-tishi"></text>部分活动不能叠加，系统已自动为您计算最优惠的价格</view>
      <!-- 订单结算 -->
			<view class='footer acea-row row-between-wrapper' :class="isFooter?'':'on'"
				v-if="cartList.valid.length > 0">
				<view>
					<checkbox-group @change="checkboxAllChange">
						<checkbox value="all" :checked="!!isAllSelect" />
						<text class='checkAll'>全选</text>
					</checkbox-group>
				</view>
				<view class='money acea-row row-middle' v-if="footerswitch==true && discountInfo.deduction">
					<view class="left">
						实付：
						<text class="font-color">￥{{selectValue.length?discountInfo.deduction.pay_price:0}}</text>
						<view class="acea-row row-right">
							<view class="detailed" @click="discountTap" v-if="(Object.prototype.toString.call(discountInfo.coupon) === '[object Object]' || discountInfo.deduction.first_order_price || discountInfo.deduction.promotions_price) && selectValue.length">优惠明细</view>
						</view>
					</view>
					<form @submit="subOrder">
						<button v-if="selectValue.length && !valiSubmittedState.disabled" class='placeOrder bg-color' formType="submit">{{Object.prototype.toString.call(discountInfo.coupon) === "[object Array]" || discountInfo.coupon.used?'去':'领券'}}结算({{selectValue.length}})</button>
						<button v-else class='placeOrder on' formType="submit">{{Object.prototype.toString.call(discountInfo.coupon) === "[object Array]" || discountInfo.coupon.used?'去':'领券'}}结算({{selectValue.length}})</button>
					</form>
				</view>
				<view class='button acea-row row-middle' v-else>
					<form @submit="subCollect">
						<button class='bnt cart-color' formType="submit">收藏</button>
					</form>
					<form @submit="subDel">
						<button class='bnt' formType="submit">删除</button>
					</form>
				</view>
			</view>
		</view>
    <!-- 产品属性显示 -->
		<productWindow :attr="attr" :isShow='1' :iSplus='1' :iScart='1' :storeInfo="storeInfo" :is_vip="is_vip" @myevent="onMyEvent" @ChangeAttr="ChangeAttr"
			@ChangeCartNum="ChangeCartNum" @attrVal="attrVal" @iptCartNum="iptCartNum" @goCat="reGoCat"
			id='product-window'></productWindow>
      <!-- 优惠明细显示 -->
		<cartDiscount :discountInfo="discountInfo" @myevent="myDiscount"></cartDiscount>
		<view class="uni-p-b-98"></view>
		<pageFooter @newDataStatus="newDataStatus"></pageFooter>
    <!-- 优惠券列表弹框显示 -->
		<couponListWindow :coupon="coupon" v-if="coupon" @ChangCouponsClone="ChangCouponsClone"
			@ChangCouponsUseState="ChangCouponsUseState" @tabCouponType="tabCouponType">
		</couponListWindow>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	// #ifdef APP-PLUS || MP
	let sysHeight = uni.getSystemInfoSync().statusBarHeight;
	// #endif
	// #ifdef H5
	let sysHeight = 0
	// #endif
	import {
		getCartList,
		getCartCounts,
		changeCartNum,
		cartDel,
		getResetCart,
		cartCompute
	} from '@/api/order.js';
	import {
		setCouponReceive,
		getCoupons
	} from '@/api/api.js';
	import {
		getProductHot,
		collectAll,
		getProductDetail
	} from '@/api/store.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import recommend from '@/components/recommend';
	import productWindow from '@/components/productWindow';
	import cartDiscount from '@/components/cartDiscount';
	import couponListWindow from '@/components/couponListWindow';
	import pageFooter from '@/components/pageFooter/index.vue'
	import colors from "@/mixins/color";
	import orderConfirmMixins from '@/mixins/orderConfirmMixins';
	import {HTTP_REQUEST_URL} from '@/config/app';
	import {Debounce} from '@/utils/validate.js'
	export default {
		components: {
			couponListWindow,
			pageFooter,
			recommend,
			productWindow,
			cartDiscount
		},
		mixins: [colors, orderConfirmMixins],
		data() {
			return {
				isFooter:false,
				isTips:false,
				//属性是否打开
				coupon: {
					coupon: false,
					type: -1,
					list: [],
					count: [],
					goFrom:1
				},
				discountInfo:{
					discount:false,
					deduction:{},
					coupon:{}
				},
				goodsHidden: true,
				footerswitch: true,
				hostProduct: [],
				cartList: {
					valid: [],
					invalid: []
				},
				isAllSelect: false, //全选
				selectValue: [], //选中的数据
				selectCountPrice: 0.00,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10,
				loading: false,
				loadend: false,
				loadTitle: '没有更多内容啦~', //提示语
				page: 1,
				limit: 20,
				loadingInvalid: false,
				loadendInvalid: false,
				loadTitleInvalid: '加载更多', //提示语
				pageInvalid: 1,
				limitInvalid: 20,
				attr: {
					cartAttr: false,
					productAttr: [],
					productSelect: {}
				},
				productValue: [], //系统属性
				storeInfo: {},
				attrValue: '', //已选属性
				attrTxt: '请选择', //属性页面提示
				cartId: 0,
				product_id: 0,
				sysHeight: sysHeight,
				footerSee: false,
				isCart: 0,
				imgHost:HTTP_REQUEST_URL,
				is_vip: 0, //是否是会员
			};
		},
		computed: mapGetters(['isLogin','cartNum']),
		onLoad: function(options) {
			this.hotPage = 1;
			this.hostProduct = [],
			this.hotScroll = false,
			this.getHostProduct();
		},
		onShow: function() {
			uni.setStorageSync('form_type_cart', 1);
			uni.pageScrollTo({
				duration:0,
				scrollTop:0
			})
			if (this.isLogin == true){
				this.resetData();
			}else{
				// #ifdef H5 || APP-PLUS
				toLogin()
				// #endif
				// #ifdef MP
				this.isShowAuth = true;
				// #endif
			}
		},
		methods: {
			onLoadFun(){
				this.resetData();
			},
			resetData(){
				this.loadend = false;
				this.page = 1;
				this.cartList.valid = [];
				// 1:表示只有在onShow里面调用;
				this.getCartList();
				this.loadendInvalid = false;
				this.pageInvalid = 1;
				this.cartList.invalid = [];
				// this.getCartNum();
				this.goodsHidden = true;
				this.footerswitch = true;
				this.hotLimit = 10;
				this.isAllSelect = false; //全选
				this.selectValue = []; //选中的数据
				this.selectCountPrice = 0.00;
				this.isShowAuth = false;
			},
			newDataStatus(val){
				this.isFooter = val;
			},
			tabCouponType: function(type) {
				this.$set(this.coupon, 'type', type);
				this.getCouponList(type);
			},
			ChangCouponsUseState(index) {
				let that = this;
				that.coupon.list[index].is_use = true;
				that.$set(that.coupon, 'list', that.coupon.list);
				that.$set(that.coupon, 'coupon', false);
			},
			ChangCouponsClone: function() {
				this.$set(this.coupon, 'coupon', false);
			},
			/**
			 * 获取优惠券
			 *
			 */
			getCouponList(type) {
				let that = this,
					obj = {
						page: 1,
						limit: 20,
						product_id: that.id
					};
				if (type !== undefined || type !== null) {
					obj.type = type;
				}
				getCoupons(obj).then(res => {
					that.$set(that.coupon, 'count', res.data.count);
					if (type === undefined || type === null) {
						let count = [...that.coupon.count],
							indexs = '';
						let index = count.findIndex(item => item);
						let delCount = that.coupon.count,
							newDelCount = [];
						let countIndex = 0;
						delCount.forEach((item, index) => {
							if (item === 0) {
								countIndex = index;
							} else {
								newDelCount.push(item)
							}
						});
						if (newDelCount.length == 3) {
							indexs = 2;
						} else if (newDelCount.length == 2) {
							if (countIndex === 2) {
								indexs = 1;
							} else {
								indexs = 2;
							}
						} else {
							indexs = delCount.findIndex(item => item === count[index]);
						}
						that.$set(that.coupon, 'type', indexs);
						that.getCouponList(indexs);
					} else {
						that.$set(that.coupon, 'list', res.data.list);
					}
				});
			},
			/**
			 * 打开优惠券插件
			 */
			couponTap: function() {
				let that = this;
				that.getCouponList();
				that.$set(that.coupon, 'coupon', true);
			},
			goCollect(item){
				uni.navigateTo({
					url: `/pages/goods/goods_list/index?sid=0&title=默认&promotions_type=${item.promotions_type}&promotions_id=${item.id}`
				})
			},
			myDiscount(){
				this.discountInfo.discount = false;
			},
			discountTap(){
				this.coupon.coupon=false;
				this.discountInfo.discount = !this.discountInfo.discount;
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e;
			},
			// 修改购物车
			reGoCat: function() {
				let that = this,
					productSelect = that.productValue[this.attrValue];
				//如果有属性,没有选择,提示用户选择
				if (
					that.attr.productAttr.length &&
					productSelect === undefined
				){
					return that.$util.Tips({
						title: "产品库存不足，请选择其它"
					});
				}	
				let q = {
					id: that.cartId,
					product_id: that.product_id,
					num: that.attr.productSelect.cart_num,
					unique: that.attr.productSelect !== undefined ?
						that.attr.productSelect.unique : ""
				};
				getResetCart(q)
					.then(function(res) {
						that.attr.cartAttr = false;
						that.$util.Tips({
							title: "添加购物车成功",
							success: () => {
								that.loadend = false;
								that.page = 1;
								that.cartList.valid = [];
								that.getCartList();
								that.getCartNum();
							}
						});
					})
					.catch(res => {
						return that.$util.Tips({
							title: res.msg
						});
					});
			},
			onMyEvent: function() {
				this.$set(this.attr, 'cartAttr', false);
			},
			// 点击切换属性
			cartAttr(item){
				this.isCart = 1;
				this.getGoodsDetails(item);
			},
			reElection: function(item) {
				this.getGoodsDetails(item)
			},
			/**
			 * 获取产品详情
			 * 
			 */
			getGoodsDetails: function(item) {
				uni.showLoading({
					title: '加载中',
					mask: true
				});
				let that = this;
				that.cartId = item.id;
				that.product_id = item.product_id;
				console.log(item.id,item.product_id);
				getProductDetail(item.product_id).then(res => {
					uni.hideLoading();
					that.attr.cartAttr = true;
					let storeInfo = res.data.storeInfo;
					that.$set(that, 'storeInfo', storeInfo);
					that.$set(that, 'is_vip', res.data.storeInfo.is_vip);
					that.$set(that.attr, 'productAttr', res.data.productAttr);
					that.$set(that, 'productValue', res.data.productValue);
					that.DefaultSelect();
				}).catch(err => {
					uni.hideLoading();
				})
			},
			/**
			 * 属性变动赋值
			 * 
			 */
			ChangeAttr: function(res) {
				let productSelect = this.productValue[res];
				if (productSelect && productSelect.stock > 0) {
					this.$set(this.attr.productSelect, "image", productSelect.image);
					this.$set(this.attr.productSelect, "price", productSelect.price);
					this.$set(this.attr.productSelect, "stock", productSelect.stock);
					this.$set(this.attr.productSelect, "unique", productSelect.unique);
					this.$set(this.attr.productSelect, "cart_num", 1);
					this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
					this.$set(this, "attrValue", res);
					this.$set(this, "attrTxt", "已选择");
				} else {
					this.$set(this.attr.productSelect, "image", this.storeInfo.image);
					this.$set(this.attr.productSelect, "price", this.storeInfo.price);
					this.$set(this.attr.productSelect, "stock", 0);
					this.$set(this.attr.productSelect, "unique", "");
					this.$set(this.attr.productSelect, "cart_num", 0);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, "attrValue", "");
					this.$set(this, "attrTxt", "请选择");
				}
			},
			/**
			 * 默认选中属性
			 * 
			 */
			DefaultSelect: function() {
				let productAttr = this.attr.productAttr;
				let value = [],stock = 0,attrValue = [];
				for (var key in this.productValue) {
					if (this.productValue[key].stock > 0) {
						value = this.attr.productAttr.length ? key.split(",") : [];
						break;
					}
				}
				//isCart 1为触发购物车 0为商品
				if (this.isCart) {
					//购物车默认打开时，随着选中的属性改变
					// let attrValue = [];
					this.cartList.valid.forEach(j=>{
						j.valid.forEach(item=>{
							if (item.id == this.cartId) {
								attrValue = item.productInfo.attrInfo.suk.split(",");
							}
						})
					})
					let key = attrValue.join(",");
					console.log('几级',key);
					console.log(this.productValue);
					stock = this.productValue[key].stock;
					for (let i = 0; i < productAttr.length; i++) {
						this.$set(productAttr[i], "index", stock?attrValue[i]:value[i]);
					}
				} else {
					for (let i = 0; i < productAttr.length; i++) {
						this.$set(productAttr[i], "index", value[i]);
					}
				}
				
				//sort();排序函数:数字-英文-汉字；
				let productSelect = this.productValue[(this.isCart&&stock)?attrValue.join(","):value.join(",")];
				if (productSelect && productAttr.length) {
					this.$set(
						this.attr.productSelect,
						"store_name",
						this.storeInfo.store_name
					);
					this.$set(this.attr.productSelect, "image", productSelect.image);
					this.$set(this.attr.productSelect, "price", productSelect.price);
					this.$set(this.attr.productSelect, "stock", productSelect.stock);
					this.$set(this.attr.productSelect, "unique", productSelect.unique);
					this.$set(this.attr.productSelect, "cart_num", 1);
					this.$set(this, "attrValue", value.join(","));
					this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
					this.$set(this, "attrTxt", "已选择");
				} else if (!productSelect && productAttr.length) {
					this.$set(
						this.attr.productSelect,
						"store_name",
						this.storeInfo.store_name
					);
					this.$set(this.attr.productSelect, "image", this.storeInfo.image);
					this.$set(this.attr.productSelect, "price", this.storeInfo.price);
					this.$set(this.attr.productSelect, "stock", 0);
					this.$set(this.attr.productSelect, "unique", "");
					this.$set(this.attr.productSelect, "cart_num", 0);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, "attrValue", "");
					this.$set(this, "attrTxt", "请选择");
				} else if (!productSelect && !productAttr.length) {
					this.$set(
						this.attr.productSelect,
						"store_name",
						this.storeInfo.store_name
					);
					this.$set(this.attr.productSelect, "image", this.storeInfo.image);
					this.$set(this.attr.productSelect, "price", this.storeInfo.price);
					this.$set(this.attr.productSelect, "stock", this.storeInfo.stock);
					this.$set(
						this.attr.productSelect,
						"unique",
						this.storeInfo.unique || ""
					);
					this.$set(this.attr.productSelect, "cart_num", 1);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, "attrValue", "");
					this.$set(this, "attrTxt", "请选择");
				}
			},
			attrVal(val) {
				this.$set(this.attr.productAttr[val.indexw], 'index', this.attr.productAttr[val.indexw].attr_values[val
					.indexn]);
			},
			/**
			 * 购物车数量加和数量减
			 * 
			 */
			ChangeCartNum: function(changeValue) {
				//changeValue:是否 加|减
				//获取当前变动属性
				let productSelect = this.productValue[this.attrValue];
				//如果没有属性,赋值给商品默认库存
				if (productSelect === undefined && !this.attr.productAttr.length)
					productSelect = this.attr.productSelect;
				//无属性值即库存为0；不存在加减；
				if (productSelect === undefined) return;
				let stock = productSelect.stock || 0;
				let num = this.attr.productSelect;
				if (changeValue) {
					num.cart_num++;
					if (num.cart_num > stock) {
						this.$set(this.attr.productSelect, "cart_num", stock ? stock : 1);
						this.$set(this, "cart_num", stock ? stock : 1);
					}
				} else {
					num.cart_num--;
					if (num.cart_num < 1) {
						this.$set(this.attr.productSelect, "cart_num", 1);
						this.$set(this, "cart_num", 1);
					}
				}
			},
			/**
			 * 购物车手动填写
			 * 
			 */
			iptCartNum: function(e) {
				this.$set(this.attr.productSelect, 'cart_num', e);
			},
			subDel: function(event) {
				let that = this,
					selectValue = that.selectValue;
				if (selectValue.length > 0)
					cartDel(selectValue).then(res => {
						that.loadend = false;
						that.page = 1;
						that.cartList.valid = [];
						that.getCartList();
						that.getCartNum();
					});
				else
					return that.$util.Tips({
						title: '请选择产品'
					});
			},
			getSelectValueProductId: function() {
				let that = this;
				let validList = that.cartList.valid;
				let selectValue = that.selectValue;
				let productId = [];
				if (selectValue.length > 0) {
					for (let j in validList) {
						for (let index in validList[j].valid){
							if (that.inArray(validList[j].valid[index].id, selectValue)) {
								productId.push(validList[j].valid[index].product_id);
							}
						}
					}
				};
				return productId;
			},
			subCollect: function(event) {
				let that = this,
					selectValue = that.selectValue;
				if (selectValue.length > 0) {
					let selectValueProductId = that.getSelectValueProductId();
					collectAll(that.getSelectValueProductId().join(',')).then(res => {
						return that.$util.Tips({
							title: res.msg,
							icon: 'success'
						});
					}).catch(err => {
						return that.$util.Tips({
							title: err
						});
					});
				} else {
					return that.$util.Tips({
						title: '请选择产品'
					});
				}
			},
			subOrder(event) {
				let that = this,
					selectValue = that.selectValue;
				if (selectValue.length > 0) {
					if (this.valiSubmittedState.disabled) {
						const reason = this.valiSubmittedState.reason;
						return reason && that.$util.Tips({
							title: reason
						})
					}
					let coupon = this.discountInfo.coupon;
					if(Object.prototype.toString.call(coupon) === '[object Object]' && !coupon.used){
						setCouponReceive(this.discountInfo.coupon.id).then(res=>{
							uni.navigateTo({
								url: '/pages/goods/order_confirm/index?cartId=' + selectValue.join(',') + '&couponId=' + res.data.id +'&couponTitle='+ coupon.coupon_title
							});
						}).catch(err=>{
							return that.$util.Tips({
								title: err
							});
						})
					}else{
						let url = '';
						if(Object.prototype.toString.call(coupon) === '[object Array]'){
							url = '/pages/goods/order_confirm/index?cartId=' + selectValue.join(',')
						}else{
							url = '/pages/goods/order_confirm/index?cartId=' + selectValue.join(',') + '&couponId=' + coupon.used.id +'&couponTitle='+ coupon.coupon_title
						}
						uni.navigateTo({
							url: url
						});
					}
				} else {
					return that.$util.Tips({
						title: '请选择产品'
					});
				}
			},
			checkboxAllChange: function(event) {
				let value = event.detail.value;
				if (value.length > 0) {
					this.setAllSelectValue(1)
				} else {
					this.setAllSelectValue(0)
				}
			},
			setAllSelectValue: function(status) {
				let that = this;
				let selectValue = [];
				let valid = that.cartList.valid;
				if (valid.length > 0) {
					valid.forEach(j=>{
						j.valid.forEach(item=>{
							if (status) {
								if (that.footerswitch) {
									if (item.attrStatus && !item.is_gift) {
										item.checked = true;
										selectValue.push(item.id);
									} else {
										item.checked = false;
									}
								} else {
									item.checked = true;
									selectValue.push(item.id);
								}
								that.isAllSelect = true;
							} else {
								item.checked = false;
								that.isAllSelect = false;
							}
						})
					})
					that.$set(that.cartList, 'valid', valid);
					that.selectValue = selectValue;
					that.switchSelect();
				}
			},
			checkboxChange: function(event) {
				let that = this;
				let value = event.detail.value;
				let valid = that.cartList.valid;
				let arr1 = [];
				let arr2 = [];
				let arr3 = [];
				let len = 0;
				valid.forEach(j=>{
					j.valid.forEach(item=>{
						len = len + 1;
						if (that.inArray(item.id, value)) {
							if (that.footerswitch) {
								if (item.attrStatus && !item.is_gift) {
									item.checked = true;
									arr1.push(item);
								} else {
									item.checked = false;
								}
							} else {
								item.checked = true;
								arr1.push(item);
							}
						} else {
							item.checked = false;
							arr2.push(item);
						}
					})
				})
				if (that.footerswitch) {
					arr3 = arr2.filter(item => !item.attrStatus || item.is_gift);
				}
				that.$set(that.cartList, 'valid', valid);
				that.isAllSelect = len === arr1.length + arr3.length;
				that.selectValue = value;
				that.switchSelect();
			},
			inArray: function(search, array) {
				for (let i in array) {
					if (array[i] == search) {
						return true;
					}
				}
				return false;
			},
			switchSelect: function() {
				let that = this;
				let validList = that.cartList.valid;
				let selectValue = that.selectValue;
				let selectCountPrice = 0.00;
				let cartId = [];
				if (selectValue.length < 1) {
					that.selectCountPrice = selectCountPrice;
				} else {
					for (let j in validList) {
						for(let index in validList[j].valid){
							if (that.inArray(validList[j].valid[index].id, selectValue)) {
								cartId.push(validList[j].valid[index].id)
								selectCountPrice = that.$util.$h.Add(selectCountPrice, that.$util.$h.Mul(validList[j].valid[index]
									.cart_num, validList[j].valid[
										index].truePrice))
							}
						}
					}
					that.selectCountPrice = selectCountPrice;
				}
				let data = {cartId:cartId.join(',')}
				if(cartId.length){
					this.getCartCompute(data);
				}
			},
			/**
			 * 购物车手动填写
			 * 
			 */
			// iptCartNum: function(index) {
			// 	let item = this.cartList.valid[index];
			// 	if (item.cart_num) {
			// 		this.setCartNum(item.id, item.cart_num);
			// 	}
			// 	this.switchSelect();
			// },
			// blurInput: function(index) {
			// 	let item = this.cartList.valid[index];
			// 	if (!item.cart_num) {
			// 		item.cart_num = 1;
			// 		this.$set(this.cartList, 'valid', this.cartList.valid)
			// 	}
			// },
			subCart: Debounce(function(jindex,index) {
				let that = this;
				let status = false;
				let item = that.cartList.valid[jindex].valid[index];
				item.cart_num = Number(item.cart_num) - 1;
				if (item.cart_num < 1) status = true;
				if (item.cart_num <= 1) {
					item.cart_num = 1;
					item.numSub = true;
				} else {
					item.numSub = false;
					item.numAdd = false;
				}
				if (false == status) {
					that.setCartNum(item.id, item.cart_num, function(data) {
						that.cartList.valid[jindex].valid[index] = item;
						that.getCartNum();
						// that.switchSelect();
						that.loadend = false;
						that.page = 1;
						// that.cartList.valid = [];
						that.getCartList('subCart');
					});
				}
			}),
			addCart: Debounce(function(jindex,index,obj) {
				if(obj.numAdd || (obj.productInfo.limit_num>0 && obj.cart_num>=obj.productInfo.limit_num)){
					return false
				}
				let that = this;
				let item = that.cartList.valid[jindex].valid[index];
				item.cart_num = Number(item.cart_num) + 1;
				let productInfo = item.productInfo;
				if (productInfo.hasOwnProperty('attrInfo') && item.cart_num >= item.productInfo.attrInfo.stock) {
					item.cart_num = item.productInfo.attrInfo.stock;
					item.numAdd = true;
					item.numSub = false;
				} else {
					item.numAdd = false;
					item.numSub = false;
				}
				that.setCartNum(item.id, item.cart_num, function(data) {
					that.cartList.valid[jindex].valid[index] = item;
					that.getCartNum();
					// that.switchSelect();
					that.loadend = false;
					that.page = 1;
					// that.cartList.valid = [];
					that.getCartList('addCart');
				});
			}),
			setCartNum(cartId, cartNum, successCallback) {
				let that = this;
				changeCartNum(cartId, cartNum).then(res => {
					successCallback && successCallback(res.data);
				});
			},
			getCartNum: function() {
				let that = this;
				getCartCounts(0).then(res => {
					this.$store.commit('indexData/setCartNum', res.data.count > 99 ? '..' : res.data.count)
					if (res.data.count > 0) {
						wx.setTabBarBadge({
							index: 2,
							text: res.data.count + ''
						})
					} else {
						wx.hideTabBarRedDot({
							index: 2
						})
					}

				});
			},
			// 购物车计算
			getCartCompute(cartId){
				cartCompute(cartId).then(res=>{
					this.discountInfo.coupon = res.data.coupon;
					this.discountInfo.deduction = res.data.deduction;
					this.config = res.data.config;
					this.userConfirmPayment = res.data.deduction.pay_price;
				}).catch(err=>{
					this.$util.Tips({
						title: err
					})
				})
			},
			getCartList: function(handle) {
				let that = this;
				if (this.loadend) return false;
				if (this.loading) return false;
				let data = {
					page: that.page,
					limit: that.limit,
					status: 1
				}
				getCartList(data).then(res => {
					this.getInvalidList();
					// this.discountInfo.deduction = res.data.deduction;
					// this.discountInfo.coupon = res.data.coupon;
					this.isTips = false;
					let cartList = res.data.valid;
					let valid = cartList.map(x =>{
						return {
							valid : x.cart,
							promotions : x.promotions
						}
					})
					let	loadend = valid.length < that.limit;
					// let validList = that.$util.SplitArray(valid, that.cartList.valid);
					let validList = valid;
					let numSub = [{
						numSub: true
					}, {
						numSub: false
					}];
					let numAdd = [{
							numAdd: true
						}, {
							numAdd: false
						}],
						selectValue = [];
					if (validList.length > 0) {
						for (let j in validList) {
							if(validList[j].promotions.length>1){
								that.isTips = true;
							}
							for (let index in validList[j].valid){
								if (validList[j].valid[index].cart_num == 1) {
									validList[j].valid[index].numSub = true;
								} else {
									validList[j].valid[index].numSub = false;
								}
								let productInfo = validList[j].valid[index].productInfo;
								if (productInfo.hasOwnProperty('attrInfo') && validList[j].valid[index].cart_num == validList[j].valid[index].productInfo.attrInfo.stock) {
									validList[j].valid[index].numAdd = true;
								} else if (validList[j].valid[index].cart_num == validList[j].valid[index].productInfo.stock) {
									validList[j].valid[index].numAdd = true;
								} else {
									validList[j].valid[index].numAdd = false;
								}
								if(validList[j].valid[index].attrStatus && !validList[j].valid[index].is_gift){
									if (['addCart', 'subCart'].includes(handle)) {
										validList[j].valid[index].checked = false;
										for (let k = 0; k < that.selectValue.length; k++) {
											if (that.selectValue[k] == validList[j].valid[index].id) {
												validList[j].valid[index].checked = true;
												break;
											}
										}
										if (validList[j].valid[index].checked) {
											selectValue.push(validList[j].valid[index].id);
										}
									} else{
										validList[j].valid[index].checked = true;
										selectValue.push(validList[j].valid[index].id);
									}
								}else if(!this.footerswitch){
									validList[j].valid[index].checked = true;
								}else{
									validList[j].valid[index].checked = false;
								}
							}
						}
					}
					
					that.$set(that.cartList, 'valid', res.data.valid.length?validList:[]);
					that.loadend = true;
					that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
					that.page = that.page + 1;
					that.loading = false;
					// that.goodsHidden = cartList.valid.length <= 0 ? false : true;
					that.selectValue = selectValue;
					let newArr = []
					validList.forEach(j=>{
						j.valid.forEach(item=>{
							if(item.attrStatus && !item.is_gift){
								newArr.push(item)
							}
						})
					})
					that.isAllSelect = newArr.length == selectValue.length && newArr.length;
					that.switchSelect();
				}).catch(function(err) {
					that.loading = false;
					that.loadTitle = '加载失败';
					that.$util.Tips({
						title: err
					})
				})
			},
			getInvalidList: function() {
				let that = this;
				if (this.loadendInvalid) return false;
				if (this.loadingInvalid) return false;
				let data = {
					page: that.pageInvalid,
					limit: that.limitInvalid,
					status: 0
				}
				getCartList(data).then(res => {
					let cartList = res.data,
						invalid = cartList.invalid,
						loadendInvalid = invalid.length < that.limitInvalid;
					// let invalidList = that.$util.SplitArray(invalid, that.cartList.invalid);
					let invalidList = invalid;
					that.$set(that.cartList, 'invalid', invalidList);
					that.loadendInvalid = loadendInvalid;
					that.loadTitleInvalid = loadendInvalid ? '没有更多内容啦~' : '加载更多';
					that.pageInvalid = that.pageInvalid + 1;
					that.loadingInvalid = false;
				}).catch(res => {
					that.loadingInvalid = false;
					that.loadTitleInvalid = '加载更多';
				})

			},
			getHostProduct: function() {
				let that = this;
				if (that.hotScroll) return
				getProductHot(
					that.hotPage,
					that.hotLimit,
				).then(res => {
					that.hotPage++
					that.hotScroll = res.data.length < that.hotLimit
					that.hostProduct = that.hostProduct.concat(res.data)
				});
			},
			goodsOpen: function() {
				let that = this;
				that.goodsHidden = !that.goodsHidden;
			},
			manage: function() {
				let that = this;
				that.footerswitch = !that.footerswitch;
				let arr1 = [];
				let arr2 = [];
				let len = 0;
				that.cartList.valid.forEach(j=>{
					j.valid.forEach(item=>{
						len = len+1;
						if (that.footerswitch) {
							if (item.attrStatus && !item.is_gift) {
								if (item.checked) {
									arr1.push(item.id);
								}
							} else {
								item.checked = false;
								arr2.push(item);
							}
						} else {
							if (item.checked) {
								arr1.push(item.id);
							}
						}
					})
				})
				if (that.footerswitch) {
					that.isAllSelect = len === arr1.length + arr2.length;
				} else {
					that.isAllSelect = len === arr1.length;
				}
				that.selectValue = arr1;
				if(that.footerswitch){
					that.switchSelect();
				}
			},
			unsetCart: function() {
				let that = this,
					ids = [];
				for (let i = 0, len = that.cartList.invalid.length; i < len; i++) {
					ids.push(that.cartList.invalid[i].id);
				}
				cartDel(ids).then(res => {
					that.$util.Tips({
						title: '清除成功'
					});
					that.$set(that.cartList, 'invalid', []);
					that.getCartNum();
				}).catch(res => {

				});
			}
		},
		onReachBottom() {
			let that = this;
			// if (that.loadend) {
			// 	that.getInvalidList();
			// } else {
			// 	that.getCartList();
			// }
			if(that.cartList.invalid.length){
				that.getInvalidList();
			}
			if (that.cartList.valid.length == 0 && that.cartList.invalid.length == 0) {
				that.getHostProduct();
			}
		}
	}
</script>

<style scoped lang="scss">
	.sys-title {
		z-index: 10;
		position: fixed;
		height: 40px;
		line-height: 40px;
		font-size: 34rpx;
		color: #333;
		background-color: #fff;
		text-align: center;
		left:0;
		width: 100%;
	}
	.sys-head {
		background-color: #fff;
		position: fixed;
		left:0;
		top:0;
		width: 100%;
		z-index: 10;
	}
	.shoppingCart .labelNav {
		height: 38px;
		padding: 0 30rpx;
		font-size: 22rpx;
		color: #8c8c8c;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		box-sizing: border-box;
		background-color: #f5f5f5;
		z-index: 5;
	}

	.shoppingCart .labelNav .item .iconfont {
		font-size: 25rpx;
		margin-right: 10rpx;
	}

	.shoppingCart .nav {
		width: 100%;
		height: 40px;
		background-color: #fff;
		padding: 0 30rpx;
		box-sizing: border-box;
		font-size: 28rpx;
		color: #282828;
		position: fixed;
		left: 0;
		z-index: 5;
	}

	.shoppingCart .nav .num {
		margin-right: 12rpx;
	}

	.shoppingCart .nav .administrate {
		font-size: 26rpx;
		color: #666;
		width: 110rpx;
		height: 46rpx;
		border-radius: 6rpx;
	}

	.shoppingCart .noCart {
		margin-top: 171rpx;
		/* #ifdef APP-PLUS */
		margin-top: 78px;
		/* #endif */
		background-color: #fff;
		padding-top: 0.1rpx;
	}

	.shoppingCart .noCart .pictrue {
		margin: 78rpx auto 56rpx auto;
		text-align: center;
		color: #999;
	}

	.shoppingCart .noCart .pictrue image {
		width: 414rpx;
		height: 304rpx;
		display: block;
		margin: 0 auto;
	}

	.shoppingCart .list {
		margin: 0 20rpx 0 20rpx;
		border-radius: 12rpx;
		background-color: #fff;
	}
	
	.shoppingCart .list .title{
		height: 74rpx;
		padding: 0 24rpx;
		font-size: 22rpx;
		color: #333;
		border-bottom: 1rpx solid #eee;
	}
	
	.shoppingCart .list .title .iconfont{
		font-size: 18rpx;
	}
	
	.shoppingCart .list .title .label{
		border-radius: 4rpx;
		padding: 2rpx 8rpx;
		background-color: var(--view-minorColorT);
		font-size: 20rpx;
		color: var(--view-theme);
		margin-right: 8rpx;
	}

	.shoppingCart .list .item {
		padding: 25rpx 30rpx;
		position: relative;
	}
	
	.shoppingCart .list .item .evaluate{
		font-size: 20rpx;
		color: var(--view-theme);
		margin-left: 248rpx;
		.num{
			font-size: 25rpx;
		}
	}
	
	.shoppingCart .list .item::after{
		position: absolute;
		content: " ";
		width: 618rpx;
		height: 1px;
		background: #EEEEEE;
		bottom: 0;
		right: 0;
	}

	.shoppingCart .list .item .picTxt {
		width: 585rpx;
		position: relative;
	}

	.shoppingCart .list .item .picTxt .pictrue {
		width: 160rpx;
		height: 160rpx;
		border-radius: 6rpx;
	}

	.shoppingCart .list .item .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.shoppingCart .list .item .picTxt .text {
		width: 400rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.shoppingCart .list .item .picTxt .text .reColor {
		color: #999;
	}

	.shoppingCart .list .item .picTxt .text .reElection {
		margin-top: 20rpx;
	}

	.shoppingCart .list .item .picTxt .text .reElection .titles {
		font-size: 24rpx;
	}

	.shoppingCart .list .item .picTxt .text .reElection .reBnt {
		width: 120rpx;
		height: 46rpx;
		border-radius: 23rpx;
		font-size: 26rpx;
	}

	.shoppingCart .list .item .picTxt .text .infor {
		font-size: 24rpx;
		color: #868686;
		margin-top: 16rpx;
	}
	
	.shoppingCart .list .item .picTxt .text .infor .name{
		max-width: 320rpx;
		display: inline-block;
		vertical-align: bottom;
	}
	
	.shoppingCart .list .item .picTxt .text .infor .iconfont{
		font-size: 20rpx;
		display: inline-block;
		padding: 3rpx 40rpx 0 10rpx;
	}
	
	.shoppingCart .list .item .picTxt .text .isGift{
		margin-top: 16rpx;
		color: var(--view-theme);
	}

	.shoppingCart .list .item .picTxt .text .money {
		font-size: 32rpx;
		color: #282828;
		margin-top: 28rpx;
	}

	.shoppingCart .list .item .picTxt .carnum {
		height: 47rpx;
		position: absolute;
		bottom: 7rpx;
		right: 0;
	}

	.shoppingCart .list .item .picTxt .carnum view {
		border: 1rpx solid #a4a4a4;
		width: 66rpx;
		text-align: center;
		height: 100%;
		line-height: 40rpx;
		font-size: 28rpx;
		color: #a4a4a4;
	}

	.shoppingCart .list .item .picTxt .carnum .reduce {
		border-right: 0;
		border-radius: 22rpx 0 0 22rpx;
	}

	.shoppingCart .list .item .picTxt .carnum .reduce.on {
		border-color: #e3e3e3;
		color: #dedede;
	}

	.shoppingCart .list .item .picTxt .carnum .plus {
		border-left: 0;
		border-radius: 0 22rpx 22rpx 0;
	}
	
	.shoppingCart .list .item .picTxt .carnum .plus.on{
		border-color: #e3e3e3;
		color: #dedede;
	}

	.shoppingCart .list .item .picTxt .carnum .num {
		color: #282828;
	}

	.shoppingCart .invalidGoods {
		background-color: #fff;
		margin: 12rpx 20rpx 0 20rpx;
		border-radius: 12rpx;
	}

	.shoppingCart .invalidGoods .goodsNav {
		width: 100%;
		height: 66rpx;
		padding: 0 30rpx;
		box-sizing: border-box;
		font-size: 28rpx;
		color: #282828;
	}

	.shoppingCart .invalidGoods .goodsNav .iconfont {
		color: #424242;
		font-size: 28rpx;
		margin-right: 17rpx;
	}

	.shoppingCart .invalidGoods .goodsNav .del {
		font-size: 26rpx;
		color: #999;
	}

	.shoppingCart .invalidGoods .goodsNav .del .icon-shanchu1 {
		color: #999;
		font-size: 33rpx;
		vertical-align: -2rpx;
		margin-right: 8rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item {
		padding: 20rpx 30rpx;
		border-top: 1rpx solid #f5f5f5;
	}

	.shoppingCart .invalidGoods .goodsList .item .invalid {
		font-size: 22rpx;
		color: #fff;
		width: 70rpx;
		height: 36rpx;
		background-color: #aaa;
		border-radius: 3rpx;
		text-align: center;
		line-height: 36rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .pictrue {
		width: 140rpx;
		height: 140rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .text {
		width: 400rpx;
		font-size: 28rpx;
		color: #ccc;
		height: 140rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .text .name {
		width: 100%;
	}

	.shoppingCart .invalidGoods .goodsList .item .text .infor {
		font-size: 24rpx;
		width: 100%;
	}

	.shoppingCart .invalidGoods .goodsList .item .text .end {
		font-size: 26rpx;
		color: #999;
		width: 100%;
	}
	
	.shoppingCart .tips{
		position: fixed;
		z-index:9;
		width: 100%;
		height: 56rpx;
		background: #FEF4E7;
		color: #FE960F;
		font-size: 24rpx;
		padding: 0 20rpx;
		box-sizing: border-box;
		bottom: 192rpx;
		bottom: calc(192rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		bottom: calc(192rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		.iconfont{
			margin-right: 12rpx;
		}
		&.on{
			// #ifndef H5
			bottom: 96rpx;
			// #endif
		}
	} 

	.shoppingCart .footer {
		z-index: 999;
		width: 100%;
		height: 96rpx;
		background-color: #fafafa;
		position: fixed;
		padding: 0 30rpx;
		box-sizing: border-box;
		border-top: 1rpx solid #eee;
		// #ifdef H5
		bottom: 94rpx;
		bottom: calc(94rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		bottom: calc(94rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		// #endif
		// #ifndef H5
		bottom: 98rpx;
		bottom: calc(98rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		bottom: calc(98rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		// #endif
	}

	.shoppingCart .footer.on {
		// #ifndef H5
		bottom: 0rpx;
		// #endif
	}

	.shoppingCart .footer .checkAll {
		font-size: 28rpx;
		color: #282828;
		margin-left: 16rpx;
	}

	// .shoppingCart .footer checkbox .wx-checkbox-input{background-color:#fafafa;}
	.shoppingCart .footer .money {
		font-size: 30rpx;
	}
	
	.shoppingCart .footer .money .left{
		text-align: right;
		font-size: 24rpx;
	}
	
	.shoppingCart .footer .money .left .font-color{
		font-size: 30rpx;
		font-weight: 600;
	}
	
	.shoppingCart .footer .money .left .detailed{
		font-size: 20rpx;
		background: #f1f1f1;
		padding: 2rpx;
		text-align: center;
		border-radius: 20rpx;
		width: 120rpx;
	}
	
	.shoppingCart .footer .money .left .detailed .iconfont{
		font-size: 24rpx;
		margin-left: 8rpx;
	}
	
	.shoppingCart .footer .placeOrder {
		color: #fff;
		font-size: 26rpx;
		width: 226rpx;
		height: 70rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 70rpx;
		margin-left: 22rpx;
		&.on{
			background-color: #ccc!important;
		}
	}

	.shoppingCart .footer .button .bnt {
		font-size: 28rpx;
		color: #999;
		border-radius: 50rpx;
		border: 1px solid #999;
		width: 160rpx;
		height: 60rpx;
		text-align: center;
		line-height: 60rpx;
	}

	.shoppingCart .footer .button form~form {
		margin-left: 17rpx;
	}

	.uni-p-b-96 {
		height: 96rpx;
	}

</style>
