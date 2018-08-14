<?php

namespace app\api\service;

use Exception;
use app\lib\exception\WeChatException;
use app\api\model\User as UserModel;
use app\lib\exception\TokenException;
use app\lib\enum\ScopeEnum;
use think\facade\Cache;

class UserToken extends Token{
    protected $code;
    protected $wxLoginUrl;

    function __construct($code) {
        $this->code = $code;
        $this->wxLoginUrl = sprintf(config('wx.login_url'),$this->code);
    } 

    public function get() {
        $result = curl_get($this->wxLoginUrl);
        // var_dump($result);
        $wxResult = json_decode($result,true);//字符串变数组 没有true则是个对象
        if(empty($wxResult)) {
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        }else{
            $loginFail = array_key_exists('errcode',$wxResult);
            if($loginFail) {
                $this->processLoginError($wxResult);
            }else {
                return $this->grantToken($wxResult);
            }
        }
    }

    private function grantToken($wxResult) {
        //拿到opentid
        //数据库看一下，这个id是否已经存在
        //如果存在则不存在，如果不存在则存进数据库里
        //生成令牌，准备缓存数据，写入缓存
        //把令牌返回到客户端
        //key:令牌
        //value:wxResult,uid,scope(权限)


        $openid = $wxResult['openid']; 
        $user = UserModel::getByOpenID($openid);
        if($user) {
            $uid = $user->id;
        }else {
            $uid = $this->newUser($openid);
        }

        $cachedValue = $this->prepareCachedValue($wxResult,$uid);
        $token = $this->saveToCache($cachedValue);
        return $token;

    }

    private function saveToCache($cachedValue){
        $key = self::generateToken();
        $value = json_encode($cachedValue);
        //过期时间
        $token_in = config('setting.token_expire_in');

        //使用助手函数cache写入缓存
        $request = cache($key,$value,$token_in);
        if(!$request) {
            throw new TokenException([
                'msg'  =>  '服务器缓存异常',
                'errorCode'  =>  10005
            ]);
        }
        return $key;
    }

    private function prepareCachedValue($wxResult,$uid){
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        //scope=16 代表用户的权限数值
        $cachedValue['scope'] = ScopeEnum::User;
        //scope=32 代表CMS(管理员)用户的权限数值
        // $cachedValue['scope'] = ScopeEnum::Super;
        return $cachedValue;
    }

    private function newUser($openid) {
        $user = UserModel::create([
            'openid' => $openid
        ]);
        return $user->id;
    }

    private function processLoginError($wxResult) {
        throw new WeChatException([
            'mes' => $wxResult['errmsg'],
            'errorcode' => $wxResult['errcode']
            ]);
    }
}