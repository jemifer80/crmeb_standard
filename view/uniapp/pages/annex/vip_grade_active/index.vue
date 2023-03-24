<template>
	<view class="gradeActive">
		<view class="headerBg"></view>
		<view class="header" :style="{backgroundImage:'url('+imgHost+'/statics/images/grade-active.png'+')'}">
			<view class="title">激活会员卡</view>
			<view>新用户免费激活会员卡</view>
		</view>
		<view class="conter">
			<view class="headerT acea-row row-center-wrapper">
				<view class="pictrue">
					<image src="../static/left.png"></image>
				</view>
				<view class="name">{{list.length?'填写以下信息':'我的成长特权'}}</view>
				<view class="pictrue on">
					<image src="../static/left.png"></image>
				</view>
			</view>
			<view class="list acea-row row-between-wrapper" v-if="!list.length">
				<view class="itemNo">
					<view class="pictrue">
						<image :src="imgHost+'/statics/images/userVip1.png'"></image>
					</view>
					<view>购物折扣</view>
				</view>
				<view class="itemNo">
					<view class="pictrue">
						<image :src="imgHost+'/statics/images/userVip2.png'"></image>
					</view>
					<view>专属徽章</view>
				</view>
				<view class="itemNo">
					<view class="pictrue">
						<image :src="imgHost+'/statics/images/userVip3.png'"></image>
					</view>
					<view>经验累积</view>
				</view>
				<view class="itemNo">
					<view class="pictrue">
						<image :src="imgHost+'/statics/images/userVip4.png'"></image>
					</view>
					<view>尊享客服</view>
				</view>
				<image class="vipBg" src="../static/vipBg.png"></image>
			</view>
			<view class="item" v-for="(item,index) in list" :key="index">
				<view class="name"><text class="asterisk" v-if="item.required==1">*</text><text>{{item.info}}</text></view>
				<!-- text -->
				<view class="input" v-if="item.format == 'text'">
					<input type="text" :placeholder="item.tip" placeholder-class="placeholder" v-model="item.value" />
				</view>
				<!-- number -->
				<view class="input" v-if="item.format=='num'">
					<input type="number" :placeholder="item.tip" placeholder-class="placeholder" v-model="item.value" />
				</view>
				<!-- email -->
				<view class="input" v-if="item.format=='mail'">
					<input type="text" :placeholder="item.tip" placeholder-class="placeholder" v-model="item.value" />
				</view>
				<!-- date -->
				<view class="input" v-if="item.format=='date'">
					<picker mode="date" :value="item.value" @change="bindDateChange($event,index)">
						<view class="acea-row row-between-wrapper">
							<view v-if="item.value == ''">{{item.tip}}</view>
							<view v-else>{{item.value}}</view>
							<text class='iconfont icon-jiantou'></text>
						</view>
					</picker>
				</view>
				<!-- id -->
				<view class="input" v-if="item.format=='id'">
					<input type="idcard" :placeholder="item.tip" placeholder-class="placeholder" v-model="item.value" />
				</view>
				<!-- phone -->
				<view class="input" v-if="item.format=='phone'">
					<input type="tel" :placeholder="item.tip" placeholder-class="placeholder" v-model="item.value" />
				</view>
				<!-- radio -->
				<view class="input" v-if="item.format=='radio'">
					<radio-group @change="radioChange($event,index)">
						<label class="label">
							<radio value="0" :checked="item.value == 0" />{{item.singlearr[0]}}
						</label>
						<label>
							<radio value="1" :checked="item.value == 1" />{{item.singlearr[1]}}
						</label>
					</radio-group>
				</view>
				<!-- address -->
				<view class="input" @click="addressList" v-if="item.format=='address'">
					<picker mode="multiSelector" @change="bindRegionChange($event,index)"
						@columnchange="bindMultiPickerColumnChange" :value="valueRegion"
						:range="multiArray">
						<view class='acea-row row-between-wrapper'>
							<view class="picker">{{region[0]}}，{{region[1]}}，{{region[2]}}</view>
							<text class='iconfont icon-jiantou'></text>
						</view>
					</picker>
				</view>
			</view>
		</view>
		<view class="bnt" @click="activate">确认激活</view>
		<ewcomerPop v-if="isComerGift" :fromActive="1" :comerGift="comerGift" @comerPop="comerPop"></ewcomerPop>
	</view>
