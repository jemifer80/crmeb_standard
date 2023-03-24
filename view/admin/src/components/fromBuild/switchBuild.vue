<template>
    <div>
        <FormItem :label="title" class="input-build" :class="getClassName()">
            <Switch size="large" v-model="valueModel" :true-value="getSwitch(true)" :false-value="getSwitch(false)" @on-change="changeEvent('change',$event)" >
                <span slot="open">{{getSwitch(true,true)}}</span>
                <span slot="close">{{getSwitch(false,true)}}</span>
            </Switch>
            
            <!-- 说明 -->
            <div v-if="info" class="info-wrapper">{{ info }}
              <Poptip placement="bottom" trigger="hover" :width="exampleSize[field]" :transfer="true" v-if="exampleImage[field]">
                  <a>查看示例</a>
                  <div class="exampleImg" :class="exampleSize[field] == 364?'on':''" slot="content">
                      <img
                              :src="baseURL+ exampleImage[field]"
                              alt=""
                      />
                  </div>
              </Poptip>
            </div>
        </FormItem>
        <template v-for="item in control">
            <template v-if="item.value === valueModel">
                <use-component :validate="validate" :errorsValidate="errorsValidate" @changeValue="changeValue" :rules="item.componentsModel"></use-component>
            </template>
        </template>
    </div>
</template>

<script>
    import build from "./build";
    import components from "./index";
    import Setting from '@/setting';

    export default {
        name: "switchBuild",
        mixins: [build, components],
        components:{
            useComponent:() => import('./useComponent'),
        },
        props: {
            control: {
                type: Array,
                default() {
                    return [];
                }
            }
        },
        data() {
          return {
            baseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
          };
        },
        created() {
            this.valueModel = this.valueModel === null ? 0 : this.valueModel;
        },
        methods:{
            changeValue(e){
                this.$emit('changeValue',{field:e.field,value:e.value});
            },
            getSwitch(e,name){
                let value = null;
                if (!this.options.length) {
                    return e ? (name ? '开启' : 1) : (name ? '关闭': 0);
                }
                this.options.map(item => {
                    if(e && item.trueValue !== undefined){
                        value = name ? item.label : item.trueValue;
                    } else if(!e && item.falseValue !== undefined){
                        value = name ? item.label : item.falseValue;
                    }
                });
                return value;
            },
        },
    }
</script>

<style scoped>
    @import url('./css/build.css');
    .exampleImg img {
      width: 204px;
      vertical-align: middle;
    }
</style>
