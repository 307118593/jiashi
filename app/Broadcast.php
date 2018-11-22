<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $table = 'broadcast';
    public $timestamps = false;
    public function admin()
    {
        return $this->belongsTo('App\Staff','uid','id');
    }
    public function project()
    {
        return $this->belongsTo('App\Project','pro_id','id');
    }
    public function flow()
    {
        return $this->belongsTo('App\Flow','f_id','id');
    }
    public function setImageAttribute($image)
    {
        if (is_array($image)) {
            $this->attributes['image'] = json_encode($image);
        }
    }
    public function getImageAttribute($image)
    {
        return json_decode($image, true);
    }
}
