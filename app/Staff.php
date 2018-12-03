<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'admin_users';
    public $timestamps = true;

 //    public function setAvatarAttribute($avatar)
	// {
	// 	$res = upload_base64_oneimage($avatar);
	// 	$res = $res->getContent();
	// 	$res = json_decode($res,true);
	//      $this->attributes['avatar'] = $res['path'];
	// }
}

