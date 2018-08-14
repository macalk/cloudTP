<?php

namespace app\api\model;

class productProperty extends BaseModel
{
    protected $hidden = ['id','delete_time','product_id'];

    public function imgUrl(){
        return $this->belongsTo('Image','img_id','id');
    }
}