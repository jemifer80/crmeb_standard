<template>
	<!-- 商品属性规格弹窗 -->
	<view>
		<view class="product-window">
			<view class="textpic">
				<image class="pictrue" :src="attr.productSelect.image"></image>
				<view class="text">
					<text class="name line1" v-if="attr.productSelect.store_name && attr.productSelect.store_name.length>=15">{{ type == 'setMeal'?title : attr.productSelect.store_name.slice(0,15)}}...</text>
					<text class="name line1" v-else>{{type == 'setMeal'?title : attr.productSelect.store_name?attr.productSelect.store_name.slice(0,15):''}}</text>
					<view class="money font-color">
						<view class="top">
							<text class="lable" v-if="type != 'points'">￥</text>
							<text class="num">{{ attr.productSelect.price }}</text>
							<text v-if="type == 'points'" class="num">积分</text>
							<text class='vip-money' v-if="is_vip>0 && attr.productSelect.vip_price && storeInfo && storeInfo.price_type == 'member'">￥{{attr.productSelect.vip_price}}</text>
							<image class="vipImg" v-if="is_vip>0 && attr.productSelect.vip_price && storeInfo && storeInfo.price_type == 'member'" src="@/static/images/vip.png" mode=""></image>
							<view class="icon" v-if="is_vip>0 && attr.productSelect.vip_price && storeInfo && storeInfo.price_type == 'level'">
								 <image class="level" :src="imgHost + '/statics/images/level.png'"></image>
								 <text class="levelName">{{storeInfo.level_name}}</text>
							</view>
						</view>
						<text class="stock" v-if='isShow'>库存: {{ attr.productSelect.stock }}</text>
						<text class='stock' v-if="limitNum">{{type == 'seckill' ? '限量' : type == 'points'?'剩余':'库存'}}: {{attr.productSelect.quota}}</text>
						<slot name="bottom" :attr="attr"></slot>
					</view>
				</view>
				<image class="close" @click="closeAttr" :src="imgHost + '/statics/images/close.png'"></image>
			</view>
			<view class="rollTop">
				<scroll-view :style="'width:100%; max-height: 600rpx; display: flex; flex-direction: column;'" :scroll-y="true">
					<view class="productWinList">
						<view class="item" v-for="(item, indexw) in attr.productAttr" :key="indexw">
							<text class="title">{{ item.attr_name }}</text>
							<view class="listn">
								<text class="itemn" :class="item.index === itemn.attr ? 'on' : ''"
									v-for="(itemn, indexn) in item.attr_value" @click="tapAttr(indexw, indexn)"
									:key="indexn">
									{{ itemn.attr }}
								</text>
							</view>
						</view>
					</view>
					<view class="cart" v-if="type != 'setMeal'">
						<text class="title">数量</text>
						<view class="carnum">
							<text class="item reduce"
								:class="attr.productSelect.cart_num <= 1 ? 'on' : ''" v-if="attr.productSelect.cart_num <= 1">
								-
							</text>
							<text class="item reduce"
								:class="attr.productSelect.cart_num <= 1 ? 'on' : ''" @click="CartNumDes" v-else>
								-
							</text>
							<view class='item'>
								<input class="num" type="number" v-model="attr.productSelect.cart_num"
									data-name="productSelect.cart_num"
									@input="bindCode(attr.productSelect.cart_num)"></input>
							</view>
							<text v-if="iSplus" class="item plus" :class="
					      attr.productSelect.cart_num >= attr.productSelect.stock
					        ? 'on'
					        : ''
					    " @click="CartNumAdd">
								+
							</text>
							<text v-else class='item plus'
								:class='(attr.productSelect.cart_num >= attr.productSelect.quota) || (attr.productSelect.cart_num >= attr.productSelect.product_stock) || (attr.productSelect.cart_num >= attr.productSelect.num) || (type=="seckill" && attr.productSelect.cart_num >= attr.productSelect.once_num)? "on":""'
								@click='CartNumAdd'>+</text>
						</view>
					</view>
				</scroll-view>
			</view>
			<view class="bntCon">
				<text class="joinBnt bg-color" v-if="iScart && attr.productSelect.stock>0" @click="goCat">确定</text>
				<text class="joinBnt on" v-else-if="iScart && !attr.productSelect.stock">已售罄</text>
			</view>
		</view>
	</view>
