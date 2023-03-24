<template>
    <div class="box" v-resize="handleResize">
		<Card :bordered="false" dis-hover class="ivu-mt">
			<Form
			  ref="formValidate"
			  :model="formValidate"
			  :label-width="labelWidth"
			  :label-position="labelPosition"
			  @submit.native.prevent
			>
				<FormItem label="时间筛选：">
					<DatePicker
					   :editable="false"
					   @on-change="onchangeTime"
					   :value="timeVal"
					   format="yyyy/MM/dd"
					   type="daterange"
					   placement="bottom-start"
					   placeholder="自定义时间"
					   style="width: 250px"
					   :options="options"
					></DatePicker>
				</FormItem>
			</Form>
		</Card>
		<cards-data :cardLists="cardLists"></cards-data>
		<Row :gutter="24"  class="ivu-mt Box">
			<Col  :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
				<div class="fonts">
					<div class="name">营业趋势</div>
				</div>
				<echarts-new ref="visitChart" :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
			</Col>
		</Row>
		<Row :gutter="24"  class="ivu-mt">
			<Col :xl="12" :lg="12" :md="24" :sm="24" :xs="24">
				<Card :bordered="false" dis-hover>
					<div class="acea-row row-between-wrapper fonts">
						<div class="name">订单来源统计</div>
						<RadioGroup v-model="orderNum" type="button">
							<Radio :label="0">
								<span class="iconfont icontongji"></span>
							</Radio>
							<Radio :label="1">
								<span class="iconfont iconbiaoge1"></span>
							</Radio>
						</RadioGroup>
					</div>
					<Table ref="selection" :columns="columnsOrder" :data="dataOrder" :loading="loading" height="400"
						   no-data-text="暂无数据" highlight-row
						   no-filtered-data-text="暂无筛选结果" v-if="orderNum">
						<template slot-scope="{ row, index }" slot="percent">
							<div style="width: 90%">
								<Progress :percent="row.percent" :stroke-width="5">
									<span style='color:#808695;'>{{row.percent}}%</span>
								</Progress>
							</div>
						</template>
					</Table>
					<echarts-new :option-data="circularData" :styles="style" height="100%" width="100%" v-if="circularData && !orderNum"></echarts-new>
				</Card>
			</Col>
		    <Col :xl="12" :lg="12" :md="24" :sm="24" :xs="24">
		        <Card :bordered="false" dis-hover>
					<div class="acea-row row-between-wrapper fonts">
						<div class="name">订单类型分析</div>
						<RadioGroup v-model="typeNum" type="button">
							<Radio :label="0">
								<span class="iconfont icontongji"></span>
							</Radio>
							<Radio :label="1">
								<span class="iconfont iconbiaoge1"></span>
							</Radio>
						</RadioGroup>
					</div>
					<Table ref="selection" :columns="columnsType" :data="dataType" :loading="loading" height="400"
					       no-data-text="暂无数据" highlight-row
					       no-filtered-data-text="暂无筛选结果" v-if="typeNum">
						   <template slot-scope="{ row, index }" slot="percent">
							   <div style="width: 90%">
								   <Progress :percent="row.percent" :stroke-width="5">
									   <span style='color:#808695;'>{{row.percent}}%</span>
								   </Progress>
							   </div>
						   </template>
					</Table>
					<echarts-new :option-data="circularDataType" :styles="style" height="100%" width="100%" v-if="circularDataType && !typeNum"></echarts-new>
		        </Card>
		    </Col>
		</Row>
	</div>
      
</template>

