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
    public function residence()
    {
        return $this->belongsTo('App\Residence','rid','id');
    }


    public function setPanoramaAttribute($panorama)
    {
        if (is_array($panorama)) {
            $this->attributes['panorama'] = json_encode($panorama);
        }
    }
    public function getPanoramaAttribute($panorama)
    {
        return json_decode($panorama, true);
    }
}
