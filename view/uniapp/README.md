# CRMEB 移动端
## 开发规范
市面上常用的命名规范：

* camelCase（小驼峰式命名法 —— 首字母小写）
* PascalCase（大驼峰式命名法 —— 首字母大写）
* kebab-case（短横线连接式）
* snake_a（下划线连接式）

##### 项目文件命名
#####1、项目名
#####全部采用小写方式， 以下划线分隔。 例：crmeb_pro_uniapp；
#####2、组件（components）
#####优先选择单个单词命名，多个单词命名以小驼峰式命名。例：crmebPro；
#####3、pages里面的文件名；
#####全部采用小写方式， 优先选择单个单词命名，多个单词命名以下划线分隔，组件参考第2条。 例：crmeb_pro_uniapp；
#####4、css文件名；
#####全部采用小写方式， 优先选择单个单词命名，多个单词命名以短横线分隔。例：crmeb-pro.css
#####5、JavaScript 文件名；
#####全部采用小写方式， 优先选择单个单词命名，多个单词命名以短横线分隔。例：crmeb-pro.js
#####6、HTML 文件名；
#####全部采用小写方式， 优先选择单个单词命名，多个单词命名以下划线分隔。例：crmeb_pro.html
#####7、图像文件名；
#####全部采用小写方式， 优先选择单个单词命名，多个单词命名以短横线分隔。例：crmeb-pro.jpg

