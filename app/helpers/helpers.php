<?php  
	// use Zzl\Umeng;
    use Zzl\Umeng\Facades\Umeng;
	use Intervention\Image\ImageManagerStatic as Image;

	// function __construct(){
 //    	$this->host = 'http://'.request()->server('HTTP_HOST').'/';
 //        $this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
 //    }

	//获取权限
	function getRole($id){
		$role_id = DB::table('admin_role_users')->where('user_id',$id)->value('role_id');
		if ($role_id == 1) {
			$role = 1;
		}elseif($role_id == 2 || $role_id==5){
			$role = 2;
		}elseif($role_id == 3 || $role_id==7){
			$role = 3;
		}elseif($role_id == 4 || $role_id==6){
			$role = 4;
		}
		return $role;
	}

	//获取cid
	function getCid($cid){
		$role_id = DB::table('admin_role_users')->where('user_id',$cid)->value('role_id');
		if ($role_id >= 5 && $role_id <= 7) {
			$cid = 2;
		}
		return $cid;
	}

	//上传单图
	function upload_image($file,$uppath='images/'){
	    $dx = $file->getClientSize();
	    $size = $dx/1024/1024;
	    if ($size > 2) {
	    	return response()->json(['error'=>1,'mes'=>'图片超过2M.']);
	    }
	    if ($file) {
	        $img = Image::make($file);  
	        $ex = $file->getClientOriginalExtension();
	        $name = time().rand(1,9).rand(1,9).".".$ex;
	        $path = $uppath.$name;
	        $img->save('upload/'.$path);
	        // $host = $request->server('HTTP_HOST');
	        return response()->json(['error'=>0,'image'=>'http://'.request()->server('HTTP_HOST').'/upload/'.$path,'path'=>$path]);

	    }
	    return response()->json(['error'=>1,'mes'=>'上传失败']);
	}
	//上传base64位多图
	function upload_base64_image($file,$uppath='images/'){

		if (is_array($file)) {
            foreach($file as $v) {
                $v = str_replace(' ', '+', $v);
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $v, $result)){
                    $v = base64_decode(str_replace($result[1], '', $v));
                    $img = Image::make($v);  
                    // $ex = $v->getClientOriginalExtension();
                    $name = time().rand(1,9).rand(1,9).rand(1,9).".".$result[2];
                    $path = $uppath.$name;
                    $img->save('upload/'.$path);
                    // $image = 'bobao/'.$name;
                }
            }
            return response()->json(['error'=>0,'image'=>'http://'.request()->server('HTTP_HOST').'/upload/'.$path,'path'=>$path]);
        }else{
            return response()->json(['error'=>1,'mes'=>'不是文件数组']);
        }

	}

	//上传base64位单图图
	function upload_base64_oneimage($file,$uppath='images/'){

		// if (is_array($file)) {
            // foreach($file as $v) {
                // dd($file);
                $file = str_replace(' ', '+', $file);
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)){
                    $file = base64_decode(str_replace($result[1], '', $file));
                    $img = Image::make($file);  
                    // $ex = $v->getClientOriginalExtension();
                    $name = time().rand(1,9).rand(1,9).rand(1,9).".".$result[2];
                    $path = $uppath.$name;
                    $img->save('upload/'.$path);
                    // $image = 'bobao/'.$name;
                    return 'upload/'.$path;
            		// return response()->json(['error'=>0,'image'=>'http://'.request()->server('HTTP_HOST').'/upload/'.$path,'path'=>$path]);
                }else{
                	return false;
                }
            // }
        // }else{
            // return response()->json(['error'=>1,'mes'=>'不是文件数组']);
        // }

	}


	//友盟单播
	function sendUnicast($device_token,$predefined,$extraField){
		Umeng::android()->sendUnicast($device_token,$predefined); //单播
		//加入本地消息
		$data = [
			'title'=>$predefined['title'],
			'content'=>$predefined['text'],
			'cid'=>$extraField['cid'],
			'addtime'=>date('Y-m-d H:i:s'),
			'type'=>-1,
		];
		$mid = DB::table('messages')->insertGetId($data);
		if ($mid) {
			$array = [
				'uid'=>$extraField['uid'],
				'mid'=>$mid,
				'sendtime'=>date('Y-m-d H:i:s'),
			];
			DB::table('messages_user')->insert($array);
		}
	}





?>