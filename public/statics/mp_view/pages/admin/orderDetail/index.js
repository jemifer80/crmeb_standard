require('../common/vendor.js');(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/admin/orderDetail/index"],{"24eb":function(e,t,n){},"4b37":function(e,t,n){"use strict";n.r(t);var r=n("d9a3"),o=n("f5ad");for(var i in o)["default"].indexOf(i)<0&&function(e){n.d(t,e,(function(){return o[e]}))}(i);n("b84f");var a=n("f0c5"),u=Object(a["a"])(o["default"],r["b"],r["c"],!1,null,null,null,!1,r["a"],void 0);t["default"]=u.exports},"945c":function(e,t,n){"use strict";(function(e){var r=n("4ea4");Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var o=r(n("2eee")),i=r(n("c973")),a=n("5a52"),u=n("48d8"),s=n("9357"),d={name:"AdminOrder",components:{PriceChange:function(){n.e("pages/admin/components/PriceChange/index").then(function(){return resolve(n("0d34"))}.bind(null,n)).catch(n.oe)},customForm:function(){n.e("components/customForm/index").then(function(){return resolve(n("cd04"))}.bind(null,n)).catch(n.oe)}},props:{},data:function(){return{openErp:!1,giveData:{give_integral:0,give_coupon:[]},giveCartInfo:[],totalNmu:0,order:!1,change:!1,order_id:"",orderInfo:{_status:{}},status:"",title:"",payType:"",types:"",statusType:"",clickNum:1,goname:"",isRefund:0}},watch:{"$route.params.oid":function(e){void 0!=e&&(this.order_id=e,this.getIndex())}},onLoad:function(e){this.order_id=e.id,this.goname=e.goname,this.statusType=e.types,this.getIndex(),this.getErpConfig()},methods:{statusChange:function(e){this.status=e},goDelivery:function(t){this.openErp||e.navigateTo({url:"/pages/admin/delivery/index?id="+t.order_id+"&listId="+t.id+"&totalNum="+t.total_num+"&orderStatus="+t.status+"&comeType=2&productType="+t.product_type})},getErpConfig:function(){var e=this;(0,u.erpConfig)().then((function(t){e.openErp=t.data.open_erp})).catch((function(t){e.$util.Tips({title:t})}))},getpreviewImage:function(t,n){e.previewImage({urls:n?this.orderInfo.refund_img:this.orderInfo.refund_goods_img,current:n?this.orderInfo.refund_img[t]:this.orderInfo.refund_goods_img[t]})},more:function(){this.order=!this.order},modify:function(e,t){1!=e&&this.openErp||(this.change=!0,this.status=e,2==e&&(this.isRefund=t))},changeclose:function(e){this.change=e},getIndex:function(){var e=this,t=this,n="";n=-3==t.statusType?(0,a.getAdminRefundDetail)(t.order_id):(0,a.getAdminOrderDetail)(t.order_id),n.then((function(n){var r=0;t.types=n.data._status._type,t.title=n.data._status._title,t.payType=n.data._status._payType,t.giveData.give_coupon=n.data.give_coupon,t.giveData.give_integral=n.data.give_integral;var o=[],i=[];n.data.cartInfo.forEach((function(e,t){r+=e.cart_num,1==e.is_gift?i.push(e):o.push(e)})),e.totalNmu=r,n.data.cartInfo=o,t.$set(t,"giveCartInfo",i),t.orderInfo=n.data})).catch((function(e){return t.$util.Tips({title:e.msg})}))},objOrderRefund:function(e){var t=this;(0,a.setOrderRefund)(e).then((function(e){t.change=!1,t.$util.Tips({title:e.msg}),t.getIndex()}),(function(e){t.change=!1,t.$util.Tips({title:e})}))},savePrice:function(e){var t=this;return(0,i.default)(o.default.mark((function n(){var r,i,u,d,f,c;return o.default.wrap((function(n){while(1)switch(n.prev=n.next){case 0:if(r=t,i={},u=e.price,d=e.refund_price,r.orderInfo.refund_status,f=e.remark,i.order_id=r.orderInfo.order_id,0!=r.status){n.next=9;break}if((0,s.isMoney)(u)){n.next=5;break}return n.abrupt("return",r.$util.Tips({title:"请输入正确的金额"}));case 5:i.price=u,(0,a.setAdminOrderPrice)(i).then((function(){r.change=!1,r.$util.Tips({title:"改价成功",icon:"success"}),r.getIndex()}),(function(){r.change=!1,r.$util.Tips({title:"改价失败",icon:"none"})})),n.next=33;break;case 9:if(2!=r.status){n.next=21;break}if(!t.isRefund){n.next=18;break}if((0,s.isMoney)(d)){n.next=13;break}return n.abrupt("return",r.$util.Tips({title:"请输入正确的金额"}));case 13:i.price=d,i.type=e.type,t.objOrderRefund(i),n.next=19;break;case 18:1==e.type&&(0,a.orderRefundAgree)(t.orderInfo.id).then((function(e){r.change=!1,r.$util.Tips({title:e.msg}),r.getIndex()})).catch((function(e){r.change=!1,r.$util.Tips({title:e})}));case 19:n.next=33;break;case 21:if(8!=r.status){n.next=27;break}i.type=e.type,i.refuse_reason=e.refuse_reason,t.objOrderRefund(i),n.next=33;break;case 27:if(f){n.next=29;break}return n.abrupt("return",t.$util.Tips({title:"请输入备注"}));case 29:i.remark=f,c="",c=-3==r.statusType?(0,a.setAdminRefundRemark)(i):(0,a.setAdminOrderRemark)(i),c.then((function(e){r.change=!1,t.$util.Tips({title:e.msg,icon:"success"}),t.orderInfo.remark=f}),(function(e){r.change=!1,r.$util.Tips({title:e})}));case 33:case"end":return n.stop()}}),n)})))()},offlinePay:function(){var e=this;this.openErp||(0,a.setOfflinePay)({order_id:this.orderInfo.order_id}).then((function(t){e.$util.Tips({title:t.msg,icon:"success"}),e.getIndex()}),(function(t){e.$util.Tips({title:t})}))},copyNum:function(t){e.setClipboardData({data:t,success:function(){}})}}};t.default=d}).call(this,n("543d")["default"])},b84f:function(e,t,n){"use strict";var r=n("24eb"),o=n.n(r);o.a},d9a3:function(e,t,n){"use strict";n.d(t,"b",(function(){return r})),n.d(t,"c",(function(){return o})),n.d(t,"a",(function(){}));var r=function(){var e=this,t=e.$createElement,n=(e._self._c,e.orderInfo.split&&e.orderInfo.split.length),r=e.orderInfo.cartInfo&&e.orderInfo.cartInfo.length,o=r?e.__map(e.orderInfo.cartInfo,(function(t,n){var r=e.__get_orig(t),o=2==e.orderInfo._status._type&&"send"==e.orderInfo.delivery_type&&!t.is_writeoff&&t.surplus_num<t.cart_num?parseInt(t.cart_num):null,i=2==e.orderInfo._status._type&&"send"==e.orderInfo.delivery_type&&!t.is_writeoff&&t.surplus_num<t.cart_num?parseInt(t.surplus_num):null;return{$orig:r,m0:o,m1:i}})):null,i=r?e.__map(e.giveData.give_coupon,(function(t,n){var r=e.__get_orig(t),o=e.giveData.give_coupon.length;return{$orig:r,g2:o}})):null,a=!e.orderInfo.split||!e.orderInfo.split.length,u=e.orderInfo.refund_img&&e.orderInfo.refund_img.length,s=e.orderInfo.refund_goods_img&&e.orderInfo.refund_goods_img.length,d="fictitious"!=e.orderInfo.delivery_type&&2===e.orderInfo._status._type&&(!e.orderInfo.split||!e.orderInfo.split.length),f=(parseFloat(e.orderInfo.total_price)+parseFloat(e.orderInfo.vip_true_price)).toFixed(2),c=e.orderInfo.vip_true_price>0?parseFloat(e.orderInfo.vip_true_price).toFixed(2):null,l=e.orderInfo.use_integral>0?parseFloat(e.orderInfo.deduction_price).toFixed(2):null,p=e.__map(e.orderInfo.promotions_detail,(function(t,n){var r=e.__get_orig(t),o=parseFloat(t.promotions_price),i=o?parseFloat(t.promotions_price).toFixed(2):null;return{$orig:r,m2:o,g10:i}})),g="looks"!=e.goname?(!e.orderInfo.refund||!e.orderInfo.refund.length)&&(0==e.orderInfo.refund_type||1==e.orderInfo.refund_type||5==e.orderInfo.refund_type)&&e.orderInfo.paid&&parseFloat(e.orderInfo.pay_price)>=0:null;e.$mp.data=Object.assign({},{$root:{g0:n,g1:r,l0:o,l1:i,g3:a,g4:u,g5:s,g6:d,g7:f,g8:c,g9:l,l2:p,g11:g}})},o=[]},f29b:function(e,t,n){"use strict";(function(e,t){var r=n("4ea4");n("8824");r(n("66fd"));var o=r(n("4b37"));e.__webpack_require_UNI_MP_PLUGIN__=n,t(o.default)}).call(this,n("bc2e")["default"],n("543d")["createPage"])},f5ad:function(e,t,n){"use strict";n.r(t);var r=n("945c"),o=n.n(r);for(var i in r)["default"].indexOf(i)<0&&function(e){n.d(t,e,(function(){return r[e]}))}(i);t["default"]=o.a}},[["f29b","common/runtime","common/vendor","pages/admin/common/vendor"]]]);