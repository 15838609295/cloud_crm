<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
Route::any('/login', function () {
    return redirect('/index.html');
});
Route::any('/index', function () {
    return redirect('/index.html');
});

/* 未登录可访问页面 */
Route::any('signin', 'LoginController@login');                                                                       //  登录
Route::any('qyWexin', 'LoginController@qyWexin');                                                                    //  企业微信登录
Route::any('signout', 'LoginController@logout');                                                                     //  登出

Route::any('test','CommonController@test');                                                                          //测试

Route::any('Nolandfall','LoginController@Nolandfall');                                                             //平台免登陆接口

Route::any('install/index','InstallController@index');                                                              //安装第一步
Route::any('install/testing','InstallController@testing');                                                          //安装第二步
Route::any('install/checkDir','InstallController@checkDir');                                                        //安装第三步
Route::any('install/mkDatabase','InstallController@mkDatabase');                                                    //安装第四步
Route::any('install/formatDataBase','InstallController@formatDataBase');                                            //安装第五步

Route::any('check','UpdateController@chack_version');                                                               //  检查更新
Route::any('settingOutdated','UpdateController@settingOutdated');                                                  //  关闭站点
Route::any('readyDownload','UpdateController@ready_download');                                                     //  下载压缩包
Route::any('readyUnzip','UpdateController@ready_unzip');                                                            //  解压压缩包
Route::any('getDirName','UpdateController@get_dir_name');                                                           //  获取压缩包文件列表
Route::any('moveDir','UpdateController@move_dir');                                                                   //  移动覆盖旧代码
Route::any('getDbTableNames','UpdateController@getDbTableNames');                                                  //  获取备份数据库表名
Route::any('backupsTableName','UpdateController@backupsTableName');                                                //  备份表结构和表信息
Route::any('updateDatabase','UpdateController@updateDatabase');                                                    //  更新数据库
Route::any('updateFail','UpdateController@update_fail');                                                            //  更新失败删除下载的压缩包与文件夹


Route::any('createDatabase', 'CommonController@createDatabase');                                                   //初始化数据库

Route::any('updateAfterSale', 'CommonController@updateAfterSale');
Route::any('updateRemindTime', 'CommonController@updateRemindTime');
Route::any('timingUpdateFormId', 'CommonController@timingUpdateFormId');
Route::any('updateActivityStatus', 'CommonController@updateActivityStatus');
Route::any('updateAfterSaleBonus', 'CommonController@updateAfterSaleBonus');
Route::any('customerUpdateInfo', 'CommonController@customerUpdateInfo');
Route::any('verifyQCloudCustomerList', 'CommonController@verifyQCloudCustomerList');
Route::post('getYzm', 'CommonController@getYzm');                              //比较验证码
Route::post('ajax/upload', 'FilesController@UploadPicture');                   //上传图片

Route::any('recordOperateData', 'CommonController@recordOperateData');         //记录平台运营数据
Route::any('CompanyOperateData', 'CommonController@CompanyOperateData');       //记录部门运营数据
Route::any('personalOperateData', 'CommonController@personalOperateData');     //记录个人运营数据

Route::any('create', 'CommonController@create');                              

Route::any('supplement', 'CommonController@supplement');    //测试补充个人业绩
Route::any('supplementCompany', 'CommonController@supplementCompany');    //测试补充部门业绩
Route::any('supplementplatform', 'CommonController@supplementplatform');    //测试补充平台业绩

Route::get('ajax/downloadFiles', 'FilesController@downloadFiles'); //下载文件
//跳转到member用户
Route::get('member/adminweblogin/{id}', 'MembersController@adminweblogin');
//企业微信跳转
Route::get('crm.netbcloud.com', 'QyWechatController@qywechat_login');
//个人微信跳转
//Route::get('crm.netbcloud.com', 'QyWechatController@wechat_login');


Route::any('toExcel','QuestionnaireController@get_excel');  //上传excel文件


/* 菜单列表 */
Route::any('config/menu', ['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@UserMenu']);                       //  获取菜单权限列表

Route::any('upload/avatar', ['f_auth' => 'admin.index.index', 'uses' => 'FilesController@UploadAvatar']);                   //  上传头像
Route::any('upload/picture', ['f_auth' => 'admin.index.index', 'uses' => 'FilesController@UploadPicture']);                 //  上传图片
Route::any('ajax/uploadContract', 'FilesController@uploadContract');                                                        //  上传合同
Route::any('ajax/uploadAnnex', 'FilesController@uploadAnnex');                                                              //  上传附件
Route::any('ajax/uploadVideo', 'FilesController@uploadVideo');                                                              //  上传视频
Route::any('cloudDownlods/file',['f_auth' => 'admin.index.index', 'uses' => 'CommonController@cloudDownlods']);             //  云开发版前端下载文件
Route::any('download/file',['f_auth' => 'admin.index.index', 'uses' => 'CommonController@download']);                       //  前端下载文件

Route::any('image/upload', ['f_auth' => 'admin.index.index', 'uses' => 'FilesController@imageUpload']);                     //  上传图片
Route::any('image/getHistoryList', ['f_auth' => 'admin.index.index', 'uses' => 'FilesController@getHistoryList']);          //  上传图片历史记录
Route::any('image/delHistory', ['f_auth' => 'admin.index.index', 'uses' => 'FilesController@delHistory']);                  //  软删除图片历史记录
Route::any('image/delete', ['f_auth' => 'admin.index.index', 'uses' => 'FilesController@delete']);                          //  删除上传图片
Route::any('upload/vodSign', ['f_auth' => 'admin.index.index', 'uses' => 'FilesController@vodSign']);                       //  云点播签名

Route::any('image/ueditor', ['f_auth' => 'admin.index.index', 'uses' => 'FilesController@uploadByUEditor']);                //  UEditor相关的
Route::any('UEditor/upload',['f_auth' => 'admin.index.index', 'uses' => 'FilesController@uploadPicByUEditor']);             //  UEditor上传图片
//测试
Route::any('upload/file', 'CommonController@uploadzipfile');



/* 用户基础信息 */
Route::any('basic', ['f_auth' => 'admin.index.index', 'uses' => 'UserController@basic']);                               //  获取用户基础信息
Route::any('modify', ['f_auth' => 'admin.index.index', 'uses' => 'UserController@modify']);                             //  修改用户基础信息

/* 控制面板 */
Route::any('index/index', ['f_auth' => 'admin.index.index', 'uses' => 'IndexController@index']);                            //首页
Route::any('index/contact-list', ['f_auth' => 'admin.index.index', 'uses' => 'IndexController@contactList']);               //获取首页待沟通客户列表
Route::any('index/user-branch', ['f_auth' => 'admin.index.index', 'uses' => 'IndexController@userBranchData']);             //获取团队成员
Route::any('index/userBranchList', ['f_auth' => 'admin.index.index', 'uses' => 'IndexController@userBranchList']);          //获取我的所有团队成员

