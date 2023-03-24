INSERT INTO `eb_system_config_tab` (`id`, `pid`, `title`, `eng_title`, `status`, `info`, `icon`, `type`, `sort`) VALUES (null, 22, 'PC配置', 'system_pc', 1, 0, '', 0, 0);




INSERT INTO `eb_system_config` (`id`, `menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES
(null, 'wechat_open_app_id', 'text', 'input', 71, NULL, NULL, '', 100, NULL, '', '开放平台appid', '开放平台appid', 0, 1),
(null, 'wechat_open_app_secret', 'text', 'input', 71, NULL, NULL, '', 100, NULL, '', '开放平台secret', '开放平台secret', 0, 1),
(null, 'contact_number', 'text', 'input', 71, NULL, NULL, '', 100, NULL, '', '联系电话', '联系电话', 0, 1),
(null, 'company_address', 'text', 'input', 71, NULL, NULL, '', 100, NULL, '', '公司地址', '公司地址', 0, 1),
(null, 'copyright', 'text', 'input', 71, NULL, NULL, '', 100, NULL, '', '版权信息', '版权信息', 0, 1),
(null, 'record_No', 'text', 'input', 71, NULL, NULL, '', 100, NULL, '', '备案号', '备案号', 0, 1),
(null, 'site_keywords', 'text', 'input', 71, NULL, NULL, NULL, 100, NULL, '', '关键词', '网站关键词', 0, 1),
(null, 'site_description', 'textarea', NULL, 71, NULL, NULL, NULL, 100, 5, '', '网站描述', '网站描述', 0, 1),
(null, 'product_phone_buy_url', 'radio', NULL, 71, '1=>公众号\n2=>小程序', NULL, NULL, NULL, NULL, '\"1\"', '商品手机购买跳转地址', '商品手机购买跳转地址（小程序|公众号）', 0, 1),
(null, 'pc_logo', 'upload', NULL, 71, NULL, 1, NULL, NULL, NULL, '', 'PC端LOGO', 'PC端LOGO', 0, 1),
(null, 'bast_number', 'text', 'number', 71, NULL, NULL, 'required:true,digits:true,min:1', 100, NULL, '4', '精品推荐个数', '首页配置精品推荐个数', 0, 1),
(null, 'first_number', 'text', 'number', 71, NULL, NULL, 'required:true,digits:true,min:1', 100, NULL, '4', '首发新品个数', '首页配置首发新品个数', 0, 1);