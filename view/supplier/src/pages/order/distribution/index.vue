<template>
    <div style="background-color: #fff">
        <div class="putSupplier" v-for="(item,index) in newArrayData" :key="index">
            <div class="header acea-row row-between-wrapper">
                <div class="left acea-row row-middle">
                    <div class="pictrue" :id="'qrCodeUrl'+index"></div>
                    <div class="info">
                        <div><span class="name">收货人：</span>{{item.real_name}}</div>
                        <div><span class="name">收货地址：</span>{{item.user_address}}</div>
                        <div><span class="name">手机号：</span><span>{{item.user_phone}}</span></div>
                    </div>
                </div>
                <div class="info">
                    <div><span class="name">订单编号：</span>{{item.order_id}}</div>
                    <div><span class="name">支付时间：</span>{{item.pay_time}}</div>
                    <div><span class="name">支付方式：</span>{{item.pay_type_name}}</div>
                </div>
            </div>
            <div class="mt20">
                <Table border :columns="columns" :data="item.goodsList" :disabled-hover="true">
                    <template slot-scope="{ row }" slot="store_name">
                        <div class="line1">{{row.store_name}}</div>
                    </template>
                    <template slot-scope="{ row }" slot="suk">
                        <div class="line1">{{row.suk}}</div>
                    </template>
                </Table>
            </div>
            <div class="bottom acea-row row-between-wrapper">
                <div class="acea-row row-middle">
                    <div class="item"><span class="name">运费：</span>{{item.freight_price}}</div>
                    <div class="item"><span class="name">优惠：</span>{{item.coupon_price}}</div>
                    <div class="item"><span class="name">会员折扣：</span>{{item.vip_true_price}}</div>
                    <div class="item"><span class="name">积分抵扣：</span>{{item.use_integral}}</div>
                </div>
                <div class="pricePay">实付金额：{{item.pay_price}}</div>
            </div>
            <div class="bottom acea-row">
                <div class="name">用户备注：<span class="con">{{item.mark || '-'}}</span></div>
            </div>
            <div v-if="data.site_name || data.refund_phone || data.refund_address" class="delivery">
              <div v-if="data.site_name">店铺信息：{{ data.site_name }}</div>
              <div v-if="data.refund_address">地址：{{ data.refund_address }}</div>
              <div v-if="data.refund_phone">联系方式：{{ data.refund_phone }}</div>
            </div>
        </div>
    </div>
