(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/activity/goods_seckill/index"],{"008f":function(t,i,e){"use strict";e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return a})),e.d(i,"a",(function(){}));var n=function(){var t=this.$createElement,i=(this._self._c,this.timeList.length),e=0==this.seckillList.length&&(1!=this.page||0==this.active);this.$mp.data=Object.assign({},{$root:{g0:i,g1:e}})},a=[]},3235:function(t,i,e){"use strict";(function(t){var n=e("4ea4");Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a=e("3995"),o=n(e("a29e")),s=e("5f9b"),c={components:{home:function(){Promise.all([e.e("common/vendor"),e.e("components/home/index")]).then(function(){return resolve(e("d878"))}.bind(null,e)).catch(e.oe)}},mixins:[o.default],data:function(){return{topImage:"",seckillList:[],timeList:[],active:5,scrollLeft:0,interval:0,status:1,countDownHour:"00",countDownMinute:"00",countDownSecond:"00",page:1,limit:8,loading:!1,loadend:!1,pageloading:!1,intoindex:"",imgHost:s.HTTP_REQUEST_URL}},onLoad:function(){this.getSeckillConfig()},onShow:function(){t.removeStorageSync("form_type_cart")},methods:{getSeckillConfig:function(){var t=this;(0,a.getSeckillIndexTime)().then((function(i){t.topImage=i.data.lovely,t.timeList=i.data.seckillTime,t.active=i.data.seckillTimeIndex,t.$nextTick((function(){t.intoindex="sort"+i.data.seckillTimeIndex})),t.timeList.length&&(t.scrollLeft=100*(t.active-1.37),setTimeout((function(){t.loading=!0}),2e3),t.seckillList=[],t.page=1,t.status=t.timeList[t.active].status,t.getSeckillList())}))},getSeckillList:function(){var t=this,i={page:t.page,limit:t.limit};t.loadend||t.pageloading||(this.pageloading=!0,(0,a.getSeckillList)(t.timeList[t.active].id,i).then((function(i){var e=i.data,n=e.length<t.limit;t.page++,t.seckillList=t.seckillList.concat(e),t.page=t.page,t.pageloading=!1,t.loadend=n})).catch((function(i){t.pageloading=!1})))},settimeList:function(t,i){this.active=i,this.interval&&(clearInterval(this.interval),this.interval=null),this.interval=0,this.countDownHour="00",this.countDownMinute="00",this.countDownSecond="00",this.status=this.timeList[this.active].status,this.loadend=!1,this.page=1,this.seckillList=[],this.getSeckillList()},goDetails:function(i){t.navigateTo({url:"/pages/activity/goods_seckill_details/index?id="+i.id+"&time="+this.timeList[this.active].stop+"&status="+this.status})}},onReachBottom:function(){this.getSeckillList()}};i.default=c}).call(this,e("543d")["default"])},8237:function(t,i,e){"use strict";var n=e("b9c3"),a=e.n(n);a.a},"9bb6":function(t,i,e){"use strict";e.r(i);var n=e("3235"),a=e.n(n);for(var o in n)["default"].indexOf(o)<0&&function(t){e.d(i,t,(function(){return n[t]}))}(o);i["default"]=a.a},a3dd:function(t,i,e){"use strict";e.r(i);var n=e("008f"),a=e("9bb6");for(var o in a)["default"].indexOf(o)<0&&function(t){e.d(i,t,(function(){return a[t]}))}(o);e("8237");var s=e("f0c5"),c=Object(s["a"])(a["default"],n["b"],n["c"],!1,null,null,null,!1,n["a"],void 0);i["default"]=c.exports},b9c3:function(t,i,e){},cbaa:function(t,i,e){"use strict";(function(t,i){var n=e("4ea4");e("8824");n(e("66fd"));var a=n(e("a3dd"));t.__webpack_require_UNI_MP_PLUGIN__=e,i(a.default)}).call(this,e("bc2e")["default"],e("543d")["createPage"])}},[["cbaa","common/runtime","common/vendor"]]]);