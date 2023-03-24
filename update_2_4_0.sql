
-- 增加pc底部配置
INSERT INTO `eb_system_config` (`id`, `is_store`, `menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES
(null, '0', 'filing_list', 'textarea', '', '74', '', '1', '', '100', '5', '', '底部自定义', 'PC底部自定义（公安备案、市场监管等）', '0', '1');

-- 商品海报
INSERT INTO `eb_system_config` (`id`, `is_store`, `menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES
(null, '0', 'product_poster_title', 'text', 'input', '72', '', '', '', 100, 0, '品牌官方 · 交易保障 · 优质口碑 · 售后无忧', '商品分享海报头部', '商品分享海报图片头部文字描述', 0, 1);

-- 商品列表视频播放
INSERT INTO `eb_system_config` (`id`, `is_store`, `menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES
(NULL, '0', 'product_video_status', 'radio', '', '5', '1=>开启\r\n0=>关闭', '', '', '0', '0', '1', '商品列表视频', '开启后，商品列表视频自动播放', '0', '1');

UPDATE `eb_system_config` set `desc` = '百度统计或其他JS代码' where  `menu_name` = 'system_statistics';
UPDATE `eb_system_config` set `info` = '一级返佣比例（%）' where  `menu_name` = 'store_brokerage_ratio';
UPDATE `eb_system_config` set `info` = '二级返佣比例（%）' where  `menu_name` = 'store_brokerage_two';
UPDATE `eb_system_config` set `info` = '冻结时间（天）' where  `menu_name` = 'extract_time';
UPDATE `eb_system_config` set `info` = '推广返佣单价（元）' where  `menu_name` = 'uni_brokerage_price';
UPDATE `eb_system_config` set `info` = '每日推广限额（元）' where  `menu_name` = 'day_brokerage_price_upper';
UPDATE `eb_system_config` set `desc` = '是否开启自购返佣（开启：分销员自己购买商品，享受一级返佣，上级享受二级返佣，上上级不在享受返佣； 关闭：分销员自己购买商品没有返佣，上级、上上级正常享受返佣）' where  `menu_name` = 'is_self_brokerage';
UPDATE `eb_system_config` set `info` = '验证码有效期（分）' where  `menu_name` = 'verify_expire_time';
UPDATE `eb_system_config` set `info` = '悬浮菜单' , `desc` = '页面悬浮菜单开关' where  `menu_name` = 'navigation_open';
UPDATE `eb_system_config` set `desc` = '达达配送是否开启（配置文档：https://doc.crmeb.com/pro/crmebprov2/6900，官方地址：http://newopen.imdada.cn/#/）' where  `menu_name` = 'dada_delivery_status';
UPDATE `eb_system_config` set `desc` = 'UU配送是否开启（配置文档：https://doc.crmeb.com/pro/crmebprov2/6899，官方地址：http://open.uupt.com/NewVer/index.html#/）' where  `menu_name` = 'uu_delivery_status';
UPDATE `eb_system_config` set `desc` = '支付公钥（开放平台网站支付宝公钥，不是应用公钥，RSA2格式）' where  `menu_name` = 'alipay_public_key';
UPDATE `eb_system_config` set `desc` = '支付私钥（开放平台助手生成，RSA2 PKCS1格式）' where  `menu_name` = 'alipay_merchant_private_key';
UPDATE `eb_system_config` set `info` = '最低累计消费金额' , `desc` = '满额分销最低累计消费xxx(元)，自动开通分销权限' where  `menu_name` = 'store_brokerage_price';
UPDATE `eb_system_config` set `desc` = '易联云打印机终端号，打印机型号：易联云打印机 K4无线版' where  `menu_name` = 'terminal_number';
UPDATE `eb_system_config` set `desc` = '请购买快递100电子面单打印机，快递100电子面单打印机型号：快递100云打印机二代3寸 电脑WiFi两用' where  `menu_name` = 'config_export_siid';

-- 消息体
INSERT INTO `eb_system_notification` (`id`, `mark`, `name`, `title`, `is_system`, `is_app`, `is_wechat`, `is_routine`, `is_sms`, `is_ent_wechat`, `system_title`, `system_text`, `app_id`, `wechat_id`, `routine_id`, `sms_id`, `ent_wechat_text`, `variable`, `url`, `type`, `add_time`) VALUES
(NULL, 'kami_deliver_goods_code', '虚拟商品发货通知', '购买虚拟商品给用户发送提醒', 1, 0, 1, 0, 1, 0, '虚拟商品发货通知', '您购买的卡密商品已支付成功，支付{price}，订单号：{order_id}，卡号：{card_no}，密码：{card_pwd}，感谢您的光临！', 0, '0', '0', 849210, '', '', '', 1, 0),
(NULL, 'login_city_error', '异地登录通知', '账号异地登录给用户发送提醒', 0, 0, 0, 0, 1, 0, '', '您的账号于{time}在{city}登录，上次登录地址为{login_city}，非本人登录请联系管理员或及时修改密码！', 0, '0', '0', 0, '', '', '', 1, 0),
(NULL, 'order_fictitious_success', '虚拟发货通知', '订单虚拟发货给用户发送提醒', 1, 0, 0, 0, 1, 0, '', '亲爱的用户{nickname}您的商品{store_name}，订单号{order_id}已发货，请注意查收', 0, '0', '0', 0, '', '', '', 1, 0);

-- 微信模板
SELECT `id` as nid FROM `eb_system_notification` WHERE `mark`='kami_deliver_goods_code' LIMIT 1 into @nid;
INSERT INTO `eb_template_message` (`notification_id`, `type`, `tempkey`, `name`, `kid`, `content`, `example`, `tempid`, `add_time`, `status`) VALUES
(@nid, 1, 'OPENTM414876266', '虚拟商品发货通知', '', '{{first.DATA}}\r\n发货形式：{{keyword1.DATA}}\r\n卡密：{{keyword2.DATA}}\r\n{{remark.DATA}}', '', '', '1672193591', 1);

--
-- 转存表中的数据 `eb_store_integral` 修改数据表
--
ALTER TABLE `eb_store_integral` ADD COLUMN  `integral` int(12) DEFAULT '0' COMMENT '积分价格' AFTER `price`;
ALTER TABLE `eb_store_integral` MODIFY column  `price` DECIMAL(10,2) DEFAULT '0.00' COMMENT '价格';

--
-- 转存表中的数据 `eb_store_product_attr_value` 修改数据表
--
ALTER TABLE `eb_store_product_attr_value` ADD COLUMN  `integral` int(12) DEFAULT '0' COMMENT '积分价格' AFTER `price`;
ALTER TABLE `eb_store_product_attr_value` MODIFY column  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '活动类型 0=商品，1=秒杀，2=砍价，3=拼团，4=积分商品，5=套餐，7=新人专享';

--
-- 转存表中的数据 `eb_store_integral_order` 修改数据表
--
ALTER TABLE `eb_store_integral_order` ADD COLUMN  `integral` int(12) DEFAULT '0' COMMENT '积分价格' AFTER `total_price`;
ALTER TABLE `eb_store_integral_order` ADD COLUMN  `total_integral` int(12) DEFAULT '0' COMMENT '总积分' AFTER `integral`;
ALTER TABLE `eb_store_integral_order` ADD COLUMN  `paid` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '支付状态' AFTER `total_integral`;
ALTER TABLE `eb_store_integral_order` ADD COLUMN  `pay_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '支付时间' AFTER `paid`;
ALTER TABLE `eb_store_integral_order` ADD COLUMN  `pay_type` varchar(32) NOT NULL DEFAULT '' COMMENT '支付方式' AFTER `pay_time`;
ALTER TABLE `eb_store_integral_order` MODIFY column  `price` DECIMAL(12,2) DEFAULT '0.00' COMMENT '价格';
ALTER TABLE `eb_store_integral_order` MODIFY column  `total_price` DECIMAL(12,2) DEFAULT '0.00' COMMENT '总价格';

-- 活动表修改
ALTER TABLE `eb_store_promotions` ADD `image` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '活动图' AFTER `title`;
ALTER TABLE `eb_store_promotions` CHANGE `promotions_type` `promotions_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '活动类型：1：限时折扣2:第N件N折3:满减满折4:满送5:活动边框6:活动背景';
-- 活动辅助表修改
ALTER TABLE `eb_store_promotions_auxiliary` ADD `brand_id` INT(11) NOT NULL DEFAULT '0' COMMENT '品牌id' AFTER `coupon_id`;
ALTER TABLE `eb_store_promotions_auxiliary` ADD `store_label_id` INT(11) NOT NULL DEFAULT '0' COMMENT '商品标签id' AFTER `brand_id`;
ALTER TABLE `eb_store_promotions_auxiliary` CHANGE `product_partake_type` `product_partake_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '参与商品类型：1:全部商品2：指定商品参与3：指定商品不参与4：指定品牌参与5：指定标签参与';

--
-- 转存表中的数据 `eb_system_timer`
--
CREATE TABLE IF NOT EXISTS `eb_system_timer` (
    `id` int(12) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '定时器名称',
    `mark` varchar(50) NOT NULL COMMENT '标识',
    `type` tinyint(1) NOT NULL COMMENT '周期状态 1=N分钟 2=N小时 3=每小时 4=每天 5=N天 6=每星期 7=每月 8=每年',
    `title` varchar(255) DEFAULT NULL COMMENT '任务说明',
    `is_open` tinyint(1) DEFAULT '0' COMMENT '是否开启',
    `cycle` varchar(255) DEFAULT NULL COMMENT '执行周期',
    `last_execution_time` int(12) DEFAULT '0' COMMENT '上次执行时间',
    `update_execution_time` int(12) DEFAULT '0' COMMENT '修改时间',
    `is_del` tinyint(1) DEFAULT '0' COMMENT '是否删除',
    `add_time` int(12) NOT NULL DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='定时器';

--
-- 转存表中的数据 `eb_system_timer`
--

INSERT INTO `eb_system_timer` (`name`, `mark`, `type`, `title`, `is_open`, `cycle`, `last_execution_time`, `update_execution_time`, `is_del`, `add_time`) VALUES
('自动确认收货', 'auto_take', 1, '', 1, '30', 0, 0, 0, 1669879367),
('自动好评', 'auto_comment', 1, '', 1, '30', 0, 0, 0, 1669879383),
('自动清空用户积分', 'auto_clear_integral', 1, '', 1, '59', 0, 0, 0, 1669879397),
('自动取消用户到期svip', 'auto_off_user_svip', 1, '', 1, '10', 0, 0, 0, 1669879443),
('自动解绑上下级', 'auto_agent', 1, '', 1, '10', 0, 0, 0, 1669879467),
('更新短信状态', 'auto_sms_code', 1, '', 1, '1', 0, 0, 0, 1669879517),
('定时创建发送朋友圈任务', 'auto_moment', 1, '', 1, '1', 0, 0, 0, 1669887925),
('定时发送群发任务', 'auto_group_task', 1, '', 1, '1', 0, 0, 0, 1669888005),
('渠道码定时任务', 'auto_channel', 1, '', 1, '1', 0, 0, 0, 1669888423),
('自动取消订单', 'auto_cancel', 1, '', 1, '20', 0, 0, 0, 1669967682),
('自动清除昨日海报', 'auto_clear_poster', 5, '', 1, '1/6/1', 0, 0, 0, 1669968148),
('自动更新直播产品状态和直播间状态', 'auto_live', 1, '', 1, '1', 0, 0, 0, 1669968223),
('拼团状态自动更新', 'auto_pink', 1, '', 1, '1', 0, 0, 0, 1669968274),
('自动上下架商品', 'auto_show', 1, '', 1, '1', 0, 0, 0, 1669968321),
('定时清理秒杀数据过期的数据缓存', 'auto_seckill', 1, '', 1, '1', 0, 0, 0, 1669968593);

--
-- 表的结构 `eb_store_product_relation`
--

CREATE TABLE IF NOT EXISTS `eb_store_product_relation` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` int(11) NOT NULL DEFAULT '1' COMMENT '关联关系1：分类2：品牌3：商品标签4：用户标签5：保障服务6：商品参数',
    `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
    `relation_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
    `relation_pid` int(11) NOT NULL DEFAULT '0' COMMENT '一级分类id',
    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品状态',
    `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `type` (`type`),
    KEY `relation_id` (`relation_id`),
    KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='商品关联辅助表';

ALTER TABLE `eb_user` ADD INDEX `phone` (`phone`);
alter table `eb_user` add index index_0 (`delete_time`);
alter table `eb_store_order` add index index_0 (`paid`, `pid`, `uid`, `refund_status`);
alter table `eb_store_order_cart_info` add index index_0 (`cart_id`, `refund_num`);

-- 修改订阅消息
UPDATE eb_template_message SET `notification_id` = '11,13,23' WHERE `tempkey` = '3098';
UPDATE eb_template_message SET `notification_id` = '11,23' WHERE `tempkey` = 'OPENTM409367318';
UPDATE eb_template_message SET `notification_id` = '13' WHERE `tempkey` = 'OPENTM410867947';
UPDATE eb_template_message SET `notification_id` = '5,17' WHERE `tempkey` in ('1451','OPENTM207284059');
UPDATE eb_template_message SET `notification_id` = '12,22' WHERE `tempkey` in ('3353','OPENTM418350969');
UPDATE eb_template_message SET `notification_id` = '14,15' WHERE `tempkey` = '1470';
UPDATE eb_template_message SET `notification_id` = '7,16' WHERE `tempkey` in ('755','OPENTM414089457');

UPDATE `eb_page_link` set `name` = '领取优惠券' where `url` = '/pages/users/user_get_coupon/index';

-- 商品评价增加sku
ALTER TABLE `eb_store_product_reply` ADD `sku_unique` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'sku唯一值' AFTER `product_id`, ADD `sku` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'sku名称' AFTER `sku_unique`;


-- 去掉会员线下折扣
DELETE FROM `eb_member_right` where `right_type` = 'offline';


