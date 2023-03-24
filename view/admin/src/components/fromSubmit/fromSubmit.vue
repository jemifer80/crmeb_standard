<template>
<!-- 用户-等级设置-会员设置-用户设置-客服设置 -->
    <div class="form-submit">
        <!--创建表单-->
        <Form :ref="'formSubmit'+field" :model="submitValue" :label-width="124">
            <use-component @changeValue="changeValue" :errorsValidate="errorsValidate" :validate="validate" :rules="rules">
            </use-component>
            <!--自定义内容-->
            <slot name="content" class="content"></slot>
            <div style="height: 2px;"></div>
            <Card v-if="buttonHide" :bordered="false" dis-hover class="fixed-card" :style="{left: `${!menuCollapse?'200px':isMobile?'0':'80px'}`}">

                <FormItem>
                    <Button type="primary" class="btn-add" :disabled="disabled" :loading="loading" @click="submit">{{butName}}</Button>
                </FormItem>
            </Card>
            <!--button-->
            <slot name="button" class="slot-button"></slot>

        </Form>
        <Drawer v-model="guideShow" :title="`${title}引导`" width="800">
          <component :is="types"></component>
        </Drawer>
    </div>
</template>

<script>
    import guide from '../settingGuide/index';
    import Schema from 'async-validator';
    import {Message} from "iview";
    import request from '@/plugins/request';
    import { mapMutations } from "vuex";

    export default {
        name: "fromSubmit",
        mixins: [guide],
        components:{
            useComponent:() => import('../fromBuild/useComponent'),
        },
        provide() {
          return {
            type: this.types
          }
        },
        props:{
            rules:{
                type:Array,
                default(){
                    return [];
                }
            },
            validate:{
                type:Object,
                default(){
                    return {};
                }
            },
            butName:{
                type:String,
                default:'提交',
            },
            field:{
                type:String,
                default(){
                    return ''+Math.random();
                },
            },
            url:{
                type:String,
                default:"",
            },
            method:{
                type:String,
                default:"POST",
            },
            buttonHide:{
                type: Boolean,
                default: true,
            },
            on:{
                type:Object,
                default(){
                    return {};
                },
            },
        },
        data() {
            return {
                submitValue: {},
                disabled: false,
                loading: false,
                errorsValidate: [],
                title: this.$parent.title,
                types: this.$parent.typeMole || this.$parent.type,
                guideShow: false,
            };
        },
        watch: {
            rules: {
                handler() {
                    this.submitValue = this.getRuleValue(this.rules);
                },
                deep: true,
            },
        },
        mounted() {
            this.submitValue = this.getRuleValue(this.rules);
            this.setCopyrightShow({ value: false });
            this.bus.$on('settingGuideShow', () => {
              this.guideShow = true;
            });
            this.$once('hook:beforeDestroy', () => {
              this.setCopyrightShow({ value: true });
              this.bus.$off('settingGuideShow');
            });
        },
        methods:{
            ...mapMutations('admin/layout', [
                'setCopyrightShow',
								"isMobile",
								"menuCollapse"
            ]),
            //组件值变动事件
            changeValue(e) {
                this.submitValue[e.field] = e.value;
                this.rules = this.setRuleValue(this.rules,e.field, e.value);
            },
            //设置组件值
            setRuleValue(rules, field, vvvv) {
                rules.map(item =>{
                    if (item.field !== undefined && item.field === field) {
                        item.value = vvvv;
                    }
                    if (typeof item.options === 'object') {
                        item.options.map(option => {
                            if (option.componentsModel !== undefined) {
                                option.componentsModel = this.setRuleValue(option.componentsModel, field, vvvv);
                            }
                        });
                    }
                    if (typeof item.control === 'object') {
                        item.control.map(value => {
                            if (value.componentsModel !== undefined) {
                                value.componentsModel = this.setRuleValue(value.componentsModel, field, vvvv);
                            }
                        });
                    }
                    if (typeof item.componentsModel === "object") {
                        item.componentsModel = this.setRuleValue(item.componentsModel, field, vvvv);
                    }
                });
                return rules;
            },
            //获取默认值
            getRuleValue(rules) {
                let submitValue = {};
                rules.map(item=>{
                    if (item.field !== undefined) {
                        submitValue[item.field] = item.value
                    }
                    if (typeof item.options === 'object') {
                        item.options.map(option => {
                            if (option.componentsModel !== undefined) {
                                let values = this.getRuleValue(option.componentsModel);
                                Object.assign(submitValue, values);
                            }
                        });
                    }
                    if (typeof item.control === 'object') {
                        item.control.map(value => {
                            if (value.componentsModel !== undefined) {
                                let values = this.getRuleValue(value.componentsModel);
                                Object.assign(submitValue, values);
                            }
                        });
                    }
                    if (typeof item.componentsModel === "object") {
                        let values = this.getRuleValue(item.componentsModel);
                        Object.assign(submitValue, values);
                    }
                })
                return submitValue;
            },
            //表单提交
            submit(){
                let  validator = new Schema(this.validate);
                validator.validate(this.submitValue,(error) => {
                    if (error === undefined || error === null) {
                        this.errorsValidate = [];
                        this.disabled = true;
                        this.loading = true;

                        if (this.on['save']) {
                            try {
                                this.on['save'](this.submitValue, ()=> this.disabled = false, ()=> this.loading = false)
                            } catch (e) {
                                Message.error(err || '提交失败')
                            }
                        } else {
                            request[this.method.toLowerCase()](this.url, this.submitValue).then((res) => {
                                Message.success(res.msg || '提交成功')
                                this.on['submit'] && this.on['submit'](res)
                            }).catch(err => {
                                Message.error(err.msg || '提交失败')
                            }).finally(() => {
                                this.disabled = false;
                                this.loading = false;
                            })
                        }
                    } else {
                        this.errorsValidate = error;
                        Message.error(error[0].message);
                    }
                });

            },
        }
    }
