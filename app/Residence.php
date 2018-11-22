<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    protected $table = 'residence';
    public $timestamps = false;
    public function admin_users()
    {
        return $this->belongsTo('App\Staff','cid','id');
    }
}
