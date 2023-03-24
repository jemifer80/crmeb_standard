// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import request from '@/plugins/request';
/**
 *订单-订单列表
 */
export function orderList(data) {
	return request({
		url: `order/list`,
		method: 'get',
		params: data
	});
};

/**
 * @description 获取快递公司
 */
export function getExpressData(status) {
    return request({
        url: `/order/express_list?status=` + status,
        method: 'get'
    })
};

/**
 * @description 发送货提交表单
 * @param {Number} param data.id {Number} 订单id
 * @param {Object} param data.datas {Object} 表单信息
 */
export function putDelivery(data) {
    return request({
        url: `/order/delivery/${data.id}`,
        method: 'put',
        data: data.datas
    });
};

/**
 * @description 拆单发送货
 * @param {Number} param data.id {Number} 订单id
 * @param {Object} param data.datas {Object} 表单信息
 */
export function splitDelivery(data) {
    return request({
        url: `/order/split_delivery/${data.id}`,
        method: 'put',
        data: data.datas
    });
};

/**
 * 电子面单模板
 * @param {com} data 快递公司编号
 */
export function orderExpressTemp(data) {
    return request({
        url: '/order/express/temp',
        method: 'get',
        params: data
    });
}

/**
 * 订单时获取所有配送员列表
 */
export function orderDeliveryList() {
    return request({
        url: '/order/delivery/list',
        method: 'get'
    });
}

/**
 * 电子面单模板
 * @param {com} data 获取电子面单默认信息
 */
export function orderSheetInfo() {
    return request({
        url: '/order/sheet_info',
        method: 'get'
    });
}

/**
 * @description 获取订单可拆分商品列表
 * @param {Object} param data {Object} 传值参数
 */
export function splitCartInfo(id) {
    return request({
        url: `order/split_cart_info/${id}`,
        method: 'get'
    })
};

/**
 * @description 获取主动退款表单数据
 * @param {Number} param id {Number} 订单id
 */
export function getRefundFrom(id) {
    return request({
        url: `/order/refund/${id}`,
        method: 'get'
    });
};

/**
 * @description 获取退积分表单
 * @param {Number} param id {Number} 订单id
 */
export function refundIntegral(id) {
    return request({
        url: `/order/refund_integral/${id}`,
        method: 'get'
    });
};

/**
 * @description 获取不退款表单数据
 * @param {Number} param id {Number} 订单id
 */
export function getnoRefund(id) {
    return request({
        url: `/order/no_refund/${id}`,
        method: 'get'
    });
};

/**
 * @description 订单表单详情数据
 * @param {Number} param id {Number} 订单id
 */
export function getDataInfo(id) {
    return request({
        url: `/order/info/${id}`,
        method: 'get'
    });
};

/**
 * @description 订单物流信息
 * @param {Number} param id {Number} 订单id
 */
export function getExpress(id) {
    return request({
        url: `/order/express/${id}`,
        method: 'get'
    });
};

/**
 * @description 获取订单记录
 * @param {Number} param data.id {Number} 订单id
 * @param {String} param data.datas {String} 分页参数
 */
export function getOrderRecord(data) {
    return request({
        url: `/order/status/${data.id}`,
        method: 'get',
        params: data.datas
    });
};

/**
 * @description 发货记录
 * @param {Object} param data {Object} 传值参数
 */
export function splitOrder(id,data) {
    return request({
        url: 'order/split_order/'+id,
        method: 'get',
        params: data
    })
};

/**
 * @description 订单表单编辑数据
 * @param {Number} param id {Number} 订单id
 */
export function getOrdeDatas(id) {
    return request({
        url: `/order/edit/${id}`,
        method: 'get'
    });
};

/**
 * @description 配送信息表单
 * @param {Number} param id {Number} 订单id
 */
export function getDistribution(id) {
    return request({
        url: `/order/distribution/${id}`,
        method: 'get'
    });
};

/**
 * @description 打印配货单
 * @param {Number} param id {Number} 订单id
 */
