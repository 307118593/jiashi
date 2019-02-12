<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class MessagesController extends Controller
{
	//获取消息列表
	public function getMessages(Request $request){
		$uid = $request->input('uid');
		$mes = DB::table('messages_user')->where('uid',$uid)->where('is_del',0)->orderBy('sendtime','desc')->get();
		foreach ($mes as $k => $v) {
			$detail = DB::table('messages')->where('id',$v->mid)->first();
			if ($detail) {
				$mes[$k]->title = $detail->title;
				$mes[$k]->content = $detail->content;
				$mes[$k]->url = $detail->url;
				$mes[$k]->image = $detail->image;
				if ($mes[$k]->url) {
					if (!preg_match("/^(http):/", $mes[$k]->url)){
						$mes[$k]->url = 'http://'.$mes[$k]->url;
					}
				}
			}else{
				unset($mes[$k]);
			}
		}
		$mes = $mes->toArray();
		$mes = array_values($mes);
		return response()->json(['error'=>0,'data'=>$mes]);
	}

	//获取消息详情
	public function getMesDetail(Request $request){
		$uid = $request->input('uid');
		$mid = $request->input('mid');
		//修改已读
		DB::table('messages_user')->where('uid',$uid)->where('mid',$mid)->update(['is_read'=>1]);
		$mes = DB::table('messages_user')->where('uid',$uid)->where('mid',$mid)->first();
		$detail = DB::table('messages')->where('id',$mid)->first();
		$mes->title = $detail->title;
		$mes->content = $detail->content;
		$mes->url = $detail->url;
		if (!$mes->url) {
			$mes->url = 'https://www.homeeyes.cn/app/livedemo/messageinfo.html';
		}

		return response()->json(['error'=>0,'data'=>$mes]);
	}

	//删除消息
	public function delMes(Request $request){
		$uid = $request->input('uid');
		$mid = $request->input('mid');
		$res = DB::table('messages_user')->where('uid',$uid)->where('mid',$mid)->update(['is_del'=>1]);
		return response()->json(['error'=>0,'mes'=>'操作成功~']);
	}

	//获取消息数据
	public function getSendSource(Request $request){
		$uid = $request->input('uid');
		$role = getRole($uid);
		$cid = $request->input('cid');
		// if ($role == 2) {
		// 	$cid = $uid;
		// }else{
		// 	$cid = DB::table('admin_users')->where('id',$uid)->value('pid');
		// }
		// return $role;
		if ($role ==2 || $role ==4) {
			$type = [0=>"发送个人",1=>"群发"];
		}else{
			$type = [0=>"发送个人"];
		}
		$staff = DB::table('admin_users')->where('pid',$cid)->pluck('name','id');
		$data['type'] = $type;
		$data['staff'] = $staff;
		return response()->json(['error'=>0,'data'=>$data]);
	}


	//发送消息
	public function sendMes(Request $request){
		$uid = $request->input('uid');
		$cid = $request->input('cid');
		$touser = $request->input('touser');
		$type = $request->input('type');
		$title = $request->input('title');
		$content = $request->input('content');
		if ($type == 1) {//群发本地消息
			$data = [
				'cid'=>$cid,
				'title'=>$title,
				'content'=>$content,
				'addtime'=>date('Y-m-d H:i:s'),
				'type'=>-1,
				'senduser'=>$cid,
			];
			$mid = DB::table('messages')->insertGetId($data);
			$staff = DB::table('admin_users')->where('pid',$cid)->pluck('id');
			foreach ($staff as $k => $v) {
				$arr = [
					'mid'=>$mid,
					'fromuser'=>$uid,
					'touser'=>$v->id,
					'addtime'=>date('Y-m-d H:i:s'),
				];
				DB::table('messages_user')->insert($arr);
			}
			return response()->json(['error'=>0,'mes'=>'ok']);

		}else{//单发
			$data = [
				'cid'=>$cid,
				'title'=>$title,
				'content'=>$content,
				'addtime'=>date('Y-m-d H:i:s'),
				'type'=>-1,
				'senduser'=>$touser,
			];
			$mid = DB::table('messages')->insertGetId($data);
			$arr = [
				'mid'=>$mid,
				'fromuser'=>$uid,
				'touser'=>$touser,
				'addtime'=>date('Y-m-d H:i:s'),
			];
			DB::table('messages_user')->insert($arr);
		}
		return response()->json(['error'=>0,'mes'=>'ok']);
	}


	//获取消息列表
	public function getMesList(Request $request){
		
	}
}