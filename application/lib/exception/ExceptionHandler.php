<?php

namespace app\lib\exception;

use Exception;
use think\exception\Handle;
use think\facade\Request; 
use think\facade\Log;

class ExceptionHandler extends Handle {
    
    private $code;
    private $msg;
    private $errorCode;
    //还需要返回当前请求的url 
    public function render(Exception $e) {

        if($e instanceof BaseException) {
            //如果是自定义的异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode; 
        }else {
 
            if(config('app_debug')) {
                //打开状态，返回框架默认的错误页面
                return parent::render($e);
            }else {
                $this->code = 500;
                $this->msg = '服务器内部错误';
                $this->errorCode = 999;
                $this->recordErrorLog($e);
            }
        }

        $request = request();
        $result = [
            'msg'=>$this->msg,
            'error_code'=>$this->errorCode,
            'request_url'=>$request->url()
        ];
        return json($result,$this->code);
    }

    private function recordErrorLog(Exception $e) {
        Log::init([
            'type'        => 'file',
            'level'       => ['error'],
        ]);
        Log::record($e->getMessage(),'error');
    }
}