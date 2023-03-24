<template>
	<view>
		<view class="title">活动规则</view>
		<view class="conter">
			<jyf-parser :html="info" ref="article" :tag-style="tagStyle"></jyf-parser>
		</view>
	</view>
</template>

<script>
	import colors from "@/mixins/color";
	export default {
		mixins: [colors],
		components: {},
		data() {
			return {
				tagStyle: {
				  img: 'width:100%;display:block;',
				  table: 'width:100%',
				  video: 'width:100%'
				},
				info:''
			};
		},
		onLoad() {
			this.info = uni.getStorageSync('infos');
		},
		onReady() {},
		onShow() {},
		methods: {
		},
		onReachBottom() {
		}
	}
</script>

<style>
	page{
		background-color: #fff;
	}
	.title{
		font-weight: 600;
		color: #333333;
		font-size: 36rpx;
		height: 100rpx;
		line-height: 100rpx;
		text-align: center;
	}
	.conter{
		padding: 0 30rpx;
	}
</style>