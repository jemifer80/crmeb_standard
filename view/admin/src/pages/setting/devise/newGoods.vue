<template>
  <div class="users">
    <Card :bordered="false" dis-hover>
      <div class="acea-row row-top">
        <div class="left" :style="colorStyle">
          <!-- 商品图片 -->
          <img src="../../../assets/images/goods.png" alt="" class="img" />
          <!-- 商品详情 -->
          <div
            class="details public dotted"
            :class="current == 1 ? 'solid' : ''"
            @click="currentShow(1)"
          >
            <div class="details-one">
              <div class="details-add">
                <span class="lable">￥</span>
                <span class="price">199.00</span>
              </div>
              <div class="details-add" v-show="isShowDataItem(1)">
                <span class="num">￥99.00</span>
                <img
                        src="../../../assets/images/svip-goods.png"
                        alt=""
                        class="vip"
                />
              </div>

              <div class="details-add" v-show="isShowDataItem(2)">
                <span class="num">￥100.00</span>
                <span>
                  <img
                    src="../../../assets/images/vip-goods.png"
                    alt=""
                    class="vip"
                  />
                </span>
              </div>
              <!-- 分享 -->
              <span
                class="iconfont iconfenxiang"
                v-show="optionstList.is_share"
              ></span>
            </div>
            <div class="details-two" v-show="optionstList.is_name">
              <span class="banrd" v-show="optionstList.is_brand">品牌</span>
              <span class="title">
                全棉针织条纹四件套新款上市性价比高
              </span>
            </div>
            <div class="details-three" v-show="optionstList.is_ot_price || optionstList.is_sales || optionstList.is_stock">
              <span class="mr15" v-show="optionstList.is_ot_price">原价:￥234.00</span>
              <span class="mr15" v-show="optionstList.is_sales">累计销量:2999999件</span>
              <span v-show="optionstList.is_stock">库存:1452件</span>
            </div>
            <div class="open">
              <img
                src="../../../assets/images/open-svip.png"
                alt=""
                class="img"
              />
            </div>
          </div>
          <!-- 优惠 -->
          <div
            class="discount public on dotted"
            :class="current == 2 ? 'solid' : ''"
            @click="currentShow(2)"
          >
            <div class="tipInfo acea-row row-center-wrapper" v-if="!optionstList.is_coupon && !optionstList.is_activity && !optionstList.is_promotions">暂无营销活动<span class="info">（此版块不展示）</span></div>
            <div class="list" v-show="optionstList.is_coupon">
              <span class="name">优惠券:</span>
              <span class="box">
                满100减30
                <span class="yuan" />
                <span class="yuan2" />
              </span>
              <span class="box">
                满100享9折
                <span class="yuan" />
                <span class="yuan2" />
              </span>
              <span class="iconfont iconjinru"></span>
            </div>
            <div class="activity" :class="!optionstList.is_coupon?'on':''" v-show="optionstList.is_activity">
              <span class="name on">活动:</span>
              <span class="list">
                <span class="iconfont iconpintuan3"></span>
                参与拼团
              </span>
              <span class="list">
                <span class="iconfont iconkanjia2"></span>
                参与砍价
              </span>
              <span class="list">
                <span class="iconfont iconmiaosha"></span>
                参与秒杀
              </span>
            </div>
            <div class="count" :class="!optionstList.is_coupon && !optionstList.is_activity?'on':''" v-show="optionstList.is_promotions">
              <span class="name">优惠:</span>
              <span class="minus">满减</span>
              <span class="text">满199减20，满500减50，满2…</span>
              <span class="iconfont iconjinru"></span>
            </div>
          </div>
          <!-- 规格尺寸 -->
          <div
            class="specifications on public dotted"
            :class="current == 3 ? 'solid' : ''"
            @click="currentShow(3)"
          >
            <div class="tipInfo acea-row row-center-wrapper" v-if="!optionstList.is_sku && !optionstList.is_specs && !optionstList.is_ensure && !optionstList.is_store">暂无商品属性<span class="info">（此版块不展示）</span></div>
            <div v-show="optionstList.is_sku">
              <div class="choice">
                <span class="name">请选择:</span>
                <span class="text">大小/尺寸</span>
                <span class="iconfont iconjinru"></span>
              </div>
              <div class="picture" v-show="optionstList.sku_style == 2">
                <img src="../../../assets/images/img.png" alt="" class="img" />
                <img src="../../../assets/images/img.png" alt="" class="img" />
                <img src="../../../assets/images/img.png" alt="" class="img" />
                <div class="choose-color">共5种颜色可选</div>
              </div>
            </div>
            <div class="parameter" :class="!optionstList.is_sku?'on':''" v-show="optionstList.is_specs">
              <span class="name">参数:</span>
              <span class="text">型号 产地…</span>
              <span class="iconfont iconjinru"></span>
            </div>
            <div class="guarantee" :class="!optionstList.is_sku && !optionstList.is_specs?'on':''" v-show="optionstList.is_ensure">
              <span class="name">保障:</span>
              <span class="text">假一赔十 · 全国包邮 · 七天无理由退货…</span>
              <span class="iconfont iconjinru"></span>
            </div>
          </div>
          <!-- 评价 -->
          <div
            class="evaluate dotted"
            :class="current == 4 ? 'solid' : ''"
            @click="currentShow(4)"
          >
            <img src="../../../assets/images/evaluate.png" alt="" class="img" />
          </div>
          <!-- 搭配购 -->
          <div
            class="collocation dotted"
            :class="current == 5 ? 'solid' : ''"
            @click="currentShow(5)"
          >
            <img
              src="../../../assets/images/collocation.png"
              alt=""
              class="img"
            />
          </div>
          <!-- 优品推荐 -->
          <div
            class="recommend dotted"
            :class="current == 6 ? 'solid' : ''"
            @click="currentShow(6)"
          >
            <img
              src="../../../assets/images/recommend.png"
              alt=""
              class="img"
            />
          </div>
          <!-- 产品介绍 -->
          <div
            class="product dotted"
            :class="current == 7 ? 'solid' : ''"
            @click="currentShow(7)"
          >
            <img src="../../../assets/images/product.png" alt="" class="img" />
          </div>
          <!-- 购物车 -->
          <!--<div class="shopping-cart">-->
            <!--<div class="kefu">-->
              <!--<span class="iconfont iconkefu2"></span>-->
              <!--<span>客服</span>-->
            <!--</div>-->
            <!--<div class="kefu">-->
              <!--<span class="iconfont icona-shoucang"></span>-->
              <!--<span>收藏</span>-->
            <!--</div>-->
            <!--<div class="kefu">-->
              <!--<span class="iconfont icongouwuche"></span>-->
              <!--<span>购物车</span>-->
            <!--</div>-->

            <!--<div class="btn">-->
              <!--<div class="join">加入购物车</div>-->
              <!--<div class="join join-add">立即购买</div>-->
            <!--</div>-->
          <!--</div>-->
        </div>

        <!-- 操作 -->
        <div class="right">
          <!-- 价格 -->
          <div class="title">商品详情设置</div>
          <div class="c_row-item">
            <Col class="label" span="4">评价开关：</Col>
            <Col span="20" class="slider-box">
              <Switch v-model="optionstList.is_reply" />
            </Col>
          </div>
          <div class="c_row-item">
            <Col class="label" span="4">搭配购开关：</Col>
            <Col span="20" class="slider-box">
              <Switch
                      v-model="optionstList.is_discounts"
              />
            </Col>
          </div>
          <div class="c_row-item">
            <Col class="label" span="4">优品推荐：</Col>
            <Col span="20" class="slider-box">
              <Switch
                      v-model="optionstList.is_recommend"
              />
            </Col>
          </div>
          <div class="c_row-item">
            <Col class="label" span="4">产品介绍：</Col>
            <Col span="20" class="slider-box">
              <Switch
                      v-model="optionstList.is_description"
              />
            </Col>
          </div>
          <div class="line"></div>
          <div v-if="current==1">
            <div class="c_row-item">
              <Col class="label" span="4">展示价格：</Col>
              <Col span="20" class="slider-box">
                <!-- <RadioGroup>
                  <Radio :label="1">
                    <Icon></Icon>
                    <span>svip价</span>
                  </Radio>
                  <Radio :label="2">
                    <Icon></Icon>
                    <span>会员价</span>
                  </Radio>
                </RadioGroup> -->
                <CheckboxGroup v-model="optionstList.price_type">
                  <Checkbox label="1">付费会员</Checkbox>
                  <Checkbox label="2">等级会员</Checkbox>
                </CheckboxGroup>
              </Col>
            </div>
            <div class="tips">两个价格都选中时，移动端显示其中的最低价</div>
            <div class="c_row-item">
              <Col class="label" span="4">是否开启：</Col>
              <Col span="20" class="slider-box">
                <Checkbox v-model="optionstList.is_share">分享</Checkbox>
                <Checkbox v-model="optionstList.is_name">商品名称</Checkbox>
                <Checkbox v-model="optionstList.is_brand">品牌</Checkbox>
                <Checkbox v-model="optionstList.is_ot_price">原价</Checkbox>
                <Checkbox v-model="optionstList.is_sales">累计销量</Checkbox>
                <Checkbox v-model="optionstList.is_stock">库存</Checkbox>
              </Col>
            </div>
            <div class="line"></div>
          </div>
          <!-- 优惠券 -->
          <div v-if="current == 2">
            <div class="c_row-item">
              <Col class="label" span="4">营销活动：</Col>
              <Col span="20" class="slider-box">
                <Checkbox v-model="optionstList.is_coupon">优惠券</Checkbox>
                <Checkbox v-model="optionstList.is_activity">活动</Checkbox>
                <Checkbox v-model="optionstList.is_promotions">
                  优惠活动
                </Checkbox>
              </Col>
            </div>
            <div class="line"></div>
          </div>

          <!-- 商品规格 -->
          <div v-if="current == 3">
            <div class="c_row-item">
              <Col class="label" span="4">商品属性：</Col>
              <Col span="20" class="slider-box">
                <Checkbox v-model="optionstList.is_specs">参数</Checkbox>
                <Checkbox v-model="optionstList.is_ensure">服务保障</Checkbox>
                <Checkbox v-model="optionstList.is_sku">规格选择</Checkbox>
              </Col>
            </div>
            <div class="c_row-item">
              <Col class="label" span="4">规格样式：</Col>
              <Col span="20" class="slider-box">
                <RadioGroup v-model="optionstList.sku_style">
                  <Radio :label="1">
                    <Icon></Icon>
                    <span>样式1</span>
                  </Radio>
                  <Radio :label="2">
                    <Icon></Icon>
                    <span>样式2</span>
                  </Radio>
                </RadioGroup>
              </Col>
            </div>
            <div class="line"></div>
          </div>
          <!-- 评论 -->
          <div v-if="current == 4">
            <div class="c_row-item">
              <Col class="label" span="4">展示数量：</Col>
              <Col span="20" class="slider-box">
                <InputNumber style="width: 460px;" :max="3" :min="1" v-model="optionstList.reply_num" placeholder="请输入评论数量..." />
              </Col>
            </div>
            <div class="c_row-item add">
              <Col class="label" span="4"></Col>
              <Col span="20" class="slider-box">
                <span class="text">若不填写，默认为1个</span>
              </Col>
            </div>
            <div class="line"></div>
          </div>
          <!-- 搭配 -->
          <div v-if="current == 5">
            <div class="c_row-item">
              <Col class="label" span="4">展示数量:</Col>
              <Col span="20" class="slider-box">
                <InputNumber style="width: 460px;" :max="3" :min="1" v-model="optionstList.discounts_num" placeholder="请输入数量..." />
              </Col>
            </div>
            <div class="c_row-item add">
              <Col class="label" span="4"></Col>
              <Col span="20" class="slider-box">
                <span class="text">建议展示1-3个，若不填写默认展示3个</span>
              </Col>
            </div>
            <div class="line"></div>
          </div>
          <!-- 优品推荐 -->
          <div v-if="current == 6">
            <div class="c_row-item">
              <Col class="label" span="4">展示数量:</Col>
              <Col span="20" class="slider-box">
                <InputNumber style="width: 460px;" :min="1" v-model="optionstList.recommend_num" placeholder="请输入数量..." />
              </Col>
            </div>
            <div class="c_row-item add">
              <Col class="label" span="4"></Col>
              <Col span="20" class="slider-box">
                <span class="text">
                  建议根据商品样式调整展示商品数量，避免出现空缺
                </span>
              </Col>
            </div>
            <div class="line"></div>
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script>
import uploadPic from './components/uploadPic'
import {
  getMember,
  memberSave,
  getProductDetail,
  saveProductDetail,
} from '@/api/diy'
export default {
  name: 'users',
  components: {
    uploadPic,
  },
  props: {},
  data() {
    return {
      // optionstList: {},
      // [{}, {}],
      // { },
      // [ is_type: false, is_xxx: false ]
      optionstList: {
        price_type: 1, //展示1:svip价2：会员价
        is_share: true, //分享
        is_name: true, //商品名称
        is_brand: true, //品牌
        is_ot_price: false, //原价
        is_sales: true, //销量
        is_stock: true, //库存
        is_coupon: true, //优惠券
        is_activity: true, //活动
        is_promotions: true, //优惠活动
        is_specs: true, //参数
        is_ensure: true, //保障服务
        is_sku: true, //规格选择
        sku_style: 1, // 规格样式 1 2
        is_store: true, //门店
        is_reply: true, //是否站评论
        reply_num: 1, //评论数量
        is_discounts: true, //是否展示搭配购
        discounts_num: 3, //搭配购数量
        is_recommend: true, //是否展示优品推荐
        recommend_num: 6, //优惠推荐数量
        is_description: true, //是否展示商品详情
      },
      price: [], // 价格
      value: '',
      current: 1,
      colorStyle: '',
      order: {},
    }
  },
  created() {
    this.getInfo()
    this.getProductDetail()
  },
  methods: {
    currentShow(type) {
      this.current = type
    },
    // 获取商品详情
    getProductDetail() {
      getProductDetail().then((res) => {
        this.optionstList = res.data
      })
    },
    getInfo() {
      let green =
        '--view-theme: #42CA4D;--view-priceColor:#FF7600;--view-minorColor:rgba(108, 198, 94, 0.5);--view-minorColorT:rgba(66, 202, 77, 0.1);--view-bntColor:#FE960F;'
      let red =
        '--view-theme: #e93323;--view-priceColor:#e93323;--view-minorColor:rgba(233, 51, 35, 0.5);--view-minorColorT:rgba(233, 51, 35, 0.1);--view-bntColor:#FE960F;'
      let blue =
        '--view-theme: #1DB0FC;--view-priceColor:#FD502F;--view-minorColor:rgba(58, 139, 236, 0.5);--view-minorColorT:rgba(9, 139, 243, 0.1);--view-bntColor:#22CAFD;'
      let pink =
        '--view-theme: #FF448F;--view-priceColor:#FF448F;--view-minorColor:rgba(255, 68, 143, 0.5);--view-minorColorT:rgba(255, 68, 143, 0.1);--view-bntColor:#282828;'
      let orange =
        '--view-theme: #FE5C2D;--view-priceColor:#FE5C2D;--view-minorColor:rgba(254, 92, 45, 0.5);--view-minorColorT:rgba(254, 92, 45, 0.1);--view-bntColor:#FDB000;'
      let gold =
        '--view-theme: #E0A558;--view-priceColor:#DA8C18;--view-minorColor:rgba(224, 165, 88, 0.5);--view-minorColorT:rgba(224, 165, 88, 0.1);--view-bntColor:#1A1A1A;'
	  getMember().then((res) => {
        switch (res.data.color_change) {
          case 1:
            this.colorStyle = blue
            break
          case 2:
            this.colorStyle = green
            break
          case 3:
            this.colorStyle = red
            break
          case 4:
            this.colorStyle = pink
            break
          case 5:
            this.colorStyle = orange
            break
          case 6:
            this.colorStyle = gold
		        break
          default:
            this.colorStyle = red
            break
        }
      })
    },
    onSubmit() {
      this.$emit('parentFun', true)
      saveProductDetail({ product_detail_diy: this.optionstList })
        .then((res) => {
          this.$Message.success(res.msg)
        })
        .catch((err) => {
          this.$Message.error(err.msg)
        })
        .finally(() => {
          this.$emit('parentFun', false)
        })
    },
    isShowDataItem(value) {
      let result = this.optionstList.price_type.some((ele) => ele == value)
      return result
    },
  },
}
</script>
<style scoped lang="stylus">
.tips{
  font-size: 12px;
  margin-top: 5px;
  color: #999;
  margin-left: 90px;
}
/* 定义滑块 内阴影+圆角 */
::-webkit-scrollbar-thumb {
  -webkit-box-shadow: inset 0 0 6px #ddd;
}

