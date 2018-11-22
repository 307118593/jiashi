<?php  
	// use Umeng;
	use Intervention\Image\ImageManagerStatic as Image;

	// function __construct(){
 //    	$this->host = 'http://'.request()->server('HTTP_HOST').'/';
 //        $this->upload = 'http://'.request()->server('HTTP_HOST').'/upload/';
 //    }
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

	function upload_base64_image($file,$uppath='images/'){

		if (is_array($file)) {
            foreach($file as $v) {
                $v = str_replace(' ', '+', $v);
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $v, $result)){
                    $v = base64_decode(str_replace($result[1], '', $v));
                    $img = Image::make($v);  
                    // $ex = $v->getClientOriginalExtension();
                    $name = $pro_id.time().rand(1,9).rand(1,9).rand(1,9).".".$result[2];
                    $path = $uppath.$name;
                    $img->save('upload/'.$path);
                    // $image = 'bobao/'.$name;
                }
            }
            return response()->json(['error'=>0,'image'=>'http://'.request()->server('HTTP_HOST').'/upload/'.$path,'path'=>$path]);
        }else{
            return response()->json(['error'=>1,'mes'=>'不是文件数组']);
        }


	    // $dx = $file->getClientSize();
	    // $size = $dx/1024/1024;
	    // if ($size > 2) {
	    // 	return response()->json(['error'=>1,'mes'=>'图片超过2M.']);
	    // }
	    // if ($file) {
	    //     $img = Image::make($file);  
	    //     $ex = $file->getClientOriginalExtension();
	    //     $name = time().rand(1,9).rand(1,9).".".$ex;
	    //     $path = $uppath.$name;
	    //     $img->save('upload/'.$path);
	    //     // $host = $request->server('HTTP_HOST');
	    //     return response()->json(['error'=>0,'image'=>'http://'.request()->server('HTTP_HOST').'/upload/'.$path,'path'=>$path]);

	    // }
	    // return response()->json(['error'=>1,'mes'=>'上传失败']);
	}



?>