</template>
<script>
    import { getDistributionInfo } from "@/api/order";
    import QRCode from 'qrcodejs2';
    import Setting from '@/setting';
    export default {
        data (){
            return {
                columns:[
                    {
                        title: '商品编号',
                        key: 'index',
                        align: 'center',
                        width: 60
                    },
                    {
                        title: '商品名称',
                        slot: 'store_name',
                        align: 'center',
                        width: 253
                    },
                    {
                        title: '商品规格',
                        slot: 'suk',
                        align: 'center',
                        width: 219
                    },
                    // {
                    //     title: '商品条码',
                    //     key: 'bar_code',
                    //     align: 'center',
                    //     width: 109
                    // },
                    // {
                    //     title: '商品编码',
                    //     key: 'code',
                    //     align: 'center',
                    //     width: 109
                    // },
                    {
                        title: '单价',
                        key: 'truePrice',
                        align: 'center',
                        width: 100
                    },
                    {
                        title: '数量',
                        key: 'cart_num',
                        align: 'center',
                        width: 60
                    },
                    {
                        title: '金额',
                        key: 'subtotal',
                        align: 'center',
                        width: 100
                    },
                ],
                data:{},
                goods:[],
                BaseURL: Setting.apiBaseURL.replace(/supplierapi/, ""),
                newArrayData:[]
            }
        },
        created() {
            this.getDistribution();
        },
        mounted() {
            this.$nextTick(function () {
                let that = this;
                setTimeout(function () {
                    that.creatQrCode();
                },200)
            })
        },
        methods:{
            // 生成二维码
            creatQrCode() {
                let url= this.BaseURL;
                let qrcode = '';
                this.newArrayData.forEach((item,index)=>{
                    let obj = document.getElementById('qrCodeUrl'+index);
                    qrcode = new QRCode(obj, {
                        text: url, // 需要转换为二维码的内容
                        width: 90,
                        height: 90,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    })
                });

            },
            getDistribution(){
                getDistributionInfo(this.$route.query.id).then(res=>{
                    this.data = res.data;
                    this.goods = res.data.list
                    let data = res.data;
                    let newArray = [];
                    data.list.forEach((i,indexi)=>{
                        let listArray = [];
                        let goods = i.list;
                        let count = Math.ceil(goods.length / 7);
                        for(let j = 0; j < count; j++){
                            let list = goods.slice(j * 7, j * 7 + 7);
                            if (list.length){
                                listArray.push(list)
                            }
                        }
                        let num = 7 - listArray[listArray.length-1].length;
                        if(num){
                            for(let z = 0; z < num; z++){
                                listArray[listArray.length-1].push({
                                    cart_num:'',
                                    index:'',
                                    store_name:'',
                                    subtotal:'',
                                    suk:'',
                                    truePrice:''
                                })
                            }
                        }
                        listArray.forEach((x)=>{
                            newArray.push({
                                real_name : i.real_name,
                                user_address : i.user_address,
                                user_phone : i.user_phone,
                                freight_price : i.freight_price,
                                coupon_price : i.coupon_price,
                                vip_true_price : i.vip_true_price,
                                use_integral : i.use_integral,
                                pay_price : i.pay_price,
                                mark : i.mark,
                                order_id: i.order_id,
                                pay_time: i.pay_time,
                                pay_type_name: i.pay_type_name,
                                goodsList : x
                            });
                        })
                    })
                    this.newArrayData = newArray;
                }).catch(err=>{
                    this.$Message.error(err.msg)
                })
            }
        }
    }

</script>
<style lang="less" scoped>
    /*@media print {*/
        /*@page {*/
            /*size: 235mm 170mm;*/
        /*}*/
    /*}*/
    /deep/.ivu-table-header thead tr th:nth-of-type(1){
        padding-left:0!important;
    }
    /deep/.ivu-table td:nth-of-type(1){
        padding-left:0!important;
    }
    /deep/.ivu-table-header table{
        border-top:0!important;
    }
    /deep/.ivu-table-border th, /deep/.ivu-table-border td{
        border-right: 1px solid #333!important;
    }
    /deep/.ivu-table th, /deep/.ivu-table td{
        border-bottom: 1px solid #333!important;
    }
    /deep/.ivu-table-wrapper-with-border{
        border-color: #333!important;
    }
    /deep/.ivu-table-border:after{
        background-color: #333;
    }
    /deep/.ivu-table{
        color: #000;
    }
    .pricePay{
        font-weight: bold;
    }
    .bottom{
        color: rgba(0, 0, 0, 0.85);
        font-size: 12px;
        font-weight: 400;
        margin-top: 10px;
        .item{
            margin-right: 30px;
        }
        .name{
            font-weight: 600;
        }
        .con{
            width: 740px;
            font-weight: unset;
        }
    }
    .putSupplier{
        width: 794px;
        background-color: #fff;
        margin: 20px auto 0;
        padding-bottom: 20px;
        /*padding: 20px 20px 450px 20px;*/
        /*box-sizing: border-box;*/
        .header{
            .info{
                font-size: 12px;
                color: rgba(0, 0, 0, 0.85);
                .name{
                    font-weight: 600;
                }
                div~div{
                    margin-top: 10px;
                }
            }
            .left{
                width: 500px;
                .pictrue{
                    width: 90px;
                    height: 90px;
                    margin-right: 20px;
                    img{
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
    }
    .delivery {
      display: flex;
      justify-content: center;
      width: 794px;
      padding-top: 20px;
      border-top: 1px solid #DDDDDD;
      margin: 15px auto;
      font-size: 10px;
      color: #333333;

      div + div {
        margin-left: 30px;
      }
    }
</style>