<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $table = 'messages';
    public $timestamps = false;
    public function admin_users()
    {
        return $this->belongsTo('App\Staff','senduser','id');
    }
}
