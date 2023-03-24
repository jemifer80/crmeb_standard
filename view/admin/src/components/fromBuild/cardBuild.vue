<template>
    <Card :style="styleModel" :bordered="false" dis-hover class="input-build-card">
        <p slot="title">
            <Icon v-if="icon" :type="icon"></Icon>
            {{title}}
        </p>
        <Button v-if="!index && ['app', 'wechat', 'work'].includes(type)" slot="extra" type="text" custom-icon="iconfont iconpeizhiyindao1" @click="showGuide">配置引导</Button>
        <use-component :validate="validate" :control="control" :errorsValidate="errorsValidate" @changeValue="changeValue" :rules="componentsModel"></use-component>
    </Card>
</template>

<script>
    export default {
        name: "cardBuild",
        components:{
            useComponent:() => import('./useComponent'),
        },
        inject: ['type'],
        props:{
            styleModel: {
                type: String,
                default:''
            },
            icon: {
                type: String,
                default:''
            },
            title: {
                type: String,
                default:''
            },
            componentsModel: {
                type:Array,
                default(){
                    return [];
                }
            },
            validate: {
                type: Object,
                default() {
                    return {};
                }
            },
            errorsValidate: {
                type: Array,
                default() {
                    return [];
                }
            },
            control: {
                type: Array,
                default() {
                    return [];
                }
            },
            index: {
              type: Number,
              default: 0
            }
        },
        date(){
            return {
                submitValue:{},
            };
        },
        mounted() {

        },
        methods:{
            changeValue(e){
                this.$emit('changeValue',{field:e.field,value:e.value});
            },
            showGuide() {
              this.bus.$emit('settingGuideShow');
            }
        },
    }
</script>

<style scoped>
    @import url('./css/build.css');
    /deep/.ivu-btn > i{
      vertical-align: -1px;
    }
    /deep/.ivu-card-extra{
      top:11px
    }
    /deep/.ivu-btn > .ivu-icon + span{
      margin-left: 0;
    }
    .ivu-btn-text {
      color: #2D8CF0;
      font-size: 13px !important;
    }
    .ivu-btn-text:focus {
      box-shadow: none;
    }
</style>
