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
		$pwd = substr($phone,-6);
        $password = \Hash::make($pwd);
		$data = [
			'phone'=>$phone,
			'name'=>$name,
			'address'=>$address,
			'password'=>$password,
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

	//修改客户资料
	public function edit_customer(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role == 4) {
			$pid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else if ($role == 2) {
			$pid = $uid;
		}else{
			return response()->json(['error'=>1,'mes'=>'无权限!']);
		}

		$user_uid = $request->input('user_uid');
		$name = $request->input('name');
		$address = $request->input('address');
		$area = $request->input('area');
		$style = $request->input('style');
		$type = $request->input('type');
		$price = $request->input('price');
		$birthday = $request->input('birthday');
		$remark = $request->input('remark');
		$source = $request->input('source');

		$data = [
			'name'=>$name,
			'address'=>$address,
			'area'=>$area,
			'type'=>$type,
			'price'=>$price,
			'birthday'=>$birthday,
			'remark'=>$remark,
			'source'=>$source,
		];

		$res = DB::table('user')->where('id',$user_uid)->update($data);
		if ($res) {
			$user = DB::table('user')->where('id',$user_uid)->first();
			return response()->json(['error'=>0,'data'=>$user]);
		}
	}
}