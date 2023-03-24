<template>
	<view class="deliveryDetail" :style="colorStyle">
		<view class="header">
			<view class="title on" v-if="deliveryInfo.delivery_id">{{deliveryInfo.deliveryOrder.finish_code}}</view>
			<view class="title" v-else>待接单</view>
			<view class="tip">{{deliveryInfo.delivery_id?'稍后请将收货码告诉配送员':'等待配送员接单完成后开始派送'}}</view>
			<view class="picTxt acea-row row-between-wrapper" v-if="deliveryInfo.delivery_id">
				<view class="left acea-row row-middle">
					<view class="pictrue">
						<image src="../static/delivery.png"></image>
					</view>
					<view class="text">
						<view class="name line1">{{deliveryInfo.delivery_name}}</view>
						<view>{{deliveryInfo.delivery_id}}</view>
					</view>
				</view>
				<view class="icon" @click="call(deliveryInfo.delivery_id)">
					<text class="iconfont icon-dianhua"></text>
				</view>
			</view>
			<view class="picTxt acea-row row-between-wrapper" v-else>
				<view class="pictrue">
					<image src="../static/dispatch.png"></image>
				</view>
				<view class="text">系统派单中...</view>
			</view>
		</view>
		<view class="delivery">
			<view class="info">
				<view class="title">收件人信息</view>
				<view class="item acea-row row-between row-top">
					<view class="name">姓名：</view>
					<view class="text">{{deliveryInfo.deliveryOrder.user_name}}</view>
				</view>
				<view class="item acea-row row-between row-top">
					<view class="name">手机号：</view>
					<view class="text">{{deliveryInfo.deliveryOrder.receiver_phone}}</view>
				</view>
				<view class="item acea-row row-between row-top">
					<view class="name">地址：</view>
					<view class="text">{{deliveryInfo.deliveryOrder.to_address}}</view>
				</view>
			</view>
			<view class="list" v-if="expressList.length">
				<view class='item' v-for="(item,index) in expressList" :key="index">
					<view class='circular acea-row row-center-wrapper' :class='index === 0 ? "on":""'>
						<text class="iconfont icon-complete" v-if="index === 0"></text>
					</view>
					<view class='text' :class='index===0 ? "on-font":""'>
						<view>{{item.label}}</view>
						<view class='data' :class='index===0 ? "on-font on":""'>{{item.time}}</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import colors from '@/mixins/color.js';
	import {
		deliveryDetail
	} from '@/api/order.js';
	export default{
		mixins: [colors],
		data(){
			return{
				orderId: 0,
				deliveryInfo:{},
				expressList: []
			}
		},
		onLoad(options){
			this.orderId = options.orderId || 0
			this.orderDetail();
		},
		onShow(){},
		methods:{
			orderDetail(){
				deliveryDetail(this.orderId).then(res=>{
					this.deliveryInfo = res.data;
					this.expressList = res.data.order_log.city_delivery;
				}).catch(err=>{
					this.$util.Tips({
						title: err
					});
				})
			},
			call(phone){
				uni.makePhoneCall({					phoneNumber: phone				});
			}
		}
	}
</script>

<style lang="scss">
	.deliveryDetail{
		padding: 14rpx 30rpx;
		.header{
			background-color: #fff;
			border-radius: 14rpx;
			text-align: center;
			padding: 56rpx 30rpx 0 30rpx;
			.title{
				font-size: 44rpx;
				font-weight: 500;
				color: #333333;
				&.on{
					font-size: 60rpx;
					font-weight: 600;
				}
			}
			.tip{
				font-size: 24rpx;
				font-weight: 400;
				color: #666666;
				margin-top: 14rpx;
				border-bottom: 1px dotted #D8D8D8;
				padding-bottom: 46rpx;
			}
			.picTxt{
				padding: 26rpx 0;
				.left{
					.text{
						width: 436rpx;
						margin-left: 20rpx;
						color: #666666;
						font-weight: 400;
						font-size: 24rpx;
						.name{
							color: #333333;
							font-size: 28rpx;
							margin-bottom: 2rpx;
						}
					}
				}
				.icon{
					width: 44rpx;
					height: 44rpx;
					background: #E7E7E7;
					border-radius: 50%;
					.iconfont{
						font-size: 24rpx;
						color: #666;
					}
				}
				.pictrue{
					width: 80rpx;
					height: 80rpx;
					image{
						width: 100%;
						height: 100%;
					}
				}
				.text{
					font-weight: 500;
					color: #333333;
					font-size: 28rpx;
					width: 520rpx;
					text-align: left;
				}
			}
		}
		.delivery{
			background-color: #fff;
			border-radius: 14rpx;
			margin-top: 14rpx;
			.info{
				padding: 24rpx 30rpx;
				border-bottom: 1px dotted #D8D8D8;
				.title{
					font-size: 30rpx;
					font-weight: 400;
					color: #333333;
					margin-bottom: 32rpx;
				}
				.item{
					font-weight: 400;
					font-size: 28rpx;
					color: #999999;
					margin-bottom: 26rpx;
					.name{
						color: #333333;
					}
					.text{
						width: 476rpx;
						text-align: right;
					}
				}
			}
			.list{
				margin-top: 34rpx;
				padding-bottom: 40rpx;
				.item {
					padding: 0 40rpx;
					position: relative;
					.circular {
						width: 20rpx;
						height: 20rpx;
						border-radius: 50%;
						position: absolute;
						top: -1rpx;
						left: 32rpx;
						background-color: #ddd;
						.iconfont{
							color: #fff;
							font-size: 20rpx;
						}
						&.on{
							width: 30rpx;
							height: 30rpx;
							background-color: var(--view-theme);
							left:28rpx;
						}
					}
					.text {
						font-size: 26rpx;
						color: #999;
						width: 615rpx;
						border-left: 2px solid #e6e6e6;
						padding: 0 0 60rpx 38rpx;
						&.on-font{
							color: var(--view-theme);
						}
						.data{
							font-size: 24rpx;
							color: #999;
							margin-top: 10rpx;
							&.on-font{
								color: var(--view-theme);
							}
							.time{
								margin-left: 15rpx;
							}
						}
						&.on{
							border-left-color: var(--view-minorColor);
						}
					}
				}
			}
		}
	}
</style>