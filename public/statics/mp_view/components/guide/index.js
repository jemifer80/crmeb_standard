(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/guide/index"],{"0dd6":function(t,n,e){"use strict";e.r(n);var a=e("78c8"),i=e.n(a);for(var u in a)["default"].indexOf(u)<0&&function(t){e.d(n,t,(function(){return a[t]}))}(u);n["default"]=i.a},"78c8":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var e=getApp(),a={data:function(){return{autoplay:!1,duration:500,jumpover:"跳过",experience:"立即体验",time:this.advData.time,timecount:void 0,navH:0}},props:{advData:{type:Object,default:function(){}},closeType:{type:Number,default:1}},mounted:function(){this.timer(),this.navH=e.globalData.navHeight},methods:{stopChange:function(){if(1==this.advData.value.length)return!1},timer:function(){var t=this,n=this.advData.time||5;this.timecount=setInterval((function(){n--,t.time=n,n<=0&&(clearInterval(t.timecount),t.launchFlag())}),1e3)},launchFlag:function(){clearInterval(this.timecount),t.switchTab({url:"/pages/index/index"})},jump:function(t){t&&(clearInterval(this.timecount),this.$util.JumpPath(t))}}};n.default=a}).call(this,e("543d")["default"])},"7ff4":function(t,n,e){"use strict";e.r(n);var a=e("c6d4"),i=e("0dd6");for(var u in i)["default"].indexOf(u)<0&&function(t){e.d(n,t,(function(){return i[t]}))}(u);e("8eb0");var c=e("f0c5"),o=Object(c["a"])(i["default"],a["b"],a["c"],!1,null,"3479bd3b",null,!1,a["a"],void 0);n["default"]=o.exports},"83f8":function(t,n,e){},"8eb0":function(t,n,e){"use strict";var a=e("83f8"),i=e.n(a);i.a},c6d4:function(t,n,e){"use strict";e.d(n,"b",(function(){return a})),e.d(n,"c",(function(){return i})),e.d(n,"a",(function(){}));var a=function(){var t=this.$createElement,n=(this._self._c,"pic"==this.advData.type&&this.advData.value.length),e=n?this.advData.value.length:null;this.$mp.data=Object.assign({},{$root:{g0:n,g1:e}})},i=[]}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/guide/index-create-component',
    {
        'components/guide/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("7ff4"))
        })
    },
    [['components/guide/index-create-component']]
]);
