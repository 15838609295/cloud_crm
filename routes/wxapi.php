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

Route::any('test', 'Wxapi\CommonController@test');
//保存用户
Route::any('get/formId', 'Wxapi\CommonController@get_form_id');

//小程序首页信息
Route::any('home/page', 'Wxapi\CommonController@homePage');                                                         // 首页页面排版
Route::any('home/config', 'Wxapi\CommonController@getWxconfig');                                                    // 小程序名称与配色

Route::any('home/companyInfo', 'Wxapi\CommonController@companyInfo');                                               // 公司简介
/*新闻*/
Route::any('news/getTypeList', 'Wxapi\NewsController@getTypeList');                                                 // 新闻类型
Route::any('news/getTypeNewsList', 'Wxapi\NewsController@getTypeNewsList');                                         // 类型下的新闻列表
Route::any('news/TypeNewsList', 'Wxapi\NewsController@TypeNewsList');                                               // 类型下的新闻列表
Route::any('news/newsInfo', 'Wxapi\NewsController@newsInfo');                                                       // 新闻详情
Route::any('news/videoCenter', 'Wxapi\NewsController@videoCenter');                                                 // 视频列表


/** 腾讯云接口 */
Route::any('tencent/selectorder', 'Wxapi\MemberController@selectorder');                                            // 查询腾讯云订单
Route::any('tencent/payOrder', 'Wxapi\MemberController@pay_order');                                                 // 腾讯云订单支付

/*会员*/
Route::any('vip/album', 'Wxapi\MailListController@vipAlbum');                                                       // 相册列表
Route::any('vip/list', 'Wxapi\MailListController@getMemberVipList');                                                // 会员列表
Route::any('vip/info', 'Wxapi\MailListController@getVipInfo');                                                      // 会员详情

//服务热线
Route::any('hotline/list', 'Wxapi\CommonController@hotlineList');                                                  //服务热线

/*用户中心*/
Route::any('user/transactionDetails', 'Wxapi\MemberController@transactionDetails');                                 // 交易明细
Route::any('user/memberOrder', 'Wxapi\OrderController@memberOrder');                                                // 客户订单
Route::any('user/updatename', 'Wxapi\MemberController@update_name');                                                // 修改名称
Route::any('user/updatemobile', 'Wxapi\MemberController@update_mobile');                                            // 修改电话
Route::any('user/passUpdate', 'Wxapi\MemberController@passUpdate');                                                 // 修改密码
Route::any('user/memberDetail', 'Wxapi\MemberController@memberDetail');                                             // 客户详情
Route::any('user/getNewsList', 'Wxapi\CommonController@getNewsList');                                               // 使用帮助
Route::any('user/getAbout', 'Wxapi\CommonController@getAbout');                                                     // 关于我们
Route::any('user/unbind', 'Wxapi\CommonController@unbind');                                                         // 解除绑定

Route::any('user/memberVipAlbum', 'Wxapi\MemberController@memberVipAlbum');                                         // 相册列表
Route::any('user/addAlbum', 'Wxapi\MemberController@memberAddAlbum');                                               // 上传相册
Route::any('user/delAlbum', 'Wxapi\MemberController@delAlbum');                                                     // 删除图片
Route::any('user/collectList', 'Wxapi\MemberController@collectList');                                               // 收藏列表
Route::any('user/fabulousList', 'Wxapi\MemberController@fabulousList');                                             // 点赞列表
Route::any('user/commentList', 'Wxapi\MemberController@commentList');                                               // 评论列表
Route::any('user/myActivity', 'Wxapi\MemberController@myActivity');                                                 // 活动管理
Route::any('user/myActivityInfo', 'Wxapi\MemberController@myActivityInfo');                                         // 活动管理详情
Route::any('user/nidustryList', 'Wxapi\MemberController@getInidustryList');                                         // 会员类型列表
Route::any('user/authentication', 'Wxapi\MemberController@authentication');                                         // 提交认证
Route::any('user/regionList', 'Wxapi\CommonController@region_list');                                                // 省市区列表


/*绑定*/
Route::any('user/getMember', 'Wxapi\CommonController@getMember');                                                   // 获取openid
Route::any('user/bindMemberAccount', 'Wxapi\CommonController@bindAccount');                                         // 客户绑定

Route::any('user/getPhoneNumber', 'Wxapi\CommonController@phoneNumber');                                            // 获取手机号

/*注册*/
Route::any('new/register', 'Wxapi\CommonController@register');                                                      // 新用户注册
Route::any('new/sendingsms', 'Wxapi\CommonController@sendingsms');                                                  // 发送短信
Route::any('new/checkverification', 'Wxapi\CommonController@check_verification');                                   // 验证码验证

Route::any('notLogin/passUpdate', 'Wxapi\CommonController@not_login_update_passwoed');                              // 未登录修改密码

/*充值*/
Route::any('charge/pay', 'Wxapi\MemberController@pay_order_api');                                                   // 客户充值
Route::any('charge/memberRecharge', 'Wxapi\MemberController@recharge');                                             // 客户充值
Route::any('charge/xiaoNotifyUrl', 'Wxapi\CommonController@xiao_notify_url');                                       // 支付回调

