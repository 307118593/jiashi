<?php  
	// use Zzl\Umeng;
    use Zzl\Umeng\Facades\Umeng;
	use Intervention\Image\ImageManagerStatic as Image;

	// function __construct(){
 //    	$this->host = 'http://'.request()->server('HTTP_HOST').'/';
 //        $this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
 //    }
	//友盟推送
    const Appkey = '5b598f75f29d98513c0000c3';
    const UmengMessageSecret = 'b49db5518f933098a82f640f8b1b1f6e';
    const AppMasterSecret = 'hkkejqyvdp483becakuup9rgqup8jn5u';

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
	//上传base64位图片
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

	//友盟单播
	function sendUnicast($device_token,$predefined){
		Umeng::android()->sendUnicast($device_token,$predefined); //单播
	}





?>