</template>

<script>
	import { HTTP_REQUEST_URL } from '@/config/app';
	export default {
		props: {
			cusPreviewImg: {
				type: Number,
				value: 0
			},
			title: {
				type: String,
				default: ''
			},
			attr: {
				type: Object,
				default: () => {}
			},
			storeInfo: {
				type: Object,
				default: () => {}
			},
			limitNum: {
				type: Number,
				value: 0
			},
			isShow: {
				type: Number,
				value: 0
			},
			iSbnt: {
				type: Number,
				value: 0
			},
			iSplus: {
				type: Number,
				value: 0
			},
			iScart: {
				type: Number,
				value: 0
			},
			is_vip: {
				type: Number,
				value: 0
			},
			type: {
				type: String,
				default: ''
			},
		},
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
			};
		},
		mounted() {},
		methods: {
			goCat: function() {
				this.$emit('goCat');
			},
			/**
			 * 购物车手动输入数量
			 * 
			 */
			bindCode: function(e) {
				this.$emit('iptCartNum', this.attr.productSelect.cart_num);
			},
			closeAttr: function() {
				this.$emit('closeScrollview');
			},
			CartNumDes: function() {
				this.$emit('ChangeCartNum', false);
			},
			CartNumAdd: function() {
				this.$emit('ChangeCartNum', true);
			},
			tapAttr: function(indexw, indexn) {
				let that = this;
				that.$emit("attrVal", {
					indexw: indexw,
					indexn: indexn
				});
				this.$set(this.attr.productAttr[indexw], 'index', this.attr.productAttr[indexw].attr_values[indexn]);
				let value = that
					.getCheckedValue()
					.join(",");
				that.$emit("ChangeAttr", value);

			},
			//获取被选中属性；
			getCheckedValue: function() {
				let productAttr = this.attr.productAttr;
				let value = [];
				for (let i = 0; i < productAttr.length; i++) {
					for (let j = 0; j < productAttr[i].attr_values.length; j++) {
						if (productAttr[i].index === productAttr[i].attr_values[j]) {
							value.push(productAttr[i].attr_values[j]);
						}
					}
				}
				return value;
			}
		}
	}
</script>

