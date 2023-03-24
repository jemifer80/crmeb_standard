<template>
<view v-if="pageShow" class="page"
		:class="bgTabVal==2?'fullsize noRepeat':bgTabVal==1?'repeat ysize':'noRepeat ysize'"
		:style="'background-color:'+bgColor+';background-image: url('+bgPic+');min-height:'+windowHeight+'px;'">	
	<view :style="{ marginTop: sortMpTop + 'px' }">
		<!-- #ifdef H5 -->
		<view v-for="(item, index) in styleConfig" :key="index">
			<component :is="item.name" :index="index" :dataConfig="item" @changeBarg="changeBarg" @changeLogin="changeLogin"
				@detail="goDetail"></component>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP || APP-PLUS-->
		<block v-for="(item, index) in styleConfig" :key="index">
			<homeComb v-if="item.name == 'homeComb'" :dataConfig="item" @bindSortId="bindSortId" :isScrolled="isScrolled" :special="1"></homeComb>
			<shortVideo v-if="item.name == 'shortVideo'" :dataConfig="item" :isSortType="isSortType">
			</shortVideo>
			<userInfor v-if="item.name == 'userInfor'" :dataConfig="item" :isSortType="isSortType" @changeLogin="changeLogin">
			</userInfor>
			<newVip v-if="item.name == 'newVip'" :dataConfig="item" :isSortType="isSortType">
			</newVip>
			<activeParty v-if="item.name == 'activeParty'" :dataConfig="item"></activeParty>
			<articleList v-if="item.name == 'articleList'" :dataConfig="item"></articleList>
			<bargain v-if="item.name == 'bargain'" :dataConfig="item" @changeBarg="changeBarg"></bargain>
			<blankPage v-if="item.name == 'blankPage'" :dataConfig="item"></blankPage>
			<combination v-if="item.name == 'combination'" :dataConfig="item"></combination>
			<coupon v-if="item.name == 'coupon'" :dataConfig="item" @changeLogin="changeLogin"></coupon>
			<customerService v-if="item.name == 'customerService'" :dataConfig="item"></customerService>
			<goodList v-if="item.name == 'goodList'" :dataConfig="item" @detail="goDetail"></goodList>
			<guide v-if="item.name == 'guide'" :dataConfig="item"></guide>
			<headerSerch v-if="item.name == 'headerSerch'" :dataConfig="item" :special="1"></headerSerch>
			<liveBroadcast v-if="item.name == 'liveBroadcast'" :dataConfig="item"></liveBroadcast>
			<menus v-if="item.name == 'menus'" :dataConfig="item"></menus>
			<news v-if="item.name == 'news'" :dataConfig="item"></news>
			<pictureCube v-if="item.name == 'pictureCube'" :dataConfig="item" :isSortType="isSortType">
			</pictureCube>
			<promotionList v-if="item.name == 'promotionList'" :dataConfig="item" @detail="goDetail"></promotionList>
			<richText v-if="item.name == 'richText'" :dataConfig="item"></richText>
			<seckill v-if="item.name == 'seckill'" :dataConfig="item"></seckill>
			<swiperBg v-if="item.name == 'swiperBg'" :dataConfig="item"></swiperBg>
			<swipers v-if="item.name == 'swipers'" :dataConfig="item"></swipers>
			<tabNav v-if="item.name == 'tabNav'" :dataConfig="item" :fromType="1"></tabNav>
			<titles v-if="item.name == 'titles'" :dataConfig="item"></titles>
		</block>
		<!-- #endif -->
	<!-- 	<view class="loadingicon acea-row row-center-wrapper"
			v-if="tempArr.length && styleConfig[styleConfig.length - 1].name == 'promotionList'">
			<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
			{{ loadTitle }}
		</view> -->
		<pageFooter></pageFooter>
		<!-- #ifdef MP -->
		  <authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		  <!-- #endif -->
	</view>
