<?php

namespace app\api\service;

use app\lib\exception\TokenException;
use app\api\model\ThirdApp;


class AppToken extends Token{
    public function get($ac,$se){
        $app = ThirdApp::check($ac,$se);
        if(!$app) {
            throw new TokenException([
                'msg' => '授权失败',
                'errorCode' => 10004
            ]);
        }else {
            $scope = $app->scope;
            $uid = $app->id;
            $values = [
                'scope' => $scope,
                'uid' => $uid
            ];
            $token = $this->saveToCache($values);
            return $token;
        }
    }

    private function saveToCache($values){
        
    }
}