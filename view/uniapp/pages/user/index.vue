<template>
	<!-- 个人中心模块 -->
	<view class="new-users copy-data" :style="{height:pageHeight}">
		<!-- 自定义顶部背景颜色 -->
		<view class="top" :style="colorStyle">
			<!-- #ifdef MP || APP-PLUS -->
			<view class="sys-head">
				<view class="sys-bar" :style="{height:sysHeight}"></view>
				<!-- #ifdef MP -->
				<view class="sys-title" :style="member_style==3?'color:#333':''">个人中心</view>
				<!-- #endif -->
				<view class="bg" :style="member_style==3?'background:#f5f5f5':''"></view>
			</view>
			<!-- #endif -->
		</view>
		<!-- 自定义顶部背景颜色 -->
		<view class="mid style-add" :style="colorStyle">
			<scroll-view scroll-y="true" class="height-add">
				<view class="head">
					<view class="user-card" :class="member_style==3?'unBg':''"
						:style="{backgroundImage:'url('+imgHost+'/statics/images/user01.png'+')'}">
						<view class="bg"></view>
						<!-- 个人资料展示 -->
						<view class="user-info">
							<view>
								<!-- 公众号未授权时显示 -->
								<!-- #ifdef H5 -->
								<button class="bntImg" v-if="userInfo.is_complete == 0 && isWeixin && !isLogin"
									@click="getWechatuserinfo">
									<image class="avatar" src='/static/images/f.png'></image>
									<view class="avatarName">获取头像</view>
								</button>
								<!-- #endif -->
								<!-- #ifdef MP -->
								<!-- <button class="bntImg" v-if="userInfo.is_complete == 0" @tap="getUserProfile">
                  <image class="avatar" src='/static/images/f.png'></image>
                  <view class="avatarName">获取头像</view>
                </button> -->
								<!-- #endif -->
								<!-- #ifndef APP-PLUS -->
								<view class="avatar-box" :class="{on:userInfo.is_money_level}">
									<image class="avatar" :src='userInfo.avatar' v-if="userInfo.avatar"
										@click="goEdit()">
									</image>
									<image v-else class="avatar" src="/static/images/f.png" mode="" @click="goEdit()">
									</image>
									<view class="headwear" v-if="userInfo.is_money_level && stu">
										<image src="/static/images/headwear.png"></image>
									</view>
								</view>
								<!-- #endif -->
								<!-- #ifdef APP-PLUS -->
								<view class="avatar-box" :class="{on:userInfo.is_money_level}">
									<image class="avatar" :src='userInfo.avatar' v-if="userInfo.avatar"
										@click="goEdit()">
									</image>
									<image v-else class="avatar" src="/static/images/f.png" mode="" @click="goEdit()">
									</image>
									<view class="headwear" v-if="userInfo.is_money_level && stu">
										<image src="/static/images/headwear.png"></image>
									</view>
								</view>
								<!-- #endif -->
							</view>
							<view class="info">
								<view class="name display-add" v-if="!userInfo.uid" @click="openAuto">
									请点击授权
								</view>
								<view class="name" v-if="userInfo.uid">
									<view class="nickname line1">{{userInfo.nickname}}</view>
									<image class="live" :src="userInfo.vip_icon" v-if="parseInt(userInfo.vip_id)"></image>
									<view class="vip" v-if="userInfo.is_money_level> 0 && stu">
										<image src="/static/images/svip.png"></image>
									</view>
								</view>
								<view class="num" v-if="userInfo.phone">
									<view class="num-txt">{{userInfo.phone}}</view>
									<!-- <view class="icon">
										<image src="/static/images/edit.png" mode=""></image>
									</view> -->
								</view>
								<view class="phone" v-if="!userInfo.phone && isLogin && userInfo.uid" @tap="bindPhone">绑定手机号</view>
							</view>
							<view v-if="isLogin" class="qrCode message iconfont icon-erweima-xingerenzhongxin"
								@click="tapQrCode">
							</view>
							<view class="message">
								<view @click="intoPage('/pages/users/user_set/index')">
									<view class="iconfont icon-shezhi"></view>
								</view>
							</view>
							<view class="message">
								<view @click="intoPage('/pages/users/message_center/index')">
									<view v-if="userInfo.service_num" class="num">
										{{userInfo.service_num >= 100 ? '99+' : userInfo.service_num}}
									</view>
									<view class="iconfont icon-s-kefu"></view>
								</view>
							</view>
							<!-- #ifdef MP -->
							<!-- <view class="setting" @click="Setting"><text class="iconfont icon-shezhi"></text></view> -->
							<!-- #endif -->

						</view>
						<view class="achsac">
						</view>
						<!-- <view class="sign" @click="goSignIn">签到</view> -->
						<!-- 会员权益 -->
						<view class="cardVipA acea-row row-between-wrapper" v-if="member_style==1 && stu && vip_type==2"
							:style="{backgroundImage:'url('+imgHost+'/statics/images/member.png'+')'}">
							<view class="left-box">
								<view v-if="vipStatus == 1" class="small">永久</view>
								<view v-else-if="vipStatus == 3" class="small">会员到期
									{{ userInfo.overdue_time | dateFormat }}
								</view>
								<view v-else-if="vipStatus == -1" class="small">已过期</view>
								<view v-else-if="vipStatus == 2" class="small">未开通会员</view>
							</view>
							<view class="acea-row row-middle">
								<navigator v-if="vipStatus == 1" url="/pages/annex/vip_paid/index" hover-class="none"
									class="btn">查看会员权益
								</navigator>
								<navigator v-else url="/pages/annex/vip_paid/index" hover-class="none" class="btn">
									{{ userInfo.overdue_time ? '立即续费' : '立即激活' }}
								</navigator>
								<text class="iconfont icon-xiangyou"></text>
							</view>
						</view>
						<view class="cardVipB acea-row row-between" v-if="member_style==3 && stu && vip_type==2">
							<view class="left-box acea-row">
								<view class="pictrue">
									<image src="../../static/images/member01.png"></image>
								</view>
								<view v-if="vipStatus == 1" class="small">永久</view>
								<view v-else-if="vipStatus == 3" class="small">会员到期
									{{ userInfo.overdue_time | dateFormat }}
								</view>
								<view v-else-if="vipStatus == -1" class="small">已过期</view>
								<view v-else-if="vipStatus == 2" class="small">未开通会员</view>
							</view>
							<view class="acea-row">
								<navigator v-if="vipStatus == 1" url="/pages/annex/vip_paid/index" hover-class="none"
									class="btn">
									会员可享多项权益</navigator>
								<navigator v-else url="/pages/annex/vip_paid/index" hover-class="none" class="btn">
									{{ userInfo.overdue_time ? '立即续费' : '立即激活' }}
								</navigator>
								<text class="iconfont icon-xiangyou btn"></text>
							</view>
						</view>
						<view class="cardVipA acea-row row-between-wrapper"
							:style="{backgroundImage:'url('+imgHost+'/statics/images/grade.png'+')'}"
							v-if="member_style==1 && memberStatus && vip_type==1">
							<view class="left-box on">
								<view class="small">{{userInfo.level_status==1?userInfo.vip_name:'激活会员卡 解锁更多权益'}}</view>
							</view>
							<view class="acea-row row-middle">
								<!-- 去激活 -->
								<navigator
									:url="userInfo.level_status==1?'/pages/annex/vip_grade/index':'/pages/annex/vip_grade_active/index'"
									hover-class="none" class="btn on">{{userInfo.level_status==1?'查看会员卡':'去激活'}}
								</navigator>
								<text class="iconfont icon-xiangyou on"></text>
							</view>
						</view>
						<view class="cardVipB acea-row row-between on"
							:style="{backgroundImage:'url('+imgHost+'/statics/images/grade01.png'+')'}"
							v-if="member_style==3 && memberStatus && vip_type==1">
							<view class="small">{{userInfo.level_status==1?userInfo.vip_name:'激活会员卡 解锁更多权益'}}</view>
							<view class="acea-row">
								<navigator
									:url="userInfo.level_status==1?'/pages/annex/vip_grade/index':'/pages/annex/vip_grade_active/index'"
									hover-class="none" class="btn on">{{userInfo.level_status==1?'查看会员卡':'去激活'}}
								</navigator>
								<text class="iconfont icon-xiangyou"></text>
							</view>
						</view>
					</view>
					<view class="card-vip" v-if="member_style==2 && stu && vip_type==2">
						<view class="left-box">
							<!-- <view class="big">会员可享多项权益</view> -->
							<view v-if="vipStatus == 1" class="small">永久</view>
							<view v-else-if="vipStatus == 3" class="small">会员到期
								{{ userInfo.overdue_time | dateFormat }}
							</view>
							<view v-else-if="vipStatus == -1" class="small">已过期</view>
							<view v-else-if="vipStatus == 2" class="small">未开通会员</view>
						</view>
						<view class="acea-row">
							<navigator v-if="vipStatus == 1" url="/pages/annex/vip_paid/index" hover-class="none"
								class="btn">查看会员权益
							</navigator>
							<navigator v-else url="/pages/annex/vip_paid/index" hover-class="none" class="btn">
								{{ userInfo.overdue_time ? '立即续费' : '立即激活' }}
							</navigator>
							<text class="iconfont icon-xiangyou"></text>
						</view>
					</view>
					<view class="card-vip on" v-if="member_style==2 && memberStatus && vip_type==1"
						:style="{backgroundImage:'url('+imgHost+'/statics/images/grade01.png'+')'}">
						<view class="left-box">
							<view class="small">{{userInfo.level_status==1?userInfo.vip_name:'激活会员卡 解锁更多权益'}}</view>
						</view>
						<view class="acea-row">
							<navigator
								:url="userInfo.level_status==1?'/pages/annex/vip_grade/index':'/pages/annex/vip_grade_active/index'"
								hover-class="none" class="btn">{{userInfo.level_status==1?'查看会员卡':'去激活'}}</navigator>
							<text class="iconfont icon-xiangyou"></text>
						</view>
					</view>
					<view class="num-wrapper"
						:class="[member_style==3?'unNum-wrapper':member_style==2?'Tnum-wrapper':'',((vip_type==2 && stu) || (vip_type==1 && memberStatus))?'':'height']"
						v-if="userInfo.uid">
						<view class="num-item" v-show="userInfo.balance_func_status"
							@click="goMenuPage('/pages/users/user_money/index')">
							<text class="num">{{userInfo.now_money || 0}}</text>
							<view class="txt">余额</view>
						</view>
						<view class="num-item" @click="goMenuPage('/pages/users/user_coupon/index')">
							<text class="num">{{userInfo.couponCount || 0}}</text>
							<view class="txt">优惠券</view>
						</view>
						<view class="num-item" @click="goMenuPage('/pages/users/user_integral/index')">
							<text class="num">{{userInfo.integral || 0}}</text>
							<view class="txt">积分</view>
						</view>
						<view class="num-item" @click="goMenuPage('/pages/users/user_goods_collection/index')">
							<text class="num">{{userInfo.collectCount || 0}}</text>
							<view class="txt">收藏</view>
						</view>
						<view class="num-item" @click="goMenuPage('/pages/users/visit_list/index')">
							<text class="num">{{userInfo.visit_num || 0}}</text>
							<view class="txt">浏览记录</view>
						</view>
					</view>
					<!-- 新人礼物 -->
					<view class="num-wrapper on" v-if="!userInfo.uid">
						<image :src="giftPic" mode="" class="img"></image>
						<view class="text">
							登录即获得<text class="gift">新人大礼包</text>
						</view>
						<view class="btn-login" @click="openAuto">去登录</view>
					</view>
					<!-- 新人专享 -->
					<view class="newcomersCon" :class="newcomer_style==1?'':'on'"
						v-if="newList.length && newcomer_status==1">
						<view class="newcomers"
							:style="{backgroundImage:'url('+ (newcomer_style==1?imgHost+'/statics/images/new-bg.png':newBg) +')'}">
							<view class="title">
								<view class="tl">
									新人专享
								</view>
								<view class="tr" @click="goNewcomers">
									更多 <text class="iconfont icon-xiangyou"></text>
								</view>
							</view>
							<scroll-view scroll-x="true" class="scroll">
								<view class="scroll-item" v-for="(item,index) in newList" :key="index"
									@click="goDetail(item)">
									<view class="pictrue">
										<image :src='item.image'></image>
										<view class="label">新人价</view>
									</view>
									<view class="money">¥{{item.price}}</view>
								</view>
							</scroll-view>
						</view>
					</view>
					<!-- 订单中心 -->
					<view class="order-wrapper">
						<view class="order-hd flex">
							<view class="left">订单中心</view>
							<view class="right flex" @click="intoPage('/pages/goods/order_list/index')">
								查看全部
								<text class="iconfont icon-xiangyou"></text>
							</view>
						</view>
						<view class="order-bd">
							<block v-for="(item,index) in orderMenu" :key="index">
								<view class="order-item" @click="intoPage(item.url)">
									<view class="pic">
										<!-- <image :src="item.img" mode=""></image> -->
										<text class="iconfont" :class="item.img"></text>
										<uni-badge class="uni-badge-left-margin" v-if="item.num>0" :text="item.num" absolute="rightTop"></uni-badge>
										<!-- <text class="order-status-num" v-if="item.num > 0">{{ item.num }}</text> -->
									</view>
									<view class="txt">{{item.title}}</view>
								</view>
							</block>
						</view>
					</view>
				</view>
				<!-- 轮播 -->
				<view class="slider-wrapper" v-if="imgUrls.length>0 && my_banner_status">
					<swiper indicator-dots="true" :autoplay="autoplay" :circular="circular" :interval="interval"
						:duration="duration" indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff">
						<block v-for="(item,index) in imgUrls" :key="index">
							<swiper-item>
								<navigator @click="goMenuPage(item.url)"
									class='slide-navigator acea-row row-between-wrapper' hover-class='none'>
									<image :src="item.pic" class="slide-image"></image>
								</navigator>
							</swiper-item>
						</block>
					</swiper>
				</view>
				<!-- 会员菜单二 -->
				<view class="menus-list" v-if="menu_status == 2 && MyMenus.length">
					<view class="title">我的服务</view>
					<!-- #ifdef APP-PLUS || H5 -->
					<block v-for="(item,index) in MyMenus" :key="index">
						<view class="item acea-row row-between-wrapper"
							v-if="item.url!='#' && item.url!='/pages/service/index'"
							@click="goMenuPage(item.url, item.name)">
							<image :src="item.pic"></image>
							<view class="text acea-row row-between-wrapper">
								<view>{{item.name}}</view>
								<view class="iconfont icon-xiangyou"></view>
							</view>
						</view>
					</block>
					<!-- #endif -->
					<!-- #ifdef MP -->
					<block v-for="(item,index) in MyMenus" :key="index">
						<view class="item acea-row row-between-wrapper" v-if="item.url!='#'
							&& item.url!='/pages/service/index' 
							&& item.url!='/pages/extension/customer_list/chat' 
							|| (item.url=='/pages/extension/customer_list/chat' && routineContact == 0)"
							@click="goMenuPage(item.url, item.name)">
							<image :src="item.pic"></image>
							<view class="text acea-row row-between-wrapper">
								<view>{{item.name}}</view>
								<view class="iconfont icon-xiangyou"></view>
							</view>
						</view>
					</block>
					<button class="item acea-row row-between-wrapper" open-type='contact' v-if="routineContact == 1">
						<image src="/static/images/contact.png"></image>
						<view class="text acea-row row-between-wrapper">
							<view>联系客服</view>
							<view class="iconfont icon-xiangyou"></view>
						</view>
					</button>
					<!-- #endif -->
					<!-- #ifdef APP-PLUS -->
					<view class="item acea-row row-between-wrapper" hover-class="none"
						@click="goMenuPage('/pages/users/privacy/index')">
						<image src="/static/images/menu.png"></image>
						<view class="text acea-row row-between-wrapper">
							<view>隐私协议</view>
							<view class="iconfont icon-xiangyou"></view>
						</view>
					</view>
					<!-- #endif -->
				</view>
				<!-- 会员菜单一 -->
				<view class="user-menus" style="margin-top: 25rpx;" v-if="menu_status == 1 && MyMenus.length">
					<view class="menu-title">我的服务</view>
					<view class="list-box">
						<!-- #ifdef APP-PLUS || H5 -->
						<block v-for="(item,index) in MyMenus" :key="index">
							<view class="item" v-if="item.url!='#' && item.url!='/pages/service/index'"
								@click="goMenuPage(item.url, item.name)">
								<image :src="item.pic"></image>
								<text>{{item.name}}</text>
							</view>
						</block>
						<!-- #endif -->
						<!-- #ifdef MP -->
						<block v-for="(item,index) in MyMenus" :key="index">
							<view class="item" v-if="item.url!='#' 
								&& item.url!='/pages/service/index' 
								&& item.url!='/pages/extension/customer_list/chat' 
								|| (item.url=='/pages/extension/customer_list/chat' && routineContact == 0)"
								@click="goMenuPage(item.url, item.name)">
								<image :src="item.pic"></image>
								<text>{{item.name}}</text>
							</view>
						</block>
						<button class="item" open-type='contact' v-if="routineContact == 1">
							<image src="/static/images/contact.png"></image>
							<text>联系客服</text>
						</button>
						<!-- #endif -->
						<!-- #ifdef APP-PLUS -->
						<view class="item" hover-class="none"
							@click="goMenuPage('/pages/users/privacy/index?type=privacy')">
							<image src="/static/images/menu.png"></image>
							<text>隐私协议</text>
						</view>
						<!-- #endif -->
					</view>
				</view>
				<view class="user-menus" style="margin-top: 25rpx;" v-if="service_status == 1 && storeMenu.length">
					<view class="menu-title">商家管理</view>
					<view class="list-box">
						<block v-for="(item,index) in storeMenu" :key="index">
							<view class="item" :url="item.url" v-if="item.url!='#' && item.url!='/pages/service/index'"
								@click="goMenuPage(item.url, item.name)">
								<image :src="item.pic"></image>
								<text>{{item.name}}</text>
							</view>
						</block>
					</view>
				</view>
				<view class="menus-list" v-if="service_status == 2 && storeMenu.length">
					<view class="title">商家管理</view>
					<block v-for="(item,index) in storeMenu" :key="index">
						<view class="item acea-row row-between-wrapper" :url="item.url" hover-class="none"
							v-if="item.url!='#' && item.url!='/pages/service/index'"
							@click="goMenuPage(item.url, item.name)">
							<image :src="item.pic"></image>
							<view class="text acea-row row-between-wrapper">
								<view>{{item.name}}</view>
								<view class="iconfont icon-xiangyou"></view>
							</view>
						</view>
					</block>
				</view>
				<view class="copy_right">
					<template v-if='copyRightText'>
						<image :src="copyRightImg" mode="aspectFill" class="copyRightImg"></image>
						<view class="copyRightText">
							{{copyRightText}}
						</view>
					</template>
					<image v-else :src="copyRightImg" mode="aspectFill" class='support'></image>
				</view>
				<view class="uni-p-b-98"></view>
			</scroll-view>
		</view>
		<view class="codePopup" :style="colorStyle" v-show="isCode">
			<view class="header acea-row row-between-wrapper">
				<view class="title" :class="{'on': codeIndex == index,'onLeft':codeIndex == 1}"
					v-for="(item, index) in codeList" :key="index" @click="tapCode(index)">{{item.name}}</view>
			</view>
			<view>
				<view class="acea-row row-center-wrapper">
					<w-barcode :options="config.bar"></w-barcode>
				</view>
				<view class="acea-row row-center-wrapper" style="margin-top: 35rpx;">
					<w-qrcode :options="config.qrc" @generate="hello"></w-qrcode>
				</view>
				<view class="codeNum">{{config.bar.code}}</view>
				<view class="tip">如遇到扫码失败请将屏幕调至最亮重新扫码</view>
			</view>
			<view class="iconfont icon-guanbi2" @click="closeCode"></view>
		</view>
		<view class="mark" v-if="isCode"></view>
		<pageFooter></pageFooter>
		<!-- 店员推广码 extension-->
		<view class="codePopup heg" :style="colorStyle" v-show="isextension">
			<view class="header">
				<view class="title on" style="width: 100%;">店员推广码</view>
			</view>
			<view>
				<view class="acea-row row-center-wrapper" style="margin-top: 25rpx;" @click="hah">
					<w-qrcode id="hah" :options="extension"></w-qrcode>
				</view>
				<view class="tip">如遇到扫码失败请将屏幕调至最亮重新扫码</view>
			</view>
			<view class="iconfont icon-guanbi2" @click="closeCode"></view>
		</view>
		<view class="mark" v-show="isextension"></view>
		<ewcomerPop v-if="isComerGift" :comerGift="comerGift" @comerPop="comerPop"></ewcomerPop>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<editUserModal :isShow="editModal" @closeEdit="closeEdit" @editSuccess="editSuccess"></editUserModal>
		<!-- #endif -->
	</view>
