<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo('App\User','uid','id');
    }
    public function admin_users()
    {
        return $this->belongsTo('App\Staff','leader_id','id');
    }
    //修改器--start
    // 轮播图
    public function setPicturesAttribute($pictures)
    {
        if (is_array($pictures)) {
            $this->attributes['pictures'] = json_encode($pictures);
        }
    }
    public function getPicturesAttribute($pictures)
    {
        return json_decode($pictures, true);
    }
    //项目成员
    public function setProjectUsAttribute($projectUs)
    {
        if (is_array($projectUs)) {
            $this->attributes['project_us'] = json_encode($projectUs);
        }
    }
    public function getProjectUsAttribute($projectUs)
    {
        return json_decode($projectUs, true);
    }
    //体验人员
    public function setTryUidAttribute($TryUid)
    {
        if (is_array($TryUid)) {
            $this->attributes['try_uid'] = json_encode($TryUid);
        }
    }
    public function getTryUidAttribute($TryUid)
    {
        return json_decode($TryUid, true);
    }
    //设备
    public function setCamerasAttribute($cameras)
    {
        if (is_array($cameras)) {
            $this->attributes['cameras'] = json_encode($cameras);
        }
    }
    public function getCamerasAttribute($cameras)
    {
        return json_decode($cameras, true);
    }
    //装修合同
     public function setZxhtAttribute($zxht)
    {
        if (is_array($zxht)) {
            $this->attributes['zxht'] = json_encode($zxht);
        }
    }
    public function getZxhtAttribute($zxht)
    {
        return json_decode($zxht, true);
    }
    //预算清单
    public function setYsqdAttribute($ysqd)
    {
        if (is_array($ysqd)) {
            $this->attributes['ysqd'] = json_encode($ysqd);
        }
    }
    public function getYsqdAttribute($ysqd)
    {
        return json_decode($ysqd, true);
    }
    //项目材料
     public function setXmclAttribute($xmcl)
    {
        if (is_array($xmcl)) {
            $this->attributes['xmcl'] = json_encode($xmcl);
        }
    }
    public function getXmclAttribute($xmcl)
    {
        return json_decode($xmcl, true);
    }
    //效果图
     public function setXgtAttribute($xgt)
    {
        if (is_array($xgt)) {
            $this->attributes['xgt'] = json_encode($xgt);
        }
    }
    public function getXgtAttribute($xgt)
    {
        return json_decode($xgt, true);
    }
    //验收照片
     public function setYszpAttribute($yszp)
    {
        if (is_array($yszp)) {
            $this->attributes['yszp'] = json_encode($yszp);
        }
    }
    public function getYszpAttribute($yszp)
    {
        return json_decode($yszp, true);
    }
    //收付款
     public function setFkxxAttribute($fkxx)
    {
        if (is_array($fkxx)) {
            $this->attributes['fkxx'] = json_encode($fkxx);
        }
    }
    public function getFkxxAttribute($fkxx)
    {
        return json_decode($fkxx, true);
    }
   
}
