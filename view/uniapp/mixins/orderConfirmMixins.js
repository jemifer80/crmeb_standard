// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

export default {
	data() {
		return { 
			config: null, 
			userConfirmPayment: null, 
			timer: 0, 
			valiSubmittedState: { 
				disabled: !0, 
				reason: '', 
				state: { 
					ready: !1, 
					orderPaymentLimit: null, 
					allowOrderTime: null, 
				} 
			},
		};
	}, 
	beforeMount() { 
		this.initTimer(); 
	}, 
	beforeDestroy() { 
		this.stopTimer(); 
	}, 
	methods: { 
		initTimer() { 
			this.timer = setInterval(() => this.changeDisabledSubmitted(), 1000); 
		}, 
		stopTimer() { 
			clearInterval(this.timer); 
		}, 
		changeDisabledSubmitted() { 
			let ready = !!this.config && this.userConfirmPayment !== null; 
			let flag = ready; 
			 
			this.valiSubmittedState.state.ready = ready; 
			if (flag) { 
				const { order_payment_limit, allow_order_time } = this.config; 
				 
				flag = this.checkOrderTime(allow_order_time); 
				if (flag) { 
					flag = this.checkOrderPaymentLimit(order_payment_limit); 
					this.valiSubmittedState.state.orderPaymentLimit = flag; 
				} 
			} 
			 
			this.valiSubmittedState.disabled = !flag; 
		}, 
		checkOrderPaymentLimit(orderPaymentLimit) { 
			const flag = !orderPaymentLimit || Number(orderPaymentLimit) === 0 || this.userConfirmPayment >= orderPaymentLimit; 
			 
			this.valiSubmittedState.state.orderPaymentLimit = flag; 
			if (!flag) { 
				this.valiSubmittedState.reason = this.formatDisabledOrderPaymentLimitReason(orderPaymentLimit); 
			} 
			 
			return flag 
		}, 
		checkOrderTime(allowOrderTime) { 
			const date = new Date; 
			const currentTime = Number([ date.getHours(), date.getMinutes(), date.getSeconds() ].map(e => e < 10 ? '0' + e : e).join('')); 
			 
			let flag = !0; 
			if (allowOrderTime) { 
				flag = allowOrderTime.every((e, i) => { 
					if (!e) { 
						return !0; 
					} 
					 
					const _time = Number(e.split(':').join('')); 
					const _flagArr = [ _time <= currentTime, _time >= currentTime ]; 
					 
					return _flagArr[i]; 
				}) 
			} 
			 
			this.valiSubmittedState.state.allowOrderTime = flag; 
			if (!flag) { 
				this.valiSubmittedState.reason = this.formatDisabledAllowOrderTimeReason(allowOrderTime); 
			} 
			 
			return flag; 
		}, 
		formatDisabledOrderPaymentLimitReason(orderPaymentLimit) { 
			return `下单金额需要超过起送价 ¥${orderPaymentLimit}`; 
		}, 
		formatDisabledAllowOrderTimeReason(allowOrderTime) { 
			const prev = '下单时间需要在当天'; 
			let body = ''; 
			 
			if (allowOrderTime[0] === '00:00:00') { 
				allowOrderTime[0] = null; 
			} 
			 
			const labels = allowOrderTime.map(e => { 
				if (e) { 
					const _e = e.split(':').reverse().flatMap(e => { 
						if (e === '00') { 
							return []; 
						} 
						 
						return [ e ]; 
					}).reverse(); 
					 
					e = _e.join(':'); 
					 
					if (_e.length === 1) { 
						e = Number(e) + '点'; 
					} 
				} 
				 
				return e 
			}) 
			 
			if (labels[0] && labels[1]) { 
				body = `${labels[0]}~${labels[1]}期间内下单`; 
			} else if (labels[0]) { 
				body = `${labels[0]}之后下单`; 
			} else if (labels[1]) { 
				body = `${labels[1]}之前下单`; 
			} 
			 
			return `${prev}${body}`; 
		}, 
	}
};
