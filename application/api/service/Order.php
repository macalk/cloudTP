<?php

namespace app\api\service;

use think\Db;
use app\api\model\Product;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use app\api\model\UserAddress;
use app\api\model\OrderProduct;

class Order{
    //订单的商品列表，也就是客户端传递过来的products参数
    protected $oProducts;
    //真实数据库的商品信息（包括库存量）
    protected $products;

    protected $uid;

    public function place($uid,$oProducts){
        //$oProducts和$products 作对比
        //$products从数据库查询出来
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $this->uid = $uid;

        $status = $this->getOrderStatus();
        if(!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        //开始创建订单快照
        $orderSnap = $this->snapOrder($status);
        //生成订单
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;
        

        return $order;
    }

    //根据订单信息查询真实的商品信息
    private function getProductsByOrder($oProducts){
        $oIDs = []; 
        foreach ($oProducts as $item){
            array_push($oIDs,$item['product_id']);
        }
        $products = Product::all($oIDs)->visible(['id','price','stock','name','main_img_url'])->toArray();
        
        return $products;
    }

    //创建订单
    private function createOrder($snap){
        //创建事务
        Db::startTrans();
        try{

            $orderNo = $this->makeOrderNo();
            $order = new \app\api\model\Order();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);
            $order->status = 1;
            $order->save();
    
            $orderID = $order->id;
            // $create_time = $order->create_time;
            foreach($this->oProducts as &$p) {/********要测试********/
                $p['order_id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);

            // 提交事务
            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                // 'create_time' => $create_time
            ];
        }catch(Exception $ex){
            // 回滚事务
            Db::rollback();
    
            throw $ex;
        }
    }

    //生成订单号
    public static function makeOrderNo(){
        $yCode = array('A','B','C','D','E','F','G','H','I','J');
        $orderSn = $yCode[intval(date('Y'))-2018].strtoupper(dechex(date('m'))).date('d').substr(time(),-7).substr(microtime(),2,5).sprintf('%02d',rand(0,99));
        return $orderSn;
    } 

    //生成订单快照
    public function snapOrder($status){
        $snap = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => null,
            'snapName' => '',
            'snapImg' => ''
        ];

        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];

        if(count($this->products)>1) {
            $snap['snapName'] .= '等';
        }
        return $snap;
    }

    private function getUserAddress(){
        $userAddress = UserAddress::where('user_id','=',$this->uid)->find();
        if(!$userAddress) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 60001, 
            ]);
        }else {
            return $userAddress->toArray();
        }
    }

    //对外开放查询接口
    public function checkOrderStock($orderID){
        $oProducts = OrderProduct::where('order_id','=',$orderID)->select();
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $status = $this->getOrderStatus();
        return $status;
    }

    private function getOrderStatus(){
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatusArray' => []
        ];

        foreach($this->oProducts as $oProduct){
            $pStatus = $this->getProductStatus($oProduct['product_id'],$oProduct['count'],$this->products);
            
            if(!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            array_push($status['pStatusArray'],$pStatus);
        }

        return $status;
    }

    private function getProductStatus($oPID,$oCount,$products){
        $pIndex = -1;
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0
        ];

        for($i = 0;$i<count($products);$i++) {
            
            if($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }

        if($pIndex == -1) {
            //客户端传递的id有可能根本就不存在
            throw new OrderException([
                'msg' => 'id为'.$oPID.'的商品不存在，创建订单失败'
            ]);
        }else {
            $product = $products[$pIndex];
            $pStatus['id'] = $product ['id'];
            $pStatus['count'] = $oCount;
            $pStatus['name'] = $product ['name'];
            $pStatus['totalPrice'] = $product['price']*$oCount;
            if($product['stock']-$oCount >= 0) {
                $pStatus['haveStock'] = true;
            }
        }
        return $pStatus;
    }

    
}