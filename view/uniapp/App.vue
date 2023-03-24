<script>
	import {
		checkLogin
	} from './libs/login';
	import {
		HTTP_REQUEST_URL
	} from './config/app';
	import {
		getShopConfig,
		silenceAuth
	} from '@/api/public';
	import Auth from '@/libs/wechat.js';
	import Routine from './libs/routine.js';
	import {
		colorChange
	} from '@/api/api.js';
	import {
		mapGetters
	} from "vuex"
	// #ifdef MP
	// let livePlayer = requirePlugin('live-player-plugin')
	// #endif
	let green =
		'--view-theme: #42CA4D;--view-priceColor:#FF7600;--view-minorColor:rgba(108, 198, 94, 0.5);--view-minorColorT:rgba(66, 202, 77, 0.1);--view-bntColor:#FE960F;'
	let red =
		'--view-theme: #e93323;--view-priceColor:#e93323;--view-minorColor:rgba(233, 51, 35, 0.5);--view-minorColorT:rgba(233, 51, 35, 0.1);--view-bntColor:#FE960F;'
	let blue =
		'--view-theme: #1DB0FC;--view-priceColor:#FD502F;--view-minorColor:rgba(58, 139, 236, 0.5);--view-minorColorT:rgba(9, 139, 243, 0.1);--view-bntColor:#22CAFD;'
	let pink =
		'--view-theme: #FF448F;--view-priceColor:#FF448F;--view-minorColor:rgba(255, 68, 143, 0.5);--view-minorColorT:rgba(255, 68, 143, 0.1);--view-bntColor:#282828;'
	let orange =
		'--view-theme: #FE5C2D;--view-priceColor:#FE5C2D;--view-minorColor:rgba(254, 92, 45, 0.5);--view-minorColorT:rgba(254, 92, 45, 0.1);--view-bntColor:#FDB000;'
	let gold =
	    '--view-theme: #E0A558;--view-priceColor:#DA8C18;--view-minorColor:rgba(224, 165, 88, 0.5);--view-minorColorT:rgba(224, 165, 88, 0.1);--view-bntColor:#1A1A1A;'
	export default {
		globalData: {
			spid: 0,
			code: 0,
			isLogin: false,
			userInfo: {},
			MyMenus: [],
			globalData: false,
			isIframe: false,
			tabbarShow: true,
			windowHeight: 0
		},
		computed: mapGetters(['isLogin', 'cartNum']),
		watch: {
			isLogin: {
				deep: true, //深度监听设置为 true
				handler: function(newV, oldV) {
					if (newV) {
						// this.getCartNum()
					} else {
						this.$store.commit('indexData/setCartNum', '')
					}
				}
			},
			cartNum(newCart, b) {
				this.$store.commit('indexData/setCartNum', newCart + '')
				if (newCart > 0) {
					uni.setTabBarBadge({
						index: 2,
						text: newCart + ''
					})
				} else {
					uni.hideTabBarRedDot({
						index: 2
					})
				}
			}
		},
		onLaunch: async function(option) {
			//#ifdef APP
			plus.screen.lockOrientation("portrait-primary");
			//#endif
			let that = this;
			colorChange('color_change').then(res => {
				let navigation = res.data.navigation; //判断悬浮导航是否显示
				let statusColor = res.data.status; //判断显示啥颜色
				uni.setStorageSync('navigation', navigation);
				uni.$emit('navOk', navigation);
				uni.setStorageSync('statusColor', statusColor);
				uni.$emit('colorOk', statusColor);
				switch (res.data.status) {
					case 1:
						uni.setStorageSync('viewColor', blue)
						uni.$emit('ok', blue)
						break;
					case 2:
						uni.setStorageSync('viewColor', green)
						uni.$emit('ok', green)
						break;
					case 3:
						uni.setStorageSync('viewColor', red)
						uni.$emit('ok', red)
						break;
					case 4:
						uni.setStorageSync('viewColor', pink)
						uni.$emit('ok', pink)
						break;
					case 5:
						uni.setStorageSync('viewColor', orange)
						uni.$emit('ok', orange)
						break;
					case 6:
						uni.setStorageSync('viewColor', gold)
						uni.$emit('ok', gold)
						break;	
					default:
						uni.setStorageSync('viewColor', red)
						uni.$emit('ok', red)
						break
				}
			});
			if (option.query.spid) {
				that.$Cache.set('spid', option.query.spid);
				that.globalData.spid = option.query.spid;
			}
			// #ifdef APP-PLUS || H5
			uni.getSystemInfo({
				success: function(res) {
					// 首页没有title获取的整个页面的高度，里面的页面有原生标题要减掉就是视口的高度
					// 状态栏是动态的可以拿到 标题栏是固定写死的是44px
					let height = res.windowHeight - res.statusBarHeight - 44
					// #ifdef H5 || APP-PLUS
					that.globalData.windowHeight = res.windowHeight + 'px'
					// #endif
					// // #ifdef APP-PLUS
					// that.globalData.windowHeight = height + 'px'
					// // #endif

				}
			});
			// #endif	
			// #ifdef MP
			if (HTTP_REQUEST_URL == '') {
				console.error(
					"请配置根目录下的config.js文件中的 'HTTP_REQUEST_URL'\n\n请修改开发者工具中【详情】->【AppID】改为自己的Appid\n\n请前往后台【小程序】->【小程序配置】填写自己的 appId and AppSecret"
				);
				return false;
			}
			if (option.query.hasOwnProperty('scene')) {

				switch (option.scene) {
					//扫描小程序码
					case 1047:
						let val = that.$util.getUrlParams(decodeURIComponent(option.query.scene));
						that.globalData.code = val.spid === undefined ? val : val.spid;
						break;
						//长按图片识别小程序码
					case 1048:
						that.globalData.code = option.query.scene;
						break;
						//手机相册选取小程序码
					case 1049:
						that.globalData.code = option.query.scene;
						break;
						//直接进入小程序
					case 1001:
						that.globalData.spid = option.query.scene;
						break;
				}
			}
			this.checkUpdateVersion();
			// #endif
			// getShopConfig().then(res => {
			// 	this.$store.commit('SETPHONESTATUS', res.data.status);
			// });
			// 获取导航高度；
			uni.getSystemInfo({
				success: function(res) {
					that.globalData.navHeight = res.statusBarHeight * (750 / res.windowWidth) + 91;
				}
			});
			// #ifdef MP
			let menuButtonInfo = uni.getMenuButtonBoundingClientRect();
			that.globalData.navH = menuButtonInfo.top * 2 + menuButtonInfo.height / 2;
			const version = uni.getSystemInfoSync().SDKVersion
			if (Routine.compareVersion(version, '2.21.2') >= 0) {
				console.log(version)
				that.$Cache.set('MP_VERSION_ISNEW', true)
			} else {
				that.$Cache.set('MP_VERSION_ISNEW', false)
			}
			// #endif

			// #ifdef H5
			// 添加crmeb chat 统计
			var __s = document.createElement('script');
			__s.src = `${HTTP_REQUEST_URL}/api/get_script`;
			document.head.appendChild(__s);

			uni.getSystemInfo({
				success(e) {
					/* 窗口宽度大于420px且不在PC页面且不在移动设备时跳转至 PC.html 页面 */
					if (e.windowWidth > 420 && !window.top.isPC && !/iOS|Android/i.test(e.system)) {
						window.location.pathname = '/static/html/pc.html';
					}
				}
			});
			if (option.query.hasOwnProperty('type')) {
				this.globalData.isIframe = true;
			} else {
				this.globalData.isIframe = false;
			}

			if (window.location.pathname !== '/' && !this.isWork()) {
				let snsapiBase = 'snsapi_base';
				let urlData = location.pathname + location.search;
				if (!that.$store.getters.isLogin && uni.getStorageSync('authIng')) {
					uni.setStorageSync('authIng', false)
				}
				if (!that.$store.getters.isLogin && Auth.isWeixin()) {
					let code,
						state,
						scope = ''

					if (option.query.code instanceof Array) {
						code = option.query.code[option.query.code.length - 1]
					} else {
						code = option.query.code
					}


					if (code && code != uni.getStorageSync('snsapiCode') && location.pathname.indexOf(
							'/pages/users/wechat_login/index') === -1) {
						// 存储静默授权code
						uni.setStorageSync('snsapiCode', code);
						try {
							let res = await silenceAuth({
								code: code,
								snsapi: 'snsapi_base',
								spread_spid: that.$Cache.get('spid')
							});
							uni.setStorageSync('snRouter', decodeURIComponent(decodeURIComponent(option.query
								.back_url)));
							if (res.data.key !== undefined && res.data.key) {
								this.$Cache.set('snsapiKey', res.data.key);
							} else {
								let time = res.data.expires_time - this.$Cache.time();
								this.$store.commit('LOGIN', {
									token: res.data.token,
									time: time
								});

								this.$store.commit('SETUID', res.data.userInfo.uid);
								this.$store.commit('UPDATE_USERINFO', res.data.userInfo);
								if (option.query.back_url) {
									location.replace(decodeURIComponent(decodeURIComponent(option.query
										.back_url)));
								}
							}
						} catch (e) {
							let url = ''
							if (option.query.back_url instanceof Array) {
								url = option.query.back_url[option.query.back_url.length - 1]
							} else {
								url = option.query.back_url
							}
							if (!that.$Cache.has('snsapiKey')) {
								if (location.pathname.indexOf('/pages/users/wechat_login/index') === -1) {
									Auth.oAuth('snsapi_userinfo', url);
								}
							}
						}
					} else {
						if (!this.$Cache.has('snsapiKey')) {
							if (location.pathname.indexOf('/pages/users/wechat_login/index') === -1) {
								Auth.oAuth(snsapiBase, urlData);
							}
						}
					}
				} else {
					if (option.query.back_url) {
						location.replace(uni.getStorageSync('snRouter'));
					}
				}
			}
			// #endif
			// #ifdef MP
			// 小程序静默授权
			if (!this.$store.getters.isLogin) {
				Routine.getCode()
					.then(code => {
						this.silenceAuth(code);
					})
					.catch(res => {
						uni.hideLoading();
					});
			}
			// #endif
		},
		onShow(options) {
			let that = this;
			//直播间分享
			// #ifdef MP
			// const sceneList = [1007, 1008, 1014, 1044, 1045, 1046, 1047, 1048, 1049, 1073, 1154, 1155];
			//  if (sceneList.includes(options.scene)) {
			// 	livePlayer.getShareParams()
			// 		.then(res => {
			// 			//记录推广人uid
			// 			if(res.custom_params.pid){
			// 				 that.$Cache.set('spid', res.custom_params.pid);
			// 				 that.globalData.spid = res.custom_params.pid;
			// 			}
			// 		}).catch(err => {
			// 		})
			// }
			// #endif
		},
		mounted() {
			// setTimeout((e) => {
			// 	if (this.$store.getters.isLogin) {
			// 		this.getCartNum()
			// 	}
			// }, 100)
		},
		methods: {
			// 小程序静默授权
			silenceAuth(code) {
				let that = this;
				let spid = that.globalData.spid ? that.globalData.spid : '';
				silenceAuth({
						code: code,
						spread_spid: spid,
						spread_code: that.globalData.code
					})
					.then(res => {
						if (res.data.token !== undefined && res.data.token) {
							uni.hideLoading();
							let time = res.data.expires_time - this.$Cache.time();
							that.$store.commit('LOGIN', {
								token: res.data.token,
								time: time
							});
							that.$store.commit('SETUID', res.data.userInfo.uid);
							that.$store.commit('UPDATE_USERINFO', res.data.userInfo);
						}
					})
					.catch(res => {});
			},
			isWork() {
				return navigator.userAgent.toLowerCase().indexOf('wxwork') !== -1 && navigator.userAgent.toLowerCase()
					.indexOf("micromessenger") !== -1
			},
			/**
			 * 检测当前的小程序
			 * 是否是最新版本，是否需要下载、更新
			 */
			checkUpdateVersion() {
				//判断微信版本是否 兼容小程序更新机制API的使用
				if (wx.canIUse('getUpdateManager')) { 
					const updateManager = wx.getUpdateManager();
					//检测版本更新
					updateManager.onCheckForUpdate(function(res) {
						if (res.hasUpdate) {
							updateManager.onUpdateReady(function() {
								wx.showModal({
									title: '温馨提示',
									content: '检测到新版本，是否重启小程序？',
									showCancel: false,
									success: function(res) {
										if (res.confirm) {
											// 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
											updateManager.applyUpdate()
										}
									}
								})
							})
							updateManager.onUpdateFailed(function() {
								// 新版本下载失败
								wx.showModal({
									title: '已有新版本',
									content: '请您删除小程序，重新搜索进入',
								})
							})
						}
					})
				} else {
					wx.showModal({
						title: '溫馨提示',
						content: '当前微信版本过低，无法使用该功能，请升级到最新微信版本后重试。'
					})
				}
			}
		},
		onHide: function() {

		}
	};