export function getDistributionInfo(id) {
    return request({
        url: `/order/distribution_info`,
        method: 'get',
        params:{ids:id}
    });
};

/**
 * @description 订单核销
 */
export function writeUpdate(id) {
    return request({
        url: `/order/write_update/${id}`,
        method: 'put'
    });
}

/**
 * @description 批量发货-手动
 * @param {Object} param data {Object} 传值参数
 */
export function handBatchDelivery(data) {
    return request({
        url: 'order/hand/batch_delivery',
        method: 'get',
        params: data
    })
};

/**
 * @description 下载物流公司对照表
 * @param {Object} param data {Object} 传值参数
 */
export function exportExpressList(id) {
    return request({
        url: 'export/expressList',
        method: 'get'
    })
};

/**
 * @description  订单核销
 * @param {String} param data {String} 核销内容
 */
export function putWrite(data) {
    return request({
        url: '/order/write',
        method: 'post',
        data: data
    });
}

/**
 * @description 订单管理 -- 导出
 */
export function storeOrderApi(data) {
    return request({
        url: `export/storeOrder`,
        method: 'get',
        params: data
    });
}

/**
 * @description 批量发货-自动
 * @param {Object} param data {Object} 传值参数
 */
export function otherBatchDelivery(data) {
    return request({
        url: 'order/other/batch_delivery',
        method: 'post',
        data
    })
};

/**
 * @description 批量发货记录
 * @param {Object} param data {Object} 传值参数
 */
export function queueIndex(data) {
    return request({
        url: 'queue/index',
        method: 'get',
        params: data
    })
};

/**
 * @description 任务列表-查看
 * @param {Object} param data {Object} 传值参数
 */
export function deliveryLog(id, type, data) {
    return request({
        url: `queue/delivery/log/${id}/${type}`,
        method: 'get',
        params: data
    })
};

/**
 * @description 重新执行
 * @param {Object} param data {Object} 传值参数
 */
export function queueAgain(id, type) {
    return request({
        url: `queue/again/do_queue/${id}/${type}`,
        method: 'get'
    })
};

/**
 * @description 清除异常任务
 * @param {Object} param data {Object} 传值参数
 */
export function queueDel(id, type) {
    return request({
        url: `queue/del/wrong_queue/${id}/${type}`,
        method: 'get'
    })
};

/**
 * @description 下载
 * @param {Object} param data {Object} 传值参数
 */
export function batchOrderDelivery(id, type, catchType) {
    return request({
        url: `export/batchOrderDelivery/${id}/${type}/${catchType}`,
        method: 'get'
    })
};

/**
 * @description 停止任务
 * @param {Object} param data {Object} 传值参数
 */
export function stopWrongQueue(id) {
    return request({
        url: `queue/stop/wrong_queue/${id}`,
        method: 'get'
    })
};

/**
 * @description 修改备注信息
 * @param {Number} param data.id {Number} 订单id
 * @param {String} param data.remark {String} 备注信息
 */
export function putRemarkData(data) {
    return request({
        url: `/order/remark/${data.id}`,
        method: 'put',
        data: data.remark
    });
};

/**
 * @description 修改退款订单备注信息
 * @param {Number} param data.id {Number} 订单id
 * @param {String} param data.remark {String} 备注信息
 */
export function putRefundRemarkData(data) {
    return request({
        url: `/refund/remark/${data.id}`,
        method: 'put',
        data: data.remark
    });
};

/**
 * @description 售后订单
 * @param {Object} param data {Object} 传值参数
 */
export function orderRefundList(data) {
    return request({
        url: 'refund/list',
        method: 'get',
        params: data
    })
};


/**
 * @description 订单表单详情数据-退款详情
 * @param {Number} param id {Number} 订单id
 */
export function getRefundDataInfo (id) {
    return request({
        url: `/refund/detail/${id}`,
        method: 'get'
    })
};

/**
 * @description 获取售后退款表单数据
 * @param {Number} param id {Number} 订单id
 */
export function getRefundOrderFrom(id) {
    return request({
        url: `/refund/refund/${id}`,
        method: 'get'
    });
};


