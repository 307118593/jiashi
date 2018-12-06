<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'record';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User','uid','id');
    }

    public function admin_users()
    {
        return $this->belongsTo('App\Staff','cid','id');
    }
}
