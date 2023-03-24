<template>
<!-- 设置-APP -->
    <from-submit :validate="ruleValidate" :url="url" :title="title" :rules="rules"/>
</template>

<script>
    import fromSubmit from '@/components/fromSubmit/fromSubmit.vue';
    import buildData from "@/pages/setting/shop/buildData";

    export default {
        name: "index",
        components:{fromSubmit},
        mixins:[buildData],
        data() {
            return {
                ruleValidate: {},
                rules: [],
                url: '',
                title:'APP配置',
                type: 'app'
            };
        },
    }
</script>

<style scoped lang="less">
/deep/.input-build-card{
  min-height: 600px;
}
</style>
