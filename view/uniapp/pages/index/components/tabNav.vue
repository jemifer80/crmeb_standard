<template>
	<!-- 商品分类 -->
	<view>
		<!-- #ifdef MP || APP-PLUS -->
		<view :style="{height: (88+pbConfig*2) + 'rpx',background:'linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%)'}" v-if="!fromType"></view>
		<!-- #endif -->
		<view class="navTabBox"
			:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%);margin-top:'+mbConfig*2+'rpx;padding-top:'+pbConfig*2+'rpx;color:'+txtColor+';top:'+isTop"
			:class="{isFixed:isFixed}">
			<view class="longTab">
				<scroll-view scroll-x="true" style="white-space: nowrap; display: flex;" scroll-with-animation
					:scroll-left="tabLeft" show-scrollbar="true">
					<view :url="'/pages/goods/goods_list/index?cid='+item.id+'&title='+item.cate_name" class="longItem"
						:style='"width:"+isWidth+"px"' :data-index="index" :class="index===tabClick?'click':''"
						v-for="(item,index) in tabTitle" :key="index" :id="'id'+index" @click="longClick(item,index)">
						{{item.cate_name}}
					</view>
					<view class="underlineBox" :style='"transform:translateX("+isLeft+"px);width:"+isWidth+"px"'>
						<view class="underline" :style="'background-color:'+txtColor"></view>
					</view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script>
	import {
		getCategoryList
	} from '@/api/store.js';
	import { getCategoryVersion } from '@/api/api.js';
	export default {
		name: 'tabNav',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isFixed: {
				type: Boolean | String | Number,
				default: false
			},
			fromType:{
				type:Number,
				default:0
			}
		},
		data() {
			return {
				tabTitle: [],
				tabLeft: 0,
				isWidth: 0, //每个导航栏占位
				tabClick: 0, //导航栏被点击
				isLeft: 0, //导航栏下划线位置
				bgColor: this.dataConfig.bgColor.color,
				mbConfig: this.dataConfig.mbConfig.val,
				pbConfig: this.dataConfig.pbConfig.val,
				txtColor: this.dataConfig.txtColor.color[0].item,
				fixedTop: 0,
				isTop: 0,
				navHeight: 45
			};
		},
		created() {
			let that = this;
			that.getAllCategory();
			// 获取设备宽度
			uni.getSystemInfo({
				success(e) {
					that.isWidth = e.windowWidth / 5
				}
			})
		},
		methods: {
			// 导航栏点击
			longClick(item, index) {
				if (this.tabTitle.length > 5) {
					this.tabLeft = (index - 2) * this.isWidth //设置下划线位置
				}
				this.tabClick = index //设置导航点击了哪一个
				this.isLeft = index * this.isWidth //设置下划线位置
				this.$emit('bindSortId', item.id)
			},
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
					uni.setStorageSync('category', JSON.stringify(res.data));
					this.setCategory(res.data);
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
							console.log(typeof category)
							this.setCategory(JSON.parse(category));
						} else{
							uni.setStorageSync('categoryVersion', res.data.version);
							this.getCategory();
						}
					});
				} else{
					this.getCategory();
				}
			}
		}
	}
</script>

<style lang="scss">
	.navTabBox {
		width: 100%;
		background: linear-gradient(90deg, $bg-star 50%, $bg-end 100%);
		color: rgba(255, 255, 255, 1);
		// #ifndef H5
		min-height: 68rpx;
		// #endif
		padding-bottom: 20rpx;
		&.isFixed {
			z-index: 45;
			position: fixed;
			left: 0;
			width: 100%;
			/* #ifdef H5 */
			padding-top: 20rpx;
			top: 0;
			/* #endif */
		}

		.longTab {
			width: 100%;

			.longItem {
				height: 50rpx;
				display: inline-block;
				line-height: 50rpx;
				text-align: center;
				font-size: 30rpx;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;

				&.click {
					font-weight: bold;
				}
			}

			.underlineBox {
				height: 3px;
				width: 20%;
				display: flex;
				align-content: center;
				justify-content: center;
				transition: .5s;

				.underline {
					width: 33rpx;
					height: 4rpx;
					background-color: white;
				}
			}
		}
	}

	.child-box {
		width: 100%;
		position: relative;
		background-color: #fff;
		box-shadow: 0 2px 5px 1px rgba(0, 0, 0, 0.02);

		.wrapper {
			display: flex;
			align-items: center;
			padding: 20rpx 0;
			background: #fff;
		}

		.child-item {
			flex-shrink: 0;
			width: 140rpx;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			margin-left: 10rpx;

			image {
				width: 90rpx;
				height: 90rpx;
				border-radius: 50%;
			}

			.txt {
				font-size: 24rpx;
				color: #282828;
				text-align: center;
				margin-top: 10rpx;
			}

			&.on {
				image {
					border: 1px solid $theme-color-opacity;
				}

				.txt {
					color: $theme-color;
				}
			}
		}
	}
</style>