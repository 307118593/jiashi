<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class CameraController extends Controller
{
	//检查用户
	public function check_user(Request $request){
		$phone = $request->input('phone');
		$name = DB::table('user')->where('phone',$phone)->value('name');
		if ($name) {
			return response()->json(['error'=>0,'name'=>$name]);
		}else{
			return response()->json(['error'=>1,'mes'=>'该手机号尚未注册.']);
		}
	}

	//添加设备和绑定设备管理员
    public function create_camera(Request $request){
    	$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$account = $request->input('account');
    	$pwd = $request->input('pwd');
    	$name = $request->input('name');
    	$res = DB::table('camera')->where('mac',$mac)->first();
    	if ($res) {
    		return response()->json(['error'=>1,'mes'=>'该设备已有管理员']);
    	}
    	// if (empty($phone)) {return response()->json(['error'=>1,'mes'=>'请输入手机号']);}
    	if (empty($mac)) {return response()->json(['error'=>1,'mes'=>'设备ID不能为空']);}
    	if (empty($account)) {return response()->json(['error'=>1,'mes'=>'无设备账号']);}
    	if (empty($pwd)) {return response()->json(['error'=>1,'mes'=>'无设备密码']);}
    	// $count = DB::table('camera')->where('phone',$phone)->count();
    	// $name = substr($phone,-4).'的摄像头'.($count + 1);
    	$array=array(
    		'uid'=>$uid,
    		'name'=>$name,
    		'mac'=>$mac,
    		'account'=>$account,
    		'pwd'=>$pwd,
    		'addtime'=>date('Y-m-d H:i:s',time()),
    	);
    	$res = DB::table('camera')->insert($array);
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'添加成功']);
    	}
    }


    //查询设备列表
    public function camera_list(Request $request){
    	$uid = $request->input('uid');
    	$mycameras = DB::table('camera')->where('uid',$uid)->orderBy('id','desc')->get();
        foreach ($mycameras as $k => $v) {
            $mycameras[$k]->addtime = strtotime($v->addtime);
        }
    	$cameras = DB::table('camera_auth')->where('uid',$uid)->orderBy('id','desc')->get();
    	foreach ($cameras as $k => $v) {
    		$camerainfo = DB::table('camera')->where('mac',$v->mac)->select('account','pwd','uid')->first();
    		$cameras[$k]->account = $camerainfo->account;
            $cameras[$k]->pwd = $camerainfo->pwd;
    		$cameras[$k]->admin_uid = $camerainfo->uid;
            $cameras[$k]->phone = DB::table('user')->where('id',$uid)->value('phone');
    		$cameras[$k]->addtime = strtotime($v->addtime);
    	}
    	$data['mycameras'] = $mycameras;
    	$data['cameras'] = $cameras;
    	return response()->json(['error'=>0,'data'=>$data]);

    }

    //删除设备
    public function del_camera(Request $request){
    	$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$admin_uid = $request->input('admin_uid');
    	if ($uid == $admin_uid) {//管理员删除设备
            $addtime = DB::table('camera')->where(['mac'=>$mac,'uid'=>$uid])->value('addtime');
            $array=array(
                    'uid'=>$uid,
                    'mac'=>$mac,
                     'is_admin'=>0,
                    'addtime'=>$addtime,
                    'losetime'=>date('Y-m-d H:i:s',time()),
                );
            DB::table('camera_log')->insert($array);
	    	$res = DB::table('camera')->where(['mac'=>$mac,'uid'=>$uid])->delete();


            $use = DB::table('camera_auth')->where('mac',$mac)->get();
            if (!$use->isEmpty()) {
                foreach ($use as $k => $v) {
                    $data = array(
                        'uid'=>$v->uid,
                        'mac'=>$mac,
                        'is_admin'=>1,
                        'addtime'=>$v->addtime,
                        'losetime'=>date('Y-m-d H:i:s',time()),
                    );
                    DB::table('camera_log')->insert($data);
                }
                DB::table('camera_auth')->where('mac',$mac)->delete();
               
            }
    	}else{//子用户删除设备
            $addtime = DB::table('camera_auth')->where(['mac'=>$mac,'uid'=>$uid])->value('addtime');
            $data = array(
                'uid'=>$uid,
                'mac'=>$mac,
                'is_admin'=>1,
                'addtime'=>$addtime,
                'losetime'=>date('Y-m-d H:i:s',time()),
            );
            DB::table('camera_log')->insert($data);
    		$res = DB::table('camera_auth')->where(['mac'=>$mac,'uid'=>$uid])->delete();
    	}
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'操作成功']);
    	}
    }

    //为设备添加子用户
    public function create_camera_user(Request $request){
    	$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$user_phone = $request->input('user_phone');

    	$admin_uid = DB::table('camera')->where('mac',$mac)->value('uid');
    	if ($uid != $admin_uid) {
    		return response()->json(['error'=>1,'mes'=>'您不是该摄像头的管理员.']);
    	}
        $user_id = DB::table('user')->where('phone',$user_phone)->value('id');
    	$res = DB::table('camera_auth')->where(['mac'=>$mac,'uid'=>$user_id])->first();
    	if ($res) {
    		return response()->json(['error'=>1,'mes'=>'用户'.$user_phone.'已经是该设备的用户了']);
    	}
    	$array = array(
    		'mac'=>$mac,
    		'uid'=>$user_id,
    		'allow'=>1,
    		'addtime'=>date('Y-m-d H:i:s',time()),
    	);
    	$res = DB::table('camera_auth')->insert($array);
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'操作成功']);
    	}

    }

    //禁用和开启子用户权限
    public function edit_camera_user(Request $request){
    	$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$use_uid = $request->input('use_uid');
    	$allow = $request->input('allow');
    	$admin_uid = DB::table('camera')->where('mac',$mac)->value('uid');
        if ($uid != $admin_uid) {
            return response()->json(['error'=>1,'mes'=>'您不是该摄像头的管理员.']);
        }
    	$res = DB::table('camera_auth')->where(['mac'=>$mac,'uid'=>$use_uid])->update(['allow'=>$allow]);
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'操作成功']);
    	}
    }

    //获取摄像头下的子用户列表
    public function get_camera_user(Request $request){
    	$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$admin_uid = DB::table('camera')->where('mac',$mac)->value('uid');
        if ($uid != $admin_uid) {
            return response()->json(['error'=>1,'mes'=>'您不是该摄像头的管理员.']);
        }
    	$user_phones = DB::table('camera_auth')->where('mac',$mac)->select('uid','addtime','allow','user_name')->get();
        foreach ($user_phones as $k => $v) {
            $user_phones[$k]->phone = DB::table('user')->where('id',$uid)->value('phone');
            $user_phones[$k]->addtime = strtotime($v->addtime);
        }
    	return response()->json(['error'=>0,'data'=>$user_phones]);
    }

    //删除子用户
    public function del_camera_user(Request $request){
    	$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$use_uid = $request->input('use_uid');
    	$admin_uid = DB::table('camera')->where('mac',$mac)->value('uid');
        if ($uid != $admin_uid) {
            return response()->json(['error'=>1,'mes'=>'您不是该摄像头的管理员.']);
        }
        $addtime = DB::table('camera_auth')->where(['mac'=>$mac,'uid'=>$use_uid])->value('addtime');
        $data = array(
            'uid'=>$use_uid,
            'mac'=>$mac, 
            'is_admin'=>1,
            'addtime'=>$addtime,
            'losetime'=>date('Y-m-d H:i:s',time()),
        );
        DB::table('camera_log')->insert($data);
    	$res = DB::table('camera_auth')->where(['mac'=>$mac,'uid'=>$use_uid])->delete();
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'操作成功']);
    	}


    }


    //修改设备名称
    public function edit_camera_name(Request $request){
    	$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$name = $request->input('name');
        $admin_uid = $request->input('admin_uid');
    	if ($uid == $admin_uid) {
    		$res = DB::table('camera')->where('mac',$mac)->update(['name'=>$name]);
    	}else{
    		$res = DB::table('camera_auth')->where(['mac'=>$mac,'uid'=>$uid])->update(['name'=>$name]);
    	}
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'操作成功']);
    	}

    }
    

    //修改设备共享状态
    public function edit_camera_share(Request $request){
    	$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$is_share = $request->input('is_share');
    	$res = DB::table('camera')->where(['mac'=>$mac,'uid'=>$uid])->update(['is_share'=>$is_share]);
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'操作成功']);
    	}

    }

    //获取共享的设备列表
    public function get_share_list(Request $request){
    	$camera_list = DB::table('camera')->where('is_share',1)->get();
    	return response()->json(['error'=>0,'data'=>$camera_list]);
    }



    //修改设备密码
    public function edit_camera_pwd(Request $request){
        $uid = $request->input('uid');
        $mac = $request->input('mac');
        $account = $request->input('account');
        $pwd = $request->input('pwd');
        $code = $request->input('code');
        $phone = DB::table('user')->where('id',$uid)->value('phone');
        $ralcode = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('code');
        $nowtime = time();
        $losetime = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('time')+10*60;
        if ($nowtime > $losetime) {
            return response()->json(['error'=>1,'mes'=>'验证码过期.']);
        }
        if ($code != $ralcode) {
            return response()->json(['error'=>1,'mes'=>'验证码错误.']);
        }
        $adminuid = DB::table('camera')->where('uid',$uid)->value('uid');
        if ($adminuid != $uid) {
            return response()->json(['error'=>1,'mes'=>'您不是设备管理员.']);
        }

        $data = [
            'account'=>$account,
            'pwd'=>$pwd,
        ];
        $res = DB::table('camera')->where(['mac'=>$mac,'uid'=>$uid])->update($data);
        if ($res) {
            return response()->json(['error'=>0,'mes'=>'修改成功']);
        }
    }

    //修改子用户的昵称
    public function edit_user_name(Request $request){
        $uid = $request->input('uid');//子用户的uid
        $mac = $request->input('mac');
        $user_name = $request->input('user_name');

        $res = DB::table('camera_auth')->where(['uid'=>$uid,'mac'=>$mac])->update(['user_name'=>$user_name]);
        if ($res) {
            return response()->json(['error'=>0,'mes'=>'修改成功']);
        }
    }


}
