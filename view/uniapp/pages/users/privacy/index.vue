<template>
	<view>
		<view class="sys-head">
		  <!-- #ifdef MP || APP-PLUS -->
		  <view class="sys-bar" :style="{height:sysHeight+'px'}"></view>
		  <!-- #endif -->
		  <view class="sys-title">
			  {{type==='privacy'?'隐私协议':'用户协议'}}
				<text class="iconfont icon-fanhui1" @click="goBack"></text>
			</view>
		</view>
		<view class="content" :style="'margin-top:'+(sysHeight+52)+'px;'">
			<jyf-parser :html="content" ref="article" :tag-style="tagStyle"></jyf-parser>
		</view>
	</view>
</template>

<script>
	let sysHeight = uni.getSystemInfoSync().statusBarHeight;
	import parser from "@/components/jyf-parser/jyf-parser";
	import {
		getUserAgreement,
	} from '@/api/user.js';
	export default {
		components: {
			"jyf-parser": parser
		},
		data() {
			return {
				tagStyle: {
					img: 'width:100%;display:block;',
					table: 'width:100%',
					video: 'width:100%'
				},
				content: ``,
				sysHeight: sysHeight || 0,
				type:''
			}
		},
		onLoad(e) {
			this.type = e.type;
			if(e){
				getUserAgreement(e.type).then(res => {
					this.content = res.data.content
				}).catch(err => {
					that.$util.Tips({
						title: err.msg
					});
				})
			}else{
				getUserAgreement('privacy').then(res => {
					this.content = res.data.content
				}).catch(err => {
					that.$util.Tips({
						title: err.msg
					});
				})
			}
		},
		mounted() {},
		methods: {
			goBack(){
				uni.navigateBack({
					delta: 1
				})
			}
		}
	}
</script>

<style scoped lang="scss">
	page{
		background-color: #fff;
	}
	.content {
		padding: 0 30rpx 40rpx 30rpx;
	}
	.sys-head {
		position: fixed;
		width: 100%;
		background-color: #fff;
		top:0;
		left:0;
		z-index: 9;
		.sys-title{
			height: 43px;
			line-height: 43px;
			text-align: center;
			position: relative;
			.iconfont{
				position: absolute;
				left:20rpx;
			}
		}
	}
</style>
