
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `achievement`;
CREATE TABLE `achievement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_name` varchar(50) DEFAULT NULL COMMENT '用户名',
  `member_phone` varchar(15) DEFAULT NULL COMMENT '用户电话',
  `admin_users_id` int(5) DEFAULT NULL COMMENT '销售ID',
  `goods_money` decimal(8,2) DEFAULT '0.00' COMMENT '商品金额',
  `order_bonus` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '订单提成',
  `sale_bonus` decimal(8,2) DEFAULT '0.00' COMMENT '订单奖金',
  `sbr_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_name` varchar(160) DEFAULT NULL COMMENT '商品名称',
  `order_number` varchar(50) DEFAULT NULL COMMENT '订单号',
  `after_sale_id` int(10) NOT NULL DEFAULT '0' COMMENT '售后ID',
  `remarks` varchar(150) DEFAULT NULL COMMENT '备注',
  `refuse_remarks` varchar(150) DEFAULT NULL COMMENT '拒绝备注',
  `sale_proof` varchar(255) DEFAULT NULL COMMENT '销售凭证',
  `status` tinyint(1) DEFAULT '0' COMMENT '0为初始 1为成功 2为拒绝 3为退款',
  `buy_time` timestamp NULL DEFAULT NULL COMMENT '购买时间',
  `buy_length` int(3) NOT NULL DEFAULT '12' COMMENT '购买时长 按月算',
  `ach_state` tinyint(1) DEFAULT '0' COMMENT '是否参加业绩计算 0为参加 1为不参加',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `verify_user_id` int(11) DEFAULT NULL COMMENT '审核人',
  `verify_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '审核时间',
  `member_id` int(11) DEFAULT NULL COMMENT '客户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '业绩表';

DROP TABLE IF EXISTS `admin_bonus_log`;
CREATE TABLE `admin_bonus_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_users_id` int(10) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为售前提成 1为售后提成 2为余额提现 3为售前退款 4为售后奖金 5为奖金提现 6增加余额 7减少余额 8增加奖金 9减少奖金',
  `money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `cur_bonus` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '当时总提成',
  `remarks` varchar(250) DEFAULT NULL COMMENT '记录备注',
  `member_name` varchar(50) DEFAULT NULL COMMENT '客户名称',
  `member_phone` varchar(30) DEFAULT NULL COMMENT '客户手机',
  `goods_name` varchar(80) DEFAULT NULL COMMENT '商品名称',
  `goods_money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '商品金额',
  `order_number` varchar(50) DEFAULT NULL COMMENT '订单号',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `submitter` varchar(255) DEFAULT NULL COMMENT '提交人',
  `auditor` varchar(255) DEFAULT NULL COMMENT '审核人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7895 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `admin_permission_role`;
CREATE TABLE `admin_permission_role` (
  `permission_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '角色权限表';


