<?php
namespace App\Http\Controllers\wechat;
use Illuminate\Http\Request;
use App\Http\Controllers\Redirect;
use DB;
use Session;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use App\Http\Controllers\Controller;
class WeixinController
{
	public function __construct(){
		$this->app = Factory::officialAccount(config('wechat.official_account.default'));
        $this->host = 'http://'.request()->server('HTTP_HOST').'/';
        $this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
    
	}

	public function jssdk(){
		$url = request('url');
		$app = $this->app;
		// return $app;
		$js = $app->jssdk->buildConfig(array('checkJsApi','updateAppMessageShareData', 'updateTimelineShareData'), $debug = true, $beta = false, $json = false,$url);
		return $js;
	}

	public function oauth(Request $request){
		$targetUrl = $request->input('targetUrl');
		session_start();
		$_SESSION['targetUrl']=$targetUrl;
		// session(['targetUrl'=>$targetUrl]);
		// session::save();
		// print(session('targetUrl')) ;
		// dd(session('targetUrl'));
		$app = $this->app;
		
        // 未登录
		// if (empty($_SESSION['wechat_user'])) {
			$response = $app->oauth->scopes(['snsapi_userinfo'])
          	->redirect();
        	return $response;
		// }
		// header('location:'. $targetUrl);
	}
	public function oauth_back(Request $reuqest){
		// dd(session('targetUrl'));
		session_start();
		// dd($_SESSION['targetUrl']);
		// dd(session());
		$app = $this->app;
		$oauth = $app->oauth;
		$user = $oauth->user()->getOriginal();
		// $user = json_decode($user);
		// return $user['unionid'];
		$_SESSION['wechat_user']=$user;
		$unionid = $user['unionid'];
        //公司端登陆
        if ($_SESSION['type'] == 1) {
            $res = DB::table('admin_users')->where('unionid',$unionid)->first();
            if ($res) {//老用户
                // return response()->json(['error'=>0,'data'=>$res]);
                $uid = DB::table('admin_users')->where('unionid',$unionid)->value('id');
            }else{
                $array = [
                    'unionid'=>$unionid,
                    'name'=>$user['nickname'],
                    'pid'=>2,
                    //'avatar'=>$user['headimgurl'],
                    // 'province'=>$user['province'],
                    // 'city'=>$user['city'],
                    // 'sex'=>$user['sex'],
                    'created_at'=>date('Y-m-d H:i:s'),
                ];
                $uid = DB::table('admin_users')->insertGetId($array);
                $res = DB::table('admin_users')->where('id',$uid)->first();

                
                // return response()->json(['error'=>0,'data'=>$res]);
            }
        }else{
            $res = DB::table('user')->where('unionid',$unionid)->first();
            if ($res) {//老用户
                // return response()->json(['error'=>0,'data'=>$res]);
                $uid = DB::table('user')->where('unionid',$unionid)->value('id');
            }else{
                $array = [
                    'unionid'=>$unionid,
                    'name'=>$user['nickname'],
                    'headurl'=>$user['headimgurl'],
                    'province'=>$user['province'],
                    'city'=>$user['city'],
                    'sex'=>$user['sex'],
                    'addtime'=>date('Y-m-d H:i:s'),
                ];
                $uid = DB::table('user')->insertGetId($array);
                $res = DB::table('user')->where('id',$uid)->first();

                
                // return response()->json(['error'=>0,'data'=>$res]);
            }
        }


		
		// return '555';
			$targetUrl = $_SESSION['targetUrl'].'?uid='.$uid;
			// return $targetUrl;
		return \Redirect::to($targetUrl, 301);
		
	}

