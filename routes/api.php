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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::any('test',function(){
	// $predefined = [
	// 	'ticker' => '这是ticker',
	//     'title' => '工地新建通知',
	//     "text"=>'您好,您的工地名称:测试..已经新建完成',   
	//     "after_open" => 'go_app',
	// ];
	// $device_token = DB::table('user')->where('id',35)->value('DeviceToken');
	// $res = sendUnicast($device_token,$predefined);
	// return $res;
	// $camera = DB::table('camera_log')->get();
	// foreach ($camera as $k => $v) {
	// 	$cid = DB::table('camera')->where('mac',$v->mac)->value('cid');
	// 	DB::table('camera_log')->where('id',$v->id)->update(['cid'=>$cid]);
	// }
});
//上传版本
Route::any('update_version','Controller@update_version');
//获取角色标识
Route::any('getRole/{uid}','Controller@getRole');

//获取初始数据
Route::any('getStartSource','Controller@getStartSource');
//关闭app通知接口
Route::any('closeApp','Controller@closeApp');
//前台进入app通知接口
Route::any('openApp','Controller@openApp');

//注册----
//短信
// Route::any('sendtemp','LoginController@sendtemp');
//发送验证码
Route::any('send_mes','LoginController@send_mes');
//注册登陆
Route::any('register','LoginController@register');
//登陆
Route::any('login','LoginController@login');
//登陆1026
Route::any('login1026','LoginController@login1026');
//手机号快捷登陆
Route::any('phone_login','LoginController@phone_login');
//重置密码
Route::any('reset_password','LoginController@reset_password');
//微信登陆
Route::any('wx_login','LoginController@wx_login');
//微信登陆
Route::any('bind_phone','LoginController@bind_phone');
//修改用户昵称
Route::any('edit_userinfo','LoginController@edit_userinfo');
//Ajax获取用户手机号
Route::any('get_phone','LoginController@get_phone');
//更改手机号
Route::any('change_phone','LoginController@change_phone');
//绑定微信
Route::any('bind_wx','LoginController@bind_wx');
//解除绑定微信
Route::any('remove_wx','LoginController@remove_wx');
//修改密码
Route::any('edit_password','LoginController@edit_password');
//上传头像
Route::any('upload_head','LoginController@upload_head');
//上传头像
Route::any('confirm_upload','LoginController@confirm_upload');
//输入邀请码
Route::any('add_invitation','LoginController@add_invitation');
//获取用户信息
Route::any('getUserInfo','LoginController@getUserInfo');
//后台将客户转为员工
Route::any('changestaff','LoginController@changestaff');
//后台将员工转为客户
Route::any('changeuser','LoginController@changeuser');


//注册--end

//设备管理
//查询accesstoken
Route::any('get_accessToken','Controller@get_accessToken');
Route::any('api_accessToken','Controller@api_accessToken');
Route::any('test001','Controller@test001');
Route::any('getAndriod','CompanyController@getAndriod');
//获取萤石设备列表
Route::any('get_YsList','YsController@get_YsList');
//修改名称
Route::any('edit_YsName','YsController@edit_YsName');
//解除绑定
Route::any('remove_Ys','YsController@remove_Ys');
//设备抓拍
Route::any('snap','YsController@snap');
//后台导入设备
Route::any('daoru','YsController@daoru');
//获取\H5直播地址
Route::any('getH5Address','YsController@getH5Address');
//打开设备通知接口
Route::any('openYs','YsController@openYs');
//关闭设备通知接口
Route::any('closeYs','YsController@closeYs');
// //查询设备列表
// Route::any('get_YsList','YsController@remove_Ys');


//检查用户
Route::any('check_user','CameraController@check_user');
//添加设备及管理员
Route::any('create_camera','CameraController@create_camera');
//查询设备列表
Route::any('camera_list','CameraController@camera_list');
//为设备添加子用户
Route::any('create_camera_user','CameraController@create_camera_user');
//删除设备
Route::any('del_camera','CameraController@del_camera');
//禁用和开启子用户权限
Route::any('edit_camera_user','CameraController@edit_camera_user');
//获取摄像头下的子用户列表
Route::any('get_camera_user','CameraController@get_camera_user');
//删除子用户
Route::any('del_camera_user','CameraController@del_camera_user');
//修改设备名称
Route::any('edit_camera_name','CameraController@edit_camera_name');
//修改设备共享状态
Route::any('edit_camera_share','CameraController@edit_camera_share');
//获取共享的设备列表
Route::any('get_share_list','CameraController@get_share_list');
//修改子用户的昵称
Route::any('edit_user_name','CameraController@edit_user_name');



//轮播
Route::any('get_banner','CasesController@get_banner');


