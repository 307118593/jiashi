<?php

namespace App\Http\Controllers;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Http\Request;
use DB;
class LoginController extends Controller
{
    
    public function __construct(){
        $this->host = 'http://'.request()->server('HTTP_HOST').'/';
    }
    //后台跳转   路由缓存需要
    public function toadmin(){
        // dd(\Request::url());
        // if (\Request::url() == "http://www.homeeyes.cn" || \Request::url() == "http://homeeyes.cn") {
        if(in_array(\Request::url(), ["http://www.homeeyes.cn","http://homeeyes.cn","https://www.homeeyes.cn","https://homeeyes.cn"])){    
            return Redirect('/HomeEye/index.html');
        }
        return Redirect('admin');
    }

    public function getUserInfo(Request $request){
        $uid = $request->input('uid');
        $pid = $request->input('pid',-2);
        $DeviceToken = $request->input('DeviceToken');
        if ($pid >= 0) {
            $info = DB::table('admin_users')->where('id',$uid)->first();
            if ($info->avatar) {
                $info->avatar = $this->host.'upload/'.$info->avatar;
            }
            $info->role = $this->getRole($info->id);
            if ($info->pid == -1) {
                $info->cid = -1;
            }else if ($info->pid == 0) {
                $info->cid = $info->id;
            }else{
                $info->cid = $info->pid;
            }
            $info->companyname = DB::table('admin_users')->where('id',$info->cid)->value('name');
            $jobs = [1=>'销售总监',2=>'客户经理',3=>'设计师',4=>'客服',10=>'工程总监',11=>'项目经理',12=>'施工人员',13=>'工程监理',];
            $info->job = $jobs[$info->job];
            return response()->json(['error'=>0,'data'=>$info]);
        }
        if ($DeviceToken) {
            DB::table('user')->where('id',$uid)->update(['DeviceToken'=>$DeviceToken]);
        }
        $info = DB::table('user')->where('id',$uid)->first();
        $info->newMes = DB::table('messages_user')->where('uid',$info->id)->where('is_read',0)->where('is_del',0)->count();
            if ($info->is_wx == 1) {
                $info->wechat_name = DB::table('oauth')->where('uid',$info->id)->value('name');
            }
        $info->isvip = getComRole($info->cid);
        return response()->json(['error'=>0,'data'=>$info]);
    }

    
	//手机号发送短信
    public function send_mes(Request $request){//type =2 时..手机号快捷登录,绑定手机号
    	$phone = $request->input('phone');
    	if (!$phone) {
    		return response()->json(['error'=>1,'mes'=>'请输入手机号.']);
    	}
    	$type = $request->input('type',0);
    	$re = DB::table('user')->where('phone',$phone)->first();
    	if ($type == 0) {//注册时
	    	if ($re) {
	    		return response()->json(['error'=>1,'mes'=>'手机号已被注册.']);
	    	}
    	}else if($type==1){//修改密码时  1106//手机号快捷登录.取消新用户快捷登录
    		if (!$re) {
	    		return response()->json(['error'=>1,'mes'=>'手机号未被注册.请前往注册!']);
	    	}
    	}
    	$count = DB::table('message_log')->where('phone',$phone)->where('sendtime','>=',date("Y-m-d"))->count();
    	if ($count > 10) {
    		return response()->json(['error'=>1,'mes'=>'短信发送已超出限制.']);
    	}
    	$code = rand(1000,9999);
    	$time = 10;
    	$tempId=273344;
    	$data = [$code,$time];
    	$this->sendtemp($phone,$data,$tempId);
    	$array = [
    		'phone'=>$phone,
    		'code'=>$code,
    		'sendtime'=>date('Y-m-d H:i:s',time()),
    		'time'=>time()
    	];
    	$res = DB::table('message_log')->insert($array);
    	if ($res) {
    		$datas['code'] = $code;
    		$datas['losetime'] = time()+$time*60;
    		return response()->json(['error'=>0,'data'=>$datas]);
    	}
    }

