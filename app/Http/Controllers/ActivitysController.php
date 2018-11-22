<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
class ActivitysController extends Controller
{
	public function getActs(Request $request){
		$cid = $request->input('cid',0);
		$acts = DB::table('activitys')->where('cid',$cid)->where('state',0)->orderBy('sort','desc')->get();
		foreach ($acts as $k => $v) {
			$acts[$k]->image = $this->upload.$v->image;
			if ($v->longimage) {
				$acts[$k]->longimage = $this->upload.$v->longimage;
			}
			
		}
		return response()->json(['error'=>0,'data'=>$acts]);
	}

	public function getActsDetail(Request $request){
		$aid = $request->input('aid');
		$act = DB::table('activitys')->where('id',$aid)->first();
		$act->image = $this->upload.$act->image;
		if ($act->longimage) {
			$act->longimage = $this->upload.$act->longimage;
		}
		return response()->json(['error'=>0,'data'=>$act]);
	}
}