</template>
<script>
	import {
		levelInfo,
		levelActivate
	} from '@/api/user.js';
	import {
		getCity
	} from '@/api/api.js';
	import {
	  HTTP_REQUEST_URL
	} from '@/config/app';
	import ewcomerPop from '@/components/ewcomerPop/index.vue'
	export default {
		components: {
			ewcomerPop
		},
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				list:[],
				district: [],
				multiArray: [],
				multiIndex: [0, 0, 0],
				valueRegion: [0, 0, 0],
				region: ['省', '市', '区'],
				comerGift:{},
				isComerGift:false
			};
		},
		onLoad() {
			this.getInfo();
		},
		onReady() {},
		onShow() {},
		methods: {
			comerPop(){
				this.isComerGift = false;
				uni.navigateTo({
					url: '/pages/annex/vip_grade/index'
				})
			},
			activate(){
				let that = this;
				for (var i = 0; i < that.list.length; i++) {
					let data = that.list[i]
					if (data.required || data.value) {
						if (data.format === 'date' || data.format === 'address') {
							if (!data.value) {
								return that.$util.Tips({
									title: `${data.tip}`
								});
							}
						}
						if(data.format === 'text'){
							if (!data.value.trim()) {
								return that.$util.Tips({
									title: `${data.tip}`
								});
							}
						}
						if (data.format === 'num') {
							if (data.value <= 0) {
								return that.$util.Tips({
									title: `${data.tip}`
								});
							}
						}
						if (data.format === 'mail') {
							if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(data.value)) {
								return that.$util.Tips({
									title: `${data.tip}`
								});
							}
						}
						if (data.format === 'phone') {
							if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(data.value)) {
								return that.$util.Tips({
									title: `${data.tip}`
								});
							}
						}
						if (data.format === 'id') {
							if (!/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i.test(data.value)) {
								return that.$util.Tips({
									title: `${data.tip}`
								});
							}
						}
					}
				}
				levelActivate(this.list).then(res=>{
					this.isComerGift = true;
					res.data['register_give_integral'] = res.data.level_give_integral;
					res.data['register_give_money'] = res.data.level_give_money;
					res.data['register_give_coupon'] = res.data.level_give_coupon;
					res.data['coupon_count'] = res.data.level_give_coupon.length;
					this.comerGift = res.data;
				}).catch(err=>{
					return this.$util.Tips({
						title: err
					});
				})
			},
			// 省市区地址处理逻辑；
			addressList(){
				this.getCityList();
			},
			// 获取地址数据
			getCityList() {
				let that = this;
				getCity().then(res => {
					this.district = res.data
					that.initialize();
				})
			},
			// 处理地址数据
			initialize: function() {
				let that = this,
					province = [],
					city = [],
					area = [];
				if (that.district.length) {
					let cityChildren = that.district[0].c || [];
					let areaChildren = cityChildren.length ? (cityChildren[0].c || []) : [];
					that.district.forEach(function(item) {
						province.push(item.n);
					});
					cityChildren.forEach(function(item) {
						city.push(item.n);
					});
					areaChildren.forEach(function(item) {
						area.push(item.n);
					});
					this.multiArray = [province, city, area]
				}
			},
			bindRegionChange(e,index) {
				let multiIndex = this.multiIndex,
					province = this.district[multiIndex[0]] || {
						c: []
					},
					city = province.c[multiIndex[1]] || {
						v: 0
					},
					multiArray = this.multiArray,
					value = e.detail.value;
			
				this.region = [multiArray[0][value[0]], multiArray[1][value[1]], multiArray[2][value[2]]]
				this.list[index].value = city.v;
				this.valueRegion = [0, 0, 0]
				this.initialize();
			},
			bindMultiPickerColumnChange(e) {
				let that = this,
					column = e.detail.column,
					value = e.detail.value,
					currentCity = this.district[value] || {
						c: []
					},
					multiArray = that.multiArray,
					multiIndex = that.multiIndex;
				multiIndex[column] = value;
				switch (column) {
					case 0:
						let areaList = currentCity.c[0] || {
							c: []
						};
						multiArray[1] = currentCity.c.map((item) => {
							return item.n;
						});
						multiArray[2] = areaList.c.map((item) => {
							return item.n;
						});
						break;
					case 1:
						let cityList = that.district[multiIndex[0]].c[multiIndex[1]].c || [];
						multiArray[2] = cityList.map((item) => {
							return item.n;
						});
						break;
					case 2:
						break;
				}
				// #ifdef MP || APP-PLUS
				this.$set(this.multiArray, 0, multiArray[0]);
				this.$set(this.multiArray, 1, multiArray[1]);
				this.$set(this.multiArray, 2, multiArray[2]);
				// #endif
				// #ifdef H5 
				this.multiArray = multiArray;
				// #endif
				this.multiIndex = multiIndex
			},
			radioChange(e, index){
				this.list[index].value = e.detail.value
			},
			bindDateChange: function(e, index) {
				this.list[index].value = e.target.value
			},
			getInfo(){
				levelInfo().then(res=>{
					res.data.forEach(item=>{
						if(item.format == 'radio'){
							item.value = '0'
						}else{
							item.value = ''
						}
					})
					this.list = res.data;
				}).catch(err=>{
					this.$util.Tips({
						title: err
					})
				})
			}
		},
		onReachBottom() {
		}
	}
