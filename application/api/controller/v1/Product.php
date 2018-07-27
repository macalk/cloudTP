<?php

namespace app\api\controller\v1;
use app\api\validate\Count;
use app\api\model\Product as ProductModel;
use app\lib\exception\ProductException;

class Product {
    public function getRecent($count=15){
        (new Count())->goCheck($count);
        $productModel = new ProductModel;
        $products = $productModel->getMostRecent($count);
        if($products->isEmpty()) {
            throw new ProductException();
        }
        $products->hidden(['summary']);
        return $products;
    }
    public function getAllInCategory($id) {
        validate('IDMustBePostiveInt')->goCheck();
        $productModel = new ProductModel;
        $products = $productModel->getProductsByCategoryID($id);
        if($products->isEmpty()){
            throw new ProductException();
        }
        $products->hidden(['summary']);
        return $products;
    }

    public function getOne($id) {
        validate('IDMustBePostiveInt')->goCheck();
        $product = ProductModel::getProductDetail($id);
        if(!$product){
            throw new ProductException();
        }
        return $product;
    }

    public function deleteOne($id) {
        
    }
}