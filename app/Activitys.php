<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activitys extends Model
{
    protected $table = 'activitys';
    public $timestamps = false;
    public function admin_users()
    {
        return $this->belongsTo('App\Staff','cid','id');
    }
}
