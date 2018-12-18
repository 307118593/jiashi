<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Arts extends Model
{
    protected $table = 'arts';
    public $timestamps = true;

    public function admin_users()
    {
        return $this->belongsTo('App\Staff','cid','id');
    }

    public function setImagesAttribute($images)
    {
        if (is_array($images)) {
            $this->attributes['images'] = json_encode($images);
        }
    }
    public function getImagesAttribute($images)
    {
        return json_decode($images, true);
    }


    public function setUidsAttribute($uids)
    {
        if (is_array($uids)) {
            $this->attributes['uids'] = json_encode($uids);
        }
    }
    public function getUidsAttribute($uids)
    {
        return json_decode($uids, true);
    }
}
