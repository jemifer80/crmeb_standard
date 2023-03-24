import Schema from 'async-validator';
import { array } from 'js-md5';
export default {
    props:{
        options:{
            type: Array,
            default(){
                return [];
            },
        },
        //默认值
        value: {
            type: String | Number|Array,
            default:'',
        },
        //索引值
        index: {
            type: Number,
            default:0,
        },
        placeholder: {
            type: String,
            default:'',
        },
        //标题
        title: {
            type: String,
            default:''
        },
        //input类型
        type: {
            type: String,
            default:'text'
        },
        //input后缀
        suffix: {
            type: String,
            default:''
        },
        //input前缀
        prefix: {
            type: String,
            default:''
        },
        //样式
        styleModel: {
            type: String,
            default:''
        },
        //样式名称
        className: {
            type: String,
            default:''
        },
        //字段名称
        field: {
            type: String,
            default:'',
        },
        timerType:{
            type: String,
            default:'timerange',
        },
        timerFormat:{
            type: String,
            default:'HH:mm:ss',
        },
        //说明
        info: {
            type: String,
            default:'',
        },
        //事件
        on: {
            type: Object,
            default() {
                return {};
            }
        },
        validate: {
            type: Object,
            default() {
                return {};
            },
        },
        errorsValidate: {
            type: Array,
            default() {
                return [];
            }
        }
    },
    data() {
        return {
            valueModel: this.value,
            errorMessage:'',
            isExampleSize:0,
            exampleImage:{
                'site_logo':'/statics/system/adminSet01.png',
                'site_logo_square':'/statics/system/adminSet02.png',
                'login_logo':'/statics/system/adminSet03.png',
                'admin_login_slide':'/statics/system/adminSet04.png',
                'wap_login_logo':'/statics/system/mobileSet01.png',
                'wechat_share_img':'/statics/system/mobileSet02.png',
                'pc_logo':'/statics/system/pcSet01.png',
                'pay_weixin_client_cert':{
                  text: '更多详情请查看：<br><a href="https://kf.qq.com/faq/161222NneAJf161222U7fARv.html" target="_blank" rel="noopener noreferrer">https://kf.qq.com/faq/161222NneAJf161222U7fARv.html</a>',
                  image: '/statics/system/wxSet01.png'
                },
                // 'pay_weixin_client_cert':'/statics/system/wxSet01.png',
                // 'pay_weixin_client_key':'/statics/system/wxSet01.png',
                'pay_weixin_client_key':{
                  text: '更多详情请查看：<br><a href="https://kf.qq.com/faq/161222NneAJf161222U7fARv.html" target="_blank" rel="noopener noreferrer">https://kf.qq.com/faq/161222NneAJf161222U7fARv.html</a>',
                  image: '/statics/system/wxSet01.png'
                },
                'terminal_number':'/statics/system/yilianyunPrinter.png',
                'config_export_siid':'/statics/system/kuadi100Dump.png',
                'product_poster_title':'/statics/system/productSharePoster.png',
                'product_video_status':'/statics/system/productVideo.png'
            },
            exampleSize:{
                'site_logo':364,
                'site_logo_square':364,
                'login_logo':364,
                'admin_login_slide':364,
                'wap_login_logo':256,
                'wechat_share_img':256,
                'pc_logo':364,
                'terminal_number':364,
                'config_export_siid':364,
                'product_poster_title':256
            }
        };
    },
    watch: {
        errorsValidate: {
            handler(n) {
                if (n) {
                    let  error = n.find(item => item.field === this.field);
                    this.errorMessage = error ? error.message : '';
                } else {
                    this.errorMessage = '';
                }
            },
            deep:true
        }
    },
    // mounted(){
    //     let list = ['site_logo','site_logo_square','login_logo','admin_login_slide','wap_login_logo','wechat_share_img','pc_logo','pay_weixin_client_cert','pay_weixin_client_key']
    //     this.isExampleSize = list.indexOf(this.field);
    // },
    methods: {

        //获取class
        getClassName() {
          let value = ['input-build-' + this.field];
          if (this.errorMessage){
              value.push('ivu-form-item-error');
          }
          let filter = this.validate[this.field] ? this.validate[this.field].filter(item => item.required === true) : [];
          if (filter.length) {
              value.push('ivu-form-item-required')
          }
          return value;
        },
        //事件回调绑定
        changeEvent(name, item) {
            if ('change' === name) {
                this.$emit('changeValue',{field: this.field, value: this.valueModel});
            }
            this.on[name] && this.on[name](item);
            //验证数据
            this.validator(name);
        },
        //数据验证
        validator(name) {
            let filter = this.validate[this.field] ? this.validate[this.field].filter(item=> item.trigger === name) : [];
            if(!filter.length){
                return ;
            }
            const validator = new Schema(this.validate);
            let  source = {[this.field]: this.valueModel};
            validator.validate(source,(errors, fields) => {
              if (errors) {
                  let  error = errors.find(item => item.field === this.field)
                  this.errorMessage = error ? error.message : '';
              } else {
                  this.errorMessage = '';
              }
            })
        },
    }
}
