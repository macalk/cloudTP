<?php

namespace app\lib\exception;

use Exception;

class baseException extends Exception{
    public $code = 400;
    public $msg = '参数错误';
    public $errorCode = 10000;

    //具有构造函数的类会在每次创建新对象时先调用此方法，所以非常适合在使用对象之前做一些初始化工作。
    public function __construct($params = []) {
        if(!is_array($params)) {
            return;
            // throw new exception('参数必须是数组');
        }

        if(array_key_exists('code',$params)) {
            $this->code = $params['code'];
        }
        if(array_key_exists('msg',$params)) {
            $this->msg = $params['msg'];
        }
        if(array_key_exists('errorCode',$params)) {
            $this->errorCode = $params['errorCode'];
        }
    }
}