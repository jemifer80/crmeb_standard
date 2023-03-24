
├── LICENSE.txt //开源协议
├── README.md //说明文档
├── app //应用目录
│   ├── AppService.php //服务类（加载容器）
│   ├── ExceptionHandle.php //应用异常处理类
│   ├── Request.php //请求类
│   ├── Route.php //路由
│   ├── build.php //创建模块配置
│   ├── provider.php //容器Provider定义文件
│   ├── service.php //服务配置
│   ├── event.php //事件
│   ├── middleware.php //中间件容器
│   ├── common.php //通用函数
│   ├── common //通用模块
│   │   └── controller
│   ├── controller //控制器
│   │   ├── InstallController.php //安装控制器
│   │   ├── UpgradeController.php //升级控制器
│   │   ├── admin //后台控制器
│   │   ├── api //用户端控制器
│   │   ├── kefu //客服模块控制器
│   │   ├── out //对外接口控制器
│   │   └── store //门店控制器
│   ├── services //SERVICES层业务逻辑层
│   │   ├── BaseServices.php //SERVICES务逻辑基类
│   │   ├── activity //营销模块
│   │   ├── agent //分销模块
│   │   ├── article //文章模块
│   │   ├── coupon //优惠券模块
│   │   ├── diy //DIY模块
│   │   ├── export //表格导出模块
│   │   ├── kefu //客服模块
│   │   ├── live //直播模块
│   │   ├── message //消息模块
│   │   ├── order //订单模块
│   │   ├── other //其它模块
│   │   ├── out //对外接口
│   │   ├── pay //支付模块
│   │   ├── pc //PC端模块
│   │   ├── product //产品模块
│   │   ├── queue //队列模块
│   │   ├── serve //一号通模块
│   │   ├── shipping //运费模版模块
│   │   ├── statistic //统计模块
│   │   ├── store //门店模块
│   │   ├── system //系统配置模块
│   │   ├── user //用户模块
│   │   └── wechat //应用模块公众号、小程序
│   ├── dao //DAO层 数据持久层
│   │   ├── BaseDao.php //DAO基类
│   │   ├── activity //营销模块
│   │   ├── agent //分销模块
│   │   ├── article //文章模块
│   │   ├── coupon //优惠券模块
│   │   ├── diy //DIY模块
│   │   ├── live //直播模块
│   │   ├── order //订单模块
│   │   ├── other //其它模块 缓存、分类、城市数据、二维码、模版消息、隐私协议
│   │   ├── out //对外接口模块
│   │   ├── product //产品模块
│   │   ├── queue //队列模块和缓存模块
│   │   ├── service //客服模块
│   │   ├── shipping //运费模块模块
│   │   ├── sms //短信模块
│   │   ├── store //门店模块
│   │   ├── system //系统设置模块
│   │   ├── user //用户模块
│   │   └── wechat //应用模块公众号、小程序
│   ├── model //MODEL层数据访问层
│   │   ├── activity //营销模块
│   │   ├── agent //分销模块
│   │   ├── article //文章模块
│   │   ├── coupon //优惠券模块
│   │   ├── diy //DIY模块
│   │   ├── live //直播模块
│   │   ├── order //订单模块
│   │   ├── other //其它模块
│   │   ├── out //对外接口模块
│   │   ├── product //产品模块
│   │   ├── queue //队列模块
│   │   ├── service //客服模块
│   │   ├── shipping //运费模版模块
│   │   ├── sms //短信模块
│   │   ├── store //门店模块
│   │   ├── system //系统设置模块
│   │   ├── user //用户模块
│   │   └── wechat //应用模块
│   ├── http //中间件
│   │   └── middleware
│   │       ├── AllowOriginMiddleware.php //跨域中间件
│   │       ├── InstallMiddleware.php //安装中间件
│   │       ├── StationOpenMiddleware.php //升级中间件
│   │       ├── admin //后台中间件
│   │       ├── api //用户端中间件
│   │       ├── kefu //客服中间件
│   │       ├── out //对外接口中间件
│   │       └── store //门店中间件
│   ├── jobs //jobs层队列执行
│   │   ├── BatchHandleJob.php //批量添加job
│   │   ├── agent
│   │   │   ├── AgentJob.php //分销员检测
│   │   │   ├── AutoAgentJob.php //分销员自动解除上下级
│   │   │   └── SystemJob.php //重置分销有效期
│   │   ├── live
│   │   │   └── AutoUpdateLiveJob.php //直播自动更新
│   │   ├── notice
│   │   │   ├── EnterpriseWechatJob.php //企业微信群通知
│   │   │   ├── PrintJob.php //打印机job
│   │   │   ├── SmsAdminJob.php //管理员短信通知队列
│   │   │   └── SmsJob.php //短信发送队列
│   │   ├── order
│   │   │   ├── AutoOrderUnpaidCancelJob.php //自动取消未支付订单
│   │   │   ├── AutoTakeOrderJob.php //自动执行确认收货
│   │   │   ├── OrderCreateAfterJob.php //订单创建后
│   │   │   ├── OrderDeliveryJob.php //订单发货
│   │   │   ├── OrderJob.php //订单消息队列
│   │   │   ├── OrderTakeJob.php //订单收货任务
│   │   │   ├── OtherOrderJob.php //其它支付订单消息队列
│   │   │   ├── SpliteStoreOrderJob.php //拆分门店订单
│   │   │   ├── TakeOrderJob.php //自动收货消息队列 //TODO弃用
│   │   │   ├── UnpaidOrderCancelJob.php //未支付根据系统设置事件取消订单
│   │   │   └── UnpaidOrderSend.php //未支付10分钟后发送短信
│   │   ├── pink
│   │   │   ├── AuthPinkFail.php //分页处理拼团
│   │   │   └── PinkJob.php //拼团失败
│   │   ├── product
│   │   │   ├── ProductCopyJob.php //采集商品
│   │   │   └── ProductLogJob.php //商品日志
│   │   ├── store
│   │   │   └── StoreFinanceJob.php //门店资金流水记录
│   │   ├── system
│   │   │   ├── AutoClearPosterJob.php //自动清除过期海报
│   │   │   ├── AutoSmsCodeJob.php //自动更新短信状态
│   │   │   └── ExportExcelJob.php //导出数据队列
│   │   ├── template
│   │   │   ├── RoutineTemplateJob.php //小程序模版消息队列
│   │   │   ├── TemplateJob.php //模版消息队列
│   │   │   └── WechatTemplateJob.php //公众号模版消息队列
│   │   └── user
│   │       ├── AutoClearIntegralJob.php //自动清除用户积分分批加入队列
│   │       ├── UserIntegralJob.php //清除到期积分
│   │       └── UserJob.php //同步微信用户
│   ├── lang //语言配置
│   │   └── zh-cn.php
│   ├── listener //事件
│   │   ├── admin
│   │   │   ├── LoginSuccess.php //管理员登录
│   │   │   └── LogoutSuccess.php  //管理员退出
│   │   ├── config
│   │   │   ├── CreateSuccess.php //添加配置
│   │   │   ├── DeleteSuccess.php //删除配置
│   │   │   └── StatusSuccess.php //修改配置
│   │   ├── live
│   │   │   └── AutoUpdateLive.php //直播数据同步
│   │   ├── notice
│   │   │   └── Notice.php //系统通知事件
│   │   ├── order
│   │   │   ├── AutoCancel.php //订单到期自动取消
│   │   │   ├── AutoTake.php //自动取消订单
│   │   │   ├── Comment.php //订单评价事件
│   │   │   ├── Create.php //创建订单
│   │   │   ├── Delivery.php //订单发货
│   │   │   ├── Pay.php //订单支付成功
│   │   │   ├── PriceRevision.php //订单改价格事件
│   │   │   ├── Refund.php //订单退款事件
│   │   │   ├── RefuseRefund.php //订单拒绝退款
│   │   │   ├── SubmitRefund.php //订单申请退款
│   │   │   └── Take.php //订单收货
│   │   ├── pink
│   │   │   └── AutoPink.php
│   │   ├── product
│   │   │   ├── CreateSuccess.php //添加商品成功
│   │   │   ├── DeleteSuccess.php //删除商品
│   │   │   └── StatusSuccess.php //商品状态变动
│   │   ├── sms
│   │   │   └── SendAfterSuccess.php //短信发送成功
│   │   ├── store
│   │   │   ├── DeleteSuccess.php //删除门店
│   │   │   ├── StatusSuccess.php //门店状态变更
│   │   │   └── StoreSuccess.php //添加门店
│   │   ├── system
│   │   │   ├── AutoClearPoster.php //定时清除海报
│   │   │   ├── AutoConfig.php //定时检测config
│   │   │   └── AutoSmsCode.php //定时检测短信状态
│   │   └── user
│   │       ├── AutoAgent.php //定时检测分销员
│   │       ├── AutoClearIntegral.php //定时清除用户积分
│   │       ├── CreateSuccess.php //创建用户
│   │       ├── Extract.php //用户申请提现
│   │       ├── Login.php //用户登录
│   │       ├── Recharge.php //用户充值支付成功
│   │       ├── Register.php //用户注册
│   │       └── VipUser.php //用户购买vip
│   ├── validate //表单验证
│   │   ├── admin //后台
│   │   ├── api //用户端
│   │   ├── kefu //客服端
│   │   ├── out //对外接口
│   │   └── store //门店
│   └── webscoket //长链接
│       ├── BaseHandler.php //处理基类
│       ├── Manager.php //websocket处理
│       ├── Ping.php //心跳ping
│       ├── Response.php //返回数据
│       ├── Room.php //聊天
│       ├── SwooleWorkerStart.php //swoole启动
│       └── handler //具体处理类
├── build.example.php //默认生成文件配置
├── composer.json //composer 配置文件
├── composer.lock //composer 文件
├── config //系统配置文件
│   ├── app.php  //系统配置
│   ├── auth.php //授权文件配置
│   ├── cache.php //缓存配置
│   ├── captcha.php //验证码配置
│   ├── console.php //控制台配置
│   ├── cookie.php //cookie配置
│   ├── database.php //数据库连接配置
│   ├── filesystem.php //系统文件目录配置
│   ├── lang.php //语言配置
│   ├── log.php //日志配置
│   ├── pay.php //支付配置
│   ├── plat.php //一号通短信配置
│   ├── printer.php //小票打印配置
│   ├── qrcode.php //二维码配置
│   ├── queue.php //队列配置
│   ├── route.php //路由配置
│   ├── session.php //session
│   ├── sms.php //短信配置
│   ├── swoole.php //swoole配置
│   ├── template.php //应用模版消息配置
│   ├── trace.php //调试配置
│   ├── upload.php //上传文件配置
│   └── view.php //模版配置
├── crmeb //CRMEB核心文件
│   ├── basic //系统基类
│   │   ├── BaseAuth.php //系统商业授权类
│   │   ├── BaseController.php //控制器基类
│   │   ├── BaseExpress.php //物流基类
│   │   ├── BaseJobs.php //消息队列基类
│   │   ├── BaseManager.php //驱动基类
│   │   ├── BaseMessage.php //消息基类
│   │   ├── BaseModel.php //model基类
│   │   ├── BasePay.php //支付基类
│   │   ├── BasePrinter.php //打印基类
│   │   ├── BaseProduct.php //产品基类
│   │   ├── BaseSms.php //短信基类
│   │   ├── BaseSmss.php //一号通短信基类
│   │   ├── BaseStorage.php //容器基类
│   │   └── BaseUpload.php //上传文件基类
│   ├── command //命令行
│   │   ├── Dao.php //生成dao类文件命令
│   │   ├── Install.php //安装命令
│   │   ├── Service.php //生成Service类文件命令
│   │   └── stubs //生成文件模版
│   ├── exceptions //异常处理类
│   │   ├── AdminException.php //后台异常处理类
│   │   ├── ApiException.php //用户端异常处理类
│   │   ├── AuthException.php //用户授权异常处理类
│   │   ├── PayException.php //支付异常处理类
│   │   ├── SmsException.php //短信异常处理类
│   │   ├── TemplateException.php //模版消息异常处理类
│   │   ├── UploadException.php //文件上传异常处理类
│   │   └── WechatReplyException.php //微信回复消息异常处理类
│   ├── interfaces //接口类
│   │   ├── HandlerInterface.php //长链接接口类
│   │   ├── JobInterface.php //队列接口类
│   │   ├── ListenerInterface.php //事件接口类
│   │   ├── MiddlewareInterface.php //中间件接口类
│   │   └── ProviderInterface.php //容器接口类
│   ├── listeners //事件
│   │   ├── InitSwooleLockListen.php //swoole 初始化事件
│   │   ├── Listener.php
│   │   ├── SwooleShutdownListen.php //swoole 停止事件
│   │   ├── SwooleStartListen.php //swoole 启动事件
│   │   ├── SwooleTaskListen.php //异步任务 事件
│   ├── services //业务处理类 需要读取系统配置或重写vendor内部类
│   │   ├── AccessTokenServeService.php //token
│   │   ├── AliPayService.php //支付宝支付
│   │   ├── CacheService.php //缓存
│   │   ├── CopyProductService.php //采集产品
│   │   ├── DownloadImageService.php //下载图片
│   │   ├── ExpressService.php //物流查询
│   │   ├── FileService.php //文件处理
│   │   ├── FormBuilder.php //自动生成表单
│   │   ├── GroupDataService.php //获取组合数据
│   │   ├── HttpService.php //curl请求
│   │   ├── MiniProgramService.php //小程序封装类
│   │   ├── MysqlBackupService.php //数据库备份
│   │   ├── PaymentService.php //微信支付重写注入request
│   │   ├── QrcodeService.php //二维码处理
│   │   ├── SpreadsheetExcelService.php //excel处理类
│   │   ├── SwooleTaskService.php //swoole异步任务类
│   │   ├── SystemConfigService.php //获取系统配置类
│   │   ├── UpgradeService.php //升级类
│   │   ├── UploadService.php //文件上传
│   │   ├── UtilService.php //工具类
│   │   ├── VicWordService.php //分词类
│   │   ├── WechatOpenService.php //微信开放平台类
│   │   ├── WechatService.php //公众号类
│   │   ├── WechatWorkService.php //企业微信类
│   │   ├── easywechat //微信封装类
│   │   ├── express //物流
│   │   ├── printer //打印机
│   │   ├── product //产品采集
│   │   ├── serve //一号通
│   │   ├── sms //短信
│   │   ├── template //模版消息
│   │   └── upload //文件上传
│   ├── traits //复用类
│   │   ├── ErrorTrait.php //设置错误
│   │   ├── JwtAuthModelTrait.php  //无用* 但是重复写函数的挺多
│   │   ├── MacroTrait.php  //获取request中属性
│   │   ├── ModelTrait.php  //model时间搜索器
│   │   ├── OptionTrait.php  //设置参数
│   │   ├── QueueTrait.php //队列
│   │   ├── SearchDaoTrait.php  //设置搜索器
│   │   └── ServicesTrait.php //services注解映射baseDao方法
│   └── utils //工具类
│       ├── ApiErrorCode.php //错误码
│       ├── Arr.php //数组处理辅助类
│       ├── Canvas.php //生成图片类
│       ├── Captcha.php //验证码类
│       ├── Hook.php //监听事件
│       ├── Json.php //json处理类
│       ├── JwtAuth.php //Jwt token处理类
│       ├── QRcode.php //二维码类* 好像也是停用的
│       ├── Queue.php //队列工具类
│       └── Cron.php //定时任务处理类
├── help //不同php版本加密文件
│   ├── 7.1
│   │   ├── config
│   │   └── crmeb
│   ├── 7.2
│   │   ├── config
│   │   └── crmeb
│   ├── 7.3
│   │   ├── config
│   │   └── crmeb
│   ├── pro_default
│   │   ├── config
│   │   └── crmeb
│   ├── swoole-compiler.txt
│   ├── swoole-loader //不同环境加密扩展
│   │   ├── php_swoole_loader_php71_nzts_x64.dll  //php7.1 非线程安全版 64位windows服务器
│   │   ├── php_swoole_loader_php71_zts_x64.dll //php7.1 非线程安全版 64位windows服务器
│   │   ├── php_swoole_loader_php72_nzts_x64.dll //php7.2 非线程安全版 64位windows服务器
│   │   ├── php_swoole_loader_php72_zts_x64.dll //php7.2 非线程安全版 64位windows服务器
│   │   ├── php_swoole_loader_php73_nzts_x64.dll //php7.3 非线程安全版 64位windows服务器
│   │   ├── php_swoole_loader_php73_zts_x64.dll //php7.3 非线程安全版 64位windows服务器
│   │   ├── swoole_loader71.so //php7.1 Linux服务器
│   │   ├── swoole_loader71_zts.so //php7.1 Linux服务器
│   │   ├── swoole_loader72.so //php7.2 Linux服务器
│   │   ├── swoole_loader72_zts.so //php7.2 Linux服务器
│   │   ├── swoole_loader73.so //php7.3 Linux服务器
│   │   └── swoole_loader73_zts.so //php7.3 Linux服务器
│   └── swoole_loader_mac //MacOS系统加密扩展
│       ├── swoole_loader71_2.2.so //php7.1 MacOS服务器
│       ├── swoole_loader72_2.2.so //php7.1 MacOS服务器
│       ├── swoole_loader73_2.2.so //php7.1 MacOS服务器
│       └── swoole_loader74_2.2.so //php7.1 MacOS服务器
├── public //系统入口目录
│   ├── favicon.ico //网站ico图标
│   ├── index.html //移动端首页
│   ├── index.php //网站入口文件
│   ├── install //安装程序目录
│   │   ├── crmeb.sql //安装程序mysql数据库文件
│   │   ├── css
│   │   ├── images
│   │   ├── js
│   │   └── view //安装程序页面
│   ├── nginx.htaccess //伪静态配置文件
│   ├── public 
│   ├── router.php //兼容不同环境路由配置文件
│   └── statics //静态文件目录
│       ├── exception.tpl
│       ├── font
│       ├── poster
│       └── qrcode
├── route //路由配置目录
│   ├── admin.php //后台路由配置
│   ├── api.php //用户端路由配置
│   ├── kefu.php //客服路由配置
│   ├── out.php //对外接口路由配置
│   ├── zroute.php //路由配置
│   └── store.php //门店端路由配置
├── think //thinphp 命令程序
├── tree3.md //目录说明
└── view //html 目录安装和升级页面
    ├── install //安装页面
    │   ├── step1.html //安装页面
    │   ├── step2.html //安装页面
    │   ├── step3.html //安装页面
    │   ├── step4.html //安装页面
    │   └── step5.html //安装页面
    └── upgrade //升级页面
        └── step1.html

