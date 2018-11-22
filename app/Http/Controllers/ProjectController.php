<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
	public function __construct(){
		$this->host = 'http://'.request()->server('HTTP_HOST').'/';
        $this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
        $this->accessToken = $this->get_accessToken();
	}
	//获取我的项目列表
    public function getMyProject(Request $request){
    	$uid = $request->input('uid');
        if (empty($uid)) {
            return response()->json(['error'=>1,'data'=>'uid为空']);
        }
        $cid = DB::table('user')->where('id',$uid)->value('cid');

        $share = DB::table('project')->where('z_uid',$cid)->Where('user_share',1)->select('try_uid','id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id')->get();
    	$project = DB::table('project')->where('uid',$uid)->where('z_uid',$cid)->Where('user_share',0)->select('try_uid','id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id')->get();

        $share = $share->toArray();
        $project = $project->toArray();
        // if (!$camera->isEmpty()) {
        $project = array_merge($project,$share);
    	foreach ($project as $k => $v) {
    		$project[$k]->starttime_d = strtotime($v->starttime_d);
    		$project[$k]->company = DB::table('admin_users')->where('id',$v->z_uid)->value('name');
    		$project[$k]->image = $this->host.'upload/'.$v->image;
    		$project_us = json_decode($v->project_us,true);
    		$project[$k]->project_us_count = count($project_us) + 1;
            $project[$k]->leader = DB::table('admin_users')->where('id',$v->leader_id)->value('name');
            $project[$k]->stage = DB::table('flow')->where('pro_id',$v->id)->whereIn('state',[1,2])->orderBy('sort','desc')->value('name');
    		$wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
            if (empty($project[$k]->stage)) {
                $project[$k]->stage = '未开始';
            }
            if ($wcjd == 0) {
    			$project[$k]->speed = '未开始';
    		}else{
    			$all = DB::table('flow')->where(['pro_id'=>$v->id])->count();
    			$finish = DB::table('flow')->where(['pro_id'=>$v->id,'state'=>2])->count();
                if ($finish == 0) {
                   $project[$k]->speed = 0;
                }else{
                   $project[$k]->speed = round($finish/$all,2)*100;
                   $project[$k]->speed = $project[$k]->speed;
                }
    		}
    	}

        $ty = DB::table('project')->whereNotNull('try_uid')->where('uid','<>',$uid)->where('user_share',0)->select('try_uid','id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id')->get();
        // return $ty;
        if (!$ty->isEmpty()) {
            foreach ($ty as $k => $v) {
                $try_uid = json_decode($v->try_uid,true);
                // return $try_uid;
                if (!in_array($uid,$try_uid)) {
                    unset($ty[$k]);
                }else{
                    $ty[$k]->starttime_d = strtotime($v->starttime_d);
                    $ty[$k]->company = DB::table('admin_users')->where('id',$v->z_uid)->value('name');
                    $ty[$k]->image = $this->host.'upload/'.$v->image;
                    $project_us = json_decode($v->project_us,true);
                    $ty[$k]->project_us_count = count($project_us) + 1;
                    $ty[$k]->leader = DB::table('admin_users')->where('id',$v->leader_id)->value('name');
                    $ty[$k]->stage = DB::table('flow')->where('pro_id',$v->id)->whereIn('state',[1,2])->orderBy('sort','desc')->value('name');
                    if (empty($ty[$k]->stage)) {
                        $ty[$k]->stage = '未开始';
                    }
                    $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
                    if ($wcjd == 0) {
                        $ty[$k]->speed = '未开始';
                    }else{
                        $all = DB::table('flow')->where(['pro_id'=>$v->id])->count();
                        $finish = DB::table('flow')->where(['pro_id'=>$v->id,'state'=>2])->count();
                        if ($finish == 0) {
                           $ty[$k]->speed = 0;
                        }else{
                           $ty[$k]->speed = round($finish/$all,2)*100;
                           $ty[$k]->speed = $ty[$k]->speed;
                        }
                    } 
                }
                
            }
        }
        
        // $project = $project->toArray();
        $ty = $ty->toArray();
        // foreach ($ty as $k => $v) {
        //     unset($ty[$k]['try_uid']);
        // }
        $project = array_merge($project,$ty);
        $project = array_values($project);      

    	return response()->json(['error'=>0,'data'=>$project]);
    }
    //获取我的项目列表1026
    public function getMyProject1026(Request $request){
        $uid = $request->input('uid');
        $cid = 2;
        if ($uid) {
            $cid = DB::table('user')->where('id',$uid)->value('cid');
            if ($cid == 0) {
                $cid = 2;
            }
        }
        //查看员工共享项目
        $is_copy = DB::table('user')->where('id',$uid)->value('is_copy');
       
        if (empty($uid) || $cid == 2) {//未登录查看项目
            // return response()->json(['error'=>1,'data'=>'uid为空']);
            $share = DB::table('project')->Where('user_share',1)->where('z_uid',$cid)->select('user_share','try_uid','id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id')->get();
           
        }else{
            $share = DB::table('project')->where('z_uid',$cid)->Where('user_share',1)->select('staff_share','user_share','try_uid','id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id')->get();
            
        }
        $share = $share->toArray();
        if ($is_copy == 1) {
            $sshare = DB::table('project')->where('z_uid',$cid)->Where('staff_share',1)->where('user_share',0)->select('staff_share','user_share','try_uid','id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id')->get();
            $sshare = $sshare->toArray();
            $share = array_merge($share,$sshare);
        }
        
        $project = DB::table('project')->where('uid',$uid)->where('z_uid',$cid)->Where('user_share',0)->select('user_share','try_uid','id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id')->get();


        $project = $project->toArray();
        // if (!$camera->isEmpty()) {
        $project = array_merge($project,$share);
        foreach ($project as $k => $v) {

            $project[$k]->starttime_d = strtotime($v->starttime_d);
            $project[$k]->company = DB::table('admin_users')->where('id',$v->z_uid)->value('name');
            $project[$k]->image = $this->host.'upload/'.$v->image;
            $project_us = json_decode($v->project_us,true);
            $project[$k]->project_us_count = count($project_us) + 1;
            $project[$k]->leader = DB::table('admin_users')->where('id',$v->leader_id)->value('name');
            $project[$k]->stage = DB::table('flow')->where('pro_id',$v->id)->whereIn('state',[1,2])->orderBy('sort','desc')->value('name');
            $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
            if (empty($project[$k]->stage)) {
                $project[$k]->stage = '未开始';
            }
            if ($wcjd == 0) {
                $project[$k]->speed = '未开始';
            }else{
                $all = DB::table('flow')->where(['pro_id'=>$v->id])->count();
                $finish = DB::table('flow')->where(['pro_id'=>$v->id,'state'=>2])->count();
                if ($finish == 0) {
                   $project[$k]->speed = 0;
                }else{
                   $project[$k]->speed = round($finish/$all,2)*100;
                   $project[$k]->speed = $project[$k]->speed.'%';
                }
            }
        }

         $ty = DB::table('project')->whereNotNull('try_uid')->where('uid','<>',$uid)->where('user_share',0)->select('staff_share','user_share','try_uid','id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id')->get();
        
        if (!$ty->isEmpty()) {
            foreach ($ty as $k => $v) {
                $try_uid = json_decode($v->try_uid,true);
                // return $try_uid;
                if (!in_array($uid,$try_uid) || $v->staff_share == 1  || $v->user_share==1) {
                    unset($ty[$k]);
                }else{
                    $ty[$k]->starttime_d = strtotime($v->starttime_d);
                    $ty[$k]->company = DB::table('admin_users')->where('id',$v->z_uid)->value('name');
                    $ty[$k]->image = $this->host.'upload/'.$v->image;
                    $project_us = json_decode($v->project_us,true);
                    $ty[$k]->project_us_count = count($project_us) + 1;
                    $ty[$k]->leader = DB::table('admin_users')->where('id',$v->leader_id)->value('name');
                    $ty[$k]->stage = DB::table('flow')->where('pro_id',$v->id)->whereIn('state',[1,2])->orderBy('sort','desc')->value('name');
                    if (empty($ty[$k]->stage)) {
                        $ty[$k]->stage = '未开始';
                    }
                    $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
                    if ($wcjd == 0) {
                        $ty[$k]->speed = '未开始';
                    }else{
                        $all = DB::table('flow')->where(['pro_id'=>$v->id])->count();
                        $finish = DB::table('flow')->where(['pro_id'=>$v->id,'state'=>2])->count();
                        if ($finish == 0) {
                           $ty[$k]->speed = 0;
                        }else{
                           $ty[$k]->speed = round($finish/$all,2)*100;
                           $ty[$k]->speed = $ty[$k]->speed.'%';
                        }
                    } 
                }
                
            }
        }
        // $project = $project->toArray();
        $ty = $ty->toArray();
        $project = array_merge($project,$ty);
        return response()->json(['error'=>0,'data'=>$project]);
    }

    //获取项目进度
    public function getMyProjectFlow(Request $request){
        $pro_id = $request->input('pro_id');
    	$uid = $request->input('uid');
    	$project = DB::table('project')->where('id',$pro_id)->select('id','uid','name','z_uid','image','project_us','state','type','area','style','cameras','leader_id')->first();
    	if ($project->image) {
    		$project->image = $this->host.'upload/'.$project->image;
    	}
        $project->headurl = DB::table('user')->where('id',$project->uid)->value('headurl');
    	$project->user_name = DB::table('user')->where('id',$project->uid)->value('name');
    	$project_us = json_decode($project->project_us,true);
        array_unshift($project_us,$project->leader_id);
        // return $project_us;
    	foreach($project_us as $k => $v) {
                if (!$v) {
                    unset($project_us[$k]);
                    break;
                }
                $us = DB::table('admin_users')->where('id',$v)->select('name','username','job','avatar')->first();
                if ($us) {
                    switch ($us->job) {
                        case 1:
                            $us->job = '销售总监';
                            break;
                        case 2:
                            $us->job = '销售';
                            break;
                        case 3:
                            $us->job = '设计师';
                            break;
                        case 4:
                            $us->job = '客服';
                            break;
                        case 10:
                            $us->job = '工程总监';
                            break;
                        case 11:
                            $us->job = '项目经理';
                            break;
                        case 12:
                            $us->job = '施工人员';
                            break;
                        case 13:
                            $us->job = '监理人员';
                            break;
                       
                    }
                    $project_us[$k] = [];
                    $project_us[$k]['name'] = $us->name;
                    $project_us[$k]['phone'] = $us->username;
                    $project_us[$k]['job'] = $us->job;
                    $project_us[$k]['avatar'] = $this->upload.$us->avatar;
                }else{
                    unset($project_us[$k]);
            	}
                }
        $project->project_us = array_values($project_us);
    	// $project->project_us = $project_us;
     //    $project = array_values($project->project_us);
    	$flow = DB::table('flow')->where(['pro_id'=>$pro_id])->orderBy('sort','asc')->get();
    	//播报
        $broadcast = DB::table('broadcast')->where('pro_id',$pro_id)->orderBy('id','desc')->get();
        foreach ($broadcast as $key => $value) {
            $broadcast[$key]->f_name = DB::table('flow')->where('id',$value->f_id)->value('name');
            $broadcast[$key]->author = DB::table('admin_users')->where('id',$value->uid)->select('username','avatar','name')->first();
            if ($broadcast[$key]->author) {
                $broadcast[$key]->author->avatar = $this->upload.$broadcast[$key]->author->avatar;
            }
            if ($value->image) {
                $img = str_replace("[\"",'',$value->image);
                $img = str_replace("\"]",'',$img);
                $broadcast[$key]->image = $this->upload.$img;
            }
            $is_zan = DB::table('zan_log')->where('bro_id',$value->id)->where('uid',$uid)->first();
            if ($is_zan) {
                $broadcast[$key]->is_zan = 1;
            }else{
                $broadcast[$key]->is_zan = 0;
            }
        }
        // $cameras = json_decode($project->cameras,true);
        $cameras = DB::table('camera')->where('pro_id',$pro_id)->select('mac','isSupportPTZ','isSupportTalk','isSupportZoom')->get();
        if (!$cameras->isEmpty()) {
            foreach ($cameras as $k => $v) {
                $cameras[$k] = DB::table('camera')->where('mac',$v->mac)->first();
                $ys = $this->vpost('https://open.ys7.com/api/lapp/device/info','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $ys = json_decode($ys);
                $td = $this->vpost('https://open.ys7.com/api/lapp/device/camera/list','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $td = json_decode($td);
                if ($ys->code == 200) {
                    $cameras[$k]->status = $ys->data->status;
                    $cameras[$k]->defence = $ys->data->defence;
                    $cameras[$k]->isEncrypt = $ys->data->isEncrypt;
                    $cameras[$k]->alarmSoundMode = $ys->data->alarmSoundMode;
                    $cameras[$k]->offlineNotify = $ys->data->offlineNotify;
                    $cameras[$k]->videoLevel = $td->data[0]->videoLevel;
                    $cameras[$k]->cameraNo = $td->data[0]->channelNo;
                    $cameras[$k]->isShared = $td->data[0]->isShared;
                    $cameras[$k]->picUrl = $td->data[0]->picUrl;
                }
            }
        }
    	$data['flow'] = $flow;
    	$data['project'] = $project;
        $data['broadcast'] = $broadcast;
        $data['cameras'] = $cameras;
    	return response()->json(['error'=>0,'data'=>$data]);
    }

    //获取项目进度-播报多图 修改日期10/15
    public function getMyProjectFlow1015(Request $request){
          $pro_id = $request->input('pro_id');
        $uid = $request->input('uid');
        $project = DB::table('project')->where('id',$pro_id)->select('id','uid','name','z_uid','image','project_us','state','type','area','style','cameras','leader_id')->first();
        if ($project->image) {
            $project->image = $this->host.'upload/'.$project->image;
        }
        $project->headurl = DB::table('user')->where('id',$project->uid)->value('headurl');
        $project->user_name = DB::table('user')->where('id',$project->uid)->value('name');
        $project_us = json_decode($project->project_us,true);
        array_unshift($project_us,$project->leader_id);
        // return $project_us;
        foreach($project_us as $k => $v) {
                if (!$v) {
                    unset($project_us[$k]);
                    break;
                }
                $us = DB::table('admin_users')->where('id',$v)->select('name','username','job','avatar')->first();
                if ($us) {
                    switch ($us->job) {
                        case 0:
                            $us->job = '员工';
                            break;
                        case 1:
                            $us->job = '销售总监';
                            break;
                        case 2:
                            $us->job = '客户经理';
                            break;
                        case 3:
                            $us->job = '设计师';
                            break;
                        case 4:
                            $us->job = '客服';
                            break;
                        case 10:
                            $us->job = '工程总监';
                            break;
                        case 11:
                            $us->job = '项目经理';
                            break;
                        case 12:
                            $us->job = '施工人员';
                            break;
                        case 13:
                            $us->job = '工程监理';
                            break;
                       
                    }
                    $project_us[$k] = [];
                    $project_us[$k]['name'] = $us->name;
                    $project_us[$k]['phone'] = $us->username;
                    $project_us[$k]['job'] = $us->job;
                    $project_us[$k]['avatar'] = $this->upload.$us->avatar;
                }else{
                    unset($project_us[$k]);
                }
                    
                
            }
        $project->project_us = array_values($project_us);
        // $project->project_us = $project_us;
        $flow = DB::table('flow')->where(['pro_id'=>$pro_id])->orderBy('sort','asc')->get();
        //播报
        $broadcast = DB::table('broadcast')->where('pro_id',$pro_id)->orderBy('id','desc')->get();
        foreach ($broadcast as $key => $value) {
            $broadcast[$key]->f_name = DB::table('flow')->where('id',$value->f_id)->value('name');
            $broadcast[$key]->author = DB::table('admin_users')->where('id',$value->uid)->select('username','avatar','name')->first();
            if ($broadcast[$key]->author) {
                $broadcast[$key]->author->avatar = $this->upload.$broadcast[$key]->author->avatar;
            }
            if ($value->image) {
               $img = json_decode($value->image,true);
                foreach ($img as $k => $v) {
                    $img[$k] = [];
                    $img[$k] = $this->upload.$v;
                }
                $broadcast[$key]->image = $img;
            }
                
            $is_zan = DB::table('zan_log')->where('bro_id',$value->id)->where('uid',$uid)->first();
            if ($is_zan) {
                $broadcast[$key]->is_zan = 1;
            }else{
                $broadcast[$key]->is_zan = 0;
            }
        }
        // $cameras = json_decode($project->cameras,true);
        $cameras = DB::table('camera')->where('pro_id',$pro_id)->select('mac','isSupportPTZ','isSupportTalk','isSupportZoom')->get();
        if (!$cameras->isEmpty()) {
            foreach ($cameras as $k => $v) {
                $cameras[$k] = DB::table('camera')->where('mac',$v->mac)->first();
                $ys = $this->vpost('https://open.ys7.com/api/lapp/device/info','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $ys = json_decode($ys);
                $td = $this->vpost('https://open.ys7.com/api/lapp/device/camera/list','accessToken='.$this->accessToken.'&deviceSerial='.$v->mac);
                $td = json_decode($td);
                if ($ys->code == 200) {
                    $cameras[$k]->status = $ys->data->status;
                    $cameras[$k]->defence = $ys->data->defence;
                    $cameras[$k]->isEncrypt = $ys->data->isEncrypt;
                    $cameras[$k]->alarmSoundMode = $ys->data->alarmSoundMode;
                    $cameras[$k]->offlineNotify = $ys->data->offlineNotify;
                    $cameras[$k]->videoLevel = $td->data[0]->videoLevel;
                    $cameras[$k]->cameraNo = $td->data[0]->channelNo;
                    $cameras[$k]->isShared = $td->data[0]->isShared;
                    $cameras[$k]->picUrl = $td->data[0]->picUrl;
                }
            }
        }
        
        $data['flow'] = $flow;
        $data['project'] = $project;
        $data['broadcast'] = $broadcast;
        $data['cameras'] = $cameras;
        return response()->json(['error'=>0,'data'=>$data]);
    }
}
