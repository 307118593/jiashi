<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    protected $table = 'camera';
    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo('App\User','uid','id');
    }
    public function project()
    {
        return $this->belongsTo('App\Project','pro_id','id');
    }

    public function admin_users()
    {
        return $this->belongsTo('App\Staff','cid','id');
    }

    // public function setUidAttribute($Uid)
    // {
    //     if (empty($Uid)) {
    //         $this->attributes['uid'] = 0;
    //     }
    // }
    // public function setProIdAttribute($ProId)
    // {
    //     if (empty($ProId)) {
    //         $this->attributes['pro_id'] = 0;
    //     }
    // }
    // public function setCidAttribute($Cid)
    // {
    //     if (empty($Cid)) {
    //         $this->attributes['cid'] = 0;
    //     }
    // }
}
