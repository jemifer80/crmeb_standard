<template>
  <!-- 首页 -->
	<view v-if="pageShow" class="page"
		:class="bgTabVal==2?'fullsize noRepeat':bgTabVal==1?'repeat ysize':'noRepeat ysize'"
		:style="'background-color:'+bgColor+';background-image: url('+bgPic+');min-height:'+windowHeight+'px;'">
		<view v-if="!errorNetwork" :style="colorStyle">
			<skeletons v-if="showSkeleton"></skeletons>
			<view class="index">
				<!-- #ifdef H5 -->
				<view v-for="(item, index) in styleConfig" :key="index">
					<component :is="item.name" :index="index" :dataConfig="item" @changeBarg="changeBarg"
						@detail="goDetail" :isSortType="isSortType" @bindSortId="bindSortId" @changeLogin="changeLogin" 
						@bindHeight="bindHeight" :isFixed="isFixed" :productVideoStatus='product_video_status' :isScrolled="isScrolled">
					</component>
				</view>
				<!-- #endif -->
				
				<!-- #ifdef MP || APP-PLUS -->
        <!-- 自定义样式 -->
				<block v-for="(item, index) in styleConfig" :key="index">
					<homeComb v-if="item.name == 'homeComb'" :dataConfig="item" @bindSortId="bindSortId" :isScrolled="isScrolled"></homeComb>
					<shortVideo v-if="item.name == 'shortVideo'" :dataConfig="item" :isSortType="isSortType">
					</shortVideo>
					<userInfor v-if="item.name == 'userInfor'" :dataConfig="item" :isSortType="isSortType" @changeLogin="changeLogin">
					</userInfor>
					<newVip v-if="item.name == 'newVip'" :dataConfig="item" :isSortType="isSortType">
					</newVip>
					<activeParty v-if="item.name == 'activeParty'" :dataConfig="item" :isSortType="isSortType">
					</activeParty>
          <!-- 文章列表 -->
					<articleList v-if="item.name == 'articleList'" :dataConfig="item" :isSortType="isSortType">
					</articleList>
					<bargain v-if="item.name == 'bargain'" :dataConfig="item" @changeBarg="changeBarg"
						:isSortType="isSortType"></bargain>
					<blankPage v-if="item.name == 'blankPage'" :dataConfig="item" :isSortType="isSortType"></blankPage>
					<combination v-if="item.name == 'combination'" :dataConfig="item" :isSortType="isSortType">
					</combination>
          <!-- 优惠券 -->
					<coupon v-if="item.name == 'coupon'" :dataConfig="item" :isSortType="isSortType" @changeLogin="changeLogin"></coupon>
          <!-- 客户服务 -->
					<customerService v-if="item.name == 'customerService'" :dataConfig="item" :isSortType="isSortType">
					</customerService>
          <!-- 商品列表 -->
					<goodList v-if="item.name == 'goodList'" :dataConfig="item" @detail="goDetail"
						:isSortType="isSortType"></goodList>
					<guide v-if="item.name == 'guide'" :dataConfig="item" :isSortType="isSortType"></guide>
          <!-- 顶部搜索框 -->
					<headerSerch v-if="item.name == 'headerSerch'" :dataConfig="item"></headerSerch>
          <!-- 直播模块 -->
					<liveBroadcast v-if="item.name == 'liveBroadcast'" :dataConfig="item" :isSortType="isSortType">
					</liveBroadcast>
					<menus v-if="item.name == 'menus'" :dataConfig="item" :isSortType="isSortType"></menus>
          <!-- 实时消息 -->
					<news v-if="item.name == 'news'" :dataConfig="item" :isSortType="isSortType"></news>
          <!-- 图片库 -->
					<pictureCube v-if="item.name == 'pictureCube'" :dataConfig="item" :isSortType="isSortType">
					</pictureCube>
          <!-- 促销列表 -->
					<promotionList v-if="item.name == 'promotionList'" :dataConfig="item" @detail="goDetail" :isSortType="isSortType" :productVideoStatus='product_video_status'>
					</promotionList>
					<richText v-if="item.name == 'richText'" :dataConfig="item" :isSortType="isSortType"></richText>
					<seckill v-if="item.name == 'seckill'" :dataConfig="item" :isSortType="isSortType"></seckill>
          <!-- 轮播图-->
					<swiperBg v-if="item.name == 'swiperBg'" :dataConfig="item" :isSortType="isSortType"></swiperBg>
					<swipers v-if="item.name == 'swipers'" :dataConfig="item" :isSortType="isSortType"></swipers>
          <!-- 顶部选项卡 -->
					<tabNav v-if="item.name == 'tabNav'" :dataConfig="item" @bindHeight="bindHeighta"
						@bindSortId="bindSortId" :isFixed="isFixed"></tabNav>
            <!-- 标题 -->
					<titles v-if="item.name == 'titles'" :dataConfig="item" :isSortType="isSortType"></titles>
				</block>
				<!-- #endif -->
				<!-- 分类商品模块 -->
				<!-- #ifdef  APP-PLUS -->
				<view class="sort-product" v-if="isSortType == 1" style="margin-top: 0;">
					<scroll-view scroll-x="true" class="sort-scroll">
						<view class="sort-box" v-if="sortList.children && sortList.children.length">
							<view class="sort-item" v-for="(item, index) in sortList.children" :key="index"
								@click="changeSort(item, index)" :class="{ on: curSort == index }">
								<image :src="item.pic" mode="" v-if="item.pic"></image>
								<image src="/static/images/sort-img.png" mode="" v-else></image>
								<view class="txt">{{ item.cate_name }}</view>
							</view>
						</view>
					</scroll-view>
          <!-- 首页商品列表 -->
					<view class="product-list" v-if="goodList.length">
						<view class="product-item" v-for="(item, index) in goodList" @click="goGoodsDetail(item)">
							<image :src="item.image" mode="aspectFill"></image>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '1'">秒杀</span>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '2'">砍价</span>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '3'">拼团</span>
							<view class="info">
								<view class="title line1">{{ item.store_name }}</view>
								<view class="price-box">
									<text>￥</text>
									{{ item.price }}
								</view>
							</view>
						</view>
					</view>
					<Loading :loaded="loaded" :loading="loading"></Loading>
					<view v-if="goodList.length == 0 && loaded" class="sort-scroll">
						<view class="empty-box">
							<image :src="imgHost + '/statics/images/no-thing.png'"></image>
							<view class="tips">暂无商品，去看点别的吧</view>
						</view>
						<recommend :hostProduct="hostProduct"></recommend>
					</view>
				</view>
				<!-- #endif -->
				<!-- #ifndef  APP-PLUS -->
        <!-- 商品排序 -->
				<view class="sort-product" v-if="isSortType == 1" :style="{ marginTop: sortMpTop + 'px' }">
					<scroll-view scroll-x="true" class="sort-scroll">
						<view class="sort-box" v-if="sortList.children && sortList.children.length">
							<view class="sort-item" v-for="(item, index) in sortList.children" :key="index"
								@click="changeSort(item, index)" :class="{ on: curSort == index }">
								<image :src="item.pic" mode="" v-if="item.pic"></image>
								<image src="/static/images/sort-img.png" mode="" v-else></image>
								<view class="txt">{{ item.cate_name }}</view>
							</view>
						</view>
					</scroll-view>
          <!-- 首页底部商品列表 -->
					<view class="product-list" v-if="goodList.length">
						<view class="product-item" v-for="(item, index) in goodList" @click="goGoodsDetail(item)">
							<view class="pictrue">
								<image :src="item.image" mode="aspectFill"></image>
								<span class="pictrue_log_big pictrue_log_class"
									v-if="item.activity && item.activity.type === '1'">秒杀</span>
								<span class="pictrue_log_big pictrue_log_class"
									v-if="item.activity && item.activity.type === '2'">砍价</span>
								<span class="pictrue_log_big pictrue_log_class"
									v-if="item.activity && item.activity.type === '3'">拼团</span>
								<view class="activityFrame" v-if="item.activity_frame.image" :style="'background-image: url('+item.activity_frame.image+');'"></view>
							</view>
							<view class="info">
								<view class="title line1">{{ item.store_name }}</view>
								<view class="price-box">
									<text>￥</text>
									{{ item.price }}
								</view>
							</view>
						</view>
					</view>
					<Loading :loaded="loaded" :loading="loading"></Loading>
					<view class="" v-if="goodList.length == 0 && loaded" class="sort-scroll">
						<view class="empty-box">
							<image :src="imgHost + '/statics/images/no-thing.png'"></image>
							<view class="tips">暂无商品，去看点别的吧</view>
						</view>
						<recommend :hostProduct="hostProduct"></recommend>
					</view>
				</view>
				<!-- #endif -->
				<couponWindow :window="isCouponShow" @onColse="couponClose" :couponImage="couponObj.image"
					:couponList="couponObj.list"></couponWindow>
				<view class="uni-p-b-98" v-if="footerStatus"></view>
				<view v-if="site_config" class="site-config" @click="goICP">{{ site_config }}</view>
				<pageFooter></pageFooter>
				<!-- #ifdef MP -->
				<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
				<!-- #endif -->
			</view>
		</view>
		<view v-else>
			<view class="error-network">
				<image :src="imgHost + '/statics/images/error-network.png'"></image>
				<view class="title">网络连接断开</view>
				<view class="con">
					<view class="label">请检查情况：</view>
					<view class="item">· 在设置中是否已开启网络权限</view>
					<view class="item">· 当前是否处于弱网环境</view>
					<view class="item">· 版本是否过低，升级试试吧</view>
				</view>
				<view class="btn" @click="reconnect">重新连接</view>
			</view>
		</view>
	</view>
