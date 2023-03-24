<template>
  <view class="waterfalls-box" :style="{ height: height + 'px' }">
    <!--  #ifdef  MP-WEIXIN -->
    <view
      v-for="(item, index) of list"
      class="waterfalls-list"
      :key="item[idKey]"
      :id="'waterfalls-list-id-' + item[idKey]"
      :ref="'waterfalls-list-id-' + item[idKey]"
      :style="{
        '--offset': offset + 'px',
        '--cols': cols,
        top: allPositionArr[index].top || 0,
        left: allPositionArr[index].left || 0,
      }"
      @click="$emit('wapper-lick', item)"
    >
		  <view class="pictrue">
				<image
				  class="waterfalls-list-image"
				  mode="aspectFill"
				  :class="{ single }"
                  :style="imageStyle"
				  :src="item[imageSrcKey] || ' '"
				  @load="imageLoadHandle(index)"
				  @error="imageLoadHandle(index)"
				  @click="$emit('image-click', item)"
				/>
				<view class="masks acea-row row-center-wrapper" v-if="item.stock<=0">
					<view class="bg">
						<view>暂时</view>
						<view>售罄</view>
					</view>
				</view>
				<video
					v-if="product_video_status && item.video_link"
					:src="item.video_link"
					:controls="false"
					:show-center-play-btn="false"
					:id="`video${item.id}`"
					:poster="item[imageSrcKey] || ''"
					class="video"
					objectFit="cover"
					loop
					muted
				></video>
				<view class="activityFrame" v-if="item.activity_frame.image" :style="'background-image: url('+item.activity_frame.image+');'"></view>
			</view>
      <slot name="slot{{index}}" />
    </view>
    <!--  #endif -->
    <!--  #ifndef  MP-WEIXIN -->
    <view
      v-for="(item, index) of list"
      class="waterfalls-list"
      :key="item[idKey]"
      :id="'waterfalls-list-id-' + item[idKey]"
      :ref="'waterfalls-list-id-' + item[idKey]"
      :style="{
        '--offset': offset + 'px',
        '--cols': cols,
        ...listStyle,
        ...(allPositionArr[index] || {}),
      }"
      @click="$emit('wapper-lick', item)"
    >
	  <view class="pictrue">
		  <image
		    class="waterfalls-list-image"
		    :class="{ single }"
		    mode="aspectFill"
		    :style="imageStyle"
		    :src="item[imageSrcKey] || ' '"
		    @load="imageLoadHandle(index)"
		    @error="imageLoadHandle(index)"
		    @click="$emit('image-click', item)"
		  />
			<view class="masks acea-row row-center-wrapper" v-if="item.stock<=0">
				<view class="bg">
					<view>暂时</view>
					<view>售罄</view>
				</view>
			</view>
			<!-- #ifdef H5 -->
			<video
				v-if="product_video_status && item.video_link"
				:src="item.video_link"
				:controls="false"
				:show-center-play-btn="false"
				:id="`video${item.id}`"
				:poster="item[imageSrcKey] || ''"
				objectFit="cover"
				class="video"
				loop
				muted
			></video>
			<!-- #endif -->
			<view class="activityFrame" v-if="item.activity_frame.image" :style="'background-image: url('+item.activity_frame.image+');'"></view>
	  </view>
      <slot v-bind="item" />
    </view>
    <!--  #endif -->
  </view>
</template>
<script>
import store from '../../store';
import {
		diyProduct
	} from '@/api/store.js';
	import {
		mapMutations
	} from 'vuex';
