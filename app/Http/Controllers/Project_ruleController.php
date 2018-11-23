<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Intervention\Image\ImageManagerStatic as Image;
class Project_ruleController extends Controller
{
    public function __construct(){
		$this->host = 'http://'.request()->server('HTTP_HOST').'/';
		$this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
		$this->accessToken = $this->get_accessToken();
	}
	//获取公司列表
	public function getCompanyList(Request $request){
		$list = DB::table('admin_users')->where('pid',0)->select('id','name','avatar','content')->get();
		foreach ($list as $k => $v) {
			$list[$k]->anli = 100;
			$list[$k]->gongdi = 200;
			if ($v->avatar) {
				$list[$k]->avatar = $this->upload.$v->avatar;
			}
		}
		return response()->json(['error'=>0,'data'=>$list]);
	}

	//查询我的项目
	public function myProject(Request $request){
		$uid = $request->input('uid');
		$role = $this->getRole($uid);
		if ($role != 1) {
			$z_uid = DB::table('admin_users')->where('id',$uid)->value('pid');
		}else{
			$z_uid = $uid;
		}
		
    	$project = DB::table('project')->where('z_uid',$z_uid)->select('id','uid','name','z_uid','starttime_d','image','project_us','state','type','area','leader_id','staff_share')->orderBy('id','desc')->get();
    	// return $z_uid;
    	// $leaderProject = [];
    	// $joinProject = [];
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
    		if ($uid != $v->leader_id && !in_array($uid,$project_us) && $v->staff_share == 0) {
    			unset($project[$k]);
    		}
    		
    	}
    	$project = $project->toArray();
    	$project = array_values($project);
    	// $data['leaderProject'] = $leaderProject;
    	// $data['joinProject'] = $joinProject;

