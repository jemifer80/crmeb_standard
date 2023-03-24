<template>
<!-- 发票设置 -->
    <from-submit :validate="ruleValidate" :url="url" :title="title" :rules="rules"/>
</template>

<script>
    import fromSubmit from '@/components/fromSubmit/fromSubmit.vue';
    import buildData from "@/pages/setting/shop/buildData";

    export default {
        name: "commonForm",
        components:{fromSubmit},
        mixins:[buildData],
        data() {
            return {
                ruleValidate: {},
                rules: [],
                url: '',
                title:'',
                type: ''
            };
        },
        props:{
            typeMole: {
                type: String,
                default:''
            },
        },
        watch: {
            typeMole(value){
                this.rules = [];
                this.getNewFormBuildRule();
            },
        }
    }
</script>

<style scoped lang="less">
/deep/.input-build-card{
  min-height: 600px;
}
</style>
