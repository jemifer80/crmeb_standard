<template>
    <div class="mobile-config">
        <div  v-for="(item,key) in rCom" :key="key">
            <component :is="item.components.name" :configObj="configObj" ref="childData" :configNme="item.configNme" :key="key" @getConfig="getConfig" :index="activeIndex" :num="item.num"></component>
        </div>
        <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </div>
</template>

<script>
    import toolCom from '@/components/mobileConfigRight/index.js'
    import rightBtn from '@/components/rightBtn/index.vue';
    import { mapState, mapMutations, mapActions } from 'vuex'
    import { newcomerList } from '@/api/diy'
    export default {
        name: 'c_new_vip',
        componentsName: 'home_new_vip',
        components: {
            ...toolCom,
            rightBtn
        },
        props: {
            activeIndex: {
                type: null
            },
            num: {
                type: null
            },
            index: {
                type: null
            }
        },
        data () {
            return {
                configObj: {},
                rCom: [
                    {
                        components: toolCom.c_set_up,
                        configNme: 'setUp'
                    }
                ]
            }
        },
        watch: {
            num (nVal) {
                this.configObj = this.$store.state.admin.mobildConfig.defaultArray[nVal]
            },
            configObj: {
                handler (nVal, oVal) {
                    this.$store.commit('admin/mobildConfig/UPDATEARR', { num: this.num, val: nVal });
                },
                deep: true
            },
            'configObj.setUp.tabVal': {
                handler (nVal, oVal) {
                    var arr = [this.rCom[0]]
                    if (nVal == 0) {
                        let tempArr = [
                            {
                                components: toolCom.c_txt_tab,
                                configNme: 'itemSort'
                            },
                            {
                                components: toolCom.c_input_number,
                                configNme: 'numConfig'
                            },
                            {
                                components: toolCom.c_checkbox,
                                configNme: 'checkboxInfo'
                            }
                        ];
                        this.rCom = arr.concat(tempArr)
                    } else {
                        let tempArr = [
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'bgColor'
                            },
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'textColor'
                            },
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'priceColor'
                            },
                            {
                                components: toolCom.c_txt_tab,
                                configNme: 'itemStyle'
                            },
                            {
                                components: toolCom.c_slider,
                                configNme: 'prConfig'
                            },
                            {
                                components: toolCom.c_slider,
                                configNme: 'mbCongfig'
                            }
                        ];
                        this.rCom = arr.concat(tempArr)
                    }
                },
                deep: true
            }
        },
        mounted () {
            this.$nextTick(() => {
                let value = JSON.parse(JSON.stringify(this.$store.state.admin.mobildConfig.defaultArray[this.num]))
                this.configObj = value;
            })
        },
        methods: {
            // 获取组件参数
            getConfig (data) {
                newcomerList({
                    page: 1,
                    limit: this.configObj.numConfig.val,
                    priceOrder: this.configObj.itemSort.type == 2 ? 'desc' : '',
                    salesOrder: this.configObj.itemSort.type == 1 ? 'desc' : ''
                }).then(res=>{
                    this.configObj.newVipList.list = res.data;
                }).catch(err=>{
                   return this.$message.error(err.msg);
                })
            },
            handleSubmit (name) {
                let obj = {}
                obj.activeIndex = this.activeIndex
                obj.data = this.configObj
                this.add(obj);
            },
            ...mapMutations({
                add: 'admin/mobildConfig/UPDATEARR'
            })
        }
    }
</script>

<style scoped>

</style>
