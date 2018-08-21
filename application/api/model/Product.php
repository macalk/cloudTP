<?php

namespace app\api\model;

class Product extends BaseModel
{
    protected $hidden = ['delete_time','delete_time','create_time','update_time','category_id',
    'from','pivot'];
    public function getMainImgUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }

    public function getMostRecent($count) {
        $products = self::limit($count)->order('create_time desc')->select();
        return $products;
    }

    public static function getProductsByCategoryID($categoryID) {
        $products = self::where('category_id','=',$categoryID)->select();
        return $products;
    }

    public function imgs(){
        return $this->hasMany('product_image', 'product_id', 'id');
    }
    public function properties(){
        return $this->hasMany('product_property','product_id','id');
    }

    public static function getProductDetail($id) {
        // $product = self::with(['imgs'=>function($query){$query->with(['imgUrl'])->order('order asc');}])->with(['properties'])->find($id);
        // return $product;
        return self::with('properties')->find($id);
    }
}
 