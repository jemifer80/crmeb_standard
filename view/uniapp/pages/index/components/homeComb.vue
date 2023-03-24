<template>
	<view class="page_count">
		<view class="bg-img">
			<img :src="bgColor" alt="">
		</view>
		<!--搜索-->
		<view :class="{'my-main': true}">
			<view class="mp-header" :class="[isScrolled?'scrollColor':'',special?'on':'']" id="home">
				<view class="sys-head" :style="{ height: statusBarHeight + 'px' }" v-if="!special"></view>
				<view class="serch-box">
					<view class="serch-wrapper flex" :class="special?'on':''">
						<view v-if="logoConfig" class="logo skeleton-rect">
							<image :src="logoConfig" mode="heightFix"></image>
						</view>
						<navigator v-if="hotWords.length > 0" :url="'/pages/goods/goods_search/index?searchVal='+searchVal"
							:class="logoConfig ? 'input' : 'uninput'" hover-class="none" class="skeleton-rect">
							<view class='swiperTxt'>
								<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" :interval="interval"
									:duration="duration" vertical="true" circular="true" @change="textChange">
									<block v-for="(item,index) in hotWords" :key='index'>
										<swiper-item catchtouchmove='catchTouchMove'>
											<view class='acea-row row-between-wrapper'>
												<view class='text acea-row row-between-wrapper'>
													<view class='newsTitle line1'>{{item.val}}</view>
												</view>
											</view>
										</swiper-item>
									</block>
								</swiper>
							</view>
							<text class="iconfont icon-sousuo"></text>
						</navigator>
						<navigator v-else url="/pages/goods/goods_search/index" :class="logoConfig ? 'input' : 'uninput'"
							hover-class="none" class="skeleton-rect">
							搜索商品
							<text class="iconfont icon-sousuo"></text>
						</navigator>
					</view>
				</view>
			</view>
			<!--选项卡-->
			<view :style="'height:'+ (statusBarHeight+43) +'px'" v-if="!special"></view>
		<!-- 	<view v-if="isFixed" style="visibility: hidden;" :style="{ height: navHeight + 'px' }"></view> -->
			<view v-if="dataConfig.classShow.val" class="navTabBox tabNav" :class="[isScrolled?'scrollColor':'',special?'on':'']">
				<view class="longTab" :style='"width:"+mainWidth+"px"'>
					<scroll-view scroll-x="true" style="white-space: nowrap; display: flex;" scroll-with-animation
						:scroll-left="tabLeft" show-scrollbar="true">
						<view class="longItem" :data-index="index" :class="index===tabClick?'click':''"
							@click="changeTab(item,index)" v-for="(item,index) in tabTitle" :key="index"
							:id="'id'+index">{{item.cate_name}}</view>
						<view class="underlineBox" :style='"transform:translateX("+isLeft+"px);width:"+isWidth+"px"'>
							<view class="underline"></view>
						</view>
					</scroll-view>
				</view>
				<view class="category" @click="showCategory">
					<text class="iconfont icon-gengduofenlei"></text>
				</view>
			</view>
			<!--固定分类-->
			<view v-if="isCategory" class="category_count">
				<view class="sys-head tui-skeleton" :style="{ height: statusBarHeight + 'px' }"></view>
				<view class="title">精选类目</view>
				<view class="cate_count">
					<view class="category_item" :style="{background:index===tabClick?classColor:''}" :class="index===tabClick?'clicks':''" @click="changeTab(item,index)"
						v-for="(item,index) in tabTitle" :key="index" :id="'ids'+index">{{item.cate_name}}</view>
				</view>
			</view>
		</view>
		<!--轮播图-->
		<view style="height: 40px;" v-if="!special && dataConfig.classShow.val"></view>
		<view class="swiperBg" :style="{ paddingBottom: isMenu ? '40rpx' : '20rpx'}" v-if="tabId==-99">
			<block>
				<view class="swiper page_swiper" v-if="imgUrls.length">
					<swiper :autoplay="true" :circular="circular" :interval="intervals" :duration="duration"
						indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff" :current="swiperCur"
						previous-margin="10rpx" next-margin="10rpx" @change="swiperChange">
						<block v-for="(item,index) in imgUrls" :key="index">
							<swiper-item :class="{ active: index == swiperCur,scalex:isScale }">
								<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
									<image :src="item.img" class="slide-image aa"></image>
								</view>
							</swiper-item>
						</block>
					</swiper>
					<!--重置小圆点的样式  -->
					<view class="dots">
						<block v-for="(item,index) in imgUrls" :key="index">
							<view class="dot" :class="index == swiperCur ? ' active' : ''"></view>
						</block>
					</view>
				</view>
			</block>
		</view>
		<view v-if="isCategory" class="mask" @click="isCategory = false"></view>
	</view>
