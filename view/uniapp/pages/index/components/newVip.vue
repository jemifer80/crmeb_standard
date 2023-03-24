<template>
    <view class="newVip" :class="{pageOn:itemStyle===0}" :style="{marginLeft:prConfig+'px',marginRight:prConfig+'px',marginTop:mbCongfig+'px',background: `linear-gradient(90deg,${bgColor[0].item} 0%,${bgColor[1].item} 100%)`}" v-show="!isSortType" v-if="list.length">
        <view class="header acea-row row-between-wrapper" :style="{color:textColor}">
            <view class="title" >新人专享</view>
            <view class="more" @click="goNewList">更多<text class="iconfont icon-xiangyou"></text></view>
        </view>
        <view class="list">
					<scroll-view scroll-x="true" class="scroll" show-scrollbar="false">
            <view class="item" v-for="(item,index) in list" :key="index" @click="goDetail(item)">
                <view class="pictrue">
										<image :src="item.image"></image>
                    <view class="label" :style="{color:textColor,background: `linear-gradient(90deg,${bgColor[0].item} 0%,${bgColor[1].item} 100%)`}" v-if="checkType.indexOf(0) != -1">新人价</view>
                </view>
                <view class="money" :style="{color:priceColor}">¥{{item.price}}</view>
                <view class="y_money" v-if="checkType.indexOf(1) != -1">¥{{item.ot_price}}</view>
            </view>
					</scroll-view>
        </view>
    </view>
</template>

<script>
	import {
		newcomerList
	} from '@/api/api.js';
	import {
		mapGetters
	} from 'vuex';
	export default {
		computed: mapGetters(['isLogin']),
		name: 'userInfor',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		data() {
			return {
				bgColor: this.dataConfig.bgColor.color,
				mbCongfig: this.dataConfig.mbCongfig.val*2,
				prConfig: this.dataConfig.prConfig.val*2, //背景边距
				itemStyle: this.dataConfig.itemStyle.type,
				checkType: this.dataConfig.checkboxInfo.type,
				textColor: this.dataConfig.textColor.color[0].item,
				priceColor: this.dataConfig.priceColor.color[0].item,
				numConfig: this.dataConfig.numConfig.val,
				list: []
			}
		},
		created() {
			this.getList();
		},
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getList();
					}
				},
				deep: true
			}
		},
		mounted() {
		},
		methods: {
			goDetail(item){
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}&fromPage='newVip'`
				});
			},
			goNewList(){
				uni.navigateTo({
					url: `/pages/store/newcomers/index`
				});
			},
			getList(){
				let limit = this.$config.LIMIT;
				let type = this.dataConfig.itemSort.type;
				newcomerList({
					page: 1,
					limit: this.numConfig >= limit ? limit : this.numConfig,
					priceOrder: type == 2 ? 'desc' : '',
					salesOrder: type == 1 ? 'desc' : ''
				}).then(res=>{
					this.list = res.data;
				}).catch(err=>{
					return this.$util.Tips({
						title: err.msg
					});
				})
			}
		}
	}
</script>

<style lang="scss">
    .pageOn{
        border-radius: 16rpx;
    }
    .newVip{
        padding: 0 30rpx 22rpx 30rpx;
				
				.scroll {
				  white-space: nowrap; 
				  display: flex;
				}
				
        .header{
            height: 78rpx;
            .title{
                font-size: 30rpx;
								font-weight: 600;
            }
            .more{
                font-size: 24rpx;
                .iconfont{
                    font-size: 24rpx;
                    margin-left: 4rpx;
                }
            }
        }
        .list{
					flex-wrap: nowrap;
					overflow: hidden;
            .item{
							  display: inline-block;
                width: 172rpx;
                background: #FFFFFF;
                border-radius: 12rpx;
                padding: 12rpx 12rpx 6rpx 12rpx;
                margin-right: 12rpx;
                .pictrue{
                    width: 148rpx;
                    height: 148rpx;
                    position: relative;
                    image{
                        width: 100%;
                        height: 100%;
                    }
                    .label{
                        width: 98rpx;
                        height: 32rpx;
                        background: #E93323;
                        border-radius: 16rpx;
                        position: absolute;
                        bottom: 12rpx;
                        left:50%;
                        margin-left: -49rpx;
                        font-size: 24rpx;
                        color: #fff;
                        text-align: center;
                        line-height: 32rpx;
                    }
                }
                .money{
                    font-weight: 500;
                    color: #E93323;
                    font-size: 24rpx;
                    margin-top: 6rpx;
                }
                .y_money{
                    font-weight: 400;
                    color: #CCCCCC;
                    font-size: 20rpx;
                    text-decoration: line-through;
                }
            }
        }
    }
</style>