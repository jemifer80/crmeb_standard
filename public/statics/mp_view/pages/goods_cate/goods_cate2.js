(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/goods_cate/goods_cate2"],{1949:function(t,i,e){"use strict";e.d(i,"b",(function(){return r})),e.d(i,"c",(function(){return a})),e.d(i,"a",(function(){}));var r=function(){var t=this.$createElement;this._self._c},a=[]},"1cae":function(t,i,e){},"49ee":function(t,i,e){"use strict";(function(t){var r=e("4ea4");Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a=r(e("9523")),s=e("78a2"),c=e("79c7"),n=e("26cb"),o=e("7a16");e("d5f7");function u(t,i){var e=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);i&&(r=r.filter((function(i){return Object.getOwnPropertyDescriptor(t,i).enumerable}))),e.push.apply(e,r)}return e}function h(t){for(var i=1;i<arguments.length;i++){var e=null!=arguments[i]?arguments[i]:{};i%2?u(Object(e),!0).forEach((function(i){(0,a.default)(t,i,e[i])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(e)):u(Object(e)).forEach((function(i){Object.defineProperty(t,i,Object.getOwnPropertyDescriptor(e,i))}))}return t}var d=t.getSystemInfoSync().statusBarHeight+"px",l={props:{isFooter:{type:Boolean,default:!1}},computed:h(h({},(0,n.mapState)({cartNum:function(t){return t.indexData.cartNum}})),(0,n.mapGetters)(["isLogin","uid","cartNum"])),components:{productWindow:function(){e.e("components/productWindow/index").then(function(){return resolve(e("82c0"))}.bind(null,e)).catch(e.oe)},goodList:function(){e.e("components/d_goodList/index").then(function(){return resolve(e("965f"))}.bind(null,e)).catch(e.oe)},cartList:function(){e.e("components/cartList/index").then(function(){return resolve(e("ae6c"))}.bind(null,e)).catch(e.oe)}},data:function(){return{marTop:0,sysHeight:d,categoryList:[],navActive:0,categoryTitle:"",categoryErList:[],tabLeft:0,isWidth:0,tabClick:0,iSlong:!0,tempArr:[],loading:!1,loadend:!1,loadTitle:"加载更多",page:1,limit:10,cid:0,sid:0,isAuto:!1,isShowAuth:!1,attr:{cartAttr:!1,productAttr:[],productSelect:{}},productValue:[],attrValue:"",storeName:"",id:0,cartData:{cartList:[],iScart:!1},totalPrice:0,is_vip:0,cart_num:0,storeInfo:{}}},mounted:function(){var i=this;t.getSystemInfo({success:function(t){i.isWidth=t.windowWidth/5}})},methods:{authColse:function(t){this.isShowAuth=t},updateFun:function(t){t.cartNum&&this.tempArr.forEach((function(i){i.id==t.id&&(i.cart_num=t.cartNum)}))},getMarTop:function(){var i=this,e=this;setTimeout((function(){var r=t.createSelectorQuery().in(i).select(".mp-header");r.boundingClientRect((function(t){e.marTop=t.height})).exec()}),100)},subOrder:function(){var i=this.cartData.cartList,e=[];if(!i.length)return this.$util.Tips({title:"请选择产品"});i.forEach((function(t){t.attrStatus&&t.status&&e.push(t.id)})),t.navigateTo({url:"/pages/goods/order_confirm/index?cartId="+e.join(",")}),this.cartData.iScart=!1},getTotalPrice:function(){var t=this,i=t.cartData.cartList,e=0;i.forEach((function(i){i.attrStatus&&i.status&&(e=t.$util.$h.Add(e,t.$util.$h.Mul(i.cart_num,i.truePrice)))})),t.$set(t,"totalPrice",e)},ChangeSubDel:function(t){var i=this,e=i.cartData.cartList,r=[];e.forEach((function(t){r.push(t.id)})),(0,c.cartDel)(r.join(",")).then((function(t){i.$set(i.cartData,"cartList",[]),i.cartData.iScart=!1,i.totalPrice=0,i.page=1,i.loadend=!1,i.tempArr=[],i.productslist(),i.getCartNum()}))},ChangeOneDel:function(t,i){var e=this,r=e.cartData.cartList;(0,c.cartDel)(t.toString()).then((function(t){r.splice(i,1),r.length||(e.cartData.iScart=!1,e.page=1,e.loadend=!1,e.tempArr=[],e.productslist()),e.getCartNum()}))},getCartList:function(t){var i=this;(0,c.vcartList)().then((function(e){i.$set(i.cartData,"cartList",e.data),e.data.length?i.$set(i.cartData,"iScart",!t&&!i.cartData.iScart):i.$set(i.cartData,"iScart",!1),i.getTotalPrice()}))},closeList:function(t){this.$set(this.cartData,"iScart",t)},getCartNum:function(){var t=this;(0,c.getCartCounts)().then((function(i){t.$store.commit("indexData/setCartNum",i.data.count>99?"..":i.data.count)}))},onMyEvent:function(){this.$set(this.attr,"cartAttr",!1)},DefaultSelect:function(){var t=this.attr.productAttr,i=[];for(var e in this.productValue)if(this.productValue[e].stock>0){i=this.attr.productAttr.length?e.split(","):[];break}for(var r=0;r<t.length;r++)this.$set(t[r],"index",i[r]);var a=this.productValue[i.join(",")];a&&t.length?(this.$set(this.attr.productSelect,"store_name",this.storeName),this.$set(this.attr.productSelect,"image",a.image),this.$set(this.attr.productSelect,"price",a.price),this.$set(this.attr.productSelect,"stock",a.stock),this.$set(this.attr.productSelect,"unique",a.unique),this.$set(this.attr.productSelect,"cart_num",1),this.$set(this.attr.productSelect,"vip_price",a.vip_price),this.$set(this,"attrValue",i.join(","))):!a&&t.length?(this.$set(this.attr.productSelect,"store_name",this.storeName),this.$set(this.attr.productSelect,"image",this.storeInfo.image),this.$set(this.attr.productSelect,"price",this.storeInfo.price),this.$set(this.attr.productSelect,"stock",0),this.$set(this.attr.productSelect,"unique",""),this.$set(this.attr.productSelect,"cart_num",0),this.$set(this.attr.productSelect,"vip_price",this.storeInfo.vip_price),this.$set(this,"attrValue","")):a||t.length||(this.$set(this.attr.productSelect,"store_name",this.storeName),this.$set(this.attr.productSelect,"image",this.storeInfo.image),this.$set(this.attr.productSelect,"price",this.storeInfo.price),this.$set(this.attr.productSelect,"stock",this.storeInfo.stock),this.$set(this.attr.productSelect,"unique",this.storeInfo.unique||""),this.$set(this.attr.productSelect,"cart_num",1),this.$set(this,"attrValue",""),this.$set(this.attr.productSelect,"vip_price",this.storeInfo.vip_price))},ChangeAttr:function(t){var i=this.productValue[t];i&&i.stock>0?(this.$set(this.attr.productSelect,"image",i.image),this.$set(this.attr.productSelect,"price",i.price),this.$set(this.attr.productSelect,"stock",i.stock),this.$set(this.attr.productSelect,"unique",i.unique),this.$set(this.attr.productSelect,"vip_price",i.vip_price),this.$set(this.attr.productSelect,"cart_num",1),this.$set(this,"attrValue",t)):i&&0==i.stock?(this.$set(this.attr.productSelect,"image",i.image),this.$set(this.attr.productSelect,"price",i.price),this.$set(this.attr.productSelect,"stock",0),this.$set(this.attr.productSelect,"unique",""),this.$set(this.attr.productSelect,"vip_price",i.vip_price),this.$set(this.attr.productSelect,"cart_num",0),this.$set(this,"attrValue","")):(this.$set(this.attr.productSelect,"image",this.storeInfo.image),this.$set(this.attr.productSelect,"price",this.storeInfo.price),this.$set(this.attr.productSelect,"stock",0),this.$set(this.attr.productSelect,"unique",""),this.$set(this.attr.productSelect,"vip_price",this.storeInfo.vip_price),this.$set(this.attr.productSelect,"cart_num",0),this.$set(this,"attrValue",""))},attrVal:function(t){this.$set(this.attr.productAttr[t.indexw],"index",this.attr.productAttr[t.indexw].attr_values[t.indexn])},iptCartNum:function(t){this.$set(this.attr.productSelect,"cart_num",t)},onLoadFun:function(){setTimeout((function(){this.isShowAuth=!1}),10)},productslist:function(){var t=this;t.loadend||t.loading||(t.loading=!0,t.loadTitle="",(0,s.getProductslist)({page:t.page,limit:t.limit,type:1,cid:t.cid,sid:t.sid}).then((function(i){var e=i.data,r=e.length<t.limit;t.tempArr=t.$util.SplitArray(e,t.tempArr),t.$set(t,"tempArr",t.tempArr),t.loading=!1,t.loadend=r,t.loadTitle=r?"没有更多内容啦~":"加载更多",t.page=t.page+1})).catch((function(i){t.loading=!1,t.loadTitle="加载更多"})))},goCartDan:function(t,i){this.isLogin?(this.tempArr[i].cart_num=1,this.$set(this,"tempArr",this.tempArr),this.goCat(0,t.id,1)):this.getIsLogin()},ChangeCartNumDan:function(t,i,e){var r=this.tempArr[i],a=this.tempArr[i].stock;this.ChangeCartNum(t,r,a,0,e.id)},ChangeCartNumDuo:function(t){var i=this.productValue[this.attrValue];if(void 0!==i||this.attr.productAttr.length||(i=this.attr.productSelect),void 0!==i){var e=i.stock||0,r=this.attr.productSelect;this.ChangeCartNum(t,r,e,1,this.id)}},ChangeCartList:function(t,i){var e=this.cartData.cartList,r=e[i],a=e[i].trueStock;this.ChangeCartNum(t,r,a,0,r.product_id,i,1),e.length||(this.cartData.iScart=!1,this.page=1,this.loadend=!1,this.tempArr=[],this.productslist())},ChangeCartNum:function(t,i,e,r,a,s,c){if(t){if(i.cart_num++,i.cart_num>e)return r?(this.$set(this.attr.productSelect,"cart_num",e||1),this.$set(this,"cart_num",e||1)):(i.cart_num=e||0,this.$set(this,"tempArr",this.tempArr),this.$set(this.cartData,"cartList",this.cartData.cartList)),this.$util.Tips({title:"该产品没有更多库存了"});r||(c?(this.goCat(0,a,1,1,i.product_attr_unique),this.getTotalPrice()):this.goCat(0,a,1))}else i.cart_num--,0==i.cart_num&&this.cartData.cartList.splice(s,1),i.cart_num<0?r?(this.$set(this.attr.productSelect,"cart_num",1),this.$set(this,"cart_num",1)):(i.cart_num=0,this.$set(this,"tempArr",this.tempArr),this.$set(this.cartData,"cartList",this.cartData.cartList)):r||(c?(this.goCat(0,a,0,1,i.product_attr_unique),this.getTotalPrice()):this.goCat(0,a,0));this.tempArr.forEach((function(t){t.id==a&&(t.cart_num=i.cart_num)}))},goCatNum:function(){this.goCat(1,this.id,1)},goCat:function(t,i,e,r,a){var c=this;if(t){var n=c.productValue[this.attrValue];if(c.attr.productAttr.length&&void 0===n)return c.$util.Tips({title:"产品库存不足，请选择其它属性"})}var o={product_id:i,num:t?c.attr.productSelect.cart_num:1,type:e,unique:t?c.attr.productSelect.unique:r?a:""};(0,s.postCartNum)(o).then((function(i){t&&(c.attr.cartAttr=!1,c.$util.Tips({title:"添加购物车成功"}),c.tempArr.forEach((function(t,i){if(t.id==c.id){var e=c.attr.productSelect.stock,r=parseInt(t.cart_num)+parseInt(c.attr.productSelect.cart_num);t.cart_num=r>e?e:r}}))),c.getCartNum(),r||c.getCartList(1)})).catch((function(t){return c.$util.Tips({title:t})}))},goCartDuo:function(i){this.isLogin?(t.showLoading({title:"加载中"}),this.storeName=i.store_name,this.getAttrs(i.id),this.$set(this,"id",i.id),this.$set(this.attr,"cartAttr",!0)):this.getIsLogin()},getIsLogin:function(){this.isShowAuth=!0},getAttrs:function(i){var e=this;(0,s.getAttr)(i,0).then((function(i){t.hideLoading(),e.$set(e.attr,"productAttr",i.data.productAttr),e.$set(e,"productValue",i.data.productValue),e.$set(e,"is_vip",i.data.storeInfo.is_vip),e.$set(e,"storeInfo",i.data.storeInfo),e.DefaultSelect()}))},goDetail:function(i){(0,o.goShopDetail)(i,this.uid).then((function(e){t.navigateTo({url:"/pages/goods_details/index?id=".concat(i.id,"&fromType=1")})}))},openTap:function(){this.iSlong=!1},closeTap:function(){this.iSlong=!0},getAllCategory:function(){var t=this;(0,s.getCategoryList)().then((function(i){var e=i.data;e.forEach((function(t){t.children.unshift({id:0,cate_name:"全部"})})),t.categoryTitle=e[0].cate_name,t.cid=e[0].id,t.sid=0,t.navActive=0,t.tabClick=0,t.categoryList=e,t.categoryErList=i.data[0].children?i.data[0].children:[],t.page=1,t.loadend=!1,t.tempArr=[],t.productslist()}))},tapNav:function(i,e){t.pageScrollTo({duration:0,scrollTop:0});var r=this.categoryList[i];this.navActive=i,this.categoryTitle=r.cate_name,this.categoryErList=e.children?e.children:[],this.tabClick=0,this.tabLeft=0,this.cid=r.id,this.sid=0,this.page=1,this.loadend=!1,this.tempArr=[],this.productslist()},longClick:function(t){this.categoryErList.length>3&&(this.tabLeft=(t-1)*(this.isWidth+6)),this.tabClick=t,this.iSlong=!0,this.sid=this.categoryErList[t].id,this.page=1,this.loadend=!1,this.tempArr=[],this.productslist()}}};i.default=l}).call(this,e("543d")["default"])},"719c":function(t,i,e){"use strict";var r=e("1cae"),a=e.n(r);a.a},"786b":function(t,i,e){"use strict";e.r(i);var r=e("49ee"),a=e.n(r);for(var s in r)["default"].indexOf(s)<0&&function(t){e.d(i,t,(function(){return r[t]}))}(s);i["default"]=a.a},fe7a:function(t,i,e){"use strict";e.r(i);var r=e("1949"),a=e("786b");for(var s in a)["default"].indexOf(s)<0&&function(t){e.d(i,t,(function(){return a[t]}))}(s);e("719c");var c=e("f0c5"),n=Object(c["a"])(a["default"],r["b"],r["c"],!1,null,null,null,!1,r["a"],void 0);i["default"]=n.exports}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'pages/goods_cate/goods_cate2-create-component',
    {
        'pages/goods_cate/goods_cate2-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("fe7a"))
        })
    },
    [['pages/goods_cate/goods_cate2-create-component']]
]);