DROP TABLE IF EXISTS `admin_permissions`;
CREATE TABLE `admin_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '权限名',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '权限解释名称',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '描述与备注',
  `cid` int(10) NOT NULL COMMENT '级别',
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '图标',
  `display` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示 0显示 1不显示',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '权限表';

INSERT INTO `admin_permissions` VALUES ('1', 'admin.permission', '权限管理', '', '0', 'fa-users',0, 0, '2016-05-21 02:06:50', '2018-06-07 18:02:53');
INSERT INTO `admin_permissions` VALUES ('2', 'admin.permission.index', '权限列表', '', '1', '',0,0, '2016-05-21 02:08:04', '2016-05-21 02:08:04');
INSERT INTO `admin_permissions` VALUES ('3', 'admin.permission.create', '权限添加', '', '1', '',0,0, '2016-05-21 02:08:18', '2016-05-21 02:08:18');
INSERT INTO `admin_permissions` VALUES ('4', 'admin.permission.edit', '权限修改', '', '1', '',0,0, '2016-05-21 02:08:35', '2016-05-21 02:08:35');
INSERT INTO `admin_permissions` VALUES ('5', 'admin.permission.destroy ', '权限删除', '', '1', '',0,0, '2016-05-21 02:09:57', '2016-05-21 02:09:57');
INSERT INTO `admin_permissions` VALUES ('6', 'admin.role.index', '角色列表', '', '1', '',0,0, '2016-05-23 02:36:40', '2016-05-23 02:36:40');
INSERT INTO `admin_permissions` VALUES ('7', 'admin.role.create', '角色添加', '', '1', '',0,0, '2016-05-23 02:37:07', '2016-05-23 02:37:07');
INSERT INTO `admin_permissions` VALUES ('8', 'admin.role.edit', '角色修改', '', '1', '',0,0, '2016-05-23 02:37:22', '2016-05-23 02:37:22');
INSERT INTO `admin_permissions` VALUES ('9', 'admin.role.destroy', '角色删除', '', '1', '',0,0, '2016-05-23 02:37:48', '2016-05-23 02:37:48');
INSERT INTO `admin_permissions` VALUES ('10', 'admin.user.index', '系统用户', '', '1', '',0,0, '2016-05-23 02:38:52', '2018-06-12 17:18:08');
INSERT INTO `admin_permissions` VALUES ('11', 'admin.user.create', '管理员添加', '', '1', '',0,0, '2016-05-23 02:39:21', '2016-06-22 05:49:29');
INSERT INTO `admin_permissions` VALUES ('12', 'admin.user.edit', '管理员编辑', '', '1', '',0,0, '2016-05-23 02:39:52', '2016-05-23 02:39:52');
INSERT INTO `admin_permissions` VALUES ('13', 'admin.user.destroy', '管理员删除', '', '1', '',0,0, '2016-05-23 02:40:36', '2016-05-23 02:40:36');
INSERT INTO `admin_permissions` VALUES ('14', 'admin.goods', '商品管理', '', '0', 'fa-sliders',0,0, '2018-05-24 09:58:48', '2018-05-24 09:58:48');
INSERT INTO `admin_permissions` VALUES ('15', 'admin.goods.index', '商品列表', '', '14', '',0,0, '2018-05-24 10:09:57', '2018-05-24 10:09:57');
INSERT INTO `admin_permissions` VALUES ('16', 'admin.goods.create', '商品添加', '', '14', '',0,0, '2018-05-25 10:00:17', '2018-05-25 10:00:17');
INSERT INTO `admin_permissions` VALUES ('17', 'admin.goods.edit', '商品修改', '', '14', '',0,0, '2018-05-25 10:00:34', '2018-05-25 10:00:34');
INSERT INTO `admin_permissions` VALUES ('18', 'admin.goods.destroy', '商品删除', '', '14', '',0,0, '2018-05-25 10:01:06', '2018-05-25 10:01:06');
INSERT INTO `admin_permissions` VALUES ('19', 'admin.index', '控制面板', '', '0', 'fa-tachometer',0,0, '2018-05-25 10:01:47', '2018-05-25 10:01:47');
INSERT INTO `admin_permissions` VALUES ('20', 'admin.index.index', '首页', '', '19', '',0,0, '2018-05-25 10:02:03', '2018-06-12 17:10:50');
INSERT INTO `admin_permissions` VALUES ('21', 'admin.branch.index', '分部列表', '', '1', '',0,0, '2018-05-28 05:43:31', '2018-05-28 05:43:31');
INSERT INTO `admin_permissions` VALUES ('22', 'admin.workstatus.index', '打卡签到', '', '19', '',0,0, '2018-05-28 06:36:50', '2018-05-28 06:36:50');
INSERT INTO `admin_permissions` VALUES ('23', 'admin.case.index', '案例列表', '', '14', '',0,0, '2018-05-28 09:55:22', '2018-05-28 09:55:22');
INSERT INTO `admin_permissions` VALUES ('24', 'admin.case.create', '案例添加', '', '14', '',0,0, '2018-05-28 10:16:13', '2018-05-28 10:16:13');
INSERT INTO `admin_permissions` VALUES ('25', 'admin.case.edit', '案例修改', '', '14', '',0,0, '2018-05-28 10:16:33', '2018-05-28 10:16:33');
INSERT INTO `admin_permissions` VALUES ('26', 'admin.case.destroy', '案例删除', '', '14', '',0,0, '2018-05-28 10:16:52', '2018-05-28 10:16:52');
INSERT INTO `admin_permissions` VALUES ('27', 'admin.recovery.index', '商品回收站', '', '14', '',0,0, '2018-05-28 10:18:07', '2018-05-28 10:18:07');
INSERT INTO `admin_permissions` VALUES ('28', 'admin.member', '客户管理', '', '0', 'fa-users',0,0, '2018-05-29 02:50:41', '2018-05-29 02:50:41');
INSERT INTO `admin_permissions` VALUES ('29', 'admin.member.index', '我的客户', '', '28', '',0,0, '2018-05-29 02:51:06', '2018-05-29 02:51:06');
INSERT INTO `admin_permissions` VALUES ('30', 'admin.member.create', '客户添加', '', '28', '',0,0, '2018-05-29 07:34:31', '2018-05-29 07:34:31');
INSERT INTO `admin_permissions` VALUES ('31', 'admin.member.edit', '客户修改', '', '28', '',0,0, '2018-05-29 07:34:50', '2018-05-29 07:34:50');
INSERT INTO `admin_permissions` VALUES ('32', 'admin.member.destroy', '客户删除', '', '28', '',0,0, '2018-05-29 07:35:06', '2018-05-29 07:35:06');
INSERT INTO `admin_permissions` VALUES ('33', 'admin.member.moneyedit', '修改余额', '', '28', '',0,0, '2018-05-29 07:50:09', '2018-05-29 07:50:32');
INSERT INTO `admin_permissions` VALUES ('34', 'admin.memberlevel.index', '等级设置', '', '88', '',0,0, '2018-05-29 09:37:01', '2018-05-29 09:37:01');
INSERT INTO `admin_permissions` VALUES ('35', 'admin.memberlevel.create', '等级添加', '', '88', '',0,0, '2018-05-29 09:49:01', '2018-05-29 09:49:01');
INSERT INTO `admin_permissions` VALUES ('36', 'admin.memberlevel.edit', '等级修改', '', '88', '',0,0, '2018-05-29 09:49:22', '2018-05-29 09:49:22');
INSERT INTO `admin_permissions` VALUES ('37', 'admin.memberlevel.destroy', '等级删除', '', '88', '',0,0, '2018-05-29 09:49:59', '2018-05-29 09:49:59');
INSERT INTO `admin_permissions` VALUES ('38', 'admin.membersource.index', '来源设置', '', '88', '',0,0, '2018-05-29 10:06:45', '2018-05-29 10:06:45');
INSERT INTO `admin_permissions` VALUES ('39', 'admin.order', '订单管理', '', '0', 'fa-reorder',0,0, '2018-05-29 11:12:38', '2018-05-29 11:12:38');
INSERT INTO `admin_permissions` VALUES ('40', 'admin.order.index', '订单列表', '', '39', '',0,0, '2018-05-29 11:12:57', '2018-05-29 11:12:57');
INSERT INTO `admin_permissions` VALUES ('41', 'admin.order.create', '订单添加', '', '39', '',0,0, '2018-06-01 02:41:10', '2018-06-01 02:41:10');
INSERT INTO `admin_permissions` VALUES ('42', 'admin.order.edit', '订单修改', '', '39', '',0,0, '2018-06-01 02:42:25', '2018-06-01 02:42:25');
INSERT INTO `admin_permissions` VALUES ('43', 'admin.order.destroy', '订单删除', '', '39', '',0,0, '2018-06-01 02:42:48', '2018-06-01 02:42:48');
INSERT INTO `admin_permissions` VALUES ('44', 'admin.article', '文章管理', '', '0', 'fa-newspaper-o',0,0, '2018-06-01 03:14:31', '2018-06-01 03:14:31');
INSERT INTO `admin_permissions` VALUES ('45', 'admin.articles.index', '新闻列表', '', '44', '',0,0, '2018-06-01 03:16:38', '2018-06-01 03:29:29');
INSERT INTO `admin_permissions` VALUES ('46', 'admin.articles.create', '新闻添加', '', '44', '',0,0, '2018-06-01 03:30:02', '2018-06-01 03:30:41');
INSERT INTO `admin_permissions` VALUES ('47', 'admin.articles.edit', '新闻修改', '', '44', '',0,0, '2018-06-01 03:30:33', '2018-06-01 07:26:33');
INSERT INTO `admin_permissions` VALUES ('48', 'admin.articles.destroy', '新闻删除', '', '44', '',0,0, '2018-06-01 03:31:31', '2018-06-01 07:26:42');
INSERT INTO `admin_permissions` VALUES ('49', 'admin.help.index', '使用帮助', '', '44', '',0,0, '2018-06-01 03:32:32', '2018-06-01 03:32:32');
INSERT INTO `admin_permissions` VALUES ('50', 'admin.about.index', '关于我们', '', '44', '',0,0, '2018-06-01 03:32:54', '2018-06-01 03:32:54');
INSERT INTO `admin_permissions` VALUES ('51', 'admin.log.index', '更新日志', '', '44', '',0,0, '2018-06-01 03:33:16', '2018-06-01 03:33:16');
INSERT INTO `admin_permissions` VALUES ('52', 'admin.finance', '财务管理', '', '0', 'fa-diamond',0,0, '2018-06-01 08:36:34', '2018-06-01 08:36:34');
INSERT INTO `admin_permissions` VALUES ('53', 'admin.wallet.index', '资金明细', '', '52', '',0,0, '2018-06-01 08:36:58', '2018-06-01 08:36:58');
INSERT INTO `admin_permissions` VALUES ('54', 'admin.donation.index', '赠送金明细', '', '52', '',0,0, '2018-06-01 09:32:13', '2018-06-01 09:32:13');
INSERT INTO `admin_permissions` VALUES ('55', 'admin.customer', '客户资源', '', '0', 'fa-user-md',0,0, '2018-06-01 09:36:25', '2018-06-01 09:36:25');
INSERT INTO `admin_permissions` VALUES ('56', 'admin.customer.index', '我的客户资源', '', '55', '',0,0, '2018-06-01 09:36:55', '2018-06-01 09:36:55');
INSERT INTO `admin_permissions` VALUES ('57', 'admin.problem', '问题反馈', '', '0', 'fa-mail-reply-all',0,0, '2018-06-05 09:54:51', '2018-06-05 09:54:51');
INSERT INTO `admin_permissions` VALUES ('58', 'admin.problem.index', '日常问题', '', '57', '',0,0, '2018-06-05 09:55:16', '2018-06-05 09:55:16');
INSERT INTO `admin_permissions` VALUES ('59', 'admin.problem.create', '问题添加', '', '57', '',0,0, '2018-06-05 10:01:37', '2018-06-05 10:01:37');
INSERT INTO `admin_permissions` VALUES ('60', 'admin.problem.edit', '问题修改', '', '57', '',0,0, '2018-06-05 10:01:55', '2018-06-05 10:01:55');
INSERT INTO `admin_permissions` VALUES ('61', 'admin.problem.destroy', '问题删除', '', '57', '',0,0, '2018-06-05 10:02:33', '2018-06-05 10:02:33');
INSERT INTO `admin_permissions` VALUES ('80', 'admin.customer.create', '客户资源添加', '', '55', '',0,0, '2018-06-07 18:10:32', '2018-06-07 18:10:32');
INSERT INTO `admin_permissions` VALUES ('81', 'admin.customer.edit', '客户资源修改', '', '55', '',0,0, '2018-06-07 18:10:51', '2018-06-07 18:10:51');
INSERT INTO `admin_permissions` VALUES ('82', 'admin.customer.destroy', '客户资源删除', '', '55', '',0,0, '2018-06-07 18:11:08', '2018-06-07 18:11:08');
INSERT INTO `admin_permissions` VALUES ('83', 'admin.achievement.index', '业绩订单', '', '39', '',0,0, '2018-06-08 11:39:25', '2018-06-08 11:39:25');
INSERT INTO `admin_permissions` VALUES ('84', 'admin.globaldata.index', '业绩排行榜', '', '19', '',0,0, '2018-06-08 16:52:14', '2018-06-08 16:52:14');
INSERT INTO `admin_permissions` VALUES ('85', 'admin.index.home', '登录初始化', '', '19', '',0,0, '2018-06-12 17:12:07', '2018-06-12 17:12:07');
INSERT INTO `admin_permissions` VALUES ('86', 'admin.member.goods', '业绩商品', '', '28', '',0,0, '2018-06-12 21:14:35', '2018-06-12 21:14:35');
INSERT INTO `admin_permissions` VALUES ('88', 'admin.configs', '系统设置', '', '0', 'fa-cog',0,0, '2018-06-23 11:00:50', '2018-06-23 11:00:50');
INSERT INTO `admin_permissions` VALUES ('89', 'admin.configs.index', '站点信息', '', '88', '',0,0, '2018-06-23 11:01:21', '2018-06-23 11:01:21');
INSERT INTO `admin_permissions` VALUES ('90', 'admin.salebonusrule.index', '提成设置', '', '88', '',0,0, '2018-06-23 11:09:45', '2018-06-23 11:09:45');
INSERT INTO `admin_permissions` VALUES ('91', 'admin.aftersale.index', '售后订单', '', '39', '',0,0, '2018-06-24 20:37:59', '2018-06-24 20:37:59');
INSERT INTO `admin_permissions` VALUES ('92', 'admin.achievement.create', '业绩增加', '', '39', '',0,0, '2018-06-26 11:52:21', '2018-06-26 11:52:21');
INSERT INTO `admin_permissions` VALUES ('93', 'admin.achievement.edit', '业绩修改', '', '39', '',0,0, '2018-06-26 11:52:38', '2018-06-26 11:52:38');
INSERT INTO `admin_permissions` VALUES ('94', 'admin.achievement.destroy', '业绩删除', '', '39', '',0,0, '2018-06-26 11:52:52', '2018-06-26 11:52:52');
INSERT INTO `admin_permissions` VALUES ('95', 'admin.aftersale.create', '售后增加', '', '39', '',0,0, '2018-06-29 17:59:15', '2018-06-29 17:59:15');
INSERT INTO `admin_permissions` VALUES ('96', 'admin.aftersale.edit', '售后修改', '', '39', '',0,0, '2018-06-29 18:00:03', '2018-06-29 18:00:03');
INSERT INTO `admin_permissions` VALUES ('97', 'admin.aftersale.destroy', '售后删除', '', '39', '',0,0, '2018-06-29 18:01:01', '2018-06-29 18:01:01');
INSERT INTO `admin_permissions` VALUES ('98', 'admin.achievement.export', '业绩导出', '', '39', '',0,0, '2018-07-12 17:55:32', '2018-07-12 17:55:32');
INSERT INTO `admin_permissions` VALUES ('99', 'admin.siteconfig.index', '站点设置', '', '88', '',0,0, '2018-07-12 17:56:00', '2018-07-12 17:56:00');
INSERT INTO `admin_permissions` VALUES ('100', 'admin.project.index', '项目设置', '', '88', '',0,0, '2018-07-12 18:02:41', '2018-07-12 18:02:41');
INSERT INTO `admin_permissions` VALUES ('101', 'admin.achievement.examine', '业绩审核', '', '39', '',0,0, '2018-07-16 19:01:46', '2018-07-16 19:01:46');
INSERT INTO `admin_permissions` VALUES ('102', 'admin.customer.assign', '客户资源指派', '', '55', '',0, 0, '2018-07-18 16:43:15', '2018-07-18 16:43:15');
INSERT INTO `admin_permissions` VALUES ('103', 'admin.takemoney.index', '提现管理', '', '52', '',0,0, '2018-07-18 16:43:15', '2018-07-18 16:43:15');
INSERT INTO `admin_permissions` VALUES ('104', 'admin.takemoney.create', '提现', '', '52', '',0,0, '2018-07-18 16:43:15', '2018-07-18 16:43:15');
INSERT INTO `admin_permissions` VALUES ('105', 'admin.takemoney.edit', '提现审核', '', '52', '',0,0, '2018-07-18 16:43:15', '2018-07-18 16:43:15');
INSERT INTO `admin_permissions` VALUES ('106', 'admin.bonuslog.index', '提成记录', '', '52', '',0,0, '2018-08-02 15:37:11', '2018-08-02 15:37:11');
INSERT INTO `admin_permissions` VALUES ('107', 'admin.customer.excel', '客户资源导入导出', '', '55', '',0,0, '2018-08-17 16:28:58', '2018-08-17 16:28:58');
INSERT INTO `admin_permissions` VALUES ('108', 'admin.company.index', '公司设置', '', '88', '',0,0, '2018-08-27 14:51:20', '2018-08-27 14:51:20');
INSERT INTO `admin_permissions` VALUES ('109', 'admin.sysupdate.index', 'CRM版本', '', '88', '',0,0, '2018-10-26 19:15:11', '2018-10-26 19:15:11');
INSERT INTO `admin_permissions` VALUES ('110', 'admin.achievement.updaterecord', '更新订单信息', '', '39', '',0, 0, '2018-10-26 19:15:47', '2018-10-26 19:15:47');
INSERT INTO `admin_permissions` VALUES ('111', 'admin.achievement.updatedetail', '更新订单详情', '', '39', '',0, 0, '2018-10-26 19:15:47', '2018-10-26 19:15:47');
INSERT INTO `admin_permissions` VALUES ('112', 'admin.hr', '人事管理', '人事管理列表', '0', 'fa-steam',0, 0, '2018-10-26 19:15:47', '2018-10-26 19:15:47');
INSERT INTO `admin_permissions` VALUES ('113', 'admin.hr.index', '员工列表', '', '112', '',0, 0, '2018-10-26 19:15:47', '2018-10-26 19:15:47');
INSERT INTO `admin_permissions` VALUES ('114', 'admin.hr.edit', '员工资料编辑', '', '112', '',0,0, '2018-10-26 19:15:47', '2018-10-26 19:15:47');
INSERT INTO `admin_permissions` VALUES ('115', 'admin.achievement.exchangeorder', '业绩订单转移', '', '39', '',0, 0, '2018-10-26 19:15:47', '2018-10-26 19:15:47');
INSERT INTO `admin_permissions` VALUES ('116', 'admin.aftersale.exchangeorder', '售后订单转移', '', '39', '',0, 0, '2018-10-26 19:15:47', '2018-10-26 19:15:47');
INSERT INTO `admin_permissions` VALUES ('117', 'admin.workorder.index', '工单列表', '', '57', '',0, 0, '2018-10-23 14:56:04', '2018-10-23 14:56:04');
INSERT INTO `admin_permissions` VALUES ('118', 'admin.workorder.edit', '工单处理', '', '57', '',0, 0, '2018-10-23 14:56:04', '2018-10-23 14:56:04');
INSERT INTO `admin_permissions` VALUES ('119', 'personalInformation', '个人人事信息', '查看个人人事信息', '112', '',0, 0, '2019-01-15 18:50:06', '2019-01-15 18:50:06');
INSERT INTO `admin_permissions` VALUES ('123', 'admin.tencent','腾讯云','腾讯云模块','0','',1,0,'2019-03-11 17:54:14','2019-03-25 17:08:33');
INSERT INTO `admin_permissions` VALUES ('124', 'admin.tencent.customer','腾讯云客户','腾讯云客户','123','',1,0,'2019-03-11 17:55:36','2019-03-11 17:55:36');
INSERT INTO `admin_permissions` VALUES ('125', 'admin.tencent.order','腾讯云订单','腾讯云订单','123','',1,0,'2019-03-11 17:56:07','2019-03-11 17:56:07');
INSERT INTO `admin_permissions` VALUES ('126', 'admin.tencent.config','腾讯云配置','腾讯云配置','123','',1,0,'2019-03-11 17:56:37','2019-03-11 17:56:37');
INSERT INTO `admin_permissions` VALUES ('127', 'admin.wallet','我的钱包','我的钱包','0','',0,0,'2019-05-18 17:56:37','2019-05-18 17:56:37');
INSERT INTO `admin_permissions` VALUES ('128', 'admin.wallet.details','钱包明细','钱包明细','127','',0,0,'2019-05-18 17:56:37','2019-05-18 17:56:37');
INSERT INTO `admin_permissions` VALUES ('129', 'admin.wallet.bonus','预期奖金列表','预期奖金列表','127','',0,0,'2019-05-18 17:56:37','2019-05-18 17:56:37');
INSERT INTO `admin_permissions` VALUES ('130', 'admin.wallet.bonus.get','奖金提现','奖金提现','127','',0,0,'2019-05-18 17:56:37','2019-05-18 17:56:37');
INSERT INTO `admin_permissions` VALUES ('131', 'admin.tencent.power','权限管理','权限管理','123','',1,0,'2019-05-18 17:56:37','2019-05-18 17:56:37');
INSERT INTO `admin_permissions` VALUES ('132', 'admin.tencent.customerOrder','腾讯云订单','腾讯云订单','123','',1,0,'2019-05-18 17:56:37','2019-05-18 17:56:37');
INSERT INTO `admin_permissions` VALUES ('133', 'admin.plugin.index','插件设置','插件设置','88','',0,0,'2019-05-18 17:56:37','2019-05-18 17:56:37');
INSERT INTO `admin_permissions` VALUES ('134', 'admin.product.productType','产品类型','产品类型','14','',0,0,'2019-05-18 17:56:37','2019-05-18 17:56:37');
INSERT INTO `admin_permissions` VALUES (135,'admin.activity','活动报名','活动报名',0,'',1,0,'2019-07-09 11:16:30','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (136,'admin.activity.list','活动列表','活动列表',135,'',1,0,'2019-07-09 11:18:24','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (137,'admin.activity.typeList','活动类型','活动类型列表',135,'',1,0,'2019-07-09 11:19:12','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (138,'admin.activity.comment','活动留言','活动留言列表',135,'',1,0,'2019-07-09 11:21:11','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (139,'admin.activity.create','创建活动','创建活动',135,'',1,0,'2019-07-09 11:23:15','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (140,'admin.activity.modify','编辑活动','编辑活动',135,'',1,0,'2019-07-09 11:24:13','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (141,'admin.activity.cancel','取消活动','取消活动',135,'',1,0,'2019-07-09 11:24:29','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (142,'admin.activity.delete','删除活动','删除活动',135,'',1,0,'2019-07-09 11:24:47','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (143,'admin.activity.reviewMember','审核用户','审核申请参加活动的用户',135,'',1,0,'2019-07-09 11:27:17','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (144,'admin.activity.reviewComment','审核评论','审核参加活动的用户的评论',135,'',1,0,'2019-07-09 11:27:17','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (145,'admin.activity.exportJoinMenber','导出参加者','导出参加者',135,'',1,0,'2019-07-09 11:27:17','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (146,'admin.memberManage','会员管理','会员管理',0,'',1,0,'2019-07-09 11:34:44','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (147,'admin.memberManage.list','会员列表','会员列表',146,'',1,0,'2019-07-09 11:36:07','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (148,'admin.memberManage.type','会员类型','会员类型',146,'',1,0,'2019-07-09 11:36:37','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (149,'amdin.memberManage.create','添加会员','添加会员',146,'',1,0,'2019-07-09 11:37:38','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (150,'admin.memberManage.edit','编辑会员','编辑会员',146,'',1,0,'2019-07-09 11:38:03','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (151,'admin.memberManage.delete','会员删除','会员删除',146,'',1,0,'2019-07-09 11:38:49','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (152,'admin.memberManage.import','导入会员','导入会员',146,'',1,0,'2019-07-09 11:39:21','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (153,'admin.exam','考试培训','考试培训',0,'',1,0,'2019-07-09 11:42:25','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (154,'admin.memberManage.details','会员详情','会员详情',146,'',1,0,'2019-07-09 11:48:31','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (155,'admin.exam.statistics','数据统计','考试数据统计',153,'',1,0,'2019-07-09 13:38:29','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (156,'admin.exam.analysis','试题分析','考试试题分析',153,'',1,0,'2019-07-09 13:38:58','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (157,'admin.exam.list','考试列表','考试列表',153,'',1,0,'2019-07-09 13:39:41','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (158,'admin.exam.create','创建考试','创建考试',153,'',1,0,'2019-07-09 13:40:18','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (159,'admin.exam.examinee','考生管理','考生管理',153,'',1,0,'2019-07-09 14:55:53','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (160,'admin.lookarticles.index','类型列表','--',44,'',0,0,'2019-07-09 15:16:21','2019-07-12 12:04:40');
INSERT INTO `admin_permissions` VALUES (161,'admin.lookarticles.create','添加类型','--',44,'',0,0,'2019-07-09 15:17:07','2019-07-12 12:04:40');
INSERT INTO `admin_permissions` VALUES (162,'admin.lookarticles.edit','修改类型','--',44,'',0,0,'2019-07-09 15:17:31','2019-07-12 12:04:40');
INSERT INTO `admin_permissions` VALUES (163,'admin.lookarticles.destroy','删除类型','--',44,'',0,0,'2019-07-09 15:17:57','2019-07-12 12:04:40');
INSERT INTO `admin_permissions` VALUES (164,'admin.articles.wxappPlugin','新闻模块小程序插件','通过这个控制是否安装小程序新闻插件',174,'',1,0,'2019-07-09 17:26:49','2019-07-12 12:04:40');
INSERT INTO `admin_permissions` VALUES (165,'admin.tencent.wxappPlugin','腾讯云小程序插件','通过这个控制是否安装小程序腾讯云插件',174,'',1,0,'2019-07-09 17:36:42','2019-07-12 12:04:49');
INSERT INTO `admin_permissions` VALUES (166,'admin.activity.wxappPlugin','活动报名小程序插件','通过这个控制是否安装小程序活动插件',174,'',1,0,'2019-07-09 17:37:30','2019-07-12 12:04:52');
INSERT INTO `admin_permissions` VALUES (167,'admin.memberManage.wxappPlugin','会员管理小程序插件','通过这个控制是否安装小程序会员管理(通讯录)插件',174,'',1,0,'2019-07-09 17:38:31','2019-07-12 14:27:36');
INSERT INTO `admin_permissions` VALUES (168,'admin.exam.wxappPlugin','考试培训小程序插件','通过这个控制是否安装小程序考试培训插件',174,'',1,0,'2019-07-09 17:39:12','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (169,'admin.member.authentication','客户认证','客户认证',28,'',0,0,'2019-07-10 09:16:33','2019-07-12 12:04:36');
INSERT INTO `admin_permissions` VALUES (170,'admin.customer.overdue','逾期客户列表','逾期客户列表',55,'',0,0,'2019-07-10 18:21:55','2019-07-12 12:04:44');
INSERT INTO `admin_permissions` VALUES (171,'admin.customer.lose','丢单客户列表','丢单客户列表',55,'',0,0,'2019-07-10 18:23:28','2019-07-12 12:04:44');
INSERT INTO `admin_permissions` VALUES (172,'admin.exam.questions','题库管理','题库管理',153,'',1,0,'2019-07-11 16:53:19','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (173,'admin.exam.paper','试卷管理','试卷管理',153,'',1,0,'2019-07-11 16:53:54','2019-07-12 14:27:39');
INSERT INTO `admin_permissions` VALUES (174,'admin.wxappPlugin','小程序插件管理','小程序插件管理',0,'',1,0,'2019-07-25 09:50:59','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (175,'admin.wxappPlugin.shop','商城业务','小程序商城',174,'',1,0,'2019-07-25 09:52:11','2019-07-25 10:12:06');
INSERT INTO `admin_permissions` VALUES (176,'admin.wxappPlugin.recharge','快速充值','快速充值',174,'',1,0,'2019-07-25 09:53:03','2019-07-25 10:06:52');
INSERT INTO `admin_permissions` VALUES (177,'admin.wxappPlugin.wallet','我的钱包','钱包',174,'',1,0,'2019-07-25 09:53:54','2019-07-25 10:12:14');
INSERT INTO `admin_permissions` VALUES (178,'admin.wxappPlugin.order','订单管理','订单管理',174,'',1,0,'2019-07-25 09:54:29','2019-07-25 10:06:58');
INSERT INTO `admin_permissions` VALUES (179,'admin.wxappPlugin.workOrder','提交工单','提交工单',174,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (180,'admin.wxapp','小程序管理','小程序管理',0,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (181,'admin.wxapp.staff','员工小程序设置','员工小程序设置',180,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (182,'admin.wxapp.agent','代理商小程序','代理商小程序',180,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (183,'admin.adminWxappPlugin','内部小程序插件管理','内部小程序插件管理',0,'',1,0,'2019-08-15 09:25:08','2019-09-12 09:59:29');
INSERT INTO `admin_permissions` VALUES (184,'admin.adminWxappPlugin.customer','客户中心','客户中心',183,'',1,0,'2019-08-15 09:26:15','2019-09-12 09:59:29');
INSERT INTO `admin_permissions` VALUES (185,'admin.adminWxappPlugin.exam','考试培训','考试培训',183,'',1,0,'2019-08-15 09:26:46','2019-09-12 09:59:29');
INSERT INTO `admin_permissions` VALUES (186,'admin.user.wallet','系统用户钱包明细','员工钱包明细',1,'',1,0,'2019-08-16 10:35:05','2019-09-12 10:13:57');
INSERT INTO `admin_permissions` VALUES (187,'admin.wallet.modify','余额修改( 慎用 )','慎用,仅管理员可提供该权限',127,'',1,0,'2019-08-16 10:38:50','2019-09-12 09:59:32');
INSERT INTO `admin_permissions` VALUES (188,'admin.exam.stop','停止考试','停止考试',153,'',1,0,'2019-08-16 11:06:12','2019-08-19 09:52:43');
INSERT INTO `admin_permissions` VALUES (189,'admin.exam.delete','删除考试','删除考试',153,'',1,0,'2019-08-16 11:06:43','2019-08-19 09:52:43');
INSERT INTO `admin_permissions` VALUES (190,'admin.exam.analysis','考试答卷详情','考试考生答卷详情统计',153,'',1,0,'2019-08-16 11:07:58','2019-08-19 09:52:44');
INSERT INTO `admin_permissions` VALUES (191,'admin.examinee.edit','考生编辑','考生信息编辑',153,'',1,0,'2019-08-16 11:10:08','2019-08-19 09:52:45');
INSERT INTO `admin_permissions` VALUES (192,'admin.examinee.assign','考生分配','考生分配到分组',153,'',1,0,'2019-08-16 11:11:29','2019-08-19 09:52:46');
INSERT INTO `admin_permissions` VALUES (193,'admin.examinee.team','考生分组管理','考生分组管理',153,'',1,0,'2019-08-16 11:11:52','2019-08-19 09:52:47');
INSERT INTO `admin_permissions` VALUES (194,'admin.examPaper.create','试卷创建','试卷创建',153,'',1,0,'2019-08-16 11:13:37','2019-08-19 09:52:48');
INSERT INTO `admin_permissions` VALUES (195,'admin.examPaper.export','试卷导出','试卷导出',153,'',1,0,'2019-08-16 11:14:02','2019-08-19 09:52:48');
INSERT INTO `admin_permissions` VALUES (196,'admin.examPaper.modify','试卷修改','试卷修改',153,'',1,0,'2019-08-16 11:14:56','2019-08-19 09:52:49');
INSERT INTO `admin_permissions` VALUES (197,'admin.examPaper.question','试卷题目管理','试卷题目管理',153,'',1,0,'2019-08-16 11:15:22','2019-08-19 09:52:50');
INSERT INTO `admin_permissions` VALUES (198,'admin.examPaper.type','试卷分类管理','试卷分类管理',153,'',1,0,'2019-08-16 11:16:10','2019-08-19 09:52:50');
INSERT INTO `admin_permissions` VALUES (199,'admin.member.afterSales','售后管理','售后管理',28,'',1,0,'2019-08-19 09:59:36','2019-09-04 11:59:00');
INSERT INTO `admin_permissions` VALUES (200,'admin.member.toAgent','跳转代理商端','跳转代理商端',28,'',1,0,'2019-08-19 10:00:08','2019-09-12 10:18:23');
INSERT INTO `admin_permissions` VALUES (201,'admin.operation.index','平台运营数据','平台运营数据',19,'',1,0,'2019-08-19 10:00:17','2019-09-12 10:28:30');
INSERT INTO `admin_permissions` VALUES (202,'admin.user.toUser','跳转系统用户账号','跳转系统用户账号',1,'',1,0,'2019-08-26 16:58:41','2019-09-12 10:12:32');
INSERT INTO `admin_permissions` VALUES (203,'admin.member.modifyLevel','客户等级修改','客户等级修改',28,'',1,0,'2019-09-06 15:22:11',NULL);
INSERT INTO `admin_permissions` VALUES (204,'admin.update.stopWeb','更新时是否启用停站功能','更新时是否启用停站功能',1,'',1,0,'2019-09-06 15:25:11','2019-09-06 15:25:30');
INSERT INTO `admin_permissions` VALUES (205,'admin.workOrder','工单管理','工单管理',0,'',1,0,'2019-09-11 14:52:05','2019-09-11 15:01:54');
INSERT INTO `admin_permissions` VALUES (206,'admin.workOrder.list','工单列表','工单列表',205,'',1,0,'2019-09-11 14:55:54','2019-09-11 15:01:52');
INSERT INTO `admin_permissions` VALUES (207,'admin.workOrder.staff','服务人员管理','服务人员管理',205,'',1,0,'2019-09-11 14:57:58','2019-09-11 15:01:52');
INSERT INTO `admin_permissions` VALUES (208,'admin.workOrder.area','服务范围管理','服务范围管理',205,'',1,0,'2019-09-11 14:58:35','2019-09-11 15:01:53');
INSERT INTO `admin_permissions` VALUES (209,'admin.workOrder.statistics','数据分析','数据分析',205,'',1,0,'2019-09-11 14:59:18','2019-09-11 15:01:54');
INSERT INTO `admin_permissions` VALUES (210,'admin.workOrder.serviceType','问题类别管理','问题类别管理',205,'',1,0,'2019-09-11 15:01:16','2019-09-11 15:01:54');
INSERT INTO `admin_permissions` VALUES (211,'admin.enterprise.display','企业展示','企业展示',44,'',1,0,'2019-09-11 15:03:41','2019-09-11 15:05:38');
INSERT INTO `admin_permissions` VALUES (212,'admin.enterprise.edit','企业展示编辑','企业展示编辑',44,'',1,0,'2019-09-11 15:04:20','2019-09-11 15:05:39');
INSERT INTO `admin_permissions` VALUES (213,'admin.wxappPlugin.enterpriseDisplay','企业展示','企业展示',174,'',1,0,'2019-09-11 16:07:17','2019-09-11 16:15:17');
INSERT INTO `admin_permissions` VALUES (214,'admin.wxappPlugin.feedback','反馈工单','反馈工单',174,'',1,0,'2019-09-11 16:08:25','2019-09-11 16:15:18');
INSERT INTO `admin_permissions` VALUES (215,'admin.wxappPlugin.hotline','服务热线','服务热线',174,'',1,0,'2019-09-11 16:09:32','2019-09-11 16:15:18');
INSERT INTO `admin_permissions` VALUES (216,'admin.setting.qyWechat','站点设置-企业微信','站点设置-企业微信',88,'',1,0,'2019-09-12 10:06:04',NULL);
INSERT INTO `admin_permissions` VALUES (217,'admin.setting.bonus','站点设置-奖金设置','站点设置-奖金设置',88,'',1,0,'2019-09-12 10:07:32',NULL);
INSERT INTO `admin_permissions` VALUES (218,'admin.user.transferOrder','系统用户订单转移','离职人员订单转移',1,'',1,0,'2019-09-12 10:14:53',NULL);
INSERT INTO `admin_permissions` VALUES (219,'admin.information.lsit','信息列表','信息列表',44,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (220,'admin.information.type','信息类型','信息类型',44,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (221,'admin.information.wxappPlugin','信息展示','另一个新闻列表',174,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (222,'admin.workOrder.export','导出工单','导出工单',205,'',1,0,'2019-07-25 11:28:21','2019-11-05 18:21:20');
INSERT INTO `admin_permissions` VALUES (223,'admin.workOrder.delete','删除工单','删除工单',205,'',1,0,'2019-07-25 11:28:21','2019-11-05 18:21:22');
INSERT INTO `admin_permissions` VALUES (224,'admin.ranking.sale','成单率排行榜','成单率排行榜',19,'',1,0,'2019-07-25 11:28:21','2019-10-15 09:19:08');
INSERT INTO `admin_permissions` VALUES (225,'admin.wordOrder.transfer','转单管理','转单管理',205,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (226,'admin.staffWxapp.tabbar','底部导航栏配置-管理端','底部导航栏配置-管理端',180,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (227,'admin.agentWxapp.tabbar','底部导航栏配置-用户端','底部导航栏配置-用户端',180,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (228,'admin.helpline','服务热线','服务热线',44,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');

DROP TABLE IF EXISTS `admin_role_user`;
CREATE TABLE `admin_role_user` (
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '用户角色表';

DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '角色名称',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '备注',
  `admin_power` tinyint(1) DEFAULT '2' COMMENT '权限范围（0代表全部,1代表分部，2代表个人）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '角色表';

INSERT INTO `admin_roles` VALUES ('1', '全部功能', '全部功能', '0', '2018-01-16 11:29:48', '2018-01-16 11:29:48');
INSERT INTO `admin_roles` VALUES ('2', '销售人员', '销售人员', '2', '2018-03-11 13:28:23', '2019-02-11 17:14:26');
INSERT INTO `admin_roles` VALUES ('3', '财务人员', '财务人员', '2', '2018-03-27 18:28:42', '2019-02-11 17:14:36');
INSERT INTO `admin_roles` VALUES ('4', '主管销售总监', '销售主管，分公司主管', '2', '2018-03-27 18:29:40', '2019-02-12 16:28:36');
INSERT INTO `admin_roles` VALUES ('5', '访客', '访客', '2', '2018-06-21 10:32:01', '2019-02-11 17:14:53');
INSERT INTO `admin_roles` VALUES ('6', '行政人事', '行政人事', '2', '2019-01-15 17:07:54', '2019-01-15 18:51:01');

DROP TABLE IF EXISTS `admin_session`;
CREATE TABLE `admin_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `session_id` varchar(50) DEFAULT NULL,
  `code` varchar(250) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员ID',
  `login_ip` char(20) DEFAULT NULL COMMENT '登录IP',
  `expire_time` int(11) DEFAULT NULL COMMENT '过期时间',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '用户登录token表';

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员用户表ID',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bonus` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '个人提成',
  `sale_bonus` decimal(9,2) DEFAULT '0.00' COMMENT '个人奖金',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mobile` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '手机',
  `branch_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT '2' COMMENT '部门信息',
  `hiredate` datetime DEFAULT NULL COMMENT '入职时间',
  `last_login_time` datetime DEFAULT NULL COMMENT '上次登录时间',
  `last_login_ip` varchar(32) COLLATE utf8_unicode_ci DEFAULT '0.0.0.0' COMMENT '上次登录ip',
  `status` tinyint(1) DEFAULT '0' COMMENT '0为有效  1为无效',
  `sex` enum('女','男') COLLATE utf8_unicode_ci DEFAULT NULL,
  `wechat_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '企业微信账号',
  `wechat_pic` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '企业微信头像',
  `work_status` tinyint(1) DEFAULT '0' COMMENT '0为接待  1为不接待',
  `power` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '权限范围',
  `work_time` datetime DEFAULT NULL COMMENT '工作签到时间',
  `position` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '职位',
  `ach_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示业绩 0为显示 1为隐藏',
  `openid` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_id` int(3) DEFAULT NULL COMMENT '公司ID',
  `entry_time` timestamp NULL DEFAULT NULL COMMENT '入职时间',
  `formal_time` timestamp NULL DEFAULT NULL COMMENT '转正时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_email_unique` (`email`) USING BTREE,
  KEY `branch_id` (`branch_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '系统用户表';

INSERT INTO `admin_users` VALUES ('1', 'root', 'root@admin.com', '1.00','1.00', '$2y$10$Q/SvQxn6PqcR547wu5KPC.6JAztZRgykqFBAm9U/SqtFsaJBQ9FdS', 'aS9PcXdw9NyKHP2xthB7e0vgsljmYEQO8Zzf7aeKCPboDKuFQP7sQmpCLk0P', '2017-12-11 15:38:55', '2019-02-11 15:11:01', '1888888888', '2', '2018-01-01 00:00:00', '2019-01-02 18:10:45', '127.0.0.1', '0', '男', '', null, '0', '1', '2019-01-09 11:25:44', '超级管理员', '1', '', '1','2018-01-01 00:00:00','2018-01-01 00:00:00');

DROP TABLE IF EXISTS `admin_users_extend`;
CREATE TABLE `admin_users_extend` (
  `admin_id` int(11) NOT NULL COMMENT 'admin_user_id',
  `birth_date` timestamp NULL DEFAULT NULL COMMENT '出生日期',
  `nation` varchar(50) DEFAULT NULL COMMENT '名族',
  `age` varchar(10) DEFAULT NULL COMMENT '年龄',
  `idcard_no` varchar(18) DEFAULT NULL COMMENT '身份证号码',
  `highest_edu` varchar(50) DEFAULT NULL COMMENT '最高学历',
  `degree` varchar(50) DEFAULT NULL COMMENT '学位',
  `marital_status` tinyint(1) DEFAULT NULL COMMENT '0 未婚/1已婚/2 离异/3 丧偶',
  `stature` varchar(3) DEFAULT NULL COMMENT '身高 单位cm',
  `political_affiliation` varchar(50) DEFAULT NULL COMMENT '政治面貌',
  `native_place` varchar(100) DEFAULT NULL COMMENT '籍贯',
  `reg_permanent_place` varchar(100) DEFAULT NULL COMMENT '户口所在地',
  `reg_permanent_type` tinyint(1) DEFAULT NULL COMMENT '户口类型',
  `technical_title` varchar(100) DEFAULT NULL COMMENT '技术职称',
  `social_security_no` varchar(50) DEFAULT NULL COMMENT '社保号',
  `public_reserve_funds` varchar(50) DEFAULT NULL COMMENT '公积金账户',
  `current_address` varchar(200) DEFAULT NULL COMMENT '当前住址',
  `home_address` varchar(200) DEFAULT NULL COMMENT '家庭住址',
  `mobile_phone` varchar(11) DEFAULT NULL COMMENT '手机号',
  `tel_phone` varchar(12) DEFAULT NULL COMMENT '固定电话',
  `binduser_name` varchar(50) DEFAULT NULL COMMENT '紧急联系人姓名',
  `binduser_work_address` varchar(200) DEFAULT NULL COMMENT '紧急联系人工作地点',
  `binduser_phone` varchar(12) DEFAULT NULL COMMENT '紧急联系人电话',
  `education_history` text COMMENT '教育/培训经历 json',
  `work_history` text COMMENT '工作经历json',
  `foreigner_language` varchar(20) DEFAULT NULL COMMENT '外语语种',
  `foreigner_language_status` varchar(20) DEFAULT NULL COMMENT '0 一般/1 熟练',
  `computer_science_level` varchar(20) DEFAULT NULL COMMENT '计算机应用水平',
  `skill_title` varchar(50) DEFAULT NULL COMMENT '技术/技能职称',
  `cantonese_skill_status` varchar(10) DEFAULT NULL COMMENT '粤语 0 不会/1 会',
  `certificate_info` varchar(50) DEFAULT NULL COMMENT '考试种类和等级',
  `lastest_employer_name` varchar(50) DEFAULT NULL COMMENT '最近雇主/上司姓名',
  `lastest_employer_job` varchar(50) DEFAULT NULL COMMENT '最近雇主/上司职务',
  `lastest_employer_phone` varchar(12) DEFAULT NULL COMMENT '最近雇主/上司电话',
  `family_info` text COMMENT '家庭成员信息',
  `acquaintance_name` varchar(50) DEFAULT NULL COMMENT '公司熟人姓名 无则为空',
  `acquaintance_department` varchar(20) DEFAULT NULL COMMENT '熟人部门',
  `acquaintance_job` varchar(20) DEFAULT NULL COMMENT '熟人职务',
  `acquaintance_relation` varchar(20) DEFAULT NULL COMMENT '熟人关系',
  `form_pic` text NOT NULL COMMENT '简历图片',
  `job_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '在职状态',
  `real_avatar` varchar(255) DEFAULT NULL COMMENT '简历头像',
  `identity_card_pic` varchar(500) DEFAULT NULL COMMENT '身份证正反面图片',
  `goods_collection` varchar(500) DEFAULT NULL COMMENT '领取物品',
  `certificate_pic` text COMMENT '证书图片',
  `examination_pic` text COMMENT '体检图片',
  `other_pic` text COMMENT '其它图片',
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '系统用户扩展表';

INSERT INTO `admin_users_extend`(`admin_id`,`job_status`) VALUES ('1','1');

DROP TABLE IF EXISTS `after_sale`;
CREATE TABLE `after_sale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `after_sale_id` int(10) NOT NULL DEFAULT '0' COMMENT '售后ID',
  `member_name` varchar(50) DEFAULT NULL COMMENT '客户名称',
  `member_phone` varchar(15) DEFAULT NULL COMMENT '客户电话',
  `goods_money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '商品金额',
  `sbr_id` int(10) NOT NULL DEFAULT '0',
  `goods_name` varchar(80) DEFAULT NULL COMMENT '商品名称',
  `order_number` varchar(50) DEFAULT NULL COMMENT '订单号',
  `buy_time` datetime DEFAULT NULL COMMENT '购买时间',
  `buy_length` int(3) NOT NULL DEFAULT '0',
  `after_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为有分成 1为无分成 2为固定分成',
  `after_money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '售后提成',
  `bonus_royalty` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '奖金提成',
  `after_time` int(3) NOT NULL DEFAULT '0' COMMENT '已服务时间  按月',
  `after_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态 0为进行中 1为结束',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '售后表';

DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `typeid` int(11) NOT NULL COMMENT '1:新闻 2:使用帮助 3:更新日志 4:关于我们',
  `description` varchar(255) DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `file_url` varchar(120) DEFAULT NULL COMMENT '附件地址',
  `is_display` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '-1:删除  0:上架 1:下架:',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `read_power` tinyint(1) DEFAULT '0' COMMENT '0全部 1内部 2外部',
  `articles_type_id` varchar(255) DEFAULT NULL COMMENT '新闻类型id',
  `picture_type` int(11) NOT NULL DEFAULT '0' COMMENT '图片类型 1：大图 2小图 3三图',
  `video_cover` varchar(255) DEFAULT NULL COMMENT '视频封面图',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;


INSERT INTO `articles` (`title`,`typeid`,`content`) VALUES ('使用帮助','2', '使用帮助说明');
INSERT INTO `articles` (`title`,`typeid`,`content`) VALUES ('关于我们','4', '关于我们说明');
INSERT INTO `articles` (`title`,`typeid`,`content`) VALUES ('用户协议','6', '用户协议');
INSERT INTO `articles` (`title`,`typeid`,`content`) VALUES ('隐私协议','7', '隐私协议');

DROP TABLE IF EXISTS `assign_log`;
CREATE TABLE `assign_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL COMMENT '客户id',
  `assign_name` varchar(50) DEFAULT NULL COMMENT '被指派人名称',
  `assign_admin` varchar(50) DEFAULT NULL COMMENT '指派人名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `assign_uid` int(11) DEFAULT NULL COMMENT '转移人',
  `assign_touid` int(11) DEFAULT NULL COMMENT '接收人',
  `operation_uid` int(11) DEFAULT NULL COMMENT '操作人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '指派客户表';

DROP TABLE IF EXISTS `branchs`;
CREATE TABLE `branchs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `branch_name` varchar(200) DEFAULT NULL COMMENT '部门名称',
  `wechat_branch_id` int(3) DEFAULT '1' COMMENT '企业微信部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '部门表';

INSERT INTO `branchs` VALUES ('1', '全部（管理全部员工，谨慎删除）', '1', '2018-11-07 02:51:01', '2018-12-03 14:19:09');

DROP TABLE IF EXISTS `cases`;
CREATE TABLE `cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_name` varchar(60) DEFAULT NULL COMMENT '案例名称',
  `case_pic` varchar(80) DEFAULT NULL COMMENT '案例图片地址',
  `case_version` varchar(80) DEFAULT NULL COMMENT '案例版本',
  `type` tinyint(4) DEFAULT NULL COMMENT '行业',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '伪删除',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '案例表';

DROP TABLE IF EXISTS `communicationlog`;
CREATE TABLE `communicationlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT '客户ID',
  `admin_user_id` int(11) NOT NULL COMMENT '记录人员ID',
  `comm_time` datetime NOT NULL COMMENT '沟通时间',
  `contentlog` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '客户沟通记录表';

DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL COMMENT '公司名称',
  `wechat_channel_id` int(3) DEFAULT NULL COMMENT '企业微信id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '公司表';

INSERT INTO `company` VALUES ('1', '默认', '0', '2018-09-04 19:51:36', '2019-01-24 15:26:59');

DROP TABLE IF EXISTS `configs`;
CREATE TABLE `configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) DEFAULT NULL COMMENT '站点标题',
  `seo_title` varchar(255) DEFAULT NULL COMMENT '网站seo的标题',
  `version` varchar(30) CHARACTER SET latin1 DEFAULT NULL COMMENT '版本信息',
  `company_id` varchar(30) DEFAULT NULL COMMENT '企业ID',
  `tongxl_secret` varchar(60) DEFAULT NULL COMMENT '通讯录secret',
  `push_secret` varchar(60) DEFAULT NULL COMMENT '自动推送secret',
  `wechat_appid` varchar(60) DEFAULT NULL COMMENT '微信appid',
  `wechat_secret` varchar(60) DEFAULT NULL COMMENT '微信secret',
  `wechat_name` varchar(60) DEFAULT NULL COMMENT '微信名称',
  `wechat_color` varchar(60) DEFAULT NULL COMMENT '微信主题色',
  `qy_mch_id` varchar(35) DEFAULT NULL COMMENT '企业支付商户号',
  `qy_pay_secret` varchar(80) DEFAULT NULL COMMENT '企业微信支付secret',
  `qy_appid` varchar(80) DEFAULT NULL COMMENT '企业微信小程序appid',
  `sms_appid` varchar(30) DEFAULT NULL COMMENT '短信appid',
  `sms_appkey` varchar(60) DEFAULT NULL COMMENT '短信appkey',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `member_wechat_appid` varchar(60) DEFAULT NULL COMMENT '客户端微信APPID',
  `member_wechat_secret` varchar(60) DEFAULT NULL COMMENT '客户端微信secret',
  `member_wechat_version` varchar(60) DEFAULT NULL COMMENT '客户端微信版本号',
  `qy_redirect` varchar(255) DEFAULT NULL COMMENT '企业微信扫码地址',
  `agent_url` varchar(255) DEFAULT NULL COMMENT '代理商地址',
  `shortcut` tinyint(3) DEFAULT '0' COMMENT '快捷登录是否开启：0：关闭 1：开启',
  `shortcut_name` varchar(60) DEFAULT NULL COMMENT '快捷登录名称',
  `shortcut_url` varchar(100) DEFAULT NULL COMMENT '快捷登录地址',
  `qywxLogin` tinyint(3) DEFAULT '0' COMMENT '企业微信是否开启：0:关闭 1:开启',
  `tencent_id` varchar(80) DEFAULT NULL COMMENT '腾讯云id',
  `tencent_appid` varchar(80) DEFAULT NULL COMMENT '腾讯云APPid',
  `tencent_secretid` varchar(200) DEFAULT NULL COMMENT '腾讯云secretid',
  `tencent_secrekey` varchar(200) DEFAULT NULL COMMENT '腾讯云secrekey',
  `tencent_agent_url` varchar(200) DEFAULT NULL COMMENT '腾讯云代理地址',
  `wx_pay_merchant_id` varchar(255) DEFAULT NULL COMMENT '微信支付商户号',
  `wx_pay_secret_key` varchar(255) DEFAULT NULL COMMENT '微信支付API密钥',
  `bonus_alone` varchar(10) DEFAULT NULL COMMENT '奖金单笔最高金额',
  `bonus_proportion` varchar(10) DEFAULT NULL COMMENT '奖金单笔最高余额比例',
  `bonus_today_second` varchar(10) DEFAULT NULL COMMENT '奖金当日提现次数',
  `bonus_month_second` varchar(10) DEFAULT NULL COMMENT '奖金当月提现次数',
  `bonus_small` varchar(10) DEFAULT NULL COMMENT '奖金提现最小金额',
  `wxapplet_name` varchar(255) DEFAULT NULL COMMENT '小程序名称',
  `wxapplet_color` varchar(255) DEFAULT NULL COMMENT '小程序主题色',
  `env` varchar(50) NOT NULL DEFAULT 'LOCAL' COMMENT '系统版本 LOCAL：服务器版  CLOUD：云开发版',
  `member_wechat_qr` varchar(255) DEFAULT NULL COMMENT '客户小程序二维码',
  `bonus_explain` varchar(255) DEFAULT NULL COMMENT '奖金说明',
  `qy_wx_pay_key` varchar(255) DEFAULT NULL COMMENT '企业微信转账key',
  `site_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '站点状态 1：正常 0维护',
  `member_format` tinyint(3) NOT NULL DEFAULT '1' COMMENT '客户小程序显示格式 3：3x3 4：4x4',
  `avatar_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '头像状态 1：强制上传 0：非强制上传',
  `agent_tarbar_list` text COMMENT '客户底部导航栏',
  `admin_tarbar_list` text COMMENT '员工底部导航栏',
  `agent_wxchat_configs` text COMMENT '员工小程序配置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '配置表';

INSERT INTO `configs` (`id`,`title`) VALUES ('1','CRM');

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL COMMENT '用户名',
  `realname` varchar(30) DEFAULT NULL COMMENT '真实名称',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 0为个人 1为企业',
  `mobile` varchar(50) DEFAULT NULL COMMENT '手机号码',
  `spare_mobile` varchar(300) DEFAULT NULL COMMENT '备用电话，多个json存储',
  `recommend` int(10) NOT NULL DEFAULT '1' COMMENT '推荐人ID',
  `addperson` varchar(30) NOT NULL DEFAULT 'root' COMMENT '上传者',
  `email` varchar(300) DEFAULT NULL COMMENT '邮箱',
  `position` varchar(30) DEFAULT NULL COMMENT '职位',
  `company` varchar(50) DEFAULT NULL COMMENT '公司',
  `wechat` varchar(300) DEFAULT NULL COMMENT '微信',
  `qq` varchar(100) DEFAULT NULL COMMENT '腾讯云id',
  `contact_next_time` datetime DEFAULT NULL COMMENT '下次联系时间',
  `source` varchar(35) DEFAULT NULL COMMENT '用户来源',
  `project` varchar(60) DEFAULT NULL COMMENT '项目',
  `progress` varchar(30) DEFAULT '初步接触' COMMENT '客户跟进状态',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户状态 0为冻结 1为正常',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `cust_state` tinyint(1) DEFAULT '0' COMMENT '0 未激活/1 已激活',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL COMMENT '座机电话',
  `tencent_id` varchar(255) DEFAULT NULL COMMENT 'QQ',
  `activation_time` timestamp NULL DEFAULT NULL COMMENT '激活时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '总客户表';

DROP TABLE IF EXISTS `goods`;
CREATE TABLE `goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sbr_id` int(10) NOT NULL DEFAULT '0' COMMENT '提成规则ID',
  `goods_name` varchar(50) NOT NULL,
  `goods_type` tinyint(5) NOT NULL DEFAULT '1' COMMENT '商品类型表id',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `price_type` tinyint(1) NOT NULL COMMENT '价格类型',
  `goods_pic` varchar(255) DEFAULT NULL COMMENT '缩略图',
  `body` text,
  `long` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:永久 1:年 2:月 3:日',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态(0:上架  1:下架)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `goods_top` smallint(5) DEFAULT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为正常 1为删除',
  `goods_version` text COMMENT '商品规格',
  `pic_list` text COMMENT '图片列表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '商品表';

DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL COMMENT '用户名',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号码',
  `password` varchar(64) DEFAULT NULL COMMENT '密码',
  `email` varchar(40) DEFAULT NULL COMMENT '邮箱',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户状态 0为冻结 1为正常',
  `openid` varchar(50) DEFAULT NULL COMMENT '客户openid',
  `active_time` timestamp NULL DEFAULT NULL COMMENT '激活时间',
  `tencent_openid` varchar(200) DEFAULT NULL COMMENT '腾讯云小程序openid',
  `tencent_uid` varchar(200) DEFAULT NULL COMMENT '客户绑定腾讯云账号id',
  `tencent_discount` int(3) DEFAULT NULL COMMENT '腾讯云代付折扣',
  `tencent_status` tinyint(1) DEFAULT '0' COMMENT '是否能代付订单 0：不能 1：可以',
  `quota` varchar(10) NOT NULL DEFAULT '0' COMMENT '单笔支付额度',
  `is_vip` varchar(255) NOT NULL DEFAULT '0' COMMENT '会员状态 0非 1待审核 2审核通过 3注销',
  `industry_type_id` varchar(255) DEFAULT NULL COMMENT '会员类型id',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '正式客户表';

DROP TABLE IF EXISTS `member_extend`;
CREATE TABLE `member_extend` (
  `member_id` int(11) NOT NULL COMMENT 'member表 客户ID',
  `realname` varchar(30) DEFAULT NULL COMMENT '真实名称',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 0为个人 1为企业',
  `avatar` varchar(200) DEFAULT NULL COMMENT '头像地址',
  `level` tinyint(2) NOT NULL DEFAULT '5' COMMENT '默认等级',
  `recommend` int(11) NOT NULL DEFAULT '1' COMMENT '推荐人ID',
  `addperson` varchar(30) NOT NULL DEFAULT 'root' COMMENT '上传者',
  `cash_coupon` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金',
  `balance` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `position` varchar(30) DEFAULT NULL COMMENT '职位',
  `company` varchar(255) DEFAULT NULL COMMENT '公司',
  `wechat` varchar(100) DEFAULT NULL COMMENT '微信',
  `qq` varchar(100) DEFAULT NULL COMMENT '腾讯id',
  `source` varchar(35) DEFAULT NULL COMMENT '用户来源',
  `project` varchar(60) DEFAULT NULL COMMENT '项目',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `certify_name` varchar(255) DEFAULT NULL COMMENT '身份证名称/企业名称',
  `certify_no` varchar(18) DEFAULT NULL COMMENT '身份证号码/营业执照',
  `certify_pic` text COMMENT '身份证正面/营业执照',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `tencent_id` varchar(255) DEFAULT NULL COMMENT 'qq',
  `telephone` varchar(300) DEFAULT NULL COMMENT '座机',
  `spare_mobile` varchar(255) DEFAULT NULL COMMENT '备用电话',
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `member_id` (`member_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '正式客户扩展表';

DROP TABLE IF EXISTS `member_level`;
CREATE TABLE `member_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '客户等级表';

DROP TABLE IF EXISTS `member_session`;
CREATE TABLE `member_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `session_id` varchar(50) DEFAULT NULL,
  `code` varchar(250) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL COMMENT '管理员ID',
  `login_ip` char(20) DEFAULT NULL COMMENT '登录IP',
  `expire_time` int(11) DEFAULT NULL COMMENT '过期时间',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '客户登录token表';

DROP TABLE IF EXISTS `member_source`;
CREATE TABLE `member_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(35) DEFAULT NULL,
  `order` int(10) DEFAULT '0' COMMENT '越大越靠前',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '客户来源表';

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_sn` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '支付订单号',
  `title` varchar(255) NOT NULL COMMENT '商品名称',
  `type` tinyint(1) NOT NULL COMMENT '类型(0:小程序，1:腾讯云，2:定制）',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `uname` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `amount` int(11) NOT NULL DEFAULT '1' COMMENT '购买数量',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '折扣',
  `long` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:永久 1:年 2:月 3:日',
  `total_price` decimal(10,2) NOT NULL COMMENT '总价',
  `pay_type` tinyint(1) DEFAULT NULL COMMENT '0:微信 1:支付宝 2:其他',
  `pay_time` varchar(20) DEFAULT NULL COMMENT '支付完成时间',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态 -3：拒绝退款；-2:退款完成 -1:申请退款 0:待付款 1:已付款 2:已完成',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态 -2:已取消 -1:申请退款 0:待处理 1:已完成',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '软删除   -1：删除',
  `submitter` varchar(40) DEFAULT NULL COMMENT '提交人',
  `flag` tinyint(1) DEFAULT '0' COMMENT '0表示全部  1表示超管',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expire_time` int(11) DEFAULT NULL COMMENT '过期时间',
  `goods_id` int(11) DEFAULT NULL COMMENT '商品ID',
  `owner_uin` varchar(200) DEFAULT NULL COMMENT '腾讯云订单保存客户uid，不是腾讯云订单请忽略',
  `goods_version` text COMMENT '修改之后的商品规格',
  `remarks` varchar(400) DEFAULT NULL COMMENT '订单购买记录',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '订单表';

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`) USING BTREE,
  KEY `password_resets_token_index` (`token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

DROP TABLE IF EXISTS `problem`;
CREATE TABLE `problem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(5) NOT NULL COMMENT '问题提出人',
  `problem_doc` text COMMENT '问题描述',
  `state` tinyint(1) DEFAULT '0' COMMENT '问题处理状态',
  `remarks` varchar(100) DEFAULT NULL COMMENT '问题处理备注',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '问题表';

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL COMMENT '项目名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '项目表';

DROP TABLE IF EXISTS `salebonusrule`;
CREATE TABLE `salebonusrule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(50) DEFAULT NULL COMMENT '规则名称',
  `rule_type` tinyint(1) DEFAULT NULL COMMENT '提成规则 0 比例/1 固定',
  `cost` int(6) NOT NULL DEFAULT '0' COMMENT '成本',
  `pre_bonus` int(6) NOT NULL DEFAULT '0' COMMENT '售前比例或固定金额',
  `after_bonus` int(6) NOT NULL DEFAULT '0' COMMENT '售后比例或固定金额',
  `after_first_bonus` int(6) DEFAULT '0' COMMENT '售后首笔提成比例',
  `bonus` int(5) DEFAULT '0' COMMENT '奖金比例或固定金额',
  `first_bonus` int(5) DEFAULT '0' COMMENT '奖金首笔提成比例',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1正常 0禁用',
  `type` tinyint(1) DEFAULT NULL COMMENT '类型 1提成 2奖金',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '销售提成规格表';

DROP TABLE IF EXISTS `sys_version`;
CREATE TABLE `sys_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `version` varchar(100) DEFAULT NULL COMMENT 'crm版本',
  `status` tinyint(1) DEFAULT NULL COMMENT '代码更新状态:0 未更新/1 未执行/2 已更新',
  `tips` varchar(200) DEFAULT NULL COMMENT '注意提示',
  `introduce` longtext COMMENT '版本介绍(html code)',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `take_bonus`;
CREATE TABLE `take_bonus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_users_id` int(10) NOT NULL COMMENT '提现人ID',
  `order_number` varchar(40) NOT NULL COMMENT '订单号',
  `pay_number` varchar(40) DEFAULT NULL COMMENT '支付订单号',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `bonus_money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `cur_bonus` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '当时提成余额',
  `remarks` varchar(255) DEFAULT NULL COMMENT '提现备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '提现状态 0为初始 1为同意 2为拒绝 3为取消',
  `handle_id` int(10) NOT NULL COMMENT '处理人ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` tinyint(1) DEFAULT '1' COMMENT '提现类型 1：余额 2：奖金',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '提成提现记录表';

DROP TABLE IF EXISTS `user_branch`;
CREATE TABLE `user_branch` (
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `branch_id` int(11) DEFAULT NULL COMMENT '部门id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '用户的部门表';

INSERT INTO `user_branch` VALUES ('1', '1');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `wallet_logs`;
CREATE TABLE `wallet_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL COMMENT '0:余额  赠送金(0:增加余额  1:赠送 2:活动补贴 -1:冲账 -2:罚款 3:其他 9:减少余额)',
  `operation` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '类型',
  `money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `wallet` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `manage` varchar(20) DEFAULT NULL COMMENT '操作者',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '余额记录表';

DROP TABLE IF EXISTS `work_order`;
CREATE TABLE `work_order` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工单表';

DROP TABLE IF EXISTS `work_order_log`;
CREATE TABLE `work_order_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL DEFAULT '0' COMMENT '类型 1：为客户id 2：为管理id',
  `log_id` int(11) NOT NULL DEFAULT '0' COMMENT '工单id',
  `type` int(11) DEFAULT NULL COMMENT '类型 1：客户补充 2：网格员补充',
  `remarks` varchar(400) DEFAULT NULL COMMENT '问题处理备注',
  `annex` varchar(300) DEFAULT NULL COMMENT '附件',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工单回复表';


DROP TABLE IF EXISTS `work_order_type`;
CREATE TABLE `work_order_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) DEFAULT NULL COMMENT '标签',
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='工单类型';


DROP TABLE IF EXISTS `verification`;
CREATE TABLE `verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(11) DEFAULT NULL COMMENT '手机号',
  `code` varchar(10) DEFAULT NULL COMMENT '验证码',
  `create_time` varchar(255) DEFAULT NULL COMMENT '创建时间',
  `overdue_time` varchar(255) DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='验证码';

DROP TABLE IF EXISTS `tencent_order`;
CREATE TABLE `tencent_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `dealId` varchar(255) DEFAULT NULL,
  `dealName` varchar(255) DEFAULT NULL,
  `goodsCategoryId` varchar(255) DEFAULT NULL,
  `ownerUin` varchar(255) DEFAULT NULL,
   `appId` varchar(255) DEFAULT NULL,
   `goodsNum` varchar(255) DEFAULT NULL,
   `creater` varchar(255) DEFAULT NULL,
   `creatTime` varchar(255) DEFAULT NULL,
   `payer` varchar(255) DEFAULT NULL,
   `billId` varchar(255) DEFAULT NULL,
   `payEndTime` varchar(255) DEFAULT NULL,
   `status` varchar(255) DEFAULT NULL,
   `voucherDecline` varchar(255) DEFAULT NULL,
   `payerMode` varchar(255) DEFAULT NULL,
   `goodsName` varchar(255) DEFAULT NULL,
   `clientRemark` varchar(255) DEFAULT NULL,
   `flag` varchar(255) DEFAULT NULL,
   `dealStatus` varchar(255) DEFAULT NULL,
   `actionType` varchar(255) DEFAULT NULL,
   `clientType` varchar(255) DEFAULT NULL,
   `projectType` varchar(255) DEFAULT NULL,
   `bigDealId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='腾讯云订单';

DROP TABLE IF EXISTS `tencent_order_price`;
CREATE TABLE `tencent_order_price` (
  `tencent_order_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '关联tencent_order表id字段',
  `price` varchar(255) DEFAULT NULL,
  `totalCost` varchar(255) DEFAULT NULL,
  `realTotalCost` varchar(255) DEFAULT NULL,
  `timeSpan` varchar(255) DEFAULT NULL,
  `timeUnit` varchar(255) DEFAULT NULL,
  `goodsNum` varchar(255) DEFAULT NULL,
  `pid` varchar(255) DEFAULT NULL,
  `priceModel` varchar(255) DEFAULT NULL,
  `policy` varchar(255) DEFAULT NULL,
  `unitPrice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tencent_order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='';


DROP TABLE IF EXISTS `picture`;
CREATE TABLE `picture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '图片名称',
  `uid` int(11) DEFAULT NULL COMMENT '上传人员id',
  `url` varchar(255) DEFAULT NULL COMMENT '图片地址',
  `status` tinyint(3) DEFAULT '1' COMMENT '是否删除 0：已删除 1：正常',
  `time` timestamp NULL DEFAULT NULL COMMENT '上传时间',
  `type` varchar(255) DEFAULT NULL COMMENT '图片类型',
  `src` varchar(255) DEFAULT NULL COMMENT '图片相对路径',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='图片记录表';

DROP TABLE IF EXISTS `bonus_sale`;
CREATE TABLE `bonus_sale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `after_sale_id` int(10) NOT NULL DEFAULT '0' COMMENT '售后ID',
  `member_name` varchar(50) DEFAULT NULL COMMENT '客户名称',
  `member_phone` varchar(15) DEFAULT NULL COMMENT '客户电话',
  `goods_money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '商品金额',
  `sbr_id` int(10) NOT NULL DEFAULT '0',
  `goods_name` varchar(80) DEFAULT NULL COMMENT '商品名称',
  `order_number` varchar(50) DEFAULT NULL COMMENT '订单号',
  `buy_time` timestamp NULL DEFAULT NULL COMMENT '购买时间',
  `buy_length` int(3) NOT NULL DEFAULT '0',
  `after_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为有分成',
  `after_money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '售后奖金',
  `after_time` int(3) NOT NULL DEFAULT '0' COMMENT '已服务时间  按月',
  `after_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态 0为进行中 1为结束',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='售后奖金表';

DROP TABLE IF EXISTS `goods_type`;
CREATE TABLE `goods_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '类型名称',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='商品类型表';

DROP TABLE IF EXISTS `activity`;
CREATE TABLE `activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '活动名称',
  `explain` varchar(100) DEFAULT NULL COMMENT '活动简介',
  `place` varchar(100) DEFAULT NULL COMMENT '地点',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `stop_time` timestamp NULL DEFAULT NULL COMMENT '截止时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '结束时间',
  `host_party` varchar(100) DEFAULT NULL COMMENT '主办方',
  `host_contact` varchar(255) DEFAULT NULL COMMENT '主办方联系方式',
  `cost` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '费用',
  `picture` varchar(100) DEFAULT NULL COMMENT '图片显示',
  `regulations` text COMMENT '活动规则',
  `details` text COMMENT '活动详情',
  `limit_number` varchar(50) DEFAULT NULL COMMENT '限制人数',
  `activity_type_id` varchar(50) DEFAULT NULL COMMENT '活动类型id',
  `audit_mode` tinyint(3) NOT NULL DEFAULT '0' COMMENT '审核方式 0手动审核 1自动审核',
  `notice` tinyint(3) DEFAULT '1' COMMENT '通知方式 1微信通知 2短信通知 3微信加短信通知',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `activity_status` tinyint(3) DEFAULT '0' COMMENT '活动状态 0：已取消 1：正常进行 2已结束',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动';

DROP TABLE IF EXISTS `activity_type`;
CREATE TABLE `activity_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动类型';

DROP TABLE IF EXISTS `album`;
CREATE TABLE `album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `chart` varchar(255) DEFAULT NULL COMMENT '图片地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='相册表';

DROP TABLE IF EXISTS `articles_type`;
CREATE TABLE `articles_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态 0不显示 1显示',
  `created_at` varchar(255) DEFAULT NULL,
  `sort` int(5) NOT NULL DEFAULT '1' COMMENT '排序',
  `type` tinyint(3) NOT NULL DEFAULT '1',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT 'cid',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='新闻类型表';


DROP TABLE IF EXISTS `attend`;
CREATE TABLE `attend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) DEFAULT NULL COMMENT '活动id',
  `member_id` int(10) DEFAULT NULL COMMENT '客户id',
  `member_name` varchar(255) DEFAULT NULL COMMENT '客户名',
  `mobile` varchar(15) DEFAULT NULL COMMENT '手机',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态 0：待付款 1:付款成功 待审核 2：审核成功 3：审核失败 待退款4：审核失败已退款 5取消报名',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL COMMENT '用户openid',
  `out_trade_no` varchar(100) DEFAULT NULL COMMENT '微信支付内部订单号',
  `money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='报名';

DROP TABLE IF EXISTS `banner`;
CREATE TABLE `banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '图片标题',
  `url` varchar(255) DEFAULT NULL COMMENT '图片路径',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态 0禁用 1开启',
  `size` varchar(255) DEFAULT NULL COMMENT '尺寸',
  `created_at` timestamp NULL DEFAULT NULL,
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型 1客户 2员工',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='轮播图表';


DROP TABLE IF EXISTS `china`;
CREATE TABLE `china` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `collect`;
CREATE TABLE `collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL COMMENT '客户id',
  `activity_id` int(11) DEFAULT NULL COMMENT '活动id',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收藏表';

DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) DEFAULT NULL COMMENT '活动类型id',
  `content` varchar(255) DEFAULT NULL COMMENT '评论内容',
  `member_id` int(11) DEFAULT NULL COMMENT '客户id',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像地址',
  `created_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(3) DEFAULT '0' COMMENT '状态 0：待审核 1：通过 2：拒绝',
  `name` varchar(100) DEFAULT NULL COMMENT '用户名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论表';

DROP TABLE IF EXISTS `fabulous`;
CREATE TABLE `fabulous` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(255) DEFAULT NULL COMMENT '客户id',
  `activity_id` varchar(255) DEFAULT NULL COMMENT '活动id',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动点赞表';

DROP TABLE IF EXISTS `form_id`;
CREATE TABLE `form_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `form_id` varchar(64) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL COMMENT '渠道方式 pay/other',
  `form_user` varchar(20) DEFAULT NULL COMMENT '通知绑定用户(身份-ID) 例:admin-18',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `is_used` tinyint(1) DEFAULT '0' COMMENT '是否已使用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `home_page`;
