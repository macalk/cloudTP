<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\validate\IDMustBePostiveInt;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;

class Pay extends BaseController {

    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];
    
    public function getPreOrder($id = ''){
        (new IDMustBePostiveInt()) -> goCheck();
        $pay = new PayService($id);
        return $pay->pay();
    }

    //支付回调
    public function receiveNotify(){
        //通知频率15、15、30、180、1800、1800、1800、1800、3600秒
        //1、监测库存量,超卖
        //2、更新订单的status状态
        //3、减库存
        //如果成功处理，返回微信成功处理的消息。否则需要返回没有成功处理。

        //特点：POST、xml格式、路由地址不携带参数

        // $notify = new WxNotify();
        // $notify->Handle();

        $xmlData = file_get_contents('php://input');
        $result = curl_post_raw('http:/test.cn/api/v1/pay/re_notif?XDEBUG_SESSION_START=13133',$xmlData);
    }

    //支付回调
    public function redirectNotify(){
        //通知频率15、15、30、180、1800、1800、1800、1800、3600秒
        //1、监测库存量,超卖
        //2、更新订单的status状态
        //3、减库存
        //如果成功处理，返回微信成功处理的消息。否则需要返回没有成功处理。

        //特点：POST、xml格式、路由地址不携带参数

        $notify = new WxNotify();
        $notify->Handle();
    }
    
    


}