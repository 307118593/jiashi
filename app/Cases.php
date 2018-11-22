<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    protected $table = 'cases';
    public $timestamps = false;
    public function admin_users()
    {
        return $this->belongsTo('App\Staff','uid','id');
    }
}