<style scoped lang="scss">
	.bg-color{
		background-color: #e93323 !important;
	}
	.vip-money {
		color: #282828;
		font-size: 28rpx;
		font-weight: 700;
		margin-left: 6rpx;
	}

	.vipImg {
		width: 56rpx;
		height: 20rpx;
		margin-left: 6rpx;
	}
	
	.bntCon{
		flex-direction: row;
		justify-content: center;
		margin-bottom: 20rpx;
	}
	
	.rollTop{
		margin-top: 30rpx;
	}
	
	.product-window.join {
		padding-bottom: 30rpx;
	}

	.product-window.joinCart {
		padding-bottom: 30rpx;
		z-index: 10000;
	}

	.product-window .textpic {
		padding: 0 130rpx 0 30rpx;
		margin-top: 29rpx;
		position: relative;
		display: flex;
		flex-direction: row;
		justify-content: space-between;
	}

	.product-window .textpic .pictrue {
		width: 150rpx;
		height: 150rpx;
		border-radius: 10rpx;
	}

	.product-window .textpic .text {
		width: 410rpx;
	}
	
	.product-window .textpic .text .name{
		font-size: 32rpx;
		color: #202020;
	}

	.product-window .textpic .text .money {
		margin-top: 34rpx;
		.top{
			display: flex;
			flex-direction: row;
			align-items: center;
			.lable{
				color: #e93323;
				font-size: 24rpx;
			}
		}
		.icon{
			display: flex;
			background: #FF9500;
			border-radius: 18rpx;
			flex-direction: row;
			justify-content: center;
			align-items: center;
			margin-left: 10rpx;
			padding: 2rpx 5rpx;
			.levelName{
				font-size: 16rpx;
				font-weight: normal;
				color: #fff;
			}
			.level{
				width: 25rpx;
				height: 25rpx;
				margin-left: 4rpx;
			}
		}
	}

	.product-window .textpic .text .money .num {
		font-size: 36rpx;
		color: #e93323;
	}

	.product-window .textpic .text .money .stock {
		color: #999;
		margin-left: 6rpx;
		font-size: 28rpx;
	}

  .product-window .textpic .close{
		position: absolute;
		right: 30rpx;
		width: 30rpx;
		height: 30rpx;
	}
	
	.item{
		background-color: transparent;
	}

	.product-window .productWinList .item~.item {
		margin-top: 36rpx;
	}

	.product-window .productWinList .item .title {
		font-size: 30rpx;
		color: #999;
		padding: 0 30rpx;
	}

	.product-window .productWinList .item .listn {
		padding: 0 30rpx 0 16rpx;
		flex-direction: row;
		align-items: center;
		flex-wrap: wrap;
	}

	.product-window .productWinList .item .listn .itemn {
		border: 1px solid #F2F2F2;
		font-size: 26rpx;
		color: #282828;
		padding: 7rpx 33rpx;
		border-radius: 25rpx;
		margin: 20rpx 0 0 14rpx;
		background-color: #F2F2F2;
	}

	.product-window .productWinList .item .listn .itemn.on {
		color: #e93323;
		background: rgba(233, 51, 35, 0.1);
		border-color: #e93323;
	}

	.product-window .productWinList .item .listn .itemn.limit {
		color: #999;
		text-decoration: line-through;
	}

	.product-window .cart {
		margin-top: 36rpx;
		padding: 0 30rpx;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
	}

	.product-window .cart .title {
		font-size: 30rpx;
		color: #999;
	}

	.product-window .cart .carnum {
		height: 54rpx;
		margin-top: 24rpx;
		flex-direction: row;
		justify-content: flex-start;
	}

	.product-window .cart .carnum .iconfont {
		font-size: 25rpx;
	}

	.product-window .cart .carnum .item {
		width: 84rpx;
		text-align: center;
		height: 100%;
		line-height: 54rpx;
		color: #282828;
		font-size: 45rpx;
	}

	.product-window .cart .carnum .reduce {
		border-right: 0;
		border-radius: 6rpx 0 0 6rpx;
		line-height: 48rpx;
		font-size: 60rpx;
		flex-direction: row;
		align-items: center;
		justify-content: center;
		margin-right: 10rpx;
		background-color: transparent;
	}

	.product-window .cart .carnum .reduce.on {
		color: #DEDEDE;
	}

	.product-window .cart .carnum .plus {
		border-left: 0;
		border-radius: 0 6rpx 6rpx 0;
		line-height: 46rpx;
		flex-direction: row;
		align-items: center;
		justify-content: center;
		font-size: 50rpx;
		margin-left: 10rpx;
		background-color: transparent;
	}

	.product-window .cart .carnum .plus.on {
		color: #dedede;
	}

	.product-window .cart .carnum .num {
		background: rgba(242, 242, 242, 1);
		color: #282828;
		font-size: 28rpx;
		flex-direction: row;
		align-items: center;
		width: 84rpx;
		height: 54rpx;
		text-align: center;
		line-height: 54rpx;
	}

	.product-window .joinBnt {
		font-size: 30rpx;
		width: 620rpx;
		height: 86rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 86rpx;
		color: #fff;
		margin-top: 21rpx;
	}

	.product-window .joinBnt.on {
		background-color: #bbb;
		color: #fff;
	}
</style>