</script>

<style lang="scss">
	/* #ifndef APP-PLUS-NVUE || APP-NVUE */
	@import url('@/plugin/emoji-awesome/css/tuoluojiang.css');
	@import url('@/plugin/animate/animate.min.css');
	@import 'static/css/base.css';
	@import 'static/iconfont/iconfont.css';
	@import 'static/css/guildford.css';
	@import 'static/css/style.scss';

	view {
		box-sizing: border-box;
	}
	
	.activityFrame{
			background-size: 100% 100%;
			background-repeat: no-repeat;
			position: absolute;
			top:0;
			left:0;
			width: 100%;
			height: 100%;
			z-index: 1;
	}

	page {
		font-family: PingFang SC;
	}
	
	.placeholder{
		color: #ccc;
	}

	.bg-color-red {
		background-color: var(--view-theme) !important;
	}

	.syspadding {
		padding-top: var(--status-bar-height);
	}

	.flex {
		display: flex;
	}

	.uni-scroll-view::-webkit-scrollbar {
		/* 隐藏滚动条，但依旧具备可以滚动的功能 */
		display: none;
	}

	::-webkit-scrollbar {
		width: 0;
		height: 0;
		color: transparent;
	}

	.uni-system-open-location .map-content.fix-position {
		height: 100vh;
		top: 0;
		bottom: 0;
	}
	/* #endif */
</style>
