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

		return response()->json(['error'=>0,'data'=>$mes]);
	}

	//删除消息
	public function delMes(Request $request){
		$uid = $request->input('uid');
		$mid = $request->input('mid');
		$res = DB::table('messages_user')->where('uid',$uid)->where('mid',$mid)->update(['is_del'=>1]);
		return response()->json(['error'=>0,'mes'=>'操作成功~']);
	}
}