<template>
    <div class="page-account">
        <div class="container"></div>
        <div class="index_from page-account-container">
            <div class="page-account-top ">
              <img :src="login_logo" alt="logo">
            </div>
            <div class="title"><span class="line"></span><span class="inner">供应商管理</span><span class="line"></span></div>
            <Form ref="formInline" :model="formInline" :rules="ruleInline" @keyup.enter="handleSubmit('formInline')">
                <FormItem prop="username">
                    <Input type="text" v-model="formInline.username" prefix="ios-contact-outline" placeholder="请输入用户名"
                        size="default" />
                </FormItem>
                <FormItem prop="password">
                    <Input type="password" v-model="formInline.password" prefix="ios-lock-outline" placeholder="请输入密码"
                        size="default" />
                </FormItem>
                <FormItem>
                    <Button type="primary" long size="default" @click="handleSubmit('formInline')" class="btn">{{
                            $t('page.login.submit')
                    }}
                    </Button>
                </FormItem>
            </Form>
            <!--<div class="info" v-if="copyrightContext">{{copyrightContext}}</div>-->
            <!--<div class="info" v-else>Copyright ©2014-2022 <a class="infoUrl" href="https://www.crmeb.com" target="_blank">{{version}}</a></div>-->
        </div>
        <div class="footer">
            <div class="pull-right" v-if="copyrightContext">{{copyrightContext}}</div>
            <div class="pull-right" v-else>Copyright ©2014-2022 <a class="infoUrl" href="https://www.crmeb.com" target="_blank">{{version}}</a></div>
        </div>
        <Verify
            @success="closeModel"
            captchaType="blockPuzzle"
            :imgSize="{ width: '330px', height: '155px' }"
            ref="verify"
        ></Verify>
    </div>
</template>
<script>
import { AccountLogin, loginInfoApi, copyrightInfoApi, isCaptcha } from '@/api/account';
import mixins from '../mixins';
import Setting from '@/setting';
import util from '@/libs/util';
import Verify from "@/components/verifition/Verify";

