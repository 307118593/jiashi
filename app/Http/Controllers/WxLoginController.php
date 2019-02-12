<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class WxLoginController extends Controller
{
	
    var $appid = 'wx092d82d9583bef76';
    var $appsecret = 'f1f761811f74af2c5a2d0112e6b745a3';
    //GET https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code
    public function wxonlogin(Request $request){
    	$code = $request->input('code');
    	$result = $this->get_openid($code);
    	if (empty($result)) {
    		return response()->json(['error'=>1,'mes'=>'请求异常.']);
    	}
    	$openid = $result['openid'];
        $headurl = $request->input('headurl');
        $name = $request->input('nickname');
        $province = $request->input('province');
        $city = $request->input('city');
        $sex = $request->input('sex');
        $sex = $sex==1?'男':'女';
        $res = DB::table('user')->where('openid',$openid)->first();
        if ($res) {//老用户
            $user = $res;
            $user->result = $result;
            return response()->json(['error'=>0,'data'=>$user]);
        }else{//新用户
            $data = [
                'name'=>$name,
                'headurl'=>$headurl,
                'province'=>$province,
                'city'=>$city,
                'sex'=>$sex,
                'openid'=>$openid,
            ];
            $uid = DB::table('user')->insertGetId($data);
            $user = DB::table('user')->where('id',$uid)->first();
            $user->result = $result;
            return response()->json(['error'=>0,'data'=>$user]);
        }
    	// return response()->json(['error'=>0,'data'=>$result]);
    }

    public function get_openid($code) {
		$url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appid."&secret=".$this->appsecret."&js_code=".$code."&grant_type=authorization_code";
		$res = $this->vpost($url,'');
		$result = json_decode($res, true);
		return $result;
	}

     //微信绑定手机号
    public function wxBindPhone(Request $request){
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
            // $co = DB::table('oauth')->where('uid',$uid)->update(['uid'=>$ret]);
            // if ($co) {
            //     $head = DB::table('user')->where('phone',$phone)->value('headurl');
            //     if (!$head) {
            //         $headurl = DB::table('user')->where('id',$uid)->value('headurl');
            //         DB::table('user')->where('phone',$phone)->update(['headurl'=>$headurl]);
            //     }
            //     DB::table('user')->where('id',$uid)->delete();
            //     DB::table('user')->where('id',$ret)->update(['cid'=>$cid]);
            //     $user = DB::table('user')->where('id',$ret)->first();
            //     if ($user->is_wx == 1) {
            //         $user->wechat_name = DB::table('oauth')->where('uid',$user->id)->value('name');
            //     }
            //     $user->isvip = getComRole($user->cid);
            //     return response()->json(['error'=>0,'mes'=>'绑定成功,您可通过手机号和密码登陆','data'=>$user]);
            // }
            $openid = DB::table('user')->where('id',$uid)->value('openid');
            $co = DB::table('user')->where('phone',$phone)->update(['openid'=>$openid]);
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
}