	     //微信绑定手机号
    public function wxMpBindPhone(Request $request){
        $uid = $request->input('uid'); 
        $phone = $request->input('phone');
        $code = $request->input('code');
        $invitation = $request->input('invitation',0);
        //邀请码
        $cid = 0;
        $invitation = $invitation-1000;
        $ret = DB::table('admin_users')->where('id',$invitation)->where('pid',0)->first();
        if ($ret) {
            $cid = $invitation;
        }
        // $password = $request->input('password');
        // $password = bcrypt($password);
        $ralcode = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('code');
        $nowtime = time();
        $losetime = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('time')+10*60;
        if ($nowtime > $losetime) {
            return response()->json(['error'=>1,'mes'=>'验证码过期.']);
        }
        if ($code != $ralcode) {
            return response()->json(['error'=>1,'mes'=>'验证码错误.']);
        }
        $ret = DB::table('user')->where('phone',$phone)->value('id');
        if ($ret) {//老用户 迁移openid
            $unionid = DB::table('user')->where('id',$uid)->value('unionid');
            $co = DB::table('user')->where('phone',$phone)->update(['unionid'=>$unionid]);
            if ($co) {
                DB::table('user')->where('id',$uid)->delete();
            }
            $user = DB::table('user')->where('phone',$phone)->first();
            $user->isvip = getComRole($user->cid);
            return response()->json(['error'=>0,'mes'=>'绑定成功,已于账号数据关联','data'=>$user]);
        }else{//新用户
            $pwd = substr($phone,-6);
            $password = \Hash::make($pwd);
            $array=array(
                'phone'=>$phone,
                'password'=>$password,
                'cid'=>$cid,
            );
            $res = DB::table('user')->where('id',$uid)->update($array);
            if ($res) {
                $user = DB::table('user')->where('id',$uid)->first();
                $user->pwd = $pwd;
                $user->isvip = getComRole($user->cid);
                return response()->json(['error'=>0,'mes'=>'绑定成功,您也可通过手机号和密码登陆APP','data'=>$user]);
            }
        }
        
    }

//公司端公众号登陆模块
    public function Com_oauth(Request $request){
        $targetUrl = $request->input('targetUrl');
        $type = 1;
        session_start();
        $_SESSION['targetUrl']=$targetUrl;
        $_SESSION['type']=$type;
        // session(['targetUrl'=>$targetUrl]);
        // session::save();
        // print(session('targetUrl')) ;
        // dd(session('targetUrl'));
        $app = $this->app;
        
        // 未登录
        // if (empty($_SESSION['wechat_user'])) {
            $response = $app->oauth->scopes(['snsapi_userinfo'])
            ->redirect();
            return $response;
        // }
        // header('location:'. $targetUrl);
    }
    public function Com_oauth_back(Request $reuqest){
        // dd(session('targetUrl'));
        session_start();
        // dd($_SESSION['targetUrl']);
        // dd(session());
        $app = $this->app;
        $oauth = $app->oauth;
        $user = $oauth->user()->getOriginal();
        // $user = json_decode($user);
        // return $user['unionid'];
        $_SESSION['wechat_user']=$user;
        $unionid = $user['unionid'];
        $res = DB::table('admin_users')->where('unionid',$unionid)->first();
        if ($res) {//老用户
            // return response()->json(['error'=>0,'data'=>$res]);
            $uid = DB::table('admin_users')->where('unionid',$unionid)->value('id');
        }else{
            $array = [
                'unionid'=>$unionid,
                'name'=>$user['nickname'],
                //'avatar'=>$user['headimgurl'],
                // 'province'=>$user['province'],
                // 'city'=>$user['city'],
                // 'sex'=>$user['sex'],
                'created_at'=>date('Y-m-d H:i:s'),
            ];
            $uid = DB::table('admin_users')->insertGetId($array);
            $res = DB::table('admin_users')->where('id',$uid)->first();

            
            // return response()->json(['error'=>0,'data'=>$res]);
        }

        return $uid;
            $targetUrl = $_SESSION['targetUrl'].'?uid='.$uid;
            // return $targetUrl;
        return \Redirect::to($targetUrl, 301);
        
    }

