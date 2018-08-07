<?php

namespace app\api\service;

require '../extend/WxPay/WxPay.Api.php';

use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\api\model\Product;
use app\lib\enum\OrderStatusEnum;

use Exception;
use think\facade\Log;
use think\Db;

class WxNotify extends \WxPay\WxPayNotify{
    public function NotifyProcess($data, &$msg){
        if($data['result_code'] == 'success') {
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try{
                $order = OrderModel::where('order_no','=',$orderNo)->find();
                if($order->status == 1) {
                    $service = new OrderService();
                    $stockStatus = $service->checkOrderStock($order->id);
                    if($stockStatus['pass']) {
                        $this->updateOrderStatus($order->id,true);
                        $this->reduceStock($stockStatus);
                    }else {
                        $this->updateOrderStatus($order->id,false);
                    }
                }

                // 提交事务
                Db::commit();
                return true;
            }
            catch (Exception $ex){
                Db::rollback();
                Log::error($ex);
                return false;
            }
        }else {
            return true;
        }
    }

    public function updateOrderStatus($orderID,$success){
        $status = $success?OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF;
        OrderModel::where('id','=',$orderID,)->update(['status'=>$status]);
    }
    public function reduceStock($stockStatus){
        foreach ($stockStatus['pStatusArray'] as $singlePStauts) {
            //setDec  直接对数据库进行减法
            Product::where('id','=',$singlePStauts['id'])->setDec('stock',$singlePStauts['count']);
        }

    }

}