/*业务接口*/
Route::post('crm/goodsList', 'Wxapi\GoodsController@goodsList');                                                   // 商品列表
Route::post('crm/typeList', 'Wxapi\GoodsController@typeList');                                                     // 类型列表
Route::post('crm/goodsDetail', 'Wxapi\GoodsController@goodsDetail');                                               // 商品详情
Route::post('crm/memberOrderDetail', 'Wxapi\OrderController@memberOrderDetail');                                   // 详情订单
Route::post('crm/submitOrder', 'Wxapi\OrderController@submitOrder');                                               // 支付订单
Route::post('crm/orderAction', 'Wxapi\OrderController@orderAction');                                               // 取消订单/继续付款/申请退款/删除订单

Route::post('work/getStreetList', 'Wxapi\WorkOrderController@getStreetList');                                     // 街道列表
Route::post('work/feedbackLabel', 'Wxapi\WorkOrderController@feedbackLabel');                                     // 标签列表
Route::post('work/workOrderSubmit', 'Wxapi\WorkOrderController@workOrderSubmit');                                 // 提交工单
Route::post('work/myFeedbackList', 'Wxapi\WorkOrderController@myFeedbackList');                                   // 我的工单
Route::post('work/feedbackInfo', 'Wxapi\WorkOrderController@feedbackInfo');                                       // 工单详情
Route::post('work/supplementWorkOrder', 'Wxapi\WorkOrderController@supplementWorkOrder');                         // 工单补充
Route::post('work/endWorkOrder', 'Wxapi\WorkOrderController@endWorkOrder');                                       // 结束工单
/*问卷调查*/
Route::post('ask/getList', 'Wxapi\QuestionnaireController@get_data_list');                                        // 问卷列表
Route::post('ask/getInfo', 'Wxapi\QuestionnaireController@get_info');                                             // 问卷填写
Route::post('ask/getSubmit', 'Wxapi\QuestionnaireController@get_submit');                                         // 问卷提交


/*活动报名*/
Route::any('activity/NotifyUrl', 'Wxapi\CommonController@activity_notify_url');                                   // 支付回调
Route::any('activity/getList', 'Wxapi\ActivityController@get_activityList');                                      // 活动列表
Route::any('activity/getInfo', 'Wxapi\ActivityController@getInfo');                                               // 活动详情
Route::any('activity/singUp', 'Wxapi\ActivityController@signUp');                                                 // 活动报名
Route::any('activity/memberComment', 'Wxapi\ActivityController@member_comment');                                  // 活动评价
Route::any('activity/commentList', 'Wxapi\ActivityController@comment_list');                                      // 评价列表
Route::any('activity/cancelSign', 'Wxapi\ActivityController@cancel_sign');                                        // 取消报名
Route::any('activity/spotFabulous', 'Wxapi\ActivityController@spotFabulous');                                     // 用户点赞
Route::any('activity/cancelFabulous', 'Wxapi\ActivityController@cancelFabulous');                                 // 用户取消点赞
Route::any('activity/memberCollect', 'Wxapi\ActivityController@memberCollect');                                   // 用户收藏
Route::any('activity/memberCancelCollect', 'Wxapi\ActivityController@memberCancelCollect');                       // 用户取消收藏
Route::any('activity/memberAddProposal', 'Wxapi\ActivityController@memberAddProposal');                           // 用户留言
Route::any('activity/memberProposalList', 'Wxapi\ActivityController@memberProposalList');                         // 用户留言列表
Route::any('activity/memberActivity', 'Wxapi\ActivityController@member_activity');                                // 用户活动状态
Route::any('activity/endActivity', 'Wxapi\ActivityController@endActivity');                                       // 往期活动

/*考试*/
Route::any('exam/examList', 'Wxapi\ExanController@examList');                                                       //  考试列表
Route::any('exam/getExamInfo', 'Wxapi\ExanController@getExamInfo');                                                 //  考试信息
Route::any('exam/getQuestionsList', 'Wxapi\ExanController@getQuestionsList');                                       //  考试题目列表
Route::any('exam/examStartResult', 'Wxapi\ExanController@examStartResult');                                         //  考试前提交创建答卷
Route::any('exam/examEndResult', 'Wxapi\ExanController@examEndResult');                                             //  考试结束提交
Route::any('exam/preSubmit', 'Wxapi\ExanController@preSubmit');                                                     //  预提交
Route::any('exam/rankingList', 'Wxapi\ExanController@rankingList');                                                 //  排名
Route::any('exam/getExamNumber', 'Wxapi\ExanController@getExamNumber');                                             //  考试剩余次数
Route::any('exam/examExplain', 'Wxapi\ExanController@examExplain');                                                 //  考试题目解析
Route::any('exam/examResult', 'Wxapi\ExanController@examResult');                                                   //  考试结果


Route::any('tencent/getAbout', 'Wxapi\CommonController@getAbout');                                                 // 获取客户

Route::any('admin/getUsers', 'Wxapi\CommonController@getUsers');                                                   // 获取员工
Route::any('admin/setUsers', 'Wxapi\CommonController@setUsers');                                                   // 绑定员工

Route::any('tencent/getAccountList', 'Wxapi\CommonController@getMemberList');                                      // 获取绑定的账号列表
Route::any('tencent/getAccountDel', 'Wxapi\CommonController@getMemberDelete');                                     // 获取绑定的账号列表