    public function Com_BindCount(Request $request){
        $uid = $request->input('uid'); 
        $phone = $request->input('phone');
        $code = $request->input('code');
        // $invitation = $request->input('invitation',0);
        //邀请码
        // $cid = 0;
        // $invitation = $invitation-1000;
        // $ret = DB::table('admin_users')->where('id',$invitation)->where('pid',0)->first();
        // if ($ret) {
        //     $cid = $invitation;
        // }
        // $password = $request->input('password');
        // $password = bcrypt($password);
        $ralcode = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('code');
        $nowtime = time();
        $losetime = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('time')+10*60;
        if ($nowtime > $losetime) {
            return response()->json(['error'=>1,'mes'=>'验证码过期.']);
        }
        if ($code != $ralcode) {
            return response()->json(['error'=>1,'mes'=>'验证码错误.']);
        }
        $ret = DB::table('admin_users')->where('username',$phone)->value('id');
            // return response()->json(['error'=>1,'mes'=>'验证码错误.']);
        if ($ret) {//老用户 迁移openid
            $unionid = DB::table('admin_users')->where('id',$uid)->value('unionid');
            if ($unionid) {
                $co = DB::table('admin_users')->where('username',$phone)->update(['unionid'=>$unionid]);
                if ($co) {
                    DB::table('admin_users')->where('id',$uid)->delete();
                }
                $user = DB::table('admin_users')->where('username',$phone)->first();
                // $user->isvip = getComRole($user->cid);
                return response()->json(['error'=>0,'mes'=>'绑定成功,已于账号数据关联','data'=>$user]);
            }else{
                $user = DB::table('admin_users')->where('username',$phone)->first();
                return response()->json(['error'=>1,'mes'=>'您已绑定过改账号了','data'=>$user]);
            }
            
            
        }else{//新用户
            return response()->json(['error'=>1,'mes'=>'错误,这不是员工账号.']);
            // $pwd = substr($phone,-6);
            // $password = \Hash::make($pwd);
            // $array=array(
            //     'phone'=>$phone,
            //     'password'=>$password,
            // );
            // $res = DB::table('admin_users')->where('id',$uid)->update($array);
            // if ($res) {
            //     $user = DB::table('admin_users')->where('id',$uid)->first();
            //     $user->pwd = $pwd;
            //     $user->isvip = getComRole($user->cid);
            //     return response()->json(['error'=>0,'mes'=>'绑定成功,您也可通过手机号和密码登陆APP','data'=>$user]);
            // }
        }
    }


    //微信公众号首页
    public function ComMPIdnex(Request $request){
        $cid = $request->input('cid');
        $uid = $request->input('uid');
        //最近的未读消息
        $data =[];
        $message = DB::table('messages_user')->where('touser',$uid)->where('is_read',0)->where('is_del',0)->orderBy('id','desc')->first();
        // return $message;
        if ($message) {
           $detail = DB::table('messages')->where('id',$message->mid)->first();
           if ($detail) {
                $message->title = $detail->title;
                $message->content = $detail->content;
                $message->url = $detail->url;
            }
        }
        
        
        $data['newMessage'] = $message;

        //最近添加的客户
        $data['newUser'] = DB::table('user')->where('cid',$cid)->orderBy('id','desc')->take(3)->get();

        //最近编辑的项目
        $project = DB::table('flow as a')->leftjoin('project as b','b.id','a.pro_id')->select('b.id','b.image','b.name','b.leader_id','a.name as flow_name','a.endtime as time','a.starttime as time')->orderBy('a.starttime','desc')->orderBy('a.endtime','desc')->take(3)->get();
        // $host = new Controller;
        foreach ($project as $k => $v) {
            $project[$k]->leader = DB::table('admin_users')->where('id',$v->leader_id)->value('name');
            $project[$k]->image = $this->upload.$v->image;
            // if ($v) {
            //     # code...
            // }
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

        $data['project'] =$project;
        return response()->json(['error'=>0,'data'=>$data]);
    }

}
