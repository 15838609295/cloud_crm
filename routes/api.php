<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::any('crm', 'Api\CrmController@index');
Route::post('customer/index', 'Api\IndexController@customer');
Route::get('index/captcha/{tmp}', 'RegisterController@captcha');

Route::post('crm/createCustomer', 'Api\CustomerController@createCustomer');                                              // 创建客户
Route::any('crm/getSources', 'Api\SourcesController@getSources');                                                        // 获取客户来源
Route::any('crm/getProjects', 'Api\SourcesController@getProjects');                                                      // 请求项目列表
Route::post('crm/setUsers', 'Api\CustomerController@setUsers');                                                          // 绑定用户信息
Route::post('crm/getUsers', 'Api\CustomerController@getUsers');                                                          // 获取管理员信息
Route::post('crm/getUsersInfo', 'Api\CustomerController@getUsersInfo');                                                  // 根据openid获取管理员信息
Route::post('crm/test', 'Api\CustomerController@test');                                                                  // 根据openid获取管理员信息
Route::any('crm/getGlobalData', 'Api\AchievementController@getGlobalData');                                              // 获取首页各种数据
Route::post('crm/getRankingList', 'Api\CommonController@getRankingList');                                                // 获取业绩排行榜
Route::post('crm/getTodayCustomer', 'Api\CustomerController@getTodayCustomer');                                          // 获取今日分配客户和逾期客户总数
Route::post('crm/takeMoney', 'Api\TakeMoneyController@takeMoney');                                                       // 提现申请
Route::post('crm/getNews', 'Api\CommonController@getNews');                                                              // 获取最新新闻
Route::post('crm/getBonusList', 'Api\TakeMoneyController@getBonusList');                                                 // 获取提成记录
Route::post('crm/getTakeMoneyList', 'Api\TakeMoneyController@getTakeMoneyList');                                         // 获取提成记录
Route::post('crm/getCustomer', 'Api\CustomerController@getCustomer');                                                    // 获取客户信息
Route::post('crm/getCommunicateLog', 'Api\SourcesController@getCommunicateLog');                                         // 获取客户沟通记录
Route::post('crm/updateCustomer', 'Api\CustomerController@updateCustomer');                                              // 修改客户信息
Route::post('crm/getCustomerList', 'Api\CustomerController@getCustomerList');                                            // 获取客户列表
Route::post('crm/getWorkStatus', 'Api\CommonController@getWorkStatus');                                                  // 获取工作状态
Route::post('crm/updateWorkStatus', 'Api\CommonController@updateWorkStatus');                                            // 获取工作状态
Route::post('crm/getUsers', 'Api\CustomerController@getUsers');                                                          // 获取用户信息
Route::post('crm/getBranchs', 'Api\CommonController@getBranchs');                                                        // 获取用户信息
Route::post('crm/createCommLog', 'Api\SourcesController@createCommLog');                                                 // 添加沟通记录
Route::post('crm/updateUsers', 'Api\TakeMoneyController@updateUsers');                                                   // 修改管理员信息
Route::post('crm/getAbout', 'Api\CommonController@getAbout');                                                            // 获取关于我们
Route::post('crm/upload', 'Api\CommonController@UploadPicture');                                                         // 上传图片

Route::post('crm/getWxconfig', 'Api\CommonController@getWxconfig');                                                      // 小程序名称和主题色
Route::post('crm/homePage', 'Api\CommonController@homePage');                                                            // 小程序首页
Route::post('crm/getFormId', 'Api\CommonController@get_form_id');                                                        // 小程序首页

Route::post('crm/unbind', 'Api\CommonController@unbind');                                                                // 解除绑定

