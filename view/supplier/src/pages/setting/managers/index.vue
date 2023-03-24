<template>
    <div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Button type="primary" @click="add">添加管理员</Button>
            <Table :columns="columns1" :data="list" class="mt25" no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果"  :loading="loading" highlight-row>
                <template slot-scope="{ row }" slot="head_pic">
                    <div class="pictrue" v-viewer>
                        <img v-lazy="row.head_pic">
                    </div>
                </template>
                <template slot-scope="{ row }" slot="status">
                    <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">
                        <span slot="open">开启</span>
                        <span slot="close">关闭</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="edit(row)">编辑</a>
                    <Divider type="vertical"/>
                    <a @click="del(row,'删除管理员', index)">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
                      :page-size="formValidate.limit"/>
            </div>
        </Card>
        <!-- 添加 编辑 -->
        <admin-from :FromData="FromData" ref="adminfrom" @submitFail="submitFail"></admin-from>
    </div>
</template>

<script>
    import { mapState } from 'vuex';
    import { adminListApi, adminFromApi, adminEditFromApi, setShowApi } from '@/api/setting';
    import adminFrom from '../../../components/from/from';
    export default {
        name: 'systemAdmin',
        components: { adminFrom },
        data () {
            return {
                total: 0,
                loading: false,
                formValidate: {
                    page: 1, // 当前页
                    limit: 20 // 每页显示条数
                },
                list: [],
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '头像',
                        slot: 'head_pic',
                        minWidth: 150
                    },
                    {
                        title: '名称',
                        key: 'real_name',
                        minWidth: 250
                    },
                    {
                        title: '账号',
                        key: 'account',
                        minWidth: 180
                    },
                    {
                        title: '状态',
                        slot: 'status',
                        minWidth: 90
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 120
                    }
                ],
                FromData: null
            }
        },
        computed: {
            ...mapState('admin/layout', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 96;
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right';
            }
        },
        created () {
            this.getList();
        },
        methods: {
            // 修改是否开启
            onchangeIsShow (row) {
                let data = {
                    id: row.id,
                    status: row.status
                }
                setShowApi(data).then(async res => {
                    this.$Message.success(res.msg);
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 请求列表
            submitFail () {
                this.getList();
            },
            // 列表
            getList () {
                this.loading = true;
                adminListApi(this.formValidate).then(async res => {
                    this.total = res.data.count;
                    this.list = res.data.list;
                    this.loading = false;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange (index) {
                this.formValidate.page = index
                this.getList();
            },
            // 添加表单
            add () {
                adminFromApi().then(async res => {
                    this.FromData = res.data;
                    this.$refs.adminfrom.modals = true;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 编辑
            edit (row) {
                adminEditFromApi(row.id).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data);
                    }
                    this.FromData = res.data;
                    this.$refs.adminfrom.modals = true;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `admin/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                };
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg);
                    this.list.splice(num, 1);
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            }
        }
    }
</script>

<style scoped lang="less">
    .pictrue{
        width: 40px;
        height: 40px;
        img{
            width: 100%;
            height: 100%;
        }
    }
</style>