/* 统计相关 */
Route::any('statistic/work-list', ['f_auth' => 'admin.index.index', 'uses' => 'StatisticController@attendanceList']);           //  签到列表
Route::any('statistic/business/{id}', ['f_auth' => 'admin.index.index', 'uses' => 'StatisticController@getBusinessInfo']);      //  营业统计
Route::any('statistic/rank-list', ['f_auth' => 'admin.index.index', 'uses' => 'StatisticController@rankList']);                 //  业绩排行榜
Route::any('statistic/team-list', ['f_auth' => 'admin.index.index', 'uses' => 'StatisticController@teamList']);                 //  团队业绩排行榜

Route::any('statistic/allList', ['f_auth' => 'admin.index.index', 'uses' => 'OrderFormController@allList']);                    //  销售成单率排行榜
Route::any('statistic/companyList', ['f_auth' => 'admin.index.index', 'uses' => 'OrderFormController@companyList']);            //  部门成单率排行榜
Route::any('statistic/branchList', ['f_auth' => 'admin.index.index', 'uses' => 'OrderFormController@branchList']);              //  团队成单率排行榜

Route::any('platform/index', ['f_auth' => 'admin.index.index', 'uses' => 'PlatformController@index']);                        //平台运营数据统计
Route::any('platform/company', ['f_auth' => 'admin.index.index', 'uses' => 'PlatformController@company']);                    //部门运营数据统计
Route::any('platform/companyUserList', ['f_auth' => 'admin.index.index', 'uses' => 'PlatformController@companyUserList']);    //部门下个人运营数据统计
Route::any('platform/user', ['f_auth' => 'admin.index.index', 'uses' => 'PlatformController@user']);                          //个人运营数据统计

/* 权限管理 */
Route::any('permission/index', ['f_auth' => 'admin.permission', 'uses' => 'PermissionController@index']);                     //  权限
Route::any('permission/list', ['f_auth' => 'admin.permission.index', 'uses' => 'PermissionController@dataList']);             //  权限列表
Route::any('permission/create', ['f_auth' => 'admin.permission.create', 'uses' => 'PermissionController@create']);            //  权限添加
Route::any('permission/edit/{id}', ['f_auth' => 'admin.permission.edit', 'uses' => 'PermissionController@edit']);             //  权限编辑
Route::any('permission/delete/{id}', ['f_auth' => 'admin.permission.destroy', 'uses' => 'PermissionController@delete']);      //  权限删除
Route::any('PlugInNnit/get', 'ConfigsController@get_function_menut');                                                         //  插件
Route::any('PlugInNnit/update/{id}', 'ConfigsController@update_function_menut');                                              //  修改

/* 角色管理 */
Route::any('role/index', ['f_auth' => 'admin.role', 'uses' => 'RoleController@index']);                                 //  角色
Route::any('role/list', ['f_auth' => 'admin.role.index', 'uses' => 'RoleController@dataList']);                         //  角色列表
Route::any('role/all-list', ['f_auth' => 'admin.role.index', 'uses' => 'RoleController@roleList']);                     //  角色列表
Route::any('role/auth-list', ['f_auth' => 'admin.role.index', 'uses' => 'RoleController@rolePermission']);              //  角色权限列表
Route::any('role/create', ['f_auth' => 'admin.role.create', 'uses' => 'RoleController@create']);                        //  角色添加
Route::any('role/edit/{id}', ['f_auth' => 'admin.role.edit', 'uses' => 'RoleController@edit']);                         //  角色编辑
Route::any('role/delete/{id}', ['f_auth' => 'admin.role.destroy', 'uses' => 'RoleController@delete']);                  //  角色删除

/* 用户管理 */
Route::any('user/index', ['f_auth' => 'admin.user.index', 'uses' => 'UserController@index']);                           //  用户
Route::any('user/list', ['f_auth' => 'admin.user.index', 'uses' => 'UserController@dataList']);                         //  用户列表
Route::any('work/staff', ['f_auth' => 'admin.workOrder.staff', 'uses' => 'UserController@dataList']);                   //  用户列表
Route::any('user/all-list', ['f_auth' => 'admin.index.index', 'uses' => 'UserController@adminList']);                   //  用户列表
Route::any('user/admin-list', ['f_auth' => 'admin.index.index', 'uses' => 'UserController@adminAllList']);              //  用户列表
Route::any('user/detail/{id}', ['f_auth' => 'admin.user.index', 'uses' => 'UserController@detail']);                    //  用户详情
Route::any('user/create', ['f_auth' => 'admin.user.create', 'uses' => 'UserController@create']);                        //  用户添加
Route::any('user/edit/{id}',['f_auth' => 'admin.user.index', 'uses' => 'UserController@edit']);                         //  用户编辑
Route::any('user/ajax/{id}', ['f_auth' => 'admin.user.edit', 'uses' => 'UserController@ajax']);                         //  用户操作
Route::any('user/editBasic/{id}', ['f_auth' => 'admin.workstatus.index', 'uses' => 'UserController@editBasic']);        //  打卡签到
Route::any('user/delete/{id}', ['f_auth' => 'admin.user.destroy', 'uses' => 'UserController@delete']);                  //  用户删除
Route::any('user/exchange', 'UserController@exchangeOrder');                                                            //  用户客户和订单转移(未完)
Route::any('user/switchUser', 'UserController@switch_user');                                                            //  root帐号登录管理帐号

/* 团队管理 */
Route::any('branch/index', 'BranchController@index');                                                                   //  团队
Route::any('branch/list', ['f_auth' => 'admin.branch.index', 'uses' => 'BranchController@dataList']);                   //  团队列表
Route::any('branch/all-list', ['f_auth' => 'admin.index.index', 'uses' => 'BranchController@branchList']);              //  团队列表
Route::any('branch/create', 'BranchController@create');                                                                 //  团队添加
Route::any('branch/edit/{id}', 'BranchController@edit');                                                                //  团队编辑
Route::any('branch/delete/{id}', 'BranchController@delete');                                                            //  团队删除

/* 公司管理 */
Route::any('company/index', 'CompanyController@index');                                                                 //  公司
Route::any('company/list', ['f_auth' => 'admin.company.index', 'uses' => 'CompanyController@dataList']);                //  公司列表
Route::any('company/all-list', ['f_auth' => 'admin.index.index', 'uses' => 'CompanyController@companyList']);           //  公司列表
Route::any('company/create', ['f_auth' => 'admin.index.index', 'uses' => 'CompanyController@create']);                  //  公司添加
Route::any('company/edit/{id}', ['f_auth' => 'admin.index.index', 'uses' => 'CompanyController@edit']);                 //  公司编辑
Route::any('company/delete/{id}', ['f_auth' => 'admin.index.index', 'uses' => 'CompanyController@delete']);             //  公司删除