/*考试*/
Route::any('crm/examList', 'Api\ExanController@examList');                                                               // 考试列表
Route::any('crm/getExamInfo', 'Api\ExanController@getExamInfo');                                                         // 考试信息
Route::any('crm/getQuestionsList', 'Api\ExanController@getQuestionsList');                                               // 考试题目列表
Route::any('crm/examStartResult', 'Api\ExanController@examStartResult');                                                 // 考试前提交创建答卷
Route::any('crm/examEndResult', 'Api\ExanController@examEndResult');                                                     // 考试结束提交
Route::any('crm/preSubmit', 'Api\ExanController@preSubmit');                                                             // 预提交
Route::any('crm/rankingList', 'Api\ExanController@rankingList');                                                         // 排名
Route::any('crm/getExamNumber', 'Api\ExanController@getExamNumber');                                                     // 考试剩余次数
Route::any('crm/examExplain', 'Api\ExanController@examExplain');                                                         // 考试题目解析
Route::any('crm/examResult', 'Api\ExanController@examResult');                                                           // 考试结果

Route::post('work/adminFeedbackList', 'Api\CustomerController@adminFeedbackList');                                       // 管理员-我的工单
Route::post('work/changeOrderLog', 'Api\CustomerController@changeOrderLog');                                             // 管理员-转单记录
Route::post('work/feedbackInfo', 'Api\CustomerController@feedbackInfo');                                                 // 管理员-工单详情
Route::post('work/acceptWorkOrder', 'Api\CustomerController@acceptWorkOrder');                                           // 管理员-签收工单
Route::post('work/transferWorkOrder', 'Api\CustomerController@transferWorkOrder');                                       // 管理员-转单申请
Route::post('work/adminWorkOrderSubmit', 'Api\CustomerController@adminWorkOrderSubmit');                                 // 管理员-提交反馈
Route::post('work/openStatus', 'Api\CustomerController@openStatus');                                                     // 管理员-在线
Route::post('work/closeStatus', 'Api\CustomerController@closeStatus');                                                   // 管理员-下线



/**  代理商接口 */
Route::post('crm/getMember', 'Api\Member\CommonController@getMember');                                                    // 获取客户
Route::post('crm/bindMemberAccount', 'Api\Member\CommonController@bindAccount');                                          // 客户绑定
Route::post('crm/goodsList', 'Api\Member\GoodsController@goodsList');                                                     // 商品列表
Route::post('crm/typeList', 'Api\Member\GoodsController@typeList');                                                       // 商品类型列表
Route::post('crm/memberOrder', 'Api\Member\OrderController@memberOrder');                                                 // 客户订单
Route::post('crm/memberOrderDetail', 'Api\Member\OrderController@memberOrderDetail');                                     // 客户订单详情
Route::post('crm/memberWalletRecord', 'Api\Member\MemberController@memberWalletRecord');                                  // 客户账单记录
Route::post('crm/workOrderSubmit', 'Api\Member\MemberController@workOrderSubmit');                                        // 提交工单
Route::post('crm/getNewsList', 'Api\Member\CommonController@getNewsList');                                                // 新闻列表
Route::post('crm/goodsDetail', 'Api\Member\GoodsController@goodsDetail');                                                 // 商品详情
Route::post('crm/submitOrder', 'Api\Member\OrderController@submitOrder');                                                 // 支付订单
Route::post('crm/memberDetail', 'Api\Member\MemberController@memberDetail');                                              // 客户详情
Route::post('crm/getAbout', 'Api\CommonController@getAbout');                                                             // 获取客户
Route::post('crm/orderAction', 'Api\Member\OrderController@orderAction');                                                 // 取消订单/继续付款/申请退款/删除订单
Route::post('crm/passUpdate', 'Api\Member\MemberController@passUpdate');                                                  // 修改密码
Route::post('crm/getAccountList', 'Api\Member\CommonController@getMemberList');                                           // 获取绑定的账号列表
Route::post('crm/getAccountDel', 'Api\Member\CommonController@getMemberDelete');                                          // 获取绑定的账号列表
Route::post('crm/workOrderList', 'Api\Member\MemberController@workOrderList');                                            // 客户工单
Route::post('crm/getVersion', 'Api\Member\CommonController@getVersion');                                                  // 版本号