require('../../common/vendor.js');(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/goods/lottery/components/noticeBar"],{"2e3c":function(t,n,e){"use strict";e.d(n,"b",(function(){return a})),e.d(n,"c",(function(){return i})),e.d(n,"a",(function(){}));var a=function(){var t=this.$createElement;this._self._c},i=[]},"4e0d":function(t,n,e){"use strict";e.r(n);var a=e("2e3c"),i=e("730f");for(var r in i)["default"].indexOf(r)<0&&function(t){e.d(n,t,(function(){return i[t]}))}(r);e("9a7e");var o=e("f0c5"),s=Object(o["a"])(i["default"],a["b"],a["c"],!1,null,"5a3d3a78",null,!1,a["a"],void 0);n["default"]=s.exports},"6d8a":function(t,n,e){},"730f":function(t,n,e){"use strict";e.r(n);var a=e("96f2"),i=e.n(a);for(var r in a)["default"].indexOf(r)<0&&function(t){e.d(n,t,(function(){return a[t]}))}(r);n["default"]=i.a},"96f2":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var a={name:"noticeBar",data:function(){return{animateUp:!1,listData:JSON.parse(JSON.stringify(this.showMsg)),timer:null}},props:{showMsg:{type:Array}},mounted:function(){this.timer=setInterval(this.scrollAnimate,2500)},methods:{scrollAnimate:function(){var t=this;this.animateUp=!0,setTimeout((function(){t.listData.push(t.listData[0]),t.listData.shift(),t.animateUp=!1}),500)}},destroyed:function(){clearInterval(this.timer)}};n.default=a},"9a7e":function(t,n,e){"use strict";var a=e("6d8a"),i=e.n(a);i.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/goods/lottery/components/noticeBar-create-component',
    {
        'pages/goods/lottery/components/noticeBar-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("4e0d"))
        })
    },
    [['pages/goods/lottery/components/noticeBar-create-component']]
]);
