(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/goodList/index"],{"161d":function(t,n,e){},"293f":function(t,n,e){"use strict";e.d(n,"b",(function(){return u})),e.d(n,"c",(function(){return i})),e.d(n,"a",(function(){}));var u=function(){var t=this.$createElement;this._self._c},i=[]},2974:function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var u=e("26cb"),i=e("7a16"),a={computed:(0,u.mapGetters)(["uid"]),props:{status:{type:Number,default:0},bastList:{type:Array,default:function(){return[]}}},data:function(){return{}},methods:{goDetail:function(n){var e=this;(0,i.goPage)().then((function(u){(0,i.goShopDetail)(n,e.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(n.id)})}))}))}}};n.default=a}).call(this,e("543d")["default"])},5081:function(t,n,e){"use strict";e.r(n);var u=e("293f"),i=e("84979");for(var a in i)["default"].indexOf(a)<0&&function(t){e.d(n,t,(function(){return i[t]}))}(a);e("b36c");var o=e("f0c5"),c=Object(o["a"])(i["default"],u["b"],u["c"],!1,null,"15c8b520",null,!1,u["a"],void 0);n["default"]=c.exports},84979:function(t,n,e){"use strict";e.r(n);var u=e("2974"),i=e.n(u);for(var a in u)["default"].indexOf(a)<0&&function(t){e.d(n,t,(function(){return u[t]}))}(a);n["default"]=i.a},b36c:function(t,n,e){"use strict";var u=e("161d"),i=e.n(u);i.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/goodList/index-create-component',
    {
        'components/goodList/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("5081"))
        })
    },
    [['components/goodList/index-create-component']]
]);
