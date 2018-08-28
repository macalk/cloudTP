<?php

namespace app\api\service;

use Request;
use Exception;
use think\facade\Cache;
use app\lib\exception\TokenException;
use app\lib\exception\ForbiddenExcpetion;
use app\lib\enum\ScopeEnum;

class Token{
    public static function generateToken(){
        //32个字符组成一组随机字符串
        $reandChars = getRandChar(32);
        //当前时间戳
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        //salt 盐
        $salt = config('secure.token_salt');

        //用3组字符串进行MD5加密
        return md5($reandChars.$timestamp.$salt);
        
    }

    public static function getCurrentTokenVar($key){
        $token = Request::instance()->header('token');
        $vars = Cache::get($token);
        if(!$vars) {
            throw new TokenException();
        }else {
            if(!is_array($vars)){
                $vars = json_decode($vars,true);
            }
            if(array_key_exists($key,$vars)) {
                return $vars[$key];
            }else {
                throw new Exception('尝试获取的token变量并不存在');
            }
            
        }
    } 

    public static function getCurrentUid(){
        //token
        $uid = self::getCurrentTokenVar('uid');
        return $uid;
    }


    //需要用户和CMS管理员都可以访问的权限
    public static function needPrimaryScope(){
        $Scope = self::getCurrentTokenVar('scope');
        if($Scope) {
            if($Scope >= ScopeEnum::User) {
                return true;
            }else {
                throw new ForbiddenExcpetion();
            }
        }else {
            throw new TokenException();
        }
    }
    //只有用户才能访问的接口权限
    public static function needExclusiveScope(){
        $Scope = self::getCurrentTokenVar('scope');
        if($Scope) {
            if($Scope == ScopeEnum::User) {
                return true;
            }else {
                throw new ForbiddenExcpetion();
            }
        }else {
            throw new TokenException();
        }
    }

    //检测用户是否合法
    public static function isValidOperate($checkedUID) {
        if(!$checkedUID) {
            throw new Exception('检测UID时必须传入一个被检测的UID');
        }
        $currentOperateUID = self::getCurrentUid();
        if($currentOperateUID == $checkedUID) {
            return true;
        }
        return false;
    } 

    //token是否有效
    public static function verifyToken($token){
        $exist = Cache::get($token);
        if($exist) {
            return true;
        }else {
            return false;
        }
    }
}