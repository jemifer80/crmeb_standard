<template>
	<!-- 无数据时显示 -->
	<view class="empty-box">
		<image :src="imgHost + '/statics/images/empty-box.png'"></image>
		<view class="txt">{{title}}</view>
		<slot name="bottom"></slot>
	</view>
</template>

<script>
	import {HTTP_REQUEST_URL} from '@/config/app';
	export default{
		props: {
			title: {
				type: String,
				default: '暂无记录',
			},
		},
		data(){
			return{
				imgHost:HTTP_REQUEST_URL
			}
		}
	}
	
</script>

<style lang="scss">
	.empty-box{
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		width: 690rpx;
		height: 760rpx;
		margin: 30rpx auto;
		background: #FFFFFF;
		border-radius: 14rpx;
		background: #fff;
		image{
			width: 414rpx;
			height: 240rpx;
		}
		.txt{
			font-size: 26rpx;
			color: #999;
		}
	}
</style>
