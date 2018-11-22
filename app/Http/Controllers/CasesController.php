<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
class CasesController extends Controller
{
    //添加案例
    public function create_case(Request $request){
    	$title = $request->input('title');
    	$uid = $request->input('uid');
    	$house = $request->input('house');
    	$area = $request->input('area');
    	$style = $request->input('style');
    	$url = $request->input('url');
        $photo = $request->input('photo');
    	$address = $request->input('address');
    	$data = [
    		'title'=>$title,
    		'uid'=>$uid,
    		'house'=>$house,
    		'area'=>$area,
    		'style'=>$style,
    		'url'=>$url,
            'photo'=>$photo,
    		'address'=>$address,
            'addtime'=>time(),
    	];
    	$res = DB::table('cases')->insert($data);
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'创建成功.']);
    	}
    }

    //上传图片
    public function upload_photo(Request $request){
    	$file = $request->file('photo');
        if ($file) {
            $img = Image::make($file);  
            $ex = $file->getClientOriginalExtension();
            $name = time().rand(1,9).rand(1,9).".".$ex;
            $path = 'upload/anli/'.$name;
            $img->save($path);
            $host = $request->server('HTTP_HOST');
            return response()->json(['error'=>0,'hesdurl'=>'http://'.$host.'/'.$path,'path'=>$path]);

        }
        return response()->json(['error'=>1,'mes'=>'上传失败']);
    }

    //获取案例列表
    public function get_cases(Request $request){
        $cid = $request->input('cid',2);
    	$cases = DB::table('cases')->where('cid',$cid)->orderBy('sort','desc')->get();
    	foreach ($cases as $k => $v) {
    		$cases[$k]->photo = $this->upload.$v->photo;
            // $cases[$k]->author = DB::table('admin_users')->where('id',$v->uid)->select('name','avatar')->first();
            // if ($cases[$k]->author) {
            //     $cases[$k]->author->avatar = $this->upload.$cases[$k]->author->avatar;
            // }
    	}
    	return response()->json(['error'=>0,'data'=>$cases]);
    }

    //统计案例热度
    public function caseHot(Request $request){
        $caseid = $request->input('caseid');
        DB::table('cases')->where('id',$caseid)->increment('hot');
        return response()->json(['error'=>0,'mes'=>'ok']);
    }


    //获取楼盘列表
    public function get_residence(Request $request){
        $cid = $request->input('cid',2);
        $residence = DB::table('residence')->where('cid',$cid)->orderBy('sort','desc')->get();
        foreach ($residence as $k => $v) {
            $residence[$k]->image = $this->upload.$v->image;
            $residence[$k]->casecount = DB::table('cases')->where('rid',$v->id)->count();
            if ($residence[$k]->casecount == 0) {
                unset($residence[$k]);
            }
        }
        $residence = $residence->toArray(); 
        $residence = array_values($residence);
        return response()->json(['error'=>0,'data'=>$residence]);
    }

    //获取楼盘下的案例列表
    public function getResidenceCase(Request $request){
        $rid = $request->input('rid');
        $cases = DB::table('cases')->where('rid',$rid)->orderBy('sort','desc')->get();
        foreach ($cases as $k => $v) {
            $cases[$k]->photo = $this->upload.$v->photo;
            $cases[$k]->author = DB::table('admin_users')->where('id',$v->uid)->select('name','avatar')->first();
            if ($cases[$k]->author) {
                $cases[$k]->author->avatar = $this->upload.$cases[$k]->author->avatar;
            }
        }
        return response()->json(['error'=>0,'data'=>$cases]);
    }
    //获取轮播图
    public function get_banner(Request $request){
        $banner = DB::table('banner')->get();
        foreach ($banner as $k => $v) {
            $banner[$k]->image = 'http://'.$request->server('HTTP_HOST').'/upload/'.$v->image;
        }

        return response()->json(['error'=>0,'data'=>$banner]);
    }

    //分享转发
    public function get_share(Request $request){
        $type = $request->input('type',0);
        $share = DB::table('share')->where(['type'=>$type,'status'=>1])->first();
        if ($share) {
            $share->imageurl = 'http://'.$request->server('HTTP_HOST').'/upload/'.$share->imageurl;
        }
        if(empty($share)){
            $share = [];
            $share['url'] = DB::table('app_version')->orderBy('version','desc')->value('url');
            $share['imageurl'] = 'http://47.97.109.9/upload/images/41fc170785c8a9ce77f290de83c2a8f1.jpg';
        }
        return response()->json(['error'=>0,'data'=>$share]);
    }
    
    //设置公司简介
    public function setCompany(Request $request){
        return 'dd';
        $z_uid = $request->input('z_uid');
        $homeurl = $request->input('homeurl');
        $content = $request->input('content');
        $file = $request->file('image');
        $data = [];
        if ($file) {
            $img = Image::make($file);  
            $ex = $file->getClientOriginalExtension();
            $name = $z_uid.".".$ex;
            $path = 'upload/company/'.$name;
            $img->save($path);
            $data['image'] = $path;
        }
        // if ($content) {
            $data['content'] = $content;
        // }
        // if ($homeurl) {
            $data['homeurl'] = $homeurl;
        // }
        $res = DB::table('admin_users')->where('id',$z_uid)->update($data);

        return Redirect('admin?res='.$res);
        
    }

    //上传图片
    public function setimages(Request $request){
        $cid = $request->input('cid');
        $sort = $request->input('sort');
        $file = $request->file('image');
        // $file = json_encode($file);
        // dd($file);
        $i = 0;
        if (is_array($file)) {
            foreach($file as $v) {
                $img = Image::make($v);  
                $ex = $v->getClientOriginalExtension();
                $name = $cid.time().rand(100000,9999999).".".$ex;
                $path = 'upload/company/'.$name;
                $img->save($path);
                $data = [
                    'cid'=>$cid,
                    'image'=>'company/'.$name,
                    'sort'=>$sort,
                    'addtime'=>date('Y-m-d H:i:s'),
                ];
                DB::table('pics')->where('cid',$cid)->insert($data);
                $i++;
            }
        }
         return Redirect('admin/pics?i='.$i);
        
        

    }
}