/* 商品管理 */
Route::any('goods/index', 'GoodsController@index');                                                                             //  商品
Route::any('goods/list', ['f_auth' => 'admin.goods.index', 'uses' => 'GoodsController@dataList']);                              //  商品列表
Route::any('goods/all-list', ['f_auth' => 'admin.index.index', 'uses' => 'GoodsController@goodsList']);                         //  商品全部列表
Route::any('goods/detail/{id}', ['f_auth' => 'admin.goods.index', 'uses' => 'GoodsController@detail']);                         //  商品详情
Route::any('goods/create', ['f_auth' => 'admin.goods.create', 'uses' => 'GoodsController@create']);                             //  商品添加
Route::any('goods/edit/{id}', ['f_auth' => 'admin.goods.edit', 'uses' => 'GoodsController@edit']);                              //  商品编辑
Route::any('goods/ajax/{id}', ['f_auth' => 'admin.goods.edit', 'uses' => 'GoodsController@ajax']);                              //  商品操作
Route::any('goods/delete/{id}', ['f_auth' => 'admin.goods.destroy', 'uses' => 'GoodsController@delete']);                       //  商品删除
Route::any('goods/goodsTypeList', ['f_auth' => 'admin.index.index', 'uses' => 'GoodsController@goods_type_list']);              //  商品类型列表
Route::any('goods/addGoodsLype', ['f_auth' => 'admin.product.productType', 'uses' => 'GoodsController@add_goods_type']);        //  添加商品类型
Route::any('goods/delGoodsType/{id}', ['f_auth' => 'admin.product.productType', 'uses' => 'GoodsController@del_goods_type']);   //  删除商品类型
Route::any('goods/updateGoodsType', ['f_auth' => 'admin.product.productType', 'uses' => 'GoodsController@update_goods_type']);  //  修改商品类型

/* 案例管理 */
Route::any('case/index', 'CaseController@index');                                                                       //案例
Route::any('case/list', ['f_auth' => 'admin.case.index', 'uses' => 'CaseController@dataList']);                         //案例列表
Route::any('case/type-list', ['f_auth' => 'admin.case.index', 'uses' => 'CaseController@typeList']);                    //案例类型列表
Route::any('case/create', 'CaseController@create');                                                                     //案例添加
Route::any('case/edit/{id}', 'CaseController@edit');                                                                    //案例编辑
Route::any('case/delete/{id}', 'CaseController@delete');                                                                //案例删除

/*我的钱包*/
Route::any('bonus/mywallet', ['f_auth' => 'admin.wallet.details', 'uses' => 'UserController@my_wallet']);                  //  钱包明细
Route::any('bonus/expectbonus', ['f_auth' => 'admin.wallet.bonus', 'uses' => 'UserController@expect_bonus']);              //  预期奖金
Route::any('bonus/delExpectDonus/{id}', ['f_auth' => 'admin.wallet.bonus', 'uses' => 'UserController@delExpectDonus']);    //  奖金删除
Route::any('bonus/applymoney', ['f_auth' => 'admin.wallet.bonus.get', 'uses' => 'UserController@apply_money']);            //  提现申请
Route::any('bonus/capitalOperation', ['f_auth' => 'admin.wallet.bonus.get', 'uses' => 'UserController@capitalOperation']); //  用户资金操作