CREATE TABLE `home_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `position` varchar(255) DEFAULT NULL COMMENT '位置',
  `status` tinyint(3) DEFAULT NULL COMMENT '状态 1开启 2关闭',
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` tinyint(3) DEFAULT NULL COMMENT '类型 1为客户小程序 2为员工小程序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='首页配置表';


INSERT INTO `home_page` VALUES (1,'轮播','1',1,'2019-06-19 18:22:38',1);
INSERT INTO `home_page` VALUES (2,'公告','2',1,'2019-06-19 18:22:38',1);
INSERT INTO `home_page` VALUES (3,'插件','3',1,'2019-06-19 18:22:38',1);
INSERT INTO `home_page` VALUES (4,'新闻','5',1,'2019-06-19 18:22:38',1);
INSERT INTO `home_page` VALUES (5,'推荐','4',1,'2019-06-19 18:22:38',1);
INSERT INTO `home_page` VALUES (6,'轮播','1',1,'2019-06-19 18:22:38',2);
INSERT INTO `home_page` VALUES (7,'公告','2',1,'2019-06-19 18:22:38',2);
INSERT INTO `home_page` VALUES (8,'插件','3',1,'2019-06-19 18:22:38',2);
INSERT INTO `home_page` VALUES (9,'新闻','5',1,'2019-06-19 18:22:38',2);
INSERT INTO `home_page` VALUES (10,'推荐','4',1,'2019-06-19 18:22:38',2);
INSERT INTO `home_page` VALUES (11,'导航','6',1,'2019-07-25 11:28:21',1);
INSERT INTO `home_page` VALUES (12,'导航','6',1,'2019-07-25 11:28:21',2);

DROP TABLE IF EXISTS `industry_type`;
CREATE TABLE `industry_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='会员类型表';

DROP TABLE IF EXISTS `member_vip`;
CREATE TABLE `member_vip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL COMMENT '客户id',
  `birthday` varchar(50) DEFAULT NULL COMMENT '生日',
  `native_place` varchar(100) DEFAULT NULL COMMENT '籍贯',
  `nation` varchar(50) DEFAULT NULL COMMENT '民族',
  `political_outlook` varchar(50) DEFAULT NULL COMMENT '政治面貌',
  `education` varchar(20) DEFAULT NULL COMMENT '学历',
  `id_number` char(18) DEFAULT NULL COMMENT '身份证号',
  `school` varchar(100) DEFAULT NULL COMMENT '毕业院校',
  `major` varchar(50) DEFAULT NULL COMMENT '专业',
  `recommender` varchar(50) DEFAULT NULL COMMENT '推荐人',
  `enterprise_name` varchar(100) DEFAULT NULL COMMENT '企业名称',
  `position` varchar(100) DEFAULT NULL COMMENT '职位',
  `nature` varchar(20) DEFAULT NULL COMMENT '企业性质',
  `province` varchar(50) DEFAULT NULL COMMENT '省',
  `city` varchar(50) DEFAULT NULL COMMENT '市',
  `area` varchar(50) DEFAULT NULL COMMENT '区',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `new_high` varchar(10) DEFAULT NULL COMMENT '是否高新企业',
  `industry` varchar(50) DEFAULT NULL COMMENT '行业',
  `zip_code` varchar(10) DEFAULT NULL COMMENT '邮编',
  `patent` varchar(255) DEFAULT NULL COMMENT '企业专利',
  `office_phone` varchar(20) DEFAULT NULL COMMENT '办公电话',
  `website` varchar(100) DEFAULT NULL COMMENT '企业网址',
  `fax` varchar(20) DEFAULT NULL COMMENT '传真号',
  `registered_capital` varchar(50) DEFAULT NULL COMMENT '企业注册资金',
  `staff_number` varchar(20) DEFAULT NULL COMMENT '企业员工人数',
  `turnover` varchar(50) DEFAULT NULL COMMENT '营业额',
  `tax_amount` varchar(50) DEFAULT NULL COMMENT '纳税额',
  `job_brief` varchar(255) DEFAULT NULL COMMENT '工作简介',
  `company_profile` varchar(255) DEFAULT NULL COMMENT '企业简介',
  `sex` varchar(50) DEFAULT '男' COMMENT '性别',
  `main_business` varchar(255) DEFAULT NULL COMMENT '主营业务',
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户会员表';

DROP TABLE IF EXISTS `proposal`;
CREATE TABLE `proposal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `picture_list` varchar(255) DEFAULT NULL COMMENT '图片json保存',
  `content` varchar(500) DEFAULT NULL COMMENT '评论内容',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='留言建议表';

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '权限名',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '权限解释名称',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '描述与备注',
  `cid` int(10) NOT NULL DEFAULT '0' COMMENT '级别',
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '图标',
  `display` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示 0显示 1不显示',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_limit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启限制 0否 1是',
  `new_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '小程序显示名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='客户权限列表';

