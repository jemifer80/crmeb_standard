(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/columnGoods/HotNewGoods/feedback"],{"0bd3":function(t,e,n){"use strict";var i=n("973e"),c=n.n(i);c.a},"428e":function(t,e,n){"use strict";(function(t,e){var i=n("4ea4");n("8824");i(n("66fd"));var c=i(n("c504"));t.__webpack_require_UNI_MP_PLUGIN__=n,e(c.default)}).call(this,n("bc2e")["default"],n("543d")["createPage"])},"973e":function(t,e,n){},af8b:function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return c})),n.d(e,"a",(function(){}));var i=function(){var t=this.$createElement;this._self._c},c=[]},bf82:function(t,e,n){"use strict";n.r(e);var i=n("e1d6"),c=n.n(i);for(var o in i)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(o);e["default"]=c.a},c504:function(t,e,n){"use strict";n.r(e);var i=n("af8b"),c=n("bf82");for(var o in c)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return c[t]}))}(o);n("0bd3");var a=n("f0c5"),u=Object(a["a"])(c["default"],i["b"],i["c"],!1,null,null,null,!1,i["a"],void 0);e["default"]=u.exports},e1d6:function(t,e,n){"use strict";(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=n("9ac9"),c={name:"feedback",data:function(){return{name:"",phone:"",con:"",feedback:""}},onLoad:function(){this.getInfo()},onShow:function(){t.removeStorageSync("form_type_cart")},methods:{gotoPre:function(){t.navigateBack()},getInfo:function(){var t=this;(0,i.serviceFeedBack)().then((function(e){t.feedback=e.data.feedback}))},subMit:function(){var t=this;return this.name?this.phone&&/^1(3|4|5|7|8|9|6)\d{9}$/i.test(this.phone)?this.con?void(0,i.feedBackPost)({rela_name:this.name,phone:this.phone,content:this.con}).then((function(e){t.$util.Tips({title:e.msg,icon:"success"},{tab:3})})).catch((function(t){that.$util.Tips({title:t})})):this.$util.Tips({title:"请填写内容"}):this.$util.Tips({title:"请填写正确的手机号码"}):this.$util.Tips({title:"请填写姓名"})}}};e.default=c}).call(this,n("543d")["default"])}},[["428e","common/runtime","common/vendor"]]]);