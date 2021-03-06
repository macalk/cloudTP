<?php

namespace app\api\model;

class Order extends BaseModel
{
    protected $hidden = ['user_id','delete_time','update_time'];
    protected $autoWriteTimestamp = 'timestamp';

    public static function getSummaryByUser($uid,$page=1,$size=15) {
        //返回的是Paginator::对象  paginate（分页）
        $pageingData = self::where('user_id','=',$uid)->order('create_time desc')->paginate($size,false,['page'=>$page]);
        return $pageingData;
    }

    public function getSnapItemsAttr($value) {
        if(empty($value)) {
            return null;
        }
        return json_decode($value);
    }
    public function getSnapAddressAttr($value) {
        if(empty($value)) {
            return null;
        }
        return json_decode($value);
    }
} 