//案例 --start
//添加案例
Route::any('create_case','CasesController@create_case');
//案例上传图片
Route::any('upload_photo','CasesController@upload_photo');
//获取案例
Route::any('get_cases','CasesController@get_cases');
//获取案例热度
Route::any('caseHot','CasesController@caseHot');
//获取楼盘
Route::any('get_residence','CasesController@get_residence');
//获取楼盘下的案例列表
Route::any('getResidenceCase','CasesController@getResidenceCase');
//分享转发
Route::any('get_share','CasesController@get_share');

//项目管理--业主 --start
//查询我的项目
Route::any('getMyProject','ProjectController@getMyProject');
//查询我的项目1026
Route::any('getMyProject1026','ProjectController@getMyProject1026');
//查询我的项目进度
Route::any('getMyProjectFlow','ProjectController@getMyProjectFlow');
//查询我的项目进度--播报多图
Route::any('getMyProjectFlow1015','ProjectController@getMyProjectFlow1015');


Route::any('getCompanyList','Project_ruleController@getCompanyList');
//项目管理--公司 --start
//查询我的项目
Route::any('myProject','Project_ruleController@myProject');
//查询项目进度
Route::any('getProjectFlow','Project_ruleController@getProjectFlow');
//修改进度状态
Route::any('editFlowState','Project_ruleController@editFlowState');
//上传播报
Route::any('createBroadcast','Project_ruleController@createBroadcast');
//上传图片
Route::any('upload_broad_image','Project_ruleController@upload_broad_image');
//点赞
Route::any('touchZan','Project_ruleController@touchZan');
//上传播报图片
Route::any('uploadBroadcast','Project_ruleController@uploadBroadcast');
//新建项目--获取项目初始数据
Route::any('getProjectData','Project_ruleController@getProjectData');
//新建项目
Route::any('creatProject','Project_ruleController@creatProject');


// 公司客户端
// 员工操作 --start
// 获取员工列表
Route::any('get_staff','StaffController@get_staff');
//获取岗位
Route::any('getJob','StaffController@getJob');
// 添加员工
Route::any('add_staff','StaffController@add_staff');
// 修改员工
Route::any('edit_staff','StaffController@edit_staff');
// 删除员工
Route::any('del_staff','StaffController@del_staff');


// 客户管理 --start
// 添加客户
Route::any('add_customer','CustomerController@add_customer');
Route::any('get_customer','CustomerController@get_customer');
//修改客户
Route::any('edit_customer','CustomerController@edit_customer');

//萤石设备管理 --start
//添加设备
Route::any('add_ys','AdminYsController@add_ys');
//查询设备列表
Route::any('get_ys','AdminYsController@get_ys');
//修改设备名称
Route::any('edit_ys_name','AdminYsController@edit_ys_name');

//获取员工共享设备
Route::any('getShareList','AdminYsController@getShareList');
//后台解除绑定工地和业主
Route::any('jiechubangding','AdminYsController@jiechubangding');



//设置公司简介
Route::any('setCompany','CasesController@setCompany');
//设置相册
Route::any('setimages','CasesController@setimages');

//公司--start
//公司首页
Route::any('companyHome','CompanyController@companyHome');
//公司首页
Route::any('companyHome1031','CompanyController@companyHome1031');
//设计师列表
Route::any('designer_list','CompanyController@designer_list');
//设计师列表
Route::any('designer_detail','CompanyController@designer_detail');
//设计师案例列表
Route::any('designer_cases','CompanyController@designer_cases');
//案例详情
Route::any('case_detail','CompanyController@case_detail');
//获取相册
Route::any('get_pics','CompanyController@get_pics');
//获取邀请页面链接
Route::any('getShareLink','CompanyController@getShareLink');
//获取施工团队里列表
Route::any('getBuildTeam','CompanyController@getBuildTeam');
//获取项目经理详情
Route::any('getBuilderDetail','CompanyController@getBuilderDetail');
//获取施工案例详情
Route::any('getBuildDetail','CompanyController@getBuildDetail');
//获取公司统计
Route::any('companyRecord','CompanyController@companyRecord');
//获取工艺详情
Route::any('artDetail','CompanyController@artDetail');
//推送
//测试推送
Route::any('send_push','PushController@send_push');
//储存用户 DeviceToken
Route::any('pushDeviceToken','PushController@pushDeviceToken');

//消息管理
//获取消息列表
Route::any('getMessages','MessagesController@getMessages');
//获取消息详情
Route::any('getMesDetail','MessagesController@getMesDetail');
//删除消息
Route::any('delMes','MessagesController@delMes');

//活动管理
//获取活动
Route::any('getActs','ActivitysController@getActs');
//活动详情
Route::any('getActsDetail','ActivitysController@getActsDetail');
 