INSERT INTO `permissions` VALUES (1,'admin.permission','权限管理','',0,'fa-users',0,1,'2016-05-21 02:06:50','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (2,'admin.permission.index','权限列表','',1,'',0,0,'2016-05-21 02:08:04','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (3,'admin.permission.create','权限添加','',1,'',0,0,'2016-05-21 02:08:18','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (4,'admin.permission.edit','权限修改','',1,'',0,0,'2016-05-21 02:08:35','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (5,'admin.permission.destroy ','权限删除','',1,'',0,0,'2016-05-21 02:09:57','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (6,'admin.role.index','角色列表','',1,'',0,0,'2016-05-23 02:36:40','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (7,'admin.role.create','角色添加','',1,'',0,0,'2016-05-23 02:37:07','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (8,'admin.role.edit','角色修改','',1,'',0,0,'2016-05-23 02:37:22','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (9,'admin.role.destroy','角色删除','',1,'',0,0,'2016-05-23 02:37:48','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (10,'admin.user.index','系统用户','',1,'',0,0,'2016-05-23 02:38:52','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (11,'admin.user.create','管理员添加','',1,'',0,0,'2016-05-23 02:39:21','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (12,'admin.user.edit','管理员编辑','',1,'',0,0,'2016-05-23 02:39:52','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (13,'admin.user.destroy','管理员删除','',1,'',0,0,'2016-05-23 02:40:36','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (14,'admin.goods','商品管理','',0,'fa-sliders',0,0,'2018-05-24 09:58:48','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (15,'admin.goods.index','商品列表','',14,'',0,0,'2018-05-24 10:09:57','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (16,'admin.goods.create','商品添加','',14,'',0,0,'2018-05-25 10:00:17','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (17,'admin.goods.edit','商品修改','',14,'',0,0,'2018-05-25 10:00:34','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (18,'admin.goods.destroy','商品删除','',14,'',0,0,'2018-05-25 10:01:06','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (19,'admin.index','控制面板','',0,'fa-tachometer',0,2,'2018-05-25 10:01:47','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (20,'admin.index.index','首页','',19,'',0,0,'2018-05-25 10:02:03','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (21,'admin.branch.index','分部列表','',1,'',0,0,'2018-05-28 05:43:31','2019-08-15 10:34:14',0,NULL);
INSERT INTO `permissions` VALUES (22,'admin.workstatus.index','打卡签到','',19,'',0,0,'2018-05-28 06:36:50','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (23,'admin.case.index','案例列表','',14,'',0,0,'2018-05-28 09:55:22','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (24,'admin.case.create','案例添加','',14,'',0,0,'2018-05-28 10:16:13','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (25,'admin.case.edit','案例修改','',14,'',0,0,'2018-05-28 10:16:33','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (26,'admin.case.destroy','案例删除','',14,'',0,0,'2018-05-28 10:16:52','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (27,'admin.recovery.index','商品回收站','',14,'',0,0,'2018-05-28 10:18:07','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (28,'admin.member','客户管理','',0,'fa-users',0,0,'2018-05-29 02:50:41','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (29,'admin.member.index','我的客户','',28,'',0,0,'2018-05-29 02:51:06','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (30,'admin.member.create','客户添加','',28,'',0,0,'2018-05-29 07:34:31','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (31,'admin.member.edit','客户修改','',28,'',0,0,'2018-05-29 07:34:50','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (32,'admin.member.destroy','客户删除','',28,'',0,0,'2018-05-29 07:35:06','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (33,'admin.member.moneyedit','修改余额','',28,'',0,0,'2018-05-29 07:50:09','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (34,'admin.memberlevel.index','等级设置','',88,'',0,0,'2018-05-29 09:37:01','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (35,'admin.memberlevel.create','等级添加','',88,'',0,0,'2018-05-29 09:49:01','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (36,'admin.memberlevel.edit','等级修改','',88,'',0,0,'2018-05-29 09:49:22','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (37,'admin.memberlevel.destroy','等级删除','',88,'',0,0,'2018-05-29 09:49:59','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (38,'admin.membersource.index','来源设置','',88,'',0,0,'2018-05-29 10:06:45','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (39,'admin.order','订单管理','',0,'fa-reorder',0,0,'2018-05-29 11:12:38','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (40,'admin.order.index','订单列表','',39,'',0,0,'2018-05-29 11:12:57','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (41,'admin.order.create','订单添加','',39,'',0,0,'2018-06-01 02:41:10','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (42,'admin.order.edit','订单修改','',39,'',0,0,'2018-06-01 02:42:25','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (43,'admin.order.destroy','订单删除','',39,'',0,0,'2018-06-01 02:42:48','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (44,'admin.article','文章管理','',0,'fa-newspaper-o',0,0,'2018-06-01 03:14:31','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (45,'admin.articles.index','新闻列表','',44,'',0,0,'2018-06-01 03:16:38','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (46,'admin.articles.create','新闻添加','',44,'',0,0,'2018-06-01 03:30:02','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (47,'admin.articles.edit','新闻修改','',44,'',0,0,'2018-06-01 03:30:33','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (48,'admin.articles.destroy','新闻删除','',44,'',0,0,'2018-06-01 03:31:31','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (49,'admin.help.index','使用帮助','',44,'',0,0,'2018-06-01 03:32:32','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (50,'admin.about.index','关于我们','',44,'',0,0,'2018-06-01 03:32:54','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (51,'admin.log.index','更新日志','',44,'',0,0,'2018-06-01 03:33:16','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (52,'admin.finance','财务管理','',0,'fa-diamond',0,0,'2018-06-01 08:36:34','2019-08-15 10:35:04',0,NULL);
INSERT INTO `permissions` VALUES (53,'admin.wallet.index','资金明细','',52,'',0,0,'2018-06-01 08:36:58','2019-08-15 10:35:04',0,NULL);
INSERT INTO `permissions` VALUES (54,'admin.donation.index','赠送金明细','',52,'',0,0,'2018-06-01 09:32:13','2019-08-15 10:35:04',0,NULL);
INSERT INTO `permissions` VALUES (55,'admin.customer','客户资源','',0,'fa-user-md',0,0,'2018-06-01 09:36:25','2019-08-15 10:34:19',0,NULL);
INSERT INTO `permissions` VALUES (56,'admin.customer.index','我的客户资源','',55,'',0,0,'2018-06-01 09:36:55','2019-08-15 10:34:19',0,NULL);
INSERT INTO `permissions` VALUES (57,'admin.problem','问题反馈','',0,'fa-mail-reply-all',0,0,'2018-06-05 09:54:51','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (58,'admin.problem.index','日常问题','',57,'',0,0,'2018-06-05 09:55:16','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (59,'admin.problem.create','问题添加','',57,'',0,0,'2018-06-05 10:01:37','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (60,'admin.problem.edit','问题修改','',57,'',0,0,'2018-06-05 10:01:55','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (61,'admin.problem.destroy','问题删除','',57,'',0,0,'2018-06-05 10:02:33','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (80,'admin.customer.create','客户资源添加','',55,'',0,0,'2018-06-07 18:10:32','2019-08-15 10:34:19',0,NULL);
INSERT INTO `permissions` VALUES (81,'admin.customer.edit','客户资源修改','',55,'',0,0,'2018-06-07 18:10:51','2019-08-15 10:34:19',0,NULL);
INSERT INTO `permissions` VALUES (82,'admin.customer.destroy','客户资源删除','',55,'',0,0,'2018-06-07 18:11:08','2019-08-15 10:34:19',0,NULL);
INSERT INTO `permissions` VALUES (83,'admin.achievement.index','业绩订单','',39,'',0,0,'2018-06-08 11:39:25','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (84,'admin.globaldata.index','业绩排行榜','',19,'',0,0,'2018-06-08 16:52:14','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (85,'admin.index.home','登录初始化','',19,'',0,0,'2018-06-12 17:12:07','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (86,'admin.member.goods','业绩商品','',28,'',0,0,'2018-06-12 21:14:35','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (88,'admin.configs','系统设置','',0,'fa-cog',0,0,'2018-06-23 11:00:50','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (89,'admin.configs.index','站点信息','',88,'',0,0,'2018-06-23 11:01:21','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (90,'admin.salebonusrule.index','提成设置','',88,'',0,0,'2018-06-23 11:09:45','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (91,'admin.aftersale.index','售后订单','',39,'',0,0,'2018-06-24 20:37:59','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (92,'admin.achievement.create','业绩增加','',39,'',0,0,'2018-06-26 11:52:21','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (93,'admin.achievement.edit','业绩修改','',39,'',0,0,'2018-06-26 11:52:38','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (94,'admin.achievement.destroy','业绩删除','',39,'',0,0,'2018-06-26 11:52:52','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (95,'admin.aftersale.create','售后增加','',39,'',0,0,'2018-06-29 17:59:15','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (96,'admin.aftersale.edit','售后修改','',39,'',0,0,'2018-06-29 18:00:03','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (97,'admin.aftersale.destroy','售后删除','',39,'',0,0,'2018-06-29 18:01:01','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (98,'admin.achievement.export','业绩导出','',39,'',0,0,'2018-07-12 17:55:32','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (99,'admin.siteconfig.index','站点设置','',88,'',0,0,'2018-07-12 17:56:00','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (100,'admin.project.index','项目设置','',88,'',0,0,'2018-07-12 18:02:41','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (101,'admin.achievement.examine','业绩审核','',39,'',0,0,'2018-07-16 19:01:46','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (102,'admin.customer.assign','客户资源指派','',55,'',0,0,'2018-07-18 16:43:15','2019-08-15 10:34:19',0,NULL);
INSERT INTO `permissions` VALUES (103,'admin.takemoney.index','提现管理','',52,'',0,0,'2018-07-18 16:43:15','2019-08-15 10:35:04',0,NULL);
INSERT INTO `permissions` VALUES (104,'admin.takemoney.create','提现','',52,'',0,0,'2018-07-18 16:43:15','2019-08-15 10:35:04',0,NULL);
INSERT INTO `permissions` VALUES (105,'admin.takemoney.edit','提现审核','',52,'',0,0,'2018-07-18 16:43:15','2019-08-15 10:35:04',0,NULL);
INSERT INTO `permissions` VALUES (106,'admin.bonuslog.index','提成记录','',52,'',0,0,'2018-08-02 15:37:11','2019-08-15 10:35:04',0,NULL);
INSERT INTO `permissions` VALUES (107,'admin.customer.excel','客户资源导入导出','',55,'',0,0,'2018-08-17 16:28:58','2019-08-15 10:34:19',0,NULL);
INSERT INTO `permissions` VALUES (108,'admin.company.index','公司设置','',88,'',0,0,'2018-08-27 14:51:20','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (109,'admin.sysupdate.index','CRM版本','',88,'',0,0,'2018-10-26 19:15:11','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (110,'admin.achievement.updaterecord','更新订单信息','',39,'',0,0,'2018-10-26 19:15:47','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (111,'admin.achievement.updatedetail','更新订单详情','',39,'',0,0,'2018-10-26 19:15:47','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (112,'admin.hr','人事管理','人事管理列表',0,'fa-steam',0,0,'2018-10-26 19:15:47','2019-08-15 10:34:21',0,NULL);
INSERT INTO `permissions` VALUES (113,'admin.hr.index','员工列表','',112,'',0,0,'2018-10-26 19:15:47','2019-08-15 10:34:21',0,NULL);
INSERT INTO `permissions` VALUES (114,'admin.hr.edit','员工资料编辑','',112,'',0,0,'2018-10-26 19:15:47','2019-08-15 10:34:21',0,NULL);
INSERT INTO `permissions` VALUES (115,'admin.achievement.exchangeorder','业绩订单转移','',39,'',0,0,'2018-10-26 19:15:47','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (116,'admin.aftersale.exchangeorder','售后订单转移','',39,'',0,0,'2018-10-26 19:15:47','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (117,'admin.workorder.index','工单列表','',57,'',0,0,'2018-10-23 14:56:04','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (118,'admin.workorder.edit','工单处理','',57,'',0,0,'2018-10-23 14:56:04','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (119,'personalInformation','个人人事信息','查看个人人事信息',112,'',0,0,'2019-01-15 18:50:06','2019-08-15 10:34:21',0,NULL);
INSERT INTO `permissions` VALUES (127,'admin.wallet','我的钱包','我的钱包',0,'',0,0,'2019-05-18 17:56:37','2019-08-15 10:34:23',0,NULL);
INSERT INTO `permissions` VALUES (128,'admin.wallet.details','钱包明细','钱包明细',127,'',0,0,'2019-05-18 17:56:37','2019-08-15 10:34:23',0,NULL);
INSERT INTO `permissions` VALUES (129,'admin.wallet.bonus','预期奖金列表','预期奖金列表',127,'',0,0,'2019-05-18 17:56:37','2019-08-15 10:34:23',0,NULL);
INSERT INTO `permissions` VALUES (130,'admin.wallet.bonus.get','奖金提现','奖金提现',127,'',0,0,'2019-05-18 17:56:37','2019-08-15 10:34:23',0,NULL);
INSERT INTO `permissions` VALUES (133,'admin.plugin.index','插件设置','插件设置',88,'',0,0,'2019-05-18 17:56:37','2019-08-15 10:34:20',0,NULL);
INSERT INTO `permissions` VALUES (134,'admin.product.productType','产品类型','产品类型',14,'',0,0,'2019-05-18 17:56:37','2019-08-15 10:34:15',0,NULL);
INSERT INTO `permissions` VALUES (160,'admin.lookarticles.index','类型列表','--',44,'',0,0,'2019-07-09 15:16:21','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (161,'admin.lookarticles.create','添加类型','--',44,'',0,0,'2019-07-09 15:17:07','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (162,'admin.lookarticles.edit','修改类型','--',44,'',0,0,'2019-07-09 15:17:31','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (163,'admin.lookarticles.destroy','删除类型','--',44,'',0,0,'2019-07-09 15:17:57','2019-08-15 10:34:18',0,NULL);
INSERT INTO `permissions` VALUES (169,'admin.member.authentication','客户认证','客户认证',28,'',0,0,'2019-07-10 09:16:33','2019-08-15 10:34:17',0,NULL);
INSERT INTO `permissions` VALUES (170,'admin.customer.overdue','逾期客户列表','逾期客户列表',55,'',0,0,'2019-07-10 18:21:55','2019-08-15 10:34:19',0,NULL);
INSERT INTO `permissions` VALUES (171,'admin.customer.lose','丢单客户列表','丢单客户列表',55,'',0,0,'2019-07-10 18:23:28','2019-08-15 10:34:19',0,NULL);
DROP TABLE IF EXISTS `customer_log`;
CREATE TABLE `customer_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_id` varchar(255) DEFAULT NULL COMMENT '客户id',
  `u_id` varchar(255) DEFAULT NULL COMMENT '管理员id',
  `u_name` varchar(255) DEFAULT NULL COMMENT '管理员名称',
  `c_info` text COMMENT '修改信息',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客户修改记录';


DROP TABLE IF EXISTS `findings`;
CREATE TABLE `findings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `questionnaire_id` int(11) DEFAULT NULL COMMENT '问卷id',
  `result` text COMMENT '填写的结果',
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='问卷填写信息表';

DROP TABLE IF EXISTS `questionnaire`;
CREATE TABLE `questionnaire` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cover` varchar(255) DEFAULT NULL COMMENT '封面图片地址',
  `title` varchar(255) DEFAULT NULL COMMENT '问卷标题',
  `remarks` varchar(255) DEFAULT NULL COMMENT '说明',
  `ending` varchar(255) DEFAULT NULL COMMENT '结尾',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(3) DEFAULT '0' COMMENT '状态 0：暂停 1：收集中',
  `total` int(11) NOT NULL DEFAULT '0' COMMENT '回收总量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='问卷表';

DROP TABLE IF EXISTS `subject`;
CREATE TABLE `subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionnaire_id` int(11) DEFAULT NULL COMMENT '问卷表id',
  `title` varchar(255) DEFAULT NULL COMMENT '问题',
  `title_number` tinyint(3) DEFAULT NULL COMMENT '题号',
  `answer` text COMMENT '所有答案序列化存储',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `result` int(11) NOT NULL DEFAULT '0' COMMENT '总结果 回答总数',
  `is_fill` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否必填 1：必填 2：选填',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '问题类型  1：单选 2：多选 3：问答',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='问卷题目表';


DROP TABLE IF EXISTS `test_paper`;
CREATE TABLE `test_paper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '试卷名称',
  `type_id` int(5) DEFAULT NULL COMMENT '考试类型id',
  `item_bank_list` varchar(255) DEFAULT NULL COMMENT '试题id，json化存储',
  `created_at` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷表';

DROP TABLE IF EXISTS `item_bank`;
CREATE TABLE `item_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '题目标题',
  `annex` varchar(255) DEFAULT NULL COMMENT '标题附件',
  `type` tinyint(3) DEFAULT NULL COMMENT '类型 1单选 2多选 3填空',
  `must` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否必填 0否 1是',
  `option` varchar(255) DEFAULT NULL COMMENT '选项json存储',
  `answer` varchar(50) DEFAULT NULL COMMENT '答案',
  `fraction` tinyint(3) DEFAULT NULL COMMENT '分数',
  `remarks` varchar(300) DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库';


DROP TABLE IF EXISTS `street`;
CREATE TABLE `street` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT '父id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL COMMENT '电话',
  `admin_id` varchar(100) DEFAULT NULL COMMENT '街道所属管理员id',
  `wx_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否显示 0：否 1：显示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='街道区域表';

DROP TABLE IF EXISTS `operate_log`;
CREATE TABLE `operate_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `month` varchar(20) DEFAULT NULL COMMENT '月份',
  `customer_number` int(11) NOT NULL DEFAULT '0' COMMENT '客户数量',
  `member_number` int(11) NOT NULL DEFAULT '0' COMMENT '激活客户数量',
  `bonus_number` float(8,2) NOT NULL DEFAULT '0.00' COMMENT '本月业绩',
  `type` tinyint(3) DEFAULT NULL COMMENT '类型 1平台数据 2部门数据 3个人数据',
  `source` text COMMENT '本月来源',
  `lose_order` int(4) NOT NULL DEFAULT '0' COMMENT '本月丢单',
  `order_form` int(4) NOT NULL DEFAULT '0' COMMENT '本月成单',
  `id` int(11) DEFAULT NULL COMMENT '记录标识id，部门id或个人id 0为平台数据',
  PRIMARY KEY (`log_id`),
  KEY `log_id` (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COMMENT='平台运营数据记录表';

DROP TABLE IF EXISTS `member_contact_log`;
CREATE TABLE `member_contact_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT '客户ID',
  `admin_user_id` int(11) NOT NULL COMMENT '记录人员ID',
  `comm_time` datetime NOT NULL COMMENT '沟通时间',
  `contentlog` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `is_contact` tinyint(3) DEFAULT NULL COMMENT '是否需要联系 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `member_assign_log`;
CREATE TABLE `member_assign_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL COMMENT '客户id',
  `assign_name` varchar(50) DEFAULT NULL COMMENT '被指派人名称',
  `assign_admin` varchar(50) DEFAULT NULL COMMENT '指派人名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `assign_uid` int(11) DEFAULT NULL COMMENT '转移人',
  `assign_touid` int(11) DEFAULT NULL COMMENT '接收人',
  `operation_uid` int(11) DEFAULT NULL COMMENT '操作人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `examinee_group_role`;
CREATE TABLE `examinee_group_role` (
  `examinee_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '分组id',
  `u_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分组记录表';

DROP TABLE IF EXISTS `examinee_group`;
CREATE TABLE `examinee_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '分组名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `group_type` tinyint(1) DEFAULT NULL COMMENT '分组类型 1是员工分组 0客户分组',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='考生分组表';

DROP TABLE IF EXISTS `exam_type`;
CREATE TABLE `exam_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '类型名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷类型';

DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `name` varchar(255) DEFAULT NULL COMMENT '用户名',
  `exam_id` int(11) DEFAULT NULL COMMENT '考试id',
  `answer` text COMMENT '回答json存储',
  `branch` tinyint(3) NOT NULL DEFAULT '0' COMMENT '分数',
  `number` tinyint(3) NOT NULL DEFAULT '0' COMMENT '考试次数',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态 0不及格 1及格',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '开考时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '提交时间',
  `use_time` float(5,2) DEFAULT NULL COMMENT '用时(单位：分钟)',
  `type` tinyint(3) DEFAULT NULL COMMENT '考生类型 1客户 2员工',
  `created_at` timestamp NULL DEFAULT NULL,
  `lately_results` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='考试结果表';

DROP TABLE IF EXISTS `exam`;
CREATE TABLE `exam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '试卷标题',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '开始考试时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '结束考试时间',
  `qualified_score` tinyint(3) NOT NULL DEFAULT '0' COMMENT '合格分数',
  `total_score` tinyint(3) NOT NULL DEFAULT '0' COMMENT '总分',
  `cover` varchar(255) DEFAULT NULL COMMENT '封面图片',
  `explain` varchar(255) DEFAULT NULL COMMENT '考试说明',
  `limit_time` varchar(255) DEFAULT NULL COMMENT '考试限制时间（分钟）',
  `number` tinyint(3) NOT NULL DEFAULT '1' COMMENT '重考次数',
  `examinee_id` varchar(255) DEFAULT NULL COMMENT '考生组',
  `is_ranking` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示排名 0不显示 1显示',
  `is_copy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许复制 0否 1是',
  `is_sort` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否打乱顺序 0否 1是',
  `notice` tinyint(1) DEFAULT NULL COMMENT '通知方式 1短信 2微信  3短信和微信',
  `subject_list` text COMMENT '所选试卷题目',
  `found` varchar(255) DEFAULT NULL COMMENT '创建人',
  `type_id` int(11) DEFAULT NULL COMMENT '试卷类型id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subject_number` int(5) DEFAULT NULL COMMENT '题目总数',
  `test_paper_name` varchar(255) DEFAULT NULL COMMENT '所选试卷的名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COMMENT='考试表';

DROP TABLE IF EXISTS `achievement_extend`;
CREATE TABLE `achievement_extend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL COMMENT '客户id',
  `remind_time` timestamp NULL DEFAULT NULL COMMENT '提示时间',
  `expire` varchar(6) DEFAULT NULL COMMENT '提示信息',
  `manager_id` int(11) DEFAULT NULL COMMENT '客户经理id',
  `manager_name` varchar(255) DEFAULT NULL COMMENT '客户经理名',
  `maintain_id` int(11) DEFAULT NULL COMMENT '运维人员id',
  `maintain_name` varchar(255) DEFAULT NULL COMMENT '运维人名字',
  `duty_id` int(11) DEFAULT NULL COMMENT '责任人id',
  `duty_name` varchar(255) DEFAULT NULL COMMENT '项目负责人名称',
  `account_number` text COMMENT '账号信息json序列化存储',
  `details` varchar(500) DEFAULT NULL COMMENT '详情',
  `trusteeship` text COMMENT '托管 （地址，描述）json存储',
  `annex` text COMMENT '附件（描述，地址）json存储',
  `contract` text COMMENT '合同（地址，描述）json存储',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `star_class` tinyint(3) NOT NULL DEFAULT '1' COMMENT '星级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='售后拓展表';

DROP TABLE IF EXISTS `modular`;
CREATE TABLE `modular` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模块插件表';

DROP TABLE IF EXISTS `wxapp_page`;
CREATE TABLE `wxapp_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL COMMENT '路径',
  `name` varchar(255) DEFAULT NULL COMMENT '页面名称',
  `is_home` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否为主页 0：否 1是',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型 1：客户小程序 2：员工小程序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COMMENT='小程序页面路径表';

INSERT INTO `wxapp_page` VALUES (1,'/pages/agent/homepage/index/index','首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (2,'/pages/agent/center/index/index','个人中心',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (3,'/pages/agent/center/personal/personal','个人信息',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (4,'/pages/agent/center/album/index','我的相册',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (5,'/pages/agent/center/feedback/list/index','我的留言',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (6,'/pages/agent/center/about-us/about-us','关于我们',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (7,'/pages/agent/center/useHelp/useHelp','使用帮助',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (8,'/pages/agent/center/setting/setting','系统设置',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (9,'/pages/agent/plugins/activity/list/index','活动中心首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (10,'/pages/agent/plugins/activity/mine/index','已报名活动',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (11,'/pages/agent/plugins/business/index/index','业务商城首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (12,'/pages/agent/plugins/business/order/order','商城订单',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (13,'/pages/agent/plugins/wallet/record/record','钱包明细',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (14,'/pages/agent/plugins/wallet/recharge/recharge','快速充值',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (15,'/pages/agent/plugins/tencentCloud/searchOrder/searchOrder','腾讯云助手首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (16,'/pages/agent/plugins/tencentCloud/order/order','腾讯云订单',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (17,'/pages/agent/plugins/telephoneBook/list/index','通讯录首页',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (18,'/pages/agent/plugins/news/list/index','新闻资讯首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (19,'/pages/agent/plugins/activity/collection/index','收藏的活动',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (20,'/pages/agent/plugins/activity/like/index','点赞的活动',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (21,'/pages/agent/plugins/exam/list/index','考试培训首页',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (22,'/pages/agent/plugins/workOrder/index/index','反馈工单首页',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (23,'/pages/agent/plugins/enterprise/display/index','企业展示',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (24,'/pages/agent/plugins/enterprise/hotline/index','服务热线',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (25,'/pages/agent/plugins/video/list/list','视频中心',1,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (26,'','',0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (27,'/pages/admin/homepage/homepage/homepage','销售统计',1,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (28,'/pages/admin/homepage/ranking-list/ranking-list','排行榜',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (29,'/pages/admin/center/center','个人中心',1,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (30,'/pages/admin/center/personal-info/personal-info','个人信息',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (31,'/pages/admin/center/wallet/wallet','我的钱包',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (32,'/pages/admin/center/about-us/about-us','关于我们',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (33,'/pages/admin/center/setting/index','系统设置',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (34,'/pages/admin/plugins/index/index','应用中心',1,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (35,'/pages/admin/plugins/customer/customer-list/customer-list','客户中心',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `wxapp_page` VALUES (36,'/pages/admin/plugins/exam/list/index','考试培训',0,2,'0000-00-00 00:00:00','0000-00-00 00:00:00');

DROP TABLE IF EXISTS `transfer_work_order`;
CREATE TABLE `transfer_work_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_member_id` int(11) DEFAULT NULL COMMENT '原网格员',
  `now_member_id` int(11) DEFAULT NULL COMMENT '指派网格员',
  `work_order_id` int(11) DEFAULT NULL COMMENT '工单id',
  `created_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL COMMENT '状态 1：待审核 2：拒绝 3：通过 4：已结单',
  `change_remarks` varchar(255) DEFAULT NULL COMMENT '转单备注',
  `verify_time` timestamp NULL DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='工单转移记录表';

DROP TABLE IF EXISTS `service_hotline`;
CREATE TABLE `service_hotline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `user_name` varchar(255) DEFAULT NULL COMMENT '负责人名称',
  `mobile` varchar(20) DEFAULT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态 1：显示 0：隐藏',
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='服务热线表';



INSERT INTO `china` VALUES (1,0,'北京','1'),(2,1,'北京市','2'),(3,2,'东城区','3'),(4,2,'西城区','3'),(5,2,'朝阳区','3'),(6,2,'丰台区','3'),(7,2,'石景山区','3'),(8,2,'海淀区','3'),(9,2,'门头沟区','3'),(10,2,'房山区','3'),(11,2,'通州区','3'),(12,2,'顺义区','3'),(13,2,'昌平区','3'),(14,2,'大兴区','3'),(15,2,'怀柔区','3'),(16,2,'平谷区','3'),(17,2,'密云县','3'),(18,2,'延庆县','3'),(19,0,'天津','1'),(20,19,'天津市','2'),(21,20,'和平区','3'),(22,20,'河东区','3'),(23,20,'河西区','3'),(24,20,'南开区','3'),(25,20,'河北区','3'),(26,20,'红桥区','3'),(27,20,'东丽区','3'),(28,20,'西青区','3'),(29,20,'津南区','3'),(30,20,'北辰区','3'),(31,20,'武清区','3'),(32,20,'宝坻区','3'),(33,20,'滨海新区','3'),(34,20,'宁河县','3'),(35,20,'静海县','3'),(36,20,'蓟县','3'),(37,0,'河北省','1'),(38,37,'石家庄市','2'),(39,38,'长安区','3'),(40,38,'桥西区','3'),(41,38,'新华区','3'),(42,38,'井陉矿区','3'),(43,38,'裕华区','3'),(44,38,'藁城区','3'),(45,38,'鹿泉区','3'),(46,38,'栾城区','3'),(47,38,'井陉县','3'),(48,38,'正定县','3'),(49,38,'行唐县','3'),(50,38,'灵寿县','3'),(51,38,'高邑县','3'),(52,38,'深泽县','3'),(53,38,'赞皇县','3'),(54,38,'无极县','3'),(55,38,'平山县','3'),(56,38,'元氏县','3'),(57,38,'赵县','3'),(58,38,'辛集市','3'),(59,38,'晋州市','3'),(60,38,'新乐市','3'),(61,37,'唐山市','2'),(62,61,'路南区','3'),(63,61,'路北区','3'),(64,61,'古冶区','3'),(65,61,'开平区','3'),(66,61,'丰南区','3'),(67,61,'丰润区','3'),(68,61,'曹妃甸区','3'),(69,61,'滦县','3'),(70,61,'滦南县','3'),(71,61,'乐亭县','3'),(72,61,'迁西县','3'),(73,61,'玉田县','3'),(74,61,'遵化市','3'),(75,61,'迁安市','3'),(76,37,'秦皇岛市','2'),(77,76,'海港区','3'),(78,76,'山海关区','3'),(79,76,'北戴河区','3'),(80,76,'青龙满族自治县','3'),(81,76,'昌黎县','3'),(82,76,'抚宁县','3'),(83,76,'卢龙县','3'),(84,37,'邯郸市','2'),(85,84,'邯山区','3'),(86,84,'丛台区','3'),(87,84,'复兴区','3'),(88,84,'峰峰矿区','3'),(89,84,'邯郸县','3'),(90,84,'临漳县','3'),(91,84,'成安县','3'),(92,84,'大名县','3'),(93,84,'涉县','3'),(94,84,'磁县','3'),(95,84,'肥乡县','3'),(96,84,'永年县','3'),(97,84,'邱县','3'),(98,84,'鸡泽县','3'),(99,84,'广平县','3'),(100,84,'馆陶县','3'),(101,84,'魏县','3'),(102,84,'曲周县','3'),(103,84,'武安市','3'),(104,37,'邢台市','2'),(105,104,'桥东区','3'),(106,104,'桥西区','3'),(107,104,'邢台县','3'),(108,104,'临城县','3'),(109,104,'内丘县','3'),(110,104,'柏乡县','3'),(111,104,'隆尧县','3'),(112,104,'任县','3'),(113,104,'南和县','3'),(114,104,'宁晋县','3'),(115,104,'巨鹿县','3'),(116,104,'新河县','3'),(117,104,'广宗县','3'),(118,104,'平乡县','3'),(119,104,'威县','3'),(120,104,'清河县','3'),(121,104,'临西县','3'),(122,104,'南宫市','3'),(123,104,'沙河市','3'),(124,37,'保定市','2'),(125,124,'新市区','3'),(126,124,'北市区','3'),(127,124,'南市区','3'),(128,124,'满城县','3'),(129,124,'清苑县','3'),(130,124,'涞水县','3'),(131,124,'阜平县','3'),(132,124,'徐水县','3'),(133,124,'定兴县','3'),(134,124,'唐县','3'),(135,124,'高阳县','3'),(136,124,'容城县','3'),(137,124,'涞源县','3'),(138,124,'望都县','3'),(139,124,'安新县','3'),(140,124,'易县','3'),(141,124,'曲阳县','3'),(142,124,'蠡县','3'),(143,124,'顺平县','3'),(144,124,'博野县','3'),(145,124,'雄县','3'),(146,124,'涿州市','3'),(147,124,'定州市','3'),(148,124,'安国市','3'),(149,124,'高碑店市','3'),(150,37,'张家口市','2'),(151,150,'桥东区','3'),(152,150,'桥西区','3'),(153,150,'宣化区','3'),(154,150,'下花园区','3'),(155,150,'宣化县','3'),(156,150,'张北县','3'),(157,150,'康保县','3'),(158,150,'沽源县','3'),(159,150,'尚义县','3'),(160,150,'蔚县','3'),(161,150,'阳原县','3'),(162,150,'怀安县','3'),(163,150,'万全县','3'),(164,150,'怀来县','3'),(165,150,'涿鹿县','3'),(166,150,'赤城县','3'),(167,150,'崇礼县','3'),(168,37,'承德市','2'),(169,168,'双桥区','3'),(170,168,'双滦区','3'),(171,168,'鹰手营子矿区','3'),(172,168,'承德县','3'),(173,168,'兴隆县','3'),(174,168,'平泉县','3'),(175,168,'滦平县','3'),(176,168,'隆化县','3'),(177,168,'丰宁满族自治县','3'),(178,168,'宽城满族自治县','3'),(179,168,'围场满族蒙古族自治县','3'),(180,37,'沧州市','2'),(181,180,'新华区','3'),(182,180,'运河区','3'),(183,180,'沧县','3'),(184,180,'青县','3'),(185,180,'东光县','3'),(186,180,'海兴县','3'),(187,180,'盐山县','3'),(188,180,'肃宁县','3'),(189,180,'南皮县','3'),(190,180,'吴桥县','3'),(191,180,'献县','3'),(192,180,'孟村回族自治县','3'),(193,180,'泊头市','3'),(194,180,'任丘市','3'),(195,180,'黄骅市','3'),(196,180,'河间市','3'),(197,37,'廊坊市','2'),(198,197,'安次区','3'),(199,197,'广阳区','3'),(200,197,'固安县','3'),(201,197,'永清县','3'),(202,197,'香河县','3'),(203,197,'大城县','3'),(204,197,'文安县','3'),(205,197,'大厂回族自治县','3'),(206,197,'霸州市','3'),(207,197,'三河市','3'),(208,37,'衡水市','2'),(209,208,'桃城区','3'),(210,208,'枣强县','3'),(211,208,'武邑县','3'),(212,208,'武强县','3'),(213,208,'饶阳县','3'),(214,208,'安平县','3'),(215,208,'故城县','3'),(216,208,'景县','3'),(217,208,'阜城县','3'),(218,208,'冀州市','3'),(219,208,'深州市','3'),(220,0,'山西省','1'),(221,220,'太原市','2'),(222,221,'小店区','3'),(223,221,'迎泽区','3'),(224,221,'杏花岭区','3'),(225,221,'尖草坪区','3'),(226,221,'万柏林区','3'),(227,221,'晋源区','3'),(228,221,'清徐县','3'),(229,221,'阳曲县','3'),(230,221,'娄烦县','3'),(231,221,'古交市','3'),(232,220,'大同市','2'),(233,232,'城区','3'),(234,232,'矿区','3'),(235,232,'南郊区','3'),(236,232,'新荣区','3'),(237,232,'阳高县','3'),(238,232,'天镇县','3'),(239,232,'广灵县','3'),(240,232,'灵丘县','3'),(241,232,'浑源县','3'),(242,232,'左云县','3'),(243,232,'大同县','3'),(244,220,'阳泉市','2'),(245,244,'城区','3'),(246,244,'矿区','3'),(247,244,'郊区','3'),(248,244,'平定县','3'),(249,244,'盂县','3'),(250,220,'长治市','2'),(251,250,'城区','3'),(252,250,'郊区','3'),(253,250,'长治县','3'),(254,250,'襄垣县','3'),(255,250,'屯留县','3'),(256,250,'平顺县','3'),(257,250,'黎城县','3'),(258,250,'壶关县','3'),(259,250,'长子县','3'),(260,250,'武乡县','3'),(261,250,'沁县','3'),(262,250,'沁源县','3'),(263,250,'潞城市','3'),(264,220,'晋城市','2'),(265,264,'城区','3'),(266,264,'沁水县','3'),(267,264,'阳城县','3'),(268,264,'陵川县','3'),(269,264,'泽州县','3'),(270,264,'高平市','3'),(271,220,'朔州市','2'),(272,271,'朔城区','3'),(273,271,'平鲁区','3'),(274,271,'山阴县','3'),(275,271,'应县','3'),(276,271,'右玉县','3'),(277,271,'怀仁县','3'),(278,220,'晋中市','2'),(279,278,'榆次区','3'),(280,278,'榆社县','3'),(281,278,'左权县','3'),(282,278,'和顺县','3'),(283,278,'昔阳县','3'),(284,278,'寿阳县','3'),(285,278,'太谷县','3'),(286,278,'祁县','3'),(287,278,'平遥县','3'),(288,278,'灵石县','3'),(289,278,'介休市','3'),(290,220,'运城市','2'),(291,290,'盐湖区','3'),(292,290,'临猗县','3'),(293,290,'万荣县','3'),(294,290,'闻喜县','3'),(295,290,'稷山县','3'),(296,290,'新绛县','3'),(297,290,'绛县','3'),(298,290,'垣曲县','3'),(299,290,'夏县','3'),(300,290,'平陆县','3'),(301,290,'芮城县','3'),(302,290,'永济市','3'),(303,290,'河津市','3'),(304,220,'忻州市','2'),(305,304,'忻府区','3'),(306,304,'定襄县','3'),(307,304,'五台县','3'),(308,304,'代县','3'),(309,304,'繁峙县','3'),(310,304,'宁武县','3'),(311,304,'静乐县','3'),(312,304,'神池县','3'),(313,304,'五寨县','3'),(314,304,'岢岚县','3'),(315,304,'河曲县','3'),(316,304,'保德县','3'),(317,304,'偏关县','3'),(318,304,'原平市','3'),(319,220,'临汾市','2'),(320,319,'尧都区','3'),(321,319,'曲沃县','3'),(322,319,'翼城县','3'),(323,319,'襄汾县','3'),(324,319,'洪洞县','3'),(325,319,'古县','3'),(326,319,'安泽县','3'),(327,319,'浮山县','3'),(328,319,'吉县','3'),(329,319,'乡宁县','3'),(330,319,'大宁县','3'),(331,319,'隰县','3'),(332,319,'永和县','3'),(333,319,'蒲县','3'),(334,319,'汾西县','3'),(335,319,'侯马市','3'),(336,319,'霍州市','3'),(337,220,'吕梁市','2'),(338,337,'离石区','3'),(339,337,'文水县','3'),(340,337,'交城县','3'),(341,337,'兴县','3'),(342,337,'临县','3'),(343,337,'柳林县','3'),(344,337,'石楼县','3'),(345,337,'岚县','3'),(346,337,'方山县','3'),(347,337,'中阳县','3'),(348,337,'交口县','3'),(349,337,'孝义市','3'),(350,337,'汾阳市','3'),(351,0,'内蒙古自治区','1'),(352,351,'呼和浩特市','2'),(353,352,'新城区','3'),(354,352,'回民区','3'),(355,352,'玉泉区','3'),(356,352,'赛罕区','3'),(357,352,'土默特左旗','3'),(358,352,'托克托县','3'),(359,352,'和林格尔县','3'),(360,352,'清水河县','3'),(361,352,'武川县','3'),(362,351,'包头市','2'),(363,362,'东河区','3'),(364,362,'昆都仑区','3'),(365,362,'青山区','3'),(366,362,'石拐区','3'),(367,362,'白云鄂博矿区','3'),(368,362,'九原区','3'),(369,362,'土默特右旗','3'),(370,362,'固阳县','3'),(371,362,'达尔罕茂明安联合旗','3'),(372,351,'乌海市','2'),(373,372,'海勃湾区','3'),(374,372,'海南区','3'),(375,372,'乌达区','3'),(376,351,'赤峰市','2'),(377,376,'红山区','3'),(378,376,'元宝山区','3'),(379,376,'松山区','3'),(380,376,'阿鲁科尔沁旗','3'),(381,376,'巴林左旗','3'),(382,376,'巴林右旗','3'),(383,376,'林西县','3'),(384,376,'克什克腾旗','3'),(385,376,'翁牛特旗','3'),(386,376,'喀喇沁旗','3'),(387,376,'宁城县','3'),(388,376,'敖汉旗','3'),(389,351,'通辽市','2'),(390,389,'科尔沁区','3'),(391,389,'科尔沁左翼中旗','3'),(392,389,'科尔沁左翼后旗','3'),(393,389,'开鲁县','3'),(394,389,'库伦旗','3'),(395,389,'奈曼旗','3'),(396,389,'扎鲁特旗','3'),(397,389,'霍林郭勒市','3'),(398,351,'鄂尔多斯市','2'),(399,398,'东胜区','3'),(400,398,'达拉特旗','3'),(401,398,'准格尔旗','3'),(402,398,'鄂托克前旗','3'),(403,398,'鄂托克旗','3'),(404,398,'杭锦旗','3'),(405,398,'乌审旗','3'),(406,398,'伊金霍洛旗','3'),(407,351,'呼伦贝尔市','2'),(408,407,'海拉尔区','3'),(409,407,'扎赉诺尔区','3'),(410,407,'阿荣旗','3'),(411,407,'莫力达瓦达斡尔族自治旗','3'),(412,407,'鄂伦春自治旗','3'),(413,407,'鄂温克族自治旗','3'),(414,407,'陈巴尔虎旗','3'),(415,407,'新巴尔虎左旗','3'),(416,407,'新巴尔虎右旗','3'),(417,407,'满洲里市','3'),(418,407,'牙克石市','3'),(419,407,'扎兰屯市','3'),(420,407,'额尔古纳市','3'),(421,407,'根河市','3'),(422,351,'巴彦淖尔市','2'),(423,422,'临河区','3'),(424,422,'五原县','3'),(425,422,'磴口县','3'),(426,422,'乌拉特前旗','3'),(427,422,'乌拉特中旗','3'),(428,422,'乌拉特后旗','3'),(429,422,'杭锦后旗','3'),(430,351,'乌兰察布市','2'),(431,430,'集宁区','3'),(432,430,'卓资县','3'),(433,430,'化德县','3'),(434,430,'商都县','3'),(435,430,'兴和县','3'),(436,430,'凉城县','3'),(437,430,'察哈尔右翼前旗','3'),(438,430,'察哈尔右翼中旗','3'),(439,430,'察哈尔右翼后旗','3'),(440,430,'四子王旗','3'),(441,430,'丰镇市','3'),(442,351,'兴安盟','2'),(443,442,'乌兰浩特市','3'),(444,442,'阿尔山市','3'),(445,442,'科尔沁右翼前旗','3'),(446,442,'科尔沁右翼中旗','3'),(447,442,'扎赉特旗','3'),(448,442,'突泉县','3'),(449,351,'锡林郭勒盟','2'),(450,449,'二连浩特市','3'),(451,449,'锡林浩特市','3'),(452,449,'阿巴嘎旗','3'),(453,449,'苏尼特左旗','3'),(454,449,'苏尼特右旗','3'),(455,449,'东乌珠穆沁旗','3'),(456,449,'西乌珠穆沁旗','3'),(457,449,'太仆寺旗','3'),(458,449,'镶黄旗','3'),(459,449,'正镶白旗','3'),(460,449,'正蓝旗','3'),(461,449,'多伦县','3'),(462,351,'阿拉善盟','2'),(463,462,'阿拉善左旗','3'),(464,462,'阿拉善右旗','3'),(465,462,'额济纳旗','3'),(466,0,'辽宁省','1'),(467,466,'沈阳市','2'),(468,467,'和平区','3'),(469,467,'沈河区','3'),(470,467,'大东区','3'),(471,467,'皇姑区','3'),(472,467,'铁西区','3'),(473,467,'苏家屯区','3'),(474,467,'浑南区','3'),(475,467,'沈北新区','3'),(476,467,'于洪区','3'),(477,467,'辽中县','3'),(478,467,'康平县','3'),(479,467,'法库县','3'),(480,467,'新民市','3'),(481,466,'大连市','2'),(482,481,'中山区','3'),(483,481,'西岗区','3'),(484,481,'沙河口区','3'),(485,481,'甘井子区','3'),(486,481,'旅顺口区','3'),(487,481,'金州区','3'),(488,481,'长海县','3'),(489,481,'瓦房店市','3'),(490,481,'普兰店市','3'),(491,481,'庄河市','3'),(492,466,'鞍山市','2'),(493,492,'铁东区','3'),(494,492,'铁西区','3'),(495,492,'立山区','3'),(496,492,'千山区','3'),(497,492,'台安县','3'),(498,492,'岫岩满族自治县','3'),(499,492,'海城市','3'),(500,466,'抚顺市','2'),(501,500,'新抚区','3'),(502,500,'东洲区','3'),(503,500,'望花区','3'),(504,500,'顺城区','3'),(505,500,'抚顺县','3'),(506,500,'新宾满族自治县','3'),(507,500,'清原满族自治县','3'),(508,466,'本溪市','2'),(509,508,'平山区','3'),(510,508,'溪湖区','3'),(511,508,'明山区','3'),(512,508,'南芬区','3'),(513,508,'本溪满族自治县','3'),(514,508,'桓仁满族自治县','3'),(515,466,'丹东市','2'),(516,515,'元宝区','3'),(517,515,'振兴区','3'),(518,515,'振安区','3'),(519,515,'宽甸满族自治县','3'),(520,515,'东港市','3'),(521,515,'凤城市','3'),(522,466,'锦州市','2'),(523,522,'古塔区','3'),(524,522,'凌河区','3'),(525,522,'太和区','3'),(526,522,'黑山县','3'),(527,522,'义县','3'),(528,522,'凌海市','3'),(529,522,'北镇市','3'),(530,466,'营口市','2'),(531,530,'站前区','3'),(532,530,'西市区','3'),(533,530,'鲅鱼圈区','3'),(534,530,'老边区','3'),(535,530,'盖州市','3'),(536,530,'大石桥市','3'),(537,466,'阜新市','2'),(538,537,'海州区','3'),(539,537,'新邱区','3'),(540,537,'太平区','3'),(541,537,'清河门区','3'),(542,537,'细河区','3'),(543,537,'阜新蒙古族自治县','3'),(544,537,'彰武县','3'),(545,466,'辽阳市','2'),(546,545,'白塔区','3'),(547,545,'文圣区','3'),(548,545,'宏伟区','3'),(549,545,'弓长岭区','3'),(550,545,'太子河区','3'),(551,545,'辽阳县','3'),(552,545,'灯塔市','3'),(553,466,'盘锦市','2'),(554,553,'双台子区','3'),(555,553,'兴隆台区','3'),(556,553,'大洼县','3'),(557,553,'盘山县','3'),(558,466,'铁岭市','2'),(559,558,'银州区','3'),(560,558,'清河区','3'),(561,558,'铁岭县','3'),(562,558,'西丰县','3'),(563,558,'昌图县','3'),(564,558,'调兵山市','3'),(565,558,'开原市','3'),(566,466,'朝阳市','2'),(567,566,'双塔区','3'),(568,566,'龙城区','3'),(569,566,'朝阳县','3'),(570,566,'建平县','3'),(571,566,'喀喇沁左翼蒙古族自治县','3'),(572,566,'北票市','3'),(573,566,'凌源市','3'),(574,466,'葫芦岛市','2'),(575,574,'连山区','3'),(576,574,'龙港区','3'),(577,574,'南票区','3'),(578,574,'绥中县','3'),(579,574,'建昌县','3'),(580,574,'兴城市','3'),(581,466,'金普新区','2'),(582,581,'金州新区','3'),(583,581,'普湾新区','3'),(584,581,'保税区','3'),(585,0,'吉林省','1'),(586,585,'长春市','2'),(587,586,'南关区','3'),(588,586,'宽城区','3'),(589,586,'朝阳区','3'),(590,586,'二道区','3'),(591,586,'绿园区','3'),(592,586,'双阳区','3'),(593,586,'九台区','3'),(594,586,'农安县','3'),(595,586,'榆树市','3'),(596,586,'德惠市','3'),(597,585,'吉林市','2'),(598,597,'昌邑区','3'),(599,597,'龙潭区','3'),(600,597,'船营区','3'),(601,597,'丰满区','3'),(602,597,'永吉县','3'),(603,597,'蛟河市','3'),(604,597,'桦甸市','3'),(605,597,'舒兰市','3'),(606,597,'磐石市','3'),(607,585,'四平市','2'),(608,607,'铁西区','3'),(609,607,'铁东区','3'),(610,607,'梨树县','3'),(611,607,'伊通满族自治县','3'),(612,607,'公主岭市','3'),(613,607,'双辽市','3'),(614,585,'辽源市','2'),(615,614,'龙山区','3'),(616,614,'西安区','3'),(617,614,'东丰县','3'),(618,614,'东辽县','3'),(619,585,'通化市','2'),(620,619,'东昌区','3'),(621,619,'二道江区','3'),(622,619,'通化县','3'),(623,619,'辉南县','3'),(624,619,'柳河县','3'),(625,619,'梅河口市','3'),(626,619,'集安市','3'),(627,585,'白山市','2'),(628,627,'浑江区','3'),(629,627,'江源区','3'),(630,627,'抚松县','3'),(631,627,'靖宇县','3'),(632,627,'长白朝鲜族自治县','3'),(633,627,'临江市','3'),(634,585,'松原市','2'),(635,634,'宁江区','3'),(636,634,'前郭尔罗斯蒙古族自治县','3'),(637,634,'长岭县','3'),(638,634,'乾安县','3'),(639,634,'扶余市','3'),(640,585,'白城市','2'),(641,640,'洮北区','3'),(642,640,'镇赉县','3'),(643,640,'通榆县','3'),(644,640,'洮南市','3'),(645,640,'大安市','3'),(646,585,'延边朝鲜族自治州','2'),(647,646,'延吉市','3'),(648,646,'图们市','3'),(649,646,'敦化市','3'),(650,646,'珲春市','3'),(651,646,'龙井市','3'),(652,646,'和龙市','3'),(653,646,'汪清县','3'),(654,646,'安图县','3'),(655,0,'黑龙江省','1'),(656,655,'哈尔滨市','2'),(657,656,'道里区','3'),(658,656,'南岗区','3'),(659,656,'道外区','3'),(660,656,'平房区','3'),(661,656,'松北区','3'),(662,656,'香坊区','3'),(663,656,'呼兰区','3'),(664,656,'阿城区','3'),(665,656,'双城区','3'),(666,656,'依兰县','3'),(667,656,'方正县','3'),(668,656,'宾县','3'),(669,656,'巴彦县','3'),(670,656,'木兰县','3'),(671,656,'通河县','3'),(672,656,'延寿县','3'),(673,656,'尚志市','3'),(674,656,'五常市','3'),(675,655,'齐齐哈尔市','2'),(676,675,'龙沙区','3'),(677,675,'建华区','3'),(678,675,'铁锋区','3'),(679,675,'昂昂溪区','3'),(680,675,'富拉尔基区','3'),(681,675,'碾子山区','3'),(682,675,'梅里斯达斡尔族区','3'),(683,675,'龙江县','3'),(684,675,'依安县','3'),(685,675,'泰来县','3'),(686,675,'甘南县','3'),(687,675,'富裕县','3'),(688,675,'克山县','3'),(689,675,'克东县','3'),(690,675,'拜泉县','3'),(691,675,'讷河市','3'),(692,655,'鸡西市','2'),(693,692,'鸡冠区','3'),(694,692,'恒山区','3'),(695,692,'滴道区','3'),(696,692,'梨树区','3'),(697,692,'城子河区','3'),(698,692,'麻山区','3'),(699,692,'鸡东县','3'),(700,692,'虎林市','3'),(701,692,'密山市','3'),(702,655,'鹤岗市','2'),(703,702,'向阳区','3'),(704,702,'工农区','3'),(705,702,'南山区','3'),(706,702,'兴安区','3'),(707,702,'东山区','3'),(708,702,'兴山区','3'),(709,702,'萝北县','3'),(710,702,'绥滨县','3'),(711,655,'双鸭山市','2'),(712,711,'尖山区','3'),(713,711,'岭东区','3'),(714,711,'四方台区','3'),(715,711,'宝山区','3'),(716,711,'集贤县','3'),(717,711,'友谊县','3'),(718,711,'宝清县','3'),(719,711,'饶河县','3'),(720,655,'大庆市','2'),(721,720,'萨尔图区','3'),(722,720,'龙凤区','3'),(723,720,'让胡路区','3'),(724,720,'红岗区','3'),(725,720,'大同区','3'),(726,720,'肇州县','3'),(727,720,'肇源县','3'),(728,720,'林甸县','3'),(729,720,'杜尔伯特蒙古族自治县','3'),(730,655,'伊春市','2'),(731,730,'伊春区','3'),(732,730,'南岔区','3'),(733,730,'友好区','3'),(734,730,'西林区','3'),(735,730,'翠峦区','3'),(736,730,'新青区','3'),(737,730,'美溪区','3'),(738,730,'金山屯区','3'),(739,730,'五营区','3'),(740,730,'乌马河区','3'),(741,730,'汤旺河区','3'),(742,730,'带岭区','3'),(743,730,'乌伊岭区','3'),(744,730,'红星区','3'),(745,730,'上甘岭区','3'),(746,730,'嘉荫县','3'),(747,730,'铁力市','3'),(748,655,'佳木斯市','2'),(749,748,'向阳区','3'),(750,748,'前进区','3'),(751,748,'东风区','3'),(752,748,'郊区','3'),(753,748,'桦南县','3'),(754,748,'桦川县','3'),(755,748,'汤原县','3'),(756,748,'抚远县','3'),(757,748,'同江市','3'),(758,748,'富锦市','3'),(759,655,'七台河市','2'),(760,759,'新兴区','3'),(761,759,'桃山区','3'),(762,759,'茄子河区','3'),(763,759,'勃利县','3'),(764,655,'牡丹江市','2'),(765,764,'东安区','3'),(766,764,'阳明区','3'),(767,764,'爱民区','3'),(768,764,'西安区','3'),(769,764,'东宁县','3'),(770,764,'林口县','3'),(771,764,'绥芬河市','3'),(772,764,'海林市','3'),(773,764,'宁安市','3'),(774,764,'穆棱市','3'),(775,655,'黑河市','2'),(776,775,'爱辉区','3'),(777,775,'嫩江县','3'),(778,775,'逊克县','3'),(779,775,'孙吴县','3'),(780,775,'北安市','3'),(781,775,'五大连池市','3'),(782,655,'绥化市','2'),(783,782,'北林区','3'),(784,782,'望奎县','3'),(785,782,'兰西县','3'),(786,782,'青冈县','3'),(787,782,'庆安县','3'),(788,782,'明水县','3'),(789,782,'绥棱县','3'),(790,782,'安达市','3'),(791,782,'肇东市','3'),(792,782,'海伦市','3'),(793,655,'大兴安岭地区','2'),(794,793,'加格达奇区','3'),(795,793,'新林区','3'),(796,793,'松岭区','3'),(797,793,'呼中区','3'),(798,793,'呼玛县','3'),(799,793,'塔河县','3'),(800,793,'漠河县','3'),(801,0,'上海','1'),(802,801,'上海市','2'),(803,802,'黄浦区','3'),(804,802,'徐汇区','3'),(805,802,'长宁区','3'),(806,802,'静安区','3'),(807,802,'普陀区','3'),(808,802,'闸北区','3'),(809,802,'虹口区','3'),(810,802,'杨浦区','3'),(811,802,'闵行区','3'),(812,802,'宝山区','3'),(813,802,'嘉定区','3'),(814,802,'浦东新区','3'),(815,802,'金山区','3'),(816,802,'松江区','3'),(817,802,'青浦区','3'),(818,802,'奉贤区','3'),(819,802,'崇明县','3'),(820,0,'江苏省','1'),(821,820,'南京市','2'),(822,821,'玄武区','3'),(823,821,'秦淮区','3'),(824,821,'建邺区','3'),(825,821,'鼓楼区','3'),(826,821,'浦口区','3'),(827,821,'栖霞区','3'),(828,821,'雨花台区','3'),(829,821,'江宁区','3'),(830,821,'六合区','3'),(831,821,'溧水区','3'),(832,821,'高淳区','3'),(833,820,'无锡市','2'),(834,833,'崇安区','3'),(835,833,'南长区','3'),(836,833,'北塘区','3'),(837,833,'锡山区','3'),(838,833,'惠山区','3'),(839,833,'滨湖区','3'),(840,833,'江阴市','3'),(841,833,'宜兴市','3'),(842,820,'徐州市','2'),(843,842,'鼓楼区','3'),(844,842,'云龙区','3'),(845,842,'贾汪区','3'),(846,842,'泉山区','3'),(847,842,'铜山区','3'),(848,842,'丰县','3'),(849,842,'沛县','3'),(850,842,'睢宁县','3'),(851,842,'新沂市','3'),(852,842,'邳州市','3'),(853,820,'常州市','2'),(854,853,'天宁区','3'),(855,853,'钟楼区','3'),(856,853,'戚墅堰区','3'),(857,853,'新北区','3'),(858,853,'武进区','3'),(859,853,'溧阳市','3'),(860,853,'金坛市','3'),(861,820,'苏州市','2'),(862,861,'虎丘区','3'),(863,861,'吴中区','3'),(864,861,'相城区','3'),(865,861,'姑苏区','3'),(866,861,'吴江区','3'),(867,861,'常熟市','3'),(868,861,'张家港市','3'),(869,861,'昆山市','3'),(870,861,'太仓市','3'),(871,820,'南通市','2'),(872,871,'崇川区','3'),(873,871,'港闸区','3'),(874,871,'通州区','3'),(875,871,'海安县','3'),(876,871,'如东县','3'),(877,871,'启东市','3'),(878,871,'如皋市','3'),(879,871,'海门市','3'),(880,820,'连云港市','2'),(881,880,'连云区','3'),(882,880,'海州区','3'),(883,880,'赣榆区','3'),(884,880,'东海县','3'),(885,880,'灌云县','3'),(886,880,'灌南县','3'),(887,820,'淮安市','2'),(888,887,'清河区','3'),(889,887,'淮安区','3'),(890,887,'淮阴区','3'),(891,887,'清浦区','3'),(892,887,'涟水县','3'),(893,887,'洪泽县','3'),(894,887,'盱眙县','3'),(895,887,'金湖县','3'),(896,820,'盐城市','2'),(897,896,'亭湖区','3'),(898,896,'盐都区','3'),(899,896,'响水县','3'),(900,896,'滨海县','3'),(901,896,'阜宁县','3'),(902,896,'射阳县','3'),(903,896,'建湖县','3'),(904,896,'东台市','3'),(905,896,'大丰市','3'),(906,820,'扬州市','2'),(907,906,'广陵区','3'),(908,906,'邗江区','3'),(909,906,'江都区','3'),(910,906,'宝应县','3'),(911,906,'仪征市','3'),(912,906,'高邮市','3'),(913,820,'镇江市','2'),(914,913,'京口区','3'),(915,913,'润州区','3'),(916,913,'丹徒区','3'),(917,913,'丹阳市','3'),(918,913,'扬中市','3'),(919,913,'句容市','3'),(920,820,'泰州市','2'),(921,920,'海陵区','3'),(922,920,'高港区','3'),(923,920,'姜堰区','3'),(924,920,'兴化市','3'),(925,920,'靖江市','3'),(926,920,'泰兴市','3'),(927,820,'宿迁市','2'),(928,927,'宿城区','3'),(929,927,'宿豫区','3'),(930,927,'沭阳县','3'),(931,927,'泗阳县','3'),(932,927,'泗洪县','3'),(933,0,'浙江省','1'),(934,933,'杭州市','2'),(935,934,'上城区','3'),(936,934,'下城区','3'),(937,934,'江干区','3'),(938,934,'拱墅区','3'),(939,934,'西湖区','3'),(940,934,'滨江区','3'),(941,934,'萧山区','3'),(942,934,'余杭区','3'),(943,934,'桐庐县','3'),(944,934,'淳安县','3'),(945,934,'建德市','3'),(946,934,'富阳区','3'),(947,934,'临安市','3'),(948,933,'宁波市','2'),(949,948,'海曙区','3'),(950,948,'江东区','3'),(951,948,'江北区','3'),(952,948,'北仑区','3'),(953,948,'镇海区','3'),(954,948,'鄞州区','3'),(955,948,'象山县','3'),(956,948,'宁海县','3'),(957,948,'余姚市','3'),(958,948,'慈溪市','3'),(959,948,'奉化市','3'),(960,933,'温州市','2'),(961,960,'鹿城区','3'),(962,960,'龙湾区','3'),(963,960,'瓯海区','3'),(964,960,'洞头县','3'),(965,960,'永嘉县','3'),(966,960,'平阳县','3'),(967,960,'苍南县','3'),(968,960,'文成县','3'),(969,960,'泰顺县','3'),(970,960,'瑞安市','3'),(971,960,'乐清市','3'),(972,933,'嘉兴市','2'),(973,972,'南湖区','3'),(974,972,'秀洲区','3'),(975,972,'嘉善县','3'),(976,972,'海盐县','3'),(977,972,'海宁市','3'),(978,972,'平湖市','3'),(979,972,'桐乡市','3'),(980,933,'湖州市','2'),(981,980,'吴兴区','3'),(982,980,'南浔区','3'),(983,980,'德清县','3'),(984,980,'长兴县','3'),(985,980,'安吉县','3'),(986,933,'绍兴市','2'),(987,986,'越城区','3'),(988,986,'柯桥区','3'),(989,986,'上虞区','3'),(990,986,'新昌县','3'),(991,986,'诸暨市','3'),(992,986,'嵊州市','3'),(993,933,'金华市','2'),(994,993,'婺城区','3'),(995,993,'金东区','3'),(996,993,'武义县','3'),(997,993,'浦江县','3'),(998,993,'磐安县','3'),(999,993,'兰溪市','3'),(1000,993,'义乌市','3'),(1001,993,'东阳市','3'),(1002,993,'永康市','3'),(1003,933,'衢州市','2'),(1004,1003,'柯城区','3'),(1005,1003,'衢江区','3'),(1006,1003,'常山县','3'),(1007,1003,'开化县','3'),(1008,1003,'龙游县','3'),(1009,1003,'江山市','3'),(1010,933,'舟山市','2'),(1011,1010,'定海区','3'),(1012,1010,'普陀区','3'),(1013,1010,'岱山县','3'),(1014,1010,'嵊泗县','3'),(1015,933,'台州市','2'),(1016,1015,'椒江区','3'),(1017,1015,'黄岩区','3'),(1018,1015,'路桥区','3'),(1019,1015,'玉环县','3'),(1020,1015,'三门县','3'),(1021,1015,'天台县','3'),(1022,1015,'仙居县','3'),(1023,1015,'温岭市','3'),(1024,1015,'临海市','3'),(1025,933,'丽水市','2'),(1026,1025,'莲都区','3'),(1027,1025,'青田县','3'),(1028,1025,'缙云县','3'),(1029,1025,'遂昌县','3'),(1030,1025,'松阳县','3'),(1031,1025,'云和县','3'),(1032,1025,'庆元县','3'),(1033,1025,'景宁畲族自治县','3'),(1034,1025,'龙泉市','3'),(1035,933,'舟山群岛新区','2'),(1036,1035,'金塘岛','3'),(1037,1035,'六横岛','3'),(1038,1035,'衢山岛','3'),(1039,1035,'舟山本岛西北部','3'),(1040,1035,'岱山岛西南部','3'),(1041,1035,'泗礁岛','3'),(1042,1035,'朱家尖岛','3'),(1043,1035,'洋山岛','3'),(1044,1035,'长涂岛','3'),(1045,1035,'虾峙岛','3'),(1046,0,'安徽省','1'),(1047,1046,'合肥市','2'),(1048,1047,'瑶海区','3'),(1049,1047,'庐阳区','3'),(1050,1047,'蜀山区','3'),(1051,1047,'包河区','3'),(1052,1047,'长丰县','3'),(1053,1047,'肥东县','3'),(1054,1047,'肥西县','3'),(1055,1047,'庐江县','3'),(1056,1047,'巢湖市','3'),(1057,1046,'芜湖市','2'),(1058,1057,'镜湖区','3'),(1059,1057,'弋江区','3'),(1060,1057,'鸠江区','3'),(1061,1057,'三山区','3'),(1062,1057,'芜湖县','3'),(1063,1057,'繁昌县','3'),(1064,1057,'南陵县','3'),(1065,1057,'无为县','3'),(1066,1046,'蚌埠市','2'),(1067,1066,'龙子湖区','3'),(1068,1066,'蚌山区','3'),(1069,1066,'禹会区','3'),(1070,1066,'淮上区','3'),(1071,1066,'怀远县','3'),(1072,1066,'五河县','3'),(1073,1066,'固镇县','3'),(1074,1046,'淮南市','2'),(1075,1074,'大通区','3'),(1076,1074,'田家庵区','3'),(1077,1074,'谢家集区','3'),(1078,1074,'八公山区','3'),(1079,1074,'潘集区','3'),(1080,1074,'凤台县','3'),(1081,1046,'马鞍山市','2'),(1082,1081,'花山区','3'),(1083,1081,'雨山区','3'),(1084,1081,'博望区','3'),(1085,1081,'当涂县','3'),(1086,1081,'含山县','3'),(1087,1081,'和县','3'),(1088,1046,'淮北市','2'),(1089,1088,'杜集区','3'),(1090,1088,'相山区','3'),(1091,1088,'烈山区','3'),(1092,1088,'濉溪县','3'),(1093,1046,'铜陵市','2'),(1094,1093,'铜官山区','3'),(1095,1093,'狮子山区','3'),(1096,1093,'郊区','3'),(1097,1093,'铜陵县','3'),(1098,1046,'安庆市','2'),(1099,1098,'迎江区','3'),(1100,1098,'大观区','3'),(1101,1098,'宜秀区','3'),(1102,1098,'怀宁县','3'),(1103,1098,'枞阳县','3'),(1104,1098,'潜山县','3'),(1105,1098,'太湖县','3'),(1106,1098,'宿松县','3'),(1107,1098,'望江县','3'),(1108,1098,'岳西县','3'),(1109,1098,'桐城市','3'),(1110,1046,'黄山市','2'),(1111,1110,'屯溪区','3'),(1112,1110,'黄山区','3'),(1113,1110,'徽州区','3'),(1114,1110,'歙县','3'),(1115,1110,'休宁县','3'),(1116,1110,'黟县','3'),(1117,1110,'祁门县','3'),(1118,1046,'滁州市','2'),(1119,1118,'琅琊区','3'),(1120,1118,'南谯区','3'),(1121,1118,'来安县','3'),(1122,1118,'全椒县','3'),(1123,1118,'定远县','3'),(1124,1118,'凤阳县','3'),(1125,1118,'天长市','3'),(1126,1118,'明光市','3'),(1127,1046,'阜阳市','2'),(1128,1127,'颍州区','3'),(1129,1127,'颍东区','3'),(1130,1127,'颍泉区','3'),(1131,1127,'临泉县','3'),(1132,1127,'太和县','3'),(1133,1127,'阜南县','3'),(1134,1127,'颍上县','3'),(1135,1127,'界首市','3'),(1136,1046,'宿州市','2'),(1137,1136,'埇桥区','3'),(1138,1136,'砀山县','3'),(1139,1136,'萧县','3'),(1140,1136,'灵璧县','3'),(1141,1136,'泗县','3'),(1142,1046,'六安市','2'),(1143,1142,'金安区','3'),(1144,1142,'裕安区','3'),(1145,1142,'寿县','3'),(1146,1142,'霍邱县','3'),(1147,1142,'舒城县','3'),(1148,1142,'金寨县','3'),(1149,1142,'霍山县','3'),(1150,1046,'亳州市','2'),(1151,1150,'谯城区','3'),(1152,1150,'涡阳县','3'),(1153,1150,'蒙城县','3'),(1154,1150,'利辛县','3'),(1155,1046,'池州市','2'),(1156,1155,'贵池区','3'),(1157,1155,'东至县','3'),(1158,1155,'石台县','3'),(1159,1155,'青阳县','3'),(1160,1046,'宣城市','2'),(1161,1160,'宣州区','3'),(1162,1160,'郎溪县','3'),(1163,1160,'广德县','3'),(1164,1160,'泾县','3'),(1165,1160,'绩溪县','3'),(1166,1160,'旌德县','3'),(1167,1160,'宁国市','3'),(1168,0,'福建省','1'),(1169,1168,'福州市','2'),(1170,1169,'鼓楼区','3'),(1171,1169,'台江区','3'),(1172,1169,'仓山区','3'),(1173,1169,'马尾区','3'),(1174,1169,'晋安区','3'),(1175,1169,'闽侯县','3'),(1176,1169,'连江县','3'),(1177,1169,'罗源县','3'),(1178,1169,'闽清县','3'),(1179,1169,'永泰县','3'),(1180,1169,'平潭县','3'),(1181,1169,'福清市','3'),(1182,1169,'长乐市','3'),(1183,1168,'厦门市','2'),(1184,1183,'思明区','3'),(1185,1183,'海沧区','3'),(1186,1183,'湖里区','3'),(1187,1183,'集美区','3'),(1188,1183,'同安区','3'),(1189,1183,'翔安区','3'),(1190,1168,'莆田市','2'),(1191,1190,'城厢区','3'),(1192,1190,'涵江区','3'),(1193,1190,'荔城区','3'),(1194,1190,'秀屿区','3'),(1195,1190,'仙游县','3'),(1196,1168,'三明市','2'),(1197,1196,'梅列区','3'),(1198,1196,'三元区','3'),(1199,1196,'明溪县','3'),(1200,1196,'清流县','3'),(1201,1196,'宁化县','3'),(1202,1196,'大田县','3'),(1203,1196,'尤溪县','3'),(1204,1196,'沙县','3'),(1205,1196,'将乐县','3'),(1206,1196,'泰宁县','3'),(1207,1196,'建宁县','3'),(1208,1196,'永安市','3'),(1209,1168,'泉州市','2'),(1210,1209,'鲤城区','3'),(1211,1209,'丰泽区','3'),(1212,1209,'洛江区','3'),(1213,1209,'泉港区','3'),(1214,1209,'惠安县','3'),(1215,1209,'安溪县','3'),(1216,1209,'永春县','3'),(1217,1209,'德化县','3'),(1218,1209,'金门县','3'),(1219,1209,'石狮市','3'),(1220,1209,'晋江市','3'),(1221,1209,'南安市','3'),(1222,1168,'漳州市','2'),(1223,1222,'芗城区','3'),(1224,1222,'龙文区','3'),(1225,1222,'云霄县','3'),(1226,1222,'漳浦县','3'),(1227,1222,'诏安县','3'),(1228,1222,'长泰县','3'),(1229,1222,'东山县','3'),(1230,1222,'南靖县','3'),(1231,1222,'平和县','3'),(1232,1222,'华安县','3'),(1233,1222,'龙海市','3'),(1234,1168,'南平市','2'),(1235,1234,'延平区','3'),(1236,1234,'建阳区','3'),(1237,1234,'顺昌县','3'),(1238,1234,'浦城县','3'),(1239,1234,'光泽县','3'),(1240,1234,'松溪县','3'),(1241,1234,'政和县','3'),(1242,1234,'邵武市','3'),(1243,1234,'武夷山市','3'),(1244,1234,'建瓯市','3'),(1245,1168,'龙岩市','2'),(1246,1245,'新罗区','3'),(1247,1245,'长汀县','3'),(1248,1245,'永定区','3'),(1249,1245,'上杭县','3'),(1250,1245,'武平县','3'),(1251,1245,'连城县','3'),(1252,1245,'漳平市','3'),(1253,1168,'宁德市','2'),(1254,1253,'蕉城区','3'),(1255,1253,'霞浦县','3'),(1256,1253,'古田县','3'),(1257,1253,'屏南县','3'),(1258,1253,'寿宁县','3'),(1259,1253,'周宁县','3'),(1260,1253,'柘荣县','3'),(1261,1253,'福安市','3'),(1262,1253,'福鼎市','3'),(1263,0,'江西省','1'),(1264,1263,'南昌市','2'),(1265,1264,'东湖区','3'),(1266,1264,'西湖区','3'),(1267,1264,'青云谱区','3'),(1268,1264,'湾里区','3'),(1269,1264,'青山湖区','3'),(1270,1264,'南昌县','3'),(1271,1264,'新建县','3'),(1272,1264,'安义县','3'),(1273,1264,'进贤县','3'),(1274,1263,'景德镇市','2'),(1275,1274,'昌江区','3'),(1276,1274,'珠山区','3'),(1277,1274,'浮梁县','3'),(1278,1274,'乐平市','3'),(1279,1263,'萍乡市','2'),(1280,1279,'安源区','3'),(1281,1279,'湘东区','3'),(1282,1279,'莲花县','3'),(1283,1279,'上栗县','3'),(1284,1279,'芦溪县','3'),(1285,1263,'九江市','2'),(1286,1285,'庐山区','3'),(1287,1285,'浔阳区','3'),(1288,1285,'九江县','3'),(1289,1285,'武宁县','3'),(1290,1285,'修水县','3'),(1291,1285,'永修县','3'),(1292,1285,'德安县','3'),(1293,1285,'星子县','3'),(1294,1285,'都昌县','3'),(1295,1285,'湖口县','3'),(1296,1285,'彭泽县','3'),(1297,1285,'瑞昌市','3'),(1298,1285,'共青城市','3'),(1299,1263,'新余市','2'),(1300,1299,'渝水区','3'),(1301,1299,'分宜县','3'),(1302,1263,'鹰潭市','2'),(1303,1302,'月湖区','3'),(1304,1302,'余江县','3'),(1305,1302,'贵溪市','3'),(1306,1263,'赣州市','2'),(1307,1306,'章贡区','3'),(1308,1306,'南康区','3'),(1309,1306,'赣县','3'),(1310,1306,'信丰县','3'),(1311,1306,'大余县','3'),(1312,1306,'上犹县','3'),(1313,1306,'崇义县','3'),(1314,1306,'安远县','3'),(1315,1306,'龙南县','3'),(1316,1306,'定南县','3'),(1317,1306,'全南县','3'),(1318,1306,'宁都县','3'),(1319,1306,'于都县','3'),(1320,1306,'兴国县','3'),(1321,1306,'会昌县','3'),(1322,1306,'寻乌县','3'),(1323,1306,'石城县','3'),(1324,1306,'瑞金市','3'),(1325,1263,'吉安市','2'),(1326,1325,'吉州区','3'),(1327,1325,'青原区','3'),(1328,1325,'吉安县','3'),(1329,1325,'吉水县','3'),(1330,1325,'峡江县','3'),(1331,1325,'新干县','3'),(1332,1325,'永丰县','3'),(1333,1325,'泰和县','3'),(1334,1325,'遂川县','3'),(1335,1325,'万安县','3'),(1336,1325,'安福县','3'),(1337,1325,'永新县','3'),(1338,1325,'井冈山市','3'),(1339,1263,'宜春市','2'),(1340,1339,'袁州区','3'),(1341,1339,'奉新县','3'),(1342,1339,'万载县','3'),(1343,1339,'上高县','3'),(1344,1339,'宜丰县','3'),(1345,1339,'靖安县','3'),(1346,1339,'铜鼓县','3'),(1347,1339,'丰城市','3'),(1348,1339,'樟树市','3'),(1349,1339,'高安市','3'),(1350,1263,'抚州市','2'),(1351,1350,'临川区','3'),(1352,1350,'南城县','3'),(1353,1350,'黎川县','3'),(1354,1350,'南丰县','3'),(1355,1350,'崇仁县','3'),(1356,1350,'乐安县','3'),(1357,1350,'宜黄县','3'),(1358,1350,'金溪县','3'),(1359,1350,'资溪县','3'),(1360,1350,'东乡县','3'),(1361,1350,'广昌县','3'),(1362,1263,'上饶市','2'),(1363,1362,'信州区','3'),(1364,1362,'上饶县','3'),(1365,1362,'广丰县','3'),(1366,1362,'玉山县','3'),(1367,1362,'铅山县','3'),(1368,1362,'横峰县','3'),(1369,1362,'弋阳县','3'),(1370,1362,'余干县','3'),(1371,1362,'鄱阳县','3'),(1372,1362,'万年县','3'),(1373,1362,'婺源县','3'),(1374,1362,'德兴市','3'),(1375,0,'山东省','1'),(1376,1375,'济南市','2'),(1377,1376,'历下区','3'),(1378,1376,'市中区','3'),(1379,1376,'槐荫区','3'),(1380,1376,'天桥区','3'),(1381,1376,'历城区','3'),(1382,1376,'长清区','3'),(1383,1376,'平阴县','3'),(1384,1376,'济阳县','3'),(1385,1376,'商河县','3'),(1386,1376,'章丘市','3'),(1387,1375,'青岛市','2'),(1388,1387,'市南区','3'),(1389,1387,'市北区','3'),(1390,1387,'黄岛区','3'),(1391,1387,'崂山区','3'),(1392,1387,'李沧区','3'),(1393,1387,'城阳区','3'),(1394,1387,'胶州市','3'),(1395,1387,'即墨市','3'),(1396,1387,'平度市','3'),(1397,1387,'莱西市','3'),(1398,1387,'西海岸新区','3'),(1399,1375,'淄博市','2'),(1400,1399,'淄川区','3'),(1401,1399,'张店区','3'),(1402,1399,'博山区','3'),(1403,1399,'临淄区','3'),(1404,1399,'周村区','3'),(1405,1399,'桓台县','3'),(1406,1399,'高青县','3'),(1407,1399,'沂源县','3'),(1408,1375,'枣庄市','2'),(1409,1408,'市中区','3'),(1410,1408,'薛城区','3'),(1411,1408,'峄城区','3'),(1412,1408,'台儿庄区','3'),(1413,1408,'山亭区','3'),(1414,1408,'滕州市','3'),(1415,1375,'东营市','2'),(1416,1415,'东营区','3'),(1417,1415,'河口区','3'),(1418,1415,'垦利县','3'),(1419,1415,'利津县','3'),(1420,1415,'广饶县','3'),(1421,1375,'烟台市','2'),(1422,1421,'芝罘区','3'),(1423,1421,'福山区','3'),(1424,1421,'牟平区','3'),(1425,1421,'莱山区','3'),(1426,1421,'长岛县','3'),(1427,1421,'龙口市','3'),(1428,1421,'莱阳市','3'),(1429,1421,'莱州市','3'),(1430,1421,'蓬莱市','3'),(1431,1421,'招远市','3'),(1432,1421,'栖霞市','3'),(1433,1421,'海阳市','3'),(1434,1375,'潍坊市','2'),(1435,1434,'潍城区','3'),(1436,1434,'寒亭区','3'),(1437,1434,'坊子区','3'),(1438,1434,'奎文区','3'),(1439,1434,'临朐县','3'),(1440,1434,'昌乐县','3'),(1441,1434,'青州市','3'),(1442,1434,'诸城市','3'),(1443,1434,'寿光市','3'),(1444,1434,'安丘市','3'),(1445,1434,'高密市','3'),(1446,1434,'昌邑市','3'),(1447,1375,'济宁市','2'),(1448,1447,'任城区','3'),(1449,1447,'兖州区','3'),(1450,1447,'微山县','3'),(1451,1447,'鱼台县','3'),(1452,1447,'金乡县','3'),(1453,1447,'嘉祥县','3'),(1454,1447,'汶上县','3'),(1455,1447,'泗水县','3'),(1456,1447,'梁山县','3'),(1457,1447,'曲阜市','3'),(1458,1447,'邹城市','3'),(1459,1375,'泰安市','2'),(1460,1459,'泰山区','3'),(1461,1459,'岱岳区','3'),(1462,1459,'宁阳县','3'),(1463,1459,'东平县','3'),(1464,1459,'新泰市','3'),(1465,1459,'肥城市','3'),(1466,1375,'威海市','2'),(1467,1466,'环翠区','3'),(1468,1466,'文登区','3'),(1469,1466,'荣成市','3'),(1470,1466,'乳山市','3'),(1471,1375,'日照市','2'),(1472,1471,'东港区','3'),(1473,1471,'岚山区','3'),(1474,1471,'五莲县','3'),(1475,1471,'莒县','3'),(1476,1375,'莱芜市','2'),(1477,1476,'莱城区','3'),(1478,1476,'钢城区','3'),(1479,1375,'临沂市','2'),(1480,1479,'兰山区','3'),(1481,1479,'罗庄区','3'),(1482,1479,'河东区','3'),(1483,1479,'沂南县','3'),(1484,1479,'郯城县','3'),(1485,1479,'沂水县','3'),(1486,1479,'兰陵县','3'),(1487,1479,'费县','3'),(1488,1479,'平邑县','3'),(1489,1479,'莒南县','3'),(1490,1479,'蒙阴县','3'),(1491,1479,'临沭县','3'),(1492,1375,'德州市','2'),(1493,1492,'德城区','3'),(1494,1492,'陵城区','3'),(1495,1492,'宁津县','3'),(1496,1492,'庆云县','3'),(1497,1492,'临邑县','3'),(1498,1492,'齐河县','3'),(1499,1492,'平原县','3'),(1500,1492,'夏津县','3'),(1501,1492,'武城县','3'),(1502,1492,'乐陵市','3'),(1503,1492,'禹城市','3'),(1504,1375,'聊城市','2'),(1505,1504,'东昌府区','3'),(1506,1504,'阳谷县','3'),(1507,1504,'莘县','3'),(1508,1504,'茌平县','3'),(1509,1504,'东阿县','3'),(1510,1504,'冠县','3'),(1511,1504,'高唐县','3'),(1512,1504,'临清市','3'),(1513,1375,'滨州市','2'),(1514,1513,'滨城区','3'),(1515,1513,'沾化区','3'),(1516,1513,'惠民县','3'),(1517,1513,'阳信县','3'),(1518,1513,'无棣县','3'),(1519,1513,'博兴县','3'),(1520,1513,'邹平县','3'),(1521,1513,'北海新区','3'),(1522,1375,'菏泽市','2'),(1523,1522,'牡丹区','3'),(1524,1522,'曹县','3'),(1525,1522,'单县','3'),(1526,1522,'成武县','3'),(1527,1522,'巨野县','3'),(1528,1522,'郓城县','3'),(1529,1522,'鄄城县','3'),(1530,1522,'定陶县','3'),(1531,1522,'东明县','3'),(1532,0,'河南省','1'),(1533,1532,'郑州市','2'),(1534,1533,'中原区','3'),(1535,1533,'二七区','3'),(1536,1533,'管城回族区','3'),(1537,1533,'金水区','3'),(1538,1533,'上街区','3'),(1539,1533,'惠济区','3'),(1540,1533,'中牟县','3'),(1541,1533,'巩义市','3'),(1542,1533,'荥阳市','3'),(1543,1533,'新密市','3'),(1544,1533,'新郑市','3'),(1545,1533,'登封市','3'),(1546,1532,'开封市','2'),(1547,1546,'龙亭区','3'),(1548,1546,'顺河回族区','3'),(1549,1546,'鼓楼区','3'),(1550,1546,'禹王台区','3'),(1551,1546,'祥符区','3'),(1552,1546,'杞县','3'),(1553,1546,'通许县','3'),(1554,1546,'尉氏县','3'),(1555,1546,'兰考县','3'),(1556,1532,'洛阳市','2'),(1557,1556,'老城区','3'),(1558,1556,'西工区','3'),(1559,1556,'瀍河回族区','3'),(1560,1556,'涧西区','3'),(1561,1556,'吉利区','3'),(1562,1556,'洛龙区','3'),(1563,1556,'孟津县','3'),(1564,1556,'新安县','3'),(1565,1556,'栾川县','3'),(1566,1556,'嵩县','3'),(1567,1556,'汝阳县','3'),(1568,1556,'宜阳县','3'),(1569,1556,'洛宁县','3'),(1570,1556,'伊川县','3'),(1571,1556,'偃师市','3'),(1572,1532,'平顶山市','2'),(1573,1572,'新华区','3'),(1574,1572,'卫东区','3'),(1575,1572,'石龙区','3'),(1576,1572,'湛河区','3'),(1577,1572,'宝丰县','3'),(1578,1572,'叶县','3'),(1579,1572,'鲁山县','3'),(1580,1572,'郏县','3'),(1581,1572,'舞钢市','3'),(1582,1572,'汝州市','3'),(1583,1532,'安阳市','2'),(1584,1583,'文峰区','3'),(1585,1583,'北关区','3'),(1586,1583,'殷都区','3'),(1587,1583,'龙安区','3'),(1588,1583,'安阳县','3'),(1589,1583,'汤阴县','3'),(1590,1583,'滑县','3'),(1591,1583,'内黄县','3'),(1592,1583,'林州市','3'),(1593,1532,'鹤壁市','2'),(1594,1593,'鹤山区','3'),(1595,1593,'山城区','3'),(1596,1593,'淇滨区','3'),(1597,1593,'浚县','3'),(1598,1593,'淇县','3'),(1599,1532,'新乡市','2'),(1600,1599,'红旗区','3'),(1601,1599,'卫滨区','3'),(1602,1599,'凤泉区','3'),(1603,1599,'牧野区','3'),(1604,1599,'新乡县','3'),(1605,1599,'获嘉县','3'),(1606,1599,'原阳县','3'),(1607,1599,'延津县','3'),(1608,1599,'封丘县','3'),(1609,1599,'长垣县','3'),(1610,1599,'卫辉市','3'),(1611,1599,'辉县市','3'),(1612,1532,'焦作市','2'),(1613,1612,'解放区','3'),(1614,1612,'中站区','3'),(1615,1612,'马村区','3'),(1616,1612,'山阳区','3'),(1617,1612,'修武县','3'),(1618,1612,'博爱县','3'),(1619,1612,'武陟县','3'),(1620,1612,'温县','3'),(1621,1612,'沁阳市','3'),(1622,1612,'孟州市','3'),(1623,1532,'濮阳市','2'),(1624,1623,'华龙区','3'),(1625,1623,'清丰县','3'),(1626,1623,'南乐县','3'),(1627,1623,'范县','3'),(1628,1623,'台前县','3'),(1629,1623,'濮阳县','3'),(1630,1532,'许昌市','2'),(1631,1630,'魏都区','3'),(1632,1630,'许昌县','3'),(1633,1630,'鄢陵县','3'),(1634,1630,'襄城县','3'),(1635,1630,'禹州市','3'),(1636,1630,'长葛市','3'),(1637,1532,'漯河市','2'),(1638,1637,'源汇区','3'),(1639,1637,'郾城区','3'),(1640,1637,'召陵区','3'),(1641,1637,'舞阳县','3'),(1642,1637,'临颍县','3'),(1643,1532,'三门峡市','2'),(1644,1643,'湖滨区','3'),(1645,1643,'渑池县','3'),(1646,1643,'陕县','3'),(1647,1643,'卢氏县','3'),(1648,1643,'义马市','3'),(1649,1643,'灵宝市','3'),(1650,1532,'南阳市','2'),(1651,1650,'宛城区','3'),(1652,1650,'卧龙区','3'),(1653,1650,'南召县','3'),(1654,1650,'方城县','3'),(1655,1650,'西峡县','3'),(1656,1650,'镇平县','3'),(1657,1650,'内乡县','3'),(1658,1650,'淅川县','3'),(1659,1650,'社旗县','3'),(1660,1650,'唐河县','3'),(1661,1650,'新野县','3'),(1662,1650,'桐柏县','3'),(1663,1650,'邓州市','3'),(1664,1532,'商丘市','2'),(1665,1664,'梁园区','3'),(1666,1664,'睢阳区','3'),(1667,1664,'民权县','3'),(1668,1664,'睢县','3'),(1669,1664,'宁陵县','3'),(1670,1664,'柘城县','3'),(1671,1664,'虞城县','3'),(1672,1664,'夏邑县','3'),(1673,1664,'永城市','3'),(1674,1532,'信阳市','2'),(1675,1674,'浉河区','3'),(1676,1674,'平桥区','3'),(1677,1674,'罗山县','3'),(1678,1674,'光山县','3'),(1679,1674,'新县','3'),(1680,1674,'商城县','3'),(1681,1674,'固始县','3'),(1682,1674,'潢川县','3'),(1683,1674,'淮滨县','3'),(1684,1674,'息县','3'),(1685,1532,'周口市','2'),(1686,1685,'川汇区','3'),(1687,1685,'扶沟县','3'),(1688,1685,'西华县','3'),(1689,1685,'商水县','3'),(1690,1685,'沈丘县','3'),(1691,1685,'郸城县','3'),(1692,1685,'淮阳县','3'),(1693,1685,'太康县','3'),(1694,1685,'鹿邑县','3'),(1695,1685,'项城市','3'),(1696,1532,'驻马店市','2'),(1697,1696,'驿城区','3'),(1698,1696,'西平县','3'),(1699,1696,'上蔡县','3'),(1700,1696,'平舆县','3'),(1701,1696,'正阳县','3'),(1702,1696,'确山县','3'),(1703,1696,'泌阳县','3'),(1704,1696,'汝南县','3'),(1705,1696,'遂平县','3'),(1706,1696,'新蔡县','3'),(1707,1532,'直辖县级','2'),(1708,1707,'济源市','3'),(1709,0,'湖北省','1'),(1710,1709,'武汉市','2'),(1711,1710,'江岸区','3'),(1712,1710,'江汉区','3'),(1713,1710,'硚口区','3'),(1714,1710,'汉阳区','3'),(1715,1710,'武昌区','3'),(1716,1710,'青山区','3'),(1717,1710,'洪山区','3'),(1718,1710,'东西湖区','3'),(1719,1710,'汉南区','3'),(1720,1710,'蔡甸区','3'),(1721,1710,'江夏区','3'),(1722,1710,'黄陂区','3'),(1723,1710,'新洲区','3'),(1724,1709,'黄石市','2'),(1725,1724,'黄石港区','3'),(1726,1724,'西塞山区','3'),(1727,1724,'下陆区','3'),(1728,1724,'铁山区','3'),(1729,1724,'阳新县','3'),(1730,1724,'大冶市','3'),(1731,1709,'十堰市','2'),(1732,1731,'茅箭区','3'),(1733,1731,'张湾区','3'),(1734,1731,'郧阳区','3'),(1735,1731,'郧西县','3'),(1736,1731,'竹山县','3'),(1737,1731,'竹溪县','3'),(1738,1731,'房县','3'),(1739,1731,'丹江口市','3'),(1740,1709,'宜昌市','2'),(1741,1740,'西陵区','3'),(1742,1740,'伍家岗区','3'),(1743,1740,'点军区','3'),(1744,1740,'猇亭区','3'),(1745,1740,'夷陵区','3'),(1746,1740,'远安县','3'),(1747,1740,'兴山县','3'),(1748,1740,'秭归县','3'),(1749,1740,'长阳土家族自治县','3'),(1750,1740,'五峰土家族自治县','3'),(1751,1740,'宜都市','3'),(1752,1740,'当阳市','3'),(1753,1740,'枝江市','3'),(1754,1709,'襄阳市','2'),(1755,1754,'襄城区','3'),(1756,1754,'樊城区','3'),(1757,1754,'襄州区','3'),(1758,1754,'南漳县','3'),(1759,1754,'谷城县','3'),(1760,1754,'保康县','3'),(1761,1754,'老河口市','3'),(1762,1754,'枣阳市','3'),(1763,1754,'宜城市','3'),(1764,1709,'鄂州市','2'),(1765,1764,'梁子湖区','3'),(1766,1764,'华容区','3'),(1767,1764,'鄂城区','3'),(1768,1709,'荆门市','2'),(1769,1768,'东宝区','3'),(1770,1768,'掇刀区','3'),(1771,1768,'京山县','3'),(1772,1768,'沙洋县','3'),(1773,1768,'钟祥市','3'),(1774,1709,'孝感市','2'),(1775,1774,'孝南区','3'),(1776,1774,'孝昌县','3'),(1777,1774,'大悟县','3'),(1778,1774,'云梦县','3'),(1779,1774,'应城市','3'),(1780,1774,'安陆市','3'),(1781,1774,'汉川市','3'),(1782,1709,'荆州市','2'),(1783,1782,'沙市区','3'),(1784,1782,'荆州区','3'),(1785,1782,'公安县','3'),(1786,1782,'监利县','3'),(1787,1782,'江陵县','3'),(1788,1782,'石首市','3'),(1789,1782,'洪湖市','3'),(1790,1782,'松滋市','3'),(1791,1709,'黄冈市','2'),(1792,1791,'黄州区','3'),(1793,1791,'团风县','3'),(1794,1791,'红安县','3'),(1795,1791,'罗田县','3'),(1796,1791,'英山县','3'),(1797,1791,'浠水县','3'),(1798,1791,'蕲春县','3'),(1799,1791,'黄梅县','3'),(1800,1791,'麻城市','3'),(1801,1791,'武穴市','3'),(1802,1709,'咸宁市','2'),(1803,1802,'咸安区','3'),(1804,1802,'嘉鱼县','3'),(1805,1802,'通城县','3'),(1806,1802,'崇阳县','3'),(1807,1802,'通山县','3'),(1808,1802,'赤壁市','3'),(1809,1709,'随州市','2'),(1810,1809,'曾都区','3'),(1811,1809,'随县','3'),(1812,1809,'广水市','3'),(1813,1709,'恩施土家族苗族自治州','2'),(1814,1813,'恩施市','3'),(1815,1813,'利川市','3'),(1816,1813,'建始县','3'),(1817,1813,'巴东县','3'),(1818,1813,'宣恩县','3'),(1819,1813,'咸丰县','3'),(1820,1813,'来凤县','3'),(1821,1813,'鹤峰县','3'),(1822,1709,'直辖县级','2'),(1823,1822,'仙桃市','3'),(1824,1822,'潜江市','3'),(1825,1822,'天门市','3'),(1826,1822,'神农架林区','3'),(1827,0,'湖南省','1'),(1828,1827,'长沙市','2'),(1829,1828,'芙蓉区','3'),(1830,1828,'天心区','3'),(1831,1828,'岳麓区','3'),(1832,1828,'开福区','3'),(1833,1828,'雨花区','3'),(1834,1828,'望城区','3'),(1835,1828,'长沙县','3'),(1836,1828,'宁乡县','3'),(1837,1828,'浏阳市','3'),(1838,1827,'株洲市','2'),(1839,1838,'荷塘区','3'),(1840,1838,'芦淞区','3'),(1841,1838,'石峰区','3'),(1842,1838,'天元区','3'),(1843,1838,'株洲县','3'),(1844,1838,'攸县','3'),(1845,1838,'茶陵县','3'),(1846,1838,'炎陵县','3'),(1847,1838,'醴陵市','3'),(1848,1827,'湘潭市','2'),(1849,1848,'雨湖区','3'),(1850,1848,'岳塘区','3'),(1851,1848,'湘潭县','3'),(1852,1848,'湘乡市','3'),(1853,1848,'韶山市','3'),(1854,1827,'衡阳市','2'),(1855,1854,'珠晖区','3'),(1856,1854,'雁峰区','3'),(1857,1854,'石鼓区','3'),(1858,1854,'蒸湘区','3'),(1859,1854,'南岳区','3'),(1860,1854,'衡阳县','3'),(1861,1854,'衡南县','3'),(1862,1854,'衡山县','3'),(1863,1854,'衡东县','3'),(1864,1854,'祁东县','3'),(1865,1854,'耒阳市','3'),(1866,1854,'常宁市','3'),(1867,1827,'邵阳市','2'),(1868,1867,'双清区','3'),(1869,1867,'大祥区','3'),(1870,1867,'北塔区','3'),(1871,1867,'邵东县','3'),(1872,1867,'新邵县','3'),(1873,1867,'邵阳县','3'),(1874,1867,'隆回县','3'),(1875,1867,'洞口县','3'),(1876,1867,'绥宁县','3'),(1877,1867,'新宁县','3'),(1878,1867,'城步苗族自治县','3'),(1879,1867,'武冈市','3'),(1880,1827,'岳阳市','2'),(1881,1880,'岳阳楼区','3'),(1882,1880,'云溪区','3'),(1883,1880,'君山区','3'),(1884,1880,'岳阳县','3'),(1885,1880,'华容县','3'),(1886,1880,'湘阴县','3'),(1887,1880,'平江县','3'),(1888,1880,'汨罗市','3'),(1889,1880,'临湘市','3'),(1890,1827,'常德市','2'),(1891,1890,'武陵区','3'),(1892,1890,'鼎城区','3'),(1893,1890,'安乡县','3'),(1894,1890,'汉寿县','3'),(1895,1890,'澧县','3'),(1896,1890,'临澧县','3'),(1897,1890,'桃源县','3'),(1898,1890,'石门县','3'),(1899,1890,'津市市','3'),(1900,1827,'张家界市','2'),(1901,1900,'永定区','3'),(1902,1900,'武陵源区','3'),(1903,1900,'慈利县','3'),(1904,1900,'桑植县','3'),(1905,1827,'益阳市','2'),(1906,1905,'资阳区','3'),(1907,1905,'赫山区','3'),(1908,1905,'南县','3'),(1909,1905,'桃江县','3'),(1910,1905,'安化县','3'),(1911,1905,'沅江市','3'),(1912,1827,'郴州市','2'),(1913,1912,'北湖区','3'),(1914,1912,'苏仙区','3'),(1915,1912,'桂阳县','3'),(1916,1912,'宜章县','3'),(1917,1912,'永兴县','3'),(1918,1912,'嘉禾县','3'),(1919,1912,'临武县','3'),(1920,1912,'汝城县','3'),(1921,1912,'桂东县','3'),(1922,1912,'安仁县','3'),(1923,1912,'资兴市','3'),(1924,1827,'永州市','2'),(1925,1924,'零陵区','3'),(1926,1924,'冷水滩区','3'),(1927,1924,'祁阳县','3'),(1928,1924,'东安县','3'),(1929,1924,'双牌县','3'),(1930,1924,'道县','3'),(1931,1924,'江永县','3'),(1932,1924,'宁远县','3'),(1933,1924,'蓝山县','3'),(1934,1924,'新田县','3'),(1935,1924,'江华瑶族自治县','3'),(1936,1827,'怀化市','2'),(1937,1936,'鹤城区','3'),(1938,1936,'中方县','3'),(1939,1936,'沅陵县','3'),(1940,1936,'辰溪县','3'),(1941,1936,'溆浦县','3'),(1942,1936,'会同县','3'),(1943,1936,'麻阳苗族自治县','3'),(1944,1936,'新晃侗族自治县','3'),(1945,1936,'芷江侗族自治县','3'),(1946,1936,'靖州苗族侗族自治县','3'),(1947,1936,'通道侗族自治县','3'),(1948,1936,'洪江市','3'),(1949,1827,'娄底市','2'),(1950,1949,'娄星区','3'),(1951,1949,'双峰县','3'),(1952,1949,'新化县','3'),(1953,1949,'冷水江市','3'),(1954,1949,'涟源市','3'),(1955,1827,'湘西土家族苗族自治州','2'),(1956,1955,'吉首市','3'),(1957,1955,'泸溪县','3'),(1958,1955,'凤凰县','3'),(1959,1955,'花垣县','3'),(1960,1955,'保靖县','3'),(1961,1955,'古丈县','3'),(1962,1955,'永顺县','3'),(1963,1955,'龙山县','3'),(1964,0,'广东省','1'),(1965,1964,'广州市','2'),(1966,1965,'荔湾区','3'),(1967,1965,'越秀区','3'),(1968,1965,'海珠区','3'),(1969,1965,'天河区','3'),(1970,1965,'白云区','3'),(1971,1965,'黄埔区','3'),(1972,1965,'番禺区','3'),(1973,1965,'花都区','3'),(1974,1965,'南沙区','3'),(1975,1965,'从化区','3'),(1976,1965,'增城区','3'),(1977,1964,'韶关市','2'),(1978,1977,'武江区','3'),(1979,1977,'浈江区','3'),(1980,1977,'曲江区','3'),(1981,1977,'始兴县','3'),(1982,1977,'仁化县','3'),(1983,1977,'翁源县','3'),(1984,1977,'乳源瑶族自治县','3'),(1985,1977,'新丰县','3'),(1986,1977,'乐昌市','3'),(1987,1977,'南雄市','3'),(1988,1964,'深圳市','2'),(1989,1988,'罗湖区','3'),(1990,1988,'福田区','3'),(1991,1988,'南山区','3'),(1992,1988,'宝安区','3'),(1993,1988,'龙岗区','3'),(1994,1988,'盐田区','3'),(1995,1988,'光明新区','3'),(1996,1988,'坪山新区','3'),(1997,1988,'大鹏新区','3'),(1998,1988,'龙华新区','3'),(1999,1964,'珠海市','2'),(2000,1999,'香洲区','3'),(2001,1999,'斗门区','3'),(2002,1999,'金湾区','3'),(2003,1964,'汕头市','2'),(2004,2003,'龙湖区','3'),(2005,2003,'金平区','3'),(2006,2003,'濠江区','3'),(2007,2003,'潮阳区','3'),(2008,2003,'潮南区','3'),(2009,2003,'澄海区','3'),(2010,2003,'南澳县','3'),(2011,1964,'佛山市','2'),(2012,2011,'禅城区','3'),(2013,2011,'南海区','3'),(2014,2011,'顺德区','3'),(2015,2011,'三水区','3'),(2016,2011,'高明区','3'),(2017,1964,'江门市','2'),(2018,2017,'蓬江区','3'),(2019,2017,'江海区','3'),(2020,2017,'新会区','3'),(2021,2017,'台山市','3'),(2022,2017,'开平市','3'),(2023,2017,'鹤山市','3'),(2024,2017,'恩平市','3'),(2025,1964,'湛江市','2'),(2026,2025,'赤坎区','3'),(2027,2025,'霞山区','3'),(2028,2025,'坡头区','3'),(2029,2025,'麻章区','3'),(2030,2025,'遂溪县','3'),(2031,2025,'徐闻县','3'),(2032,2025,'廉江市','3'),(2033,2025,'雷州市','3'),(2034,2025,'吴川市','3'),(2035,1964,'茂名市','2'),(2036,2035,'茂南区','3'),(2037,2035,'电白区','3'),(2038,2035,'高州市','3'),(2039,2035,'化州市','3'),(2040,2035,'信宜市','3'),(2041,1964,'肇庆市','2'),(2042,2041,'端州区','3'),(2043,2041,'鼎湖区','3'),(2044,2041,'广宁县','3'),(2045,2041,'怀集县','3'),(2046,2041,'封开县','3'),(2047,2041,'德庆县','3'),(2048,2041,'高要市','3'),(2049,2041,'四会市','3'),(2050,1964,'惠州市','2'),(2051,2050,'惠城区','3'),(2052,2050,'惠阳区','3'),(2053,2050,'博罗县','3'),(2054,2050,'惠东县','3'),(2055,2050,'龙门县','3'),(2056,1964,'梅州市','2'),(2057,2056,'梅江区','3'),(2058,2056,'梅县区','3'),(2059,2056,'大埔县','3'),(2060,2056,'丰顺县','3'),(2061,2056,'五华县','3'),(2062,2056,'平远县','3'),(2063,2056,'蕉岭县','3'),(2064,2056,'兴宁市','3'),(2065,1964,'汕尾市','2'),(2066,2065,'城区','3'),(2067,2065,'海丰县','3'),(2068,2065,'陆河县','3'),(2069,2065,'陆丰市','3'),(2070,1964,'河源市','2'),(2071,2070,'源城区','3'),(2072,2070,'紫金县','3'),(2073,2070,'龙川县','3'),(2074,2070,'连平县','3'),(2075,2070,'和平县','3'),(2076,2070,'东源县','3'),(2077,1964,'阳江市','2'),(2078,2077,'江城区','3'),(2079,2077,'阳东区','3'),(2080,2077,'阳西县','3'),(2081,2077,'阳春市','3'),(2082,1964,'清远市','2'),(2083,2082,'清城区','3'),(2084,2082,'清新区','3'),(2085,2082,'佛冈县','3'),(2086,2082,'阳山县','3'),(2087,2082,'连山壮族瑶族自治县','3'),(2088,2082,'连南瑶族自治县','3'),(2089,2082,'英德市','3'),(2090,2082,'连州市','3'),(2091,1964,'东莞市','2'),(2092,2091,'莞城区','3'),(2093,2091,'南城区','3'),(2094,2091,'万江区','3'),(2095,2091,'石碣镇','3'),(2096,2091,'石龙镇','3'),(2097,2091,'茶山镇','3'),(2098,2091,'石排镇','3'),(2099,2091,'企石镇','3'),(2100,2091,'横沥镇','3'),(2101,2091,'桥头镇','3'),(2102,2091,'谢岗镇','3'),(2103,2091,'东坑镇','3'),(2104,2091,'常平镇','3'),(2105,2091,'寮步镇','3'),(2106,2091,'大朗镇','3'),(2107,2091,'麻涌镇','3'),(2108,2091,'中堂镇','3'),(2109,2091,'高埗镇','3'),(2110,2091,'樟木头镇','3'),(2111,2091,'大岭山镇','3'),(2112,2091,'望牛墩镇','3'),(2113,2091,'黄江镇','3'),(2114,2091,'洪梅镇','3'),(2115,2091,'清溪镇','3'),(2116,2091,'沙田镇','3'),(2117,2091,'道滘镇','3'),(2118,2091,'塘厦镇','3'),(2119,2091,'虎门镇','3'),(2120,2091,'厚街镇','3'),(2121,2091,'凤岗镇','3'),(2122,2091,'长安镇','3'),(2123,1964,'中山市','2'),(2124,2123,'石岐区','3'),(2125,2123,'南区','3'),(2126,2123,'五桂山区','3'),(2127,2123,'火炬开发区','3'),(2128,2123,'黄圃镇','3'),(2129,2123,'南头镇','3'),(2130,2123,'东凤镇','3'),(2131,2123,'阜沙镇','3'),(2132,2123,'小榄镇','3'),(2133,2123,'东升镇','3'),(2134,2123,'古镇镇','3'),(2135,2123,'横栏镇','3'),(2136,2123,'三角镇','3'),(2137,2123,'民众镇','3'),(2138,2123,'南朗镇','3'),(2139,2123,'港口镇','3'),(2140,2123,'大涌镇','3'),(2141,2123,'沙溪镇','3'),(2142,2123,'三乡镇','3'),(2143,2123,'板芙镇','3'),(2144,2123,'神湾镇','3'),(2145,2123,'坦洲镇','3'),(2146,1964,'潮州市','2'),(2147,2146,'湘桥区','3'),(2148,2146,'潮安区','3'),(2149,2146,'饶平县','3'),(2150,1964,'揭阳市','2'),(2151,2150,'榕城区','3'),(2152,2150,'揭东区','3'),(2153,2150,'揭西县','3'),(2154,2150,'惠来县','3'),(2155,2150,'普宁市','3'),(2156,1964,'云浮市','2'),(2157,2156,'云城区','3'),(2158,2156,'云安区','3'),(2159,2156,'新兴县','3'),(2160,2156,'郁南县','3'),(2161,2156,'罗定市','3'),(2162,0,'广西壮族自治区','1'),(2163,2162,'南宁市','2'),(2164,2163,'兴宁区','3'),(2165,2163,'青秀区','3'),(2166,2163,'江南区','3'),(2167,2163,'西乡塘区','3'),(2168,2163,'良庆区','3'),(2169,2163,'邕宁区','3'),(2170,2163,'武鸣县','3'),(2171,2163,'隆安县','3'),(2172,2163,'马山县','3'),(2173,2163,'上林县','3'),(2174,2163,'宾阳县','3'),(2175,2163,'横县','3'),(2176,2163,'埌东新区','3'),(2177,2162,'柳州市','2'),(2178,2177,'城中区','3'),(2179,2177,'鱼峰区','3'),(2180,2177,'柳南区','3'),(2181,2177,'柳北区','3'),(2182,2177,'柳江县','3'),(2183,2177,'柳城县','3'),(2184,2177,'鹿寨县','3'),(2185,2177,'融安县','3'),(2186,2177,'融水苗族自治县','3'),(2187,2177,'三江侗族自治县','3'),(2188,2177,'柳东新区','3'),(2189,2162,'桂林市','2'),(2190,2189,'秀峰区','3'),(2191,2189,'叠彩区','3'),(2192,2189,'象山区','3'),(2193,2189,'七星区','3'),(2194,2189,'雁山区','3'),(2195,2189,'临桂区','3'),(2196,2189,'阳朔县','3'),(2197,2189,'灵川县','3'),(2198,2189,'全州县','3'),(2199,2189,'兴安县','3'),(2200,2189,'永福县','3'),(2201,2189,'灌阳县','3'),(2202,2189,'龙胜各族自治县','3'),(2203,2189,'资源县','3'),(2204,2189,'平乐县','3'),(2205,2189,'荔浦县','3'),(2206,2189,'恭城瑶族自治县','3'),(2207,2162,'梧州市','2'),(2208,2207,'万秀区','3'),(2209,2207,'长洲区','3'),(2210,2207,'龙圩区','3'),(2211,2207,'苍梧县','3'),(2212,2207,'藤县','3'),(2213,2207,'蒙山县','3'),(2214,2207,'岑溪市','3'),(2215,2162,'北海市','2'),(2216,2215,'海城区','3'),(2217,2215,'银海区','3'),(2218,2215,'铁山港区','3'),(2219,2215,'合浦县','3'),(2220,2162,'防城港市','2'),(2221,2220,'港口区','3'),(2222,2220,'防城区','3'),(2223,2220,'上思县','3'),(2224,2220,'东兴市','3'),(2225,2162,'钦州市','2'),(2226,2225,'钦南区','3'),(2227,2225,'钦北区','3'),(2228,2225,'灵山县','3'),(2229,2225,'浦北县','3'),(2230,2162,'贵港市','2'),(2231,2230,'港北区','3'),(2232,2230,'港南区','3'),(2233,2230,'覃塘区','3'),(2234,2230,'平南县','3'),(2235,2230,'桂平市','3'),(2236,2162,'玉林市','2'),(2237,2236,'玉州区','3'),(2238,2236,'福绵区','3'),(2239,2236,'玉东新区','3'),(2240,2236,'容县','3'),(2241,2236,'陆川县','3'),(2242,2236,'博白县','3'),(2243,2236,'兴业县','3'),(2244,2236,'北流市','3'),(2245,2162,'百色市','2'),(2246,2245,'右江区','3'),(2247,2245,'田阳县','3'),(2248,2245,'田东县','3'),(2249,2245,'平果县','3'),(2250,2245,'德保县','3'),(2251,2245,'靖西县','3'),(2252,2245,'那坡县','3'),(2253,2245,'凌云县','3'),(2254,2245,'乐业县','3'),(2255,2245,'田林县','3'),(2256,2245,'西林县','3'),(2257,2245,'隆林各族自治县','3'),(2258,2162,'贺州市','2'),(2259,2258,'八步区','3'),(2260,2258,'昭平县','3'),(2261,2258,'钟山县','3'),(2262,2258,'富川瑶族自治县','3'),(2263,2258,'平桂管理区','3'),(2264,2162,'河池市','2'),(2265,2264,'金城江区','3'),(2266,2264,'南丹县','3'),(2267,2264,'天峨县','3'),(2268,2264,'凤山县','3'),(2269,2264,'东兰县','3'),(2270,2264,'罗城仫佬族自治县','3'),(2271,2264,'环江毛南族自治县','3'),(2272,2264,'巴马瑶族自治县','3'),(2273,2264,'都安瑶族自治县','3'),(2274,2264,'大化瑶族自治县','3'),(2275,2264,'宜州市','3'),(2276,2162,'来宾市','2'),(2277,2276,'兴宾区','3'),(2278,2276,'忻城县','3'),(2279,2276,'象州县','3'),(2280,2276,'武宣县','3'),(2281,2276,'金秀瑶族自治县','3'),(2282,2276,'合山市','3'),(2283,2162,'崇左市','2'),(2284,2283,'江州区','3'),(2285,2283,'扶绥县','3'),(2286,2283,'宁明县','3'),(2287,2283,'龙州县','3'),(2288,2283,'大新县','3'),(2289,2283,'天等县','3'),(2290,2283,'凭祥市','3'),(2291,0,'海南省','1'),(2292,2291,'海口市','2'),(2293,2292,'秀英区','3'),(2294,2292,'龙华区','3'),(2295,2292,'琼山区','3'),(2296,2292,'美兰区','3'),(2297,2291,'三亚市','2'),(2298,2297,'海棠区','3'),(2299,2297,'吉阳区','3'),(2300,2297,'天涯区','3'),(2301,2297,'崖州区','3'),(2302,2291,'三沙市','2'),(2303,2302,'西沙群岛','3'),(2304,2302,'南沙群岛','3'),(2305,2302,'中沙群岛','3'),(2306,2291,'直辖县级','2'),(2307,2306,'五指山市','3'),(2308,2306,'琼海市','3'),(2309,2306,'儋州市','3'),(2310,2306,'文昌市','3'),(2311,2306,'万宁市','3'),(2312,2306,'东方市','3'),(2313,2306,'定安县','3'),(2314,2306,'屯昌县','3'),(2315,2306,'澄迈县','3'),(2316,2306,'临高县','3'),(2317,2306,'白沙黎族自治县','3'),(2318,2306,'昌江黎族自治县','3'),(2319,2306,'乐东黎族自治县','3'),(2320,2306,'陵水黎族自治县','3'),(2321,2306,'保亭黎族苗族自治县','3'),(2322,2306,'琼中黎族苗族自治县','3'),(2323,0,'重庆','1'),(2324,2323,'重庆市','2'),(2325,2324,'万州区','3'),(2326,2324,'涪陵区','3'),(2327,2324,'渝中区','3'),(2328,2324,'大渡口区','3'),(2329,2324,'江北区','3'),(2330,2324,'沙坪坝区','3'),(2331,2324,'九龙坡区','3'),(2332,2324,'南岸区','3'),(2333,2324,'北碚区','3'),(2334,2324,'綦江区','3'),(2335,2324,'大足区','3'),(2336,2324,'渝北区','3'),(2337,2324,'巴南区','3'),(2338,2324,'黔江区','3'),(2339,2324,'长寿区','3'),(2340,2324,'江津区','3'),(2341,2324,'合川区','3'),(2342,2324,'永川区','3'),(2343,2324,'南川区','3'),(2344,2324,'璧山区','3'),(2345,2324,'铜梁区','3'),(2346,2324,'潼南县','3'),(2347,2324,'荣昌县','3'),(2348,2324,'梁平县','3'),(2349,2324,'城口县','3'),(2350,2324,'丰都县','3'),(2351,2324,'垫江县','3'),(2352,2324,'武隆县','3'),(2353,2324,'忠县','3'),(2354,2324,'开县','3'),(2355,2324,'云阳县','3'),(2356,2324,'奉节县','3'),(2357,2324,'巫山县','3'),(2358,2324,'巫溪县','3'),(2359,2324,'石柱土家族自治县','3'),(2360,2324,'秀山土家族苗族自治县','3'),(2361,2324,'酉阳土家族苗族自治县','3'),(2362,2324,'彭水苗族土家族自治县','3'),(2363,2323,'两江新区','2'),(2364,2363,'北部新区','3'),(2365,2363,'保税港区','3'),(2366,2363,'工业园区','3'),(2367,0,'四川省','1'),(2368,2367,'成都市','2'),(2369,2368,'锦江区','3'),(2370,2368,'青羊区','3'),(2371,2368,'金牛区','3'),(2372,2368,'武侯区','3'),(2373,2368,'成华区','3'),(2374,2368,'龙泉驿区','3'),(2375,2368,'青白江区','3'),(2376,2368,'新都区','3'),(2377,2368,'温江区','3'),(2378,2368,'金堂县','3'),(2379,2368,'双流县','3'),(2380,2368,'郫县','3'),(2381,2368,'大邑县','3'),(2382,2368,'蒲江县','3'),(2383,2368,'新津县','3'),(2384,2368,'都江堰市','3'),(2385,2368,'彭州市','3'),(2386,2368,'邛崃市','3'),(2387,2368,'崇州市','3'),(2388,2367,'自贡市','2'),(2389,2388,'自流井区','3'),(2390,2388,'贡井区','3'),(2391,2388,'大安区','3'),(2392,2388,'沿滩区','3'),(2393,2388,'荣县','3'),(2394,2388,'富顺县','3'),(2395,2367,'攀枝花市','2'),(2396,2395,'东区','3'),(2397,2395,'西区','3'),(2398,2395,'仁和区','3'),(2399,2395,'米易县','3'),(2400,2395,'盐边县','3'),(2401,2367,'泸州市','2'),(2402,2401,'江阳区','3'),(2403,2401,'纳溪区','3'),(2404,2401,'龙马潭区','3'),(2405,2401,'泸县','3'),(2406,2401,'合江县','3'),(2407,2401,'叙永县','3'),(2408,2401,'古蔺县','3'),(2409,2367,'德阳市','2'),(2410,2409,'旌阳区','3'),(2411,2409,'中江县','3'),(2412,2409,'罗江县','3'),(2413,2409,'广汉市','3'),(2414,2409,'什邡市','3'),(2415,2409,'绵竹市','3'),(2416,2367,'绵阳市','2'),(2417,2416,'涪城区','3'),(2418,2416,'游仙区','3'),(2419,2416,'三台县','3'),(2420,2416,'盐亭县','3'),(2421,2416,'安县','3'),(2422,2416,'梓潼县','3'),(2423,2416,'北川羌族自治县','3'),(2424,2416,'平武县','3'),(2425,2416,'江油市','3'),(2426,2367,'广元市','2'),(2427,2426,'利州区','3'),(2428,2426,'昭化区','3'),(2429,2426,'朝天区','3'),(2430,2426,'旺苍县','3'),(2431,2426,'青川县','3'),(2432,2426,'剑阁县','3'),(2433,2426,'苍溪县','3'),(2434,2367,'遂宁市','2'),(2435,2434,'船山区','3'),(2436,2434,'安居区','3'),(2437,2434,'蓬溪县','3'),(2438,2434,'射洪县','3'),(2439,2434,'大英县','3'),(2440,2367,'内江市','2'),(2441,2440,'市中区','3'),(2442,2440,'东兴区','3'),(2443,2440,'威远县','3'),(2444,2440,'资中县','3'),(2445,2440,'隆昌县','3'),(2446,2367,'乐山市','2'),(2447,2446,'市中区','3'),(2448,2446,'沙湾区','3'),(2449,2446,'五通桥区','3'),(2450,2446,'金口河区','3'),(2451,2446,'犍为县','3'),(2452,2446,'井研县','3'),(2453,2446,'夹江县','3'),(2454,2446,'沐川县','3'),(2455,2446,'峨边彝族自治县','3'),(2456,2446,'马边彝族自治县','3'),(2457,2446,'峨眉山市','3'),(2458,2367,'南充市','2'),(2459,2458,'顺庆区','3'),(2460,2458,'高坪区','3'),(2461,2458,'嘉陵区','3'),(2462,2458,'南部县','3'),(2463,2458,'营山县','3'),(2464,2458,'蓬安县','3'),(2465,2458,'仪陇县','3'),(2466,2458,'西充县','3'),(2467,2458,'阆中市','3'),(2468,2367,'眉山市','2'),(2469,2468,'东坡区','3'),(2470,2468,'彭山区','3'),(2471,2468,'仁寿县','3'),(2472,2468,'洪雅县','3'),(2473,2468,'丹棱县','3'),(2474,2468,'青神县','3'),(2475,2367,'宜宾市','2'),(2476,2475,'翠屏区','3'),(2477,2475,'南溪区','3'),(2478,2475,'宜宾县','3'),(2479,2475,'江安县','3'),(2480,2475,'长宁县','3'),(2481,2475,'高县','3'),(2482,2475,'珙县','3'),(2483,2475,'筠连县','3'),(2484,2475,'兴文县','3'),(2485,2475,'屏山县','3'),(2486,2367,'广安市','2'),(2487,2486,'广安区','3'),(2488,2486,'前锋区','3'),(2489,2486,'岳池县','3'),(2490,2486,'武胜县','3'),(2491,2486,'邻水县','3'),(2492,2486,'华蓥市','3'),(2493,2367,'达州市','2'),(2494,2493,'通川区','3'),(2495,2493,'达川区','3'),(2496,2493,'宣汉县','3'),(2497,2493,'开江县','3'),(2498,2493,'大竹县','3'),(2499,2493,'渠县','3'),(2500,2493,'万源市','3'),(2501,2367,'雅安市','2'),(2502,2501,'雨城区','3'),(2503,2501,'名山区','3'),(2504,2501,'荥经县','3'),(2505,2501,'汉源县','3'),(2506,2501,'石棉县','3'),(2507,2501,'天全县','3'),(2508,2501,'芦山县','3'),(2509,2501,'宝兴县','3'),(2510,2367,'巴中市','2'),(2511,2510,'巴州区','3'),(2512,2510,'恩阳区','3'),(2513,2510,'通江县','3'),(2514,2510,'南江县','3'),(2515,2510,'平昌县','3'),(2516,2367,'资阳市','2'),(2517,2516,'雁江区','3'),(2518,2516,'安岳县','3'),(2519,2516,'乐至县','3'),(2520,2516,'简阳市','3'),(2521,2367,'阿坝藏族羌族自治州','2'),(2522,2521,'汶川县','3'),(2523,2521,'理县','3'),(2524,2521,'茂县','3'),(2525,2521,'松潘县','3'),(2526,2521,'九寨沟县','3'),(2527,2521,'金川县','3'),(2528,2521,'小金县','3'),(2529,2521,'黑水县','3'),(2530,2521,'马尔康县','3'),(2531,2521,'壤塘县','3'),(2532,2521,'阿坝县','3'),(2533,2521,'若尔盖县','3'),(2534,2521,'红原县','3'),(2535,2367,'甘孜藏族自治州','2'),(2536,2535,'康定县','3'),(2537,2535,'泸定县','3'),(2538,2535,'丹巴县','3'),(2539,2535,'九龙县','3'),(2540,2535,'雅江县','3'),(2541,2535,'道孚县','3'),(2542,2535,'炉霍县','3'),(2543,2535,'甘孜县','3'),(2544,2535,'新龙县','3'),(2545,2535,'德格县','3'),(2546,2535,'白玉县','3'),(2547,2535,'石渠县','3'),(2548,2535,'色达县','3'),(2549,2535,'理塘县','3'),(2550,2535,'巴塘县','3'),(2551,2535,'乡城县','3'),(2552,2535,'稻城县','3'),(2553,2535,'得荣县','3'),(2554,2367,'凉山彝族自治州','2'),(2555,2554,'西昌市','3'),(2556,2554,'木里藏族自治县','3'),(2557,2554,'盐源县','3'),(2558,2554,'德昌县','3'),(2559,2554,'会理县','3'),(2560,2554,'会东县','3'),(2561,2554,'宁南县','3'),(2562,2554,'普格县','3'),(2563,2554,'布拖县','3'),(2564,2554,'金阳县','3'),(2565,2554,'昭觉县','3'),(2566,2554,'喜德县','3'),(2567,2554,'冕宁县','3'),(2568,2554,'越西县','3'),(2569,2554,'甘洛县','3'),(2570,2554,'美姑县','3'),(2571,2554,'雷波县','3'),(2572,0,'贵州省','1'),(2573,2572,'贵阳市','2'),(2574,2573,'南明区','3'),(2575,2573,'云岩区','3'),(2576,2573,'花溪区','3'),(2577,2573,'乌当区','3'),(2578,2573,'白云区','3'),(2579,2573,'观山湖区','3'),(2580,2573,'开阳县','3'),(2581,2573,'息烽县','3'),(2582,2573,'修文县','3'),(2583,2573,'清镇市','3'),(2584,2572,'六盘水市','2'),(2585,2584,'钟山区','3'),(2586,2584,'六枝特区','3'),(2587,2584,'水城县','3'),(2588,2584,'盘县','3'),(2589,2572,'遵义市','2'),(2590,2589,'红花岗区','3'),(2591,2589,'汇川区','3'),(2592,2589,'遵义县','3'),(2593,2589,'桐梓县','3'),(2594,2589,'绥阳县','3'),(2595,2589,'正安县','3'),(2596,2589,'道真仡佬族苗族自治县','3'),(2597,2589,'务川仡佬族苗族自治县','3'),(2598,2589,'凤冈县','3'),(2599,2589,'湄潭县','3'),(2600,2589,'余庆县','3'),(2601,2589,'习水县','3'),(2602,2589,'赤水市','3'),(2603,2589,'仁怀市','3'),(2604,2572,'安顺市','2'),(2605,2604,'西秀区','3'),(2606,2604,'平坝区','3'),(2607,2604,'普定县','3'),(2608,2604,'镇宁布依族苗族自治县','3'),(2609,2604,'关岭布依族苗族自治县','3'),(2610,2604,'紫云苗族布依族自治县','3'),(2611,2572,'毕节市','2'),(2612,2611,'七星关区','3'),(2613,2611,'大方县','3'),(2614,2611,'黔西县','3'),(2615,2611,'金沙县','3'),(2616,2611,'织金县','3'),(2617,2611,'纳雍县','3'),(2618,2611,'威宁彝族回族苗族自治县','3'),(2619,2611,'赫章县','3'),(2620,2572,'铜仁市','2'),(2621,2620,'碧江区','3'),(2622,2620,'万山区','3'),(2623,2620,'江口县','3'),(2624,2620,'玉屏侗族自治县','3'),(2625,2620,'石阡县','3'),(2626,2620,'思南县','3'),(2627,2620,'印江土家族苗族自治县','3'),(2628,2620,'德江县','3'),(2629,2620,'沿河土家族自治县','3'),(2630,2620,'松桃苗族自治县','3'),(2631,2572,'黔西南布依族苗族自治州','2'),(2632,2631,'兴义市','3'),(2633,2631,'兴仁县','3'),(2634,2631,'普安县','3'),(2635,2631,'晴隆县','3'),(2636,2631,'贞丰县','3'),(2637,2631,'望谟县','3'),(2638,2631,'册亨县','3'),(2639,2631,'安龙县','3'),(2640,2572,'黔东南苗族侗族自治州','2'),(2641,2640,'凯里市','3'),(2642,2640,'黄平县','3'),(2643,2640,'施秉县','3'),(2644,2640,'三穗县','3'),(2645,2640,'镇远县','3'),(2646,2640,'岑巩县','3'),(2647,2640,'天柱县','3'),(2648,2640,'锦屏县','3'),(2649,2640,'剑河县','3'),(2650,2640,'台江县','3'),(2651,2640,'黎平县','3'),(2652,2640,'榕江县','3'),(2653,2640,'从江县','3'),(2654,2640,'雷山县','3'),(2655,2640,'麻江县','3'),(2656,2640,'丹寨县','3'),(2657,2572,'黔南布依族苗族自治州','2'),(2658,2657,'都匀市','3'),(2659,2657,'福泉市','3'),(2660,2657,'荔波县','3'),(2661,2657,'贵定县','3'),(2662,2657,'瓮安县','3'),(2663,2657,'独山县','3'),(2664,2657,'平塘县','3'),(2665,2657,'罗甸县','3'),(2666,2657,'长顺县','3'),(2667,2657,'龙里县','3'),(2668,2657,'惠水县','3'),(2669,2657,'三都水族自治县','3'),(2670,0,'云南省','1'),(2671,2670,'昆明市','2'),(2672,2671,'五华区','3'),(2673,2671,'盘龙区','3'),(2674,2671,'官渡区','3'),(2675,2671,'西山区','3'),(2676,2671,'东川区','3'),(2677,2671,'呈贡区','3'),(2678,2671,'晋宁县','3'),(2679,2671,'富民县','3'),(2680,2671,'宜良县','3'),(2681,2671,'石林彝族自治县','3'),(2682,2671,'嵩明县','3'),(2683,2671,'禄劝彝族苗族自治县','3'),(2684,2671,'寻甸回族彝族自治县','3'),(2685,2671,'安宁市','3'),(2686,2670,'曲靖市','2'),(2687,2686,'麒麟区','3'),(2688,2686,'马龙县','3'),(2689,2686,'陆良县','3'),(2690,2686,'师宗县','3'),(2691,2686,'罗平县','3'),(2692,2686,'富源县','3'),(2693,2686,'会泽县','3'),(2694,2686,'沾益县','3'),(2695,2686,'宣威市','3'),(2696,2670,'玉溪市','2'),(2697,2696,'红塔区','3'),(2698,2696,'江川县','3'),(2699,2696,'澄江县','3'),(2700,2696,'通海县','3'),(2701,2696,'华宁县','3'),(2702,2696,'易门县','3'),(2703,2696,'峨山彝族自治县','3'),(2704,2696,'新平彝族傣族自治县','3'),(2705,2696,'元江哈尼族彝族傣族自治县','3'),(2706,2670,'保山市','2'),(2707,2706,'隆阳区','3'),(2708,2706,'施甸县','3'),(2709,2706,'腾冲县','3'),(2710,2706,'龙陵县','3'),(2711,2706,'昌宁县','3'),(2712,2670,'昭通市','2'),(2713,2712,'昭阳区','3'),(2714,2712,'鲁甸县','3'),(2715,2712,'巧家县','3'),(2716,2712,'盐津县','3'),(2717,2712,'大关县','3'),(2718,2712,'永善县','3'),(2719,2712,'绥江县','3'),(2720,2712,'镇雄县','3'),(2721,2712,'彝良县','3'),(2722,2712,'威信县','3'),(2723,2712,'水富县','3'),(2724,2670,'丽江市','2'),(2725,2724,'古城区','3'),(2726,2724,'玉龙纳西族自治县','3'),(2727,2724,'永胜县','3'),(2728,2724,'华坪县','3'),(2729,2724,'宁蒗彝族自治县','3'),(2730,2670,'普洱市','2'),(2731,2730,'思茅区','3'),(2732,2730,'宁洱哈尼族彝族自治县','3'),(2733,2730,'墨江哈尼族自治县','3'),(2734,2730,'景东彝族自治县','3'),(2735,2730,'景谷傣族彝族自治县','3'),(2736,2730,'镇沅彝族哈尼族拉祜族自治县','3'),(2737,2730,'江城哈尼族彝族自治县','3'),(2738,2730,'孟连傣族拉祜族佤族自治县','3'),(2739,2730,'澜沧拉祜族自治县','3'),(2740,2730,'西盟佤族自治县','3'),(2741,2670,'临沧市','2'),(2742,2741,'临翔区','3'),(2743,2741,'凤庆县','3'),(2744,2741,'云县','3'),(2745,2741,'永德县','3'),(2746,2741,'镇康县','3'),(2747,2741,'双江拉祜族佤族布朗族傣族自治县','3'),(2748,2741,'耿马傣族佤族自治县','3'),(2749,2741,'沧源佤族自治县','3'),(2750,2670,'楚雄彝族自治州','2'),(2751,2750,'楚雄市','3'),(2752,2750,'双柏县','3'),(2753,2750,'牟定县','3'),(2754,2750,'南华县','3'),(2755,2750,'姚安县','3'),(2756,2750,'大姚县','3'),(2757,2750,'永仁县','3'),(2758,2750,'元谋县','3'),(2759,2750,'武定县','3'),(2760,2750,'禄丰县','3'),(2761,2670,'红河哈尼族彝族自治州','2'),(2762,2761,'个旧市','3'),(2763,2761,'开远市','3'),(2764,2761,'蒙自市','3'),(2765,2761,'弥勒市','3'),(2766,2761,'屏边苗族自治县','3'),(2767,2761,'建水县','3'),(2768,2761,'石屏县','3'),(2769,2761,'泸西县','3'),(2770,2761,'元阳县','3'),(2771,2761,'红河县','3'),(2772,2761,'金平苗族瑶族傣族自治县','3'),(2773,2761,'绿春县','3'),(2774,2761,'河口瑶族自治县','3'),(2775,2670,'文山壮族苗族自治州','2'),(2776,2775,'文山市','3'),(2777,2775,'砚山县','3'),(2778,2775,'西畴县','3'),(2779,2775,'麻栗坡县','3'),(2780,2775,'马关县','3'),(2781,2775,'丘北县','3'),(2782,2775,'广南县','3'),(2783,2775,'富宁县','3'),(2784,2670,'西双版纳傣族自治州','2'),(2785,2784,'景洪市','3'),(2786,2784,'勐海县','3'),(2787,2784,'勐腊县','3'),(2788,2670,'大理白族自治州','2'),(2789,2788,'大理市','3'),(2790,2788,'漾濞彝族自治县','3'),(2791,2788,'祥云县','3'),(2792,2788,'宾川县','3'),(2793,2788,'弥渡县','3'),(2794,2788,'南涧彝族自治县','3'),(2795,2788,'巍山彝族回族自治县','3'),(2796,2788,'永平县','3'),(2797,2788,'云龙县','3'),(2798,2788,'洱源县','3'),(2799,2788,'剑川县','3'),(2800,2788,'鹤庆县','3'),(2801,2670,'德宏傣族景颇族自治州','2'),(2802,2801,'瑞丽市','3'),(2803,2801,'芒市','3'),(2804,2801,'梁河县','3'),(2805,2801,'盈江县','3'),(2806,2801,'陇川县','3'),(2807,2670,'怒江傈僳族自治州','2'),(2808,2807,'泸水县','3'),(2809,2807,'福贡县','3'),(2810,2807,'贡山独龙族怒族自治县','3'),(2811,2807,'兰坪白族普米族自治县','3'),(2812,2670,'迪庆藏族自治州','2'),(2813,2812,'香格里拉市','3'),(2814,2812,'德钦县','3'),(2815,2812,'维西傈僳族自治县','3'),(2816,0,'西藏自治区','1'),(2817,2816,'拉萨市','2'),(2818,2817,'城关区','3'),(2819,2817,'林周县','3'),(2820,2817,'当雄县','3'),(2821,2817,'尼木县','3'),(2822,2817,'曲水县','3'),(2823,2817,'堆龙德庆县','3'),(2824,2817,'达孜县','3'),(2825,2817,'墨竹工卡县','3'),(2826,2816,'日喀则市','2'),(2827,2826,'桑珠孜区','3'),(2828,2826,'南木林县','3'),(2829,2826,'江孜县','3'),(2830,2826,'定日县','3'),(2831,2826,'萨迦县','3'),(2832,2826,'拉孜县','3'),(2833,2826,'昂仁县','3'),(2834,2826,'谢通门县','3'),(2835,2826,'白朗县','3'),(2836,2826,'仁布县','3'),(2837,2826,'康马县','3'),(2838,2826,'定结县','3'),(2839,2826,'仲巴县','3'),(2840,2826,'亚东县','3'),(2841,2826,'吉隆县','3'),(2842,2826,'聂拉木县','3'),(2843,2826,'萨嘎县','3'),(2844,2826,'岗巴县','3'),(2845,2816,'昌都市','2'),(2846,2845,'卡若区','3'),(2847,2845,'江达县','3'),(2848,2845,'贡觉县','3'),(2849,2845,'类乌齐县','3'),(2850,2845,'丁青县','3'),(2851,2845,'察雅县','3'),(2852,2845,'八宿县','3'),(2853,2845,'左贡县','3'),(2854,2845,'芒康县','3'),(2855,2845,'洛隆县','3'),(2856,2845,'边坝县','3'),(2857,2816,'山南地区','2'),(2858,2857,'乃东县','3'),(2859,2857,'扎囊县','3'),(2860,2857,'贡嘎县','3'),(2861,2857,'桑日县','3'),(2862,2857,'琼结县','3'),(2863,2857,'曲松县','3'),(2864,2857,'措美县','3'),(2865,2857,'洛扎县','3'),(2866,2857,'加查县','3'),(2867,2857,'隆子县','3'),(2868,2857,'错那县','3'),(2869,2857,'浪卡子县','3'),(2870,2816,'那曲地区','2'),(2871,2870,'那曲县','3'),(2872,2870,'嘉黎县','3'),(2873,2870,'比如县','3'),(2874,2870,'聂荣县','3'),(2875,2870,'安多县','3'),(2876,2870,'申扎县','3'),(2877,2870,'索县','3'),(2878,2870,'班戈县','3'),(2879,2870,'巴青县','3'),(2880,2870,'尼玛县','3'),(2881,2870,'双湖县','3'),(2882,2816,'阿里地区','2'),(2883,2882,'普兰县','3'),(2884,2882,'札达县','3'),(2885,2882,'噶尔县','3'),(2886,2882,'日土县','3'),(2887,2882,'革吉县','3'),(2888,2882,'改则县','3'),(2889,2882,'措勤县','3'),(2890,2816,'林芝地区','2'),(2891,2890,'林芝县','3'),(2892,2890,'工布江达县','3'),(2893,2890,'米林县','3'),(2894,2890,'墨脱县','3'),(2895,2890,'波密县','3'),(2896,2890,'察隅县','3'),(2897,2890,'朗县','3'),(2898,0,'陕西省','1'),(2899,2898,'西安市','2'),(2900,2899,'新城区','3'),(2901,2899,'碑林区','3'),(2902,2899,'莲湖区','3'),(2903,2899,'灞桥区','3'),(2904,2899,'未央区','3'),(2905,2899,'雁塔区','3'),(2906,2899,'阎良区','3'),(2907,2899,'临潼区','3'),(2908,2899,'长安区','3'),(2909,2899,'蓝田县','3'),(2910,2899,'周至县','3'),(2911,2899,'户县','3'),(2912,2899,'高陵区','3'),(2913,2898,'铜川市','2'),(2914,2913,'王益区','3'),(2915,2913,'印台区','3'),(2916,2913,'耀州区','3'),(2917,2913,'宜君县','3'),(2918,2898,'宝鸡市','2'),(2919,2918,'渭滨区','3'),(2920,2918,'金台区','3'),(2921,2918,'陈仓区','3'),(2922,2918,'凤翔县','3'),(2923,2918,'岐山县','3'),(2924,2918,'扶风县','3'),(2925,2918,'眉县','3'),(2926,2918,'陇县','3'),(2927,2918,'千阳县','3'),(2928,2918,'麟游县','3'),(2929,2918,'凤县','3'),(2930,2918,'太白县','3'),(2931,2898,'咸阳市','2'),(2932,2931,'秦都区','3'),(2933,2931,'杨陵区','3'),(2934,2931,'渭城区','3'),(2935,2931,'三原县','3'),(2936,2931,'泾阳县','3'),(2937,2931,'乾县','3'),(2938,2931,'礼泉县','3'),(2939,2931,'永寿县','3'),(2940,2931,'彬县','3'),(2941,2931,'长武县','3'),(2942,2931,'旬邑县','3'),(2943,2931,'淳化县','3'),(2944,2931,'武功县','3'),(2945,2931,'兴平市','3'),(2946,2898,'渭南市','2'),(2947,2946,'临渭区','3'),(2948,2946,'华县','3'),(2949,2946,'潼关县','3'),(2950,2946,'大荔县','3'),(2951,2946,'合阳县','3'),(2952,2946,'澄城县','3'),(2953,2946,'蒲城县','3'),(2954,2946,'白水县','3'),(2955,2946,'富平县','3'),(2956,2946,'韩城市','3'),(2957,2946,'华阴市','3'),(2958,2898,'延安市','2'),(2959,2958,'宝塔区','3'),(2960,2958,'延长县','3'),(2961,2958,'延川县','3'),(2962,2958,'子长县','3'),(2963,2958,'安塞县','3'),(2964,2958,'志丹县','3'),(2965,2958,'吴起县','3'),(2966,2958,'甘泉县','3'),(2967,2958,'富县','3'),(2968,2958,'洛川县','3'),(2969,2958,'宜川县','3'),(2970,2958,'黄龙县','3'),(2971,2958,'黄陵县','3'),(2972,2898,'汉中市','2'),(2973,2972,'汉台区','3'),(2974,2972,'南郑县','3'),(2975,2972,'城固县','3'),(2976,2972,'洋县','3'),(2977,2972,'西乡县','3'),(2978,2972,'勉县','3'),(2979,2972,'宁强县','3'),(2980,2972,'略阳县','3'),(2981,2972,'镇巴县','3'),(2982,2972,'留坝县','3'),(2983,2972,'佛坪县','3'),(2984,2898,'榆林市','2'),(2985,2984,'榆阳区','3'),(2986,2984,'神木县','3'),(2987,2984,'府谷县','3'),(2988,2984,'横山县','3'),(2989,2984,'靖边县','3'),(2990,2984,'定边县','3'),(2991,2984,'绥德县','3'),(2992,2984,'米脂县','3'),(2993,2984,'佳县','3'),(2994,2984,'吴堡县','3'),(2995,2984,'清涧县','3'),(2996,2984,'子洲县','3'),(2997,2898,'安康市','2'),(2998,2997,'汉滨区','3'),(2999,2997,'汉阴县','3'),(3000,2997,'石泉县','3'),(3001,2997,'宁陕县','3'),(3002,2997,'紫阳县','3'),(3003,2997,'岚皋县','3'),(3004,2997,'平利县','3'),(3005,2997,'镇坪县','3'),(3006,2997,'旬阳县','3'),(3007,2997,'白河县','3'),(3008,2898,'商洛市','2'),(3009,3008,'商州区','3'),(3010,3008,'洛南县','3'),(3011,3008,'丹凤县','3'),(3012,3008,'商南县','3'),(3013,3008,'山阳县','3'),(3014,3008,'镇安县','3'),(3015,3008,'柞水县','3'),(3016,2898,'西咸新区','2'),(3017,3016,'空港新城','3'),(3018,3016,'沣东新城','3'),(3019,3016,'秦汉新城','3'),(3020,3016,'沣西新城','3'),(3021,3016,'泾河新城','3'),(3022,0,'甘肃省','1'),(3023,3022,'兰州市','2'),(3024,3023,'城关区','3'),(3025,3023,'七里河区','3'),(3026,3023,'西固区','3'),(3027,3023,'安宁区','3'),(3028,3023,'红古区','3'),(3029,3023,'永登县','3'),(3030,3023,'皋兰县','3'),(3031,3023,'榆中县','3'),(3032,3022,'嘉峪关市','2'),(3033,3032,'雄关区','3'),(3034,3032,'长城区','3'),(3035,3032,'镜铁区','3'),(3036,3022,'金昌市','2'),(3037,3036,'金川区','3'),(3038,3036,'永昌县','3'),(3039,3022,'白银市','2'),(3040,3039,'白银区','3'),(3041,3039,'平川区','3'),(3042,3039,'靖远县','3'),(3043,3039,'会宁县','3'),(3044,3039,'景泰县','3'),(3045,3022,'天水市','2'),(3046,3045,'秦州区','3'),(3047,3045,'麦积区','3'),(3048,3045,'清水县','3'),(3049,3045,'秦安县','3'),(3050,3045,'甘谷县','3'),(3051,3045,'武山县','3'),(3052,3045,'张家川回族自治县','3'),(3053,3022,'武威市','2'),(3054,3053,'凉州区','3'),(3055,3053,'民勤县','3'),(3056,3053,'古浪县','3'),(3057,3053,'天祝藏族自治县','3'),(3058,3022,'张掖市','2'),(3059,3058,'甘州区','3'),(3060,3058,'肃南裕固族自治县','3'),(3061,3058,'民乐县','3'),(3062,3058,'临泽县','3'),(3063,3058,'高台县','3'),(3064,3058,'山丹县','3'),(3065,3022,'平凉市','2'),(3066,3065,'崆峒区','3'),(3067,3065,'泾川县','3'),(3068,3065,'灵台县','3'),(3069,3065,'崇信县','3'),(3070,3065,'华亭县','3'),(3071,3065,'庄浪县','3'),(3072,3065,'静宁县','3'),(3073,3022,'酒泉市','2'),(3074,3073,'肃州区','3'),(3075,3073,'金塔县','3'),(3076,3073,'瓜州县','3'),(3077,3073,'肃北蒙古族自治县','3'),(3078,3073,'阿克塞哈萨克族自治县','3'),(3079,3073,'玉门市','3'),(3080,3073,'敦煌市','3'),(3081,3022,'庆阳市','2'),(3082,3081,'西峰区','3'),(3083,3081,'庆城县','3'),(3084,3081,'环县','3'),(3085,3081,'华池县','3'),(3086,3081,'合水县','3'),(3087,3081,'正宁县','3'),(3088,3081,'宁县','3'),(3089,3081,'镇原县','3'),(3090,3022,'定西市','2'),(3091,3090,'安定区','3'),(3092,3090,'通渭县','3'),(3093,3090,'陇西县','3'),(3094,3090,'渭源县','3'),(3095,3090,'临洮县','3'),(3096,3090,'漳县','3'),(3097,3090,'岷县','3'),(3098,3022,'陇南市','2'),(3099,3098,'武都区','3'),(3100,3098,'成县','3'),(3101,3098,'文县','3'),(3102,3098,'宕昌县','3'),(3103,3098,'康县','3'),(3104,3098,'西和县','3'),(3105,3098,'礼县','3'),(3106,3098,'徽县','3'),(3107,3098,'两当县','3'),(3108,3022,'临夏回族自治州','2'),(3109,3108,'临夏市','3'),(3110,3108,'临夏县','3'),(3111,3108,'康乐县','3'),(3112,3108,'永靖县','3'),(3113,3108,'广河县','3'),(3114,3108,'和政县','3'),(3115,3108,'东乡族自治县','3'),(3116,3108,'积石山保安族东乡族撒拉族自治县','3'),(3117,3022,'甘南藏族自治州','2'),(3118,3117,'合作市','3'),(3119,3117,'临潭县','3'),(3120,3117,'卓尼县','3'),(3121,3117,'舟曲县','3'),(3122,3117,'迭部县','3'),(3123,3117,'玛曲县','3'),(3124,3117,'碌曲县','3'),(3125,3117,'夏河县','3'),(3126,0,'青海省','1'),(3127,3126,'西宁市','2'),(3128,3127,'城东区','3'),(3129,3127,'城中区','3'),(3130,3127,'城西区','3'),(3131,3127,'城北区','3'),(3132,3127,'大通回族土族自治县','3'),(3133,3127,'湟中县','3'),(3134,3127,'湟源县','3'),(3135,3126,'海东市','2'),(3136,3135,'乐都区','3'),(3137,3135,'平安县','3'),(3138,3135,'民和回族土族自治县','3'),(3139,3135,'互助土族自治县','3'),(3140,3135,'化隆回族自治县','3'),(3141,3135,'循化撒拉族自治县','3'),(3142,3126,'海北藏族自治州','2'),(3143,3142,'门源回族自治县','3'),(3144,3142,'祁连县','3'),(3145,3142,'海晏县','3'),(3146,3142,'刚察县','3'),(3147,3126,'黄南藏族自治州','2'),(3148,3147,'同仁县','3'),(3149,3147,'尖扎县','3'),(3150,3147,'泽库县','3'),(3151,3147,'河南蒙古族自治县','3'),(3152,3126,'海南藏族自治州','2'),(3153,3152,'共和县','3'),(3154,3152,'同德县','3'),(3155,3152,'贵德县','3'),(3156,3152,'兴海县','3'),(3157,3152,'贵南县','3'),(3158,3126,'果洛藏族自治州','2'),(3159,3158,'玛沁县','3'),(3160,3158,'班玛县','3'),(3161,3158,'甘德县','3'),(3162,3158,'达日县','3'),(3163,3158,'久治县','3'),(3164,3158,'玛多县','3'),(3165,3126,'玉树藏族自治州','2'),(3166,3165,'玉树市','3'),(3167,3165,'杂多县','3'),(3168,3165,'称多县','3'),(3169,3165,'治多县','3'),(3170,3165,'囊谦县','3'),(3171,3165,'曲麻莱县','3'),(3172,3126,'海西蒙古族藏族自治州','2'),(3173,3172,'格尔木市','3'),(3174,3172,'德令哈市','3'),(3175,3172,'乌兰县','3'),(3176,3172,'都兰县','3'),(3177,3172,'天峻县','3'),(3178,0,'宁夏回族自治区','1'),(3179,3178,'银川市','2'),(3180,3179,'兴庆区','3'),(3181,3179,'西夏区','3'),(3182,3179,'金凤区','3'),(3183,3179,'永宁县','3'),(3184,3179,'贺兰县','3'),(3185,3179,'灵武市','3'),(3186,3178,'石嘴山市','2'),(3187,3186,'大武口区','3'),(3188,3186,'惠农区','3'),(3189,3186,'平罗县','3'),(3190,3178,'吴忠市','2'),(3191,3190,'利通区','3'),(3192,3190,'红寺堡区','3'),(3193,3190,'盐池县','3'),(3194,3190,'同心县','3'),(3195,3190,'青铜峡市','3'),(3196,3178,'固原市','2'),(3197,3196,'原州区','3'),(3198,3196,'西吉县','3'),(3199,3196,'隆德县','3'),(3200,3196,'泾源县','3'),(3201,3196,'彭阳县','3'),(3202,3178,'中卫市','2'),(3203,3202,'沙坡头区','3'),(3204,3202,'中宁县','3'),(3205,3202,'海原县','3'),(3206,0,'新疆维吾尔自治区','1'),(3207,3206,'乌鲁木齐市','2'),(3208,3207,'天山区','3'),(3209,3207,'沙依巴克区','3'),(3210,3207,'新市区','3'),(3211,3207,'水磨沟区','3'),(3212,3207,'头屯河区','3'),(3213,3207,'达坂城区','3'),(3214,3207,'米东区','3'),(3215,3207,'乌鲁木齐县','3'),(3216,3206,'克拉玛依市','2'),(3217,3216,'独山子区','3'),(3218,3216,'克拉玛依区','3'),(3219,3216,'白碱滩区','3'),(3220,3216,'乌尔禾区','3'),(3221,3206,'吐鲁番地区','2'),(3222,3221,'吐鲁番市','3'),(3223,3221,'鄯善县','3'),(3224,3221,'托克逊县','3'),(3225,3206,'哈密地区','2'),(3226,3225,'哈密市','3'),(3227,3225,'巴里坤哈萨克自治县','3'),(3228,3225,'伊吾县','3'),(3229,3206,'昌吉回族自治州','2'),(3230,3229,'昌吉市','3'),(3231,3229,'阜康市','3'),(3232,3229,'呼图壁县','3'),(3233,3229,'玛纳斯县','3'),(3234,3229,'奇台县','3'),(3235,3229,'吉木萨尔县','3'),(3236,3229,'木垒哈萨克自治县','3'),(3237,3206,'博尔塔拉蒙古自治州','2'),(3238,3237,'博乐市','3'),(3239,3237,'阿拉山口市','3'),(3240,3237,'精河县','3'),(3241,3237,'温泉县','3'),(3242,3206,'巴音郭楞蒙古自治州','2'),(3243,3242,'库尔勒市','3'),(3244,3242,'轮台县','3'),(3245,3242,'尉犁县','3'),(3246,3242,'若羌县','3'),(3247,3242,'且末县','3'),(3248,3242,'焉耆回族自治县','3'),(3249,3242,'和静县','3'),(3250,3242,'和硕县','3'),(3251,3242,'博湖县','3'),(3252,3206,'阿克苏地区','2'),(3253,3252,'阿克苏市','3'),(3254,3252,'温宿县','3'),(3255,3252,'库车县','3'),(3256,3252,'沙雅县','3'),(3257,3252,'新和县','3'),(3258,3252,'拜城县','3'),(3259,3252,'乌什县','3'),(3260,3252,'阿瓦提县','3'),(3261,3252,'柯坪县','3'),(3262,3206,'克孜勒苏柯尔克孜自治州','2'),(3263,3262,'阿图什市','3'),(3264,3262,'阿克陶县','3'),(3265,3262,'阿合奇县','3'),(3266,3262,'乌恰县','3'),(3267,3206,'喀什地区','2'),(3268,3267,'喀什市','3'),(3269,3267,'疏附县','3'),(3270,3267,'疏勒县','3'),(3271,3267,'英吉沙县','3'),(3272,3267,'泽普县','3'),(3273,3267,'莎车县','3'),(3274,3267,'叶城县','3'),(3275,3267,'麦盖提县','3'),(3276,3267,'岳普湖县','3'),(3277,3267,'伽师县','3'),(3278,3267,'巴楚县','3'),(3279,3267,'塔什库尔干塔吉克自治县','3'),(3280,3206,'和田地区','2'),(3281,3280,'和田市','3'),(3282,3280,'和田县','3'),(3283,3280,'墨玉县','3'),(3284,3280,'皮山县','3'),(3285,3280,'洛浦县','3'),(3286,3280,'策勒县','3'),(3287,3280,'于田县','3'),(3288,3280,'民丰县','3'),(3289,3206,'伊犁哈萨克自治州','2'),(3290,3289,'伊宁市','3'),(3291,3289,'奎屯市','3'),(3292,3289,'霍尔果斯市','3'),(3293,3289,'伊宁县','3'),(3294,3289,'察布查尔锡伯自治县','3'),(3295,3289,'霍城县','3'),(3296,3289,'巩留县','3'),(3297,3289,'新源县','3'),(3298,3289,'昭苏县','3'),(3299,3289,'特克斯县','3'),(3300,3289,'尼勒克县','3'),(3301,3206,'塔城地区','2'),(3302,3301,'塔城市','3'),(3303,3301,'乌苏市','3'),(3304,3301,'额敏县','3'),(3305,3301,'沙湾县','3'),(3306,3301,'托里县','3'),(3307,3301,'裕民县','3'),(3308,3301,'和布克赛尔蒙古自治县','3'),(3309,3206,'阿勒泰地区','2'),(3310,3309,'阿勒泰市','3'),(3311,3309,'布尔津县','3'),(3312,3309,'富蕴县','3'),(3313,3309,'福海县','3'),(3314,3309,'哈巴河县','3'),(3315,3309,'青河县','3'),(3316,3309,'吉木乃县','3'),(3317,3206,'直辖县级','2'),(3318,3317,'石河子市','3'),(3319,3317,'阿拉尔市','3'),(3320,3317,'图木舒克市','3'),(3321,3317,'五家渠市','3'),(3322,3317,'北屯市','3'),(3323,3317,'铁门关市','3'),(3324,3317,'双河市','3'),(3325,0,'台湾','1'),(3326,3325,'台北市','2'),(3327,3326,'松山区','3'),(3328,3326,'信义区','3'),(3329,3326,'大安区','3'),(3330,3326,'中山区','3'),(3331,3326,'中正区','3'),(3332,3326,'大同区','3'),(3333,3326,'万华区','3'),(3334,3326,'文山区','3'),(3335,3326,'南港区','3'),(3336,3326,'内湖区','3'),(3337,3326,'士林区','3'),(3338,3326,'北投区','3'),(3339,3325,'高雄市','2'),(3340,3339,'盐埕区','3'),(3341,3339,'鼓山区','3'),(3342,3339,'左营区','3'),(3343,3339,'楠梓区','3'),(3344,3339,'三民区','3'),(3345,3339,'新兴区','3'),(3346,3339,'前金区','3'),(3347,3339,'苓雅区','3'),(3348,3339,'前镇区','3'),(3349,3339,'旗津区','3'),(3350,3339,'小港区','3'),(3351,3339,'凤山区','3'),(3352,3339,'林园区','3'),(3353,3339,'大寮区','3'),(3354,3339,'大树区','3'),(3355,3339,'大社区','3'),(3356,3339,'仁武区','3'),(3357,3339,'鸟松区','3'),(3358,3339,'冈山区','3'),(3359,3339,'桥头区','3'),(3360,3339,'燕巢区','3'),(3361,3339,'田寮区','3'),(3362,3339,'阿莲区','3'),(3363,3339,'路竹区','3'),(3364,3339,'湖内区','3'),(3365,3339,'茄萣区','3'),(3366,3339,'永安区','3'),(3367,3339,'弥陀区','3'),(3368,3339,'梓官区','3'),(3369,3339,'旗山区','3'),(3370,3339,'美浓区','3'),(3371,3339,'六龟区','3'),(3372,3339,'甲仙区','3'),(3373,3339,'杉林区','3'),(3374,3339,'内门区','3'),(3375,3339,'茂林区','3'),(3376,3339,'桃源区','3'),(3377,3339,'那玛夏区','3'),(3378,3325,'基隆市','2'),(3379,3378,'中正区','3'),(3380,3378,'七堵区','3'),(3381,3378,'暖暖区','3'),(3382,3378,'仁爱区','3'),(3383,3378,'中山区','3'),(3384,3378,'安乐区','3'),(3385,3378,'信义区','3'),(3386,3325,'台中市','2'),(3387,3386,'中区','3'),(3388,3386,'东区','3'),(3389,3386,'南区','3'),(3390,3386,'西区','3'),(3391,3386,'北区','3'),(3392,3386,'西屯区','3'),(3393,3386,'南屯区','3'),(3394,3386,'北屯区','3'),(3395,3386,'丰原区','3'),(3396,3386,'东势区','3'),(3397,3386,'大甲区','3'),(3398,3386,'清水区','3'),(3399,3386,'沙鹿区','3'),(3400,3386,'梧栖区','3'),(3401,3386,'后里区','3'),(3402,3386,'神冈区','3'),(3403,3386,'潭子区','3'),(3404,3386,'大雅区','3'),(3405,3386,'新社区','3'),(3406,3386,'石冈区','3'),(3407,3386,'外埔区','3'),(3408,3386,'大安区','3'),(3409,3386,'乌日区','3'),(3410,3386,'大肚区','3'),(3411,3386,'龙井区','3'),(3412,3386,'雾峰区','3'),(3413,3386,'太平区','3'),(3414,3386,'大里区','3'),(3415,3386,'和平区','3'),(3416,3325,'台南市','2'),(3417,3416,'东区','3'),(3418,3416,'南区','3'),(3419,3416,'北区','3'),(3420,3416,'安南区','3'),(3421,3416,'安平区','3'),(3422,3416,'中西区','3'),(3423,3416,'新营区','3'),(3424,3416,'盐水区','3'),(3425,3416,'白河区','3'),(3426,3416,'柳营区','3'),(3427,3416,'后壁区','3'),(3428,3416,'东山区','3'),(3429,3416,'麻豆区','3'),(3430,3416,'下营区','3'),(3431,3416,'六甲区','3'),(3432,3416,'官田区','3'),(3433,3416,'大内区','3'),(3434,3416,'佳里区','3'),(3435,3416,'学甲区','3'),(3436,3416,'西港区','3'),(3437,3416,'七股区','3'),(3438,3416,'将军区','3'),(3439,3416,'北门区','3'),(3440,3416,'新化区','3'),(3441,3416,'善化区','3'),(3442,3416,'新市区','3'),(3443,3416,'安定区','3'),(3444,3416,'山上区','3'),(3445,3416,'玉井区','3'),(3446,3416,'楠西区','3'),(3447,3416,'南化区','3'),(3448,3416,'左镇区','3'),(3449,3416,'仁德区','3'),(3450,3416,'归仁区','3'),(3451,3416,'关庙区','3'),(3452,3416,'龙崎区','3'),(3453,3416,'永康区','3'),(3454,3325,'新竹市','2'),(3455,3454,'东区','3'),(3456,3454,'北区','3'),(3457,3454,'香山区','3'),(3458,3325,'嘉义市','2'),(3459,3458,'东区','3'),(3460,3458,'西区','3'),(3461,3325,'新北市','2'),(3462,3461,'板桥区','3'),(3463,3461,'三重区','3'),(3464,3461,'中和区','3'),(3465,3461,'永和区','3'),(3466,3461,'新庄区','3'),(3467,3461,'新店区','3'),(3468,3461,'树林区','3'),(3469,3461,'莺歌区','3'),(3470,3461,'三峡区','3'),(3471,3461,'淡水区','3'),(3472,3461,'汐止区','3'),(3473,3461,'瑞芳区','3'),(3474,3461,'土城区','3'),(3475,3461,'芦洲区','3'),(3476,3461,'五股区','3'),(3477,3461,'泰山区','3'),(3478,3461,'林口区','3'),(3479,3461,'深坑区','3'),(3480,3461,'石碇区','3'),(3481,3461,'坪林区','3'),(3482,3461,'三芝区','3'),(3483,3461,'石门区','3'),(3484,3461,'八里区','3'),(3485,3461,'平溪区','3'),(3486,3461,'双溪区','3'),(3487,3461,'贡寮区','3'),(3488,3461,'金山区','3'),(3489,3461,'万里区','3'),(3490,3461,'乌来区','3'),(3491,3325,'宜兰县','2'),(3492,3491,'宜兰市','3'),(3493,3491,'罗东镇','3'),(3494,3491,'苏澳镇','3'),(3495,3491,'头城镇','3'),(3496,3491,'礁溪乡','3'),(3497,3491,'壮围乡','3'),(3498,3491,'员山乡','3'),(3499,3491,'冬山乡','3'),(3500,3491,'五结乡','3'),(3501,3491,'三星乡','3'),(3502,3491,'大同乡','3'),(3503,3491,'南澳乡','3'),(3504,3325,'桃园县','2'),(3505,3504,'桃园市','3'),(3506,3504,'中坜市','3'),(3507,3504,'平镇市','3'),(3508,3504,'八德市','3'),(3509,3504,'杨梅市','3'),(3510,3504,'芦竹市','3'),(3511,3504,'大溪镇','3'),(3512,3504,'大园乡','3'),(3513,3504,'龟山乡','3'),(3514,3504,'龙潭乡','3'),(3515,3504,'新屋乡','3'),(3516,3504,'观音乡','3'),(3517,3504,'复兴乡','3'),(3518,3325,'新竹县','2'),(3519,3518,'竹北市','3'),(3520,3518,'竹东镇','3'),(3521,3518,'新埔镇','3'),(3522,3518,'关西镇','3'),(3523,3518,'湖口乡','3'),(3524,3518,'新丰乡','3'),(3525,3518,'芎林乡','3'),(3526,3518,'横山乡','3'),(3527,3518,'北埔乡','3'),(3528,3518,'宝山乡','3'),(3529,3518,'峨眉乡','3'),(3530,3518,'尖石乡','3'),(3531,3518,'五峰乡','3'),(3532,3325,'苗栗县','2'),(3533,3532,'苗栗市','3'),(3534,3532,'苑里镇','3'),(3535,3532,'通霄镇','3'),(3536,3532,'竹南镇','3'),(3537,3532,'头份镇','3'),(3538,3532,'后龙镇','3'),(3539,3532,'卓兰镇','3'),(3540,3532,'大湖乡','3'),(3541,3532,'公馆乡','3'),(3542,3532,'铜锣乡','3'),(3543,3532,'南庄乡','3'),(3544,3532,'头屋乡','3'),(3545,3532,'三义乡','3'),(3546,3532,'西湖乡','3'),(3547,3532,'造桥乡','3'),(3548,3532,'三湾乡','3'),(3549,3532,'狮潭乡','3'),(3550,3532,'泰安乡','3'),(3551,3325,'彰化县','2'),(3552,3551,'彰化市','3'),(3553,3551,'鹿港镇','3'),(3554,3551,'和美镇','3'),(3555,3551,'线西乡','3'),(3556,3551,'伸港乡','3'),(3557,3551,'福兴乡','3'),(3558,3551,'秀水乡','3'),(3559,3551,'花坛乡','3'),(3560,3551,'芬园乡','3'),(3561,3551,'员林镇','3'),(3562,3551,'溪湖镇','3'),(3563,3551,'田中镇','3'),(3564,3551,'大村乡','3'),(3565,3551,'埔盐乡','3'),(3566,3551,'埔心乡','3'),(3567,3551,'永靖乡','3'),(3568,3551,'社头乡','3'),(3569,3551,'二水乡','3'),(3570,3551,'北斗镇','3'),(3571,3551,'二林镇','3'),(3572,3551,'田尾乡','3'),(3573,3551,'埤头乡','3'),(3574,3551,'芳苑乡','3'),(3575,3551,'大城乡','3'),(3576,3551,'竹塘乡','3'),(3577,3551,'溪州乡','3'),(3578,3325,'南投县','2'),(3579,3578,'南投市','3'),(3580,3578,'埔里镇','3'),(3581,3578,'草屯镇','3'),(3582,3578,'竹山镇','3'),(3583,3578,'集集镇','3'),(3584,3578,'名间乡','3'),(3585,3578,'鹿谷乡','3'),(3586,3578,'中寮乡','3'),(3587,3578,'鱼池乡','3'),(3588,3578,'国姓乡','3'),(3589,3578,'水里乡','3'),(3590,3578,'信义乡','3'),(3591,3578,'仁爱乡','3'),(3592,3325,'云林县','2'),(3593,3592,'斗六市','3'),(3594,3592,'斗南镇','3'),(3595,3592,'虎尾镇','3'),(3596,3592,'西螺镇','3'),(3597,3592,'土库镇','3'),(3598,3592,'北港镇','3'),(3599,3592,'古坑乡','3'),(3600,3592,'大埤乡','3'),(3601,3592,'莿桐乡','3'),(3602,3592,'林内乡','3'),(3603,3592,'二仑乡','3'),(3604,3592,'仑背乡','3'),(3605,3592,'麦寮乡','3'),(3606,3592,'东势乡','3'),(3607,3592,'褒忠乡','3'),(3608,3592,'台西乡','3'),(3609,3592,'元长乡','3'),(3610,3592,'四湖乡','3'),(3611,3592,'口湖乡','3'),(3612,3592,'水林乡','3'),(3613,3325,'嘉义县','2'),(3614,3613,'太保市','3'),(3615,3613,'朴子市','3'),(3616,3613,'布袋镇','3'),(3617,3613,'大林镇','3'),(3618,3613,'民雄乡','3'),(3619,3613,'溪口乡','3'),(3620,3613,'新港乡','3'),(3621,3613,'六脚乡','3'),(3622,3613,'东石乡','3'),(3623,3613,'义竹乡','3'),(3624,3613,'鹿草乡','3'),(3625,3613,'水上乡','3'),(3626,3613,'中埔乡','3'),(3627,3613,'竹崎乡','3'),(3628,3613,'梅山乡','3'),(3629,3613,'番路乡','3'),(3630,3613,'大埔乡','3'),(3631,3613,'阿里山乡','3'),(3632,3325,'屏东县','2'),(3633,3632,'屏东市','3'),(3634,3632,'潮州镇','3'),(3635,3632,'东港镇','3'),(3636,3632,'恒春镇','3'),(3637,3632,'万丹乡','3'),(3638,3632,'长治乡','3'),(3639,3632,'麟洛乡','3'),(3640,3632,'九如乡','3'),(3641,3632,'里港乡','3'),(3642,3632,'盐埔乡','3'),(3643,3632,'高树乡','3'),(3644,3632,'万峦乡','3'),(3645,3632,'内埔乡','3'),(3646,3632,'竹田乡','3'),(3647,3632,'新埤乡','3'),(3648,3632,'枋寮乡','3'),(3649,3632,'新园乡','3'),(3650,3632,'崁顶乡','3'),(3651,3632,'林边乡','3'),(3652,3632,'南州乡','3'),(3653,3632,'佳冬乡','3'),(3654,3632,'琉球乡','3'),(3655,3632,'车城乡','3'),(3656,3632,'满州乡','3'),(3657,3632,'枋山乡','3'),(3658,3632,'三地门乡','3'),(3659,3632,'雾台乡','3'),(3660,3632,'玛家乡','3'),(3661,3632,'泰武乡','3'),(3662,3632,'来义乡','3'),(3663,3632,'春日乡','3'),(3664,3632,'狮子乡','3'),(3665,3632,'牡丹乡','3'),(3666,3325,'台东县','2'),(3667,3666,'台东市','3'),(3668,3666,'成功镇','3'),(3669,3666,'关山镇','3'),(3670,3666,'卑南乡','3'),(3671,3666,'鹿野乡','3'),(3672,3666,'池上乡','3'),(3673,3666,'东河乡','3'),(3674,3666,'长滨乡','3'),(3675,3666,'太麻里乡','3'),(3676,3666,'大武乡','3'),(3677,3666,'绿岛乡','3'),(3678,3666,'海端乡','3'),(3679,3666,'延平乡','3'),(3680,3666,'金峰乡','3'),(3681,3666,'达仁乡','3'),(3682,3666,'兰屿乡','3'),(3683,3325,'花莲县','2'),(3684,3683,'花莲市','3'),(3685,3683,'凤林镇','3'),(3686,3683,'玉里镇','3'),(3687,3683,'新城乡','3'),(3688,3683,'吉安乡','3'),(3689,3683,'寿丰乡','3'),(3690,3683,'光复乡','3'),(3691,3683,'丰滨乡','3'),(3692,3683,'瑞穗乡','3'),(3693,3683,'富里乡','3'),(3694,3683,'秀林乡','3'),(3695,3683,'万荣乡','3'),(3696,3683,'卓溪乡','3'),(3697,3325,'澎湖县','2'),(3698,3697,'马公市','3'),(3699,3697,'湖西乡','3'),(3700,3697,'白沙乡','3'),(3701,3697,'西屿乡','3'),(3702,3697,'望安乡','3'),(3703,3697,'七美乡','3'),(3704,3325,'金门县','2'),(3705,3704,'金城镇','3'),(3706,3704,'金湖镇','3'),(3707,3704,'金沙镇','3'),(3708,3704,'金宁乡','3'),(3709,3704,'烈屿乡','3'),(3710,3704,'乌丘乡','3'),(3711,3325,'连江县','2'),(3712,3711,'南竿乡','3'),(3713,3711,'北竿乡','3'),(3714,3711,'莒光乡','3'),(3715,3711,'东引乡','3'),(3716,0,'香港特别行政区','1'),(3717,3716,'香港岛','2'),(3718,3717,'中西区','3'),(3719,3717,'湾仔区','3'),(3720,3717,'东区','3'),(3721,3717,'南区','3'),(3722,3716,'九龙','2'),(3723,3722,'油尖旺区','3'),(3724,3722,'深水埗区','3'),(3725,3722,'九龙城区','3'),(3726,3722,'黄大仙区','3'),(3727,3722,'观塘区','3'),(3728,3716,'新界','2'),(3729,3728,'荃湾区','3'),(3730,3728,'屯门区','3'),(3731,3728,'元朗区','3'),(3732,3728,'北区','3'),(3733,3728,'大埔区','3'),(3734,3728,'西贡区','3'),(3735,3728,'沙田区','3'),(3736,3728,'葵青区','3'),(3737,3728,'离岛区','3'),(3738,0,'澳门特别行政区','1'),(3739,3738,'澳门半岛','2'),(3740,3739,'花地玛堂区','3'),(3741,3739,'圣安多尼堂区','3'),(3742,3739,'大堂区','3'),(3743,3739,'望德堂区','3'),(3744,3739,'风顺堂区','3'),(3745,3738,'氹仔岛','2'),(3746,3745,'嘉模堂区','3'),(3747,3738,'路环岛','2'),(3748,3747,'圣方济各堂区','3'),(3749,0,'钓鱼岛','1');