</script>

<style lang="less" scoped>
.ivu-tabs{
    padding: 80px !important;
}
.btn-add {
 height: 32px !important;
}
.form-submit {
	  /deep/.ivu-card{
			border-radius: 0;
		}
    margin-bottom: 79px;

    .fixed-card {
        position: fixed;
        right: 0;
        bottom: 0;
        left: 200px;
        z-index: 10;
        box-shadow: 0 -1px 2px rgb(240, 240, 240);

        /deep/ .ivu-card-body {
            padding: 15px 16px 14px;
        }

        .ivu-form-item {
            margin-bottom: 0;
        }

        /deep/ .ivu-form-item-content {
            margin-right: 124px;
            text-align: center;
        }

        .ivu-btn {
            height: 36px;
            padding: 0 20px;
        }
    }
}
/deep/.ivu-drawer-wrap {
  .ivu-tabs-bar {
    margin-bottom: 30px;
  }

  .ivu-tabs-nav {
    display: flex;
    width: 100%;
  }

  .ivu-tabs-ink-bar {
    display: none;
  }

  .ivu-tabs-tab {
    flex: 1;
    padding: 10px 16px 10px 32px;
    margin-right: 0;
    background-color: #F5F5F5;
    text-align: center;
    font-size: 15px;
    line-height: 21px;
    color: #666666;
    transition: none;

    &::before {
      content: '';
      position: absolute;
      top: 6px;
      left: -14px;
      width: 29px;
      height: 29px;
      border: inherit;
      border-left-color: transparent;
      border-bottom-color: transparent;
      background-color: #FFFFFF;
      transform: rotate(45deg);
    }

    &::after {
      content: '';
      position: absolute;
      top: 6px;
      right: -14px;
      z-index: 3;
      width: 29px;
      height: 29px;
      border: inherit;
      border-left-color: transparent;
      border-bottom-color: transparent;
      background: inherit;
      transform: rotate(45deg);
    }

    &:hover {
      color: #666666 !important;
    }
  }

  .ivu-tabs-tab-active {
    background-color: #2D8BEF;
    color: #FFFFFF;

    &:hover {
      color: #FFFFFF !important;
    }
  }

  .ivu-timeline {
    .ivu-timeline-item-tail {
      left: 10px;
    }

    .ivu-timeline-item-head-custom {
      left: 0;
      padding: 0;
      margin-top: 0;
    }

    .ivu-timeline-item-content {
      top: -10px;
      padding: 0 0 10px 30px;
    }

    .dot {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background-color: #2D8CF0;
      line-height: 20px;
      color: #FFFFFF;
    }

    .title {
      margin-bottom: 10px;
      font-size: 16px;
      line-height: 20px;
      color: #333333;
    }

    .item + .item {
      margin-top: 20px;
    }

    .text {
      font-size: 12px;
      line-height: 17px;
      color: #666666;
    }

    .image {
      margin-top: 8px;
    }

    img {
      display: block;
      width: 320px;
      height: 160px;

      + img {
        margin-top: 8px;
      }
    }

    .ivu-alert {
      margin-top: 8px;
      color: #666666;

      div span {
        color: #2D8CF0;
      }
    }
  }
}
</style>
