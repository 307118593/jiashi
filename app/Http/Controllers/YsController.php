<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class YsController extends Controller
{
	public function __construct(){
		$this->accessToken = $this->get_accessToken();
        $this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
        $this->host = 'http://'.request()->server('HTTP_HOST').'/';
	}
	// 获取设备列表 优化之前的接口
	public function get_YsList222(Request $request){
		$uid = $request->input('uid',29);
        $cid = DB::table('user')->where('id',$uid)->value('cid');
        if ($cid == 0 ) {
            $cid = 2;
        }
        $invitation = 1000+$cid;
        //查看员工共享设备
        $is_copy = DB::table('user')->where('id',$uid)->value('is_copy');
        $share = DB::table('camera')->where('cid',$cid)->Where('user_share',1)->get();
        $share = $share->toArray();
        if ($is_copy == 1) {
            $sshare = DB::table('camera')->where('cid',$cid)->Where('staff_share',1)->where('user_share',0)->get();
            $sshare = $sshare->toArray();
            $share = array_merge($share,$sshare);
        }
		$camera = DB::table('camera')->where('uid',$uid)->where('cid',$cid)->Where('user_share',0)->get();
        $camera = $camera->toArray();
        // if (!$camera->isEmpty()) {
        $camera = array_merge($camera,$share);
        if (!empty($camera)) {
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
                    $image = DB::table('project')->where('id',$v->pro_id)->value('image');
                    if ($image) {
                        $camera[$k]->picUrl = $this->upload.$image;
                    }else{
                        $camera[$k]->picUrl = $this->host.'upload/live_base.jpg';
                    }
                    $camera[$k]->shareUrl = 'app/livedemo/liveplay.html?mac='.$v->mac.'&invitation='.$invitation;
                }
            }
        }

		
        $auth = DB::table('camera_auth')->where('uid',$uid)->whereNotNull('mac')->get();
        if (!$auth->isEmpty()) {
            foreach ($auth as $k => $v) {
                $ys = $this->vpost('https://open.ys7.com/api/lapp/device/info','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $ys = json_decode($ys);
                $td = $this->vpost('https://open.ys7.com/api/lapp/device/camera/list','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $td = json_decode($td);
                $Support = DB::table('camera')->where('mac',$v->mac)->select('staff_share','user_share','pro_id','isSupportPTZ','isSupportTalk','isSupportZoom','name','picUrl')->first();
                if ($ys->code == 200 && $Support) {
                    $auth[$k]->status = $ys->data->status;
                    $auth[$k]->defence = $ys->data->defence;
                    $auth[$k]->isEncrypt = $ys->data->isEncrypt;
                    $auth[$k]->alarmSoundMode = $ys->data->alarmSoundMode;
                    $auth[$k]->offlineNotify = $ys->data->offlineNotify;
                    $auth[$k]->videoLevel = $td->data[0]->videoLevel;
                    $auth[$k]->cameraNo = $td->data[0]->channelNo;
                    $auth[$k]->isShared = $td->data[0]->isShared;
                    $image = DB::table('project')->where('id',$Support->pro_id)->value('image');
                    if ($image) {
                        $auth[$k]->picUrl = $this->upload.$image;
                    }else{
                        $auth[$k]->picUrl = $this->host.'upload/live_base.jpg';
                    }
                    
                    $auth[$k]->isSupportPTZ = $Support->isSupportPTZ;
                    $auth[$k]->isSupportTalk = $Support->isSupportTalk;
                    $auth[$k]->isSupportZoom = $Support->isSupportZoom;
                    $auth[$k]->name = $Support->name;
                    $auth[$k]->shareUrl = 'app/livedemo/liveplay.html?mac='.$v->mac.'&invitation='.$invitation;
                    if ($Support->user_share == 1 || ($is_copy == 1 && $Support->staff_share == 1)) {
                        unset($auth[$k]);
                    }
                    // if ($is_copy == 1 && $Support->staff_share == 1) {
                    //     unset($auth[$k]);
                    // }
                }
                
            }
        
        }
        $auth = $auth->toArray();
        $auth = array_values($auth);
        $data['camera'] = $camera;
        $data['camera_auth'] = $auth;
		return response()->json(['error'=>0,'data'=>$data]);
	}
    // 获取设备列表2019/2/22
    public function get_YsList(Request $request){
        $uid = $request->input('uid',29);
        $cid = DB::table('user')->where('id',$uid)->value('cid');
        if ($cid == 0 ) {
            $cid = 2;
        }
        $invitation = 1000+$cid;
        //查看员工共享设备
        $is_copy = DB::table('user')->where('id',$uid)->value('is_copy');
        $share = DB::table('camera')->where('cid',$cid)->Where('user_share',1)->get();
        $share = $share->toArray();
        if ($is_copy == 1) {
            $sshare = DB::table('camera')->where('cid',$cid)->Where('staff_share',1)->where('user_share',0)->get();
            $sshare = $sshare->toArray();
            $share = array_merge($share,$sshare);
        }
        $camera = DB::table('camera')->where('uid',$uid)->where('cid',$cid)->Where('user_share',0)->get();
        $camera = $camera->toArray();
        // if (!$camera->isEmpty()) {
        $camera = array_merge($camera,$share);
        if (!empty($camera)) {
            foreach ($camera as $k => $v) {
                    $image = DB::table('project')->where('id',$v->pro_id)->value('image');
                    if ($image) {
                        $camera[$k]->picUrl = $this->upload.$image;
                    }else{
                        $camera[$k]->picUrl = $this->host.'upload/live_base.jpg';
                    }
                    $camera[$k]->shareUrl = 'app/livedemo/liveplay.html?mac='.$v->mac.'&invitation='.$invitation;
            }
        }

        
        $auth = DB::table('camera_auth')->where('uid',$uid)->whereNotNull('mac')->get();
        if (!$auth->isEmpty()) {
            foreach ($auth as $k => $v) {
                $Support = DB::table('camera')->where('mac',$v->mac)->select('status','staff_share','user_share','pro_id','isSupportPTZ','isSupportTalk','isSupportZoom','name','picUrl','defence','isEncrypt','alarmSoundMode','offlineNotify','videoLevel','cameraNo','is_playback')->first();
                    $image = DB::table('project')->where('id',$Support->pro_id)->value('image');
                    if ($image) {
                        $auth[$k]->picUrl = $this->upload.$image;
                    }else{
                        $auth[$k]->picUrl = $this->host.'upload/live_base.jpg';
                    }
                    
                    $auth[$k]->isSupportPTZ = $Support->isSupportPTZ;
                    $auth[$k]->isSupportTalk = $Support->isSupportTalk;
                    $auth[$k]->isSupportZoom = $Support->isSupportZoom;
                    $auth[$k]->name = $Support->name;
                    $auth[$k]->status = $Support->status;
                    $auth[$k]->defence = $Support->defence;
                    $auth[$k]->isEncrypt = $Support->isEncrypt;
                    $auth[$k]->alarmSoundMode = $Support->alarmSoundMode;
                    $auth[$k]->offlineNotify = $Support->offlineNotify;
                    $auth[$k]->videoLevel = $Support->videoLevel;
                    $auth[$k]->cameraNo = $Support->cameraNo;
                    $auth[$k]->is_playback = $Support->is_playback;
                    $auth[$k]->shareUrl = 'app/livedemo/liveplay.html?mac='.$v->mac.'&invitation='.$invitation;
                    if ($Support->user_share == 1 || ($is_copy == 1 && $Support->staff_share == 1)) {
                        unset($auth[$k]);
                    }
                 
                
            }
        
        }
        $auth = $auth->toArray();
        $auth = array_values($auth);
        $data['camera'] = $camera;
        $data['camera_auth'] = $auth;
        return response()->json(['error'=>0,'data'=>$data]);
    }

    // // 获取设备列表1026
    // public function get_YsList1026(Request $request){
    //     $uid = $request->input('uid',35);
    //     $camera = DB::table('camera')->where('uid',$uid)->get();
    //     if (!$camera->isEmpty()) {
    //         foreach ($camera as $k => $v) {
    //             $ys = $this->vpost('https://open.ys7.com/api/lapp/device/info','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
    //             $ys = json_decode($ys);
    //             $td = $this->vpost('https://open.ys7.com/api/lapp/device/camera/list','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
    //             $td = json_decode($td);
    //             if ($ys->code == 200) {
    //                 $camera[$k]->status = $ys->data->status;
    //                 $camera[$k]->defence = $ys->data->defence;
    //                 $camera[$k]->isEncrypt = $ys->data->isEncrypt;
    //                 $camera[$k]->alarmSoundMode = $ys->data->alarmSoundMode;
    //                 $camera[$k]->offlineNotify = $ys->data->offlineNotify;
    //                 $camera[$k]->videoLevel = $td->data[0]->videoLevel;
    //                 $camera[$k]->cameraNo = $td->data[0]->channelNo;
    //                 $camera[$k]->isShared = $td->data[0]->isShared;
    //                 $camera[$k]->picUrl = $this->host.'upload/live_base.jpg';
    //             }
    //         }
    //     }
        
    //     $auth = DB::table('camera_auth')->where('uid',$uid)->get();
    //     if (!$auth->isEmpty()) {
    //         foreach ($auth as $k => $v) {
    //             $ys = $this->vpost('https://open.ys7.com/api/lapp/device/info','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
    //             $ys = json_decode($ys);
    //             $td = $this->vpost('https://open.ys7.com/api/lapp/device/camera/list','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
    //             $td = json_decode($td);
    //             $Support = DB::table('camera')->where('mac',$v->mac)->select('isSupportPTZ','isSupportTalk','isSupportZoom','name','picUrl')->first();
    //             if ($ys->code == 200) {
    //                 $auth[$k]->status = $ys->data->status;
    //                 $auth[$k]->defence = $ys->data->defence;
    //                 $auth[$k]->isEncrypt = $ys->data->isEncrypt;
    //                 $auth[$k]->alarmSoundMode = $ys->data->alarmSoundMode;
    //                 $auth[$k]->offlineNotify = $ys->data->offlineNotify;
    //                 $auth[$k]->videoLevel = $td->data[0]->videoLevel;
    //                 $auth[$k]->cameraNo = $td->data[0]->channelNo;
    //                 $auth[$k]->isShared = $td->data[0]->isShared;
    //                 $auth[$k]->picUrl = $this->host.'upload/live_base.jpg';
    //                 $auth[$k]->isSupportPTZ = $Support->isSupportPTZ;
    //                 $auth[$k]->isSupportTalk = $Support->isSupportTalk;
    //                 $auth[$k]->isSupportZoom = $Support->isSupportZoom;
    //                 $auth[$k]->name = $Support->name;
    //             }
    //         }
        
    //     }
    //     $data['camera'] = $camera;
    //     $data['camera_auth'] = $auth;
    //     return response()->json(['error'=>0,'data'=>$data]);
    // }

	//修改设备名称
	public function edit_YsName(Request $request){
		$uid = $request->input('uid');
    	$mac = $request->input('mac');
    	$name = $request->input('name');
        $admin_uid = $request->input('admin_uid');
    	if ($uid == $admin_uid) {
    		$ret = $this->vpost('https://open.ys7.com/api/lapp/device/name/update','accessToken='.$this->accessToken.'&deviceSerial='.$mac.'&deviceName='.$name);
            $ret = json_decode($ret);
            if ($ret->code == 200) {
            	$res = DB::table('camera')->where('mac',$mac)->update(['name'=>$name]);
            }
    	}else{
    		$res = DB::table('camera_auth')->where(['mac'=>$mac,'uid'=>$uid])->update(['name'=>$name]);
    	}
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'操作成功']);
    	}
	}

	//解除设备绑定
	public function remove_Ys(Request $request){
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
	    	$res = DB::table('camera')->where(['mac'=>$mac,'uid'=>$uid])->update(['uid'=>0]);


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

    public function snap(Request $request){
        $mac = $request->input('mac');
        $pictime = DB::table('camera')->where('mac',$mac)->value('picUrltime');
        if (time()-$pictime < 300) {//抓拍控制5分钟
            return response()->json(['error'=>1,'mes'=>'限制频繁抓拍!']);
        }
        $ret = $this->vpost('https://open.ys7.com/api/lapp/device/capture','accessToken='.$this->accessToken.'&deviceSerial='.$mac.'&channelNo=1');
        $ret = json_decode($ret);
        if ($ret->code != 200) {
            return response()->json(['error'=>1,'mes'=>'截图失败!']);
        }
        $url = $ret->data->picUrl;
        $path = $this->download($url,$mac);
        DB::table('camera')->where('mac',$mac)->update(['picUrl'=>$this->host.$path,'picUrltime'=>time()]);
        return response()->json(['error'=>0,'mes'=>'操作成功']);
    }

    public function download($url,$mac ,$path = 'upload/snap/')
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
      $file = curl_exec($ch);
      curl_close($ch);
      // $filename = pathinfo($url, PATHINFO_BASENAME);
      $filename = $mac.'.jpg';
      if(file_exists($path.$filename)){
            unlink($path.$filename);
        }
      $resource = fopen($path . $filename, 'a');
      fwrite($resource, $file);
      fclose($resource);
      // return $res;
      return $path.$filename;
    }

    //后台导入萤石设备
    public function daoru(){
        $camera = [];
        for ($i=0; $i < 100; $i++) { 
            $ys = $this->getYsList($i);
            $ys = json_decode($ys,true);
            $camera = array_merge($camera,$ys['data']);
            if ($ys['page']['size'] != 50) {
                break;
            }
        }
        $macs = DB::table('camera')->pluck('mac');
        // return $macs;
        $macs = $macs->toArray();
        foreach ($camera as $k => $v) {
            if (in_array($v['deviceSerial'],$macs)) {
                unset($camera[$k]);
            }else{
                $data = [
                    'mac'=>$v['deviceSerial'],
                    'name'=>$v['channelName'],
                    'number'=>$v['channelName'],
                    'addtime'=>date('Y-m-d H:i:s'),
                ];
                DB::table('camera')->insert($data);
            }
        }
        return response()->json(['error'=>0,'mes'=>'操作成功']);
    }

    public function getYsList($pageStart){
        $ys = $this->vpost('https://open.ys7.com/api/lapp/camera/list','accessToken='.$this->accessToken.'&pageStart='.$pageStart.'&pageSize=50');
        return $ys;
    }

    //更新设备状态.优化拉取速度
    public function updateYs(){
        $macs = DB::table('camera')->pluck('mac');
        // $macs = $macs->toArray();
        foreach ($macs as $k => $v) {
            $ys = $this->vpost('https://open.ys7.com/api/lapp/device/info','accessToken='.$this->accessToken.'&deviceSerial='.$v);
            $ys = json_decode($ys);
            $td = $this->vpost('https://open.ys7.com/api/lapp/device/camera/list','accessToken='.$this->accessToken.'&deviceSerial='.$v);
            $td = json_decode($td);
            $data = [
                'status'=>$ys->data->status,
                'defence'=>$ys->data->defence,
                'isEncrypt'=>$ys->data->isEncrypt,
                'alarmSoundMode'=>$ys->data->alarmSoundMode,
                'offlineNotify'=>$ys->data->offlineNotify,
                'videoLevel'=>$td->data[0]->videoLevel,
                'cameraNo'=>$td->data[0]->channelNo,
                'isShared'=>$td->data[0]->isShared,
            ];
            DB::table('camera')->where('mac',$v)->update($data);
      
        }
        return 'ok';
    }

    public function getH5Address(Request $request){
        $mac = $request->input('mac');
        $camera = DB::table('camera')->where('mac',$mac)->select('pro_id','name')->first();
        $res = $this->vpost('https://open.ys7.com/api/lapp/live/address/get','accessToken='.$this->accessToken.'&source='.$mac.':1');
        $res = json_decode($res);
        // return $res->data->deviceSerial;
        if ($res->code == 200) {
            $data = [];
            $data['hls'] = $res->data[0]->hls;
            $data['rtmp'] = $res->data[0]->rtmp;
            $data['project'] = DB::table('project')->where('id',$camera->pro_id)->value('name');
            $data['name'] = $camera->name;
            return response()->json(['error'=>0,'data'=>$data]);
        }else{
            return response()->json(['error'=>1,'code'=>$res->code]);
        }
        
    }

    public function ysLive(Request $request){
        $mac = $request->input('mac');
        $res = $this->vpost('https://open.ys7.com/api/lapp/live/address/get','accessToken='.$this->accessToken.'&source='.$mac.':1');
        // return $res;
        $res = json_decode($res);
        if ($res->code == 200) {
            $data = [];
            $data['hls'] = $res->data[0]->hls;
            $data['rtmp'] = $res->data[0]->rtmp;
            $data['name'] = DB::table('camera')->where('mac',$mac)->value('name');
            return view('live.ysLive',['data'=>$data]);
        }else{
            return response()->json(['error'=>1,'code'=>$res->code]);
        }
    }

    public function lives(Request $request){
        $uid = $request->input('userid');
        $count = $request->input('count',4);
        $page = $request->input('page',1);
        $userid = substr($uid,10);
        $role = $this->getRole($userid);
        if ($role == 1) {
            $where=[];
        }elseif($role==2){
            $where=['cid'=>$userid];
        }else{
            $where=['cid'=>DB::table('admin_users')->where('id',$userid)->value('pid')];
        }
        DB::table('camera')->where($where)->whereRaw('(uid> ? or pro_id > ?)', [0,0])->update(['hls'=>null,'rtmp'=>null,'alive'=>0]);
        $live = DB::table('camera')->where($where)->whereRaw('(uid> ? or pro_id > ?)', [0,0])->get();
        foreach ($live as $k => $v) {
            $res = $this->vpost('https://open.ys7.com/api/lapp/live/address/get','accessToken='.$this->accessToken.'&source='.$v->mac.':1');
            $res = json_decode($res);
                if ($res->data[0]->status == 1 && $res->data[0]->exception == 0) {
                    DB::table('camera')->where('id',$v->id)->update(['hls'=>$res->data[0]->hlsHd,'rtmp'=>$res->data[0]->rtmpHd,'alive'=>1]);
                }
         
        }
        $camera = DB::table('camera')->where($where)->whereRaw('(uid> ? or pro_id > ?)', [0,0])->orderBy('alive','desc')->paginate($count);
        $number = count($camera);
        return view('live.allLive',['camera'=>$camera,'count'=>$count,'uid'=>$uid,'number'=>$number]);
    }

    //打开直播通知接口
    public function openYs(Request $request){
        $mac = $request->input('mac');
        $uid = $request->input('uid',0);
        $res = DB::table('camera_log')->where('uid',$uid)->where('mac',$mac)->where('day',date('Y-m-d'))->first();
        if (empty($res)) {
            $new = [
                'uid'=>$uid,
                'mac'=>$mac,
                'cid'=>DB::table('camera')->where('mac',$mac)->value('cid'),
                'opentime'=>time(),
                'day'=>date('Y-m-d'),
            ];
            DB::table('camera_log')->insert($new);
        }else{
            $new = [
                'opentime'=>time(),
            ];
            DB::table('camera_log')->where('id',$res->id)->update($new);
        }
        return response()->json(['error'=>0,'data'=>'ok']);

    }

    //关闭直播通知接口
    public function closeYs(Request $request){
        $mac = $request->input('mac');
        $uid = $request->input('uid',0);
        $res = DB::table('camera_log')->where('uid',$uid)->where('mac',$mac)->orderBy('id','desc')->first();
        $up = [
            'alivetime'=>time()-$res->opentime + $res->alivetime,
            'closetime'=>time(),
        ];
        DB::table('camera_log')->where('id',$res->id)->update($up);
        return response()->json(['error'=>0,'data'=>'ok']);
    }

    //后台批量分配
    public function fenpei(Request $request){
        $request = $request->all();
        $ids = $request['ids'];//设备id
        $cid = $request['cid'];
        $did = $request['did'];
        $data = [];
        if ($cid > 0) {
            $data['cid']=$cid;
        }
        if ($did > 0) {
            $data['did']=$did;
        }

        foreach ($ids as $k => $v) {
            DB::table('camera')->where('id',$v)->update($data);
        }

        return response()->json(['error'=>0,'data'=>'ok']);
    }
 
 
}