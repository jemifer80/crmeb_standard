<template>
    <div>
        <Card :bordered="false" dis-hover class="ivu-mt pt10">
            <template>
                <Form ref="formValidate" :model="formValidate" :label-width="120">
                    <FormItem label="小票打印:" prop="status">
                        <Switch v-model="formValidate.status" :true-value="1" :false-value="0" size="large">
                            <span slot="open">开启</span>
                            <span slot="close">关闭</span>
                        </Switch>
                        <div class="tips">支付成功自动小票打印功能，需要购买易联云K4无线打印机</div>
                    </FormItem>
                    <FormItem label="用户ID:" prop="develop_id">
                        <Input v-model="formValidate.develop_id" placeholder="请输入用户ID" class="input"></Input>
                        <div class="tips">易联云开发者ID</div>
                    </FormItem>
                    <FormItem label="应用密钥:" prop="api_key">
                        <Input v-model="formValidate.api_key" placeholder="请输入应用密钥" class="input"></Input>
                        <div class="tips">易联应用密钥</div>
                    </FormItem>
                    <FormItem label="应用ID:" prop="client_id">
                        <Input v-model="formValidate.client_id" placeholder="请输入应用ID" class="input"></Input>
                        <div class="tips">易联应用ID</div>
                    </FormItem>
                    <FormItem label="终端号:" prop="terminal_number">
                        <Input v-model="formValidate.terminal_number" placeholder="请输入终端号" class="input"></Input>
                        <div class="tips">易联云打印机终端号</div>
                    </FormItem>
                </Form>
            </template>
        </Card>
        <Card :bordered="false" dis-hover class="fixed-card">
            <Form>
                <FormItem>
                    <Button type="primary" @click="handleSubmit">保存</Button>
                </FormItem>
            </Form>
        </Card>
    </div>
</template>

<script>
    import { printing, putPrinting } from "@/api/setting";
    import { mapMutations } from "vuex";
    export default {
        name: 'index',
        components: {},
        data() {
            return {
                formValidate: {
                    id:0,
                    status:0,
                    develop_id: 0,
                    api_key: '',
                    client_id: '',
                    terminal_number: ''
                }
            }
        },
        created() {
            this.getInfo();
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
            getInfo(){
                printing().then(res=>{
                    this.formValidate = res.data
                }).catch(err=>{
                    this.$Message.error(err.msg)
                })
            },
            handleSubmit () {
                putPrinting(this.formValidate).then(res=>{
                    this.$Message.success(res.msg)
                }).catch(err=>{
                    this.$Message.success(err.msg)
                })
            }
        }
    }
</script>

<style lang="less" scoped>
    .ivu-form-item{
        margin-bottom: 15px;
        .tips{
            font-size: 12px;
            color: #999999;
            font-weight: 400;
        }
    }
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
