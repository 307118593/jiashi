<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Build_case_images extends Model
{
    protected $table = 'build_case_images';
    public $timestamps = false;

    protected $fillable = ['title', 'images'];

    public function build_case()
    {
        return $this->belongsTo(Build_case::class, 'bid');
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
}
