<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenExcpetion;
use app\lib\exception\TokenException;
use app\api\validate\OrderPlace;

class Order extends BaseController {

    //用户在选择商品后，向API提交包含它所选择商品的相关信息
    //API在接受到消息后，需要检查订单相关商品的库存量
    //有库存，把订单数据存入数据库中 = 下单成功了，返回客户端消息，告诉客户端可以支付了
    //调用支付接口，进行支付
    //再次进行库存量的查询
    //服务器这边可以调用微信的支付接口进行支付
    //微信会返回一个支付的结果（异步）
    //成功：也需要进行库存量的查询
    //成功：进行库存量的扣除

    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeorder']
    ];
    
    public function placeOrder(){
        (new OrderPlace())->goCheck();
        $products = input('post.products/a');
        $uid = TokenService::getCurrentUid();

        $order = new OrderService();
        $status = $order->place($uid,$products);
        // var_dump($status);
        return $status;
    }
}