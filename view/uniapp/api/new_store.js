// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from "@/utils/request.js";








/**
 * 获取门店客服详情
 * @param int id
 * 
 */
export function getCustomerInfo(store_id) {
	return request.get('store/customer/info/' + store_id, {},{
		noAuth: true
	});
}


/**
 * 附近门店列表
 * @param int data
 */
export function getList( data) {
	return request.get('store/list',data, {
	noAuth: true
	
	});
}

/**
 * 删除收藏产品
 * @param int id
 * @param string category product=普通产品,product_seckill=秒杀产品
 */
export function collectDel(id, category) {
	return request.post('collect/del', {
		id: id,
		category: category === undefined ? 'product' : category
	});
}

/**
 * 购车添加
 * 
 */
export function postCartAdd(data) {
	return request.post('cart/add', data);
}

/**
 * 获取分类列表
 * 
 */
export function getCategoryList() {
	return request.get('category', {}, {
		noAuth: true
	});
}

/**
 * 获取产品列表
 * @param object data
 */
export function getProductslist(data) {
	return request.get('products', data, {
		noAuth: true
	});
}



/**
 * 获取推荐产品
 * 
 */
export function getProductHot(page, limit) {
	return request.get("product/hot", {
		page: page === undefined ? 1 : page,
		limit: limit === undefined ? 4 : limit
	}, {
		noAuth: true
	});
}
/**
 * 批量收藏
 * 
 * @param object id  产品编号 join(',') 切割成字符串
 * @param string category 
 */
export function collectAll(id, category) {
	return request.post('collect/all', {
		id: id,
		category: category === undefined ? 'product' : category
	});
}

/**
 * 首页产品的轮播图和产品信息
 * @param int type 
 * 
 */
export function getGroomList(type, data) {
	return request.get('groom/list/' + type, data, {
		noAuth: true
	});
}

/**
 * 获取收藏列表
 * @param object data
 */
export function getCollectUserList(data) {
	return request.get('collect/user', data)
}

/**
 * 获取浏览记录列表
 * @param object data
 */
export function getVisitList(data) {
	return request.get('user/visit_list', data)
}

/**
 * 获取浏览记录列表-删除
 * @param object data
 */
export function deleteVisitList(data) {
	return request.delete('user/visit', data)
}

/**
 * 获取产品评论
 * @param int id
 * @param object data
 * 
 */
export function getReplyList(id, data) {
	return request.get('v2/reply/list/' + id, data,{noAuth: true})
}

/**
 * 产品评价数量和好评度
 * @param int id
 */
export function getReplyConfig(id) {
	return request.get('reply/config/' + id,{},{noAuth: true});
}

/**
 * 评论点赞
 * @param int id
 */
export function getReplyPraise(id) {
	return request.post('reply/reply_praise/' + id);
}

/**
 * 取消评论点赞
 * @param int id
 */
export function getUnReplyPraise(id) {
	return request.post('reply/un_reply_praise/' + id);
}

/**
 * 获取评论详情
 * @param int id
 */
export function getReplyInfo(id) {
	return request.get('reply/info/' + id);
}

/**
 * 获取评论回复列表
 * @param int id
 */
export function getReplyComment(id,data) {
	return request.get('reply/comment/' + id,data);
}

/**
 * 评论回复点赞
 * @param int id
 */
export function postReplyPraise(id) {
	return request.post('reply/praise/' + id);
}

/**
 * 取消评论回复点赞
 * @param int id
 */
export function postUnReplyPraise(id) {
	return request.post('reply/un_praise/' + id);
}

/**
 * 保存商品评价回复
 * @param int id
 */
export function replyComment(id,data) {
	return request.post('reply/comment/' + id,data);
}

/**
 * 获取搜索关键字获取
 * 
 */
export function getSearchKeyword() {
	return request.get('search/keyword', {}, {
		noAuth: true
	});
}


/**
 * 获取新人礼信息
 * 
 */
export function newcomerInfo() {
	return request.get('marketing/newcomer/info', {}, {
		noAuth: true
	});
}

/**
 * 新人专享商品
 * 
 */
export function newcomerList(data) {
	return request.get('marketing/newcomer/product_list', data, {
		noAuth: true
	});
}

/**
 * 新人大礼包弹窗
 * 
 */
export function newcomerGift(data) {
	return request.get('marketing/newcomer/gift');
}


