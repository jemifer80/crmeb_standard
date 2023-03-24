<template>
    <div class="diy-table">
        <FormItem :label="title" class="input-build" :class="getClassName()">
            <Table :columns="columns" :data="valueModel" :width="750" class="diy_table">
                <template slot-scope="{ row, index }" :slot="item.key" v-for="item in options">
                    <template v-if="item.type === 'image'">
                        <div class="" @click="modalPicTap(item,index)">
                            <div class="pictrueTab" v-if="valueModel[index][item.key]">
                                <img v-lazy="valueModel[index][item.key]" />
                            </div>
                            <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                                <Icon type="ios-camera-outline" size="21"/>
                            </div>
                        </div>
                    </template>
                    <template v-else-if="item.type === 'input'">
                        <Input type="text" v-model="valueModel[index][item.key]" style="width:150px;" />
                    </template>
                    <template v-else-if="item.type === 'select'">
                        <Select v-model="valueModel[index][item.key]" >
                            <Option v-for="vv in item.props.options || []" :value="vv.value" :key="vv.value">{{ vv.label }}</Option>
                        </Select>
                    </template>
                    <template v-else-if="item.type === 'inputNumber'">
                        <InputNumber v-model="valueModel[index][item.key]" :editable="item.props.editable || false" :name="field" :min="0" style="width:150px;" />
                    </template>
                    <template v-else-if="item.type === 'switch'">
                        <Switch size="large" v-model="valueModel[index][item.key]" :true-value="1" :false-value="0">
                            <span slot="open">开启</span>
                            <span slot="close">关闭</span>
                        </Switch>
                    </template>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <span class="delete" @click="del(index)">删除</span>
                </template>
            </Table>
            <div class="diy-button">
                <Button type="primary" @click="add">添加</Button>
            </div>
        </FormItem>
        <Modal
                v-model="modalPic"
                width="960px"
                scrollable
                footer-hide
                closable
                title="上传商品图"
                :mask-closable="false"
                :z-index="1"
        >
            <uploadPictures
                    :isChoice="isChoice"
                    @getPic="getPic"
                    :gridBtn="gridBtn"
                    :gridPic="gridPic"
                    v-if="modalPic"
            ></uploadPictures>
        </Modal>
    </div>
</template>

<script>
    import uploadPictures from "@/components/uploadPictures";
    import build from "./build";
    export default {
        name: "diyTabelBuild",
        components: {
            uploadPictures
        },
        mixins: [build],
        watch: {
            valueModel: {
                handler(n) {
                    this.changeValue();
                },
                deep: true
            },
        },
        data(){
            return {
                gridPic: {
                    xl: 6,
                    lg: 8,
                    md: 12,
                    sm: 12,
                    xs: 12,
                },
                gridBtn: {
                    xl: 4,
                    lg: 8,
                    md: 8,
                    sm: 8,
                    xs: 8,
                },
                data: [],
                modalPic:false,
                isChoice:'单选',
                tableIndex:0,
                keyName:'icon'
            };
        },
        mounted() {

        },
        methods: {
            // 点击商品图
            modalPicTap(item,index) {
                this.modalPic = true;
                this.tableIndex = index;
                this.keyName = item.key;
            },
            // 获取单张图片信息
            getPic(pc) {
                this.valueModel[this.tableIndex][this.keyName] = pc.att_dir;
                this.modalPic = false;
            },
            add() {
                let value = {};
                this.options.map(item => {
                    if (item.key === 'sort') {
                        value[item.key] = 0;
                    } else {
                        value[item.key] = '';
                    }
                })
                this.valueModel.push(value);
            },
            del(index) {
                this.valueModel.splice(index,1);
            },
            changeValue() {
                this.$emit('changeValue', {field: this.field,value: this.valueModel});
                //触发change事件
                this.on['change'] && this.on['change'](this.valueModel);
            },
        },
        computed: {
            columns(){
                let columns = [];
                this.options.map(item => {
                    columns.push({title:item.name,slot:item.key,align:item.alert || 'left'});
                });
                columns.push({title:'操作', slot:'action', width:100, align:'left'});
                return columns;
            },
        }
    }
</script>

<style scoped lang="stylus">
    @import url('./css/build.css');
    .pictrueTab {
        width: 40px !important;
        height: 40px !important;
        cursor pointer
        img{
            width 100%;
            height 100%;
        }
    }
    .upLoad {
        width: 58px;
        height: 58px;
        line-height: 58px;
        border: 1px dotted rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        background: rgba(0, 0, 0, 0.02);
        cursor: pointer;
    }
    .diy-table .diy-button{
        margin-top: 10px;
    }
    /deep/.ivu-table-cell{
        padding 0!important;
    }
    .diy_table{
        box-sizing: border-box;
        position: relative;
        &:before{
            content:"";
            width:1px;
            height:100%;
            background: #eee;
            position: absolute;
            top:0;
            left:0;
        }
        &:after{
            content:"";
            width:1px;
            height:100%;
            background: #eee;
            position: absolute;
            top:0;
            right:0;
        }
    }
    .delete{
        color: #57a3f3;
        cursor pointer;
    }
</style>
