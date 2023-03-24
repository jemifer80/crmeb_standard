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
    import { videoList } from '@/api/marketing'
    import { mapState, mapMutations, mapActions } from 'vuex'
    export default {
        name: 'c_short_video',
        componentsName: 'home_short_video',
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
                                components: toolCom.c_input_number,
                                configNme: 'numConfig'
                            }
                        ];
                        this.rCom = arr.concat(tempArr)
                    } else {
                        let tempArr = [
                            {
                                components: toolCom.c_txt_tab,
                                configNme: 'itemStyle'
                            },
							{
							    components: toolCom.c_bg_color,
							    configNme: 'titleColor'
							},
							{
							    components: toolCom.c_bg_color,
							    configNme: 'infoColor'
							},
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'bgColor'
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
            getVideoList(limit){
                videoList({
                    page:1,
                    limit:limit
                }).then(res=>{
                    this.configObj.videoList = res.data.list;
                }).catch(err=>{
                    this.$message.error(err.msg)
                })
            },
            // 获取组件参数
            getConfig (data) {
                if( data.name=='radio'){
                    return;
                }
                this.getVideoList(data.numVal);
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
