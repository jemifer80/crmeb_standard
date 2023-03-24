<template>
	<view class="collection" :style="colorStyle">
		<view class="nav acea-row row-center-wrapper">
			<view class="item" :class="active == index?'on':''" v-for="(item,index) in navList" :key="index" @click="navTap(index)">{{item.name}}</view>
		</view>
		<view class="manage acea-row row-between-wrapper" v-if="collectProductList.length">
			<view>当前共 <text class="num">{{count}}</text> {{!active ? '件商品':'条视频'}}</view>
			<view class="close"  @click="manageTap" v-if="administer">取消</view>
			<view class="font-color" @click="manageTap" v-else>管理</view>
		</view>
		<view class="collectList" v-if="collectProductList.length && !active">
			<checkbox-group @change="checkboxChange">
			  <view class="item acea-row row-between-wrapper" v-for="(item,index) in collectProductList" :key="index" @click="goGoods(item.product_id)">
					<view class="pictrue">
						<!-- #ifndef MP -->
						<checkbox class="checkbox" v-if="administer" :value="(item.id).toString()" :checked="item.checked" />
						<!-- #endif -->
						<!-- #ifdef MP -->
						<checkbox class="checkbox" v-if="administer" :value="item.id" :checked="item.checked" />
						<!-- #endif -->
						<image :src="item.image" mode="aspectFill"></image>
						<view class="activityFrame" v-if="item.activity_frame.image" :style="'background-image: url('+item.activity_frame.image+');'"></view>
					</view>
					<view class="text">
						<view class="top">
							<view class="name line2">{{item.store_name}}</view>
							<view class="label acea-row" v-if="item.promotions.title">
								<text class="labelCon">{{item.promotions.title}}</text>
							</view>
						</view>
						<view class="money">¥<text class="num">{{item.price}}</text></view>
					</view>
			  </view>
			</checkbox-group>
			<view class='loadingicon acea-row row-center-wrapper'>
				<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
			</view>
		  <view class="footer acea-row row-between-wrapper" v-if="administer">
		  	<checkbox-group @change="checkboxAllChange">
		  		<checkbox value="all" :checked="isAllSelect" />
		  		<text class='checkAll'>全选</text>
		  	</checkbox-group>
		  	<view class="acea-row row-middle">
		  		<view class="bnt on acea-row row-center-wrapper" @click="del('product')">取消收藏</view>
		  	</view>
		  </view> 
		</view>
		<view class="videoList" v-if="collectProductList.length && active">
			<checkbox-group @change="checkboxChange">
				<view class="acea-row row-middle">
					<view class="item" v-for="(item,index) in collectProductList" :key="index" @click="goVideo(item.video_id)">
						<!-- #ifndef MP -->
						<checkbox class="checkbox" v-if="administer" :value="(item.id).toString()" :checked="item.checked" />
						<!-- #endif -->
						<!-- #ifdef MP -->
						<checkbox class="checkbox" v-if="administer" :value="item.id" :checked="item.checked" />
						<!-- #endif -->
						<image :src="item.image" mode="aspectFill"></image>
						<view class="like acea-row row-bottom">
							<text class="iconfont icon-shipindianzan-weidian1"></text>{{item.like_num}}
						</view>
					</view>
				</view>
			</checkbox-group>
			<view class='loadingicon acea-row row-center-wrapper'>
				<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
			</view>
			<view class="footer acea-row row-between-wrapper" v-if="administer">
				<checkbox-group @change="checkboxAllChange">
					<checkbox value="all" :checked="isAllSelect" />
					<text class='checkAll'>全选</text>
				</checkbox-group>
				<view class="acea-row row-middle">
					<view class="bnt on acea-row row-center-wrapper" @click="del('video')">取消收藏</view>
				</view>
			</view>
		</view>
		<view class='noCommodity' v-else-if="!collectProductList.length && page > 1">
			<view class='pictrue'>
				<image :src="imgHost + '/statics/images/noCollection.png'"></image>
			</view>
			<recommend :hostProduct="hostProduct"></recommend>
		</view>
		<view></view>
		<home v-if="navigation"></home>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	import colors from '@/mixins/color.js';
	import {HTTP_REQUEST_URL} from '@/config/app';
	import {
		getCollectUserList,
		getProductHot,
		collectDel
	} from '@/api/store.js';
	import {
		mapGetters
	} from "vuex";
	import {
		toLogin
	} from '@/libs/login.js';
	import recommend from '@/components/recommend';
	import home from '@/components/home';
	export default{
		mixins:[colors],
		computed: mapGetters(['isLogin']),
		components: {
			recommend,
			home
		},
		data(){
			return{
				navList:[
					{
						name:'商品'
					},
					{
						name:'视频'
					}
				],
				active:0,
				hostProduct: [],
				loadTitle: '加载更多',
				loading: false,
				loadend: false,
				collectProductList: [],
				limit: 4,
				page: 1,
				hotScroll:false,
				hotPage:1,
				hotLimit:10,
				imgHost:HTTP_REQUEST_URL,
				administer:0,
				isAllSelect: false,
				count:0,
				isShowAuth: false
			}
		},
		onLoad() {},
		onShow(){
			uni.removeStorageSync('form_type_cart');
			this.loadend = false;
			this.page = 1;
			this.collectProductList = [];
			this.get_host_product();
			if (this.isLogin) {
				this.get_user_collect_product(this.active ? 'video' : 'product');
			} else {
				//#ifndef MP
				toLogin();
				//#endif
				//#ifdef MP
				this.isShowAuth = true;
				//#endif
			}
		},
		methods:{
			onLoadFun(){
				this.get_user_collect_product('product');
				this.isShowAuth = false;
			},
			// 授权关闭
			authColse: function(e) {
			  this.isShowAuth = e
			},
			goGoods(id){
				if(this.administer) return false
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${id}`
				});
			},
			goVideo(id){
				if(this.administer) return false
				uni.navigateTo({
					//#ifdef APP
					url: '/pages/short_video/appSwiper/index?id='+id,
					//#endif
					//#ifndef APP
					url: '/pages/short_video/nvueSwiper/index?id='+id,
					//#endif
				})
			},
			del(type){
				let ids = [];
				this.collectProductList.forEach(item=>{
					if(item.checked){
						ids.push(item.id);
					}
				})
				if(!ids.length){
					return this.$util.Tips({
						title: '请选择收藏商品或视频'
					});
				}
				collectDel(ids,type).then(res=>{
					this.loadend = false;
					this.page = 1;
					this.$set(this,'collectProductList',[]);
					this.get_user_collect_product(type);
					return this.$util.Tips({
						title: res.msg
					});
				}).catch(err=>{
					return this.$util.Tips({
						title: err
					});
				})
			},
			checkboxChange(event){
				let idList = event.detail.value;
				this.collectProductList.forEach((item)=>{
					if(idList.indexOf(item.id + '') !== -1){
						item.checked = true;
					}else{
						item.checked = false;
					}
				})
				if(idList.length == this.collectProductList.length){
					this.isAllSelect = true;
				}else{
					this.isAllSelect = false;
				}
			},
			forGoods(val){
				let that = this;
				if(!that.collectProductList.length) return
				that.collectProductList.forEach((item)=>{
					if(val){
						item.checked = true;
					}else{
						item.checked = false;
					}
				})
			},
			checkboxAllChange(event){
				let value = event.detail.value;
				if(value.length){
					this.forGoods(1)
				}else{
					this.forGoods(0)
				}
			},
			manageTap(){
				this.administer = !this.administer;
			},
			navTap(index){
				this.active = index;
				let type = 'product'
				if(index){
					type = 'video'
				}else{
					type = 'product'
				}
				this.isAllSelect = false;
				this.forGoods(0);
				this.loadend = false;
				this.page = 1;
				this.$set(this,'collectProductList',[]);
				this.get_user_collect_product(type);
			},
			/**
			 * 获取收藏产品
			 */
			get_user_collect_product: function(type) {
				let that = this;
				if (this.loading) return;
				if (this.loadend) return;
				that.loading = true;
				that.loadTitle = "";
				getCollectUserList({
					page: that.page,
					limit: that.limit,
					category:type
				}).then(res => {
					let collectProductList = res.data.list;
					collectProductList.forEach(item=>{
						item.checked = false;
					})
					this.count = res.data.count;
					let loadend = collectProductList.length < that.limit;
					that.collectProductList = that.$util.SplitArray(collectProductList, that.collectProductList);
					that.$set(that, 'collectProductList', that.collectProductList);
					that.loadend = loadend;
					that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
					that.page = that.page + 1;
					that.loading = false;
					console.log(that.page)
				}).catch(err => {
					that.loading = false;
					that.loadTitle = "加载更多";
				});
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
				let that = this;
				if(that.hotScroll) return
				getProductHot(
					that.hotPage,
					that.hotLimit,
				).then(res => {
					that.hotPage++
					that.hotScroll = res.data.length<that.hotLimit
					that.hostProduct = that.hostProduct.concat(res.data)
				});
			}
		},
		onReachBottom() {
			if(this.collectProductList.length){
				this.get_user_collect_product('product');
			}else{
				// this.get_host_product();
			}
		}
	}
</script>

<style lang="scss">
	.collection{
		.nav{
			background-color: #fff;
			height: 88rpx;
			.item{
				margin: 0 48rpx;
				font-weight: 400;
				color: #282828;
				font-size: 30rpx;
				position: relative;
				&.on{
					font-weight: 500;
					color: var(--view-theme);
					&:before{
						content: '';
						position: absolute;
						width: 60rpx;
						height: 3rpx;
						background: var(--view-theme);
						bottom: -28rpx;
					}
				}
			}
		}
		.manage{
			padding: 0 30rpx;
			font-weight: 400;
			color: #333333;
			font-size: 28rpx;
			background-color: #fff;
			height: 74rpx;
			.close{
				color: #999999;
			}
			.num{
				color: var(--view-theme);
				margin: 0 5rpx;
			}
		}
		.collectList{
			padding: 0 20rpx 100rpx 20rpx;
			.item{
				margin-top: 20rpx;
				background-color: #fff;
				border-radius: 14rpx;
				padding: 20rpx;
				.pictrue{
					width: 220rpx;
					height: 220rpx;
					border-radius: 10rpx;
					position: relative;
					.activityFrame{
						border-radius: 10rpx;
					}
					image{
						border-radius: 10rpx;
						width:100%;
						height: 100%;
					}
					.checkbox{
						position: absolute;
						top:10rpx;
						left:10rpx;
						z-index: 9;
					}
					/deep/checkbox .uni-checkbox-input {
						background-color: rgba(0, 0, 0, 0.16);
					}
					
					/deep/checkbox .wx-checkbox-input {
						background-color: rgba(0, 0, 0, 0.16);
					}
				}
				.text{
					width:420rpx;
					.top{
						height: 128rpx;
					}
					.name{
						font-weight: 400;
						color: #333333;
						font-size: 28rpx;
					}
					.label{
						margin-top: 16rpx;
						.labelCon{
							border: 1px solid var(--view-theme);
							padding: 2rpx 4rpx;
							color: var(--view-theme);
							font-weight: 400;
							font-size: 20rpx;
							border-radius: 10rpx;
						}
					}
					.money{
						margin-top: 62rpx;
						font-size: 24rpx;
						font-weight: 600;
						color: var(--view-theme);
						.num{
							font-size: 30rpx;
						}
					}
				}
			}
		}
		.videoList{
			padding: 0 4rpx 100rpx 4rpx;
			.item{
				width: 226rpx;
				height: 300rpx;
				border-radius: 8rpx;
				position: relative;
				margin-left: 16rpx;
				margin-top: 20rpx;
				position: relative;
                overflow: hidden;
				image{
					width: 100%;
					height: 100%;
				}
				.checkbox{
					position: absolute;
					top:10rpx;
					left:10rpx;
					z-index: 9;
				}
				/deep/checkbox .uni-checkbox-input {
					background-color: rgba(0, 0, 0, 0.16);
				}
				
				/deep/checkbox .wx-checkbox-input {
					background-color: rgba(0, 0, 0, 0.16);
				}
				.like{
					position: absolute;
					color: #fff;
					bottom: 0;
					font-weight: 400;
					font-size: 20rpx;
					left: 0;
					width: 226rpx;
					height: 100rpx;
					background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(0,0,0,0.25) 100%);
					border-radius: 0 0 8rpx 8rpx;
					padding: 0 0 14rpx 14rpx;
					.iconfont{
						font-size: 24rpx;
						margin-right: 6rpx;
					}
				}
			}
		}
		.footer {
			box-sizing: border-box;
			padding: 0 30rpx;
			width: 100%;
			height: 96rpx;
			box-shadow: 0px -4px 20px 0px rgba(0, 0, 0, 0.06);
			background-color: #fff;
			position: fixed;
			bottom: 0;
			z-index: 30;
			height: calc(96rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
			height: calc(96rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
			padding-bottom: constant(safe-area-inset-bottom); ///兼容 IOS<11.2/
			padding-bottom: env(safe-area-inset-bottom); ///兼容 IOS>11.2/
			width: 100%;
			left: 0;
			
			.bnt {
				width: 160rpx;
				height: 60rpx;
				border-radius: 30rpx;
				border: 1rpx solid #ccc;
				color: #666666;
		
				&.on {
					border: 1rpx solid var(--view-theme);
					margin-left: 16rpx;
					color: var(--view-theme);
				}
			}
		}
	}
</style>