</template>

<script>
	const app = getApp();
	import colors from "@/mixins/color";
	import couponWindow from '@/components/couponWindow/index';
	import permision from "@/js_sdk/wa-permission/permission.js";
	import skeletons from './components/skeletons';


	import {
		getCouponV2,
		getCouponNewUser,
		copyRight
	} from '@/api/api.js';
	import {
		getShare
	} from '@/api/public.js';
	// #ifdef H5
	import mConfig from './components/index.js';
	import {
		silenceAuth
	} from '@/api/public.js';
	// #endif
	// #ifdef MP || APP-PLUS
	import userInfor from './components/userInfor';
	import homeComb from './components/homeComb';
	import newVip from './components/newVip';
	import shortVideo from './components/shortVideo';
	import activeParty from './components/activeParty';
	import headerSerch from './components/headerSerch';
	import swipers from './components/swipers';
	import coupon from './components/coupon';
	import articleList from './components/articleList';
	import bargain from './components/bargain';
	import blankPage from './components/blankPage';
	import combination from './components/combination';
	import customerService from './components/customerService';
	import goodList from './components/goodList';
	import guide from './components/guide';
	import liveBroadcast from './components/liveBroadcast';
	import menus from './components/menus';
	import news from './components/news';
	import pictureCube from './components/pictureCube';
	import promotionList from './components/promotionList';
	import richText from './components/richText';
	import seckill from './components/seckill';
	import swiperBg from './components/swiperBg';
	import tabNav from './components/tabNav';
	import titles from './components/titles';
	import {
		getTemlIds
	} from '@/api/api.js';
	import {
		SUBSCRIBE_MESSAGE,
		TIPS_KEY
	} from '@/config/cache';

	// #endif
	import {
		mapGetters,
		mapMutations
	} from 'vuex';
	import {
		getDiy,
		getIndexData,
		getDiyVersion
	} from '@/api/api.js';
	import {
		getCategoryList,
		getProductslist,
		getProductHot,
    diyProduct
	} from '@/api/store.js';
	import {
		goShopDetail
	} from '@/libs/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {HTTP_REQUEST_URL} from '@/config/app';
	import pageFooter from '@/components/pageFooter/index.vue';
	import Loading from '@/components/Loading/index.vue';
	import recommend from '@/components/recommend';
	export default {
		computed: mapGetters(['isLogin', 'uid', 'cartNum']),
		mixins: [colors],
		components: {
			skeletons,
			recommend,
			Loading,
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
			pictureCube,
			news,
			promotionList,
			richText,
			seckill,
			swiperBg,
			tabNav,
			titles
			// #endif
		},
		data() {
			return {
				showSkeleton: true, //骨架屏显示隐藏
				styleConfig: [],
				loading: false,
				loadend: false,
				loadTitle: '下拉加载更多', //提示语
				page: 1,
				limit: this.$config.LIMIT,
				numConfig: 0,
				code: '',
				isCouponShow: false,
				couponObj: {},
				couponObjs: {
					show: false
				},
				shareInfo: {},
				footConfig: {},
				isSortType: 0,
				sortList: '',
				sortAll: [],
				goodPage: 1,
				goodList: [],
				sid: 0,
				curSort: 0,
				sortMpTop: 0,
				loaded: false,
				loading: false,
				hostProduct: [],
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10,
				domOffsetTop: 50,
				// #ifdef APP-PLUS || MP
				isFixed: true,
				// #endif

				// #ifdef H5
				isFixed: false,
				// #endif
				site_config: '',
				errorNetwork: false, // 是否断网
				footerStatus: false,
				isHeaderSerch: false,
				bgColor: '',
				bgPic: '',
				bgTabVal: '',
				pageShow: true,
				windowHeight: 0,
				imgHost:HTTP_REQUEST_URL,
				isShowAuth: false,
				isScrolled:false,
				product_video_status: false,
				confirm_video_status: false
			};
		},
		onLoad(options) {
			let that = this
			that.getOptions(options);
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
			
			this.diyData();
			this.getIndexData();
			// #ifdef H5
			this.setOpenShare();
			// #endif
			// #ifdef MP
			this.getTemlIds();
			// #endif
			getShare().then(res => {
				this.shareInfo = res.data;
			});
			this.getCopyRight();
			this.$eventHub.$on('confirm_video_status', () => {
				if (this.confirm_video_status) {
					return;
				}
				this.confirm_video_status = true;
				let flag = true;
				// #ifdef H5
				flag = window.self == window.top;
				// #endif
				if (!flag) {
					return;
				}
				uni.showModal({
					content: '当前使用移动网络，是否继续播放视频？',
					success: (res) => {
						if (res.confirm) {
							// 监听
							this.SET_AUTOPLAY(true);
							this.$eventHub.$emit('product_video_observe');
						}
					}
				});
			});
		},
		watch: {
			isLogin: {
				deep: true, //深度监听设置为 true
				handler: function(newV, oldV) {
					// 优惠券弹窗
					var newDates = new Date().toLocaleDateString();
					if (newV) {
						try {
							var oldDate = uni.getStorageSync('oldDate') || '';
						} catch {}
						if (oldDate != newDates) {
							this.getCoupon();
						}
					}
				}
			}
		},
		onShow() {
			if (this.cartNum > 0) {
				uni.setTabBarBadge({
					index: 2,
					text: this.cartNum + ''
				})
			} else {
				uni.hideTabBarRedDot({
					index: 2
				})
			}
			uni.removeStorageSync('form_type_cart');
			// 优惠券弹窗
			if (this.isLogin) {
				this.getCoupon();
			}
        this.getdiyProduct()
		},
		onReady() {},
		methods: {
			...mapMutations(['SET_AUTOPLAY']),
			// 授权关闭
			authColse: function(e) {
			  this.isShowAuth = e
			},
      // div商品详情
      getdiyProduct() {
        diyProduct().then(res=>{
          uni.setStorageSync('diyProduct',JSON.stringify(res.data.product_detail))
          uni.setStorageSync('product_video_status',JSON.stringify(res.data.product_video_status))
		  this.$eventHub.$emit('product_video_status', res.data.product_video_status);
		  this.product_video_status = res.data.product_video_status;
        })
      },
			getCopyRight(){
				copyRight().then(res => {
					let data = res.data;
					// #ifndef APP-PLUS
					this.site_config = data.record_No;
			    // #endif
					if(!data.copyrightContext && !data.copyrightImage){
						data.copyrightImage = '/static/images/support.png'
					}
					uni.setStorageSync('copyNameInfo', data.copyrightContext);
					uni.setStorageSync('copyImageInfo', data.copyrightImage);
					// #ifdef MP
					uni.setStorageSync('MPSiteData', JSON.stringify({site_logo:data.site_logo,site_name:data.site_name}));
					// #endif
				}).catch(err => {
					return this.$util.Tips({
						title: err.msg
					});
				});
			},
			getOptions(options){
				let that = this;
				// #ifdef MP
				if (options.scene) {
					let value = that.$util.getUrlParams(decodeURIComponent(options.scene));
					//记录推广人uid
					if (value.spid) app.globalData.spid = value.spid;
				}
				// #endif
				if (options.spid) app.globalData.spid = options.spid;
			},
			// 重新链接
			reconnect() {
				// uni.showLoading({
				// 	title: '加载中'
				// })
				this.diyData();
				this.getIndexData();
				getShare().then(res => {
					this.shareInfo = res.data;
				});
			},
			goICP() {
				// #ifdef H5
				window.open('http://beian.miit.gov.cn/');
				// #endif
				// #ifdef MP
				uni.navigateTo({
					url: `/pages/annex/web_view/index?url=https://beian.miit.gov.cn/`
				});
				// #endif
			},
			bindHeighta(data) {
				// #ifdef APP-PLUS
				this.sortMpTop = data.top + data.height;
				// #endif
			},
			bindHeight(data) {
				uni.hideLoading();
				this.domOffsetTop = data.top;
			},
			// 去商品详情
			goGoodsDetail(item) {
				goShopDetail(item, this.uid).then(res => {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${item.id}`
					});
				});
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
				let that = this;
				if (that.hotScroll) return;
				getProductHot(that.hotPage, that.hotLimit).then(res => {
					that.hotPage++;
					that.hotScroll = res.data.length < that.hotLimit;
					that.hostProduct = that.hostProduct.concat(res.data);
					// that.$set(that, 'hostProduct', res.data)
				});
			},
			// 分类点击
			changeSort(item, index) {
				if (this.curSort == index) return;
				this.curSort = index;
				this.sid = item.id;
				this.goodList = [];
				this.goodPage = 1;
				this.loaded = false;
				this.getGoodsList();
			},
			// 获取分类id
			bindSortId(data) {
				this.isSortType = data == -99 ? 0 : 1;
				this.getProductList(data);
				if (this.hostProduct.length == 0) {
					this.get_host_product();
				}
			},
			getProductList(data) {
				let tempObj = '';
				this.curSort = 0;
				this.loaded = false;
				if (this.sortAll.length > 0) {
					this.sortAll.forEach((el, index) => {
						if (el.id == data) {
							this.$set(this, 'sortList', el);
							this.sid = el.children.length ? el.children[0].id : '';
						}
					});
					this.goodList = [];
					this.goodPage = 1;
					this.$nextTick(() => {
						if (this.sortList != '') this.getGoodsList();
					});
				} else {
					getCategoryList().then(res => {
						this.sortAll = res.data;
						res.data.forEach((el, index) => {
							if (el.id == data) {
								this.sortList = el;
								this.sid = el.children.length ? el.children[0].id : '';
							}
						});
						this.goodList = [];
						this.goodPage = 1;

						this.$nextTick(() => {
							if (this.sortList != '') this.getGoodsList();
						});
					});
				}
			},
      // 商品列表
			getGoodsList() {
				if (this.loading || this.loaded) return;
				this.loading = true;
				getProductslist({
					sid: this.sid,
					keyword: '',
					priceOrder: '',
					salesOrder: '',
					news: 0,
					page: this.goodPage,
					limit: 10,
					cid: this.sortList.id
				}).then(res => {
					this.loading = false;
					this.loaded = res.data.length < 10;
					this.goodPage++;
					this.goodList = this.goodList.concat(res.data);
				});
			},
			// 新用户优惠券
			getNewCoupon() {
				const oldUser = uni.getStorageSync('oldUser') || 0;
				if (!oldUser) {
					getCouponNewUser().then(res => {
						const {
							data
						} = res;
						if (data.show) {
							if (data.list.length) {
								this.isCouponShow = true;
								this.couponObj = data;
								uni.setStorageSync('oldUser', 1);
							}
						} else {
							uni.setStorageSync('oldUser', 1);
						}
					});
				}
			},
			// 优惠券弹窗
			getCoupon() {
				const tagDate = uni.getStorageSync('tagDate') || '',
					nowDate = new Date().toLocaleDateString();
				if (tagDate === nowDate) {
					this.getNewCoupon();
				} else {
					getCouponV2().then(res => {
						const {
							data
						} = res;
						if (data.list.length) {
							this.isCouponShow = true;
							this.couponObj = data;
							uni.setStorageSync('tagDate', new Date().toLocaleDateString());
						} else {
							this.getNewCoupon();
						}
					});
				}
			},
			// 优惠券弹窗关闭
			couponClose() {
				this.isCouponShow = false;
				if (!uni.getStorageSync('oldUser')) {
					this.getNewCoupon();
				}
			},
			onLoadFun() {
				this.isShowAuth = false
			},
			// #ifdef H5
			// 获取url后面的参数
			getQueryString(name) {
				var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
				var reg_rewrite = new RegExp('(^|/)' + name + '/([^/]*)(/|$)', 'i');
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

			// #ifdef MP
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
				let obj = Object.keys(data);
				let m = obj.map(key => data[key]);
				return m;
			},
			setDiyData(data) {
				this.errorNetwork = false
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
				});
				let temp = [];
				let lastArr = this.objToArr(data.value);
				lastArr.forEach((item, index, arr) => {
					if (item.name == 'headerSerch') {
						this.isHeaderSerch = true
					}
					if (item.name == 'pageFoot') {
						arr.splice(index, 1);
					}
					if (item.name == 'tabNav') {
						// #ifndef APP-PLUS
						// uni.showLoading({
						// 	title: '加载中',
						// 	mask: true,
						// });
						// #endif
						// setTimeout(function() {
						// 	uni.hideLoading();
						// }, 8000);
					}
					temp = arr;
				});
											
				function sortNumber(a, b) {
					return a.timestamp - b.timestamp;
				}
				temp.sort(sortNumber)
				this.styleConfig = temp;
				this.showSkeleton = false
			},
			getDiyData() {
				getDiy(0).then(res => {
					uni.setStorageSync('diyData', JSON.stringify(res.data));
					this.setDiyData(res.data);
				}).catch(error => {
					// #ifdef APP-PLUS
					if (error.status) {
						uni.hideLoading()
						if (this.errorNetwork) {
							uni.showToast({
								title: '请开启网络连接',
								icon: 'none',
								duration: 2000
							})
						}
						this.errorNetwork = true
						this.showSkeleton = false;
					}
					// #endif
				});
			},
			diyData() {
				let diyData = uni.getStorageSync('diyData');
				if (diyData) {
					getDiyVersion(0).then(res => {
						let diyVersion = uni.getStorageSync('diyVersion');
						if ((res.data.version + '0') === diyVersion) {
							this.setDiyData(JSON.parse(diyData));
						} else{
							uni.setStorageSync('diyVersion', (res.data.version + '0'));
							this.getDiyData();
						}
					});
				} else{
					this.getDiyData();
				}
				// getDiy(0).then(res => {
				// 	this.errorNetwork = false
				// 	let data = res.data;
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
				// 	});
				// 	let temp = [];
				// 	let lastArr = that.objToArr(res.data.value);
				// 	lastArr.forEach((item, index, arr) => {
				// 		if (item.name == 'headerSerch') {
				// 			this.isHeaderSerch = true
				// 		}
				// 		if (item.name == 'pageFoot') {
				// 			arr.splice(index, 1);
				// 		}
				// 		if (item.name == 'tabNav') {
				// 			// #ifndef APP-PLUS
				// 			// uni.showLoading({
				// 			// 	title: '加载中',
				// 			// 	mask: true,
				// 			// });
				// 			// #endif
				// 			// setTimeout(function() {
				// 			// 	uni.hideLoading();
				// 			// }, 8000);
				// 		}
				// 		temp = arr;
				// 	});
				
				// 	function sortNumber(a, b) {
				// 		return a.timestamp - b.timestamp;
				// 	}
				// 	temp.sort(sortNumber)
				// 	that.styleConfig = temp;
				// 	this.showSkeleton = false
				// }).catch(error => {
				// 	// #ifdef APP-PLUS
				// 	if (error.status) {
				// 		uni.hideLoading()
				// 		if (that.errorNetwork) {
				// 			uni.showToast({
				// 				title: '连接失败',
				// 				icon: 'none',
				// 				duration: 2000
				// 			})
				// 		}
				// 		this.errorNetwork = true
				// 		this.showSkeleton = false;
				// 	}
				// 	// #endif
				// });
			},
			getIndexData() {},
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
						url: `/pages/activity/goods_bargain_details/index?id=${item.id}&spid=${this.$store.state.app.uid}`
					});
				}
			},
			goDetail(item) {
				goShopDetail(item, this.$store.state.app.uid).then(res => {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${item.id}`
					});
				});
			},
			// #ifdef H5
			// 微信分享；
			setOpenShare: function() {
				let that = this;
				let uid = this.uid?this.uid:0;
				if (that.$wechat.isWeixin()) {
					getShare().then(res => {
						let data = res.data;
						let configAppMessage = {
							desc: data.synopsis,
							title: data.title,
							link: location.href+'?spid='+uid,
							imgUrl: data.img
						};
						that.$wechat.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData',
								'onMenuShareAppMessage', 'onMenuShareTimeline'
							],
							configAppMessage);
					});
				}
			}
			// #endif
		},
		onReachBottom: function() {
			if (this.isSortType == 0) {
			} else {
				this.getGoodsList();
			}
		},
		onPageScroll(e) {
			// #ifdef H5
			if (this.isHeaderSerch) {
				if (e.scrollTop > this.domOffsetTop) {
					this.isFixed = true;
				}
				if (e.scrollTop < this.domOffsetTop) {
					this.$nextTick(() => {
						this.isFixed = false;
					});
				}
			} else {
				this.isFixed = false
			}
			// #endif
			if(e.scrollTop>10){
				this.isScrolled = true;
			}else{
				this.isScrolled = false;
			}
		},
		//#ifdef MP
		onShareAppMessage() {
			let uid = this.uid?this.uid:0;
			return {
				title: this.shareInfo.title,
				path: '/pages/index/index?spid='+uid
			};
		},
		//分享到朋友圈
		onShareTimeline: function() {
			return {
				title: this.shareInfo.title,
				path: '/pages/index/index'
				// imageUrl: this.shareInfo.img
			};
		}
		//#endif
	};