</template>

<script>
	import {
		getCategoryList
	} from '@/api/store.js';
	import { getCategoryVersion } from '@/api/api.js';
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight;
	export default {
		name: 'homeComb',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isFixed: {
				type: Boolean,
				default: false
			},
			isScrolled: {
				type: Boolean,
				default: false
			},
			isScale: {
				type: Boolean,
				default: false
			},
			isMenu: {
				type: Boolean,
				default: false
			},
			special: {
				type: Number,
				default: 0
			}
		},
		data() {
			return {
				statusBarHeight: statusBarHeight,
				autoplay: true,
				interval: this.dataConfig.numConfig.val * 1000 || 2500,
				duration: 500,
				marTop: 0,
				logoConfig: this.dataConfig.logoConfig.url,
				hotWords: this.dataConfig.hotWords.list || [],
				tabClick: 0, //导航栏被点击
				isLeft: 0, //导航栏下划线位置
				isWidth: 0, //每个导航栏占位
				mainWidth: 0,
				tabLeft: 0,
				tabTitle: [],
				isTop: 0,
				navHeight: 38,
				homeTop: statusBarHeight,
				indicatorDots: false,
				circular: true,
				intervals: 3000,
				imgUrls: [], //图片轮播数据
				imageH: 310,
				swiperCur: 0,
				searchVal: '',
				bgColor: this.dataConfig.swiperConfig.list && this.dataConfig.swiperConfig.list[0]['img'],
				tabId: -99,
				isCategory: false,
				classColor: this.dataConfig.classColor.color[0].item,
			};
		},
		watch: {
			imageH(nVal, oVal) {
				let self = this
				this.imageH = nVal
			},
		},
		created() {
			var that = this
			// 获取设备宽度
			uni.getSystemInfo({
				success(e) {
					that.mainWidth = e.windowWidth
					that.isWidth = (e.windowWidth - 65) / 8
				}
			})
			const query = uni.createSelectorQuery().in(that);
			that.$nextTick(() => {
				query.select('.navTabBox').boundingClientRect(data => {
					that.navHeight = data.height > 42 ? data.height : 42
				}).exec();
			})

			that.isTop = (this.statusBarHeight + 43) + 'px'
			that.imgUrls = that.dataConfig.swiperConfig.list
			that.getAllCategory();
		},
		mounted() {
			let that = this;
			uni.setStorageSync('hotList', that.hotWords);
			setTimeout(() => {
				// 获取小程序头部高度
				let info = uni.createSelectorQuery().in(this).select(".mp-header");
				info.boundingClientRect(function(data) {
					that.marTop = data.height
				}).exec()
			}, 100)
			that.$nextTick(function() {
				uni.getImageInfo({
					src: that.setDomain(that.imgUrls[0].img),
					success: function(res) {
						that.$set(that, 'imageH', res.height);
					},
					fail: function(error) {
						that.$set(that, 'imageH', 310);
					}
				})
				// const menuButton = uni.getMenuButtonBoundingClientRect();
				const query = uni.createSelectorQuery().in(this);
				query
					.select('#home')
					.boundingClientRect(data => {
						//this.homeTop = menuButton.top * 2 + menuButton.height - data.height;
					})
					.exec();
			})
		},
		methods: {
			setCategory(data) {
				data.unshift({
					"id": -99,
					'cate_name': '首页'
				})
				this.tabTitle = data;
				// #ifdef MP || APP-PLUS
				this.isTop = (uni.getSystemInfoSync().statusBarHeight + 43) + 'px'
				// #endif
				// #ifdef H5 
				this.isTop = 0
				// #endif
			},
			getCategory() {
				getCategoryList().then(res => {
					let data = res.data;
					let datas = [...data];
					data.unshift({
						"id": -99,
						'cate_name': '首页'
					})
					uni.setStorageSync('category', JSON.stringify(datas));
					this.tabTitle = data;
				})
			},
			// 获取导航
			getAllCategory: function() {
				let that = this;
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
			goDetail(url) {
				let urls = url.info[1].value
				this.$util.JumpPath(urls);
			},
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf("https://") > -1) return url;
				else return url.replace('http://', 'https://');
			},
			swiperChange(e) {
				let {
					current,
					source
				} = e.detail;
				if (source === 'autoplay' || source === 'touch') {
					this.swiperCur = e.detail.current;
					this.bgColor = this.imgUrls[e.detail.current]['img']
				}
			},
			textChange(e) {
				let {
					current,
					source
				} = e.detail;
				if (source === 'autoplay' || source === 'touch') {
					this.searchVal = this.hotWords[e.detail.current]['val']
				}
			},
			/**显示全部分类*/
			showCategory() {
				this.isCategory = true;
			},
			/*跳转为页面*/
			changeTab(item, index) {
				if (this.tabClick == index) return;
				this.tabClick = index; //设置导航点击了哪一个
				this.isLeft = index * this.isWidth + 16; //设置下划线位置
				// this.bgColor = item.id ? item.img : this.dataConfig.swiperConfig.list[0]['img']
				// this.imgUrls = item.id ? [{
				// 	img: item.img
				// }] : this.dataConfig.swiperConfig.list
				this.tabId = item.id;
				console.log('66667',item.id);
				this.$emit('bindSortId', item.id);
			}
		},
	}
