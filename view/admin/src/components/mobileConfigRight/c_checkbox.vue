<template>
    <div class="acea-row row-top" style="margin-bottom: 20px">
        <div class="title-tips" v-if="configData">
            <span>{{configData.title}}</span>
        </div>
        <div class="checkbox-box">
            <CheckboxGroup v-model="configData.type" @on-change="checkboxChange($event)">
                <Checkbox :label="item.id" v-for="(item,index) in configData.list" :key="index">
                    <span>{{item.name}}</span>
                </Checkbox>
            </CheckboxGroup>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'c_checkbox',
        props: {
            configObj: {
                type: Object
            },
            configNme: {
                type: String
            }
        },
        data () {
            return {
                formData: {
                    type: 0
                },
                defaults: {},
                configData: {}
            }
        },
        watch: {
            configObj: {
                handler (nVal, oVal) {
                    this.defaults = nVal
                    this.configData = nVal[this.configNme]
                },
                deep: true
            }
        },
        mounted () {
            this.$nextTick(() => {
                this.defaults = this.configObj;
                this.configData = this.configObj[this.configNme];
            })
        },
        methods: {
            checkboxChange (e) {
                this.$emit('getConfig', e);
            }
        }
    }
</script>

<style scoped lang="less">
    .title-tips{
        margin-right: 14px;
    }
    .checkbox-box{
        width: 280px;
    }
    .ivu-checkbox-group-item{
        margin-bottom: 15px;
        margin-right: 15px;
    }
    /deep/.ivu-checkbox{
        margin-right: 7px;
    }
</style>
