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
/** 腾讯云接口 */
Route::any('tencent/test', 'Tencent\CommonController@test');                                               // ceshi1
Route::any('tencent/register', 'Tencent\CommonController@register');                                               // 新用户注册
Route::any('tencent/sendingsms', 'Tencent\CommonController@sendingsms');                                          // 发送短信
Route::any('tencent/checkverification', 'Tencent\CommonController@check_verification');                          // 验证短信验证码
Route::any('tencent/getMember', 'Tencent\CommonController@getMember');                                             // 获取openid
Route::any('tencent/bindMemberAccount', 'Tencent\CommonController@bindAccount');                                  // 客户绑定
Route::any('tencent/getAbout', 'Tencent\CommonController@getAbout');                                               // 关于我们
Route::any('tencent/getNewsList', 'Tencent\CommonController@getNewsList');                                        // 新闻列表
Route::any('tencent/xiaoNotifyUrl', 'Tencent\CommonController@xiao_notify_url');                                  // 支付回调
Route::any('tencent/notLoginPassUpdate', 'Tencent\CommonController@not_login_update_passwoed');                 // 未登录修改密码

Route::any('tencent/pay', 'Tencent\MemberController@pay_order_api');                                               // 客户充值
Route::any('tencent/memberRecharge', 'Tencent\MemberController@recharge');                                         // 客户充值
Route::any('tencent/transactionDetails', 'Tencent\MemberController@transactionDetails');                         // 交易明细
Route::any('tencent/memberOrder', 'Tencent\OrderController@memberOrder');                                         // 客户订单
Route::any('tencent/updatename', 'Tencent\MemberController@update_name');                                         // 修改名称
Route::any('tencent/updatemobile', 'Tencent\MemberController@update_mobile');                                     // 修改电话
Route::any('tencent/passUpdate', 'Tencent\MemberController@passUpdate');                                          // 修改密码
Route::any('tencent/selectorder', 'Tencent\MemberController@selectorder');                                        // 查询腾讯云订单
Route::any('tencent/payOrder', 'Tencent\MemberController@pay_order');                                              // 腾讯云订单支付
Route::any('tencent/memberDetail', 'Tencent\MemberController@memberDetail');                                      // 客户详情


Route::any('tencent/getAbout', 'Tencent\CommonController@getAbout');                                               // 获取客户

Route::any('tencent/getAccountList', 'Tencent\CommonController@getMemberList');                                   // 获取绑定的账号列表
Route::any('tencent/getAccountDel', 'Tencent\CommonController@getMemberDelete');                                  // 获取绑定的账号列表

