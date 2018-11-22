<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DB;
use Illuminate\Http\Request;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    private $appKey = '89c993d3f10843fea1a6d02d9653b36e';
    private $appSecret = 'a9db3ba88639d5833ff41c5e9df52b78';
    //友盟推送
    public $Appkey = '5b598f75f29d98513c0000c3';
    private $UmengMessageSecret = 'b49db5518f933098a82f640f8b1b1f6e';
    public $AppMasterSecret = 'hkkejqyvdp483becakuup9rgqup8jn5u';

    public function __construct(){
    	$this->host = 'http://'.request()->server('HTTP_HOST').'/';
        $this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
    }
    
    public function index(){
		return response()->json(['code'=>200,'mes'=>'hello']);
		
	}
	public function getRole($uid){
		return DB::table('admin_role_users')->where('user_id',$uid)->value('role_id');
	}

	public function test001(){
		$accessToken = $this->get_accessToken();
        $res = $this->vpost('https://open.ys7.com/api/lapp/device/add','accessToken='.$accessToken.'&deviceSerial=C15769474&validateCode=XIRIVB');
        $res = json_decode($res);
        dd($res->code);
	}
	
	public function update_version(request $request){
		$version = $request->input('version',-1); 
		$iosUrl = 'https://itunes.apple.com/us/app/%E5%AE%B6%E8%A7%86%E8%A3%85%E4%BF%AE%E7%9B%B4%E6%92%AD/id1439192101?mt=8&uo=4';
		if ($version == -1) {
			$res = DB::table('app_version')->orderBy('version','desc')->first();
			$res->iosUrl = $iosUrl;
			return response()->json(['error'=>0,'data'=>$res]);
		}
		$res = DB::table('app_version')->where('version','>',$version)->orderBy('version','desc')->first();
		if ($res) {
			$res->addtime = strtotime($res->addtime);
			$res->iosUrl = $iosUrl;
			return response()->json(['error'=>0,'data'=>$res]);
		}else{
			return response()->json(['error'=>1,'mes'=>'已经是最新版本']);
		}
	}

	public function api_accessToken(){
		$accessToken = DB::table('config')->where('name','accessToken')->first();
		if ($accessToken->losetime > time()) {
			return response()->json(['error'=>0,'accessToken'=>$accessToken->value]);
		}
		$accesstoken = $this->vpost('https://open.ys7.com/api/lapp/token/get','appKey='.$this->appKey.'&appSecret='.$this->appSecret);

		$accesstoken = json_decode($accesstoken);
		// dd($accesstoken->data->accessToken);
		if ($accesstoken->code == 200) {
			$data = [
				'value'=>$accesstoken->data->accessToken,
				'losetime'=>substr($accesstoken->data->expireTime,0,10) - 36000,//10小时之后过期
			];
			DB::table('config')->where('name','accessToken')->update($data);
			return response()->json(['error'=>0,'accessToken'=>$accessToken->value]);
		}
		
	}
	//获取初始数据
	public function getStartSource(Request $request){
		$cid = $request->input('cid',0);
		$uid = $request->input('uid');
		if ($uid) {
    	DB::table('user')->where('id',$uid)->update(['uptime'=>date('Y-m-d H:i:s',time())]);
			$res = DB::table('record')->where('uid',$uid)->where('day',date('Y-m-d'))->first();
			if (empty($res)) {
				
				$new = [
					'uid'=>$uid,
					'cid'=>$cid,
					'starttime'=>time(),
					'day'=>date('Y-m-d'),
				];
				DB::table('record')->insert($new);
			}else{
				$new = [
					'starttime'=>time(),
				];
				DB::table('record')->where('id',$res->id)->update($new);
			}
		}
		$welcome = DB::table('welcome_page')->where('cid',$cid)->orderBy('sort','asc')->take(3)->get();
		foreach ($welcome as $k => $v) {
			$welcome[$k]->image = $this->upload.$v->image;
		}
		$data['welcome'] = $welcome;
		$accessToken = DB::table('config')->where('name','accessToken')->first();
		if ($accessToken->losetime > time()) {
			$data['accessToken'] = $accessToken->value;
			return response()->json(['error'=>0,'data'=>$data]);
		}
		$accesstoken = $this->vpost('https://open.ys7.com/api/lapp/token/get','appKey='.$this->appKey.'&appSecret='.$this->appSecret);

		$accesstoken = json_decode($accesstoken);
		// dd($accesstoken->data->accessToken);
		if ($accesstoken->code == 200) {
			$data = [
				'value'=>$accesstoken->data->accessToken,
				'losetime'=>substr($accesstoken->data->expireTime,0,10) - 36000,//10小时之前过期
			];
			DB::table('config')->where('name','accessToken')->update($data);
			$data['accessToken'] = $accessToken->value;
			return response()->json(['error'=>0,'data'=>$data]);
		}
	}

	//关闭app通知接口
	public function closeApp(Request $request){
		$uid = $request->input('uid');
		// return response()->json(['error'=>0,'uid'=>$uid]);
		$res = DB::table('record')->where('uid',$uid)->orderBy('id','desc')->first();
		$up = [
			'alivetime'=>time()-$res->starttime + $res->alivetime,
			'endtime'=>time(),
		];
		DB::table('record')->where('id',$res->id)->update($up);
		return response()->json(['error'=>0,'data'=>'ok']);
	}

	public function get_accessToken(){
		$accessToken = DB::table('config')->where('name','accessToken')->first();
		if ($accessToken->losetime > time()) {
			return $accessToken->value;
		}
		$accesstoken = $this->vpost('https://open.ys7.com/api/lapp/token/get','appKey='.$this->appKey.'&appSecret='.$this->appSecret);

		$accesstoken = json_decode($accesstoken);
		// dd($accesstoken->data->accessToken);
		if ($accesstoken->code == 200) {
			$data = [
				'value'=>$accesstoken->data->accessToken,
				'losetime'=>substr($accesstoken->data->expireTime,0,10) - 36000,//10小时之前过期
			];
			DB::table('config')->where('name','accessToken')->update($data);
			return $accesstoken->data->accessToken;
		}
		
	}
	public function vpost($url,$data){ // 模拟提交数据函数
	    $curl = curl_init(); // 启动一个CURL会话
	    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
	    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
	    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	    $tmpInfo = curl_exec($curl); // 执行操作
	    if (curl_errno($curl)) {
	       echo 'Errno'.curl_error($curl);//捕抓异常
	    }
	    curl_close($curl); // 关闭CURL会话
	    return $tmpInfo; // 返回数据
	}
}

