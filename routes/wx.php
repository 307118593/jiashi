<?php

use Illuminate\Http\Request;

// Route::any('test',function(){
// 	$res = curlGet('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx1b9d074b65169f5d&secret=36c217cf88bf598670b3b54d9eff4e4c');
// 	return $res;
// });
//公众号客户端登陆
Route::any('jssdk','WeixinController@jssdk');
Route::any('server','ServerController@index');
Route::any('oauth','WeixinController@oauth');
Route::any('oauth_back','WeixinController@oauth_back');
Route::any('wxMpBindPhone','WeixinController@wxMpBindPhone');
Route::any('Com_BindCount','WeixinController@Com_BindCount');
//ComMPIdnex
Route::any('ComMPIdnex','WeixinController@ComMPIdnex');


//公众号公司段登陆
Route::any('Com_oauth','WeixinController@Com_oauth');
Route::any('Com_oauth_back','WeixinController@Com_oauth_back');
// Route::group(['middleware' => ['web', 'wechat.oauth']], function () {
//     Route::get('/user', function () {
//             header('location:http://www.homeeyes.cn');
//         // $user = session('wechat.oauth_user.default'); // 拿到授权用户资料

//         // dd($user);
//     });
// });