::-webkit-scrollbar {
  width: 4px !important; /* 对垂直流动条有效 */
}

.tipInfo{
  font-size: 14px;
  margin-top: 3px;
  .info{
    font-size 12px
  }
}

.default {
  background-color: #fff;
  text-align: center;
  height: 50px;
  line-height: 50px;
  border-radius: 8px;
}

.listB {
  width: 100%;
  background-color: #fff;
  border-radius: 6px;

  .item {
    padding-left: 12px;
    color: #333;
    font-size: 12px;

    img {
      width: 17px;
      height: 17px;
      display: block;
    }

    .text {
      width: 227px;
      border-bottom: 1px solid #EEEEEE;
      padding: 10px 12px 10px 0;

      .iconfont {
        color: #8A8A8A;
        font-size: 12px;
      }
    }
  }
}

.dotted {
  border: 1px dashed #2d8cf0;
  cursor: pointer;
}

.solid {
  border: 1px solid #2d8cf0 !important;
}

.c_row-item {
  .slider-box {
    .info {
      font-size: 13px;
      color: #999999;
    }
  }
}

.swiper-pagination-fraction, .swiper-pagination-custom, .swiper-container-horizontal > .swiper-pagination-bullets {
  bottom: 2px;
}

/deep/.swiper-pagination-bullet {
  width: 4px;
  height: 4px;
}

