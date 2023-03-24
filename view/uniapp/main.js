// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import Vue from 'vue'
import App from './App'
import store from './store'
import Cache from './utils/cache'
import util from 'utils/util'
import configs from './config/app.js'
import socket from './libs/new_chat.js'
Vue.prototype.$util = util;
Vue.prototype.$config = configs;
Vue.prototype.$Cache = Cache;
Vue.prototype.$eventHub = new Vue();
Vue.prototype.$socket = new socket();
Vue.config.productionTip = false
import skeleton from './components/skeleton/index.vue'

Vue.component('skeleton', skeleton)
// #ifdef MP
import authorize from './components/authorize/index.vue'
Vue.component('authorize', authorize)
// #endif
// #ifdef H5


import { parseQuery } from "./utils";
import Auth from './libs/wechat';
import { SPREAD } from './config/cache';
Vue.prototype.$wechat = Auth;



let cookieName = "VCONSOLE",
	query = parseQuery(),
	urlSpread = query["spid"],
	vconsole = query[cookieName.toLowerCase()],
	md5Crmeb = "b14d1e9baeced9bb7525ab19ee35f2d2", //CRMEB MD5 加密开启vconsole模式
	md5UnCrmeb = "3dca2162c4e101b7656793a1af20295c"; //UN_CREMB MD5 加密关闭vconsole模式

if (urlSpread !== undefined) {
	var spid = Cache.get(SPREAD);
	urlSpread = parseInt(urlSpread);
	if (!Number.isNaN(urlSpread) && spid !== urlSpread) {
		Cache.set("spid", urlSpread || 0);
	} else if (spid === 0 || typeof spid !== "number") {
		Cache.set("spid", urlSpread || 0);
	}
}

if (vconsole !== undefined) {
  if (vconsole === md5UnCrmeb && Cache.has(cookieName))
	  Cache.clear(cookieName);
} else vconsole = Cache.get(cookieName);

import VConsole from './pages/extension/components/vconsole.min.js'

if (vconsole !== undefined && vconsole === md5Crmeb) {
	Cache.set(cookieName, md5Crmeb, 3600);
	let vConsole = new VConsole();
}

// let snsapiBase = 'snsapi_base';
// Auth.isWeixin() && Auth.oAuth(snsapiBase);


//全局路由前置守卫
// #endif

App.mpType = 'app'


const app = new Vue({
    ...App,
	store,
	Cache,
})
app.$mount();