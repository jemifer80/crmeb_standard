<template>
	<view :style="colorStyle">
		<image :src="imgHost+'/statics/images/header.png'" mode="" class="img"></image>
		<view class="tipaddress">
			<view class="title">{{fromActive == 1?'激活送好礼':'新人大礼包'}}</view>
			<view class="list">
				<view class="list-img acea-row row-between-wrapper" :style="{backgroundImage:'url('+imgHost+'/statics/images/box1.png'+')'}" v-if="comerGift.product_count>0 && fromActive == 0">
				  <view class="left acea-row row-center-wrapper">
						<image :src="imgHost+'/statics/images/vip.png'" mode=""></image>
					</view>
					<view class="right">新人专享价商品</view>
				</view>
				<view class="list-img acea-row row-between-wrapper" :style="{backgroundImage:'url('+imgHost+'/statics/images/box1.png'+')'}" v-if="comerGift.first_order_discount>0 && fromActive == 0">
          <view class="left">
            {{parseFloat(comerGift.first_order_discount)/10 || 10}}<text class="text">折</text>
          </view>
          <view class="right">新人首单优惠</view>
        </view>
				<view class="list-img acea-row row-between-wrapper" :style="{backgroundImage:'url('+imgHost+'/statics/images/box1.png'+')'}" v-if="comerGift.register_give_integral>0">
				  <view class="left">{{comerGift.register_give_integral}}</view>
				  <view class="right">新人赠送积分</view>
				</view>
				<view class="list-img acea-row row-between-wrapper" :style="{backgroundImage:'url('+imgHost+'/statics/images/box1.png'+')'}" v-if="comerGift.register_give_money>0">
				  <view class="left">{{comerGift.register_give_money}}<text class="text">元</text></view>
				  <view class="right">新人赠送余额</view>
				</view>
				<view class="list-img acea-row row-between-wrapper" :style="{backgroundImage:'url('+imgHost+'/statics/images/box1.png'+')'}" v-if="comerGift.coupon_count>0" v-for="(item,index) in comerGift.register_give_coupon" :key="index">
				   <view class="left">
						 <text v-if="item.coupon_type==1">{{item.coupon_price.toString().split(".")[0]}}</text>
						 <text class="nums"
						 	v-if="item.coupon_price.toString().split('.').length>1 && item.coupon_type==1">.{{item.coupon_price.toString().split(".")[1]}}</text>
						 <text v-if="item.coupon_type==2">{{parseFloat(item.coupon_price)/10}}</text>
						 <text class="text">{{item.coupon_type==1?'元':'折'}}</text>
				   </view>
				   <view class="right">优惠券</view>
				</view>
			</view>
			<view class="btn" @click="accept">
				立即收下
			</view>
		</view>
		<view class="mark"></view>
	</view>
</template>

<script>
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import colors from '@/mixins/color';
	export default {
		mixins: [colors],
		props:{
			comerGift: {
				type: Object,
				default: function() {
					return {}
				},
			},
			fromActive: {
				type: Number,
				default: 0
			}
		},
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
			};
		},
		methods:{
			accept(){
				this.$emit('comerPop')
			}
		}
	}
</script>

<style lang="scss">
	.img {
		position: fixed;
		top: 162rpx;
		left: 9%;
		width: 590rpx;
		height: 294rpx;
		z-index: 100;

	}

	.tipaddress {
		position: fixed;
		left: 13%;
		top: 25%;
		width: 538rpx;
		height: 650rpx;
		background-color: var(--view-theme);
		border-radius: 10rpx;
		z-index: 100;
		text-align: center;
		
		.title{
			color: #fff;
			font-size: 50rpx;
			margin-top: 32rpx;
			margin-bottom: 18rpx;
		}

		.goods-img {
			width: 258rpx;
			height: 52rpx;
			margin-top: 50rpx;

		}

		.list {
			height: 370rpx;
			overflow-x: hidden;
			overflow-y: auto;

			.list-img {
				margin-top: 14rpx;
				margin-left: 32rpx;
				width: 474rpx;
				height: 124rpx;
				background-repeat: no-repeat;
				background-size: 100% 100%;

				.left {
					width: 144rpx;
					font-size: 48rpx;
					font-weight: 500;
					color: var(--view-theme);
					
					image{
						width: 72rpx;
						height: 72rpx;
						display: block;
					}

					.text {
						font-size: 24rpx;
					}
					
					.nums{
						font-size: 30rpx;
					}
				}

				.right {
					width: 328rpx;
					font-size: 28rpx;
					font-weight: 500;
					color: var(--view-theme);
					text-align: left;
					padding-left: 50rpx;
				}
			}
		}


		.btn {
			width: 474rpx;
			height: 78rpx;
			background: #FFDE5C;
			border-radius: 39rpx;
			font-size: 30rpx;
			font-weight: 500;
			color: #B66A08;
			line-height: 78rpx;
			text-align: center;
			margin-left: 32rpx;
			margin-top: 48rpx;
		}

	}

	.mark {
		position: fixed;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
		background: rgba(0, 0, 0, 0.5);
		z-index: 99;
	}
</style>
