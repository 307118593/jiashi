<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class StaffController extends Controller
{
	public function getJob(Request $request){
		$job = $reuqest->input('job',0);
		$array = [1=>'销售总监',2=>'--销售',3=>'--设计师',4=>'--客服',10=>'工程总监',10=>'--项目总监',11=>'--施工人员',12=>'--监理人员'];
		if ($job == 0) {	
			$job = $array;
		}else{
			$job = $array[$job];
		}
		
		return response()->json(['error'=>0,'data'=>$job]);
	}

	//获取员工列表
	public function get_staff(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role == 4) {
			$pid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else if ($role == 2) {
			$pid = $uid;
		}else{
			return response()->json(['error'=>1,'mes'=>'无权限!']);
		}
		$staff = DB::table('admin_users')->where('pid',$pid)->orderBy('id','desc')->select('id','username','name','job','avatar')->get();
		if ($staff->isEmpty()) {
			 return response()->json(['error'=>0,'data'=>$staff]);
		}
		foreach ($staff as $k => $v) {
			switch ($v->job) {
                case 1:
                    $staff[$k]->job = '销售总监';
                    break;
                case 2:
                    $staff[$k]->job = '销售';
                    break;
                case 3:
                    $staff[$k]->job = '设计师';
                    break;
                case 4:
                    $staff[$k]->job = '客服';
                    break;
                case 10:
                    $staff[$k]->job = '项目经理';
                    break;
                case 11:
                    $staff[$k]->job = '施工人员';
                    break;
                case 12:
                    $staff[$k]->job = '监理人员';
                    break;
               
            }
		}
		return response()->json(['error'=>0,'data'=>$staff]);

	}

	//添加员工
	public function add_staff(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role == 4) {
			$pid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else if ($role == 2) {
			$pid = $uid;
		}
		$phone = $request->input('phone');
		$res = DB::table('admin_users')->where('username',$phone)->value('id');
		if ($res) {
			return response()->json(['error'=>1,'mes'=>'该账号已存在..']);
		}
		$name = $request->input('name');
		$job = $request->input('job');
		$password = substr($phone,-6);
		$password = \Hash::make($password);
		$data = [
			'username'=>$phone,
			'name'=>$name,
			'password'=>$password,
			'created_at'=>date('Y-m-d H:i:s'),
			'avatar'=>'images/touxiang.jpg',
			'job'=>$job,
			'pid'=>$pid,
		];
		$res = DB::table('admin_users')->insertGetId($data);
		if ($res) {
			if ($job == 1 || $job == 10) {
                DB::table('admin_role_users')->insert(['user_id'=>$res,'role_id'=>4]);//设置用户为总监权限
            }else{
                DB::table('admin_role_users')->insert(['user_id'=>$res,'role_id'=>3]);//设置用户为员工权限
            }
			return response()->json(['error'=>0,'mes'=>'添加成功.']);
		}
		return response()->json(['error'=>1,'mes'=>'添加失败.']);
	}

	//修改员工
	public function edit_staff(Request $request){
		$staff_id = $request->input('staff_id');
		$job = $reuqest->input('job');
		$res = DB::table('admin_users')->where('id',$staff_id)->update(['job'=>$job]);
		if ($res) {
			if ($job == 1 || $job == 10) {
                DB::table('admin_role_users')->where('user_id',$staff_id)->update(['role_id'=>4]);//设置用户为总监权限
            }else{
                DB::table('admin_role_users')->where('user_id',$staff_id)->update(['role_id'=>3]);//设置用户为员工权限
            }
            return response()->json(['error'=>0,'mes'=>'修改成功.']);
		}
		return response()->json(['error'=>1,'mes'=>'修改失败.']);
	}

	//删除员工
	public function del_staff(Request $request){
		$staff_id = $request->input('staff_id');
		$res = DB::table('admin_users')->where('id',$staff_id)->delete();
		if ($res) {
			return response()->json(['error'=>0,'mes'=>'操作成功.']);
		}
		return response()->json(['error'=>1,'mes'=>'操作失败.']);
	}
}