## 目录结构
主要目录结构及说明：
~~~
├── api                       # 请求接口
│   ├── activity              # 活动接口
│   ├── admin                 # 管理端接口
│   ├── api                   # 文章优惠券等接口
│   ├── esp                   # esp接口
│   ├── kefu                  # 客服接口
│   ├── lottery               # 抽奖活动接口
│   ├── order                 # 订单接口
│   ├── points_mall           # 积分商城接口
│   ├── public                # 配置类接口
│   ├── store                 # 商品接口
│   ├── user                  # 我的接口
│   └──work                   # 企业微信接口
│   ├── components            # 公共组件
│   │    └──addressWindow          # 选择地址
│   │    └──cartDiscount         # 购物车优惠明细
│   │    └──cartList             # 购物车列表
│   │    └──countDown           # 倒计时
│   │    └──couponListWindow           # 优惠券列表
│   │    └──couponWindow          # 首页优惠券
│   │    └──cusPreviewImg          # 规格轮播
│   │    └──customForm          # 自定义组件
│   │    └──d_goodList          # 分类二的组件
│   │    └──goodClass          # 分类三的组件
│   │    └──goodList          # 营销组件
│   │    └──guide          # 闪屏组件
│   │    └──home          # 悬浮导航
│   │    └──homeList          # 导航列表
│   │    └──jyf-parser          # 富文本
│   │    └──kefuIcon          # 客服
│   │    └──Loading          # 加载
│   │    └──orderGoods          # 订单商品
│   │    └──pageFooter          # 底部导航
│   │    └──payment          # 支付
│   │    └──productConSwiper          # 商品轮播
│   │    └──productWindow          # 商品属性
│   │    └──recommend          # 热门推荐
│   │    └──skeleton          # 骨架屏
│   │    └──storeLis          # 门店列表
│   │    └──swipers          # 轮播
│   │    └──uni-calendar          # 日期
│   │    └──uniNoticeBar          # 跑马灯
│   │    └──userEvaluation          # 评价
│   │    └──zb-code          # 二维码生成插件
│   │    └──emptyPage          # 无数据时显示页面
│   ├── config               # 项目配置文件
│   ├── libs                  
│   │    └──login            # 登录
│   │    └──network            # 检测长链接
│   │    └──new_chat            # 检测长链接
│   │    └──order            # 订单跳转
│   │    └──routine            # 授权
│   │    └──wechat            # h5授权支付等函数
│   │    └──work            # 企业微信
│   ├── mixins                # 通用混合
│   │    └──color          # 一键换色
│   │    └──SendVerifyCode  # 获取验证码
│   ├── pages                 # 所有页面
│   │    └──activity              # 活动
│   │         └──bargain # 砍价状态列表
│   │         └──components # 组件
│   │               └──giftGoods      # 商品赠品列表
│   │         └──discount      # 活动折扣列表
│   │         └──goods_bargain        # 砍价商品列表
│   │         └──goods_bargain_details # 砍价详情
│   │         └──goods_combination     # 拼团列表
│   │         └──goods_combination_details    # 拼团详情
│   │         └──goods_combination_status   # 拼团状态
│   │         └──goods_seckill   # 秒杀列表
│   │         └──goods_seckill_details   # 秒杀详情
│   │         └──poster-poster   # 海报
│   │         └──presell   # 预售列表
│   │         └──static   # 图片
│   │    └──admin             # 管理端
│   │         └──components   # 组件
│   │               └──priceChange      # 改价以及备注弹窗
│   │               └──ucharts      # 统计图
│   │               └──writeOffSwitching      # 核销列表
│   │         └──custom_date   # 选择日期
│   │         └──delivery   # 订单发货
│   │         └──distribution   
│   │               └──orderDetail      # 订单详情
│   │               └──scanning      # 扫描结果
│   │               └──index      # 配送员
│   │         └──order   # 订单统计
│   │         └──order_cancellation   # 订单核销
│   │         └──orderDetail   # 订单详情
│   │         └──orderList   # 订单列表
│   │         └──static   # 图片
│   │         └──statistics   # 订单数据统计
│   │         └──store   # 门店管理
│   │               └──custom_date      # 选择日期
│   │               └──deliverGoods      # 订单发货
│   │               └──order      # 订单管理
│   │               └──orderDetail      # 订单详情
│   │               └──scanning      # 扫描结果
│   │               └──statistics      # 订单数据统计
│   │               └──index      # 门店中心
│   │    └──annex   # 会员
│   │         └──offline_pay      # 支付
│   │         └──offline_result      # 支付结果
│   │         └──special      # 专题页
│   │         └──vip_active      # 激活会员
│   │         └──vip_clause      # 会员协议
│   │         └──vip_coupon      # 会员优惠券
│   │         └──vip_paid      # SVIP会员
│   │         └──web_view      # 外部链接跳转
│   │    └──auth   # 登录页
│   │    └──columnGoods            
│   │         └──HotNewGoods   
│   │               └──feedback      # 我的客服
│   │               └──index      # 精品推荐
│   │         └──live_list    #推荐好货
│   │         └──static    #图片
│   │    └──extension
│   │         └──components    #组件
│   │               └──shareInfo      # 分享  
│   │               └──vconsole.min      # 查看后台打印
│   │         └──customer_list  
│   │               └──chat      # 客服聊天界面  
│   │         └──invite_friend    #邀请好友
│   │         └──news_details    #资讯详情
│   │         └──news_list    #资讯   
│   │         └──static    #图片
│   │    └──goods
│   │         └──admin_order_detail    #订单详情
│   │         └──components    #组件
│   │               └──invoiceModal      # 选择发票 
│   │               └──invoicePicker      # 添加发票信息 
│   │               └──lottery      # 抽奖转盘 
│   │               └──maramlee-waterfalls-flow      # 客服聊天界面 
│   │         └──goods_comment_con    #组件
│   │               └──comment_con      # 评价详情
│   │               └──index      # 商品评价
│   │               └──lottery_comment      # 订单评价
│   │         └──goods_comment_list    #商品评分列表
│   │         └──goods_details_store    #门店列表
│   │         └──goods_list    #商品列表
│   │         └──goods_logistics    #商品物流
│   │         └──goods_return    #申请退货
│   │         └──goods_return_list    #退货列表
│   │         └──goods_search    #商品搜索
│   │         └──lottery     
│   │               └──grids
│   │                    └──index  #抽奖活动
│   │                    └──record   #中奖纪录
│   │         └──order_confirm    #确认订单
│   │         └──order_details    #订单详情
│   │         └──order_pay    #订单支付
│   │         └──order_pay_status    #支付成功
│   │         └──order_refund_goods    #退回商品
│   │         └──order_pay_status    #退回商品
│   │         └──static    #图片
│   │    └──goods_cate 分类
│   │    └──goods_details 商品详情
│   │    └──guide 闪屏
│   │    └──index 首页
│   │    └──order_addcart 购物车
│   │    └──points_mall 积分商城
│   │         └──components    #组件
│   │               └──productWindow      #商品规格 
│   │         └──static    #图片
│   │         └──exchange_record    #兑换记录
│   │         └──index    #积分商城首页
│   │         └──integral_goods_details    #商品详情
│   │         └──integral_goods_list    #商品列表
│   │         └──integral_order    #积分订单
│   │         └──integral_order_details    #兑换订单详情
│   │         └──integral_order_status    #兑换成功
│   │         └──logistics_details    #兑换物流详情
│   │         └──user_address    #选择地址
│   │    └──user 个人中心
│   │    └──users 我的
│   │         └──alipay_invoke    #支付提示
│   │         └──commission_rank    #佣金排行
│   │         └──components    #组件
│   │               └──areaWindow      #选择地区
│   │               └──login_mobile      #登录
│   │               └──pageHeader      #头部导航
│   │               └──timeSlot      #时间日期插件
│   │         └──login    #登录
│   │         └──message_center    
│   │               └──index      #消息中心
│   │               └──messageDetail      #消息详情
│   │         └──privacy    #协议
│   │         └──promoter_rank    #推广人排行
│   │         └──promoter-list    #推广人列表
│   │         └──promoter-order    #推广人订单
│   │         └──retrievePassword    #忘记密码
│   │         └──scan_login    #授权登录
│   │         └──static    #图片
│   │         └──user_address    #选择地址
│   │         └──user_address_list    #地址管理
│   │         └──user_bill    #账单明细
│   │         └──user_cancellation    #注销说明
│   │         └──user_cash    #提现
│   │         └──user_coupon    #我的优惠券
│   │         └──user_distribution_level    #分销等级
│   │         └──user_get_coupon    #领取优惠券
│   │         └──user_goods_collection    #收藏商品
│   │         └──user_info    #个人资料
│   │         └──user_integral    #积分详情
│   │         └──user_invoice_form    #添加新发票
│   │         └──user_invoice_list    #发票管理
│   │         └──user_invoice_order    #订单详情
│   │         └──user_money    #我的账户
│   │         └──user_payment    #余额充值
│   │         └──user_phone    #绑定手机
│   │         └──user_pwd_edit    #修改密码
│   │         └──user_return_list    #退货列表
│   │         └──user_sgin    #签到
│   │         └──user_sgin_list    #签到记录
│   │         └──user_spread_code    #分销海报
│   │         └──user_spread_money    #佣金记录
│   │         └──user_spread_user    #我的推广
│   │         └──user_vip    #我的等级
│   │         └──user_vip_areer    #经验记录
│   │         └──visit_list    #浏览记录
│   │         └──wechat_login    #账户登录
│   │    └──work 企业微信
│   │         └──components    #组件
│   │               └──tabNav      #导航 
│   │         └──groupInfo    #群组信息
│   │         └──orderDetail    #订单详情
│   │         └──orderList    #交易管理
│   │         └──record    #记录
│   │         └──userInfo    #客户信息
│   ├── plugins                # 插件
│   ├── static                 # 静态文件
│   ├── store                  # Vuex 状态管理
│   ├── utils                  # js工具
│   ├── App                # 入口文件
│   ├── main.js                # 入口文件，注册vue等
│   └── pages.json                # 页面配置
~~~
## 开发打包项目
~~~
uniapp开发工具必须为HBuilder

# 启动项目(本地开发环境)
点击运行

# 打包项目
点击发行
~~~


###开发团队：

##### 前端开发：小小、娜娜
##### 后端开发：等风来、zhypy
##### 产品经理：木子刀客
##### UI设计：xy-yyds
##### 测试：夏天

注：排名不分前后
