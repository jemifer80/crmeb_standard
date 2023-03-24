<template>
    <GlobalFooter class="i-copyright" :links="links" :copyright="copyright" />
</template>
<script>
    import { copyright } from '@/api/account';
    export default {
        name: 'i-copyright',
        data () {
            return {
                links: [
                    {
                        title: '官网',
                        key: '官网',
                        href: 'https://www.crmeb.com',
                        blankTarget: true
                    },
                    {
                        title: '社区',
                        key: '社区',
                        href: 'http://q.crmeb.com',
                        blankTarget: true
                    },
                    {
                        title: '文档',
                        key: '文档',
                        href: 'http://doc.crmeb.com',
                        blankTarget: true
                    }
                ],
                copyright: ''
            }
        },
        mounted () {
            this.getCopyright();
        },
        methods: {
            getCopyright () {
                copyright().then(res=>{
                    this.copyright += res.data.copyrightContext?res.data.copyrightContext:'Copyright © 2014-2022 ';
                    this.getVersion(res);
                }).catch(err=>{
                    this.$Message.error(err.msg)
                })
            },
            getVersion (res) {
                this.$store.dispatch('store/db/get', {
                    dbName: 'sys',
                    path: 'user.info',
                    user: true
                }).then(data => {
                    this.copyright += (data.version && !res.data.copyrightContext) ? data.version : '';
                })
            }
        }
    }
</script>
<style lang="less">
    .i-copyright{
        flex: 0 0 auto;
        z-index: 1;
    }
</style>
