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
Route::any('/', function () {
    return redirect('/index.html');
});
Route::any('/admin', function () {
    return redirect('/index.html');
});

//前台用户
Route::any('weblogin', 'Web\LoginController@showLoginForm')->name('login');
Route::any('weblogin', 'Web\LoginController@login');
Route::any('weblogout', 'Web\LoginController@weblogout');
//验证码
Route::any('/index/captcha/{tmp}', 'RegisterController@captcha');

//注册
Route::any('sendCose', 'Web\LoginController@sendCode');                    //发送验证码
Route::any('checkVerification', 'Web\LoginController@checkVerification');  //验证验证码
Route::any('register', 'Web\LoginController@register');                    //注册
Route::any('updatePassword', 'Web\LoginController@updatePassword');        //忘记密码修改

Route::any('/webindex', 'Web\IndexController@index');
//跳转路由
Route::any('admin_web_login/{id}', 'Web\LoginController@ad_web_login');
//上传图片
Route::any('web/ajax/upload', 'Web\FilesController@UploadPicture');
//获取用户基本信息
Route::any('getuserInfo', 'Web\IndexController@GetUserInfo');
//CRM代理端更改 不需要此路由
//Route::group(['middleware' => ['auth:web', 'auth']], function () {
//修改密码
Route::any('user/passupdate', ['uses' => 'Web\UserController@passupdate']);
//修改头像
Route::any('user/picurl', ['uses' => 'Web\UserController@picurl']);
//商品路由
Route::any('goods/index', ['uses' => 'Web\GoodsController@index']);  //商品列表
Route::any('goods/buy/', ['uses' => 'Web\GoodsController@buy']);
Route::any('goods/details/',['uses' => 'Web\GoodsController@details']);   //商品详情

//订单路由
Route::any('order/index', ['uses' => 'Web\OrderController@index']);  //商品列表
Route::any('order/index', ['uses' => 'Web\OrderController@index']);
Route::any('order/buy', ['uses' => 'Web\OrderController@buy']);
Route::any('order/del', ['uses' => 'Web\OrderController@del']);

//提交订单
Route::any('ajax/submit/order', ['uses' => 'Web\OrderController@submitOrder']);

//费用明细列表
Route::any('cost/index', ['uses' => 'Web\CostController@index']);
Route::any('cost/index', ['uses' => 'Web\CostController@index']);

//新闻列表
Route::any('news/index', ['uses' => 'Web\NewsController@news']);
Route::any('news/info/{id}', ['uses' => 'Web\NewsController@newInfo']);
Route::any('help/index', ['uses' => 'Web\NewsController@help']);
Route::any('about/index', ['uses' => 'Web\NewsController@about']);
Route::any('log/index', ['uses' => 'Web\NewsController@log']);
Route::any('log/loadlog', ['uses' => 'Web\NewsController@loadLog']);
Route::any('articles/view/{id}', ['uses' => 'Web\NewsController@view']);

//财务明细
Route::any('wallet/index', ['uses' => 'Web\CostController@wallet']);
Route::any('donation/index', ['uses' => 'Web\CostController@donation']);

//个人中心
Route::any('member/index', ['uses' => 'Web\MemberController@index']);
Route::any('member/update', ['uses' => 'Web\MemberController@updateUserInfo']);

//腾讯云模块
Route::any('tencent/selectorder', ['uses' => 'Web\OrderController@selectorder']);
Route::any('tencent/agentpayment', ['uses' => 'Web\OrderController@agent_payment']);
//});