</script>
<style lang="scss">
	.gradeActive{
		padding-bottom: 20rpx;
		.headerBg{
			width: 100%;
			height: 264rpx;
			background: linear-gradient(58deg, #FDC683 0%, #FFDAB6 100%);
			border-radius: 0 0 100rpx 100rpx;
		}
		.header{
			background-repeat:no-repeat;
			background-size: 100% 100%;
			width: 690rpx;
			height: 288rpx;
			margin: -236rpx auto 0 auto;
			font-weight: 400;
			color: #EDCAAC;
			font-size: 24rpx;
			padding: 78rpx 48rpx;
			.title{
				font-size: 48rpx;
				font-weight: 600;
				background: linear-gradient(180deg, #FFEFE1 0%, #FFD0A8 100%);
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
				margin-bottom: 25rpx;
			}
		}
		.bnt{
			width: 690rpx;
			height: 88rpx;
			background: linear-gradient(90deg, #FFAE49 0%, #FCC887 100%);
			border-radius: 44rpx;
			font-weight: 500;
			color: #FFFFFF;
			font-size: 30rpx;
			margin: 24rpx auto 0 auto;
			text-align: center;
			line-height: 88rpx;
		}
		.conter{
			background-color: #fff;
			border-radius: 14rpx;
			margin: 26rpx auto 0 auto;
			width: 690rpx;
			padding: 44rpx 48rpx 64rpx 48rpx;
			.list{
				margin-top: 78rpx;
				padding-bottom: 28rpx;
				position: relative;
				.vipBg{
					width: 262rpx;
					height: 174rpx;
					display: block;
					position: absolute;
					right: -48rpx;
					bottom: -64rpx;
				}
				.itemNo{
					font-weight: 400;
					color: #282828;
					font-size: 26rpx;
					position: relative;
					z-index: 1;
					.pictrue{
						width: 90rpx;
						height: 90rpx;
						margin-bottom: 12rpx;
						image{
							width: 100%;
							height: 100%;
							border-radius: 50%;
						}
					}
				}
			}
			.item{
				font-size: 24rpx;
				color: #666;
				height: 156rpx;
				border-bottom: 1px solid #F5F5F5;
				padding-top: 40rpx;
				/deep/uni-radio{
					 vertical-align: bottom;
					 margin-right: 10rpx;
				}
				/deep/uni-radio .uni-radio-input{
					width: 28rpx;
					height: 28rpx;
					border: 1px solid #E6993A;
				}
				/deep/uni-radio .uni-radio-input.uni-radio-input-checked{
					border: 1px solid #E6993A !important;
					background-color: #E6993A !important;
				}
				/deep/.wx-radio-input {
					width: 28rpx;
					height: 28rpx;
					border: 1px solid #E6993A;
				}
				/deep/.wx-radio-input.wx-radio-input-checked {
					border: 1px solid #E6993A !important;
					background-color: #E6993A !important;
				}
				/deep/uni-radio .uni-radio-input.uni-radio-input-checked:before{
					font-size: 24rpx;
				}
				.label{
					margin-right: 80rpx;
				}
				.name{
					position: relative;
					margin-left: 17rpx;
					.asterisk{
						color: #E93323;
						position: absolute;
						color:red;
						left:-15rpx
					}
				}
				.placeholder{
					font-size: 28rpx;
					font-weight: 400;
					color: #CCCCCC;
				}
				.input{
					margin-top: 24rpx;
					padding: 0 30rpx;
					.iconfont{
						font-size: 24rpx;
						color: #999;
					}
					input{
						font-size: 28rpx;
					}
				}
			}
			.headerT{
				font-size: 30rpx;
				color: #CC803B;
				.name{
					margin: 0 40rpx;
				}
				.pictrue{
					width: 124rpx;
					height: 22rpx;
					image{
						width: 100%;
						height: 100%;
						display: block;
					}
					&.on{
						transform: rotateX(180deg);
					}
				}
			}
		}
	}
</style>