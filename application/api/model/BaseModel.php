<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    //get要固定+字段名字+Attr (读取器)
    protected function prefixImgUrl($value,$data) {
        
        if($data['from'] == 1) {
            return config('setting.img_prefix').$value;
        }else {
            return $value;
        }
    }
}
