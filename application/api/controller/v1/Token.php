<?php

namespace app\api\controller\v1;
use app\api\validate\TokenGet;
use app\api\validate\AppTokenGet;
use app\api\service\UserToken;
use app\lib\exception\ParameterException;
use app\api\service\Token as TokenService;

class Token{
    public function getToken($code=''){
        (new TokenGet())->goCheck();
        $ut = new UserToken($code);
        $token = $ut->get();
        return [
            'token' => $token
        ];
    }

    public function verifyToken($token='') {
        if(!$token) {
            throw new ParameterException([
                'token不能为空'
            ]);
        }
        $valid = TokenService::verifyToken($token);
        return [
            'isValid'=>$valid
            ];
    }

    //第三方应用获取令牌
    public function getAppToken($ac = '',$se = ''){
        (new AppTokenGet())->goCheck();
        $app = new AppToken();
        $token = $app->get($ac,$se);
        return [
            'token' => $token
        ];

    }
}