</template>
<script>
	let sysHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
	import {
		storeCardApi
	} from '@/api/store.js';
	import {
		newcomerGift
	} from '@/api/new_store.js';
	import {
		newcomerList,
		copyRight
	} from '@/api/api.js';
	import {
		getMenuList,
		getUserInfo,
		setVisit,
		updateUserInfo,
		getRandCode,
		updateWechatInfo
	} from '@/api/user.js';
	import {
		wechatAuthV2,
		silenceAuth
	} from '@/api/public.js'
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapState,
		mapGetters
	} from "vuex";
	// #ifdef H5
	import Auth from '@/libs/wechat';
	// #endif
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	const app = getApp();
	import ewcomerPop from '@/components/ewcomerPop/index.vue'
	import pageFooter from '@/components/pageFooter/index.vue'
	import dayjs from '@/plugin/dayjs/dayjs.min.js';
	import Routine from '@/libs/routine';
	import colors from '@/mixins/color';
	// #ifdef MP
	import editUserModal from '@/components/eidtUserModal/index.vue'
	// #endif
	export default {
		components: {
			pageFooter,
			ewcomerPop,
			// #ifdef MP
			editUserModal
			// #endif
		},
		computed: {
			...mapGetters({
				cartNum: 'cartNum',
				isLogin: 'isLogin'
			})
		},
		filters: {
			coundTime(val) {
				var setTime = val * 1000
				var nowTime = new Date()
				var rest = setTime - nowTime.getTime()
				var day = parseInt(rest / (60 * 60 * 24 * 1000))
				// var hour = parseInt(rest/(60*60*1000)%24) //小时
				return day + '天'
			},
			dateFormat: function(value) {
				return dayjs(value * 1000).format('YYYY-MM-DD');
			}
		},
		mixins: [colors],
		data() {
			return {
				vipStatus: 0,
				stu: false,
				storeMenu: [], // 商家管理
				orderMenu: [{
						img: 'icon-daifukuan',
						title: '待付款',
						url: '/pages/goods/order_list/index?status=0'
					},
					{
						img: 'icon-daifahuo',
						title: '待发货',
						url: '/pages/goods/order_list/index?status=1'
					},
					{
						img: 'icon-daishouhuo',
						title: '待收货',
						url: '/pages/goods/order_list/index?status=2'
					},
					{
						img: 'icon-daipingjia',
						title: '待评价',
						url: '/pages/goods/order_list/index?status=3'
					},
					{
						img: 'icon-a-shouhoutuikuan',
						title: '售后/退款',
						url: '/pages/users/user_return_list/index'
					},
				],
				imgUrls: [],
				autoplay: true,
				circular: true,
				interval: 3000,
				duration: 500,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				orderStatusNum: {},
				userInfo: {},
				MyMenus: [],
				sysHeight: sysHeight,
				mpHeight: 0,
				showStatus: 1,
				// #ifdef H5 || MP
				pageHeight: '100%',
				routineContact: 0,
				// #endif
				// #ifdef APP-PLUS
				pageHeight: app.globalData.windowHeight,
				// #endif
				// #ifdef H5
				isWeixin: Auth.isWeixin(),
				//#endif
				footerSee: false,
				member_style: 1,
				my_banner_status: 1,
				menu_status: 1,
				service_status: 1,
				newcomer_status: 1,
				codeList: [{
					name: '会员码'
				}, {
					name: '付款码'
				}],
				codeIndex: 0,
				config: {
					bar: {
						code: '',
						color: ['#000'],
						bgColor: '#FFFFFF', // 背景色
						width: 480, // 宽度
						height: 110 // 高度
					},
					qrc: {
						code: '',
						size: 380, // 二维码大小
						level: 3, //等级 0～4
						bgColor: '#FFFFFF', //二维码背景色 默认白色
						border: {
							color: ['#eee', '#eee'], //边框颜色支持渐变色
							lineWidth: 3, //边框宽度
						},
						// img: '/static/logo.png', //图片
						// iconSize: 40, //二维码图标的大小
						color: ['#333', '#333'], //边框颜色支持渐变色
					}
				},
				isCode: false,
				isextension: false,
				extension: {
					code: "",
					size: 380, // 二维码大小
					level: 3, //等级 0～4
					bgColor: '#FFFFFF', //二维码背景色 默认白色
					border: {
						color: ['#eee', '#eee'], //边框颜色支持渐变色
						lineWidth: 3, //边框宽度
					},
					// img: '/static/logo.png', //图片
					// iconSize: 40, //二维码图标的大小
					color: ['#333', '#333'], //边框颜色支持渐变色
				},
				imgHost: HTTP_REQUEST_URL,
				copyRightText: "",
				copyRightImg: '',
				giftPic: '',
				vip_type: 1,
				newcomer_style: 1,
				newList: [],
				newBg: '',
				comerGift: {},
				isComerGift: false,
				memberStatus: 0,
				editModal: false, // 编辑头像信息
			}
		},
		created() {
			let that = this;
			// #ifdef MP
			// 小程序静默授权
			if (!this.$store.getters.isLogin) {
				// Routine.getCode()
				//   .then(code => {
				//     Routine.silenceAuth(code).then(res => {
				//       this.onLoadFun();
				//     })
				//   })
				//   .catch(res => {
				//     uni.hideLoading();
				//   });
			}
			// #endif
			if (that.isLogin == false) {
				// #ifdef H5 || APP-PLUS
				toLogin()
				// #endif
				// #ifdef MP
				this.isShowAuth = true;
				// #endif
			}
			// #ifdef APP-PLUS
			that.$set(that, 'pageHeight', app.globalData.windowHeight);
			// #endif
		},
		async onLoad(option) {
			this.copyRightText = uni.getStorageSync('copyNameInfo');
			if(!this.copyRightText){
				this.getCopyRight();
			}
			if (this.isLogin) {
				this.getNewcomerGift();
			}
			// #ifdef H5 
			if (this.isLogin && Auth.isWeixin() && option.code) {
				try {
					let res = await updateWechatInfo({
						code: option.code
					});
					this.userInfo.nickname = res.data.nickname;
					this.userInfo.avatar = res.data.avatar;
					this.userInfo.is_complete = 1;
				} catch (e) {}
			}
			// #endif
			this.getNewList();
			this.colorData();
			let giftList = [
				this.imgHost + '/statics/images/gift01.png',
				this.imgHost + '/statics/images/gift02.png',
				this.imgHost + '/statics/images/gift03.png',
				this.imgHost + '/statics/images/gift04.png',
				this.imgHost + '/statics/images/gift05.png',
				this.imgHost + '/statics/images/gift06.png'
			];
			let newBg = [
				this.imgHost + '/statics/images/blue-bg.png',
				this.imgHost + '/statics/images/green-bg.png',
				this.imgHost + '/statics/images/red-bg.png',
				this.imgHost + '/statics/images/pink-bg.png',
				this.imgHost + '/statics/images/orange-bg.png',
				this.imgHost + '/statics/images/gold-bg.png'
			]
			setTimeout(() => {
				switch (this.colorNum) {
					case 1:
						this.giftPic = giftList[2];
						this.newBg = newBg[0];
						break;
					case 2:
						this.giftPic = giftList[1];
						this.newBg = newBg[1];
						break;
					case 3:
						this.giftPic = giftList[4];
						this.newBg = newBg[2];
						break;
					case 4:
						this.giftPic = giftList[0];
						this.newBg = newBg[3];
						break;
					case 5:
						this.giftPic = giftList[3];
						this.newBg = newBg[4];
						break;
					case 6:
						this.giftPic = giftList[5];
						this.newBg = newBg[5];
						break;
					default:
						this.giftPic = giftList[4];
						this.newBg = newBg[2];
						break
				}
			}, 1)
		},
		onReady() {
			let self = this
			// #ifdef MP
			let info = uni.createSelectorQuery().select(".sys-head");
			info.boundingClientRect(function(data) { //data - 各种参数
				self.mpHeight = data.height
			}).exec()
			// #endif
		},
		onShow: function() {
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
			this.copyRightText = uni.getStorageSync('copyNameInfo');
			this.copyRightImg = uni.getStorageSync('copyImageInfo');
			uni.removeStorageSync('form_type_cart');
			let that = this;
			// #ifdef APP-PLUS
			uni.getSystemInfo({
				success: function(res) {
					that.pageHeight = res.windowHeight + 'px'
				}
			});
			// #endif
			if (that.isLogin) {
				// #ifdef MP
				this.isShowAuth = false;
				// #endif
				this.getUserInfo();
				this.getMyMenus();
				this.setVisit();
			};
		},
		methods: {
			// #ifdef MP
			editSuccess() {
				this.editModal = false
				this.getUserInfo();
			},
			closeEdit() {
				this.editModal = false
			},
			// #endif
			getCopyRight(){
				copyRight().then(res => {
					let data = res.data;
					if(!data.copyrightContext && !data.copyrightImage){
						data.copyrightImage = '/static/images/support.png'
					}
					uni.setStorageSync('copyNameInfo', data.copyrightContext);
					uni.setStorageSync('copyImageInfo', data.copyrightImage);
				}).catch(err => {
					return this.$util.Tips({
						title: err.msg
					});
				});
			},
			// 查看订单
			intoPage(url) {
				if (this.isLogin) {
					uni.navigateTo({
						url: url
					})
				} else {
					// #ifdef H5 || APP-PLUS
					toLogin()
					// #endif
					// #ifdef MP
					this.isShowAuth = true;
					// #endif
				}
			},
			goDetail(item) {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}&fromPage='newVip'`
				});
			},
			comerPop() {
				this.isComerGift = false;
			},
			getNewcomerGift() {
				if (uni.getStorageSync('newcomerGift')) {
					return this.isComerGift = false;
				}
				newcomerGift().then(res => {
					this.comerGift = res.data;
					if (Object.prototype.toString.call(this.comerGift) == '[object Object]') {
						if (res.data.coupon_count || res.data.product_count || res.data.register_give_money || res
							.data.first_order_discount || res.data.register_give_integral) {
							uni.setStorageSync('newcomerGift', true);
							this.isComerGift = true;
						}
					}
				}).catch(err => {
					return this.$util.Tips({
						title: err
					});
				})
			},
			getNewList() {
				newcomerList({
					page: 1,
					limit: 6,
					priceOrder: '',
					salesOrder: ''
				}).then(res => {
					this.newList = res.data;
				}).catch(err => {
					return this.$util.Tips({
						title: err
					});
				})
			},
			hah() {
				let that = this
				// #ifdef H5
				var canvas = document.getElementById("hah").getElementsByTagName("canvas")
				var imgURL = canvas[0].toDataURL("image/png")
				var dlLink = document.createElement('a');
				dlLink.download = "店员推广码";
				dlLink.href = imgURL;
				dlLink.dataset.downloadurl = ["image/png", dlLink.download, dlLink.href].join(':');

				document.body.appendChild(dlLink);
				dlLink.click();
				document.body.removeChild(dlLink);
				// #endif
				// #ifdef MP
				// let ctx = uni.createCanvasContext("WQrcode")

				// let ctx = uni.createSelectorQuery().in(this).select("canvas")
				// uni.canvasToTempFilePath({
				// 	canvasId: "WQrcode",
				// 	fileType: 'image/png',
				// 	success: (res) => {
				// 	 // 保存当前绘制推广码
				// 	that.savePic(res.tempFilePath)
				// 	},
				// 	fail: function(err) {
				// 	console.log(err, '推广码生成失败')
				// 	}
				// },that)

				//  ctx.draw(false, uni.canvasToTempFilePath({
				// 			canvasId: 'WQrcode',
				// 			success: function (res) {
				// 			that.savePic(res.tempFilePath)
				// 			},
				// 			fail: function (err) {
				// 				console.log(err, '推广码生成失败')
				// 			}
				// },that))
				// console.log("222222",ctx)
				// #endif
				// #ifdef APP-PLUS
				uni.canvasToTempFilePath({
					canvasId: 'WQrcode',
					success: (res) => {
						uni.hideLoading()
						// 保存当前绘制推广码
						that.savePic(res.tempFilePath)
					},
					fail: function(err) {
						console.log(err, '推广码生成失败')
					}
				})
				// #endif
			},
			//保存推广码到本地
			savePic(filePath) {
				// #ifdef APP-PLUS
				uni.showLoading({
					title: '正在保存'
				});
				uni.saveImageToPhotosAlbum({
					filePath: filePath,
					success: function() {
						uni.showToast({
							title: '推广码保存成功～'
						});
					},
					fail: function(e) {
						console.log(e, '推广码保存失败')
					},
					complete: function() {
						uni.hideLoading()
					}
				});
				// #endif
			},
			hello(res) {
				// console.log(321,res)

			},
			// 前往新人专享
			goNewcomers() {
				uni.navigateTo({
					url: '/pages/store/newcomers/index'
				})
			},

			getCode() {
				getRandCode().then(res => {
					let code = res.data.code;
					this.config.bar.code = code;
					this.config.qrc.code = code;
				}).catch(err => {
					return this.$util.Tips(err);
				})
			},
			tapQrCode() {
				this.isCode = true;
				this.codeIndex = 0;
				this.$nextTick(function() {
					let code = this.userInfo.bar_code;
					this.config.bar.code = code;
					this.config.qrc.code = code;
				})
			},
			closeCode() {
				this.isCode = false
				this.isextension = false
			},
			tapCode(index) {
				this.codeIndex = index;
				if (index == 1) {
					this.getCode();
				} else {
					let code = this.userInfo.bar_code;
					this.config.bar.code = code;
					this.config.qrc.code = code;
				}
			},
			getWechatuserinfo() {
				//#ifdef H5
				Auth.isWeixin() && Auth.toAuth('snsapi_userinfo', '/pages/user/index');
				//#endif
			},
			getRoutineUserInfo(e) {
				updateUserInfo({
					userInfo: e.detail.userInfo
				}).then(res => {
					this.getUserInfo();
					return this.$util.Tips('更新用户信息成功');
				}).catch(err => {
					return this.$util.Tips(err);
				})
			},
			// 记录会员访问
			setVisit() {
				setVisit({
					url: '/pages/user/index'
				}).then(res => {})
			},
			// 打开授权
			openAuto() {
				//#ifndef MP
				toLogin();
				//#endif
				//#ifdef MP
				this.isShowAuth = true;
				//#endif
			},
			// 授权回调
			onLoadFun() {
				this.getUserInfo();
				this.getMyMenus();
				this.setVisit();
				this.isShowAuth = false;
			},
			Setting: function() {
				uni.openSetting({
					success: function(res) {}
				});
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			// 绑定手机
			bindPhone() {
				uni.navigateTo({
					url: '/pages/users/user_phone/index'
				})
			},
			/**
			 * 获取个人用户信息
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					that.userInfo = res.data
					that.stu = res.data.svip_open
					that.memberStatus = parseInt(res.data.member_func_status)
					that.vipStatus = res.data.vip_status
					that.$store.commit("SETUID", res.data.uid);
					that.$store.commit("UPDATE_USERINFO", res.data);
					that.orderMenu.forEach((item, index) => {
						switch (item.title) {
							case '待付款':
								item.num = res.data.orderStatusNum.unpaid_count
								break
							case '待发货':
								item.num = res.data.orderStatusNum.unshipped_count
								break
							case '待收货':
								item.num = res.data.orderStatusNum.received_count
								break
							case '待评价':
								item.num = res.data.orderStatusNum.evaluated_count
								break
							case '售后/退款':
								item.num = res.data.orderStatusNum.refunding_count
								break
						}
					})
				});
			},
			//小程序授权api替换 getUserInfo
			getUserProfile() {
				uni.showLoading({
					title: '获取中'
				});
				let self = this;
				Routine.getUserProfile()
					.then(res => {
						Routine.getCode()
							.then(code => {
								let userInfo = res.userInfo;
								userInfo.code = code;
								userInfo.spread_spid = app.globalData.spid; //获取推广人ID
								userInfo.spread_code = app.globalData.code; //获取推广人分享二维码ID
								Routine.authUserInfo(userInfo)
									.then(res => {
										if (res.data.key !== undefined && res.data.key) {
											uni.hideLoading();
											self.authKey = res.data.key;
											self.isPhoneBox = true;
										} else {
											uni.hideLoading();
											let time = res.data.expires_time - self.$Cache.time();
											self.$store.commit('LOGIN', {
												token: res.data.token,
												time: time
											});
											this.getUserInfo()
										}
									})
									.catch(res => {
										uni.hideLoading();
										uni.showToast({
											title: res.msg,
											icon: 'none',
											duration: 2000
										});
									});
							})
							.catch(res => {
								uni.hideLoading();
							});
					})
					.catch(res => {
						uni.hideLoading();
					});
			},
			/**
			 * 
			 * 获取个人中心图标
			 */
			switchTab(order) {
				this.orderMenu.forEach((item, index) => {
					switch (item.title) {
						case '待付款':
							item.img = order.dfk
							break
						case '待发货':
							item.img = order.dfh
							break
						case '待收货':
							item.img = order.dsh
							break
						case '待评价':
							item.img = order.dpj
							break
						case '售后/退款':
							item.img = order.sh
							break
					}
				})
			},
			getMyMenus: function() {
				let that = this;
				// if (this.MyMenus.length) return;
				getMenuList().then(res => {
					let storeMenu = []
					let myMenu = []
					res.data.routine_my_menus.forEach((el, index, arr) => {
						if (el.type == '2') {
							storeMenu.push(el)
						} else {
							myMenu.push(el)
						}
					})
					this.member_style = Number(res.data.diy_data.status)
					this.my_banner_status = res.data.diy_data.my_banner_status
					this.menu_status = res.data.diy_data.menu_status
					this.service_status = res.data.diy_data.service_status
					this.vip_type = res.data.diy_data.vip_type;
					this.newcomer_style = res.data.diy_data.newcomer_style;
					this.newcomer_status = res.data.diy_data.newcomer_status;
					let order01 = {
						dfk: 'icon-daifukuan',
						dfh: 'icon-daifahuo',
						dsh: 'icon-daishouhuo',
						dpj: 'icon-daipingjia',
						sh: 'icon-a-shouhoutuikuan'
					}
					let order02 = {
						dfk: 'icon-daifukuan-lan',
						dfh: 'icon-daifahuo-lan',
						dsh: 'icon-daishouhuo-lan',
						dpj: 'icon-daipingjia-lan',
						sh: 'icon-shouhou-tuikuan-lan'
					}
					let order03 = {
						dfk: 'icon-daifukuan-ju',
						dfh: 'icon-daifahuo-ju',
						dsh: 'icon-daishouhuo-ju',
						dpj: 'icon-daipingjia-ju',
						sh: 'icon-shouhou-tuikuan-ju'
					}
					let order04 = {
						dfk: 'icon-daifukuan-fen',
						dfh: 'icon-daifahuo-fen',
						dsh: 'icon-daishouhuo-fen',
						dpj: 'icon-daipingjia-fen',
						sh: 'icon-a-shouhoutuikuan-fen'
					}
					let order05 = {
						dfk: 'icon-daifukuan-lv',
						dfh: 'icon-daifahuo-lv',
						dsh: 'icon-daishouhuo-lv',
						dpj: 'icon-daipingjia-lv',
						sh: 'icon-shouhou-tuikuan-lv'
					}
					switch (res.data.diy_data.order_status) {
						case 1:
							this.switchTab(order01)
							break
						case 2:
							this.switchTab(order02)
							break
						case 3:
							this.switchTab(order03)
							break
						case 4:
							this.switchTab(order04)
							break
						case 5:
							this.switchTab(order05)
							break
					}
					that.$set(that, 'MyMenus', myMenu);
					that.$set(that, 'storeMenu', storeMenu);
					this.imgUrls = res.data.routine_my_banner
					this.routineContact = Number(res.data.routine_contact_type)
				});
			},
			// 编辑页面
			goEdit() {
				if (this.isLogin == false || !this.userInfo.uid) {
					//#ifndef MP
					toLogin();
					//#endif
					//#ifdef MP
					this.isShowAuth = true;
					//#endif
				} else {
					// #ifdef MP
					if (this.userInfo.is_default_avatar) {
						this.editModal = true
						return
					}
					// #endif
					uni.navigateTo({
						url: '/pages/users/user_set/index'
					})
				}
			},
			// 签到
			goSignIn() {
				uni.navigateTo({
					url: '/pages/users/user_sgin/index'
				})
			},
			goMenuPage(url, name) {
				if (this.isLogin) {
					let arr = url.split('@APPID=');
					if (arr.length > 1) {
						//#ifdef MP
						uni.navigateToMiniProgram({
							appId: arr[arr.length - 1], // 此为生活缴费appid
							path: arr[0], // 此为生活缴费首页路径
							envVersion: "release",
							success: res => {
								console.log("打开成功", res);
							},
							fail: err => {
								console.log('sgdhgf', err);
							}
						})
						//#endif
						//#ifndef MP
						this.Tips({
							title: 'h5与app端不支持跳转外部小程序'
						});
						//#endif
					} else {
						if (url == '/pages/extension/customer_list/chat' || url == 'https://chat.crmeb.net/chat/mobile') {
							this.$util.getCustomer(this.userInfo)
						} else {
							if (url.indexOf('http') === -1) {
								// #ifdef H5
								if (name && name === '订单核销') {
									return window.location.href = `${location.origin}${url}`
								}
								// #endif
								// #ifdef MP
								if (url != '#' && url == '/pages/users/user_set/index') {
									uni.openSetting({
										success: function(res) {}
									});
								}
								// #endif
								if (url == '/pages/store_spread/index') {
									storeCardApi().then(res => {
										this.isextension = true
										this.$nextTick(function() {
											this.extension.code = res.data.url
										})
									}).catch(err => {
										uni.hideLoading()
										this.$util.Tips({
											title: err
										})
									})
								}
								if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart', '/pages/user/index', '/pages/store_cate/store_cate','/pages/index/index']
									.indexOf(url) == -1) {
									uni.navigateTo({
										url: url
									})
								} else {
									uni.reLaunch({
										url: url
									})
								}
							} else {
								uni.navigateTo({
									url: `/pages/annex/web_view/index?url=${url}`
								});
							}
						}
					}
				}
			}
		}
	}
</script>

<style lang="scss">
	.style-add {
		flex: 1;
		overflow: hidden;
	}

	.height-add {
		height: 100%;
	}

	.display-add {
		height: 100%;
		display: flex;
		align-items: center;
	}

	page,
	body {
		height: 100%;
	}

	.heg {
		height: 670rpx !important;
	}

	.mark {
		position: fixed;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
		background: rgba(0, 0, 0, 0.5);
		z-index: 30;
	}

	.height {
		margin-top: -84rpx !important;
	}

	.menus-list {
		width: 690rpx;
		background-color: #fff;
		border-radius: 14rpx;
		margin: 25rpx auto 0 auto;

		.title {
			font-weight: 600;
			font-size: 30rpx;
			color: #333;
			padding: 30rpx 30rpx 10rpx 30rpx;
		}

		.item {
			margin-left: 30rpx;
			font-size: 30rpx;
			color: #333;

			image {
				width: 42rpx;
				height: 42rpx;
				display: block;
			}

			.text {
				width: 590rpx;
				border-bottom: 1rpx solid #EEEEEE;
				padding: 28rpx 30rpx 28rpx 0;

				.iconfont {
					font-size: 20rpx;
					color: #8A8A8A;
				}
			}
		}
	}

	.num-wrapper {
		background-color: #fff;
		z-index: 29;
		position: relative;
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-top: 22rpx;
		color: #333;
		margin: 30rpx 30rpx 25rpx 30rpx;
		border-radius: 16rpx;
		height: 160rpx;

		&.on {
			height: 128rpx;
			margin-top: -68rpx;

			.gift {
				color: var(--view-theme);
				margin-left: 10rpx;
				font-weight: bold;
			}
		}

		.img {
			margin-left: 38rpx;
			width: 76rpx;
			height: 68rpx;
		}

		.text {
			font-size: 28rpx;
			font-weight: 400;
			color: #333333;
			margin-left: -60rpx;
		}

		.btn-login {
			width: 144rpx;
			height: 56rpx;
			// background: linear-gradient(270deg, #FF7931 0%, #E93323 100%);
			background-color: var(--view-theme);
			border-radius: 28rpx;
			text-align: center;
			line-height: 56rpx;
			margin-right: 32rpx;
			font-size: 28rpx;
			font-weight: 500;
			color: #FFFFFF;

		}

		.num-item {
			width: 33.33%;
			text-align: center;

			&~.num-item {
				position: relative;

				&:before {
					content: '';
					position: absolute;
					width: 1rpx;
					height: 28rpx;
					top: 50%;
					margin-top: -14rpx;
					background-color: rgba(255, 255, 255, 0.4);
					left: 0;
				}
			}

			.num {
				font-size: 36rpx;
				font-weight: bold;
			}

			.txt {
				margin-top: 8rpx;
				font-size: 26rpx;
				color: #555;
			}
		}
	}

	// 新人专享
	.newcomersCon {
		background-color: var(--view-theme);
		width: 690rpx;
		height: 296rpx;
		margin: 0 30rpx;
		border-radius: 16rpx;

		&.on {
			background-color: #fff;

			.newcomers {
				.title {
					.tl {
						color: #333;
					}

					.tr {
						width: unset;
						color: #666666;
						background: unset;
					}
				}

				.scroll {
					.scroll-item {
						background-color: var(--view-minorColorT);
					}
				}
			}
		}

		.newcomers {
			padding: 24rpx 30rpx;
			width: 100%;
			height: 100%;
			background-repeat: no-repeat;
			background-size: 100% 100%;

			.title {
				display: flex;
				justify-content: space-between;

				.tl {
					font-size: 30rpx;
					font-weight: 600;
					color: #FFFFFF;
				}

				.tr {
					width: 90rpx;
					height: 36rpx;
					background: linear-gradient(135deg, #FFCA52 0%, #FE960F 100%);
					border-radius: 18rpx;
					font-size: 24rpx;
					text-align: center;
					line-height: 36rpx;
					font-weight: 400;
					color: #FFFFFF;

					.icon-xiangyou {
						font-size: 16rpx;
					}
				}
			}

			.scroll {
				margin-top: 20rpx;
				white-space: nowrap;
				// padding-right: 60rpx;

				.scroll-item {
					display: inline-block;
					width: 162rpx;
					height: 194rpx;
					background-color: #fff;
					border-radius: 12rpx;
					margin-right: 10rpx;
					padding: 12rpx;

					.pictrue {
						width: 138rpx;
						height: 138rpx;
						position: relative;

						image {
							width: 100%;
							height: 100%;
						}

						.label {
							width: 98rpx;
							height: 32rpx;
							background: var(--view-theme);
							border-radius: 16rpx;
							color: #FFFFFF;
							font-size: 24rpx;
							text-align: center;
							line-height: 32rpx;
							position: absolute;
							bottom: 2rpx;
							left: 50%;
							margin-left: -49rpx;
						}
					}

					.money {
						font-size: 24rpx;
						color: var(--view-priceColor);
						margin-top: 6rpx;
					}
				}
			}
		}
	}

	.Tnum-wrapper {
		margin: -34rpx 30rpx 25rpx 30rpx;
	}

	.unNum-wrapper {
		margin: 0 auto 25rpx auto;
		width: 690rpx;
	}

	.unBg {
		background-color: unset !important;

		.user-info {
			.info {
				.name {
					color: #333333 !important;
					font-weight: 600;
				}

				.num {
					color: #333 !important;

					.num-txt {
						height: 38rpx;
						background-color: rgba(51, 51, 51, 0.13);
						padding: 0 12rpx;
						border-radius: 16rpx;
					}
				}
			}
		}

		.qrCode {
			color: #333 !important;
		}

		.message {
			.iconfont {
				color: #333 !important;
			}

			.num {
				color: #fff !important;
				background-color: var(--view-theme) !important;
			}
		}

		.setting {
			.iconfont {
				color: #333 !important;
			}
		}
	}

	.cardVipB {
		background-color: #343A48;
		width: 100%;
		height: 124rpx;
		border-radius: 16rpx 16rpx 0 0;
		padding: 22rpx 30rpx 0 30rpx;
		margin-top: 16px;

		&.on {
			width: 690rpx;
			height: 130rpx;
			background-repeat: no-repeat;
			background-size: 100% 100%;
			color: #fff;
			margin-left: auto;
			margin-right: auto;
			padding: 26rpx 30rpx 0 90rpx;

			.iconfont {
				font-size: 24rpx;
				margin-left: 6rpx;
			}
		}

		.left-box {
			.small {
				color: #F8D5A8;
				font-size: 28rpx;
				margin-left: 18rpx;
			}

			.pictrue {
				width: 40rpx;
				height: 45rpx;

				image {
					width: 100%;
					height: 100%;
				}
			}
		}

		.btn {
			color: #BBBBBB;
			font-size: 26rpx;

			&.on {
				color: #fff;
			}
		}

		.icon-xiangyou {
			margin-top: 6rpx;
		}
	}

	.cardVipA {
		position: absolute;
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 750rpx;
		height: 84rpx;
		bottom: -2rpx;
		left: 0;
		padding: 0 56rpx 0 135rpx;

		.left-box {
			font-size: 26rpx;
			color: #905100;
			font-weight: 400;

			&.on {
				color: #fff;
			}
		}

		.btn {
			color: #905100;
			font-weight: 400;
			font-size: 24rpx;

			&.on {
				color: #fff;
			}
		}

		.iconfont {
			font-size: 20rpx;
			margin: 4rpx 0 0 4rpx;

			&.on {
				color: #fff;
				margin-top: 0;
			}
		}
	}

	.new-users {
		display: flex;
		flex-direction: column;
		height: 100%;

		.sys-head {
			position: relative;
			width: 100%;
			// background: linear-gradient(90deg, $bg-star1 0%, $bg-end1 100%);

			.bg {
				position: absolute;
				left: 0;
				top: 0;
				width: 100%;
				height: 100%;
				// background: url("~@/static/images/user-sys.png") no-repeat;
				background: var(--view-theme);
				background-size: 100% auto;
				background-position: left bottom;
			}

			.sys-title {
				z-index: 10;
				position: relative;
				height: 43px;
				text-align: center;
				line-height: 43px;
				font-size: 34rpx;
				color: #FFFFFF;
			}
		}

		.head {
			// background: #fff;

			.user-card {
				position: relative;
				width: 100%;
				height: 270rpx;
				margin: 0 auto;
				padding: 35rpx 28rpx;
				background-size: 100% auto;
				background-color: var(--view-theme);

				.user-info {
					z-index: 20;
					position: relative;
					display: flex;

					.headwear {
						position: absolute;
						right: -4rpx;
						top: -14rpx;
						width: 44rpx;
						height: 44rpx;

						image {
							width: 100%;
							height: 100%;
						}
					}

					.live {
						width: 28rpx;
						height: 28rpx;
						display: block;
						margin-left: 14rpx;
					}

					.bntImg {
						width: 120rpx;
						height: 120rpx;
						border-radius: 50%;
						text-align: center;
						line-height: 120rpx;
						background-color: unset;
						position: relative;

						.avatarName {
							font-size: 16rpx;
							color: #fff;
							text-align: center;
							background-color: rgba(0, 0, 0, 0.6);
							height: 37rpx;
							line-height: 37rpx;
							position: absolute;
							bottom: 0;
							left: 0;
							width: 100%;
						}
					}

					.avatar-box {
						position: relative;
						display: flex;
						align-items: center;
						justify-content: center;
						width: 120rpx;
						height: 120rpx;
						border-radius: 50%;

						&.on {
							.avatar {
								border: 2px solid #FFAC65;
								border-radius: 50%;
								box-sizing: border-box;
							}
						}
					}

					.avatar {
						position: relative;
						width: 120rpx;
						height: 120rpx;
						border-radius: 50%;

					}

					.info {
						width: 346rpx;
						flex: 1;
						display: flex;
						flex-direction: column;
						justify-content: space-between;
						margin-left: 20rpx;
						padding: 20rpx 0;

						.name {
							display: flex;
							align-items: center;
							color: #fff;
							font-size: 31rpx;
							width: 346rpx;

							.nickname {
								max-width: 196rpx;
								display: block;
							}

							.vip {
								margin-left: 10rpx;
								width: 78rpx;
								height: 30rpx;
								image {
									width: 100%;
									height: 100%;
									display: block;
								}
							}
						}

						.num {
							display: flex;
							align-items: center;
							font-size: 24rpx;
							color: rgba(255, 255, 255, 0.6);

							image {
								width: 22rpx;
								height: 23rpx;
								margin-left: 20rpx;
							}
						}
					}
				}

				.qrCode {
					color: #fff;
					margin-top: 18rpx !important;
					font-size: 38rpx;
					margin-right: 36rpx !important;
				}

				.message {
					align-self: flex-start;
					position: relative;
					margin-top: 15rpx;
					margin-right: 20rpx;

					.num {
						position: absolute;
						top: -8rpx;
						left: 18rpx;
						padding: 0 6rpx;
						height: 28rpx;
						border-radius: 12rpx;
						background-color: #fff;
						font-size: 18rpx;
						line-height: 28rpx;
						text-align: center;
						color: var(--view-theme);
					}

					.iconfont {
						font-size: 40rpx;
						color: #fff;
					}
				}

				.sign {
					z-index: 200;
					position: absolute;
					right: -12rpx;
					top: 80rpx;
					display: flex;
					align-items: center;
					justify-content: center;
					width: 120rpx;
					height: 60rpx;
					background: linear-gradient(90deg, rgba(255, 225, 87, 1) 0%, rgba(238, 193, 15, 1) 100%);
					border-radius: 29rpx 4rpx 4rpx 29rpx;
					color: #282828;
					font-size: 28rpx;
					font-weight: bold;
				}
			}

			.order-wrapper {
				background: #fff;
				margin: 0 30rpx;
				border-radius: 16rpx;
				position: relative;

				.order-hd {
					justify-content: space-between;
					padding: 30rpx 20rpx 10rpx;
					margin-top: 25rpx;
					font-size: 30rpx;
					color: #282828;

					.left {
						font-weight: bold;
					}

					.right {
						align-items: center;
						color: #666666;
						font-size: 26rpx;

						.icon-xiangyou {
							margin-left: 5rpx;
							margin-top: 6rpx;
							font-size: 26rpx;
						}
					}
				}

				.order-bd {
					display: flex;
					padding: 0 0;

					.order-item {
						display: flex;
						flex-direction: column;
						justify-content: center;
						align-items: center;
						width: 20%;
						height: 140rpx;

						.pic {
							position: relative;
							text-align: center;

							.iconfont {
								font-size: 48rpx;
								color: var(--view-theme);
							}

							image {
								width: 58rpx;
								height: 48rpx;
							}
							
							.uni-badge-left-margin{
								position: absolute;
								// #ifdef MP
								top:-22rpx;
								// #endif
								/deep/.uni-badge--error{
									background-color: #fff !important;
								}
								/deep/.uni-badge{
									color: var(--view-theme);
									border: 1px solid var(--view-theme);
								}
							}
						}

						.txt {
							margin-top: 6rpx;
							font-size: 26rpx;
							color: #454545;
						}
					}
				}
			}
		}

		.slider-wrapper {
			margin: 20rpx 30rpx;
			height: 130rpx;

			swiper,
			swiper-item {
				height: 100%;
			}

			image {
				width: 100%;
				height: 130rpx;
				border-radius: 16rpx;
			}
		}

		.user-menus {
			background-color: #fff;
			margin: 0 30rpx;
			border-radius: 16rpx;

			.menu-title {
				padding: 30rpx 30rpx 40rpx;
				font-size: 30rpx;
				color: #282828;
				font-weight: bold;
			}

			.list-box {
				display: flex;
				flex-wrap: wrap;
				padding: 0;
			}

			.item {
				position: relative;
				display: flex;
				align-items: center;
				justify-content: space-between;
				flex-direction: column;
				width: 25%;
				margin-bottom: 47rpx;
				font-size: 26rpx;
				color: #333333;

				image {
					width: 52rpx;
					height: 52rpx;
					margin-bottom: 18rpx;
				}


				&:last-child::before {
					display: none;
				}
			}

			button {
				font-size: 28rpx;
			}
		}

		.phone {
			color: #fff;
			background-color: #CCC;
			border-radius: 15px;
			width: max-content;
			padding: 2px 10px;
			font-size: 20rpx;
			margin-top: 4px;
		}

		.order-status-num {
			min-width: 12rpx;
			background-color: #fff;
			color: var(--view-theme);
			border-radius: 15px;
			position: absolute;
			right: -14rpx;
			top: -15rpx;
			font-size: 20rpx;
			padding: 0 8rpx;
			border: 1px solid var(--view-theme);
		}

		.support {
			width: 219rpx;
			height: 74rpx;
			margin: 54rpx auto;
			display: block;
		}

		.copyRightImg {
			width: 219rpx;
			height: 74rpx;
			margin: 16rpx auto;
			display: block;
		}

		.copy_right {
			text-align: center;
			color: #ccc;
			font-size: 22rpx;
			margin-top: 40rpx;

			.copyRightText {
				margin-top: 0rpx;
				color: #ccc;
				font-size: 20rpx;
				margin-bottom: 20rpx;
			}
		}
	}

	.card-vip {
		display: flex;
		justify-content: space-between;
		position: relative;
		width: 690rpx;
		height: 125rpx;
		margin: -72rpx auto 0;
		background: url('~@/static/images/user_vip.png');
		background-size: cover;
		padding: 28rpx 30rpx 0 92rpx;

		&.on {
			.left-box {
				color: #fff;
			}

			.btn {
				color: #fff;
			}

			.iconfont {
				color: #fff;
			}
		}

		.left-box {
			font-size: 28rpx;
			color: #AE5A2A;

			.big {
				font-size: 28rpx;
			}

			.small {
				opacity: 0.8;
			}
		}

		.btn {
			color: #AE5A2A;
			font-weight: 400;
			font-size: 26rpx;
		}

		.iconfont {
			font-size: 20rpx;
			margin: 4rpx 0 0 4rpx;
			color: #AE5A2A;
		}
	}

	.setting {
		margin-top: 15rpx;
		margin-left: 15rpx;
		color: #fff;

		.iconfont {
			font-size: 40rpx;
		}
	}

	.new-users {
		padding-bottom: 0;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
	}
</style>
