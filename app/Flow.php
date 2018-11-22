<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flow extends Model
{
    protected $table = 'flow';
    public $timestamps = false;
    public function admin()
    {
        return $this->belongsTo('App\Staff','admin_uid','id');
    }
}
