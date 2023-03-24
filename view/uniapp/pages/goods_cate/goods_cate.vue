<template>
  <!-- 商品分类 -->
	<view :style="colorStyle">
    <!-- 商品分类的三种样式布局 -->
		<goodsCate1 v-show="category==1" ref="classOne" :isFooter="isFooter"></goodsCate1>
		<goodsCate2 v-show="category==2" ref="classTwo" :isFooter="isFooter"></goodsCate2>
		<goodsCate3 v-show="category==3" ref="classThree" :isFooter="isFooter"></goodsCate3>
		<pageFooter @newDataStatus="newDataStatus"></pageFooter>
	</view>
</template>

<script>
	import colors from "@/mixins/color";
	import goodsCate1 from './goods_cate1';
	import goodsCate2 from './goods_cate2';
	import goodsCate3 from './goods_cate3';
	import pageFooter from '@/components/pageFooter/index.vue'
	import {
		colorChange
	} from '@/api/api.js';
	import {
		mapGetters
	} from 'vuex';
	export default {
		computed: mapGetters(['isLogin', 'uid']),
		components: {
			goodsCate1,
			goodsCate2,
			goodsCate3,
			pageFooter
		},
		mixins: [colors],
		data() {
			return {
				category:'',
				isFooter:false
			}
		},
		onLoad(options) {
			if(!uni.getStorageSync('form_type_cart')){
				this.classStyle();
			}
		},
		onReady() {
		},
		onShow() {
			if(uni.getStorageSync('form_type_cart')){
				this.classStyle();
			}
		},
		methods: {
			newDataStatus(val){
				this.isFooter = val;
			},
			otherFun(object){
				if(!!object){
					if(this.category==2){
						this.$refs.classTwo.updateFun(object);
					}
					if(this.category==3){
						this.$refs.classThree.updateFun(object);
					}
				}
			},
			classStyle(){
       
				colorChange('category').then(res=>{
					let status = res.data.status;
					this.category = status;
					if(status==1){
						this.$refs.classOne.getAllCategory();
					}
					if(status==2){
						if(this.isLogin){
							this.$refs.classTwo.getCartList(1);
						}
						this.$refs.classTwo.getAllCategory();
						this.$refs.classTwo.getMarTop();
					}
					if(status==3){
						if(this.isLogin){
							this.$refs.classThree.getCartList(1);
						}
						this.$refs.classThree.getAllCategory();
						this.$refs.classThree.getMarTop();
					}
				})
			}
		},
		onReachBottom: function() {
			if(this.category==2){
				this.$refs.classTwo.productslist();
			}
			if(this.category==3){
				this.$refs.classThree.productslist();
			}
		}
	}
</script>
<style scoped lang="scss">
	/deep/.mask{
		z-index: 99;
	}
</style>
