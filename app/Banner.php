<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banner';
    public $timestamps = true;
    public function admin_users()
    {
        return $this->belongsTo('App\Staff','cid','id');
    }
}