    //注册
    public function register(Request $request){
    	$phone = $request->input('phone');
        $code = $request->input('code');
    	$invitation = $request->input('invitation',0);
    	if (!$phone) {
    		return response()->json(['error'=>1,'mes'=>'请输入手机号.']);
    	}
    	$re = DB::table('user')->where('phone',$phone)->first();
    	if ($re) {
	    	return response()->json(['error'=>1,'mes'=>'手机号已被注册.']);
	    }
	    $ralcode = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('code');
	    $nowtime = time();
	    $losetime = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('time')+10*60;
	    if ($nowtime > $losetime) {
	    	return response()->json(['error'=>1,'mes'=>'验证码过期.']);
	    }
	    if ($code != $ralcode) {
	    	return response()->json(['error'=>1,'mes'=>'验证码错误.']);
	    }
    	$password = $request->input('password');
    	$password = bcrypt($password);
    	$name = '用户-'.$phone;

        //邀请码
        $cid = 0;
        $invitation = $invitation-1000;
        $ret = DB::table('admin_users')->where('id',$invitation)->where('pid',0)->first();
        if ($ret) {
            $cid = $invitation;
        }
    	$array = array(
    		'phone'=>$phone,
    		'password'=>$password,
            'name'=>$name,
            'headurl'=>'http://47.97.109.9/upload/images/touxiang.jpg',
    		'cid'=>$cid,
    		'addtime'=>date('Y-m-d H:i:s',time()),
    	);
    	$uid = DB::table('user')->insertGetId($array);
    	if ($uid) {
            $this->vpost('http://47.97.109.9/api/openApp','uid='.$uid); 
    		$user = DB::table('user')->where('phone',$phone)->first();
            $user->isvip = getComRole($user->cid);
    		return response()->json(['error'=>0,'mes'=>'注册成功','data'=>$user]);
    	}
    }

    // 添加邀请码
    public function add_invitation(Request $request){
        $uid = $request->input('uid');
        $invitation = $request->input('invitation');
        $invitation = $invitation-1000;
        $cname = DB::table('admin_users')->where('id',$invitation)->where('pid',0)->value('name');
        if (!$cname) {
            return response()->json(['error'=>1,'mes'=>'您输入的邀请码有误.']);
        }
        
        DB::table('user')->where('id',$uid)->update(['cid'=>$invitation]);
        $user = DB::table('user')->where('id',$uid)->first();
        $user->newMes = DB::table('messages_user')->where('uid',$user->id)->where('is_read',0)->where('is_del',0)->count();

        if ($user->is_wx == 1) {
            $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
        }
            $user->isvip = getComRole($user->cid);
        return response()->json(['error'=>0,'data'=>$user]);

    }

    //登陆
    public function login(Request $request){
    	$phone = $request->input('phone');
        $password = $request->input('password');
    	$type = $request->input('type',0);
        // $res = DB::table('admin_users')->where('username',$phone)->first();
        // if (!empty($res)) {
        //     $type = 1;
        // }
        if ($type == 1) {//装修公司登陆
            $relpassword = DB::table('admin_users')->where('username',$phone)->value('password');
            if (\Hash::check($password, $relpassword)) {
                DB::table('admin_users')->where('username',$phone)->update(['updated_at'=>date('Y-m-d H:i:s',time())]);
                $user = DB::table('admin_users')->where('username',$phone)->first();
                if ($user->avatar) {
                    $user->avatar = $this->host.'upload/'.$user->avatar;
                }
                $user->role = $this->getRole($user->id);
                if ($user->pid == -1) {
                    $user->cid = -1;
                }else if ($user->pid == 0) {
                    $user->cid = $user->id;
                }else{
                    $user->cid = $user->pid;
                }
                return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
            }else{
                return response()->json(['error'=>1,'mes'=>'账号或密码错误']);
            }
        }

    	$relpassword = DB::table('user')->where('phone',$phone)->value('password');
    	if (\Hash::check($password, $relpassword)) {
    		DB::table('user')->where('phone',$phone)->update(['uptime'=>date('Y-m-d H:i:s',time())]);
    		$user = DB::table('user')->where('phone',$phone)->first();
            $user->newMes = DB::table('messages_user')->where('uid',$user->id)->where('is_read',0)->where('is_del',0)->count();

            if ($user->is_wx == 1) {
                $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
            }
            $user->isvip = getComRole($user->cid);
			return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
		}else{
			return response()->json(['error'=>1,'mes'=>'账号或密码错误']);
		}
    }

