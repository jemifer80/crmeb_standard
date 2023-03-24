<template>
    <!-- 同城配送设置 -->
    <from-submit :validate="ruleValidate" :url="url" :title="title" :rules="rules"/>
</template>

<script>
    import fromSubmit from '@/components/fromSubmit/fromSubmit.vue';
    import buildData from "../shop/buildData";

    export default {
        name: "setting",
        components:{fromSubmit},
        mixins:[buildData],
        data() {
            return {
                ruleValidate: {},
                rules: [],
                url: '',
                title:'配送设置',
                type: 'city_deliver'
            };
        },
    }
</script>

<style scoped>
/deep/.input-build-card{
  min-height: 500px;
}
</style>
