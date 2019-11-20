<?php
$arr = [
    //2019-10-25
    [
        "sql1" => "alter table articles_type add `cid` int(10) NOT NULL DEFAULT '0' COMMENT '父级id';",
        "sql2" => "alter table articles_type add `deleted_at` timestamp NULL DEFAULT NULL;",
        "sql3" => "alter table articles_type modify `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型 1：分类 2：专题';",
        "sql4" => "alter table articles modify `articles_type_id` varchar(400) DEFAULT NULL COMMENT '新闻类型id';",
        "sql5" => "alter table sys_version modify `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间';",
        "sql6" => "CREATE TABLE `modular` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL,
  `new_name` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `display` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否显示 0显示 1不显示',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_limit` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否限制 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模块插件表';",
        ],
    [
        "sql1" => "alter table articles_type add `icon` varchar(255) DEFAULT NULL COMMENT '图标';",
        "sql2" => "alter table articles add `picture_type` int(11) NOT NULL DEFAULT '0' COMMENT '图片类型 1：大图 2小图 3三图';",
        "sql3" => "alter table articles add `video_cover` varchar(255) DEFAULT NULL COMMENT '视频封面图';",
        ],
    [
        "sql" => "alter table street add `wx_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否显示 0：否 1：显示';",
        ],
    //2019-11-06
    [
        "sql1" => "INSERT INTO `admin_permissions` VALUES (225,'admin.wordOrder.transfer','转单管理','转单管理',205,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');",
        "sql2" => "INSERT INTO `admin_permissions` VALUES (226,'admin.staffWxapp.tabbar','底部导航栏配置-管理端','底部导航栏配置-管理端',180,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');",
        "sql3" => "INSERT INTO `admin_permissions` VALUES (227,'admin.agentWxapp.tabbar','底部导航栏配置-用户端','底部导航栏配置-用户端',180,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');",
        "sql4" => "INSERT INTO `home_page` VALUES (11,'导航','6',1,'2019-07-25 11:28:21',1);",
        "sql5" => "INSERT INTO `home_page` VALUES (12,'导航','6',1,'2019-07-25 11:28:21',2);",
        "sql6" => "alter table configs add `agent_tarbar_list` text COMMENT '客户底部导航栏';",
        "sql7" => "alter table configs add `admin_tarbar_list` text COMMENT '员工底部导航栏';",
        "sql8" => "alter table street modify `admin_id` varchar(100) DEFAULT NULL COMMENT '街道所属管理员id';",
        "sql9" => "CREATE TABLE `wxapp_page` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL COMMENT '路径',
  `name` varchar(255) DEFAULT NULL COMMENT '页面名称',
  `is_home` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否为主页 0：否 1是',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型 1：客户小程序 2：员工小程序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='小程序页面路径表';",
        "sql10" => "INSERT INTO `wxapp_page` VALUES (1,'/pages/agent/homepage/index/index','首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql11" => "INSERT INTO `wxapp_page` VALUES (2,'/pages/agent/center/index/index','个人中心',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql12" => "INSERT INTO `wxapp_page` VALUES (3,'/pages/agent/center/personal/personal','个人信息',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql13" => "INSERT INTO `wxapp_page` VALUES (4,'/pages/agent/center/album/index','我的相册',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql14" => "INSERT INTO `wxapp_page` VALUES (5,'/pages/agent/center/feedback/list/index','我的留言',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql15" => "INSERT INTO `wxapp_page` VALUES (6,'/pages/agent/center/about-us/about-us','关于我们',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql16" => "INSERT INTO `wxapp_page` VALUES (7,'/pages/agent/center/useHelp/useHelp','使用帮助',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql17" => "INSERT INTO `wxapp_page` VALUES (8,'/pages/agent/center/setting/setting','系统设置',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql18" => "INSERT INTO `wxapp_page` VALUES (9,'/pages/agent/plugins/activity/list/index','活动中心首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql19" => "INSERT INTO `wxapp_page` VALUES (10,'/pages/agent/plugins/activity/mine/index','已报名活动',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql20" => "INSERT INTO `wxapp_page` VALUES (11,'/pages/agent/plugins/business/index/index','业务商城首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql21" => "INSERT INTO `wxapp_page` VALUES (12,'/pages/agent/plugins/business/order/order','商城订单',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql22" => "INSERT INTO `wxapp_page` VALUES (13,'/pages/agent/plugins/wallet/record/record','钱包明细',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql23" => "INSERT INTO `wxapp_page` VALUES (14,'/pages/agent/plugins/wallet/recharge/recharge','快速充值',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql24" => "INSERT INTO `wxapp_page` VALUES (15,'/pages/agent/plugins/tencentCloud/searchOrder/searchOrder','腾讯云助手首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql25" => "INSERT INTO `wxapp_page` VALUES (16,'/pages/agent/plugins/tencentCloud/order/order','腾讯云订单',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql26" => "INSERT INTO `wxapp_page` VALUES (17,'/pages/agent/plugins/telephoneBook/list/index','通讯录首页',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql27" => "INSERT INTO `wxapp_page` VALUES (18,'/pages/agent/plugins/news/list/index','新闻资讯首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql28" => "INSERT INTO `wxapp_page` VALUES (19,'/pages/agent/plugins/activity/collection/index','收藏的活动',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql29" => "INSERT INTO `wxapp_page` VALUES (20,'/pages/agent/plugins/activity/like/index','点赞的活动',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql30" => "INSERT INTO `wxapp_page` VALUES (21,'/pages/agent/plugins/exam/list/index','考试培训首页',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql31" => "INSERT INTO `wxapp_page` VALUES (22,'/pages/agent/plugins/workOrder/index/index','反馈工单首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql32" => "INSERT INTO `wxapp_page` VALUES (23,'/pages/agent/plugins/enterprise/display/index','企业展示',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql33" => "INSERT INTO `wxapp_page` VALUES (24,'/pages/agent/plugins/enterprise/hotline/index','服务热线',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql34" => "INSERT INTO `wxapp_page` VALUES (25,'/pages/agent/plugins/video/list/list','视频中心',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql35" => "INSERT INTO `wxapp_page` VALUES (26,'','',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql36" => "INSERT INTO `wxapp_page` VALUES (27,'/pages/admin/homepage/homepage/homepage','销售统计',1,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql37" => "INSERT INTO `wxapp_page` VALUES (28,'/pages/admin/homepage/ranking-list/ranking-list','排行榜',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql38" => "INSERT INTO `wxapp_page` VALUES (29,'/pages/admin/center/center','个人中心',1,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql39" => "INSERT INTO `wxapp_page` VALUES (30,'/pages/admin/center/personal-info/personal-info','个人信息',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql40" => "INSERT INTO `wxapp_page` VALUES (31,'/pages/admin/center/wallet/wallet','我的钱包',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql41" => "INSERT INTO `wxapp_page` VALUES (32,'/pages/admin/center/about-us/about-us','关于我们',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql42" => "INSERT INTO `wxapp_page` VALUES (33,'/pages/admin/center/setting/index','系统设置',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql43" => "INSERT INTO `wxapp_page` VALUES (34,'/pages/admin/plugins/index/index','应用中心',1,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql44" => "INSERT INTO `wxapp_page` VALUES (35,'/pages/admin/plugins/customer/customer-list/customer-list','客户中心',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql45" => "INSERT INTO `wxapp_page` VALUES (36,'/pages/admin/plugins/exam/list/index','考试培训',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');",
        "sql46" => "DROP TABLE IF EXISTS `work_order_log`;",
        "sql47" => "CREATE TABLE `work_order_log` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL DEFAULT '0' COMMENT '类型 1：为客户id 2：为管理id',
  `log_id` int(11) NOT NULL DEFAULT '0' COMMENT '工单id',
  `type` int(11) DEFAULT NULL COMMENT '类型 1：客户补充 2：网格员补充',
  `remarks` varchar(400) DEFAULT NULL COMMENT '问题处理备注',
  `annex` varchar(300) DEFAULT NULL COMMENT '附件',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工单回复表';",
        "sql48" => "DROP TABLE IF EXISTS `work_order`;",
        "sql49" => "CREATE TABLE `work_order` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) DEFAULT '0' COMMENT '问题父级ID 默认为0(父级)备用字段',
  `member_id` int(11) DEFAULT NULL COMMENT '客户id',
  `description` text COMMENT '问题描述',
  `pic_list` text COMMENT '图片列表',
  `street_id` int(11) DEFAULT NULL COMMENT '街道父id',
  `c_street_id` int(11) DEFAULT NULL COMMENT '街道子id',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `type_id` int(11) DEFAULT NULL COMMENT '反馈类型id',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态 0：未接受 1：已接受，处理待 2：已处理，待确认 3：处理完成',
  `member_name` varchar(255) DEFAULT NULL,
  `member_phone` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `accept_time` timestamp NULL DEFAULT NULL COMMENT '接受时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '结束时间',
  `admin_id` int(11) DEFAULT NULL COMMENT '当前网格员id',
  `former_admin_id` int(11) DEFAULT NULL COMMENT '转单前，管理员id',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工单表';",
        "sql50" => "CREATE TABLE `transfer_work_order` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `original_member_id` int(11) DEFAULT NULL COMMENT '原网格员',
  `now_member_id` int(11) DEFAULT NULL COMMENT '指派网格员',
  `work_order_id` int(11) DEFAULT NULL COMMENT '工单id',
  `created_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL COMMENT '状态 1：待审核 2：拒绝 3：通过',
  `change_remarks` varchar(255) DEFAULT NULL COMMENT '转单备注',
  `verify_time` timestamp NULL DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='工单转移记录表';",
        ],
    //2019-11-18
    [
        "sql1" => "INSERT INTO `admin_permissions` VALUES (228,'admin.helpline','服务热线','服务热线',44,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');",
        "sql2" => "ALTER TABLE configs DROP COLUMN tencent_wechat_appid;",
        "sql3" => "ALTER TABLE configs DROP COLUMN tencent_wechat_secret;",
        "sql4" => "alter table configs add `agent_wechat_configs` text COMMENT '员工小程序配置';",
        "sql5" => "DROP TABLE IF EXISTS `service_hotline`;",
        "sql6" => "CREATE TABLE `service_hotline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `user_name` varchar(255) DEFAULT NULL COMMENT '负责人名称',
  `mobile` varchar(20) DEFAULT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态 1：显示 0：隐藏',
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='服务热线表';",
    ]
];





















?>