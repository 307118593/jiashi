<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use Umeng;
class PushController extends Controller
{
	public function send_push(){
		
	}

	// //储存用户 DeviceToken
	// public function pushDeviceToken(Request $request){
	// 	$uid = $request->input('uid');
	// 	$DeviceToken = $request->input('DeviceToken');
	// 	$res = DB::table('user')->where('id',$uid)->value('DeviceToken');
	// 	if (empty($res)) {
	// 		DB::table('user')->where('id',$uid)->update(['DeviceToken'=>$DeviceToken]);
	// 		return response()->json(['error'=>0,'mes'=>'ok']);
	// 	}
	// 	return response()->json(['error'=>1,'mes'=>'error']);
	// }
}
