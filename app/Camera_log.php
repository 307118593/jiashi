<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Camera_log extends Model
{
    protected $table = 'camera_log';
    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo('App\User','uid','id');
    }
}