    	return response()->json(['error'=>0,'data'=>$project]);

	}
	//查询项目进度
	public function getProjectFlow(Request $request){
		$pro_id = $request->input('pro_id');
		$uid = $request->input('uid');
		$project = DB::table('project')->where('id',$pro_id)->select('id','uid','name','z_uid','image','project_us','state','type','area','style','leader_id','cameras')->first();
    	if ($project->image) {
    		$project->image = $this->host.'upload/'.$project->image;
    	}
    	$project->headurl = DB::table('user')->where('id',$project->uid)->value('headurl');
        $project->user_name = DB::table('user')->where('id',$project->uid)->value('name');
        $project_us = json_decode($project->project_us,true);
        array_unshift($project_us,$project->leader_id);
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
    	//进度
    	$flow = DB::table('flow')->where(['pro_id'=>$pro_id])->orderBy('sort','asc')->get();
    	if ($project->leader_id == $uid) {
    		foreach ($flow as $k => $v) {
    			if ($v->state < 2) {
    				$flow[$k]->allow = 1;
    			}else{
    				$flow[$k]->allow = 0;
    			}
    		}
    	}
    	//播报
        $broadcast = DB::table('broadcast')->where('pro_id',$pro_id)->orderBy('id','desc')->get();
        foreach ($broadcast as $key => $value) {
            $broadcast[$key]->f_name = DB::table('flow')->where('id',$value->f_id)->value('name');
            $broadcast[$key]->author = DB::table('admin_users')->where('id',$value->uid)->select('username','avatar','name')->first();
            if ($broadcast[$key]->author) {
                $broadcast[$key]->author->avatar = $this->upload.$broadcast[$key]->author->avatar;
            }
            $img = json_decode($value->image,true);
            foreach ($img as $k => $v) {
                $img[$k] = [];
                $img[$k] = $this->upload.$v;
            }
            $broadcast[$key]->image = $img;
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
	//修改进度状态
	public function editFlowState(Request $request){
		$pro_id = $request->input('pro_id');
		$f_id = $request->input('f_id');
		$state = $request->input('state');
		$restate = DB::table('flow')->where(['pro_id'=>$pro_id,'id'=>$f_id])->value('state');
		if ($restate >= $state) {
			return response()->json(['error'=>1,'mes'=>'操作失败.']);
		}
		if ($state == 1) {
			$res = DB::table('flow')->where(['pro_id'=>$pro_id,'id'=>$f_id])->update(['starttime'=>date('Y-m-d H:i:s',time()),'state'=>$state]);
		}

		if ($state == 2) {
			$res = DB::table('flow')->where(['pro_id'=>$pro_id,'id'=>$f_id])->update(['endtime'=>date('Y-m-d H:i:s',time()),'state'=>$state]);
		}

		if ($res) {
			return response()->json(['error'=>0,'mes'=>'操作成功.']);
		}
		
	}

	//上传图片
    public function upload_broad_image(Request $request){
        $file = $request->file('image');
        $dx = $file->getClientSize();
        $size = $dx/1024/1024;
        if ($size > 2) {
        	return response()->json(['error'=>1,'mes'=>'图片超过2M.']);
        }
        if ($file) {
            $img = Image::make($file);  
            $ex = $file->getClientOriginalExtension();
            $name = time().rand(1,9).rand(1,9).".".$ex;
            $path = 'bobao/'.$name;
            $img->save('upload/'.$path);
            // $host = $request->server('HTTP_HOST');
            return response()->json(['error'=>0,'image'=>$this->upload.$path,'path'=>$path]);

        }
        return response()->json(['error'=>1,'mes'=>'上传失败']);
        
    }
	//上传播报
	public function createBroadcast(Request $request){
		$pro_id = $request->input('pro_id');
		$f_id = $request->input('f_id');
		$uid = $request->input('uid');
		$content = $request->input('content');
		$image = $request->input('image');
		$data = [
			'pro_id'=>$pro_id,
			'f_id'=>$f_id,
			'uid'=>$uid,
			'content'=>$content,
			'image'=>$image,
			'addtime'=>date('Y-m-d H:i:s',time()),
		];
		$res = DB::table('broadcast')->insert($data);
		if ($res) {
			return response()->json(['error'=>0,'mes'=>'上传成功']);
		}
	}
	

	//点赞 
	public function touchZan(Request $request){
		$bro_id = $request->input('bro_id');
		$uid = $request->input('uid');
		$type = $request->input('type',0);
		$state = $request->input('state',0);//0取消点赞 1点赞
		if ($state == 1) {
			if ($type == 1) {
				$data = [
					'bro_id'=>$bro_id,
					'admin_uid'=>$uid,
				];
				$ret = DB::table('zan_log')->where(['bro_id'=>$bro_id,'admin_uid'=>$uid])->first();
				if ($ret) {
					return response()->json(['error'=>1,'mes'=>'您已经点过赞了']);
				}
				$res = DB::table('zan_log')->insert($data);
			
			}else{
				$data = [
					'bro_id'=>$bro_id,
					'uid'=>$uid,
				];
				$ret = DB::table('zan_log')->where(['bro_id'=>$bro_id,'uid'=>$uid])->first();
				if ($ret) {
					return response()->json(['error'=>1,'mes'=>'您已经点过赞了']);
				}
				$res = DB::table('zan_log')->insert($data);
			}
			if ($res) {
				$res = DB::table('broadcast')->where('id',$bro_id)->increment('zan');
				if ($res) {
					$data = DB::table('broadcast')->where('id',$bro_id)->select('id','zan')->first();
					return response()->json(['error'=>0,'mes'=>'操作成功','data'=>$data]);
				}
			}
		}else{//取消点赞
			if ($type == 1) {
				$data = [
					'bro_id'=>$bro_id,
					'admin_uid'=>$uid,
				];
				$ret = DB::table('zan_log')->where(['bro_id'=>$bro_id,'admin_uid'=>$uid])->first();
				if ($ret) {
					DB::table('zan_log')->where(['bro_id'=>$bro_id,'admin_uid'=>$uid])->delete();
					DB::table('broadcast')->where('id',$bro_id)->decrement('zan');
				}else{
					return response()->json(['error'=>1,'mes'=>'您已经取消点赞了']);
				}
			}else{
				$data = [
					'bro_id'=>$bro_id,
					'uid'=>$uid,
				];
				$ret = DB::table('zan_log')->where(['bro_id'=>$bro_id,'uid'=>$uid])->first();
				if ($ret) {
					DB::table('zan_log')->where(['bro_id'=>$bro_id,'uid'=>$uid])->delete();
					DB::table('broadcast')->where('id',$bro_id)->decrement('zan');
				}else{
					return response()->json(['error'=>1,'mes'=>'您已经取消点赞了']);
				}
			}
			$data = DB::table('broadcast')->where('id',$bro_id)->select('id','zan')->first();
			return response()->json(['error'=>0,'mes'=>'操作成功','data'=>$data]);

		}
		
	}
    //上传播报图片
    public function uploadBroadcast(Request $request){
        $pro_id = $request->input('pro_id');
        $f_id = $request->input('f_id');
        $uid = $request->input('uid');
        $content = $request->input('content');
        $file = $request->input('image');
        $image = [];
        if (is_array($file)) {
            foreach($file as $v) {
                $v = str_replace(' ', '+', $v);
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $v, $result)){
                    $v = base64_decode(str_replace($result[1], '', $v));
                    $img = Image::make($v);  
                    // $ex = $v->getClientOriginalExtension();
                    $name = $pro_id.time().rand(1,9).rand(1,9).rand(1,9).".".$result[2];
                    $path = 'bobao/'.$name;
                    $img->save('upload/'.$path);
                    $image[] = 'bobao/'.$name;
                }
            }
        }else{
            return response()->json(['error'=>1,'mes'=>'不是文件数组']);
        }
         $data = [
            'pro_id'=>$pro_id,
            'f_id'=>$f_id,
            'uid'=>$uid,
            'content'=>$content,
            'image'=>json_encode($image),
            'addtime'=>date('Y-m-d H:i:s',time()),
        ];
        $res = DB::table('broadcast')->insert($data);
        return response()->json(['error'=>0,'mes'=>'操作成功']);
    }

    //新建项目--获取项目初始数据
    public function getProjectData(Request $request){
    	$cid = $request->input('cid');
    	$phones = DB::table('user')->select('id','phone','name')->where('cid',$cid)->get();
    	// foreach ($phones as $k => $v) {
     //        $users[$v->id] = $v->phone.'--'.$v->name;
     //    }
    	$data['users'] = $phones;
    	$staff = DB::table('admin_users')->where('pid',$cid)->select('id','username','name')->get();  
    	$data['staff'] = $staff;
    	// $ty = DB::table('user')->where('cid',$cid)->select('id','phone','name')->get(); 
    	// $data['ty'] = $ty;
    	$temp = DB::table('flow_model')->where('z_uid',$cid)->groupBy('temp')->select('temp')->get();
        foreach ($temp as $k => $v) {
            $temp[$k]->flow = DB::table('flow_model')->where('z_uid',$cid)->where('temp',$v->temp)->select('name')->orderBy('sort','asc')->get();
        }
        $flow = [];
        foreach ($temp as $k => $v) {
            foreach ($v->flow as $kk => $vv) {
                if ($kk == 0) {
                    $str = $vv->name;
                }else{
                    $str .= '->'.$vv->name;
                }
            }
            $flow[$v->temp] = '模板'.$v->temp.':'.$str;
        }
    	$data['flow'] = $flow;
    	// dd($flow);
        return response()->json(['error'=>0,'data'=>$data]);

    }


    //新建项目
    public function creatProject(Request $request){
    	$request = $request->all();
    	$name = $request['name'];
    	$uid = $request['uid'];
    	$starttime_d = $request['starttime_d'];
    	$z_uid = $request['cid'];
    	$project_us = $request['project_us'];
    	$type = $request['type'];
    	$area = $request['area'];
    	$month = $request['month'];
    	$leader_id = $request['leader_id'];		
    	$created_at = date('Y-m-d H:i:s');
    	$temp = $request['temp'];		
		$res = upload_base64_image($request['image']);
		$res = $res->getContent();
		$res = json_decode($res,true);
		// dump($res['error']);exit;
		if ($res['error'] != 0 ) {
			return response()->json(['error'=>1,'mes'=>$res['mes']]);
		}
		$image = $res['path'];
		$array = [
			'name'=>$name,
			'uid'=>$uid,
			'starttime_d'=>$starttime_d,
			'z_uid'=>$z_uid,
			'project_us'=>json_encode($project_us),
			'type'=>$type,
			'area'=>$area,
			'leader_id'=>$leader_id,
			'created_at'=>$created_at,
			'temp'=>$temp,
			'image'=>$image,
			'month'=>$month,
		];
		$id = DB::table('project')->insertGetID($array);
		$flow = DB::table('flow_model')->where(['z_uid'=>$z_uid,'temp'=>$temp])->get();
        foreach ($flow as $k => $v) {
            $data = [
                'pro_id'=>$id,
                'name'=>$v->name,
                'sort'=>$v->sort,
            ];
            DB::table('flow')->insert($data);
        }

        return response()->json(['error'=>0,'mes'=>'添加成功']);

    }

}
