<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Build_case extends Model
{
    protected $table = 'build_case';
    public $timestamps = false;
    public function build_images()
    {
        return $this->hasMany(Build_case_images::class, 'bid');
    }
    public function admin_users()
    {
        return $this->belongsTo('App\Staff','uid','id');
    }

    public function setKetingAttribute($keting)
    {
        if (is_array($keting)) {
            $this->attributes['keting'] = json_encode($keting);
        }
    }
    public function getKetingAttribute($keting)
    {
        return json_decode($keting, true);
    }

     public function setWoshiAttribute($woshi)
    {
        if (is_array($woshi)) {
            $this->attributes['woshi'] = json_encode($woshi);
        }
    }
    public function getWoshiAttribute($woshi)
    {
        return json_decode($woshi, true);
    }

     public function setWeishengjianAttribute($weishengjian)
    {
        if (is_array($weishengjian)) {
            $this->attributes['weishengjian'] = json_encode($weishengjian);
        }
    }
    public function getWeishengjianAttribute($weishengjian)
    {
        return json_decode($weishengjian, true);
    }

     public function setChufangAttribute($chufang)
    {
        if (is_array($chufang)) {
            $this->attributes['chufang'] = json_encode($chufang);
        }
    }
    public function getChufangAttribute($chufang)
    {
        return json_decode($chufang, true);
    }

     public function setShuidianshigongAttribute($shuidianshigong)
    {
        if (is_array($shuidianshigong)) {
            $this->attributes['shuidianshigong'] = json_encode($shuidianshigong);
        }
    }
    public function getShuidianshigongAttribute($shuidianshigong)
    {
        return json_decode($shuidianshigong, true);
    }

     public function setQiqianggongyiAttribute($qiqianggongyi)
    {
        if (is_array($qiqianggongyi)) {
            $this->attributes['qiqianggongyi'] = json_encode($qiqianggongyi);
        }
    }
    public function getQiqianggongyiAttribute($qiqianggongyi)
    {
        return json_decode($qiqianggongyi, true);
    }

     public function setMugonggongyiAttribute($mugonggongyi)
    {
        if (is_array($mugonggongyi)) {
            $this->attributes['mugonggongyi'] = json_encode($mugonggongyi);
        }
    }
    public function getMugonggongyiAttribute($mugonggongyi)
    {
        return json_decode($mugonggongyi, true);
    }

     public function setYouqigongyiAttribute($youqigongyi)
    {
        if (is_array($youqigongyi)) {
            $this->attributes['youqigongyi'] = json_encode($youqigongyi);
        }
    }
    public function getYouqigongyiAttribute($youqigongyi)
    {
        return json_decode($youqigongyi, true);
    }
}
