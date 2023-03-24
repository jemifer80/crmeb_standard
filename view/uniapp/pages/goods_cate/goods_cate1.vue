<template>
  <!-- 商品分类第一种布局样式 -->
	<view class='productSort copy-data' :style="{height:pageHeight}">
		<!-- #ifdef APP-PLUS || MP -->
		<view class="sys-head" :style="{height:sysHeight}"></view>
		<!-- #endif -->
		<!-- #ifdef MP || APP-PLUS -->
		<view class="sys-title">商品分类</view>
		<!-- #endif -->
		<view class='header acea-row row-center-wrapper'>
			<view class='acea-row row-between-wrapper input'>
				<text class='iconfont icon-sousuo'></text>
				<input type='text' placeholder='点击搜索商品信息' @confirm="searchSubmitValue" confirm-type='search'
					name="search" placeholder-class='placeholder'></input>
			</view>
		</view>
		<view class="scroll-box">
			<view class='aside'>
				<scroll-view scroll-y="true" scroll-with-animation='true' class="height-add">
					<view class='item acea-row row-center-wrapper' :class='index==navActive?"on":""'
						v-for="(item,index) in productList" :key="index" @click='tap(index,"b"+index)'>
						<text>{{item.cate_name}}</text>
					</view>
					<!-- #ifdef APP-PLUS -->
					<view class="item" v-if="isFooter"></view>
					<!-- #endif -->
				</scroll-view>
			</view>


			<view class='conter'>
				<scroll-view scroll-y="true" :scroll-into-view="toView" @scroll="scroll" scroll-with-animation='true'
				 class="conterScroll height-add">
					<block v-for="(item,index) in productList" :key="index">
						<view class='listw' :id="'b'+index">
							<view class='title acea-row row-center-wrapper'>
								<view class='line'></view>
								<view class='name'>{{item.cate_name}}</view>
								<view class='line'></view>
							</view>
							<view class='list acea-row'>
								<block v-for="(itemn,indexn) in item.children" :key="indexn">
									<navigator hover-class='none'
										:url='"/pages/goods/goods_list/index?sid="+itemn.id+"&title="+itemn.cate_name'
										class='item acea-row row-column row-middle'>
										<view class='picture'>
											<image :src='itemn.pic' v-if="itemn.pic"></image>   
											<image src="/static/images/sort-img.png" v-else></image>
										</view>
										<view class='name line1'>{{itemn.cate_name}}</view>
									</navigator>
								</block>
							</view>
						</view>
					</block>
					<view :style='"height:"+(height-heightDiv)+"rpx;"'></view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script>
	let sysHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
	import {
		getCategoryList
	} from '@/api/store.js';
	import { getCategoryVersion } from '@/api/api.js';
	import {
		mapState,
		mapGetters
	} from "vuex"
	const app = getApp();
	export default {
		props: {
			isFooter:{
				type:Boolean,
				default:false
			}
		},
		data() {
			return {
				navlist: [],
				productList: [],
				navActive: 0,
				number: "",
				height: 0,
				heightDiv: 0,
				hightArr: [],
				toView: "",
				tabbarH: 0,
				footH: 0,
				windowHeight: 0,
				pageHeight: '100%',
				sysHeight: sysHeight,
				// #ifdef APP-PLUS
				pageHeight: app.globalData.windowHeight,
				// #endif
				footerStatus: false,
				lock: false
			}
		},
		computed: {
			...mapState({
				cartNum: state => state.indexData.cartNum
			})
		},
		mounted() {
			let that = this
			// this.getAllCategory();
			
			// #ifdef H5
			uni.getSystemInfo({
				success: function(res) {
					that.pageHeight = res.windowHeight + 'px'
				}
			});
			// #endif
		},
		methods: {
			footHeight(data) {
				this.footH = data
			},
			infoScroll: function() {
				let that = this;
				let len = that.productList.length;
				this.number = that.productList[len - 1].children.length;
				let height = 0;
				let hightArr = [];
				//设置商品列表高度
				let query = uni.createSelectorQuery().in(this);
				query.select(".conter").boundingClientRect();
				query.exec(function(res){
					height = res[0].height;
				})
				for (let i = 0; i < len; i++) {
					//获取元素所在位置
					let query = uni.createSelectorQuery().in(this);
					let idView = "#b" + i;
					query.select(idView).boundingClientRect();
					query.exec(function(res) {
						let top = res[0].top;
						that.hightArr.push(top);
						if(len == that.hightArr.length){
							//设置转化比例
							uni.getSystemInfo({
								success: function(res) {
									let per = (750 / res.windowWidth);
									that.height = height * per;
									that.heightDiv = (that.hightArr[that.hightArr.length-1] - that.hightArr[that.hightArr.length-2])*per;
								},
							});
						}
					});
				};
			},
			tap: function(index, id) {
				this.toView = id;
				this.navActive = index;
				this.$set(this, 'lock', true);
			},
			setCategory(data) {
				let that = this;
				this.productList = data;
				this.$nextTick(res => {
					setTimeout(function(){
						that.infoScroll();
					})
				})
			},
			getCategory() {
				getCategoryList().then(res => {
					uni.setStorageSync('category', JSON.stringify(res.data));
					this.setCategory(res.data);
				})
			},
			getAllCategory: function() {
				// let that = this;
				let category = uni.getStorageSync('category');
				if (category) {
					getCategoryVersion().then(res => {
						let categoryVersion = uni.getStorageSync('categoryVersion');
						if (res.data.version === categoryVersion) {
							this.setCategory(JSON.parse(category));
						} else{
							uni.setStorageSync('categoryVersion', res.data.version);
							this.getCategory();
						}
					});
				} else{
					this.getCategory();
				}
			},
			scroll: function(e) {
				let scrollTop = e.detail.scrollTop;
				let scrollArr = this.hightArr;
				if (this.lock) {
					this.$set(this, 'lock', false);
					return;
				}
				for (let i = 0; i < scrollArr.length; i++) {
					if (scrollTop >= 0 && scrollTop < scrollArr[1] - scrollArr[0]) {
						this.navActive = 0
					} else if (scrollTop >= scrollArr[i] - scrollArr[0] && scrollTop < scrollArr[i + 1] - scrollArr[
							0]) {
						this.navActive = i
					} else if (scrollTop >= scrollArr[scrollArr.length - 1] - scrollArr[0]) {
						this.navActive = scrollArr.length - 1
					}
				}
			},
			searchSubmitValue: function(e) {
				if (this.$util.trim(e.detail.value).length > 0)
					uni.navigateTo({
						url: '/pages/goods/goods_list/index?searchValue=' + e.detail.value
					})
				else
					return this.$util.Tips({
						title: '请填写要搜索的产品信息'
					});
			},
		}
	}