/* 客户管理 */
Route::any('member/index', 'MemberController@index');                                                                     //  客户
Route::any('member/list', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@dataList']);                     //  客户列表
Route::any('member/detail/{id}', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@detail']);                //  客户详情
Route::any('member/create', ['f_auth' => 'admin.member.create', 'uses' => 'MemberController@create']);                    //  客户添加
Route::any('member/edit/{id}', ['f_auth' => 'admin.member.edit', 'uses' => 'MemberController@edit']);                     //  客户编辑
Route::any('member/ajax/{id}', ['f_auth' => 'admin.member.edit', 'uses' => 'MemberController@ajax']);                     //  客户操作
Route::any('member/delete/{id}', ['f_auth' => 'admin.member.destroy', 'uses' => 'MemberController@delete']);              //  客户删除
Route::any('member/source-list', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@memberSource']);          //  客户等级列表
Route::any('member/level-list', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@memberLevel']);            //  客户来源列表
Route::any('member/moneyEdit/{id}', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@moneyEdit']);          //  客户金额更新
Route::any('member/CapitalDetails', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@CapitalDetails']);     //  客户余额（赠送金）明细

Route::any('member/afterSaleService', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@afterSaleService']);   //  售后服务列表
Route::any('member/contactLog/{id}', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@contactLog']);          //  沟通记录
Route::any('member/updateStarClass', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@updateStarClass']);     //  修改客户星级
Route::any('member/memberAssignLog', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@memberAssignLog']);     //  修改指派人
Route::any('member/contact', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@contact']);                     //  增加联系记录
Route::any('member/delDataInfo', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@delDataInfo']);             //  删除记录

Route::any('member/addmaintainList', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@addmaintainList']);      //  维护信息
Route::any('member/updateRemind', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@updateRemind']);            //  修改提醒信息
Route::any('member/addAccountNumber', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@addAccountNumber']);    //  添加账号信息
Route::any('member/updateDetails', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@updateDetails']);          //  修改详情
Route::any('member/addTrusteeship', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@addTrusteeship']);        //  添加代码托管信息
Route::any('member/addAnnex', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@addAnnex']);                    //  添加附件信息
Route::any('member/addContract', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@addContract']);              //  添加合同信息
Route::any('member/contractList', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@contractList']);            //  合同信息列表
Route::any('member/ordersMember', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@ordersMember']);            //  订单列表
Route::any('member/updateAccount', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@updateAccount']);          //  修改账号信息
Route::any('member/updateTrusteeship', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@updateTrusteeship']);  //  修改托管信息
Route::any('member/updateAnnex', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@updateAnnex']);              //  修改附件信息
Route::any('member/updateContract', ['f_auth' => 'admin.member.index', 'uses' => 'MemberController@updateContract']);        //  修改合同信息

/* 未激活客户管理 */
Route::any('customer/index', 'CustomerController@index');                                                                    //客户
Route::any('customer/list', ['f_auth' => 'admin.customer.index', 'uses' => 'CustomerController@dataList']);                  //客户列表
Route::any('customer/detail/{id}', ['f_auth' => 'admin.customer.index', 'uses' => 'CustomerController@detail']);             //客户详情
Route::any('customer/updateLog/{id}', ['f_auth' => 'admin.customer.index', 'uses' => 'CustomerController@customerLog']);     //客户修改记录
Route::any('customer/create', ['f_auth' => 'admin.customer.create', 'uses' => 'CustomerController@create']);                 //客户添加
Route::any('customer/edit/{id}', ['f_auth' => 'admin.customer.edit', 'uses' => 'CustomerController@edit']);                  //客户编辑
Route::any('customer/ajax/{id}', ['f_auth' => 'admin.customer.edit', 'uses' => 'CustomerController@ajax']);                  //客户操作
Route::any('customer/export', ['f_auth' => 'admin.customer.excel', 'uses' => 'CustomerController@export']);                  //客户导出
Route::any('customer/delete/{id}', ['f_auth' => 'admin.customer.destroy', 'uses' => 'CustomerController@delete']);           //客户删除
Route::any('customer/deleteall', ['f_auth' => 'admin.customer.destroy', 'uses' => 'CustomerController@deleteall']);          //客户批量删除

/* 订单管理 */
Route::any('order/index', 'OrderController@index');                                                                          //订单
Route::any('order/list', ['f_auth' => 'admin.order.index', 'uses' => 'OrderController@dataList']);                           //订单列表
Route::any('order/ajax/{id}', ['f_auth' => 'admin.order.edit', 'uses' => 'OrderController@ajax']);                           //订单操作

/* 业绩订单管理 */
Route::any('achievement/index', 'AchievementController@index');                                                                         //业绩订单
Route::any('achievement/list', ['f_auth' => 'admin.achievement.index', 'uses' => 'AchievementController@dataList']);                    //业绩订单列表
Route::any('achievement/detail/{id}',['f_auth' => 'admin.achievement.index', 'uses' => 'AchievementController@detail']);                //业绩订单详情
Route::any('achievement/edit/{id}', ['f_auth' => 'admin.achievement.edit', 'uses' => 'AchievementController@edit']);                    //业绩订单修改
Route::any('achievement/ajax/{id}', ['f_auth' => 'admin.achievement.edit', 'uses' => 'AchievementController@ajax']);                    //业绩订单操作
Route::any('achievement/verify/{id}', ['f_auth' => 'admin.achievement.examine', 'uses' => 'AchievementController@verifyRecord']);       //业绩订单审核
Route::any('achievement/export', ['f_auth' => 'admin.achievement.export', 'uses' => 'AchievementController@export']);                   //业绩订单导出
Route::any('achievement/delete/{id}', ['f_auth' => 'admin.achievement.destroy', 'uses' => 'AchievementController@delete']);             //业绩订单删除
Route::any('member/addAchievement/{id}', ['f_auth' => 'admin.achievement.create', 'uses' => 'AchievementController@achievementAdd']);   //(添加)业绩录入
Route::any('achievement/getList', ['f_auth' => 'admin.member.index', 'uses' => 'AchievementController@getList']);                       //个人业绩列表
Route::any('achievement/exchangeOrder', ['f_auth' => 'admin.member.index', 'uses' => 'AchievementController@exchangeOrder']);           //业绩订单转移

/* 售后订单管理 */
Route::any('aftersale/index', 'AftersaleController@index');                                                                    //售后订单
Route::any('aftersale/list', ['f_auth' => 'admin.aftersale.index', 'uses' => 'AftersaleController@dataList']);                 //售后订单列表
Route::any('aftersale/detail/{id}',['f_auth' => 'admin.aftersale.index', 'uses' => 'AftersaleController@detail']);             //售后订单详情
Route::any('aftersale/create', ['f_auth' => 'admin.aftersale.create', 'uses' => 'AftersaleController@create']);                //售后订单添加
Route::any('aftersale/edit/{id}', ['f_auth' => 'admin.aftersale.edit', 'uses' => 'AftersaleController@edit']);                 //售后订单修改
Route::any('aftersale/ajax/{id}', ['f_auth' => 'admin.aftersale.edit', 'uses' => 'AftersaleController@ajax']);                 //售后订单操作
Route::any('aftersale/exchangeOrder', ['f_auth' => 'admin.aftersale.edit', 'uses' => 'AftersaleController@exchangeOrder']);    //售后订单转移
Route::any('aftersale/delete/{id}', ['f_auth' => 'admin.aftersale.destroy', 'uses' => 'AftersaleController@delete']);          //售后订单删除
#Route::any('achievement/verify/{id}', 'AchievementController@verifyRecord');

/* 销售提成规则管理 */
Route::any('salerule/index', 'SaleRuleController@index');                                                                        //提成规则
Route::any('salerule/list', ['f_auth' => 'admin.salebonusrule.index', 'uses' => 'SaleRuleController@dataList']);                 //提成规则列表
Route::any('salerule/all-list', ['f_auth' => 'admin.index.index', 'uses' => 'SaleRuleController@saleRuleList']);                 //提成规则全部列表
Route::any('salerule/detail/{id}',['f_auth' => 'admin.salerule.index', 'uses' => 'SaleRuleController@detail']);                  //提成规则详情
Route::any('salerule/create', ['f_auth' => 'admin.salebonusrule.index', 'uses' => 'SaleRuleController@create']);                 //提成规则添加
Route::any('salerule/edit/{id}', ['f_auth' => 'admin.salebonusrule.index', 'uses' => 'SaleRuleController@edit']);                //提成规则修改
Route::any('salerule/delete/{id}', ['f_auth' => 'admin.salebonusrule.index', 'uses' => 'SaleRuleController@delete']);            //提成规则删除
Route::any('salerule/updateSaleRule', ['f_auth' => 'admin.salebonusrule.index', 'uses' => 'SaleRuleController@updateSaleRule']); //提成规则状态

/* 文章管理 */
Route::any('article/index', ['f_auth' => 'admin.articles.index', 'ArticleController@index']);                             //  文章规则
Route::any('article/list', ['f_auth' => 'admin.articles.index', 'uses' => 'ArticleController@dataList']);                 //  系统文章列表
Route::any('article/partDataList', ['f_auth' => 'admin.articles.index', 'uses' => 'ArticleController@partDataList']);     //  插件文章列表
Route::any('article/detail/{id}',['f_auth' => 'admin.articles.index', 'uses' => 'ArticleController@detail']);             //  文章详情
Route::any('article/create', ['f_auth' => 'admin.articles.create', 'uses' => 'ArticleController@create']);                //  文章添加
Route::any('article/edit/{id}', ['f_auth' => 'admin.articles.edit', 'uses' => 'ArticleController@edit']);                 //  文章修改
Route::any('article/ajax/{id}', ['f_auth' => 'admin.article.edit', 'uses' => 'ArticleController@ajax']);                  //  文章操作
Route::any('article/delete/{id}', ['f_auth' => 'admin.article.destroy', 'uses' => 'ArticleController@delete']);           //  文章删除

/* 财务管理 */
Route::any('finance/balance', ['f_auth' => 'admin.wallet.index', 'uses' => 'FinanceController@balance']);                    //余额
Route::any('finance/balance-list', ['f_auth' => 'admin.wallet.index', 'uses' => 'FinanceController@balanceRecord']);         //余额明细
Route::any('finance/coupon', ['f_auth' => 'admin.donation.index', 'uses' => 'FinanceController@giftMoney']);                 //赠送金
Route::any('finance/coupon-list', ['f_auth' => 'admin.donation.index', 'uses' => 'FinanceController@giftMoneyRecord']);      //赠送金明细
Route::any('finance/commossion', ['f_auth' => 'admin.bonuslog.index', 'uses' => 'FinanceController@commissionRecord']);      //提成记录
Route::any('finance/withdrawals', ['f_auth' => 'admin.donation.index', 'uses' => 'FinanceController@withdrawalsRecord']);    //提现记录
Route::any('finance/ajax/{id}', ['f_auth' => 'admin.takemoney.edit', 'uses' => 'FinanceController@ajax']);                   //财务操作


//人事管理
Route::any('hr/getDataList', ['f_auth' => 'admin.hr.index', 'uses' => 'HrController@getDataList']);                       //人事列表
Route::any('hr/getInfo/{id}', ['f_auth' => 'personalInformation', 'uses' => 'HrController@getInfo']);                     //获取人事信息
Route::any('hr/save/{id}', ['f_auth' => 'admin.hr.edit', 'uses' => 'HrController@update']);                               //更新人事信息
Route::any('hr/add', ['f_auth' => 'admin.hr.edit', 'uses' => 'HrController@insert']);                                     //添加人事信息
Route::any('hr/del/{id}', ['f_auth' => 'admin.hr.edit', 'uses' => 'HrController@del']);                                   //删除人事信息

//系统设置 - 等级设置
Route::any('memberlevel/list', ['f_auth' => 'admin.memberlevel.index', 'uses' => 'MemberLevelController@getDataList']);      //  等级列表
Route::any('memberlevel/detail/{id}', ['f_auth' => 'admin.memberlevel.index', 'uses' => 'MemberLevelController@detail']);    //  等级详细
Route::any('memberlevel/create', ['f_auth' => 'admin.memberlevel.create', 'uses' => 'MemberLevelController@create']);        //  添加等级
Route::any('memberlevel/update/{id}', ['f_auth' => 'admin.memberlevel.edit', 'uses' => 'MemberLevelController@update']);     //  更新等级
Route::any('memberlevel/del/{id}', ['f_auth' => 'admin.memberlevel.destroy', 'uses' => 'MemberLevelController@destroy']);    //  删除等级

//来源管理
Route::any('membersource/list', ['f_auth' => 'admin.membersource.index', 'uses' => 'MemberSourceController@getDataList']);    //  来源列表
Route::any('membersource/create', ['f_auth' => 'admin.membersource.index', 'uses' => 'MemberSourceController@create']);       //  添加来源
Route::any('membersource/detail/{id}', ['f_auth' => 'admin.membersource.index', 'uses' => 'MemberSourceController@detail']);  //  来源详细
Route::any('membersource/update/{id}', ['f_auth' => 'admin.membersource.index', 'uses' => 'MemberSourceController@update']);  //  更新来源
Route::any('membersource/del/{id}', ['f_auth' => 'admin.membersource.index', 'uses' => 'MemberSourceController@destroy']);    //  删除来源

//问题反馈路由
Route::any('problem/getDataList', ['f_auth' => 'admin.problem.index', 'uses' => 'ProblemController@getDataList']);         //问题列表
Route::any('problem/update',['f_auth' => 'admin.problem.update', 'uses' => 'ProblemController@update']);                   //问题修改
Route::any('problem/create',['f_auth' => 'admin.problem.create', 'uses' => 'ProblemController@create']);                   //添加问题

//工单路由
Route::any('workorder/enterprise', ['f_auth' => 'admin.index.index', 'uses' => 'WorkOrderController@enterprise']);                                   //  公司信息
Route::any('work/staff', ['f_auth' => 'admin.workOrder.staff', 'uses' => 'UserController@dataList']);                                                //  用户列表
Route::any('work/userToExcel', ['f_auth' => 'admin.workOrder.staff', 'uses' => 'UserController@userToExcel']);                                       //  用户列表导出
Route::any('workorder/UpdateEnterprise', ['f_auth' => 'admin.index.index', 'uses' => 'WorkOrderController@UpdateEnterprise']);                       //  修改公司信息
Route::any('workorder/streetList', ['f_auth' => 'admin.workOrder.area', 'uses' => 'WorkOrderController@streetList']);                                //  街道列表
Route::any('workorder/streetToExcel', ['f_auth' => 'admin.workOrder.area', 'uses' => 'WorkOrderController@streetToExcel']);                          //  街道列表
Route::any('workorder/getNoPageStreet', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@getNoPageStreet']);                      //  街道无分页列表
Route::any('workorder/addStreetInfo', ['f_auth' => 'admin.workOrder.area', 'uses' => 'WorkOrderController@addStreetInfo']);                          //  添加街道信息
Route::any('workorder/updateStreet', ['f_auth' => 'admin.workOrder.area', 'uses' => 'WorkOrderController@updateStreet']);                            //  修改街道信息
//Route::any('workorder/updateStreetStatus', ['f_auth' => 'admin.workOrder.area', 'uses' => 'WorkOrderController@updateStreetStatus']);                //  修改区域状态
Route::any('workorder/delStreetId/{id}', ['f_auth' => 'admin.workOrder.area', 'uses' => 'WorkOrderController@delStreetId']);                         //  删除街道信息
Route::any('workorder/feedbackTypeList', ['f_auth' => 'admin.workOrder.serviceType', 'uses' => 'WorkOrderController@feedbackTypeList']);             //  问题类型列表
Route::any('workorder/addFeedback', ['f_auth' => 'admin.workOrder.serviceType', 'uses' => 'WorkOrderController@addFeedback']);                       //  添加问题类型
Route::any('workorder/updateFeedback', ['f_auth' => 'admin.workOrder.serviceType', 'uses' => 'WorkOrderController@updateFeedback']);                 //  添加问题类型
Route::any('workorder/delFeedback/{id}', ['f_auth' => 'admin.workOrder.serviceType', 'uses' => 'WorkOrderController@delFeedback']);                  //  删除问题类型
Route::any('workorder/getDataList', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@getDataList']);                              //  工单列表
Route::any('workorder/orderToExcel', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@orderToExcel']);                            //  工单导出
Route::any('workorder/orderInfo/{id}', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@orderInfo']);                             //  工单详情
Route::any('workorder/changeOrderAdminList', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@changeOrderAdminList']);            //  转单人员列表
Route::any('workorder/changeOrderList', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@changeOrderList']);                      //  转单列表
Route::any('workorder/changeOrderInfo/{id}', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@changeOrderInfo']);                 //  转单详情
Route::any('workorder/changeOrderVerify', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@changeOrderVerify']);                  //  转单审核
Route::any('workorder/delWorkOrder/{id}', ['f_auth' => 'admin.workOrder.list', 'uses' => 'WorkOrderController@delWorkOrder']);                       //  工单删除
Route::any('workorder/workOrderStatistics', ['f_auth' => 'admin.workOrder.statistics', 'uses' => 'WorkOrderController@workOrderStatistics']);        //  工单统计
Route::any('workorder/workOrderSelect', ['f_auth' => 'admin.workOrder.statistics', 'uses' => 'WorkOrderController@workOrderSelect']);                //  工单查询统计
Route::any('workorder/workOrderSelectToExcel', ['f_auth' => 'admin.workOrder.statistics', 'uses' => 'WorkOrderController@workOrderSelectToExcel']);  //  工单查询统计导出

//服务热线
Route::any('hotline/list', ['f_auth' => 'admin.index.index', 'uses' => 'BrowsingController@hotlineList']);                               //服务热线列表
Route::any('hotline/addHotline', ['f_auth' => 'admin.index.index', 'uses' => 'BrowsingController@addHotline']);                          //添加服务热线
Route::any('hotline/updateHotline', ['f_auth' => 'admin.index.index', 'uses' => 'BrowsingController@updateHotline']);                   //修改服务热线
Route::any('hotline/delHotline', ['f_auth' => 'admin.index.index', 'uses' => 'BrowsingController@delHotline']);                          //删除服务热线
Route::any('hotline/updateHotlineStatus', ['f_auth' => 'admin.index.index', 'uses' => 'BrowsingController@updateHotlineStatus']);      //修改服务热线状态

//CRM版本
Route::any('sysupdate/list', ['f_auth' => 'admin.sysupdate.index', 'uses' => 'SysUpdateController@getDataList']);       //crm版本更新记录

//站点信息
Route::any('configs/index', ['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@index']);
Route::any('configs/other', ['f_auth' => 'admin.configs.index', 'uses' => 'CommonController@other']);

//公告
Route::any('notice/list',['f_auth' => 'admin.article.index', 'uses' => 'ArticleController@getListContent']);            //公告

//相关链接
Route::any('lookarticles/list',['f_auth' => 'admin.index.index', 'uses' =>'BrowsingController@getDataList']);            //加载新闻列表
Route::any('lookarticles/view/{id}', ['f_auth' => 'admin.index.index', 'uses' =>'BrowsingController@view']);             //查看新闻详细

Route::any('lookarticles/typeList', ['f_auth' => 'admin.lookarticles.index', 'uses' =>'BrowsingController@type_list']);              //  新闻类型列表
Route::any('lookarticles/addType', ['f_auth' => 'admin.lookarticles.create', 'uses' =>'BrowsingController@add_type']);               //  添加新闻类型
Route::any('lookarticles/updateType', ['f_auth' => 'admin.lookarticles.edit', 'uses' =>'BrowsingController@update_type']);           //  修改新闻类型
Route::any('lookarticles/delType/{id}', ['f_auth' => 'admin.lookarticles.destroy', 'uses' =>'BrowsingController@del_type']);         //  删除新闻类型
Route::any('lookarticles/updateSort', ['f_auth' => 'admin.lookarticles.edit', 'uses' =>'BrowsingController@update_sort']);           //  修改权重
Route::any('lookarticles/updateStatus', ['f_auth' => 'admin.lookarticles.edit', 'uses' =>'BrowsingController@update_status']);       //  修改状态
Route::any('lookarticles/allTypeList', ['f_auth' => 'admin.lookarticles.edit', 'uses' =>'BrowsingController@typeList']);             //  类型列表

Route::any('lookhelp/index',['f_auth' => 'admin.help.index', 'uses' =>'BrowsingController@help']);                               //  使用帮助
Route::any('lookabout/index',['f_auth' => 'admin.about.index', 'uses' =>'BrowsingController@about']);                            //  关于我们
Route::any('looklog/list',['f_auth' => 'admin.log.index', 'uses' =>'BrowsingController@log']);                                   //  更新日志

//站点设置
Route::any('siteconfig/update', ['f_auth' => 'admin.siteconfig.index', 'uses' => 'SiteconfigController@update']);                //  更新站点信息
Route::any('siteconfig/list', ['f_auth' => 'admin.siteconfig.index', 'uses' => 'SiteconfigController@getDataList']);             //  站点设置
Route::any('siteconfig/wxPay', ['f_auth' => 'admin.siteconfig.index', 'uses' => 'SiteconfigController@wx_pay_config']);          //  支付配置
Route::any('siteconfig/updateWxPay', ['f_auth' => 'admin.siteconfig.index', 'uses' => 'SiteconfigController@update_wx_pay']);    //  更新支付配置
Route::any('common/getTitle', ['uses' => 'CommonController@getTitle']);                                                          //  获取站点名称

//腾讯云
Route::any('tencent/list',['f_auth' => 'admin.tencent.index', 'uses' => 'TencentController@index']);                                               //腾讯云业务明细
Route::any('tencent/customerExamine',['f_auth' => 'admin.tencent.customer', 'uses' => 'TencentController@customer_examine']);                      //腾讯云已审核客户
Route::any('tencent/customerBeAudited',['f_auth' => 'admin.tencent.customer', 'uses' => 'TencentController@customer_be_audited']);                 //腾讯云待审核客户
Route::any('tencent/examineCustomer',['f_auth' => 'admin.tencent.index', 'uses' => 'TencentController@examine_customer']);                         //腾讯云审核客户
Route::any('tencent/updateCustomerRemarks',['f_auth' => 'admin.tencent.index', 'uses' => 'TencentController@update_customer_remarks']);            //腾讯云修改客户备注
Route::any('tencent/selectBalance',['f_auth' => 'admin.tencent.index', 'uses' => 'TencentController@select_balance']);                             //腾讯云查询客户余额
Route::any('tencent/transferAccountsCustomer',['f_auth' => 'admin.tencent.index', 'uses' => 'TencentController@transfer_accounts_customer']);      //腾讯云给客户转账
Route::any('tencent/substitutePayment',['f_auth' => 'admin.tencent.customer', 'uses' => 'TencentController@substitute_payment']);                  //腾讯云代付
Route::any('tencent/agentDiscount',['f_auth' => 'admin.tencent.index', 'uses' => 'TencentController@agent_discount']);                             //腾讯云代理商返佣信息
Route::any('tencent/selectCustomerOrders',['f_auth' => 'admin.tencent.index', 'uses' => 'TencentController@select_customer_orders']);              //腾讯云查询代客订单
Route::any('tencent/tencentConfig',['f_auth' => 'admin.tencent.config', 'uses' => 'TencentController@tencent_config']);                            //腾讯云配置
Route::any('tencent/updateTencentConfig',['f_auth' => 'admin.tencent.config', 'uses' => 'TencentController@update_tencent_config']);               //修改腾讯云配置
Route::any('tencent/cacheTencentOrder',['f_auth' => 'admin.tencent.order', 'uses' => 'CommonController@cache_tencent_order']);                     //缓存腾讯云订单
Route::any('tencent/getTencentOrders',['f_auth' => 'admin.tencent.order', 'uses' => 'TencentController@get_tencent_orders']);                      //获取腾讯云订单
Route::any('tencent/updateDiscount', ['f_auth' => 'admin.tencent.power', 'uses' => 'TencentController@update_discount']);                          //修改客户代付折扣
Route::any('tencent/tencentStatus',['f_auth' => 'admin.tencent.power', 'uses' => 'TencentController@tencent_status']);                             //修改客户代付权限

Route::any('Management/plug-in',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@get_function_menut']);                                //插件列表
Route::any('update/plug-in/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@update_function_menut']);                            //插件列表
Route::any('cus/functionList',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@cusFunction']);                                         //权限功能列表
Route::any('cus/functionInfo/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@cusFunctionInfo']);                                //权限功能详情
Route::any('cus/updateFunction',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@updateFunction']);                                    //修改权限功能
Route::any('cus/delFunction/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@delFunction']);                                     //修改权限功能
Route::any('cus/addFunction',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@addFunction']);                                          //添加权限功能
Route::any('cus/switchFunction',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@switchFunction']);                                    //开关权限功能
Route::any('cus/updateSever',['f_auth' => 'admin.index.index', 'uses' => 'ConfigsController@updateSever']);                                          //系统版本切换

//问卷调查
Route::any('ask/getFindingsInfo/{id}',['f_auth' => 'admin.index.index', 'uses' => 'QuestionnaireController@get_findings_info']);                     //调查详情
Route::any('ask/getFindingsList',['f_auth' => 'admin.index.index', 'uses' => 'QuestionnaireController@get_findings_list']);                          //问卷调查列表
Route::any('ask/statisticalData',['f_auth' => 'admin.index.index', 'uses' => 'QuestionnaireController@statistical_data']);                           //问卷统计
Route::any('ask/getFindingsList',['f_auth' => 'admin.index.index', 'uses' => 'QuestionnaireController@get_findings_list']);                          //答卷列表
Route::any('ask/getDataList',['f_auth' => 'admin.index.index', 'uses' => 'QuestionnaireController@get_questionnaire_list']);                         //问卷列表
Route::any('ask/answerList',['f_auth' => 'admin.index.index', 'uses' => 'QuestionnaireController@answer_list']);                                     //问答列表
Route::any('ask/findingsToLoad/{id}',['f_auth' => 'admin.index.index', 'uses' => 'QuestionnaireController@findings_to_excel']);                      //答卷详情导出
Route::any('ask/compareToExcel/{id}',['f_auth' => 'admin.index.index', 'uses' => 'QuestionnaireController@compare_to_excel']);                       //统计详情导出

//活动管理
Route::any('activity/TypeAjax',['f_auth' => 'admin.activity.typeList', 'uses' => 'ActivityController@activity_type_ajax']);                            //活动类型增删改
Route::any('activity/getTypeList',['f_auth' => 'admin.activity.typeList', 'uses' => 'ActivityController@get_activity_type_list']);                     //活动类型列表
Route::any('activity/addActivity',['f_auth' => 'admin.activity.create', 'uses' => 'ActivityController@add_activity']);                                 //添加活动
Route::any('activity/getActivityList',['f_auth' => 'admin.activity.list', 'uses' => 'ActivityController@get_activity_list']);                          //活动列表
Route::any('activity/getActivityInfo/{id}',['f_auth' => 'admin.activity.list', 'uses' => 'ActivityController@get_activity_info']);                     //活动详情
Route::any('activity/updateActivity',['f_auth' => 'admin.activity.modify', 'uses' => 'ActivityController@update_activity']);                           //活动修改
Route::any('activity/activityDel/{id}',['f_auth' => 'admin.activity.delete', 'uses' => 'ActivityController@activity_del']);                            //删除活动
Route::any('activity/cancelActivity/{id}',['f_auth' => 'admin.activity.cancel', 'uses' => 'ActivityController@cancel_activity']);                      //取消活动
Route::any('activity/entryList/{id}',['f_auth' => 'admin.activity.reviewMember', 'uses' => 'ActivityController@entry_list']);                          //报名列表
Route::any('activity/toExcel',['f_auth' => 'admin.activity.exportJoinMenber', 'uses' => 'ActivityController@to_excel']);                               //导出Excel表
Route::any('activity/verifyRefuse',['f_auth' => 'admin.activity.reviewMember', 'uses' => 'ActivityController@agree_refuse']);                          //审核报名人员
Route::any('activity/commentList',['f_auth' => 'admin.activity.reviewComment', 'uses' => 'ActivityController@comment_list']);                          //评价列表
Route::any('activity/checkComment',['f_auth' => 'admin.activity.reviewComment', 'uses' => 'ActivityController@check_comment']);                        //评价审核
Route::any('activity/delComment/{id}',['f_auth' => 'admin.activity.reviewComment', 'uses' => 'ActivityController@del_comment']);                       //删除评价
Route::any('activity/proposalList',['f_auth' => 'admin.activity.comment', 'uses' => 'ActivityController@proposal_list']);                              //留言列表
Route::any('activity/proposalInfo/{id}',['f_auth' => 'admin.activity.comment', 'uses' => 'ActivityController@proposal_info']);                         //留言详情
Route::any('activity/delProposal/{id}',['f_auth' => 'admin.activity.comment', 'uses' => 'ActivityController@proposal_del']);                           //留言删除
Route::any('activity/activityAllList',['f_auth' => 'admin.activity.list', 'uses' => 'ActivityController@activity_all_list']);                          //活动列表无分页

//会员管理
Route::any('vip/typeList',['f_auth' => 'admin.memberManage.type', 'uses' => 'MemberVipController@getInidustryList']);                               //类型列表
Route::any('vip/addType',['f_auth' => 'admin.memberManage.type', 'uses' => 'MemberVipController@addIndustryType']);                                 //添加类型
Route::any('vip/updateType',['f_auth' => 'admin.memberManage.type', 'uses' => 'MemberVipController@updateInidustry']);                              //类型修改
Route::any('vip/delType/{id}',['f_auth' => 'admin.memberManage.type', 'uses' => 'MemberVipController@delInidustryType']);                           //删除类型

Route::any('vip/enterpriseList',['f_auth' => 'admin.memberManage.list', 'uses' => 'MemberVipController@enterpriseList']);                           //公司列表
Route::any('vip/list',['f_auth' => 'admin.memberManage.list', 'uses' => 'MemberVipController@getDataList']);                                        //会员列表
Route::any('vip/info/{id}',['f_auth' => 'admin.memberManage.details', 'uses' => 'MemberVipController@getVipInfo']);                                 //会员详情
Route::any('vip/addVip',['f_auth' => 'amdin.memberManage.create', 'uses' => 'MemberVipController@addMemberVip']);                                   //添加会员
Route::any('vip/updateVip/{id}',['f_auth' => 'amdin.memberManage.edit', 'uses' => 'MemberVipController@updateMemberVip']);                          //会员修改
Route::any('vip/delMemberVip/{id}',['f_auth' => 'admin.memberManage.delete', 'uses' => 'MemberVipController@delMemberVip']);                        //会员删除
Route::any('vip/toExcel',['f_auth' => 'admin.memberManage.import', 'uses' => 'MemberVipController@toExcel']);                                       //导入模本
Route::any('vip/excleInsert',['f_auth' => 'admin.memberManage.import', 'uses' => 'MemberVipController@excleInsert']);                               //批量导入
Route::any('vip/regionList',['f_auth' => 'admin.index.index', 'uses' => 'MemberVipController@regionList']);                                         //地区列表


Route::any('vip/authenticationList',['f_auth' => 'admin.index.index', 'uses' => 'MemberVipController@authenticationList']);                     //认证列表
Route::any('vip/memberVipInfo/{id}',['f_auth' => 'admin.index.index', 'uses' => 'MemberVipController@memberVipInfo']);                          //认证列表
Route::any('vip/examineMemberVip',['f_auth' => 'admin.index.index', 'uses' => 'MemberVipController@examineMemberVip']);                         //审核认证
Route::any('vip/delInfo',['f_auth' => 'admin.index.index', 'uses' => 'MemberVipController@delInfo']);                                           //审核认证

//小程序首页设置
Route::any('plugin/list',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@index']);                                              //插件信息列表
Route::any('plugin/updatePlugin',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateInfo']);                                 //修改插件信息
Route::any('plugin/modularList',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@modularList']);                                 //模块信息列表
Route::any('plugin/updateModular',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateModular']);                             //修改模块信息
Route::any('plugin/getPlugUnitOrder',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@getPlugUnitOrder']);                       //插件排序列表
Route::any('plugin/getModularOrder',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@getModularOrder']);                         //模块排序列表
Route::any('plugin/getWxapplet',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@getWxapplet']);                                 //小程序名称和色调
Route::any('plugin/updateWxapplet',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateWxapplet']);                           //修改小程序名称和色调
Route::any('plugin/updateModularOrder',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateModularOrder']);                   //修改模块位置
Route::any('plugin/updatePlugUnitOrder',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updatePlugUnitOrder']);                 //修改插件位置

Route::any('navigation/list',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@navigationList']);                                  //底部导航栏列表
Route::any('navigation/add',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@addNavigationInfo']);                                //添加底部导航栏
Route::any('navigation/update',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateNavigationInfo']);                          //修改底部导航栏
Route::any('navigation/updateStatus',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateNavigationStatus']);                  //修改底部导航栏状态
Route::any('navigation/del',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@delNavigationId']);                                  //删除底部导航栏
Route::any('navigation/move',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateNavigationOrder']);                           //移动底部导航栏
Route::any('navigation/pagePath',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@getWeAppPagePath']);                            //小程序页面路径

Route::any('wechatConfigs/info',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@getAgentWechatConfigs']);            //  客户小程序配置
Route::any('wechatConfigs/update',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateAgentWechatConfigs']);       //  客户小程序配置

//轮播图管理
Route::any('banner/list',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@getBannerList']);                        //轮播图列表
Route::any('banner/add',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@addBanner']);                             //添加轮播图
Route::any('banner/getInfo/{id}',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@getBannerList']);                //轮播图详情
Route::any('banner/update',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@updateBanner']);                       //轮播图修改
Route::any('banner/delId/{id}',['f_auth' => 'admin.index.index', 'uses' => 'PlugInController@delBannerId']);                    //轮播图删除

//考试管理
Route::any('exam/typeList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examTypeList']);                         //试卷类型列表
Route::any('exam/addType',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@addExamType']);                           //添加试卷类型
Route::any('exam/uptadeType',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@updateExamType']);                     //修改试卷类型
Route::any('exam/delType/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@delExamType']);                      //删除试卷类型
Route::any('exam/testPaperList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@testPaperList']);                   //试卷列表
Route::any('exam/testPaperSubjectList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@testPaperSubjectList']);     //试卷题目列表
Route::any('exam/addTestPaper',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@addTestPaper']);                     //添加试卷
Route::any('exam/updateTestPaper',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@updateTestPaper']);               //修改试卷
Route::any('exam/delTestPaper/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@delTestPaper']);                //删除试卷
Route::any('exam/testAddSubject',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@testAddSubject']);                 //添加试卷题目
Route::any('exam/itemAddTest',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@itemAddTest']);                       //题库添加试卷题目
Route::any('exam/updateTestItem',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@updateTestItem']);                 //修改试卷题目
Route::any('exam/delTestItem',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@delTestItem']);                       //删除试卷题目
Route::any('exam/testPaperToExcel',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@testPaperToExcel']);             //试卷导出
Route::any('exam/batchDelId',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@batchDelId']);                         //试卷批量删除
Route::any('exam/testPaperNoPage',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@testPaperNoPage']);               //试卷列表无分页
Route::any('exam/downloadExcelDemo',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@downloadExcelDemo']);           //试卷导入模板
Route::any('exam/batchUploadItem',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@batchUploadItem']);               //上传试卷题目
Route::any('exam/itemInfo/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@itemInfo']);                        //试卷题目详情
Route::any('exam/itmeDel/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@itmeDel']);                          //题库删除试题
Route::any('exam/itemBatchDel',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@itemBatchDel']);                     //题库批量删除
Route::any('exam/updateItem',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@updateItemBatch']);                    //题库修改题目
Route::any('exam/addItemBatch',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@addItemBatch']);                     //题库修改题目
Route::any('exam/itemBatchUpload',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@itemBatchUpload']);               //题库批量上传
Route::any('exam/itemBankList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@itemBankList']);                     //题库列表
Route::any('exam/examList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examList']);                             //考试列表
Route::any('exam/stopTheExam/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@stopTheExam']);                  //考试列表
Route::any('exam/itemListNoPage',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@itemListNoPage']);                 //考试列表无分页
Route::any('exam/addExamData',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@addExamData']);                       //创建考试
Route::any('exam/examInfo/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examInfo']);                        //考试详情
Route::any('exam/examResultAnalyse/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examResultAnalyse']);      //考试结果分析
Route::any('exam/examResultList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examResultList']);                 //考试答卷列表
Route::any('exam/examResultList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examResultList']);                 //考试答卷列表
Route::any('exam/examineeInfo',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examineeInfo']);                     //考生详情
Route::any('exam/answerInfo',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@answerInfo']);                         //答卷详情
Route::any('exam/examineeList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examineeList']);                     //考生列表
Route::any('exam/examineeGroup',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examineeGroup']);                   //考生分组
Route::any('exam/batchDelExaminee',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@batchDelExaminee']);             //删除（批量删除）分组考生
Route::any('exam/addExamineeGroup',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@addExamineeGroup']);             //创建分组
Route::any('exam/examineeGroupList',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examineeGroupList']);           //分组无分页列表
Route::any('exam/delExamineeGroup/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@delExamineeGroup']);        //删除分组
Route::any('exam/updateExamineeGroup',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@updateExamineeGroup']);       //修改分组

Route::any('exam/examCompute',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examCompute']);                           //考试统计
Route::any('exam/examAnalyse',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examAnalyse']);                           //考试题目分析
Route::any('exam/examDel/{id}',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examDel']);                              //删除考试
Route::any('exam/userExamToExcel',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@userExamToExcel']);                   //答卷导出
Route::any('exam/examAnalyseToExcel',['f_auth' => 'admin.index.index', 'uses' => 'ExamController@examAnalyseToExcel']);             //考试分析导出

//更新管理员信息
Route::post('updateuser/ajax/{id}', 'UserController@ajax');

