// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { spread } from "@/api/user";
import Cache from "@/utils/cache";

/**
 * 绑定用户授权
 * @param {Object} puid
 */
export function silenceBindingSpread()
{
	
	
	//#ifdef H5
	let puid = Cache.get('spid'),code = 0;
	//#endif
	
	//#ifdef MP || APP-PLUS
	let puid = getApp().globalData.spid,code = getApp().globalData.code;
	//#endif
	
	puid = parseInt(puid);
	if(Number.isNaN(puid)){
		puid = 0;
	}
	if(puid){
		spread({puid,code}).then(res=>{
			//#ifdef H5
			 Cache.clear('spid');
			//#endif
			
			//#ifdef MP || APP-PLUS
			 getApp().globalData.spid = 0;
			 getApp().globalData.code = 0;
			//#endif
			
		}).catch(res=>{
		});
	}
}

export function isWeixin() {
  return navigator.userAgent.toLowerCase().indexOf("micromessenger") !== -1;
}

export function parseQuery() {
  const res = {};

  const query = (location.href.split("?")[1] || "")
    .trim()
    .replace(/^(\?|#|&)/, "");

  if (!query) {
    return res;
  }

  query.split("&").forEach(param => {
    const parts = param.replace(/\+/g, " ").split("=");
    const key = decodeURIComponent(parts.shift());
    const val = parts.length > 0 ? decodeURIComponent(parts.join("=")) : null;

    if (res[key] === undefined) {
      res[key] = val;
    } else if (Array.isArray(res[key])) {
      res[key].push(val);
    } else {
      res[key] = [res[key], val];
    }
  });

  return res;
}

// #ifdef H5
	const VUE_APP_WS_URL = process.env.VUE_APP_WS_URL || `ws://${location.hostname}`;
	export {VUE_APP_WS_URL}
// #endif



export default parseQuery;