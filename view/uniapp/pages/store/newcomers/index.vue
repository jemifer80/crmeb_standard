<template>
	<view :style="colorStyle" class="pages">
		<view class="header">
			<view class="rule" @click="goRule(info)">规则</view>
			<image src="../static/newcomers.png" mode="" class="img"></image>
			<countDown :is-day="true" :tip-text="'剩余时间：'" :day-text="'天'" :hour-text="'时'" :minute-text="'分'"
				:second-text="''" :isSecond="false" :datatime="info.last_time" v-if="info.last_time"></countDown>
			<view class="list" :class="info.last_time?'':'marginList'">
				<view class="list-item">
					<image src="../static/order1.png" mode="" class="icon"></image>
					<text class="text">赠送积分</text>
					<text class="text">{{info.register_give_integral || 0}}</text>
				</view>
				<view class="list-item">
					<image src="../static/order2.png" mode="" class="icon"></image>
					<text class="text">赠送余额</text>
					<text class="text">{{info.register_give_money || 0}}</text>
				</view>
				<view class="list-item">
					<image src="../static/order3.png" mode="" class="icon"></image>
					<text class="text">优惠券</text>
					<text class="text">{{info.coupon_count || 0}}</text>
				</view>
				<view class="list-item">
					<image src="../static/order4.png" mode="" class="icon"></image>
					<text class="text">首单优惠</text>
					<text class="text">{{parseFloat(info.first_order_discount)/10 || 10}}折</text>
				</view>
				<view class="list-item">
					<image src="../static/order5.png" mode="" class="icon"></image>
					<text class="text">新人商品</text>
					<text class="text">{{info.product_count || 0}}件</text>
				</view>
			</view>
		</view>
		<view class="content" :class="info.last_time?'':'marginCon'">
			<!-- 新人红包 -->
			<view class="red-envelopes" v-if="info.register_give_coupon?info.register_give_coupon.length:false">
				<view class="title">
					<text class="red-text">新人红包</text>
					<text class="text">新人专享红包，优惠不容错过</text>
				</view>
				<scroll-view scroll-x="true" class="scroll" show-scrollbar="false">
					<!-- 优惠券列表 -->
					<view class="scroll-item" :class="item._type==0?'on':''" v-for="(item,index) in info.register_give_coupon" :key="index">
						<image src="../static/box-use.png" v-if="item._type==0" mode="" class="img" />
						<image src="../static/box.png" v-else mode="" class="img" />
						<view class="condition">
							<view class='money font-color'>
								<text v-if="item.coupon_type==1">￥</text>
								<text class='num'
									v-if="item.coupon_type==1">{{item.coupon_price.toString().split(".")[0]}}</text>
								<text class="nums"
									v-if="item.coupon_price.toString().split('.').length>1 && item.coupon_type==1">.{{item.coupon_price.toString().split(".")[1]}}</text>
								<text class='num' v-if="item.coupon_type==2">{{parseFloat(item.coupon_price)/10}}</text>
								<text v-if="item.coupon_type==2">折</text>
							</view>
							<view class="num2" v-if="item.use_min_price > 0">满{{item.use_min_price}}可用</view>
							<view class="num2" v-else>无门槛券</view>
						</view>
						<view class="use" v-if="item._type==0">
							{{item._msg}}
						</view>
						<view class="use" v-else @click="goGoodList">
							{{item._msg}}
							<text class="iconfont icon-xiangyou"></text>
						</view>
					</view>
				</scroll-view>
			</view>
			<!-- 首单优惠 -->
			<view class="first-order">
				<view class="title">
					<text class="red-text">首单优惠</text>
					<text class="text">优惠仅此一次，建议尽快使用</text>
				</view>
				<view class="order acea-row row-middle">
					<view class="pictrue acea-row row-center-wrapper">
						<text class="iconfont icon-shoudanyouhui"></text>
					</view>
					<view class="order-content">
						<view class="order-text">新人首单{{parseFloat(info.first_order_discount)/10 || 10}}折</view>
						<view class="text">新人首次下单立享{{parseFloat(info.first_order_discount)/10 || 10}}折优惠哦～</view>
					</view>
					<view class="btn" @click="goGoodList">
						去逛逛
					</view>
				</view>
			</view>
			<!-- 新人专享价 -->
			<view class="exclusive" v-if="newList.length">
				<view class="title">
					<text class="red-text">新人专享价</text>
					<text class="text">新人专享特价商品，仅限购买一个</text>
				</view>
				<view class="box acea-row row-middle">
				  <view class="list" v-for="(item,index) in newList" :key="index" @click="goDetail(item)">
						<view class="img">
							<image :src="item.image" mode="aspectFill"></image>
						</view>
				    <view class="name line1">
				      {{item.store_name}}
				    </view>
				    <view class="price">¥{{item.price}}</view>
						<view class="y_price">¥{{item.ot_price}}</view>
				  </view>
				</view>
			</view>
			<view class='loadingicon acea-row row-center-wrapper' v-if="newList.length">
				<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
			</view>
		</view>
		<home v-if="navigation"></home>
	</view>