/deep/.swiper-pagination-bullet-active {
  background: #fff;
}

.public {
  background: #FFFFFF;
  border-radius: 6px;
  margin-top: 6px;
  padding: 5px 8px 10px 8px;
  &.on{
    padding-top 8px;
  }
}

.users {
  .left {
    background: #F7F7F7;
    width: 310px;
    height: 550px;
    overflow-x: hidden;
    overflow-y: auto;
    padding-bottom 1px;
    border-radius: 10px;
    margin-right: 30px;
    border 1px solid #eee;

    .img {
      width: 100%;
      height: 310px;
      display block;
    }

    .details {
      width: 100%;
      position: relative;

      .iconfenxiang {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 16px;
      }

      .details-one {
        .lable {
          color: var(--view-theme);
          font-size: 14px;
          font-weight bold
        }

        .details-add {
          display: inline-block;
          margin-right 2px;
        }

        .price {
          font-size: 21px;
          color: var(--view-theme);
          font-weight: 600;
        }

        .num {
          font-size: 14px;
          font-weight: 600;
          color: #333333;
          margin-left: 1px;
        }

        .vip {
          width: 28px;
          height: 10px;
          margin-left: 3px;
        }
      }

      .details-two {
        margin-top 5px;
        .banrd {
          width: 26px;
          height: 16px;
          text-align: center;
          line-height: 16px;
          border: 1px solid var(--view-theme);
          font-size: 8px;
          font-weight: 400;
          color: var(--view-theme);
          margin-right: 2px;
          padding: 1px 2px;
          border-radius: 3px;
        }

        .title {
          font-size: 14px;
          font-weight: 600;
          color: #333333;
        }
      }

      .details-three {
        font-size: 12px;
        font-weight: 400;
        color: #999999;
        display: flex;
        margin-top: 8px;
      }

      .open {
        margin-top: 9px;

        .img {
          width: 100%;
          height: 30px;
        }
      }
    }

    .discount {
      width: 100%;

      .name {
        font-size: 12px;
        font-weight: 400;
        color: #666666;
        &.on{
          margin-right 8px;
        }
      }

      .iconjinru {
        font-size: 9px;
        margin-left: 61px;
      }

      .list {
        .box {
          display: inline-block;
          width: 79px;
          height: 17px;
          border: 1px solid var(--view-theme);
          color: var(--view-theme);
          font-size: 12px;
          font-weight: 400;
          text-align: center;
          line-height: 17px;
          position: relative;
          margin-left: 7px;

          .yuan {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #fff;
            position: absolute;
            top: 4px;
            left: -6px;
            border-right: 1px solid var(--view-theme);
          }

          .yuan2 {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #fff;
            position: absolute;
            top: 4px;
            right: -6px;
            border-left: 1px solid var(--view-theme);
          }
        }
      }

      .activity {
        margin-top: 13px;
        &.on{
          margin-top 0;
        }

        .list {
          display: inline-block;
          width: 78px;
          height: 22px;
          background: var(--view-theme);
          border-radius: 16px;
          font-size: 12px;
          font-weight: 400;
          color: #FFFFFF;
          text-align: right;
          line-height: 22px;
          position: relative;
          padding-right: 9px;
          margin-left: 5px;
          .iconfont {
            font-size: 14px;
          }

          .iconmiaosha {
            font-size: 15px;
            position: absolute;
            top: 0px;
            left: 6px;
          }

          .iconkanjia {
            font-size: 15px;
            position: absolute;
            top: 0px;
            left: 6px;
          }
        }
      }

      .count {
        margin-top: 13px;
        &.on{
          margin-top 0;
        }

        .minus {
          display: inline-block;
          margin-left: 10px;
          width: 37px;
          height: 17px;
          text-align: center;
          line-height: 19px;
          background: var(--view-minorColorT);
          border-radius: 9px;
          color: var(--view-theme);
          font-size: 12px;
          font-weight: 400;
        }

        .text {
          font-size: 12px;
          font-weight: 400;
          color: #666666;
          margin-left: 4px;
        }

        .iconjinru {
          font-size: 9px;
          margin-left: 25px;
        }
      }
    }

    .specifications {
      width: 100%;
      &.on{
        padding-top 7px
      }

      .name {
        font-size: 12px;
        font-weight: 400;
        color: #666666;
      }

      .choice {
        position: relative;

        .text {
          font-size: 12px;
          font-weight: 400;
          color: #333333;
          margin-left: 7px;
        }

        .iconjinru {
          font-size: 9px;
          position: absolute;
          top: 2px;
          right: 0;
        }
      }

      .picture {
        display: flex;
        margin-top: 6px;
        padding-left: 45px;

        .img {
          width: 30px;
          height: 30px;
          margin-right: 7px;
          background-color: #fff;
        }

        .choose-color {
          width: 115px;
          height: 30px;
          background: #EEEEEE;
          border-radius: 3px;
          font-size: 12px;
          text-align: center;
          font-weight: 400;
          color: #999999;
          line-height: 33px;
        }
      }

      .parameter {
        margin-top: 13px;
        position: relative;
        &.on{
          margin-top 0;
        }

        .text {
          margin-left: 15px;
          font-size: 12px;
          font-weight: 400;
          color: #333333;
        }

        .iconjinru {
          font-size: 9px;
          position: absolute;
          top: 2px;
          right: 0px;
        }
      }

      .guarantee {
        margin-top: 13px;
        position: relative;
        &.on{
          margin-top 0
        }

        .text {
          margin-left: 15px;
          font-size: 12px;
          font-weight: 400;
          color: #333333;
        }

        .iconjinru {
          font-size: 9px;
          position: absolute;
          top: 2px;
          right: 0px;
        }
      }

      .store {
        margin-top: 13px;
        &.on{
          margin-top 0;
        }

        .store-up {
          position: relative;

          .img {
            width: 13px;
            height: 12px;
            margin-left: 15px;
            margin-right: 6px;
            background-color: #fff;
          }

          .store-name {
            font-size: 12px;
            font-weight: 400;
            color: #333333;
          }

          .iconjinru {
            font-size: 9px;
            position: absolute;
            top: 2px;
            right: 0px;
          }
        }

        .nearby {
          margin-top: 5px;
          margin-left: 40px;
          font-size: 12px;
          font-weight: 400;
          color: #333;

          .nearby-name {
            color: var(--view-theme) !important;
          }
        }
      }
    }

    .evaluate {
      margin-top: 8px;
      width: 100%;
      height: 245px;
      border-radius: 7px;

      .img {
        width: 100%;
        height: 100%;
        background: #FFFFFF;
        border-radius: 7px;
        display block;
      }
    }

    .collocation {
      margin-top: 8px;
      width: 100%;
      height: 128px;
      border-radius: 7px;

      .img {
        width: 100%;
        height: 100%;
        background: #FFFFFF;
        border-radius: 7px;
        display block
      }
    }

    .recommend {
      margin-top: 8px;
      width: 100%;
      height: 332px;
      border-radius: 7px;

      .img {
        width: 100%;
        height: 100%;
        background: #FFFFFF;
        border-radius: 7px;
        display block
      }
    }

    .product {
      margin-top: 8px;
      width: 100%;
      height: 390px;
      border-radius: 7px;

      .img {
        width: 100%;
        height: 100%;
        border-radius: 7px;
        display block
      }
    }

    // 购物车
    .shopping-cart {
      margin-top: 15px;
      width: 100%;
      height: 50px;
      display: flex;
      justify-content: space-between;

      .kefu {
        display: flex;
        flex-direction: column;
        font-size: 9px;
        color: #666666;

        .iconguanzhugongzhonghao {
          font-size: 15px;
        }
      }

      .btn {
        display: flex;
        font-size: 14px;
        font-weight: 400;
        color: #FFFFFF;

        .join {
          width: 111px;
          height: 38px;
          background: #FE960F;
          line-height: 38px;
          text-align: center;
          border-radius: 20px 0 0 20px;
        }

        .join-add {
          background-color: var(--view-theme) !important;
          border-radius: 0 20px 20px 0 !important;
        }
      }
    }

    .Tcenter {
      margin-top: -25px;
    }

    .header {
      background-color: var(--view-theme);
      background-image: url('../../../assets/images/user01.png');
      background-size: 100%;
      background-repeat: no-repeat;
      width: 100%;
      height: 112px;
      position: relative;
      margin-bottom: 12px;

      .img {
        width: 375px;
        height: 375px;
        background: pink;
      }

      .top {
        padding: 19px 20px 0 20px;

        .picTxt {
          .pictrue {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;

            img {
              width: 100%;
              height: 100%;
              border-radius: 50%;
            }
          }

          .txt {
            .name {
              font-size: 12px;
              color: #fff;
              font-weight: 600;

              img {
                width: 40px;
                height: 15px;
                margin-left: 7px;
                vertical-align: middle;
              }
            }

            .phone {
              width: 86px;
              height: 21px;
              border-radius: 13px;
              background-color: rgba(16, 55, 72, 0.2);
              font-size: 11px;
              color: #fff;
              margin-top: 4px;

              .iconfont {
                font-size: 11px;
              }
            }
          }
        }

        .news {
          position: relative;
          margin-left: 10px;

          .iconfont {
            font-size: 22px;
            color: #fff;
          }

          .num {
            position: absolute;
            width: 14px;
            height: 14px;
            background: #FFFFFF;
            border-radius: 50%;
            font-size: 9px;
            color: var(--view-theme);
            text-align: center;
            line-height: 14px;
            top: 3px;
            right: -4px;
          }
        }

        .iconerweima-xingerenzhongxin {
          font-size: 20px;
          color: #fff;
        }
      }

      .bottom {
        background-image: url('../../../assets/images/member.png');
        width: 310px;
        height: 34px;
        background-size: 100%;
        background-repeat: no-repeat;
        position: absolute;
        bottom: 0;
        padding: 0 23px 0 50px;
        font-size: 13px;
        color: #905100;

        .renew {
          font-size: 12px;

          .iconjinru {
            font-size: 11px;
          }
        }
      }
    }

    .orderCenter {
      background: #FFFFFF;
      border-radius: 8px;
      margin: 0 18px 10px 18px;
      text-align: center;
      padding: 15px 0;

      &.on {
        position: relative;
        margin-top: 10px;
      }

      .title {
        padding: 0 10px;
        font-size: 13px;
        color: #282828;
        font-weight: 600px;
        margin-bottom: 2px;

        .all {
          font-size: 12px;
          color: #666666;

          .iconfont {
            font-size: 12px;
            margin-left: 2px;
          }
        }
      }

      .list {
        margin-top: 10px;

        .item {
          font-size: 12px;
          color: #454545;

          .iconfont {
            font-size: 20px;
            color: var(--view-theme);
          }
        }
      }

      &.service {
        padding: 15px 0 0 0;
        margin-top: 10px;

        .list {
          .item {
            width: 25%;
            margin-bottom: 10px;

            .pictrue {
              width: 23px;
              height: 23px;
              margin: 0 auto 8px auto;
              font-size: 12px;

              img {
                width: 100%;
                height: 100%;
              }
            }
          }
        }
      }
    }
  }

  .right {
    width: 540px;

    /deep/.ivu-radio-wrapper {
      font-size: 13px;
      margin-right: 20px;
    }

    .line {
      margin-top: 30px;
      width: 100%;
      height: 1px;
      background-color: #EEEEEE;
    }

    .title {
      font-size: 14px;
      color: rgba(0, 0, 0, 0.85);
      position: relative;
      font-weight: bold;

      &:before {
        content: '';
        position: absolute;
        width: 2px;
        height: 14px;
        background: #1890FF;
        top: 50%;
        margin-top: -7px;
        left: -8px;
      }
    }

    .c_row-item {
      margin-top: 24px;
    }

    .add {
      margin-top: 15px !important;
    }

    .text {
      font-size: 12px;
      font-weight: 400;
      color: #999999;
    }
  }
}
</style>
