<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class CustomerController extends Controller
{
	//添加客户
	public function add_customer(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role == 4) {
			$pid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else if ($role == 2) {
			$pid = $uid;
		}else{
			return response()->json(['error'=>1,'mes'=>'无权限!']);
		}
		$phone = $request->input('phone');
		$res = DB::table('user')->where('phone',$phone)->first();
		if ($res) {
			return response()->json(['error'=>1,'mes'=>'该手机号已存在!']);
		}
		$name = $request->input('name');
		$address = $request->input('address');
		$data = [
			'phone'=>$phone,
			'name'=>$name,
			'address'=>$address,
			'password'=>\Hash::make('111111'),
			'cid'=>$pid,
			'headurl'=>'http://47.97.109.9/headurl/kehu.jpg',
			'addtime'=>date('Y-m-d H:i:s'),
		];
		$res = DB::table('user')->insert($data);
		if ($res) {
			return response()->json(['error'=>0,'mes'=>'添加成功!']);
		}

	}


	//获取用户列表
	public function get_customer(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role == 4) {
			$pid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else if ($role == 2) {
			$pid = $uid;
		}else{
			return response()->json(['error'=>1,'mes'=>'无权限!']);
		}
		$customer = DB::table('user')->where('cid',$pid)->get();
		return response()->json(['error'=>0,'data'=>$customer]);
	}
}