<script>
	import { mapState } from 'vuex'
	import { headerApi, orderChannel, orderType, operateApi } from '@/api/index'
	import echartsNew from '@/components/echartsNew/index'
	import cardsData from "@/components/cards/cards";
	import timeOptions from "@/utils/timeOptions";
	import { formatDate } from '@/utils/validate';
	export default {
		name: "home",
		components: { cardsData, echartsNew },
		data() {
			return{
				loading:false,
				optionData:{},
				circularData:{},
				circularDataType:{},
				dataType:[],
				dataOrder:[],
				typeNum:1,
				orderNum:0,
				columnsType:[
					{
						title: '序号',
						key: 'index',
						minWidth: 100
					},
					{
						title: '类型',
						key: 'name',
						minWidth: 100
					},
					{
						title: '金额',
						key: 'value',
						minWidth: 100
					},
					{
						title: '占比率',
						slot: 'percent',
						minWidth: 100
					},
				],
				columnsOrder:[
					{
						title: '序号',
						key: 'index',
						minWidth: 100
					},
					{
						title: '来源',
						key: 'name',
						minWidth: 100
					},
					{
						title: '金额',
						key: 'value',
						minWidth: 100
					},
					{
						title: '占比率',
						slot: 'percent',
						minWidth: 100
					},
				],
				formValidate: {
				  data: ""
				},
				style: { height: '400px' },
				options: timeOptions,
				timeVal: [],
				cardLists: [],
				infoList:{}
			}
		},
		computed: {
			...mapState('store/layout', [
			    'isMobile'
			]),
		  labelWidth() {
		    return this.isMobile ? undefined : 80;
		  },
		  labelPosition() {
		    return this.isMobile ? "top" : "left";
		  },
		},
		created(){
			const end = new Date()
			const start = new Date()
			start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
			this.timeVal = [start, end]
			this.formValidate.data = formatDate(start, 'yyyy/MM/dd')+ '-'+ formatDate(end, 'yyyy/MM/dd');
		},
		mounted() {
			this.$nextTick(function(){
				this.cardList()
				this.trends()
				this.circularChart();
				this.getOrderType();
			})
		},
		methods:{
			getOrderType(){
				orderType({data:this.formValidate.data}).then(res=>{
					let data = res.data.list,bingData = res.data.bing_data;
					data.forEach((item,index)=>{
						item.index = index+1
					});
					this.dataType = data;
					this.circularDataType = {
						tooltip: {
							trigger: 'item',
							formatter: '{a} <br/>{b} : {c} ({d}%)'
						},
						legend: {
							orient: 'vertical',
							left: 'left'
						},
						series: [
							{
								name: '订单类型分析',
								type: 'pie',
								radius: '50%',
								center: ['50%', '50%'],
								data: bingData,
								emphasis: {
									itemStyle: {
										shadowBlur: 10,
										shadowOffsetX: 0,
										shadowColor: 'rgba(0, 0, 0, 0.5)'
									}
								}
							}
						]
					}
				}).catch(err=>{
					this.$Message.error(err.msg)
				})
			},
			circularChart(){
				orderChannel({data:this.formValidate.data}).then(res=>{
					let data = res.data.bing_data,list = res.data.list;
					list.forEach((item,index)=>{
						item.index = index+1
					});
					this.dataOrder = list;
					this.circularData = {
						tooltip: {
							trigger: 'item',
							formatter: '{a} <br/>{b} : {c} ({d}%)'
						},
						legend: {
							orient: 'vertical',
							left: 'left'
						},
						series: [
							{
								name: '订单来源统计',
								type: 'pie',
								radius: '50%',
								center: ['50%', '50%'],
								data: data,
								emphasis: {
									itemStyle: {
										shadowBlur: 10,
										shadowOffsetX: 0,
										shadowColor: 'rgba(0, 0, 0, 0.5)'
									}
								}
							}
						]
					}
				}).catch(err=>{
					this.$Message.error(err.msg)
				})
			},
			trends(){
				operateApi({data:this.formValidate.data}).then(async res => {
					const cardLists = res.data;
					this.infoList = cardLists;
					this.get(cardLists);
			    }).catch(res => {
			        this.$Message.error(res.msg)
			    })
			},
			get(extract){
				let legend = extract.series.map(item => {
					return item.name
				});
				let col = ['#5B8FF9', '#5AD8A6', '#FFAB2B', '#F5222D'];
				extract.series.forEach((item,index)=>{
					let style = {
						normal: {
							color: col[index]
						}
					}
					item.itemStyle = style
					item.smooth = true
				});
				this.optionData = {
					tooltip: {
						trigger: 'axis',
						axisPointer: {
							type: 'cross',
							label: {
								backgroundColor: '#6a7985'
							}
						}
					},
					legend: {
						x:'center',
						data: legend
					},
					grid: {
						left: '3%',
						right: '4%',
						bottom: '3%',
						containLabel: true
					},
					toolbox: {
						show: true,
						right: '2%',
						feature: {
							saveAsImage: {
								name: '营业趋势_'+formatDate(new Date(Number(new Date().getTime())), 'yyyyMMddhhmmss')
							}
						}
					},
					xAxis: {
						type: 'category',
						boundaryGap: true,
						axisLabel: {
							interval: 0,
							rotate: 40,
							textStyle: {
								color: '#000000'
							}
						},
						data: extract.xAxis
					},
					yAxis: {
						type: 'value',
						axisLine: {
							show: false
						},
						axisTick: {
							show: false
						},
						axisLabel: {
							textStyle: {
								color: '#7F8B9C'
							}
						},
						splitLine: {
							show: true,
							lineStyle: {
								color: '#F5F7F9'
							}
						}
					},
					series: extract.series
				}
			},
			// 具体日期
			onchangeTime(e) {
			  this.timeVal = e;
			  this.formValidate.data = this.timeVal[0] ? this.timeVal.join("-") : "";
			  this.trends()
			  this.cardList()
			  this.getOrderType();
			  this.circularChart();
			},
			cardList(){
				headerApi({data:this.formValidate.data}).then(res=>{
					this.cardLists = [
					  {
					    col: 6,
					    count: res.data.pay_price,
					    name: "订单金额",
					    className: "icondingdanjine",
						type:1
					  },
					  {
					    col: 6,
					    count: res.data.pay_count,
					    name: "订单量",
					    className: "icondingdanliang",
						type:1
					  },
					  {
					    col: 6,
					    count: res.data.refund_price,
					    name: "退款金额",
					    className: "icontuikuanjine",
						type:1
					  },
					  {
						col: 6,
						count: res.data.refund_count,
						name: "退款订单数",
						className: "icontuikuandingdanliang",
						type:1
					  }
					]
					
				})
			},
			// 监听页面宽度变化，刷新表格
			handleResize () {
				this.$refs.visitChart.wsFunc();
			}
		}
	}
</script>

<style scoped lang="less">
	/deep/.ivu-progress-success .ivu-progress-bg{
		background-color: #2d8cf0!important;
	}
	.ivu-radio-group-button .ivu-radio-wrapper{
		color: #999999;
	}
	.ivu-radio-group-button .ivu-radio-wrapper-checked{
		color: #2d8cf0;
	}
	/deep/.ivu-mb{
		margin-bottom: 0!important;
	}
	.ivu-form-item{
		margin-bottom: 0;
	}
	.Box{background-color: #FFFFFF;margin-left: 0px !important;margin-right: 0px !important;padding-top: 20px;}
	.fonts{
		margin-bottom: 18px;margin-top: 2px;
		.name{
			font-weight: bold;
			font-size: 16px;
			color: #000;
			position: relative;
			padding-left: 10px;
			&:before{
				content: ' ';
				position: absolute;
				width: 2px;
				height: 17px;
				background-color: #2d8cf0;
				left: 0;
				top: 3px;
			}
		}
	}
</style>