</template>
<script>
	import home from '@/components/home';
	import countDown from '@/components/countDown';
	import colors from "@/mixins/color";
	import {
		newcomerInfo,
		newcomerList
	} from '@/api/new_store.js';
	export default {
		mixins: [colors],
		components: {
			countDown,
			home
		},
		data() {
			return {
				info: {},
				newList:[],
				loading: false,
				loadend: false,
				loadTitle: '加载更多',
				page: 1,
				limit: 9,
			};
		},
		onLoad() {
			this.getNewcomerInfo();
			this.productList();
		},
		onReady() {

		},
		onShow() {

		},
		methods: {
			goDetail(item){
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}&fromPage='newVip'`
				});
			},
			goRule(e){
				let that = this;
				uni.setStorageSync('infos', this.info.newcomer_agreement);
				uni.navigateTo({
					url: `/pages/store/ruleInfo/index`
				});
			},
			getNewcomerInfo() {
				newcomerInfo().then(res => {
					res.data.last_time = parseInt(res.data.last_time);
					this.info = res.data;
				}).catch(err => {
					this.$util.Tips({
						title: err
					});
				})
			},
			productList() {
				let that = this;
				if (that.loading) return;
				if (that.loadend) return;
				that.loading = true;
				that.loadTitle = '';
				newcomerList({
					page: that.page,
					limit: that.limit
				}).then(res=>{
					let list = res.data;
					let loadend = list.length < that.limit;
					that.newList = that.$util.SplitArray(list, that.newList);
					that.$set(that, 'newList', that.newList);
					that.loadend = loadend;
					that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
					that.page = that.page + 1;
					that.loading = false;
				}).catch(err=>{
					that.loading = false;
					that.loadTitle = '加载更多';
					this.$util.Tips({
						title: err
					});
				})
			},
			goGoodList(){
				uni.navigateTo({
					url: `/pages/goods/goods_list/index`
				});
			}
		},
		onReachBottom() {
			this.productList();
		}
	}
</script>

<style lang="scss">
	.marginCon{
		top: 410rpx !important;
	}
	.marginList{
		top: 170rpx !important;
	}
	.box {
	  width: 640rpx;
		margin: 16rpx auto 0 auto;
	
	  .list {
			margin-right: 10rpx;
			margin-top: 20rpx;
			&:nth-of-type(3n){
				margin-right: 0;
			}
	    .img {
	      width: 206rpx;
	      height: 206rpx;
	      border-radius: 11rpx 11rpx 0 0;
				image{
					width: 100%;
					height: 100%;
					border-radius: 11rpx 11rpx 0 0;
				}
	    }
	
	    .name {
	      margin-top: 12rpx;
	      width: 178rpx;
	      font-size: 26rpx;
	      font-weight: 400;
	      color: #333333;
	      overflow: hidden;
	      white-space: nowrap;
	      text-overflow: ellipsis;
	      -o-text-overflow: ellipsis;
	    }
	    .price {
	      margin-top: 8rpx;
	      font-size: 28rpx;
	      font-weight: 600;
	      color: var(--view-theme);
	    }
			.y_price{
				font-weight: 400;
				color: #CCCCCC;
				font-size: 20rpx;
				text-decoration: line-through;
			}
	  }
	}
	/deep/.time {
		.red {
			color: #fff;
		}
		.styleAll {
			width: 36rpx;
			height: 36rpx;
			text-align: center;
			line-height: 36rpx;
			background-color: #fff;
			border-radius: 4rpx;
			color: var(--view-theme);
		}

		.timeTxt {
			margin: 0 12rpx;
		}
	}

	.header {
		width: 100%;
		height: 580rpx;
		background-color: var(--view-theme);
		border-radius: 0rpx 0rpx 100rpx 100rpx;
		position: relative;
		.rule{
			position: absolute;
			background-color: rgba(0,0,0,0.15);
			width: 74rpx;
			height: 34rpx;
			top:20rpx;
			right: 0;
			color: #fff;
			font-size: 20rpx;
			border-radius: 17rpx 0 0 17rpx;
			text-align: center;
			line-height: 34rpx;
			z-index: 9;
		}

		.img {
			width: 100%;
			height: 580rpx;
		}

		.time {
			position: absolute;
			top: 150rpx;
			left: 188rpx;
			font-size: 26rpx;
			font-weight: 400;
			color: #FFFFFF;
			display: flex;

			.time-box {
				width: 36rpx;
				height: 36rpx;
				background: #FFFFFF;
				border-radius: 4rpx;
				color: var(--view-theme);
				text-align: center;
				line-height: 36rpx;
				margin: 0 12rpx;
			}
		}

		.list {
			position: absolute;
			top: 230rpx;
			left: 24rpx;
			width: 702rpx;
			height: 220rpx;
			background: #FFFFFF;
			border-radius: 12rpx;
			display: flex;
			justify-content: space-between;
			padding: 34rpx 38rpx;

			.list-item {
				display: flex;
				flex-direction: column;
				align-items: center;

				.icon {
					width: 86rpx;
					height: 86rpx;
					background: #FDE9BC;
					border-radius: 50%;
					margin-bottom: 10rpx;
				}

				.text {
					font-size: 22rpx;
					font-weight: 400;
					color: #C47C16;
				}
			}
		}

	}

	.content {
		position: absolute;
		top: 470rpx;
		width: 702rpx;
		background: #fff;
		border-radius: 12rpx;
		padding-top: 36rpx;
		padding-bottom: 20rpx;
		left:50%;
		margin-left: -351rpx;

		.title {
			padding: 0 30rpx;
			.red-text {
				font-size: 30rpx;
				font-weight: 600;
				color: var(--view-theme);
			}

			.text {
				margin-left: 12rpx;
				font-size: 22rpx;
				font-weight: 400;
				color: #999999;
			}
		}

		.red-envelopes {
			margin-bottom: 36rpx;
			.scroll {
				white-space: nowrap;
				margin-top: 20rpx;
				height: 150rpx;
				padding-left: 30rpx;

				.scroll-item {
					margin-top: 30rpx;
					display: inline-block;
					width: 174rpx;
					height: 112rpx;
					background: var(--view-theme);
					border-radius: 12px;
					margin-right: 10rpx;
					position: relative;
					text-align: center;
					&.on{
						background: #ccc;
						.condition{
							color: #ccc;
							.money{
								color: #ccc !important;
							}
						}
					}

					.img {
						width: 156rpx;
						height: 88rpx;
						position: absolute;
						left: 10rpx;
						top: -16rpx;
					}

					.condition {
						width: 156rpx;
						height: 88rpx;
						position: absolute;
						top: -13rpx;
						left: 10rpx;
						color: var(--view-theme);

						.money {
							font-size: 20rpx;
						}

						.num {
							font-size: 36rpx;
							font-weight: 600;
						}

						.num2 {
							font-size: 20rpx;
							font-weight: 400;
						}

						.nums {
							font-size: 20rpx;
							font-weight: 600;
						}
					}

					.use {
						margin-top: 75rpx;
						font-size: 20rpx;
						font-family: PingFangSC-Regular, PingFang SC;
						font-weight: 400;
						color: #FFFFFF;

						.icon-xiangyou {
							font-size: 12rpx;
						}
					}
				}

				.use-item {
					border-radius: 0 !important;
					background: none !important;
					margin-top: 30rpx !important;

					.imghh {
						width: 174rpx;
						height: 132rpx;
					}
				}
			}
		}

		.first-order {
			.order {
				width: 642rpx;
				height: 128rpx;
				background: linear-gradient(135deg, var(--view-minorColor) 0%, var(--view-theme) 30%);
				border-radius: 8rpx;
				display: flex;
				margin: 20rpx 30rpx 0 30rpx;

				.pictrue {
					width: 64rpx;
					height: 64rpx;
					background: #FFFFFF;
					border-radius: 50%;
					margin-left: 30rpx;
					.iconfont{
						font-size: 34rpx;
						color: var(--view-theme);
					}
				}

				.order-content {
					margin-left: 20rpx;
					color: #FFFFFF;

					.order-text {
						font-size: 28rpx;
						font-weight: 500;

					}

					.text {
						font-size: 22rpx;
						font-weight: 400;

					}
				}
			}

			.btn {
				width: 132rpx;
				height: 48rpx;
				background: #FFFFFF;
				border-radius: 24rpx;
				font-size: 26rpx;
				font-weight: 400;
				line-height: 48rpx;
				text-align: center;
				color: var(--view-theme);
				margin-left: 40rpx;
			}
		}

		// 新人专享
		.exclusive {
			margin-top: 36rpx;

		}
	}
</style>