export default {
  props: {
    list: { type: Array, required: true },
    // offset 间距，单位为 px
    offset: { type: Number, default: 10 },
    // 列表渲染的 key 的键名，值必须唯一，默认为 id
    idKey: { type: String, default: "id" },
    // 图片 src 的键名
    imageSrcKey: { type: String, default: "image" },
    // 列数
    cols: { type: Number, default: 2, validator: (num) => num >= 2 },
    imageStyle: { type: Object },

    // 是否是单独的渲染图片的样子，只控制图片圆角而已
    single: { type: Boolean, default: false },

    // #ifndef MP-WEIXIN
    listStyle: { type: Object },
    // #endif
  },
  data() {
    return {
      topArr: [], // left, right 多个时依次表示第几列的数据
      allPositionArr: [], // 保存所有的位置信息
      allHeightArr: [], // 保存所有的 height 信息
      height: 0, // 外层包裹高度
      oldNum: 0,
      num: 0,
	  updateCount: 0,
	  product_video_status: false
    };
  },
  watch: {
  	list: {
		handler(newValue, oldValue) {
			if (!newValue.length) {
				return;
			}
			this.$nextTick(() => {
				this.updateCount++;
				if (!this.product_video_status) {
					return;
				}
				uni.getNetworkType({
					success: (res) => {
						if (['wifi', 'unknown'].includes(res.networkType)) {
							// 监听
							this.observeVideo();
						}
						if (['2g', '3g', '4g', '5g'].includes(res.networkType)) {
							if (!this.$store.state.app.autoplay) {
								if (this.updateCount != 1) {
									return;
								}
								return uni.showModal({
									content: '当前使用移动网络，是否继续播放视频？',
									success: (res) => {
										if (res.confirm) {
											// 监听
											this.SET_AUTOPLAY(true);
											this.observeVideo();
										}
									}
								});
							}
							// 监听
							this.observeVideo();
						}
					}
				});
			});
		},
		immediate: true
  	}
  },
  created() {
	  let that = this;
    this.refresh();
	let product_video_status = null;
	try{
		product_video_status = JSON.parse(uni.getStorageSync('product_video_status'));
	}catch(e){
		//TODO handle the exception
	}
	if (typeof product_video_status == 'boolean') {
		this.product_video_status = product_video_status;
	} else{
		this.getdiyProduct();
	}
  },
  methods: {
	  ...mapMutations(['SET_AUTOPLAY']),
	  observeVideo() {
	  	let observer = uni.createIntersectionObserver(this, { observeAll: true });
	  	observer.relativeToViewport().observe('.video', res => {
	  		if (res.intersectionRatio) {
				setTimeout(() => {
					uni.createVideoContext(res.id, this).play();
				}, 200)
	  		} else{
				setTimeout(() => {
					uni.createVideoContext(res.id, this).pause();
				}, 200)
	  		}
	  	});
		this.$once('hook:beforeDestroy', () => {
			observer.disconnect();
		});
	  },
	  // div商品详情
	getdiyProduct() {
		diyProduct().then(res => {
			uni.setStorageSync('product_video_status',JSON.stringify(res.data.product_video_status))
			this.product_video_status = res.data.product_video_status;
		})
	},
    imageLoadHandle(index) {
			if(!this.list.length){
				return
			}
      const id = "waterfalls-list-id-" + this.list[index][this.idKey],
        query = uni.createSelectorQuery().in(this);
      query
        .select("#" + id)
        .fields({ size: true }, (data) => {
          this.num++;
          this.$set(this.allHeightArr, index, data.height);
          if (this.num === this.list.length) {
            for (let i = this.oldNum; i < this.num; i++) {
              const getTopArrMsg = () => {
                let arrtmp = [...this.topArr].sort((a, b) => a - b);
                return {
                  shorterIndex: this.topArr.indexOf(arrtmp[0]),
                  shorterValue: arrtmp[0],
                  longerIndex: this.topArr.indexOf(arrtmp[this.cols - 1]),
                  longerValue: arrtmp[this.cols - 1],
                };
              };

              const { shorterIndex, shorterValue } = getTopArrMsg();
              const position = {
                top: shorterValue + "px",
                left: (data.width + this.offset) * shorterIndex + "px",
              };
              this.$set(this.allPositionArr, i, position);
              this.topArr[shorterIndex] =
                shorterValue + this.allHeightArr[i] + this.offset;
              this.height = getTopArrMsg().longerValue - this.offset;
            }
            this.oldNum = this.num;
            // 完成渲染 emit `image-load` 事件
            this.$emit("image-load");
          }
        })
        .exec();
    },
    refresh() {
      let arr = [];
      for (let i = 0; i < this.cols; i++) {
        arr.push(0);
      }
      this.topArr = arr;
      this.num = 0;
      this.oldNum = 0;
      this.height = 0;
    },
  },
};
</script>
<style lang="scss" scoped>
// 这里可以自行配置
$border-radius: 10px;

.waterfalls-box {
  position: relative;
  width: 100%;
  overflow: hidden;
  .waterfalls-list {
    width: 346rpx;
    position: absolute;
    background-color: #fff;
    border-radius: $border-radius;
    // 防止刚开始渲染时堆叠在第一幅图的地方
    left: calc(-50% - var(--offset));
	.pictrue{
		position: relative;
		.masks{
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0, 0, 0, 0.2);
			border-radius: 20rpx 20rpx 0 0;
			.bg{
				width: 152rpx;
				height: 152rpx;
				background: #000000;
				opacity: 0.6;
				color: #fff;
				font-size: 32rpx;
				border-radius: 50%;
				padding: 34rpx 0;
				text-align: center;
			}
		}
		.activityFrame{
			border-radius: $border-radius $border-radius 0 0;
		}
	}
	
	
	
    .waterfalls-list-image {
	  width: 100%;
	  height: 346rpx !important;
      will-change: transform;
      border-radius: $border-radius $border-radius 0 0;
      display: block;
      &.single {
        border-radius: $border-radius;
      }
    }
  }
  
  .video {
	  position: absolute;
	  top: 0;
	  left: 0;
	  width: 100%;
	  height: 346rpx !important;
	  border-radius: $border-radius $border-radius 0 0;
  }
}
</style>
