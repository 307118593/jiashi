<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
class CasesController extends Controller
{
    //获取案例数据
    public function getCaseSource(Request $request){
        $cid = $request->input('cid');
        $type = ['0'=>'全包','1'=>'半包'];
        $uids = DB::table('admin_users')->where('job',3)->where('pid',$cid)->pluck('name','id');
        $residence = DB::table('residence')->where('cid',$cid)->pluck('name','id');
        $uids = $this->objectToArray($uids);
        $residence = $this->objectToArray($residence);
        $data = [];
        $data['type'] = $type;
        $data['uids'] = $uids;
        $data['residence'] = $residence;
        // $data = $this->objectToArray($data);
        // dump($data['uids']);
        return response()->json(['error'=>0,'data'=>$data]);
    }
    
    public function objectToArray($object) {
        //先编码成json字符串，再解码成数组
        return json_decode(json_encode($object), true);
    }
    //添加案例
    public function create_case(Request $request){
        $case_id = $request->input('case_id',0);
    	$title = $request->input('title');
    	$uid = $request->input('uid');
    	$area = $request->input('area');
    	$style = $request->input('style');
    	$url = $request->input('url');
        $photo = $request->input('photo');
        $panorama = $request->input('panorama');
        $address = $request->input('address');
        $cid = $request->input('cid');
        $type = $request->input('type');
        $is_up = $request->input('is_up');
    	$rid = $request->input('rid');
       
        $data = [
            'title'=>$title,
            'uid'=>$uid,
            'area'=>$area,
            'style'=>$style,
            'url'=>$url,
            'address'=>$address,
            'cid'=>$cid,
            'type'=>$type,
            'rid'=>$rid,
            'is_up'=>$is_up,
            'addtime'=>time(),
        ];
         if (!is_url($photo)) {
            $photo = upload_base64_oneimage($photo,'anli/');
            $data['photo'] = $photo;
        }
        if ($panorama) {
            $res = upload_base64_image($panorama,'anli/');
            $res = $res->getContent();
            $res = json_decode($res,true);
            // dump($res['error']);exit;
            if ($res['error'] != 0 ) {
                return response()->json(['error'=>1,'mes'=>$res['mes']]);
            }
        
            $data['panorama'] = json_encode($res['cpath']);
        }
        if ($case_id > 0) {
            $res = DB::table('cases')->where('id',$case_id)->update($data);
        }else{
            $res = DB::table('cases')->insert($data);
        }
    	
        // return $data;
    	if ($res) {
    		return response()->json(['error'=>0,'mes'=>'创建成功.']);
    	}
    }


 //获取施工案例数据
    public function getBuildCaseSource(Request $request){
        $cid = $request->input('cid');
        $type = ['0'=>'全包','1'=>'半包'];
        $uids = DB::table('admin_users')->where('job',11)->where('pid',$cid)->pluck('name','id');
        $uids = $this->objectToArray($uids);
        $data = [];
        $data['uids'] = $uids;
        $data['type'] = $type;
        // $data = $this->objectToArray($data);
        // dump($data['uids']);
        return response()->json(['error'=>0,'data'=>$data]);
    }

     //添加施工案例
    public function createBuildcase(Request $request){
        $build_id = $request->input('build_id',0);
        $title = $request->input('title');
        $uid = $request->input('uid');
        $area = $request->input('area');
        $style = $request->input('style');
        $praise = $request->input('praise');
        $photo = $request->input('photo');
        $cid = $request->input('cid');
        $type = $request->input('type');
        $data = [
            'title'=>$title,
            'uid'=>$uid,
            'area'=>$area,
            'style'=>$style,
            'praise'=>$praise,
            'cid'=>$cid,
            'type'=>$type,
            'addtime'=>time(),
        ];
        if (!is_url($photo)) {
            $photo = upload_base64_oneimage($photo,'anli/');
            $data['photo'] = $photo;
        }
       
        if ($build_id > 0) {
            $res = DB::table('build_case')->where('id',$build_id)->update($data);
        }else{
            $res = DB::table('build_case')->insert($data);
        }
        
        // return $data;
        if ($res) {
            return response()->json(['error'=>0,'mes'=>'创建成功.']);
        }
    }

     //添加工艺展示
    public function createArt(Request $request){
        $name = $request->input('name');
        $uids = json_encode($request->input('uids'));
        $image = $request->input('image');
        $cid = $request->input('cid');
        // $sort = $request->input('sort');
        $res = upload_base64_image($image,'arts/');
        $res = $res->getContent();
        $res = json_decode($res,true);
        // dump($res['error']);exit;
        if ($res['error'] != 0 ) {
            return response()->json(['error'=>1,'mes'=>$res['mes']]);
        }
        $data = [
            'name'=>$name,
            'uids'=>$uids,
            'cid'=>$cid,
            // 'sort'=>$sort,
            'created_at'=>time(),
        ];
        $data['images'] = json_encode($res['cpath']);
        $res = DB::table('arts')->insert($data);
        // return $data;
        if ($res) {
            return response()->json(['error'=>0,'mes'=>'创建成功.']);
        }
    }

    //获取工艺展示列表
    public function getArts(Request $request){
        $uid = $request->input('uid');
        $role = getRole($uid);
        $cid = getCid($uid);
        if ($role == 2 || $role == 4) {
            $arts = DB::table('arts')->where('cid',$cid)->orderby('sort','desc')->get();
        }else{
            $arts = DB::table('arts')->where('uids','like','%"'.$uid.'"%')->orderby('sort','desc')->get();
        }
        
        foreach ($arts as $k => $v) {
            $v->images = $this->upload.json_decode($v->images)[0];
            $uids = json_decode($v->uids);
            foreach ($uids as $kk => $vv) {
                $data[] = DB::table('admin_users')->where('id',$vv)->value('name');
            }
            $arts[$k]->uids = $data; 
            unset($data);
        }
        return response()->json(['error'=>0,'data'=>$arts]);
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
        $condition = $request->input('condition','');
    	$cases = DB::table('cases')->where('cid',$cid)->when($condition,function($query) use($condition){
            return $query->where(function($query) use($condition){
                $query->orwhere('title','like','%'.$condition.'%')->orwhere('style','like','%'.$condition.'%')->orwhere('address','like','%'.$condition.'%');
            });
        })->orderBy('sort','desc')->get();
    	foreach ($cases as $k => $v) {
    		$cases[$k]->photo = $this->upload.$v->photo;
            // if ($v->panorama && empty($v->url)) {
            //     $cases[$k]->url = 'http://www.homeeyes.cn/app/3DShow/index.html?type=1&case_id='.$v->id;
            // }
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
