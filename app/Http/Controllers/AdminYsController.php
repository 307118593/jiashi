<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class AdminYsController extends Controller
{
	public function __construct(){
		$this->accessToken = $this->get_accessToken();
		$this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
        $this->host = 'http://'.request()->server('HTTP_HOST').'/';
	}
	//添加设备
	public function add_ys(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role == 4) {
			$pid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else if ($role == 2) {
			$pid = $uid;
		}else{
			return response()->json(['error'=>1,'mes'=>'无权限!']);
		}
		$name = $request->input('name');
		$mac = $request->input('mac');
		$code = $request->input('code');
		// $c_uid = $request->input('c_uid',0);//绑定客户
		$accessToken = $this->accessToken;
        $res = $this->vpost('https://open.ys7.com/api/lapp/device/add','accessToken='.$accessToken.'&deviceSerial='.$mac.'&validateCode='.$code);
        $ret = $this->vpost('https://open.ys7.com/api/lapp/device/name/update','accessToken='.$accessToken.'&deviceSerial='.$mac.'&deviceName='.$name);
        $ret = json_decode($ret);
        $res = json_decode($res);
        if ($res->code != 200 && $res->code != 20017) {
        	return response()->json(['error'=>1,'mes'=>'添加失败..错误代码:'.$res->code]);
        }
        if ($ret->code != 200) {
        	return response()->json(['error'=>1,'mes'=>'添加失败..错误代码:'.$ret->code]);
        }
        $data = [
        	'name'=>$name,
        	'mac'=>$mac,
        	'code'=>$code,
        	'uid'=>$c_uid,
        	'addtime'=>date('Y-m-d H:i:s'),
        	'cid'=>$pid,
        ];
        $res = DB::table('camera')->insert($data);
        if ($res) {
        	return response()->json(['error'=>0,'mes'=>'添加成功!']);
        }
	}

	//查询设备列表
	public function get_ys(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role == 4) {
			$pid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else if ($role == 2) {
			$pid = $uid;
		}else{
			return response()->json(['error'=>1,'mes'=>'无权限!']);
		}
		$camera = DB::table('camera')->where('cid',$pid)->get();
		if (!$camera->isEmpty()) {
            foreach ($camera as $k => $v) {
                $ys = $this->vpost('https://open.ys7.com/api/lapp/device/info','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $ys = json_decode($ys);
                $td = $this->vpost('https://open.ys7.com/api/lapp/device/camera/list','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $td = json_decode($td);
                if ($ys->code == 200) {
                    $camera[$k]->status = $ys->data->status;
                    $camera[$k]->defence = $ys->data->defence;
                    $camera[$k]->isEncrypt = $ys->data->isEncrypt;
                    $camera[$k]->alarmSoundMode = $ys->data->alarmSoundMode;
                    $camera[$k]->offlineNotify = $ys->data->offlineNotify;
                    $camera[$k]->videoLevel = $td->data[0]->videoLevel;
                    $camera[$k]->cameraNo = $td->data[0]->channelNo;
                    $camera[$k]->isShared = $td->data[0]->isShared;
                    $camera[$k]->picUrl = $td->data[0]->picUrl;
                }
            }
        }
        return response()->json(['error'=>0,'data'=>$camera]);
	}

	//修改设备名称
	public function edit_ys_name(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role == 4) {
			$pid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else if ($role == 2) {
			$pid = $uid;
		}else{
			return response()->json(['error'=>1,'mes'=>'无权限!']);
		}
		$mac = $request->input('mac');
		$name = $request->input('name');
		$ret = $this->vpost('https://open.ys7.com/api/lapp/device/name/update','accessToken='.$accessToken.'&deviceSerial='.$mac.'&deviceName='.$name);
		if ($ret->code != 200) {
        	return response()->json(['error'=>1,'mes'=>'操作失败..错误代码:'.$ret->code]);
        }
        $res = DB::table('camera')->where('mac',$mac)->update(['name'=>$name]);
        if ($res) {
        	return response()->json(['error'=>0,'mes'=>'操作成功!']);
        }
	}


	//后台解除绑定工地
	public function jiechubangding(Request $request){
		$id = $request->input('id');
		$res = DB::table('camera')->where('id',$id)->update(['uid'=>0,'pro_id'=>0]);
		if ($res == 1) {
			DB::table('camera_auth')->where('mac',$mac)->delete();
			return response()->json(['error'=>0,'mes'=>'操作成功!']);
		}else{
			return response()->json(['error'=>1,'mes'=>'操作失败!']);
		}
	}


	// 获取员工共享设备
	public function getShareList(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		// return $role;
		if ($role == 2) {
			$cid = $uid;
		}else{
			$cid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}
		$camera = DB::table('camera')->where('cid',$cid)->where('staff_share',1)->get();
		if (!$camera->isEmpty()) {
            foreach ($camera as $k => $v) {
                $ys = $this->vpost('https://open.ys7.com/api/lapp/device/info','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $ys = json_decode($ys);
                $td = $this->vpost('https://open.ys7.com/api/lapp/device/camera/list','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $td = json_decode($td);
                if ($ys->code == 200) {
                    $camera[$k]->status = $ys->data->status;
                    $camera[$k]->defence = $ys->data->defence;
                    $camera[$k]->isEncrypt = $ys->data->isEncrypt;
                    $camera[$k]->alarmSoundMode = $ys->data->alarmSoundMode;
                    $camera[$k]->offlineNotify = $ys->data->offlineNotify;
                    $camera[$k]->videoLevel = $td->data[0]->videoLevel;
                    $camera[$k]->cameraNo = $td->data[0]->channelNo;
                    $camera[$k]->isShared = $td->data[0]->isShared;
                    $camera[$k]->picUrl = $this->host.'upload/live_base.jpg';
                }
            }
        }
        $data['camera'] = $camera;
        return response()->json(['error'=>0,'data'=>$data]);
	}
}