      //登陆
    public function login1026(Request $request){
        $phone = $request->input('phone');
        $password = $request->input('password');
        $type = $request->input('type',0);
        // $res = DB::table('admin_users')->where('username',$phone)->first();
        // if (!empty($res)) {
        //     $type = 1;
        // }
        if ($type == 1) {//装修公司登陆
            $relpassword = DB::table('admin_users')->where('username',$phone)->value('password');
            if (\Hash::check($password, $relpassword)) {
                DB::table('admin_users')->where('username',$phone)->update(['updated_at'=>date('Y-m-d H:i:s',time())]);
                $user = DB::table('admin_users')->where('username',$phone)->first();
                if ($user->avatar) {
                    $user->avatar = $this->host.'upload/'.$user->avatar;
                }
                $user->role = $this->getRole($user->id);
                if ($user->pid == -1) {
                    $user->cid = -1;
                }else if ($user->pid == 0) {
                    $user->cid = $user->id;
                }else{
                    $user->cid = $user->pid;
                }
                return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
            }else{
                return response()->json(['error'=>1,'mes'=>'账号或密码错误']);
            }
        }

        $relpassword = DB::table('user')->where('phone',$phone)->value('password');
        if (\Hash::check($password, $relpassword)) {
            DB::table('user')->where('phone',$phone)->update(['uptime'=>date('Y-m-d H:i:s',time())]);
            $user = DB::table('user')->where('phone',$phone)->first();
            $user->newMes = DB::table('messages_user')->where('uid',$user->id)->where('is_read',0)->where('is_del',0)->count();

            if ($user->is_wx == 1) {
                $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
            }
            $this->vpost('http://47.97.109.9/api/openApp','uid='.$user->id); 
            $user->isvip = getComRole($user->cid);
            return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
        }else{
            return response()->json(['error'=>1,'mes'=>'账号或密码错误']);
        }
    }

    //手机号快捷登陆
    public function phone_login(Request $request){
        $phone = $request->input('phone');
        $code = $request->input('code');
        $invitation = $request->input('invitation',0);
        $address = $request->input('address','');
        $ralcode = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('code');
        $nowtime = time();
        $losetime = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('time')+10*60;
        if ($nowtime > $losetime) {
            return response()->json(['error'=>1,'mes'=>'验证码过期.']);
        }
        if ($code != $ralcode) {
            return response()->json(['error'=>1,'mes'=>'验证码错误.']);
        }

        $user = DB::table('user')->where('phone',$phone)->first();
        if ($user) {//老用户
            DB::table('user')->where('phone',$phone)->update(['uptime'=>date('Y-m-d H:i:s',time())]);
             if ($user->is_wx == 1) {
                $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
            }
            $user->newMes = DB::table('messages_user')->where('uid',$user->id)->where('is_read',0)->where('is_del',0)->count();
            $user->isvip = getComRole($user->cid);
            return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
        }else{
            //邀请码
            $cid = 0;
            $invitation = $invitation-1000;
            $ret = DB::table('admin_users')->where('id',$invitation)->where('pid',0)->first();
            if ($ret) {
                $cid = $invitation;
            }
            $name = '用户-'.$phone;
            $pwd = substr($phone,-6);
            $password = \Hash::make($pwd);
            $array = array(
                'phone'=>$phone,
                'password'=>$password,
                'name'=>$name,
                'cid'=>$cid,
                'address'=>$address,
                'addtime'=>date('Y-m-d H:i:s',time()),
            );
            $uid = DB::table('user')->insertGetId($array);
            if ($uid) {
            $this->vpost('http://47.97.109.9/api/openApp','uid='.$uid); 
                $user = DB::table('user')->where('id',$uid)->first();
                $user->pwd = $pwd;
                $user->newMes = DB::table('messages_user')->where('uid',$user->id)->where('is_read',0)->where('is_del',0)->count();
                $user->isvip = getComRole($user->cid);
                return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
            }
        }
       

    }

