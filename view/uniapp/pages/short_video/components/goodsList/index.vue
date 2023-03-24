<template>
	<view class="goodsList">
		<view class="nav">
			<view class="left">
				<text class="name">宝贝口袋</text>
				<text class="num">共{{count}}件商品</text>
			</view>
			<view class="cart" @click="goIncart">
				<image class="image" :src="imgHost + '/statics/images/cart.png'"></image>
				<text class="num">购物车</text>
			</view>
		</view>
		<scroll-view @scrolltolower="scrolltolower" :style="'width: '+ Width +'px; height: '+ (Height/num) +'px; display: flex; flex-direction: column;'" :scroll-y="true">
			<view class="itemList" v-for="(item,index) in goodList" :key="index" @click="goDetails(item)">
				<image class="pictrue" :src="item.image"></image>
				<view class="text">
					<view class="textCon">
						<text class="name line2" v-if="item.store_name.length>=32">{{item.store_name.slice(0,32)}}...</text>
						<text class="name line2" v-else>{{item.store_name}}</text>
						<view class="labelCon" v-if="item.promotions.title">
							<text class="label">{{item.promotions.title}}</text>
						</view>
					</view>
					<view class="bottom">
						<view class="money">
							<text class="lable">¥</text>
							<text class="price">{{item.price}}</text>
						</view>
						<view class="cart">
							<image v-if="item.product_type == 0 && !item.custom_form.length" class="image" @click.stop="goCart(item)" :src="imgHost + '/statics/images/add-cart.png'"></image>
							<view class="bnt" @click.stop="goBuy(item)">
								<text class="name">去抢购</text>
							</view>
						</view>
					</view>
				</view>
			</view>
			<text class="loading" v-if="loadend">没有更多内容啦~</text>
		</scroll-view>
		<uni-popup type="bottom" ref="pinglunGoods" @touchmove.stop.prevent="moveHandle">
			<view :style="'width: '+ windowWidth +'px; background-color: #fff; border-top-left-radius: 10px; border-top-right-radius: 10px;'">
					<productWindow :attr="attr" :isShow="1" :iSplus="1" :iScart='1' :storeInfo="storeInfo" @closeScrollview="closeScrollview" @ChangeAttr="ChangeAttr" @ChangeCartNum="ChangeCartNum" @attrVal="attrVal" @iptCartNum="iptCartNum" @goCat="goCat" id="product-window"
						:is_vip="is_vip"></productWindow>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import {
		mapGetters
	} from 'vuex';
	import { HTTP_REQUEST_URL } from '@/config/app';
	import productWindow from '../productWindow/index.vue';
	import {
		videoProduct
	} from '@/api/short-video.js';
	import {
		getProductDetail,
		postCartAdd
	} from '@/api/store.js';
	export default {
		components: {
			productWindow
		},
		computed: mapGetters(['cartNum']),
		props: {
			Width: {
				type:Number,
				default:0
			},
			Height : {
				type:Number,
				default:0
			}
		},
		data() {
			return {
				windowWidth: 0,
				imgHost: HTTP_REQUEST_URL,
				num: 1.15,
				goodList: [],
				videoID: 0,
				attr: {
					cartAttr: false,
					productAttr: [],
					productSelect: {}
				},
				storeInfo:{},
				productValue: [], //系统属性
				is_vip: 0, //是否是会员
				attrTxt: '请选择', //属性页面提示
				attrValue: '', //已选属性
				news: 1,
				id:0,//商品id
				count:0,
				limit: 20,
				page: 1,
				loadend: false,
				deliveryType:1,
				storeId:0
			};
		},
		created(){
			this.windowWidth = uni.getSystemInfoSync().windowWidth;
			this.videoID = uni.getStorageSync("videoID");
		},
		mounted(){
			// #ifndef MP
			this.productList();
			// #endif
		},
		methods: {
			goDetails(item){
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}`
				});
			},
			closeScrollview(){
				this.$refs.pinglunGoods.close();
			},
			goCart(item){
				this.news = 0;
				this.getGoodsDetails(item);
			},
			goBuy(item){
				this.news = 1;
				this.getGoodsDetails(item);
			},
			/*
			 * 加入购物车
			 */
			goCat: function() {
				let that = this,
					productSelect = that.productValue[this.attrValue];
				//如果有属性,没有选择,提示用户选择
				if (that.attr.productAttr.length && productSelect === undefined)
					return uni.showToast({
						title: '产品库存不足，请选择其它属性',
						icon: 'none',
						duration: 2000
					});
				if (that.attr.productSelect.cart_num <= 0) {
					that.attr.productSelect.cart_num = 1
					return uni.showToast({								title: '请先选择属性',								icon: 'none',								duration: 2000							});
				}
				let q = {
					productId: that.id,
					cartNum: that.attr.productSelect.cart_num,
					new: that.news,
					uniqueId: that.attr.productSelect !== undefined ? that.attr.productSelect.unique : ''
				};
				postCartAdd(q)
					.then(function(res) {
						if (that.news) {
							console.log(that.deliveryType);
							console.log(that.deliveryType);
							uni.navigateTo({
								url: '/pages/goods/order_confirm/index?new=1&cartId=' + res.data.cartId+'&delivery_type=' + that.deliveryType + '&store_id=' + that.storeId
							});
						} else {
							let cartNums = parseInt(that.cartNum) + parseInt(that.attr.productSelect.cart_num);
							that.$store.commit('indexData/setCartNum', cartNums > 99 ? '..' : cartNums + '')
							uni.showToast({
								title: '添加购物车成功',
								icon: 'none',
								duration: 2000
							});
							that.$refs.pinglunGoods.close();
							
						}
					})
					.catch(err => {
						return uni.showToast({										title: err,										icon: 'none',										duration: 2000									});
					});
			},
			goIncart(){
				uni.switchTab({
					url: '/pages/order_addcart/order_addcart'
				});
			},
			getGoodsDetails: function(item) {
				this.id = item.id;
				uni.showLoading({
					title: '加载中',
					mask: true
				});
				let that = this;
				getProductDetail(item.product_id).then(res => {
					uni.hideLoading();	
					this.$refs.pinglunGoods.open('bottom');
					let storeInfo = res.data.storeInfo;
					that.$set(that, 'storeInfo', storeInfo);
					that.$set(that.attr, 'cartAttr', true);
					that.$set(that.attr, 'productAttr', res.data.productAttr);
					that.$set(that, 'productValue', res.data.productValue);
					that.$set(that, 'is_vip', res.data.storeInfo.is_vip);
					let deliverySort = res.data.storeInfo.delivery_type.sort((x,y)=>x - y);
					that.$set(that, 'deliveryType', deliverySort[0] || 1);
					let storeId = res.data.storeInfo.type==1?res.data.storeInfo.relation_id:0;
					that.$set(that, 'storeId', storeId);
					that.DefaultSelect();
				}).catch(err => {
					uni.hideLoading();
				})
			},
			/**
			 * 默认选中属性
			 *
			 */
			DefaultSelect: function() {
				let productAttr = this.attr.productAttr;
				let valueobj = [];
				let value = [];
				for (var key in this.productValue) {
					if (this.productValue[key].stock > 0) {
						valueobj = this.attr.productAttr.length ? key.split(',') : [];
						break;
					}
				}
				// 处理已售罄时默认选中第一个
				if(!valueobj.length && this.attr.productAttr.length){
					value = Object.keys(this.productValue)[0].split(',');
				}else{
					value = valueobj;
				}
				
				for (let i = 0; i < productAttr.length; i++) {
					this.$set(productAttr[i], 'index', value[i]);
				}
				//sort();排序函数:数字-英文-汉字；
				let productSelect = this.productValue[value.join(',')];
				if (productSelect && productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeInfo.store_name);
					this.$set(this.attr.productSelect, 'image', productSelect.image);
					this.$set(this.attr.productSelect, 'price', productSelect.price);
					this.$set(this.attr.productSelect, 'stock', productSelect.stock);
					this.$set(this.attr.productSelect, 'unique', productSelect.unique);
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this, 'attrValue', value.join(','));
					this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
					this.$set(this, 'attrTxt', '已选择');
				} else if (!productSelect && productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeInfo.store_name);
					this.$set(this.attr.productSelect, 'image', this.storeInfo.image);
					this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
					this.$set(this.attr.productSelect, 'stock', 0);
					this.$set(this.attr.productSelect, 'unique', '');
					this.$set(this.attr.productSelect, 'cart_num', 0);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', '请选择');
				} else if (!productSelect && !productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeInfo.store_name);
					this.$set(this.attr.productSelect, 'image', this.storeInfo.image);
					this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
					this.$set(this.attr.productSelect, 'stock', this.storeInfo.stock);
					this.$set(this.attr.productSelect, 'unique', this.storeInfo.unique || '');
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', '请选择');
				}
			},
			/**
			 * 属性变动赋值
			 *
			 */
			ChangeAttr: function(res) {
				let productSelect = this.productValue[res];
				this.$set(this, "selectSku", productSelect);
				if (productSelect && productSelect.stock >= 0) {
					this.$set(this.attr.productSelect, 'image', productSelect.image);
					this.$set(this.attr.productSelect, 'price', productSelect.price);
					this.$set(this.attr.productSelect, 'stock', productSelect.stock);
					this.$set(this.attr.productSelect, 'unique', productSelect.unique);
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
					this.$set(this, 'attrValue', res);
					this.$set(this, 'attrTxt', '已选择');
				} else {
					this.$set(this.attr.productSelect, 'image', this.storeInfo.image);
					this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
					this.$set(this.attr.productSelect, 'stock', 0);
					this.$set(this.attr.productSelect, 'unique', '');
					this.$set(this.attr.productSelect, 'cart_num', 0);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', '请选择');
				}
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
				if (productSelect === undefined && !this.attr.productAttr.length) productSelect = this.attr
					.productSelect;
				//无属性值即库存为0；不存在加减；
				if (productSelect === undefined) return;
				let stock = productSelect.stock || 0;
				let num = this.attr.productSelect;
				if (changeValue) {
					num.cart_num++;
					if (num.cart_num > stock) {
						this.$set(this.attr.productSelect, 'cart_num', stock ? stock : 1);
						this.$set(this, 'cart_num', stock ? stock : 1);
					}
				} else {
					num.cart_num--;
					if (num.cart_num < 1) {
						this.$set(this.attr.productSelect, 'cart_num', 1);
						this.$set(this, 'cart_num', 1);
					}
				}
			},
			attrVal(val) {
				this.$set(this.attr.productAttr[val.indexw], 'index', this.attr.productAttr[val.indexw].attr_values[val
					.indexn]);
			},
			/**
			 * 购物车手动填写
			 *
			 */
			iptCartNum: function(e) {
				this.$set(this.attr.productSelect, 'cart_num', e);
			},
			productList(id,type){
				// #ifndef MP
				let ids = this.videoID;
				// #endif
				// #ifdef MP
				this.videoID = id;
				let ids = id;
				if(type){
					this.page = 1;
					this.goodList = [];
				}
				// #endif
				videoProduct(ids,{
					limit: this.limit,
					page: this.page
				}).then(res=>{
					let list = res.data.list;
					let loadend = list.length < this.limit;
					this.goodList = this.goodList.concat(list);
					this.loadend = loadend;
					this.page = this.page + 1;
					this.count = res.data.count;
				}).catch(err=>{
					return uni.showToast({
							title: err,
							icon: 'none',
							duration: 2000
						});
				})
			},
			scrolltolower(){
				this.productList(this.videoID);
			},
			closeGoods(){
				this.$emit('myevent');
			}
		}
	}
</script>

<style lang="scss">
	.loading{
		text-align: center;
		height: 70rpx;
		line-height: 70rpx;
		font-size: 28rpx;
		display: flex;
		flex-direction: row;
		justify-content: center;
		color: #999999;
	}
	.goodsList{
		padding: 0 20rpx;
		.itemList{
			flex-direction: row;
			background-color: #fff;
			margin-bottom: 20rpx;
			padding: 20rpx;
			width: 710rpx;
			border-radius: 14rpx;
			.pictrue{
				width: 220rpx;
				height: 220rpx;
				border-radius: 10rpx;
				margin-right: 16rpx;
			}
			.text{
				width: 420rpx;
				.textCon{
					height: 154rpx;
				}
				.bottom{
					display: flex;
					flex-direction: row;
					justify-content: space-between;
					align-items: flex-end;
					.cart{
						display: flex;
						flex-direction: row;
						align-items: center;
						.image{
							width: 62rpx;
							height: 62rpx;
						}
						.bnt{
							width: 144rpx;
							height: 64rpx;
							background: #F5222D;
							border-radius: 37rpx;
							display: flex;
							justify-content: center;
							align-items: center;
							margin-left: 20rpx;
							.name{
								font-weight: 400;
								color: #FFFFFF;
								font-size: 28rpx;
							}
						}
					}
					.money{
						display: flex;
						flex-direction: row;
						align-items: flex-end;
						.lable{
							font-weight: 600;
							color: #E93323;
							font-size: 24rpx;
						}
						.price{
							font-weight: 600;
							color: #E93323;
							font-size: 30rpx;
						}
					}
				}
				.labelCon{
					display: flex;
					flex-direction: row;
					margin-top: 16rpx;
					.label{
						border: 1px solid #E93323;
						padding: 2rpx 4rpx;
						font-size: 20rpx;
						font-weight: 400;
						color: #E93323;
						border-radius: 5rpx;
					}
				}
				.name{
					color:#333333;
					font-size: 28rpx;
					font-weight: 400;
				}
			}
		}
		.nav{
			display: flex;
			flex-direction: row;
			justify-content: space-between;
			align-items: center;
			height: 126rpx;
			.num{
				font-weight: 400;
				color: #999999;
				font-size: 22rpx;
			}
			.cart{
				align-items: center;		
				.image{
					margin-bottom: 5rpx;
					width:28rpx;
					height: 28rpx;
				}
			}
			.left{
				.name{
					font-weight: 500;
					color: #333333;
					font-size: 30rpx;
				}
			}
		}
	}
</style>