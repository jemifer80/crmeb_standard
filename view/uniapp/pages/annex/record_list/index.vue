<template>
	<view class="record" :style="colorStyle">
		<timeSlot @changeTime="changeTime"></timeSlot>
		<view class="list" v-if="list.length">
			<view class="item acea-row row-between row-top" v-for="(item,index) in list" :key="index">
				<view class="acea-row row-top">
					<text class="icon iconfont icon-xiaofeijilu1" v-if="item.type==1"></text>
					<text class="icon iconfont icon-fufeihuiyuan1" v-else-if="item.type==7"></text>
					<text class="icon iconfont icon-xiaofeijilu-rongcuo" v-else></text>
					<view class="txt">
						<view class="line1">{{item.title}}</view>
						<view class="exp record">{{item.type_name}}</view>
						<view class="time">{{item.day}}</view>
					</view>
				</view>
				<view class="num">-{{item.price}}</view>
			</view>
		</view>
		<view class="list" v-else>
			<emptyPage title="暂无消费记录～"></emptyPage>
		</view>
		<view class='loadingicon acea-row row-center-wrapper' v-if="list.length">
			<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
		</view>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import {
		moneyList
	} from '@/api/user.js';
	import emptyPage from '@/components/emptyPage.vue'
	import timeSlot from '@/components/timeSlot/index.vue';
	import colors from '@/mixins/color.js';
	export default {
		components: {
			timeSlot,
			emptyPage
		},
		mixins: [colors],
		computed: mapGetters(['isLogin']),
		data(){
			return {
				page: 1,
				limit: 15,
				start: 0,
				stop: 0,
				loading: false,
				loadend: false,
				loadTitle: '加载更多',
				list:[],
				isShowAuth: false
			}
		},
		onLoad(){
			if (this.isLogin) {
				this.getUserBillList();
			}
		},
		onShow(){
			if(!this.isLogin){
				// #ifndef MP
				toLogin()
				// #endif
				// #ifdef MP
				this.isShowAuth = true;
				// #endif
			}
		},
		methods:{
			onLoadFun(){
				this.getUserBillList();
				this.isShowAuth = false;
			},
			authColse: function(e) {
			  this.isShowAuth = e
			},
			changeTime(time) {
				this.start = time.start
				this.stop = time.stop
				this.page = 1;
				this.loadend = false;
				this.list = [];
				this.getUserBillList()
			},
			getUserBillList(){
				let that = this;
				if (that.loading) return;
				if (that.loadend) return;
				that.loading = true;
				that.loadTitle = '';
				moneyList({
					page: this.page,
					limit: this.limit,
					start: this.start,
					stop: this.stop
				},9).then(res=>{
					let list = res.data.list;
					let loadend = list.length < that.limit;
					that.list = that.$util.SplitArray(list, that.list);
					that.$set(that, 'list', that.list);
					that.loadend = loadend;
					that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
					that.page = that.page + 1;
					that.loading = false;
				}).catch(err=>{
					that.loading = false;
					that.loadTitle = '加载更多';
				})
			}
		},
		onReachBottom: function() {
			this.getUserBillList();
		}
	}
</script>

<style lang="scss">
	.record{
		.list{
			width: 690rpx;
			background-color: #fff;
			border-radius: 14rpx;
			margin: 20rpx auto 0 auto;
			padding-top: 42rpx;
			.item{
				// padding: 0 24rpx 26rpx 24rpx;
				// position: relative;
				// margin-bottom: 34rpx;
				.icon{
					font-size: 72rpx;
					color: #E7C993;
				}
				.num{
					color: #282828;
					font-size: 32rpx;
					font-weight: 600;
				}
				.pictrue{
					width: 76rpx;
					height: 76rpx;
					background-color: #FDF8EE;
					border-radius: 50%;
					.iconfont{
						color: #F7B942;
					}
				}
				.txt{
					font-weight: 400;
					color: #282828;
					font-size: 28rpx;
					margin-left: 24rpx;
					.line1{
						width: 320rpx;
					}
					.exp{
						color: #999999;
						font-size: 22rpx;
						margin-top: 2rpx;
						&.record{
							margin-top: 8rpx;
						}
						image{
							color: #999999;
							margin-right: 10rpx;
							width: 22rpx;
							height: 22rpx;
						}
					}
					.time{
						color: #999999;
						font-size: 22rpx;
						margin-top: 8rpx;
					}
				}
				.bnt{
					width: 112rpx;
					height: 44rpx;
					background: #F4DBAB;
					border-radius: 26rpx;
					color: #755214;
					font-size: 24rpx;
				}
				&::before{
					position: absolute;
					content: '';
					width: 542rpx;
					height: 1px;
					background: #EEEEEE;
					bottom: 0rpx;
					right: 24rpx;
				}
			}
		}
	}
</style>