</view>	
</template>

<script>
	const app = getApp();
	import couponWindow from '@/components/couponWindow/index'
	import {
		getCouponV2,
		getCouponNewUser
	} from '@/api/api.js'
	import {
		getShare
	} from '@/api/public.js';
	// #ifdef H5
	import mConfig from '@/pages/index/components/index.js';
	import {
		silenceAuth
	} from '@/api/public.js';
	// #endif
	// #ifdef MP || APP-PLUS
	import homeComb from '@/pages/index/components/homeComb';
	import userInfor from '@/pages/index/components/userInfor';
	import newVip from '@/pages/index/components/newVip';
	import shortVideo from '@/pages/index/components/shortVideo';
	import activeParty from '@/pages/index/components/activeParty';
	import headerSerch from '@/pages/index/components/headerSerch';
	import swipers from '@/pages/index/components/swipers';
	import coupon from '@/pages/index/components/coupon';
	import articleList from '@/pages/index/components/articleList';
	import bargain from '@/pages/index/components/bargain';
	import blankPage from '@/pages/index/components/blankPage';
	import combination from '@/pages/index/components/combination';
	import customerService from '@/pages/index/components/customerService';
	import goodList from '@/pages/index/components/goodList';
	import guide from '@/pages/index/components/guide';
	import liveBroadcast from '@/pages/index/components/liveBroadcast';
	import menus from '@/pages/index/components/menus';
	import news from '@/pages/index/components/news';
	import promotionList from '@/pages/index/components/promotionList';
	import richText from '@/pages/index/components/richText';
	import seckill from '@/pages/index/components/seckill';
	import swiperBg from '@/pages/index/components/swiperBg';
	import tabNav from '@/pages/index/components/tabNav';
	import titles from '@/pages/index/components/titles';
	import pictureCube from '@/pages/index/components/pictureCube';

	import {
		getTemlIds
	} from '@/api/api.js';
	import {
		SUBSCRIBE_MESSAGE,
		TIPS_KEY
	} from '@/config/cache';

	// #endif
	import {
		mapGetters
	} from 'vuex';
	import {
		getDiy,
		getIndexData,
		getDiyVersion
	} from '@/api/api.js';
	import {
		goShopDetail
	} from '@/libs/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import pageFooter from '@/components/pageFooter/index.vue'
	export default {
		computed: mapGetters(['isLogin', 'uid']),
		components: {
			pageFooter,
			couponWindow,
			// #ifdef H5
			...mConfig,
			// #endif
			// #ifdef MP || APP-PLUS
			homeComb,
			newVip,
			userInfor,
			shortVideo,
			activeParty,
			headerSerch,
			swipers,
			coupon,
			articleList,
			bargain,
			blankPage,
			combination,
			customerService,
			goodList,
			guide,
			liveBroadcast,
			menus,
			news,
			promotionList,
			richText,
			seckill,
			swiperBg,
			tabNav,
			titles,
			pictureCube
			// #endif
		},
		data() {
			return {
				styleConfig: [],
				loading: false,
				loadend: false,
				loadTitle: '加载更多', //提示语
				page: 1,
				limit: this.$config.LIMIT,
				numConfig: 0,
				code: '',
				isCouponShow: false,
				couponObj: {},
				couponObjs: {},
				shareInfo: {},
				footConfig: {},
				pageId: '',
				sortMpTop: 0,
				bgColor: '',
				bgPic: '',
				bgTabVal: '',
				pageShow: true,
				windowHeight: 0,
				isShowAuth: false
			};
		},
		onLoad(options) {
			let that = this
			this.$nextTick(function() {
				uni.getSystemInfo({
					success: function(res) {
						that.windowHeight = res.windowHeight;
					}
				});
			})
			const {
				state,
				scope
			} = options;
			this.pageId = options.id
			// #ifdef MP
			if (options.scene) {
				let value = that.$util.getUrlParams(decodeURIComponent(options.scene));
				this.pageId = value.id
			}
			// #endif
			uni.setNavigationBarTitle({
				title: '专题栏'
			});
			
			// #ifdef APP-PLUS
			this.sortMpTop = -50
			// #endif
			this.diyData();
			this.getIndexData();
			// #ifdef H5
			this.setOpenShare();
			// #endif
			// #ifdef MP || APP-PLUS
			this.getTemlIds();
			// #endif
			getShare().then(res => {
				this.shareInfo = res.data;
			})
		},
		watch: {
			isLogin: {
				deep: true, //深度监听设置为 true
				handler: function(newV, oldV) {
					// 优惠券弹窗
					var newDates = new Date().toLocaleDateString();
					if (newV) {
						try {
							var oldDate = uni.getStorageSync('oldDate') || ''
						} catch {}
						if (oldDate != newDates) {
							this.getCoupon();

						}
					}
				}
			}
		},
		onShow() {
			uni.removeStorageSync('form_type_cart');
			// 优惠券弹窗
			var newDates = new Date().toLocaleDateString();
			if (this.isLogin) {
				try {
					var oldDate = uni.getStorageSync('oldDate') || ''
				} catch {}
				if (oldDate != newDates) {
					this.getCoupon();
				}
				let oldUser = uni.getStorageSync('oldUser') || 0;
				if (!oldUser) {
					this.getCouponOnce();
				}
			}
		},
		mounted() {},
		methods: {
			onLoadFun() {
				this.isShowAuth = false
			},
			authColse: function(e) {
			  this.isShowAuth = e
			},
			// 新用户优惠券
			getCouponOnce() {
				getCouponNewUser().then(res => {
					this.couponObjs = res.data;
				});
			},
			couponCloses() {
				this.couponObjs.show = false;
				try {
					uni.setStorageSync('oldUser', 1);
				} catch (e) {

				}
			},
			// 优惠券弹窗
			getCoupon() {
				getCouponV2().then(res => {
					this.couponObj = res.data
					if (res.data.list.length > 0) {
						this.isCouponShow = true
					}
				})
			},
			// 优惠券弹窗关闭
			couponClose() {
				this.isCouponShow = false
				try {
					uni.setStorageSync('oldDate', new Date().toLocaleDateString());
				} catch {}
			},
			// #ifdef H5
			// 获取url后面的参数
			getQueryString(name) {
				var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
				var reg_rewrite = new RegExp("(^|/)" + name + "/([^/]*)(/|$)", "i");
				var r = window.location.search.substr(1).match(reg);
				var q = window.location.pathname.substr(1).match(reg_rewrite);
				if (r != null) {
					return unescape(r[2]);
				} else if (q != null) {
					return unescape(q[2]);
				} else {
					return null;
				}
			},
			// #endif

			// #ifdef MP || APP-PLUS
			getTemlIds() {
				let messageTmplIds = wx.getStorageSync(SUBSCRIBE_MESSAGE);
				if (!messageTmplIds) {
					getTemlIds().then(res => {
						if (res.data) wx.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
					});
				}
			},
			// #endif
			// 对象转数组
			objToArr(data) {
				const keys = Object.keys(data)
				keys.sort((a, b) => a - b)
				const m = keys.map(key => data[key]);
				return m;
			},
			setDiyData(data) {
				// let data = res.data;
				if (data.length == 0) {
					return this.$util.Tips({
						title: '暂无数据'
					}, {
						tab: 3
					})
				}
				
				if (data.is_bg_color) {
					this.bgColor = data.color_picker
				}
				if (data.is_bg_pic) {
					this.bgPic = data.bg_pic
					this.bgTabVal = data.bg_tab_val
				}
				this.pageShow = data.is_show
				uni.setNavigationBarTitle({
					title: data.title
				})
				let temp = []
				let lastArr = this.objToArr(data.value)
				lastArr.forEach((item, index, arr) => {
					if (item.name == 'pageFoot') {
						uni.setStorageSync('pageFoot', item)
						this.$store.commit('FOOT_UPLOAD', item)
						arr.splice(index, 1)
					}
					temp = arr
				});
				this.styleConfig = temp;
			},
			getDiyData() {
				getDiy(this.pageId).then(res => {
					uni.setStorageSync('specialDiyData', JSON.stringify(res.data));
					this.setDiyData(res.data);
				});
			},
			diyData() {
				let that = this;
				let diyData = uni.getStorageSync('specialDiyData');
				if (diyData) {
					getDiyVersion(this.pageId).then(res => {
						let diyVersion = uni.getStorageSync('specialDiyVersion');
						if ((res.data.version + this.pageId) === diyVersion) {
							this.setDiyData(JSON.parse(diyData));
						} else{
							uni.setStorageSync('specialDiyVersion', (res.data.version + this.pageId));
							this.getDiyData();
						}
					});
				} else{
					this.getDiyData();
				}
				// getDiy(this.pageId).then(res => {
				// 	let data = res.data;
				// 	if (res.data.length == 0) {
				// 		return this.$util.Tips({
				// 			title: '暂无数据'
				// 		}, {
				// 			tab: 3
				// 		})
				// 	}
					
				// 	if (data.is_bg_color) {
				// 		this.bgColor = data.color_picker
				// 	}
				// 	if (data.is_bg_pic) {
				// 		this.bgPic = data.bg_pic
				// 		this.bgTabVal = data.bg_tab_val
				// 	}
				// 	this.pageShow = data.is_show
				// 	uni.setNavigationBarTitle({
				// 		title: res.data.title
				// 	})
				// 	let temp = []
				// 	let lastArr = that.objToArr(res.data.value)
				// 	lastArr.forEach((item, index, arr) => {
				// 		if (item.name == 'pageFoot') {
				// 			uni.setStorageSync('pageFoot', item)
				// 			that.$store.commit('FOOT_UPLOAD', item)
				// 			arr.splice(index, 1)
				// 		}
				// 		temp = arr
				// 	});
				// 	that.styleConfig = temp;
				// });
			},
			getIndexData() {
			},
			changeLogin(){
				this.getIsLogin();
			},
			getIsLogin(){
				// #ifndef MP
				toLogin()
				// #endif
				// #ifdef MP
				this.isShowAuth = true;
				// #endif
			},
			changeBarg(item) {
				if (!this.isLogin) {
					this.getIsLogin();
				} else {
					uni.navigateTo({
						url: `/pages/activity/goods_bargain_details/index?id=${item.id}&spid=${this.uid}`
					});
				}
			},
			goDetail(item) {
				goShopDetail(item, this.uid).then(res => {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${item.id}`
					});
				});

			},
			// #ifdef H5
			// 微信分享；
			setOpenShare: function() {
				let that = this;
				if (that.$wechat.isWeixin()) {
					getShare().then(res => {
						let data = res.data.data;
						let configAppMessage = {
							desc: data.synopsis,
							title: data.title,
							link: location.href,
							imgUrl: data.img
						};
						that.$wechat.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData'],
							configAppMessage);
					});
				}
			}
			// #endif
		},
		onReachBottom: function() {
		},
		//#ifdef MP || APP-PLUS
		onShareAppMessage() {
			return {
				title: this.shareInfo.title,
				path: '/pages/index/index',
				imageUrl: this.storeInfo.img,
			};
		},
		//#endif
	};
</script>

<style lang="scss">
	.page {
		padding-bottom: 50px;
	}
	
	.ysize {
		background-size: 100%;
	}
	
	.fullsize {
		background-size: 100% 100%;
	}
	
	.repeat {
		background-repeat: repeat;
	}
	
	.noRepeat {
		background-repeat: no-repeat;
	}
	
</style>