<template>
	<!-- 搜索框 -->
	<!-- #ifdef H5  -->
	<view class="header"
		:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%);margin-top:'+ mbConfig*2 +'rpx;'">
		<view class="serch-wrapper acea-row row-middle"
			:class="tabVal===0 && txtStyle===1?'center':tabVal===0 && txtStyle===1?'right':''"
			:style="'padding-left:'+ prConfig*2 +'rpx;padding-right:'+ (tabVal===0?prConfig*2:20) +'rpx;'">
			<view class="logo skeleton-rect" v-if="logoConfig && tabVal===1 && radioVal===1">
				<image :src="logoConfig" mode="heightFix"></image>
			</view>
			<view class="title" :class="tabVal===1?'on':''"
				:style="{color:`${textColor}`,fontStyle:`${textStyle!='bold'?textStyle:''}`,fontWeight:`${textStyle=='bold'?textStyle:''}`}"
				v-if="tabVal===0 || (tabVal===1 && radioVal === 0)" @click="goLink">{{titleConfig}}</view>
			<navigator v-if="tabVal===1" url="/pages/goods/goods_search/index"
				class="input acea-row row-middle skeleton-rect"
				:class="[boxStyle?'':'fillet',logoConfig?'':'on',txtStyle===1?'row-center':txtStyle===2?'row-right':'']"
				hover-class="none"><text class="iconfont icon-sousuo"></text>
				搜索商品</navigator>
		</view>
	</view>
	<!-- #endif -->
	<!-- #ifdef MP || APP-PLUS -->
	<view v-if="special" class="header"
		:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%);'">
		<view class="serch-wrapper acea-row row-middle"
			:class="tabVal===0 && txtStyle===1?'center':tabVal===0 && txtStyle===2?'right':''"
			:style="'padding-left:'+ prConfig*2 +'rpx!important;padding-right:'+ (tabVal===0?prConfig*2:20) +'rpx;'">
			<view class="logo skeleton-rect" v-if="logoConfig && tabVal===1 && radioVal===1">
				<image :src="logoConfig" mode="heightFix"></image>
			</view>
			<view class="title" :class="tabVal===1?'on':''"
				:style="{color:`${textColor}`,fontStyle:`${textStyle!='bold'?textStyle:''}`,fontWeight:`${textStyle=='bold'?textStyle:''}`}"
				v-if="tabVal===0 || (tabVal===1 && radioVal === 0)" @click="goLink">{{titleConfig}}</view>
			<navigator v-if="tabVal===1" url="/pages/goods/goods_search/index"
				class="input acea-row row-middle skeleton-rect"
				:class="[boxStyle?'':'fillet',logoConfig?'':'on',txtStyle===1?'row-center':txtStyle===2?'row-right':'']"
				hover-class="none"><text class="iconfont icon-sousuo"></text>
				搜索商品</navigator>
		</view>
	</view>
	<view v-else>
		<view class="mp-header"
			:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%);'">
			<view class="sys-head" :style="{ height: statusBarHeight + 'px'}"></view>
			<view class="serch-box" style="height: 43px;">
				<view class="serch-wrapper acea-row row-middle"
					:class="tabVal===0 && txtStyle===1?'center':tabVal===0 && txtStyle===2?'right':''"
					:style="'padding-left:'+ prConfig*2 +'rpx!important;padding-right:'+ (tabVal===0?prConfig*2:20) +'rpx;'">
					<view class="logo skeleton-rect" v-if="logoConfig && tabVal===1 && radioVal===1">
						<image :src="logoConfig" mode="heightFix"></image>
					</view>
					<view class="title" :class="tabVal===1?'on':''"
						:style="{color:`${textColor}`,fontStyle:`${textStyle!='bold'?textStyle:''}`,fontWeight:`${textStyle=='bold'?textStyle:''}`}"
						v-if="tabVal===0 || (tabVal===1 && radioVal === 0)" @click="goLink">{{titleConfig}}</view>
					<navigator v-if="tabVal===1" url="/pages/goods/goods_search/index"
						class="input acea-row row-middle skeleton-rect"
						:class="[boxStyle?'':'fillet',logoConfig?'':'on',txtStyle===1?'row-center':txtStyle===2?'row-right':'']"
						hover-class="none">
						<text class="iconfont icon-sousuo"></text>
						搜索商品
					</navigator>
				</view>
			</view>
		</view>
		<view :style="'height:'+(statusBarHeight+43)+'px;'"></view>
	</view>
	<!-- #endif -->
</template>