</script>

<style lang="scss" scoped>
	.scrollColor{
		transition: background-color .5s ease;
		background-color: #fff;
		color: #333 !important;
		
		.longItem{
			color: #333 !important;
			
			&.click{
				&::after{
					background: #333 !important;
				}
			}
		}
	}
	.page_count {
		position: relative;
		overflow: hidden;

		.bg-img {
			position: absolute;
			width: 100%;
			height: 100%;
			top: 0;
			z-index: 0;
			filter: blur(0);
			overflow: hidden;

			img {
				width: 100%;
				height: 100%;
				filter: blur(30rpx);
				transform: scale(1.5);
			}
		}
	}

	.my-main {
		transition: background-color .5s ease;
	}

	.swiperTxt {
		width: 300rpx;
		height: 100%;
		line-height: 58rpx;
		overflow: hidden;
	}

	.swiperTxt .text {
		width: 480rpx;
	}

	.swiperTxt .text .label {
		font-size: 20rpx;
		color: #ff4c48;
		width: 64rpx;
		height: 30rpx;
		border-radius: 40rpx;
		text-align: center;
		line-height: 28rpx;
		border: 2rpx solid #ff4947;
	}

	.swiperTxt .text .newsTitle {
		width: 300rpx;
		font-size: 24rpx;
		color: #fff;
	}

	.swiperTxt swiper {
		height: 100%;
	}

	.mp-header {
		z-index: 99;
		position: fixed;
		left: 0;
		top:0;
		width: 100%;
		
		&.on{
			position: relative;
		}
		
		.serch-box{
			height: 43px;
		}

		.logo {
			height: 60rpx;
			margin-right: 20rpx;
			image{
				width: 100%;
				height: 100%;
			}
		}

		.serch-wrapper {
			align-items: center;
			/* #ifdef MP */
			padding: 0 220rpx 0 30rpx;
			/* #endif */
			/* #ifndef MP */
			padding: 0 30rpx;
			/* #endif */
			height: 100%;
			
			&.on{
				padding: 0 30rpx;
			}

			.input,
			.uninput {
				flex: 1;
				display: flex;
				align-items: center;
				width: 330rpx;
				height: 58rpx;
				padding: 0 50rpx 0 30rpx;
				background: rgba(0, 0, 0, .22);
				border-radius: 29rpx;
				color: #fff;
				font-size: 28rpx;
				position: relative;
				box-sizing: border-box;

				.iconfont {
					position: absolute;
					right: 20rpx;
					top: 13rpx;
				}
			}
		}
	}

	.tabNav {
		padding-top: 10rpx;
	}

	.navTabBox {
		width: 100%;
		color: rgba(255, 255, 255, 1);
		padding: 0 80rpx 0 30rpx;
		z-index: 99;
		position: fixed;
		left: 0;
		width: 100%;
		padding-top: 5px;
		
		&.on{
			position: relative;
		}

		scroll-view {
			/* #ifdef MP */
			width: 640rpx;
			/* #endif */
			/* #ifndef MP */
			width: 666rpx;
			/* #endif */
			padding-right: 30rpx;
			height: 70rpx;
		}

		.click {
			color: white;
		}

		.longTab {
			height: 34px;
			.longItem {
				display: inline-block;
				text-align: center;
				font-size: 28rpx;
				color: #FFFFFF;
				max-width: 160rpx;
				margin-right: 30rpx;
				position: relative;
				font-weight: 400;

				&:last-child {
					margin-right: 0;
				}

				&.click {
					font-weight: 600;
					font-size: 30rpx;
					color: #FFFFFF;

					&::after {
						content: '';
						transition: .5s;
						// width: 20rpx;
						height: 3rpx;
						background: #FFFFFF;
						position: absolute;
						bottom: -7rpx;
						left: 50%;
						margin-left: -10rpx;
				}
					}
			}
		}

		.category {
			position: absolute;
			right: 0;
			top: 8rpx;
			width: 60rpx;
			height: 45rpx;
			line-height: 45rpx;
			z-index: 10;
			
			.iconfont{
				font-size: 35rpx;
			}
		}

		&.isFixed {
			z-index: 10;
			position: fixed;
			left: 0;
			width: 100%;
		}
	}

	.category_count {
		width: 100%;
		background: #fff;
		padding: 0 32rpx 16rpx 32rpx;
		position: fixed;
		top: 0;
		left: 0;
		z-index: 100;

		.title {
			color: #292929;
			font-size: 28rpx;
			/* #ifdef MP */
			padding-top: 11px;
			/* #endif */
			/* #ifndef MP */
			padding-top: 14px;
			/* #endif */
		}

		.cate_count {
			margin-top: 32rpx;
			display: flex;
			flex-wrap: wrap;

			.category_item {
				margin-right: 20rpx;
				width: 24%;
				padding: 0 20rpx;
				height: 72rpx;
				text-align: center;
				line-height: 72rpx;
				word-wrap: break-word;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
				margin-right: calc(4% /3);
				background-color: #F5F5F5;
				border-radius: 8rpx;
				margin-bottom: 16rpx;
				font-size: 24rpx;

				&.clicks {
					color: #fff;
				}

				&:nth-child(4n) {
					margin-right: 0;
				}
			}
		}
	}

	.mask {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, .7);
		z-index: 22;
	}

	.scrolled {
		z-index: 99;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		background-color: #fff;

		.longItem,
		.click,
		.category text {
			color: #000 !important;
		}

		.underline {
			background: #000 !important;
		}

		.input,
		.uninput {
			background: rgba(0, 0, 0, 0.22) !important;
		}

		.click {
			&::after {
				background-color: #fff !important;
			}
		}
	}

	.swiperBg {
		z-index: 1;

		.colorBg {
			position: absolute;
			left: 0;
			top: 0;
			height: 130rpx;
			width: 100%;
		}

		.page_swiper {
			position: relative;
			width: 100%;
			height: auto;
			margin: 0 auto;
			border-radius: 10rpx;
			overflow-x: hidden;
			z-index: 20;
			padding: 0 10rpx;

			swiper-item {
				border-radius: 10rpx;
			}

			.swiper-item,
			image,
			.acea-row.row-between-wrapper {
				width: 100%;
				height: 100%;
				margin: 0 auto;
				border-radius: 10rpx;
			}

			swiper {
				width: 100%;
				display: block;
			}

			image {
				transform: scale(0.93);
				transition: all 0.6s ease;
				// &.scalex{
				// 	transform: scale(1);
				// 	transition: none;
				// }
			}

			swiper-item.active,
			swiper-item.scalex {
				image {
					transform: scale(1);
				}
			}

			/*用来包裹所有的小圆点  */
			.dots {
				width: 156rpx;
				height: 36rpx;
				display: flex;
				flex-direction: row;
				position: absolute;
				left: 320rpx;
				bottom: 0;
			}

			/*未选中时的小圆点样式 */
			.dot {
				width: 16rpx;
				height: 6rpx;
				border-radius: 6rpx;
				margin-right: 6rpx;
				background-color: rgba(255, 255, 255, .4);

				/*选中以后的小圆点样式  */
				&.active {
					width: 32rpx;
					height: 6rpx;
					background-color: rgba(255, 255, 255, .4);
				}
			}

		}
	}

	/deep/.dot0 .uni-swiper-dots-horizontal {
		left: 10%;
	}

	/deep/.dot1 .uni-swiper-dots-horizontal {
		left: 50%;
	}

	/deep/.dot2 .uni-swiper-dots-horizontal {
		left: 90%;
	}
</style>
