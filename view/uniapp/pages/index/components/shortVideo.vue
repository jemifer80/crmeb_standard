<template>
	<view class="shortVideo" :style="{paddingLeft:prConfig+'rpx',paddingRight:prConfig+'rpx',marginTop:mbCongfig+'rpx',background:bgColor}" v-show="!isSortType" v-if="videoList.length">
		<view class="nav acea-row row-between-wrapper">
			<view class="title skeleton-radius" :style="'color:'+titleColor">短视频</view>
			<view class="more skeleton-radius" @click="more(0)">更多<text class="iconfont icon-you"></text></view>
		</view>
		<view class="list on" v-if="itemStyle">
			<scroll-view scroll-x="true" class="scroll" show-scrollbar="false">
				<view class="item skeleton-radius" v-for="(item,index) in videoList" :key="index" @click="more(item.id)">
					<view class="pictrue">
						<image :src="item.image" mode="aspectFill"></image>
						<view class="like acea-row row-bottom">
							<text class="iconfont icon-shipindianzan-weidian1"></text>{{item.like_num}}
						</view>
					</view>
				</view>
			</scroll-view>
		</view>
		<view class="list" v-else>
			<view class="item acea-row row-between" v-for="(item,index) in videoList" :key="index" @click="more(item.id)">
				<view class="pictrue skeleton-radius">
					<image :src="item.image" mode="aspectFill"></image>
					<view class="like acea-row row-bottom">
						<text class="iconfont icon-shipindianzan-weidian1"></text>{{item.like_num}}
					</view>
				</view>
				<view class="text">
					<view class="conter">
						<view class="header acea-row row-middle skeleton-radius">
							<image :src="item.type_image" mode="aspectFill"></image>
							<view class="name line1" :style="'color:'+titleColor">{{item.type_name}}</view>
						</view>
						<view class="info line2 skeleton-radius" :style="'color:'+infoColor">{{item.desc}}</view>
					</view>
					<view class="goodsList acea-row row-middle">
						<view class="pictrue skeleton-radius" v-for="(j,jindex) in item.product_info" :key="jindex" v-if="jindex<3" @click.stop="goGoods(j.id)">
							<image :src="j.image" mode="aspectFill"></image>
							<view class="money acea-row row-bottom row-center" v-if="jindex<2">
								<text>¥{{j.price}}</text>
							</view>
							<view class="num acea-row row-center-wrapper" v-else>
								<text>+{{item.product_num-2}}</text>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import {
		diyVideoList
	} from '@/api/short-video.js';
	export default {
		name: 'shortVideo',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		data() {
			return {
				videoList: [],
				bgColor: this.dataConfig.bgColor.color[0].item,
				titleColor: this.dataConfig.titleColor.color[0].item,
				infoColor: this.dataConfig.infoColor.color[0].item,
				mbCongfig: this.dataConfig.mbCongfig.val*2,
				prConfig: this.dataConfig.prConfig.val*2, //背景边距
				itemStyle: this.dataConfig.itemStyle.type,
				numConfig: this.dataConfig.numConfig.val
			}
		},
		created() {},
		mounted() {
			this.getVideoList();
		},
		methods: {
			getVideoList: function() {
				let that = this;
				let limit = this.$config.LIMIT;
				diyVideoList({
					page: 1,
					limit: this.numConfig >= limit ? limit : this.numConfig
				}).then(res => {
					that.videoList = res.data;
				});
			},
			more(id){
				uni.navigateTo({
					//#ifdef APP
					url: '/pages/short_video/appSwiper/index?id='+id,
					//#endif
					//#ifndef APP
					url: '/pages/short_video/nvueSwiper/index?id='+id,
					//#endif
				})
			},
			goGoods(id){
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${id}`
				});
			}
		}
	}
</script>

<style lang="scss">
	.shortVideo {
		.scroll {
		  white-space: nowrap; 
		  display: flex;
		}
		.nav {
			width: 100%;
			height: 90rpx;

			.title {
				font-weight: 600;
				color: #333333;
				font-size: 30rpx;
			}

			.more {
				font-weight: 400;
				color: #999999;
				font-size: 24rpx;

				.iconfont {
					font-size: 24rpx;
				}
			}
		}

		.list {
			padding-bottom: 1rpx;
			&.on {
				flex-wrap: nowrap;
				overflow: hidden;
				padding-bottom: 20rpx;

				.item {
					margin-right: 24rpx;
					margin-bottom: 0;
					display: inline-block;
					&:last-of-type{
						margin-right: 0;
					}

					.pictrue {
						margin-right: 0;
					}
				}
			}

			.item {
				margin-bottom: 40rpx;

				.pictrue {
					width: 226rpx;
					height: 300rpx;
					border-radius: 8rpx;
					position: relative;
					margin-right: 30rpx;
					border-radius: 8rpx;

					image {
						width: 100%;
						height: 100%;
						border-radius: 8rpx;
					}

					.like {
						position: absolute;
						bottom: 0;
						left: 0;
						font-size: 20rpx;
						font-weight: 400;
						color: #FFFFFF;
						background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.25) 100%);
						width: 226rpx;
						height: 100rpx;
						padding: 0 0 14rpx 14rpx;
						border-radius: 8rpx;

						.iconfont {
							font-size: 24rpx;
							margin-right: 6rpx;
						}
					}
				}

				.text {
					flex: 1;

					.goodsList {
						margin-top: 34rpx;
						overflow: hidden;

						.pictrue {
							width: 128rpx;
							height: 128rpx;
							border-radius: 6rpx;
							position: relative;
							margin-right: 24rpx;

							&:nth-of-type(3n) {
								margin-right: 0;
							}

							image {
								width: 100%;
								height: 100%;
								display: block;
								border-radius: 6rpx;
							}
							
							.num{
								position: absolute;
								color: #fff;
								font-size: 30rpx;
								font-weight: 400;
								background: rgba(0,0,0,0.3);
								left:0;
								top:0;
								width: 100%;
								height: 100%;
								border-radius: 6rpx;
							}

							.money {
								position: absolute;
								color: #fff;
								font-size: 22rpx;
								bottom: 0;
								left: 0;
								width: 128rpx;
								height: 100rpx;
								background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.25) 100%);
								border-radius: 6rpx;
								padding-bottom: 6rpx;
								text-align: center;
							}
						}
					}

					.conter {
						height: 134rpx;

						.header {
							.name {
								flex: 1;
								width: 300rpx;
							}

							image {
								width: 36rpx;
								height: 36rpx;
								border: 1px solid #FFFFFF;
								display: block;
								margin-right: 10rpx;
								border-radius: 50%;
							}

							font-weight: 500;
							color: #333333;
							font-size: 28rpx;
						}

						.info {
							font-weight: 400;
							color: #666666;
							font-size: 24rpx;
							margin-top: 20rpx;
							height: 62rpx;
						}
					}
				}
			}
		}
	}
</style>
