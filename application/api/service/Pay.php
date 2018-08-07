<?php

namespace app\api\service;

use Exception;
use think\Loader;
use think\facade\Log;

use app\api\service\Order as OrderService;
use app\api\service\Token;
use app\api\model\Order as OrderModel;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use app\lib\enum\OrderStatusEnum;


require '../extend/WxPay/WxPay.Api.php';
// use WxPay\WxPay;

/*************好像更新了....***********/
//extend/WxPay/WxPay.Api.php
// Loader::import('WxPay.WxPay',EXTEND_PATH,'Api.php');

class Pay{
    private $orderID;
    private $orderNO;

    function __construct($orderID){
        if(!$orderID) {
            throw new Exception('订单号不能为空');
        }
        $this->orderID = $orderID;
    }

    public function pay(){
        //订单号可能不存在
        //订单号存在，但是订单号和当前用户不匹配
        //订单有可能已经被支付过
        //进行库存量监测
        $this->checkOrderValid();
        $orderService = new OrderService();
        $status = $orderService->checkOrderStock($this->orderID);
        if(!$status['pass']) {
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrice']);
    }

    //向微信发送预订单请求
    private function makeWxPreOrder($totalPrice){
        //openid
        $openid = Token::getCurrentTokenVar('openid');
        if(!$openid) {
            throw new TokenException();
        }

        // $wxPay = new WxPay();
        $wxOrderData = new \WxPay\WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNO);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice*100);
        $wxOrderData->SetBody('零食商贩');
        // $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetOpenid('oQUaG5IRVE-G6rHdoTPtJ465GvWQ');
        $wxOrderData->SetSpbill_create_ip('127.0.0.1');
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));

        return $this->getPaySignature($wxOrderData);
    }

    //微信下单接口调用
    private function getPaySignature($wxOrderData){
        
        $wxOrder = \WxPay\WxPayApi::unifiedOrder($wxOrderData);
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
        }
        // var_dump($wxOrder);
        //prepay_id 向用户推送一个模板消息需要用
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    //签名
    private function sign($wxOrder){
        $jsApiPayData = new \WxPay\WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time().mt_rand(0,1000));//随机字符串
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $jsApiPayData->SetSign();

        $rawValues = $jsApiPayData->GetValues();//获取参数
        unset($rawValues['appId']);

        return $rawValues;
    }

    //对wxOrder进行处理
    private function recordPreOrder($wxOrder) {
        OrderModel::where('id','=',$this->orderID)->update(['prepay_id'=>$wxOrder['prepay_id']]);
        
    }

    private function checkOrderValid(){
        $order = OrderModel::where('id','=',$this->orderID)->find();
        if(!$order) {
            throw new OrderException();
        }
        if(!Token::isValidOperate($order->user_id)){
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }
        if($order->status != OrderStatusEnum::UNPAID) {
            throw new OrderException([
                'msg' => '该订单已支付过啦',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }
        $this->orderNO = $order->order_no;
        return true;
    }

    
} 