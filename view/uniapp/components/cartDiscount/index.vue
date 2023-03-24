<template>
	<view>
		<!-- 购物车优惠明细 -->
		<view class="cartDiscount" :class="discountInfo.discount === true ? 'on' : ''">
			<view class="title">优惠明细<text class="iconfont icon-guanbi5" @click="closeDiscount"></text></view>
			<view class="list">
				<view class="item acea-row row-between-wrapper">
					<view>商品总价：</view>
					<view>￥{{discountInfo.deduction.sum_price}}</view>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view>优惠抵扣：</view>
					<view class="font-color">-￥{{$util.$h.Sub(discountInfo.deduction.sum_price,discountInfo.deduction.pay_price)}}</view>
				</view>
				<view class="discountList">
					<view class="coupon acea-row row-between-wrapper" v-if="discountInfo.deduction.coupon_price">
						<view>{{discountInfo.coupon.coupon_title}}</view>
						<view>-￥{{discountInfo.deduction.coupon_price}}</view>
					</view>
					<view class="coupon acea-row row-between-wrapper" v-if="discountInfo.deduction.first_order_price">
						<view>新人首单优惠</view>
						<view>-￥{{discountInfo.deduction.first_order_price}}</view>
					</view>
					<view class="coupon acea-row row-between-wrapper" v-if="discountInfo.deduction.promotions_price">
						<view>优惠活动</view>
						<view>-￥{{discountInfo.deduction.promotions_price}}</view>
					</view>
					<view class="coupon acea-row row-between-wrapper" v-if="discountInfo.deduction.vip_price">
						<view>会员优惠</view>
						<view>-￥{{discountInfo.deduction.vip_price}}</view>
					</view>
				</view>
				<div class="item">
					<slot name="bottom"></slot>
				</div>
				<view class="bottom">
					<view class="item acea-row row-between-wrapper">
						<view>共优惠：</view>
						<view class="font-color">-￥{{$util.$h.Sub(discountInfo.deduction.sum_price,discountInfo.deduction.pay_price)}}</view>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view class="total">合计：</view>
						<view class="money">￥{{discountInfo.deduction.pay_price}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="mask" @touchmove.prevent :hidden="discountInfo.discount === false" @click="closeDiscount"></view>
	</view>
</template>

<script>
	export default {
		props: {
			discountInfo: {
				type: Object,
				default: () => {}
			}
		},
		data() {
			return {};
		},
		mounted() {},
		methods: {
			closeDiscount(){
				this.$emit('myevent');
			}
		}
	}
</script>

<style scoped lang="scss">
	.discountList{
		background: #F5F5F5;
	}
	.cartDiscount{
		position: fixed;
		bottom: 0;
		width: 100%;
		left: 0;
		background-color: #fff;
		z-index: 9;
		border-radius: 24rpx 24rpx 0 0;
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
		padding-bottom: 200rpx;
		padding-bottom: calc(200rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(200rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		.title{
			font-size: 32rpx;
			color: #282828;
			text-align: center;
			position: relative;
			background-color: #F5F5F5;
			height: 120rpx;
			line-height: 120rpx;
			border-radius: 24rpx 24rpx 0 0;
			.iconfont{
				position: absolute;
				right: 30rpx;
				top:0;
				font-size: 36rpx;
			}
		}
		.list{
			max-height: 600rpx;
			overflow-x: hidden;
			overflow-y: auto;
			padding-top: 40rpx;
			.discountList{
				width: 692rpx;
				background: #F5F5F5;
				margin: 0 auto;
				border-radius: 12rpx;
				padding: 0 24rpx;
			}
			.coupon{
				height: 70rpx;
				font-size: 24rpx;
			}
			.bottom{
				border-top: 2rpx dotted #EEEEEE;
				margin-top: 30rpx;
				padding-top: 30rpx;
				.total{
					font-size: 30rpx;
					font-weight: 600;
				}
				.money{
					font-size: 36rpx;
					font-weight: 600;
				}
			}
			.item{
				margin: 0 30rpx 30rpx 30rpx;
			}
		}
	}
	.cartDiscount.on{
		transform: translate3d(0, 0, 0);
	}
</style>
