(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["pages/activity/goods_seckill_details/index"],{"0c50":function(t,e,i){"use strict";(function(t,o){var s=i("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r,n=s(i("9523")),a=i("26cb"),u=i("3995"),c=i("78a2"),h=i("ace8"),l=(i("d5f7"),i("2f2d")),d=i("99c8"),p=i("5f9b"),f=i("9357"),m=s(i("a29e")),g=getApp(),b=t.getSystemInfoSync().statusBarHeight,S=(r={computed:(0,a.mapGetters)(["isLogin"]),mixins:[m.default],data:function(){return{showSkeleton:!0,isNodes:0,dataShow:0,id:0,time:0,countDownHour:"00",countDownMinute:"00",countDownSecond:"00",storeInfo:{brand_name:""},imgUrls:[],parameter:{navbar:"1",return:"1",title:"抢购详情页",color:!1},attribute:{cartAttr:!1,productAttr:[],productSelect:{}},productValue:[],isOpen:!1,attr:"请选择",attrValue:"",status:1,isAuto:!1,isShowAuth:!1,iShidden:!1,limitNum:1,iSplus:!1,replyCount:0,reply:[],replyChance:0,navH:"",navList:["商品","评价","详情"],opacity:0,scrollY:0,topArr:[],toView:"",height:0,heightArr:[],lock:!1,scrollTop:0,tagStyle:{img:"width:100%;display:block;",table:"width:100%",video:"width:100%"},datatime:0,navActive:0,meunHeight:0,backH:"",posters:!1,weixinStatus:!1,posterImageStatus:!1,canvasStatus:!1,storeImage:"",PromotionCode:"",posterImage:"",posterbackgd:"/static/images/posterbackgd.png",actionSheetHidden:!1,cart_num:"",homeTop:20,returnShow:!0,H5ShareBox:!1,routineContact:0,siteName:"",themeColor:"",fontColor:"",skuArr:[],codeShow:!1,cid:"1",codeVal:"",size:200,unit:"upx",background:"#FFF",foreground:"#000",pdground:"#000",codeIcon:"",iconsize:40,lv:3,onval:!0,loadMake:!0,base64Show:0,shareQrcode:0,followCode:"",selectSku:{},currentPage:!1,sysHeight:b,imgHost:p.HTTP_REQUEST_URL,posterTitle:""}},components:{zbCode:function(){Promise.all([i.e("common/vendor"),i.e("components/zb-code/zb-code")]).then(function(){return resolve(i("0657"))}.bind(null,i)).catch(i.oe)},productConSwiper:function(){i.e("components/productConSwiper/index").then(function(){return resolve(i("4550"))}.bind(null,i)).catch(i.oe)},productWindow:function(){i.e("components/productWindow/index").then(function(){return resolve(i("82c0"))}.bind(null,i)).catch(i.oe)},userEvaluation:function(){i.e("components/userEvaluation/index").then(function(){return resolve(i("56cf"))}.bind(null,i)).catch(i.oe)},kefuIcon:function(){i.e("components/kefuIcon/index").then(function(){return resolve(i("2004"))}.bind(null,i)).catch(i.oe)},cusPreviewImg:function(){i.e("components/cusPreviewImg/index").then(function(){return resolve(i("17d4"))}.bind(null,i)).catch(i.oe)},"jyf-parser":function(){Promise.all([i.e("common/vendor"),i.e("components/jyf-parser/jyf-parser")]).then(function(){return resolve(i("217b"))}.bind(null,i)).catch(i.oe)},countDown:function(){i.e("components/countDown/index").then(function(){return resolve(i("00a7"))}.bind(null,i)).catch(i.oe)},homeList:function(){i.e("components/homeList/index").then(function(){return resolve(i("8164"))}.bind(null,i)).catch(i.oe)}}},(0,n.default)(r,"computed",(0,a.mapGetters)(["isLogin"])),(0,n.default)(r,"watch",{isLogin:{handler:function(t,e){},deep:!0}}),(0,n.default)(r,"onLoad",(function(e){var i=this,o=this,s=getCurrentPages();o.returnShow=1!==s.length,t.getSystemInfo({success:function(t){o.height=t.windowHeight,t.statusBarHeight}}),this.isLogin&&(0,l.silenceBindingSpread)(),this.navH=g.globalData.navHeight;var r=t.getMenuButtonBoundingClientRect();if(this.meunHeight=r.height,this.backH=o.navH/2+this.meunHeight/2,e.scene){var n=this.$util.getUrlParams(decodeURIComponent(e.scene));if(!n.id)return this.showSkeleton=!1,this.$util.Tips({title:"缺少参数无法查看商品"},{tab:3,url:1});this.id=n.id,n.spid&&(g.globalData.spid=n.spid)}e.id&&(this.id=e.id,e.spid&&(g.globalData.spid=e.spid)),this.getSeckillDetail(),this.isLogin&&e.id&&o.downloadFilePromotionCode(),this.$nextTick((function(){var e=t.getMenuButtonBoundingClientRect(),o=t.createSelectorQuery().in(i);o.select("#home").boundingClientRect((function(t){i.homeTop=2*e.top+e.height-t.height})).exec()})),this.colorData();var a=[{themeColor:"#1DB0FC",fontColor:"#FD502F"},{themeColor:"#42CA4D",fontColor:"#FF7600"},{themeColor:"#e93323",fontColor:"#e93323"},{themeColor:"#FF448F",fontColor:"#FF448F"},{themeColor:"#FE5C2D",fontColor:"#FE5C2D"},{themeColor:"#E0A558",fontColor:"#DA8C18"}];setTimeout((function(){switch(i.colorNum){case 1:i.themeColor=a[0].themeColor,i.fontColor=a[0].fontColor;break;case 2:i.themeColor=a[1].themeColor,i.fontColor=a[1].fontColor;break;case 3:i.themeColor=a[2].themeColor,i.fontColor=a[2].fontColor;break;case 4:i.themeColor=a[3].themeColor,i.fontColor=a[3].fontColor;break;case 5:i.themeColor=a[4].themeColor,i.fontColor=a[4].fontColor;break;case 6:i.themeColor=a[5].themeColor,i.fontColor=a[5].fontColor;break;default:i.themeColor=a[2].themeColor,i.fontColor=a[2].fontColor;break}}),1)})),(0,n.default)(r,"onReady",(function(){this.isNodes++})),(0,n.default)(r,"onShow",(function(){t.removeStorageSync("form_type_cart")})),(0,n.default)(r,"methods",{seckillQRCode:function(t){var e=this;(0,u.seckillQRCode)(this.id).then((function(i){e.followCode=i.data.code_base,e.getImageBase64(t)}))},changeLogin:function(){this.getIsLogin()},onLoadFun:function(t){this.downloadFilePromotionCode(),this.isShowAuth=!1},getIsLogin:function(){this.isShowAuth=!0},authColse:function(t){this.isShowAuth=t},moreNav:function(){this.currentPage=!this.currentPage},showImg:function(t){this.$refs.cusPreviewImg.open(this.selectSku.suk)},changeSwitch:function(t){var e=this,i=this.skuArr[t];this.$set(this,"selectSku",i);var o=i.suk.split(",");o.forEach((function(t,i){e.$set(e.attribute.productAttr[i],"index",o[i])})),i&&(this.$set(this.attribute.productSelect,"image",i.image),this.$set(this.attribute.productSelect,"price",i.price),this.$set(this.attribute.productSelect,"stock",i.stock),this.$set(this.attribute.productSelect,"unique",i.unique),this.$set(this.attribute.productSelect,"cart_num",1),this.$set(this.attribute.productSelect,"quota",i.quota),this.$set(this.attribute.productSelect,"quota_show",i.quota_show),this.$set(this,"attrValue",i.suk),this.attrTxt="已选择")},qrR:function(t){},getpreviewImage:function(){if(this.posterImage){var e=[];e.push(this.posterImage),t.previewImage({urls:e,current:this.posterImage})}else his.$util.Tips({title:"您的海报尚未生成"})},iptCartNum:function(t){this.$set(this.attribute.productSelect,"cart_num",t),this.$set(this,"cart_num",t)},returns:function(){return t.navigateBack({delta:1})},getSeckillDetail:function(){var e=this,i=this;(0,u.getSeckillDetail)(i.id).then((function(o){e.dataShow=1;var s=o.data.storeInfo.title;for(var r in e.storeInfo=o.data.storeInfo,e.posterTitle=o.data.product_poster_title,e.datatime=Number(o.data.storeInfo.last_time),e.status=o.data.storeInfo.status,e.imgUrls=o.data.storeInfo.images,e.storeInfo.description=e.storeInfo.description.replace(/<img/gi,'<img style="max-width:100%;height:auto;float:left;display:block" '),e.attribute.productAttr=o.data.productAttr,e.productValue=o.data.productValue,o.data.productValue){var n=o.data.productValue[r];i.skuArr.push(n)}i.selectSku=i.skuArr[0],e.attribute.productSelect.num=o.data.storeInfo.num,e.attribute.productSelect.once_num=o.data.storeInfo.once_num,e.replyCount=o.data.replyCount,e.reply=o.data.reply,e.replyChance=o.data.replyChance,e.shareQrcode=o.data.share_qrcode,i.routineContact=Number(o.data.routine_contact_type),t.setNavigationBarTitle({title:s.substring(0,7)+"..."}),i.siteName=o.data.site_name;var a=["商品","详情"];o.data.replyCount&&a.splice(1,0,"评价"),i.$set(i,"navList",a),i.downloadFilestoreImage(),i.DefaultSelect(),setTimeout((function(){i.infoScroll()}),500),g.globalData.openPages="/pages/activity/goods_seckill_details/index?id="+i.id+"&time="+i.time+"&status="+i.status+"&spid="+i.storeInfo.uid,setTimeout((function(){i.showSkeleton=!1}),300)})).catch((function(t){i.$util.Tips({title:t},{tab:3})}))},setShare:function(){this.$wechat.isWeixin()&&this.$wechat.wechatEvevt(["updateAppMessageShareData","updateTimelineShareData","onMenuShareAppMessage","onMenuShareTimeline"],{desc:this.storeInfo.info,title:this.storeInfo.title,link:location.href,imgUrl:this.storeInfo.image}).then((function(t){})).catch((function(t){}))},DefaultSelect:function(){var t=this.attribute.productAttr,e=[];for(var i in this.productValue)if(this.productValue[i].quota>0){e=this.attribute.productAttr.length?i.split(","):[];break}for(var o=0;o<t.length;o++)this.$set(t[o],"index",e[o]);var s=this.productValue[e.join(",")];s&&t.length?(this.$set(this.attribute.productSelect,"store_name",this.storeInfo.title),this.$set(this.attribute.productSelect,"image",s.image),this.$set(this.attribute.productSelect,"price",s.price),this.$set(this.attribute.productSelect,"stock",s.stock),this.$set(this.attribute.productSelect,"unique",s.unique),this.$set(this.attribute.productSelect,"quota",s.quota),this.$set(this.attribute.productSelect,"quota_show",s.quota_show),this.$set(this.attribute.productSelect,"product_stock",s.product_stock),this.$set(this.attribute.productSelect,"cart_num",1),this.$set(this,"attrValue",e.join(",")),this.attrValue=e.join(",")):!s&&t.length?(this.$set(this.attribute.productSelect,"store_name",this.storeInfo.title),this.$set(this.attribute.productSelect,"image",this.storeInfo.image),this.$set(this.attribute.productSelect,"price",this.storeInfo.price),this.$set(this.attribute.productSelect,"quota",0),this.$set(this.attribute.productSelect,"quota_show",0),this.$set(this.attribute.productSelect,"product_stock",0),this.$set(this.attribute.productSelect,"stock",0),this.$set(this.attribute.productSelect,"unique",""),this.$set(this.attribute.productSelect,"cart_num",0),this.$set(this,"attrValue",""),this.$set(this,"attrTxt","请选择")):s||t.length||(this.$set(this.attribute.productSelect,"store_name",this.storeInfo.title),this.$set(this.attribute.productSelect,"image",this.storeInfo.image),this.$set(this.attribute.productSelect,"price",this.storeInfo.price),this.$set(this.attribute.productSelect,"stock",this.storeInfo.stock),this.$set(this.attribute.productSelect,"quota",this.storeInfo.quota),this.$set(this.attribute.productSelect,"product_stock",this.storeInfo.product_stock),this.$set(this.attribute.productSelect,"unique",this.storeInfo.unique||""),this.$set(this.attribute.productSelect,"cart_num",1),this.$set(this.attribute.productSelect,"quota",s.quota),this.$set(this.attribute.productSelect,"product_stock",s.product_stock),this.$set(this,"attrValue",""),this.$set(this,"attrTxt","请选择"))},selecAttr:function(){this.currentPage=!1,this.attribute.cartAttr=!0},onMyEvent:function(){this.$set(this.attribute,"cartAttr",!1),this.$set(this,"isOpen",!1)},ChangeCartNum:function(t){var e=this.productValue[this.attrValue];if(this.cart_num&&(e.cart_num=this.cart_num,this.attribute.productSelect.cart_num=this.cart_num),void 0!==e||this.attribute.productAttr.length||(e=this.attribute.productSelect),void 0!==e){e.stock,e.quota_show;var i=e.quota||0,o=e.product_stock||0,s=this.attribute.productSelect,r=this.storeInfo.num||0,n=this.storeInfo.once_num||0;if(void 0==e.cart_num&&(e.cart_num=1),t){s.cart_num++;var a=[];a.push(r),a.push(n),a.push(i),a.push(o);var u=Math.min.apply(null,a);s.cart_num>=u&&(this.$set(this.attribute.productSelect,"cart_num",u||1),this.$set(this,"cart_num",u||1)),this.$set(this,"cart_num",s.cart_num),this.$set(this.attribute.productSelect,"cart_num",s.cart_num)}else s.cart_num--,s.cart_num<1&&(this.$set(this.attribute.productSelect,"cart_num",1),this.$set(this,"cart_num",1)),this.$set(this,"cart_num",s.cart_num),this.$set(this.attribute.productSelect,"cart_num",s.cart_num)}},attrVal:function(t){this.attribute.productAttr[t.indexw].index=this.attribute.productAttr[t.indexw].attr_values[t.indexn]},ChangeAttr:function(t){this.$set(this,"cart_num",1);var e=this.productValue[t];this.$set(this,"selectSku",e),e?(this.$set(this.attribute.productSelect,"image",e.image),this.$set(this.attribute.productSelect,"price",e.price),this.$set(this.attribute.productSelect,"stock",e.stock),this.$set(this.attribute.productSelect,"unique",e.unique),this.$set(this.attribute.productSelect,"cart_num",1),this.$set(this.attribute.productSelect,"quota",e.quota),this.$set(this.attribute.productSelect,"quota_show",e.quota_show),this.$set(this,"attrValue",t),this.attrTxt="已选择"):(this.$set(this.attribute.productSelect,"image",this.storeInfo.image),this.$set(this.attribute.productSelect,"price",this.storeInfo.price),this.$set(this.attribute.productSelect,"stock",0),this.$set(this.attribute.productSelect,"unique",""),this.$set(this.attribute.productSelect,"cart_num",0),this.$set(this.attribute.productSelect,"quota",0),this.$set(this.attribute.productSelect,"quota_show",0),this.$set(this,"attrValue",""),this.attrTxt="已选择")},scroll:function(t){var e=t.detail.scrollTop,i=e/200;if(i=i>1?1:i,this.opacity=i,this.scrollY=e,this.currentPage=!1,this.lock)this.lock=!1;else for(var o=0;o<this.topArr.length;o++)if(e<this.topArr[o]-g.globalData.navHeight/2+this.heightArr[o]){this.navActive=o;break}},tap:function(t,e){var i=t.id;e=e;this.replyCount||"past1"!=i||(i="past2"),this.toView=i,this.navActive=e,this.lock=!0,this.scrollTop=e>0?this.topArr[e]-g.globalData.navHeight/2:this.topArr[e]},infoScroll:function(){for(var t=this,e=[],i=[],s=0;s<t.navList.length;s++){var r=o.createSelectorQuery().in(this),n="#past"+s;this.replyCount||1!=s||(n="#past2"),r.select(n).boundingClientRect(),r.exec((function(o){var s=o[0].top,r=o[0].height;e.push(s),i.push(r),t.topArr=e,t.heightArr=i}))}},setCollect:(0,f.Debounce)((function(){var t=this;this.storeInfo.userCollect?(0,c.collectDel)(this.storeInfo.product_id).then((function(e){t.storeInfo.userCollect=!t.storeInfo.userCollect})):(0,c.collectAdd)(this.storeInfo.product_id).then((function(e){t.storeInfo.userCollect=!t.storeInfo.userCollect}))})),openAlone:(0,f.Debounce)((function(){t.navigateTo({url:"/pages/goods_details/index?id=".concat(this.storeInfo.product_id)})})),goCat:function(){var e=this;if(this.isLogin){this.currentPage=!1;var i=this.productValue[this.attrValue];if(this.isOpen?this.attribute.cartAttr=!0:this.attribute.cartAttr=!this.attribute.cartAttr,!0===this.attribute.cartAttr&&0==this.isOpen)return this.isOpen=!0;if(this.attribute.productAttr.length&&void 0===i&&1==this.isOpen)return g.$util.Tips({title:"请选择属性"});(0,c.postCartAdd)({productId:this.storeInfo.product_id,secKillId:this.id,bargainId:0,combinationId:0,cartNum:this.cart_num,uniqueId:void 0!==i?i.unique:"",new:1}).then((function(i){e.isOpen=!1,t.navigateTo({url:"/pages/goods/order_confirm/index?new=1&cartId="+i.data.cartId})})).catch((function(t){return e.$util.Tips({title:t})}))}else this.getIsLogin()},listenerActionSheet:function(){this.currentPage=!1,!1===this.isLogin?this.getIsLogin():(this.posters=!0,this.goPoster())},listenerActionClose:function(){this.posters=!1},posterImageClose:function(){this.posterImageStatus=!1,this.posters=!1},setDomain:function(t){return t=t?t.toString():"",t.indexOf("https://")>-1?t:t.replace("http://","https://")},downloadFilestoreImage:function(){var e=this;t.downloadFile({url:e.setDomain(e.storeInfo.image),success:function(t){e.storeImage=t.tempFilePath,e.base64Show=1},fail:function(){return e.$util.Tips({title:""})}})},downloadFilePromotionCode:function(e){var i=this;(0,u.seckillCode)(i.id,{stop_time:i.datatime}).then((function(o){t.downloadFile({url:i.setDomain(o.data.code),success:function(t){i.$set(i,"isDown",!1),"function"==typeof e?e&&e(t.tempFilePath):i.$set(i,"PromotionCode",t.tempFilePath)},fail:function(){i.$set(i,"isDown",!1),i.$set(i,"PromotionCode","")}})})).catch((function(t){i.$set(i,"isDown",!1),i.$set(i,"PromotionCode","")}))},getImageBase64:function(t){var e=this;(0,h.imageBase64)(e.storeImage,e.followCode).then((function(i){e.storeImage=i.data.image,t&&(e.PromotionCode=i.data.code),e.base64Show=1})).catch((function(){}))},goPoster:function(){var e=this;e.$set(e,"canvasStatus",!0);var i=[e.posterbackgd,e.storeImage,e.PromotionCode];return""!=e.PromotionCode||e.isDown?e.isDown?e.$util.Tips({title:"正在下载海报,请稍后再试！"},(function(){e.posters=!1})):void t.getImageInfo({src:e.PromotionCode,fail:function(t){return e.$util.Tips({title:"小程序二维码需要发布正式版后才能获取到"})},success:function(){""==i[2]?e.downloadFilePromotionCode((function(t){if(i[2]=t,""==i[2])return e.$util.Tips({title:"海报二维码生成失败！"});e.$nextTick((function(){e.$util.PosterCanvas(e.fontColor,e.themeColor,e.siteName,i,e.storeInfo.title,e.storeInfo.price,e.storeInfo.ot_price,e.posterTitle,(function(t){e.$set(e,"posterImage",t),e.$set(e,"posterImageStatus",!0),e.$set(e,"canvasStatus",!1),e.$set(e,"actionSheetHidden",!e.actionSheetHidden)}))}))})):e.$nextTick((function(){e.$util.PosterCanvas(e.fontColor,e.themeColor,e.siteName,i,e.storeInfo.title,e.storeInfo.price,e.storeInfo.ot_price,e.posterTitle,(function(t){e.$set(e,"posterImage",t),e.$set(e,"posterImageStatus",!0),e.$set(e,"canvasStatus",!1),e.$set(e,"actionSheetHidden",!e.actionSheetHidden)}))}))}}):e.$util.Tips({title:"小程序二维码需要发布正式版后才能获取到"},(function(){e.posters=!1}))},savePosterPath:function(){var e=this;t.getSetting({success:function(i){i.authSetting["scope.writePhotosAlbum"]?t.saveImageToPhotosAlbum({filePath:e.posterImage,success:function(t){e.posterImageClose(),e.$util.Tips({title:"保存成功",icon:"success"})},fail:function(t){e.$util.Tips({title:"保存失败"})}}):t.authorize({scope:"scope.writePhotosAlbum",success:function(){t.saveImageToPhotosAlbum({filePath:e.posterImage,success:function(t){e.posterImageClose(),e.$util.Tips({title:"保存成功",icon:"success"})},fail:function(t){e.$util.Tips({title:"保存失败"})}})}})}})},setShareInfoStatus:function(){var t=this,e=this.storeInfo,i=location.href;this.$wechat.isWeixin()&&(this.posters=!0,(0,d.getUserInfo)().then((function(o){i=-1===i.indexOf("?")?i+"?spid="+o.data.uid:i+"&spid="+o.data.uid;var s={desc:e.store_info,title:e.store_name,link:i,imgUrl:e.image};t.$wechat.wechatEvevt(["updateAppMessageShareData","updateTimelineShareData"],s)})))}}),(0,n.default)(r,"onShareAppMessage",(function(){return{title:this.storeInfo.title,path:g.globalData.openPages,imageUrl:this.storeInfo.image}})),(0,n.default)(r,"onShareTimeline",(function(){return{title:this.storeInfo.title,imageUrl:this.storeInfo.image,path:g.globalData.openPages}})),r);e.default=S}).call(this,i("543d")["default"],i("bc2e")["default"])},3869:function(t,e,i){"use strict";i.r(e);var o=i("c3ee"),s=i("5602");for(var r in s)["default"].indexOf(r)<0&&function(t){i.d(e,t,(function(){return s[t]}))}(r);i("3ffc");var n=i("f0c5"),a=Object(n["a"])(s["default"],o["b"],o["c"],!1,null,null,null,!1,o["a"],void 0);e["default"]=a.exports},"3ffc":function(t,e,i){"use strict";var o=i("c9da"),s=i.n(o);s.a},5602:function(t,e,i){"use strict";i.r(e);var o=i("0c50"),s=i.n(o);for(var r in o)["default"].indexOf(r)<0&&function(t){i.d(e,t,(function(){return o[t]}))}(r);e["default"]=s.a},a634:function(t,e,i){"use strict";(function(t,e){var o=i("4ea4");i("8824");o(i("66fd"));var s=o(i("3869"));t.__webpack_require_UNI_MP_PLUGIN__=i,e(s.default)}).call(this,i("bc2e")["default"],i("543d")["createPage"])},c3ee:function(t,e,i){"use strict";i.d(e,"b",(function(){return s})),i.d(e,"c",(function(){return r})),i.d(e,"a",(function(){return o}));var o={jyfParser:function(){return Promise.all([i.e("common/vendor"),i.e("components/jyf-parser/jyf-parser")]).then(i.bind(null,"217b"))}},s=function(){var t=this,e=t.$createElement,i=(t._self._c,t.attribute.productAttr.length&&(t.attribute.productAttr.length?t.attribute.productAttr[0].attr_values.length:0)>1),o=t.storeInfo.brand_name&&t.storeInfo.brand_name.trim(),s=t.attribute.productAttr.length,r=s?t.skuArr.length:null,n=s&&r>1?t.skuArr.slice(0,4):null,a=s&&r>1?t.skuArr.length:null;t._isMounted||(t.e0=function(e){t.H5ShareBox=!1}),t.$mp.data=Object.assign({},{$root:{g0:i,g1:o,g2:s,g3:r,l0:n,g4:a}})},r=[]},c9da:function(t,e,i){}},[["a634","common/runtime","common/vendor"]]]);