    //重置密码
    public function reset_password(Request $request){
    	$phone = $request->input('phone');
    	$password = $request->input('password');
    	$password = bcrypt($password);
    	$res = DB::table('user')->where('phone',$phone)->update(['password'=>$password]);
    	if ($res) {
    		$user = DB::table('user')->where('phone',$phone)->first();
             if ($user->is_wx == 1) {
                $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
            }
            $user->isvip = getComRole($user->cid);
    		return response()->json(['error'=>0,'mes'=>'修改成功,请使用新密码登陆','data'=>$user]);
    	}
    }

    public function sendtemp($phone,$data,$tempId){
    	require_once(__DIR__."/../../../vendor/CCPRestSmsSDK.php");
    	//主帐号,对应开官网发者主账号下的 ACCOUNT SID
		$accountSid= '8a216da8552a3cd401554350785e1294';
		//主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
		$accountToken= 'c252a0d026534424a2154aedc5c7fec9';
		//应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
		//在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
		$appId='8a216da864da60ef0164db1cc221016f';
		//请求地址
		//沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
		//生产环境（用户应用上线使用）：app.cloopen.com
		$serverIP='app.cloopen.com';
		//请求端口，生产环境和沙盒环境一致
		$serverPort='8883';
		//REST版本号，在官网文档REST介绍中获得。
		$softVersion='2013-12-26';
	     $rest = new \REST($serverIP,$serverPort,$softVersion);
	     $rest->setAccount($accountSid,$accountToken);
	     $rest->setAppId($appId);
	    
	     // 发送模板短信
	     // echo "Sending TemplateSMS to $phone <br/>";
	     // $phone = '13032279323';
	     // $data=array(rand(100000,999999),10);
	     // $tempId = '273344';
	     $result = $rest->sendTemplateSMS($phone,$data,$tempId);
	     if($result == NULL ) {
	         echo "result error!";
	         return 0;
	     }
	     if($result->statusCode!=0) {
	         echo "error code :" . $result->statusCode . "<br>";
	         echo "error msg :" . $result->statusMsg . "<br>";
	         //TODO 添加错误处理逻辑
	     }else{
	         // echo "Sendind TemplateSMS success!<br/>";
	         // // 获取返回信息
	         // $smsmessage = $result->TemplateSMS;
	         // echo "dateCreated:".$smsmessage->dateCreated."<br/>";
	         // echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
	     	return 1;
	         //TODO 添加成功处理逻辑
	     }
    }

    //微信登陆
    public function wx_login(Request $request){
        $type = $request->input('type',0);
    	$request = $request->all();
        if ($type == 1) {
            $ret = DB::table('oauth_admin')->where('openid',$request['openid'])->value('uid');
            if (empty($ret)) {
                return response()->json(['error'=>1,'mes'=>'没有找到用户.请先绑定微信']);
            }else{
                $user = DB::table('admin_users')->where('id',$ret)->first();
                if ($user->avatar) {
                    $user->avatar = $this->host.'upload/'.$user->avatar;
                    $user->isvip = getComRole($user->cid);
                    return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
                }
            }
        }

    	$array = array(
    		'name'=>$request['name'],
	    	'headurl'=>$request['headurl'],
    		'addtime'=>date('Y-m-d H:i:s',time()),
            'sex'=>$request['sex'],
            'province'=>$request['province'],
            'city'=>$request['city'],
    	);
    	$ret = DB::table('oauth')->where('openid',$request['openid'])->value('uid');
    	if (empty($ret)) {//新用户
    		$uid = DB::table('user')->insertGetId($array);
    		if ($uid) {
	    		$data = array(
	    			'uid'=>$uid,
	    			'type'=>1,
	    			'openid'=>$request['openid'],
                    'name'=>$request['name'],
	    		);
	    		$re = DB::table('oauth')->insert($data);
	    		if ($re) {
                    DB::table('user')->where('id',$uid)->update(['is_wx'=>1]);
	    			$user = DB::table('user')->where('id',$uid)->first();
                    $user->newMes = DB::table('messages_user')->where('uid',$user->id)->where('is_read',0)->where('is_del',0)->count();
                    $user->isvip = getComRole($user->cid);
	    			return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
	    		}
	    	}
    	}else{//老用户
    		DB::table('user')->where('id',$ret)->update(['uptime'=>date('Y-m-d H:i:s',time())]);
    		$user = DB::table('user')->where('id',$ret)->first();
            if ($user->is_wx == 1) {
                $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
            }
            $user->newMes = DB::table('messages_user')->where('uid',$user->id)->where('is_read',0)->where('is_del',0)->count();
            $user->isvip = getComRole($user->cid);
	    	return response()->json(['error'=>0,'mes'=>'登陆成功','data'=>$user]);
    	}
    	
    	

    }


