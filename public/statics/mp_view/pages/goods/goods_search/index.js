require('../common/vendor.js');(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/goods/goods_search/index"],{"0328":function(t,e,i){"use strict";i.r(e);var n=i("d062"),o=i("cee4");for(var a in o)["default"].indexOf(a)<0&&function(t){i.d(e,t,(function(){return o[t]}))}(a);i("81e8");var s=i("f0c5"),c=Object(s["a"])(o["default"],n["b"],n["c"],!1,null,null,null,!1,n["a"],void 0);e["default"]=c.exports},"2b1e":function(t,e,i){"use strict";(function(t){var n=i("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var o=i("78a2"),a=i("53c2"),s=n(i("a29e")),c=i("5f9b"),r={components:{goodList:function(){Promise.all([i.e("common/vendor"),i.e("components/goodList/index")]).then(function(){return resolve(i("5081"))}.bind(null,i)).catch(i.oe)},recommend:function(){Promise.all([i.e("common/vendor"),i.e("components/recommend/index")]).then(function(){return resolve(i("6019"))}.bind(null,i)).catch(i.oe)},home:function(){Promise.all([i.e("common/vendor"),i.e("components/home/index")]).then(function(){return resolve(i("d878"))}.bind(null,i)).catch(i.oe)}},mixins:[s.default],data:function(){return{hostProduct:[],searchValue:"",focus:!0,bastList:[],hotSearchList:[],first:0,limit:8,page:1,loading:!1,loadend:!1,loadTitle:"加载更多",hotPage:1,isScroll:!0,history:[],imgHost:c.HTTP_REQUEST_URL}},onLoad:function(t){this.searchValue=t.searchVal||"",this.searchValue&&this.searchBut()},onShow:function(e){t.removeStorageSync("form_type_cart"),this.getHostProduct(),this.searchList();try{this.hotSearchList=t.getStorageSync("hotList")}catch(i){}},onReachBottom:function(){this.bastList.length>0?this.getProductList():this.getHostProduct()},methods:{searchList:function(){var t=this;(0,a.searchList)({page:1,limit:10}).then((function(e){t.history=e.data}))},clear:function(){var e=this;(0,a.clearSearch)().then((function(i){t.showToast({title:i.msg,success:function(){e.history=[]}})}))},inputConfirm:function(e){e.detail.value&&(t.hideKeyboard(),this.setHotSearchValue(e.detail.value))},getRoutineHotSearch:function(){var t=this;(0,o.getSearchKeyword)().then((function(e){t.$set(t,"hotSearchList",e.data)}))},getProductList:function(){var t=this;t.loadend||t.loading||(t.loading=!0,t.loadTitle="",(0,o.getProductslist)({keyword:t.searchValue,page:t.page,limit:t.limit}).then((function(e){var i=e.data,n=i.length<t.limit;t.bastList=t.$util.SplitArray(i,t.bastList),t.$set(t,"bastList",t.bastList),t.loading=!1,t.loadend=n,t.loadTitle=n?"没有更多内容啦~":"加载更多",t.page=t.page+1})).catch((function(e){t.loading=!1,t.loadTitle="加载更多"})))},getHostProduct:function(){var t=this;this.isScroll&&(0,o.getProductHot)(t.hotPage,t.limit).then((function(e){t.isScroll=e.data.length>=t.limit,t.hostProduct=t.hostProduct.concat(e.data),t.hotPage+=1}))},setHotSearchValue:function(t){this.$set(this,"searchValue",t),this.page=1,this.loadend=!1,this.loading=!1,this.$set(this,"bastList",[]),this.getProductList()},setValue:function(t){this.$set(this,"searchValue",t.detail.value)},searchBut:function(){if(this.focus=!1,!(this.searchValue.length>0))return this.$util.Tips({title:"请输入要搜索的商品",icon:"none",duration:1e3,mask:!0});this.page=1,this.loadend=!1,this.$set(this,"bastList",[]),t.showLoading({title:"正在搜索中"}),this.getProductList(),t.hideLoading()}}};e.default=r}).call(this,i("543d")["default"])},"81e8":function(t,e,i){"use strict";var n=i("a679"),o=i.n(n);o.a},a679:function(t,e,i){},cee4:function(t,e,i){"use strict";i.r(e);var n=i("2b1e"),o=i.n(n);for(var a in n)["default"].indexOf(a)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(a);e["default"]=o.a},d062:function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){}));var n=function(){var t=this,e=t.$createElement,i=(t._self._c,t.history.length),n=t.bastList.length,o=t.bastList.length,a=t.bastList.length,s=t.bastList.length,c=0==t.bastList.length&&t.page>=1;t.$mp.data=Object.assign({},{$root:{g0:i,g1:n,g2:o,g3:a,g4:s,g5:c}})},o=[]},d7ad:function(t,e,i){"use strict";(function(t,e){var n=i("4ea4");i("8824");n(i("66fd"));var o=n(i("0328"));t.__webpack_require_UNI_MP_PLUGIN__=i,e(o.default)}).call(this,i("bc2e")["default"],i("543d")["createPage"])}},[["d7ad","common/runtime","common/vendor"]]]);