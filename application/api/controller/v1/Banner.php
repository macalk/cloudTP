<?php

namespace app\api\controller\v1;

use app\api\validate\IDMustBePostiveInt;
use app\api\model\Banner as BannerModel;

use app\lib\exception\BannerMissException;

class Banner {

    public function getBanner($id) {

        // phpinfo();

        return '这是banner接口';
          
        // validate('IDMustBePostiveInt')->goCheck();

        // $bannerModel = new BannerModel;
        // $banner = $bannerModel->getBannerByID($id);

        // if(!$banner) {
        //     throw new BannerMissException();
        // }
        // return $banner;
    }
}