    //设置密码
    public function edit_password(Request $request){
        $uid = $request->input('uid'); 
        $phone = $request->input('phone');
        $code = $request->input('code');
        $password = $request->input('password');
        $password = bcrypt($password);
        $ralcode = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('code');
        $nowtime = time();
        $losetime = DB::table('message_log')->where('phone',$phone)->orderBy('id','desc')->value('time')+10*60;
        if ($nowtime > $losetime) {
            return response()->json(['error'=>1,'mes'=>'验证码过期.']);
        }
        if ($code != $ralcode) {
            return response()->json(['error'=>1,'mes'=>'验证码错误.']);
        }
        $res = DB::table('user')->where('id',$uid)->update(['password'=>$password]);
        if ($res) {
            return response()->json(['error'=>0,'mes'=>'设置成功,您可通过手机号和密码登陆']);
        }

    }

    //绑定手机号
    public function bind_phone(Request $request){
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
        if ($ret) {
            $co = DB::table('oauth')->where('uid',$uid)->update(['uid'=>$ret]);
            if ($co) {
                $head = DB::table('user')->where('phone',$phone)->value('headurl');
                if (!$head) {
                    $headurl = DB::table('user')->where('id',$uid)->value('headurl');
                    DB::table('user')->where('phone',$phone)->update(['headurl'=>$headurl]);
                }
                DB::table('user')->where('id',$uid)->delete();
                DB::table('user')->where('id',$ret)->update(['cid'=>$cid]);
                $user = DB::table('user')->where('id',$ret)->first();
                if ($user->is_wx == 1) {
                    $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
                }
                $user->isvip = getComRole($user->cid);
                return response()->json(['error'=>0,'mes'=>'绑定成功,您可通过手机号和密码登陆','data'=>$user]);
            }
        }else{
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
                if ($user->is_wx == 1) {
                    $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
                }
                $user->isvip = getComRole($user->cid);
                return response()->json(['error'=>0,'mes'=>'绑定成功,您可通过手机号和密码登陆','data'=>$user]);
            }
        }
    	
    }



    //绑定微信
    public function bind_wx(Request $request){
        $uid = $request->input('uid');
        $openid = $request->input('openid');
        $name = $request->input('name');
        $res = DB::table('oauth')->where(['type'=>1,'uid'=>$uid])->first();
        if ($res) {
            return response()->json(['error'=>1,'mes'=>'您已绑定过微信.']);
        }
        $res = DB::table('oauth')->where(['type'=>1,'openid'=>$openid])->first();
        if ($res) {
            return response()->json(['error'=>1,'mes'=>'操作失败,此微信号已被绑定.']);
        }
        $array = [
            'uid'=>$uid,
            'type'=>1,
            'openid'=>$openid,
            'name'=>$name,
        ];
        DB::table('oauth')->insert($array);
        DB::table('user')->where('id',$uid)->update(['is_wx'=>1]);
        return response()->json(['error'=>0,'mes'=>'操作成功.您可以使用微信号登陆啦']);
    }

    //解除绑定微信
    public function remove_wx(Request $request){
        $uid = $request->input('uid');
        $res = DB::table('oauth')->where(['type'=>1,'uid'=>$uid])->first();
        if (empty($res)) {
            return response()->json(['error'=>1,'mes'=>'您还未绑定微信.']);
        }
        $res = DB::table('oauth')->where(['uid'=>$uid,'type'=>1])->delete();
        DB::table('user')->where('id',$uid)->update(['is_wx'=>0]);
        if ($res) {
            return response()->json(['error'=>0,'mes'=>'操作成功.']);
        }
    }


    //更改手机号
    public function change_phone(Request $request){
        $uid = $request->input('uid');
        $phone = $request->input('phone');
        $code = $request->input('code');
        $new_phone = $request->input('new_phone');
        $ralcode = DB::table('message_log')->where('phone',$new_phone)->orderBy('id','desc')->value('code');
        $nowtime = time();
        $losetime = DB::table('message_log')->where('phone',$new_phone)->orderBy('id','desc')->value('time')+10*60;
        if ($nowtime > $losetime) {
            return response()->json(['error'=>1,'mes'=>'验证码过期.']);
        }
        if ($code != $ralcode) {
            return response()->json(['error'=>1,'mes'=>'验证码错误.']);
        }
        $ret = DB::table('user')->where('phone',$new_phone)->first();
        if ($ret) {
            return response()->json(['error'=>1,'mes'=>'该手机号已被注册.']);
        }
        $res = DB::table('user')->where(['id'=>$uid,'phone'=>$phone])->update(['phone'=>$new_phone]);
        if ($res) {
            $user = DB::table('user')->where('id',$uid)->first();
                if ($user->is_wx == 1) {
                    $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
                }
            return response()->json(['error'=>0,'mes'=>'修改成功,请使用新手机号重新登录.','data'=>$user]);
        }
    }

    //修改用户信息
    public function edit_userinfo(Request $request){
    	$uid = $request->input('uid');
    	$data = array(
            'name'=>$request->input('name'),
            // 'headurl'=>$request->input('headurl'),
            'sex'=>$request->input('sex'),
            'address'=>$request->input('address',''),
        );
    	$res = DB::table('user')->where('id',$uid)->update($data);
    	if ($res) {
            $user = DB::table('user')->where('id',$uid)->first();
            if ($user->is_wx == 1) {
                $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
            }
    		return response()->json(['error'=>0,'data'=>$user]);
    	}
    }
    //上传头像
    public function upload_head(Request $request){
        $file = $request->file('headurl');
        $uid = $request->input('uid');
        if ($file) {
            $img = Image::make($file);  
            $ex = $file->getClientOriginalExtension();
            $name = $uid.".".$ex;
            $path = 'headurl/'.$name;
            if(file_exists($path)){
                unlink($path);
            }
            $img->save($path);
            $host = $request->server('HTTP_HOST');
            $headurl = 'http://'.$host.'/'.$path;
            DB::table('user')->where('id',$uid)->update(['headurl'=>$headurl]);
            return response()->json(['error'=>0,'hesdurl'=>'http://'.$host.'/'.$path,'path'=>$path]);

        }
        return response()->json(['error'=>1,'mes'=>'上传失败,未接收到图片..']);
        
    }
    // 确认上传
    public function confirm_upload(Request $request){
        $uid = $request->input('uid');
        $path = $request->input('path');
        $res = DB::table('user')->where('id',$uid)->update(['headurl'=>$path]);
        if ($res) {
            return response()->json(['error'=>0,'mes'=>'保存成功']);
        }
    }

    // 后台客户转为员工
    public function changestaff(Request $request){
        $uid = $request->input('id');
        $user = DB::table('user')->where('id',$uid)->first();
        $data = [
            'username'=>$user->phone,
            'password'=>$user->password,
            'name'=>$user->name,
            'pid'=>$user->cid,
            'created_at'=>date('Y-m-d H:i:s'),
        ];
        $res = DB::table('admin_users')->insert($data);
        DB::table('user')->where('id',$uid)->update(['is_copy'=>1]);
        // DB::table('user')->where('id',$uid)->delete();
        // DB::table('oauth')->where('uid',$uid)->delete();
        return response()->json(['error'=>0,'mes'=>'操作成功']);
    }

     // 后台员工转为客户
    public function changeuser(Request $request){
        $uid = $request->input('id');
        $user = DB::table('admin_users')->where('id',$uid)->first();
        $data = [
            'phone'=>$user->username,
            'password'=>$user->password,
            'name'=>$user->name,
            'cid'=>$user->pid,
            'addtime'=>date('Y-m-d H:i:s'),
            'is_copy'=>1,
        ];
        $res = DB::table('user')->insert($data);
        // DB::table('admin_users')->where('id',$uid)->delete();
        // DB::table('oauth')->where('uid',$uid)->delete();
        return response()->json(['error'=>0,'mes'=>'操作成功']);
    }



}
