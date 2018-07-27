<?php

namespace app\api\model;

class Banner extends BaseModel{

    //修改表明
    // protected $table = 'banner_item';

    protected $hidden = ['delete_time','update_time'];

    public function items()
    {
        return $this->hasMany('banner_item','banner_id','id');
    }

    public function getBannerByID($id) {

        $banner  = self::with(['items','items.img'])->find($id);
        return $banner;

    }
}   