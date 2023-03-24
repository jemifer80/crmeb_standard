<template>
    <div>
        <Card :bordered="false" dis-hover class="ivu-mt pt10">
            <template>
                <Form ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="120">
                    <FormItem label="供应商名称:" prop="supplier_name">
                        <Input v-model="formValidate.supplier_name " placeholder="请输入供应商名称" class="input"></Input>
                    </FormItem>
                    <FormItem label="联系人姓名:" prop="name">
                        <Input v-model="formValidate.name" placeholder="请输入联系人姓名" class="input"></Input>
                    </FormItem>
                    <FormItem label="联系电话:" prop="phone">
                        <Input type="text" v-model="formValidate.phone" maxlength="11" placeholder="请输入联系电话" class="input"></Input>
                    </FormItem>
                    <FormItem label="供应商邮箱" prop="email">
                        <Input v-model="formValidate.email" placeholder="请输入供应商邮箱" class="input"></Input>
                    </FormItem>
                    <FormItem label="供应商地址：" label-for="address" prop="address">
                        <Cascader :data="addresData" :load-data="loadData" v-model="formValidate.addressSelect" @on-change="addchack" class="input"></Cascader>
                    </FormItem>
                    <FormItem label="供应商详细地址:" prop="detailed_address">
                        <Input v-model="formValidate.detailed_address" placeholder="请输入供应商详细地址" class="input"></Input>
                    </FormItem>
                </Form>
            </template>
        </Card>
        <Card :bordered="false" dis-hover class="fixed-card">
            <Form>
                <FormItem>
                    <Button type="primary" @click="handleSubmit('formValidate')">保存</Button>
                </FormItem>
            </Form>
        </Card>
    </div>
</template>

<script>
    import { supplier, putSupplier, cityApi } from "@/api/setting";
    import { mapMutations } from "vuex";
    export default {
        name: 'index',
        components: {},
        data() {
            const validatePhone = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入联系电话'));
                } else {
                    if (this.value !== '') {
                        if(!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(value)){
                            callback(new Error('请输入正确的联系电话'));
                        }else {
                            callback();
                        }
                    }
                }
            };
            return {
                formValidate: {
                    supplier_name: '',
                    name: '',
                    email: '',
                    phone: '',
                    detailed_address: '',
                    province:0,
                    city:0,
                    area:0,
                    street:0,
                    addressSelect:[],
                    address:''
                },
                addresData: [],
                ruleValidate: {
                    supplier_name: [
                        { required: true, message: '请输入供应商名称', trigger: 'blur' }
                    ],
                    phone: [
                        { required: true, message: '请输入联系电话', trigger: 'blur' },
                        { validator: validatePhone, trigger: 'blur' }
                    ]
                }
            }
        },
        created() {
            this.getInfo();
            let data = {pid:0}
            this.cityInfo(data);
        },
        mounted() {
            this.setCopyrightShow({ value: false });
        },
        destroyed () {
            this.setCopyrightShow({ value: true });
        },
        methods: {
            ...mapMutations('store/layout', [
                'setCopyrightShow'
            ]),
            loadData (item, callback) {
                item.loading = true;
                cityApi({pid:item.value}).then(res=>{
                    item.children = res.data;
                    item.loading = false;
                    callback();
                });
            },
            addchack(e,selectedData){
                e.forEach((i,index)=>{
                    if(index==0){
                        this.formValidate.province = i
                    }else if(index==1){
                        this.formValidate.city = i
                    }else if(index==2){
                        this.formValidate.area = i
                    }else {
                        this.formValidate.street = i
                    }
                })
                this.formValidate.address = (selectedData.map(o => o.label)).join("/");
            },
            cityInfo(data){
                cityApi(data).then(res=>{
                    this.addresData = res.data
                })
            },
            getInfo(){
                supplier().then(res=>{
                    this.formValidate = res.data;
                    let addressSelect = [];
                    if(res.data.province){
                        addressSelect.push(res.data.province)
                    }
                    if(res.data.city){
                        addressSelect.push(res.data.city)
                    }
                    if(res.data.area){
                        addressSelect.push(res.data.area)
                    }
                    if(res.data.street){
                        addressSelect.push(res.data.street)
                    }
                    this.formValidate.addressSelect = addressSelect;
                }).catch(err=>{
                    this.$Message.error(err.msg);
                })
            },
            handleSubmit (name) {
                if(this.formValidate.email !== ''){
                    if(!/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/i.test(this.formValidate.email)){
                        return this.$Message.error('请输入正确的供应商邮箱')
                    }
                }
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        putSupplier(this.formValidate).then(res=>{
                            this.$Message.success(res.msg)
                        }).catch(err=>{
                            this.$Message.error(err.msg)
                        })
                    }
                })
            }
        }
    }
</script>

<style lang="less" scoped>
    .input{
        max-width: 460px;
    }
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
</style>