</script>

<style lang="scss">
	// page {
	// 	padding-bottom: 50px;
	// }
	.pictrue_log_class {
		background-color: var(--view-theme);
	}

	.page {
		padding-bottom: 98rpx;
		padding-bottom: calc(98rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(98rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
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

	.privacy-wrapper {
		z-index: 999;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		background: #7F7F7F;


		.privacy-box {
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate(-50%, -50%);
			width: 560rpx;
			padding: 50rpx 45rpx 0;
			background: #fff;
			border-radius: 20rpx;

			.title {
				text-align: center;
				font-size: 32rpx;
				text-align: center;
				color: #333;
				font-weight: 700;
			}

			.content {
				margin-top: 20rpx;
				line-height: 1.5;
				font-size: 26rpx;
				color: #666;

				navigator {
					display: inline-block;
					color: #E93323;
				}
			}

			.btn-box {
				margin-top: 40rpx;
				text-align: center;
				font-size: 30rpx;

				.btn-item {
					height: 82rpx;
					line-height: 82rpx;
					background: linear-gradient(90deg, #F67A38 0%, #F11B09 100%);
					color: #fff;
					border-radius: 41rpx;
				}

				.btn {
					padding: 30rpx 0;
				}
			}
		}
	}

	.error-network {
		position: fixed;
		left: 0;
		top: 0;
		display: flex;
		flex-direction: column;
		align-items: center;
		width: 100%;
		height: 100%;
		padding-top: 40rpx;
		background: #fff;

		image {
			width: 414rpx;
			height: 336rpx;
		}

		.title {
			position: relative;
			top: -40rpx;
			font-size: 32rpx;
			color: #666;
		}

		.con {
			font-size: 24rpx;
			color: #999;

			.label {
				margin-bottom: 20rpx;
			}

			.item {
				margin-bottom: 20rpx;
			}
		}

		.btn {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 508rpx;
			height: 86rpx;
			margin-top: 100rpx;
			border: 1px solid #D74432;
			color: #E93323;
			font-size: 30rpx;
			border-radius: 120rpx;
		}
	}
.sort-scroll {
  background-color: #fff;
}
	.sort-product {
		margin-top: 20rpx;

		.sort-box {
			display: flex;
			width: 100%;
			border-radius: 16rpx;
			padding: 30rpx 0;

			.sort-item {
				width: 20%;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				flex-shrink: 0;

				image {
					width: 90rpx;
					height: 90rpx;
					border-radius: 50%;
				}

				.txt {
					color: #272727;
					font-size: 24rpx;
					margin-top: 10rpx;
					overflow: hidden;
					white-space: nowrap;
					text-overflow: ellipsis;
					width: 140rpx;
					text-align: center;
				}

				.pictrues {
					width: 90rpx;
					height: 90rpx;
					background: #f8f8f8;
					border-radius: 50%;
					margin: 0 auto;
				}

				.icon-gengduo1 {
					color: #333;
				}

				&.on {
					.txt {
						color: #fc4141;
					}

					image {
						border: 1px solid #fc4141;
					}
				}
			}
		}

		.product-list {
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
			margin-top: 30rpx;
			padding: 0 20rpx;

			.product-item {
				position: relative;
				width: 344rpx;
				background: #fff;
				border-radius: 10rpx;
				margin-bottom: 20rpx;
				overflow: hidden;
				
				.pictrue{
					position: relative;
				}

				image {
					width: 100%;
					height: 344rpx;
					border-radius: 10rpx 10rpx 0 0;
				}

				.info {
					padding: 14rpx 16rpx;

					.title {
						font-size: 28rpx;
					}

					.price-box {
						font-size: 34rpx;
						font-weight: 700;
						margin-top: 8px;
						color: #fc4141;

						text {
							font-size: 26rpx;
						}
					}
				}
			}
		}
	}

	.empty-box {
		text-align: center;
		padding-top: 50rpx;
		.tips{
			color: #aaa;
			font-size: 26rpx;
		}
		image {
			width: 414rpx;
			height: 304rpx;
		}
	}

	.site-config {
		margin: 40rpx 0;
		font-size: 24rpx;
		text-align: center;
		color: #666;

		&.fixed {
			position: fixed;
			bottom: 69px;
			left: 0;
			width: 100%;
		}
	}
</style>