<script>
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight;
	export default {
		name: 'headerSerch',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			special: {
				type: Number,
				default: 0
			}
		},
		data() {
			return {
				statusBarHeight: statusBarHeight,
				marTop: 63,
				bgColor: this.dataConfig.bgColor.color,
				boxStyle: this.dataConfig.boxStyle.type,
				logoConfig: this.dataConfig.logoConfig.url,
				mbConfig: this.dataConfig.mbConfig.val,
				txtStyle: this.dataConfig.txtStyle.type,
				hotWords: this.dataConfig.hotWords.list,
				prConfig: this.dataConfig.prConfig.val,
				tabVal: this.dataConfig.tabConfig.tabVal,
				radioVal: this.dataConfig.radioConfig.tabVal,
				textColor: this.dataConfig.textColor.color[0].item,
				textStyle: this.dataConfig.textStyle.list[this.dataConfig.textStyle.type].style,
				titleConfig: this.dataConfig.titleConfig.value
			};
		},
		mounted() {
			let that = this;
			uni.setStorageSync('hotList', that.hotWords);
			that.$store.commit('hotWords/setHotWord', that.hotWords);
			// #ifdef MP || APP-PLUS
			setTimeout(() => {
				// 获取小程序头部高度
				let info = uni.createSelectorQuery().in(this).select(".mp-header");
				info.boundingClientRect(function(data) {
					that.marTop = data.height
				}).exec()
			}, 100)
			// #endif
		},
		methods: {
			goLink(){
				let url = this.dataConfig.linkConfig.value;
				this.$util.JumpPath(url);
			}
		}
	}
</script>

<style lang="scss">
	.serch-wrapper {
	
		&.center {
			justify-content: center;
		}
	
		&.right {
			justify-content: flex-end;
			/* #ifdef MP */
			padding-right: 185rpx !important;
			/* #endif */
		}
	}
	
	.title {
		font-size: 30rpx;
		&.on{
			margin-right: 20rpx;
		}
	}
	
	.map {
		color: #fff;
		font-size: 30rpx;
		margin-right: 20rpx;
	
		.info {
			&.on {
				max-width: 260rpx;
			}
	
			&.on1 {
				max-width: 156rpx;
			}
		}
	
		.iconfont {
			font-size: 32rpx;
			margin-right: 6rpx;
		}
	
	 .icon-xiangxia {
			font-size: 28rpx;
			margin-left: 10rpx;
		}
	}
	.header {
		width: 100%;
		height: 100rpx;
		background: linear-gradient(90deg, $bg-star 50%, $bg-end 100%);
		
		.serch-wrapper {
			padding: 20rpx 20rpx 0 0;

			.logo {
				height: 60rpx;
				margin-right: 20rpx;

				image {
					width: 100%;
					height: 100%;
				}
			}

			.input {
				flex: 1;
				height: 58rpx;
				padding: 0 20rpx 0 30rpx;
				background: rgba(247, 247, 247, 1);
				border: 1px solid rgba(241, 241, 241, 1);
				color: #BBBBBB;
				font-size: 28rpx;

				.iconfont {
					margin-right: 20rpx;
				}

				// 没有logo，直接搜索框
				&.on {
					width: 100%;
				}

				// 设置圆角
				&.fillet {
					border-radius: 29rpx;
				}

				// 文本框文字居中
				&.row-center {
					padding: 0;
				}
			}
		}
	}

	/* #ifdef MP || APP-PLUS */
	.mp-header {
		z-index: 30;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		background: linear-gradient(90deg, $bg-star 50%, $bg-end 100%);

		.serch-wrapper {
			height: 100%;
			/* #ifdef MP */
			padding: 0 220rpx 0 0 !important;
			/* #endif */
			/* #ifdef APP-PLUS */
			padding: 0 50rpx 0 40rpx;

			/* #endif */
			.logo {
				height: 60rpx;
				margin-right: 30rpx;

				image {
					width: 100%;
					height: 100%;
				}
			}

			.input {
				flex: 1;
				height: 50rpx;
				padding: 0 0 0 30rpx;
				background: rgba(247, 247, 247, 1);
				border: 1px solid rgba(241, 241, 241, 1);
				color: #BBBBBB;
				font-size: 28rpx;

				.iconfont {
					margin-right: 20rpx;
				}

				// 没有logo，直接搜索框
				&.on {
					/* #ifdef MP */
					width: 70%;
					/* #endif */
					/* #ifdef APP-PLUS */
					width: 100%;
					/* #endif */
				}

				// 设置圆角
				&.fillet {
					border-radius: 29rpx;
				}

				// 文本框文字居中
				&.row-center {
					padding: 0;
				}
			}
		}
	}

	/* #endif */
</style>
