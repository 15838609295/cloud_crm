--admin_permissions增加入职时间和转正时间字段;
INSERT INTO `admin_permissions` VALUES (183,'admin.adminWxappPlugin','内部小程序插件管理','内部小程序插件管理',0,'',1,0,'2019-07-25 09:50:59','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (184,'admin.adminWxappPlugin.customer','客户中心','客户中心',183,'',1,0,'2019-07-25 09:52:11','2019-07-25 10:12:06');
INSERT INTO `admin_permissions` VALUES (185,'admin.adminWxappPlugin.exam','考试培训','考试培训',183,'',1,0,'2019-07-25 09:53:03','2019-07-25 10:06:52');
INSERT INTO `admin_permissions` VALUES (186,'admin.user.wallet','系统用户钱包明细','系统用户钱包明细',1,'',1,0,'2019-07-25 09:53:54','2019-07-25 10:12:14');
INSERT INTO `admin_permissions` VALUES (187,'admin.wallet.modify','余额修改( 慎用 )','余额修改( 慎用 )',127,'',1,0,'2019-07-25 09:54:29','2019-07-25 10:06:58');
INSERT INTO `admin_permissions` VALUES (188,'admin.exam.stop','停止考试','停止考试',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (189,'admin.exam.delete','删除考试','删除考试',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (190,'admin.exam.analysis','考试答卷详情','考试答卷详情',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (191,'admin.examinee.edit','考生编辑','考生编辑',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (192,'admin.examinee.assign','考生分配','考生分配',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (193,'admin.examinee.team','考生分组管理','考生分组管理',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (194,'admin.examPaper.create','试卷创建','试卷创建',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (195,'admin.examPaper.export','试卷导出','试卷导出',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (196,'admin.examPaper.modify','试卷修改','试卷修改',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (197,'admin.examPaper.question','试卷题目管理','试卷题目管理',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');
INSERT INTO `admin_permissions` VALUES (198,'admin.examPaper.type','试卷分类管理','试卷分类管理',153,'',1,0,'2019-07-25 11:28:21','2019-07-30 13:41:16');

--admin_permission表cid字段类型;
alter table admin_bonus_log modify `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为售前提成 1为售后提成 2为余额提现 3为售前退款 4为售后奖金 5为奖金提现 6增加余额 7减少余额 8增加奖金 9减少奖金';

--banner增加小程序配置字段;
alter table banner add `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型 1客户 2员工';

--admin_bonus_log增加小程序配置字段;
alter table admin_bonus_log add `submitter` varchar(255) NOT NULL DEFAULT NULL COMMENT '提交人';
alter table admin_bonus_log add `auditor` varchar(255) NOT NULL DEFAULT NULL COMMENT '审核人';

--home_page增加小程序配置字段;
alter table home_page add `type` tinyint(3) DEFAULT NULL COMMENT '类型 1客户小程序 2员工小程序';

--salebonusrule增加状态和类型字段;
alter table salebonusrule add `status` tinyint(1) DEFAULT NULL COMMENT '状态 1正常 0奖金';
alter table salebonusrule add `type` tinyint(1) DEFAULT NULL COMMENT '类型 1提成 2奖金';

--configs增加小程序配置字段;
alter table configs add `wechat_name` varchar(255) DEFAULT NULL COMMENT '员工小程序名称';
alter table configs add `wechat_color` varchar(255) DEFAULT NULL COMMENT '员工小程序主题色';
alter table configs add `admin_wechat_qr` varchar(255) DEFAULT NULL COMMENT '员工小程序二维码';
alter table configs add `member_wechat_qr` varchar(255) DEFAULT NULL COMMENT '客户小程序二维码';
alter table configs add `bonus_explain` varchar(255) DEFAULT NULL COMMENT '奖金说明';
alter table configs add `qy_wx_pay_key` varchar(50) DEFAULT NULL COMMENT '企业微信支付kay';

 --修改configs表字段值;
update configs set sms_appid='1400193859' where id=1;
update configs set sms_appkey='2a0277f28897687907f6a3035e5830af' where id=1;
update configs set qy_wx_pay_key='8b2b1ffc787adf02ea108a8b58351edc' where id=1;

 --修改home_page表字段值;
update home_page set type= 1 where id=1;
update home_page set type= 1 where id=2;
update home_page set type= 1 where id=3;
update home_page set type= 1 where id=4;
update home_page set type= 1 where id=5;

 --home_page表添加数据;
INSERT INTO `home_page` VALUES (6,'轮播',1,1,'2019-07-25 11:28:21',2);
INSERT INTO `home_page` VALUES (7,'公告',2,1,'2019-07-25 11:28:21',2);
INSERT INTO `home_page` VALUES (8,'插件',3,1,'2019-07-25 11:28:21',2);
INSERT INTO `home_page` VALUES (9,'新闻',4,1,'2019-07-25 11:28:21',2);
INSERT INTO `home_page` VALUES (10,'推荐',5,1,'2019-07-25 11:28:21',2);

--新增考试表;
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
  `notice` tinyint(1) DEFAULT NULL COMMENT '通知方式 1短信 2微信',
  `subject_list` text COMMENT '所选试卷题目',
  `found` varchar(255) DEFAULT NULL COMMENT '创建人',
  `type_id` int(11) DEFAULT NULL COMMENT '试卷类型id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subject_number` int(5) DEFAULT NULL COMMENT '题目总数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='考试表';

--新增试卷类型表;
DROP TABLE IF EXISTS `exam_type`;
CREATE TABLE `exam_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '类型名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='试卷类型';


--新增活答卷表;
DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `name` varchar(255) DEFAULT NULL COMMENT '用户名',
  `exam_id` int(11) DEFAULT NULL COMMENT '考试id',
  `answer` varchar(255) DEFAULT NULL COMMENT '回答json存储',
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

--新增考生分组表;
DROP TABLE IF EXISTS `examinee_group`;
CREATE TABLE `examinee_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '分组名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `group_type` tinyint(1) DEFAULT NULL COMMENT '分组类型 1是员工分组 0客户分组',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='考生分组表';

--新增题库表;
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

-- -- 新增试卷表;
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

--新增考生分组记录表;
DROP TABLE IF EXISTS `examinee_group_role`;
CREATE TABLE `examinee_group_role` (
  `examinee_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '分组id',
  `u_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分组记录表';