export default {
    mixins: [mixins],
    components: {
        Verify
    },
    data() {
        return {
            autoLogin: true,
            formInline: {
                username: '',
                password: ''
            },
            ruleInline: {
                username: [
                    { required: true, message: '请输入用户名', trigger: 'blur' }
                ],
                password: [
                    { required: true, message: '请输入密码', trigger: 'blur' }
                ]
            },
            errorNum: 0,
            login_logo: '',
            site_name: '',
            site_url: '',
            copyrightContext:'',
            version:''
        }
    },
    created() {
        var _this = this;
        top != window && (top.location.href = location.href);
        document.onkeydown = function (e) {
            if (_this.$route.name === 'login') {
                let key = window.event.keyCode;
                if (key === 13) {
                    _this.handleSubmit('formInline');
                }
            }
        };
    },
    mounted: function () {
        this.$nextTick(() => {
            this.swiperData();
            this.copyrightInfo();
        });
    },
    methods: {
        swiperData() {
            loginInfoApi().then(res => {
                let data = res.data || {};
                this.login_logo = data.login_logo ? data.login_logo : require('@/assets/images/logo.png');
                this.site_name = data.site_name;
                this.site_url = data.site_url;
                localStorage.setItem('file_size_max',data.upload_file_size_max);
            }).catch(res => {
                this.$Message.error(res.msg)
            })
        },
        copyrightInfo(){
            copyrightInfoApi().then(res=>{
                this.copyrightContext = res.data.copyrightContext;
                this.version = res.data.version;
            }).catch(err=>{
                this.$Message.error(err.msg)
            })
        },
        // 关闭模态框
        closeModel(params) {
            let msg = this.$Message.loading({
                content: '登录中...',
                duration: 0
            });
            AccountLogin({
                account: this.formInline.username,
                pwd: this.formInline.password,
                captchaType: params ? 'blockPuzzle' : '',
                captchaVerification: params ? params.captchaVerification : ''
            }).then(async res => {
                this.$store.dispatch('store/account/setPageTitle')
                msg();
                let expires = res.data.expires_time;
                // 记录用户登陆信息
                util.cookies.set('uuid', res.data.user_info.id, {
                    expires: expires
                });
                util.cookies.set('token', res.data.token, {
                    expires: expires
                });
                util.cookies.set('expires_time', res.data.expires_time, {
                    expires: expires
                });
                const db = await this.$store.dispatch('store/db/database', {
                    user: true
                });
                // 保存菜单信息
                // db.set('menus', res.data.menus).set('unique_auth', res.data.unique_auth).set('user_info', res.data.user_info).write();
                db.set('unique_auth', res.data.unique_auth).set('user_info', res.data.user_info).write();

                this.$store.commit('store/menus/getmenusNav', res.data.menus);

                let userInfoSupplier = {
                    'account': res.data.user_info.account,
                    'avatar': res.data.user_info.avatar,
                    'logo': res.data.logo,
                    'logoSmall': res.data.logo_square
                }
                let storage = window.localStorage;
                storage.setItem('userInfoSupplier', JSON.stringify(userInfoSupplier));

                // 记录用户信息
                this.$store.dispatch('store/user/set', {
                    name: res.data.user_info.account,
                    avatar: res.data.user_info.avatar,
                    access: res.data.unique_auth,
                    logo: res.data.logo,
                    logoSmall: res.data.logo_square,
                    version: res.data.version,
                    newOrderAudioLink: res.data.newOrderAudioLink
                });
                return this.$router.replace({ path: this.$route.query.redirect || '/supplier/' });
            }).catch(res => {
                msg();
                let data = res === undefined ? {} : res;
                this.errorNum++;
                this.$Message.error(data.msg || '登录失败');
            });
        },
        getExpiresTime(expiresTime) {
            let nowTimeNum = Math.round(new Date() / 1000);
            let expiresTimeNum = expiresTime - nowTimeNum;
            return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
        },
        closefail() {
            this.$Message.error('校验错误');
        },
        handleSubmit(name) {
            this.$refs[name].validate((valid) => {
                if (valid) {
                    isCaptcha({
                      account: this.formInline.username
                    }).then(res => {
                      if (res.data.is_captcha) {
                        this.$refs.verify.show();
                      } else {
                        this.closeModel();
                      }
                    });
                }
            })
        }
    }
};
</script>
<style scoped lang="stylus">
    .pull-right {
        float: right!important;
        .infoUrl{
            margin 0;
            color #515a6e !important;
            &:hover{
                color #1890ff!important;
            }
        }
    }
    .footer{
        position: fixed;
        bottom: 0;
        width: 100%;
        left: 0;
        margin: 0;
        //background: rgba(255,255,255,.8);
        overflow: hidden;
        padding: 10px 20px;
        height: 36px;
    }
    .page-account {
        .container{
            width 100%;
            height 350px;
            background-image: url('../../../assets/images/bg.png');
            background-repeat no-repeat;
            background-position: center;
            background-size: cover;
        }
    }

    .page-account .code {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-account .code .pictrue {
        height: 32px;
    }

    .swiperPic img {
        width: 100%;
        height: 100%;
    }

    .index_from {
        padding: 0 40px 32px 40px;
        height: 370px;
        box-sizing: border-box;
    }

    .page-account-container {
        box-shadow: 0 2px 9px 6px rgba(0, 0, 0, 0.03);
        border-radius: 5px;
        margin-top -210px;
        background-color #fff;
        .title{
            font-size: 18px;
            font-weight 500;
            color rgba(0, 0, 0, 0.85);
            padding-top 25px;
            margin-bottom 25px;
            line-height: 25px
        }
        .info{
            color: #CCCCCC;
            font-size 12px;
            margin-top 53px;
            .infoUrl{
                margin 0;
                color #ccc !important;
            }
        }

        .inner{
          padding: 0 15px
          vertical-align: middle
        }
        .line{
          display: inline-block
          width: 43px
          height: 1px
          background-color: #CCCCCC
          vertical-align: middle
        }

        .page-account-top{
          padding-top: 20px
          padding-bottom: 0
            img{
              display: block
              width: 300px
              height 75px;
              margin: 0 auto
              object-fit: contain
            }
        }

        >>>.ivu-input{
          height: 40px
        }

        >>>.ivu-input-prefix i{
          line-height: 40px
        }
    }

    .btn {
      height: 40px
        background: #1890FF !important;
    }

    .captchaBox {
        width: 310px;
    }

    input {
        display: block;
        width: 290px;
        line-height: 40px;
        margin: 10px 0;
        padding: 0 10px;
        outline: none;
        border: 1px solid #c8cccf;
        border-radius: 4px;
        color: #6a6f77;
    }

    #msg {
        width: 100%;
        line-height: 40px;
        font-size: 14px;
        text-align: center;
    }

    a:link, a:visited, a:hover, a:active {
        margin-left: 100px;
        color: #0366D6;
    }

    .index_from >>> .ivu-input-large
        font-size:14px!important

</style>