</script>
<style>
  .height-add {
    height: 100%;
  }
	page {
		height: 100%;
	}
</style>
<style scoped lang="scss">
	/deep/uni-scroll-view{
		padding-bottom: 0!important;
	}
	.sys-title {
		z-index: 10;
		position: relative;
		height: 40px;
		line-height: 40px;
		font-size: 34rpx;
		color: #333;
		background-color: #fff;
		text-align: center;
	}
	.sys-head {
		background-color: #fff;
	}
	.productSort {
		display: flex;
		flex-direction: column;
		//#ifdef MP
		height: calc(100vh - var(--window-top)) !important;
		//#endif
		//#ifndef MP
		height: 100vh
		//#endif
	}

	.productSort .header {
		width: 100%;
		height: 96rpx;
		background-color: #fff;
		border-bottom: 1rpx solid #f5f5f5;
	}

	.productSort .header .input {
		width: 700rpx;
		height: 60rpx;
		background-color: #f5f5f5;
		border-radius: 50rpx;
		box-sizing: border-box;
		padding: 0 25rpx;
	}

	.productSort .header .input .iconfont {
		font-size: 35rpx;
		color: #555;
	}

	.productSort .header .input .placeholder {
		color: #999;
	}

	.productSort .header .input input {
		font-size: 26rpx;
		height: 100%;
		width: 597rpx;
	}

	.productSort .scroll-box {
		flex: 1;
		overflow: hidden;
		display: flex;
	}

	// #ifndef MP
	uni-scroll-view {
		padding-bottom: 100rpx;
	}

	// #endif

	.productSort .aside {
		width: 180rpx;
		height: 100%;
		overflow: hidden;
		background-color: #f7f7f7;
	}

	.productSort .aside .item {
		height: 100rpx;
		width: 100%;
		font-size: 26rpx;
		color: #424242;
		text-align: center;
	}

	.productSort .aside .item.on {
		background-color: #fff;
		border-left: 4rpx solid var(--view-theme);
		width: 100%;
		color: var(--view-theme);
		font-weight: bold;
	}

	.productSort .conter {
		flex: 1;
		height: 100%;
		overflow: hidden;
		padding: 0 14rpx;
		background-color: #fff;
		position: relative;
	}

	.productSort .conter .listw {
		padding-top: 20rpx;
	}

	.productSort .conter .listw .title {
		height: 90rpx;
	}

	.productSort .conter .listw .title .line {
		width: 100rpx;
		height: 2rpx;
		background-color: #f0f0f0;
	}

	.productSort .conter .listw .title .name {
		font-size: 28rpx;
		color: #333;
		margin: 0 30rpx;
		font-weight: bold;
	}

	.productSort .conter .list {
		flex-wrap: wrap;
	}

	.productSort .conter .list .item {
		width: 177rpx;
		margin-top: 26rpx;
	}

	.productSort .conter .list .item .picture {
		width: 120rpx;
		height: 120rpx;
		border-radius: 50%;
	}

	.productSort .conter .list .item .picture image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.productSort .conter .list .item .name {
		font-size: 24rpx;
		color: #333;
		height: 56rpx;
		line-height: 56rpx;
		width: 120rpx;
		text-align: center;
	}
</style>
