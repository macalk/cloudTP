<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\validate\AddressNew;
use app\api\service\Token as TokenService;
use app\api\model\User as UserModel;
use app\api\model\UserAddress;
use app\lib\exception\UserException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\TokenException;
use app\lib\exception\ForbiddenExcpetion;
use app\lib\enum\ScopeEnum;

class Address extends BaseController{

    // protected $beforeActionList = [
    //     'first' => ['only' => 'second,third']
    // ];

    // public function first(){
    //     echo 'first';
    // }
    // //API接口
    // public function second(){
    //     echo 'second';
    // }
    // public function third(){
    //     echo 'third';
    // }

    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createOrUpdateAddress,getUserAddress']
    ];

    public function createOrUpdateAddress(){

        $validate = new AddressNew();
        $validate->goCheck();
        //根据token获取uid
        //根据uid查询用户数据，判断用户是发存在，如果不存在，抛出异常
        //获取用户从客户端提交的地址信息
        //根据用户地址信息是否存在，从而判断是添加还是更新
        $uid = TokenService::getCurrentUid();
        $user = UserModel::get($uid);

        if(!$user) {
            throw new UserException();
        }

        $dataArray = $validate->getDataByRule(input('post.')); //返回验证的所有参数
        $userAddress = $user->address;//需要测试
        if(!$userAddress) {
            $user->address()->save($dataArray);//新增
        }else {
            $user->address->save($dataArray);//更新
        }
        return new SuccessMessage();
    }

    public function getUserAddress(){
        $uid = TokenService::getCurrentUid();
        // var_dump($uid);
        $userAddress = UserAddress::where('user_id',$uid)->find();
        if(!$userAddress) {
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 60001
            ]);
        }
        return $userAddress;

    }
}
