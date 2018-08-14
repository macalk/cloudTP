<?php

namespace app\api\controller;

use think\Controller;

use app\api\service\Token as TokenService;

class BaseController extends Controller{
    public function checkPrimaryScope(){

        TokenService::needPrimaryScope();
        
    }
    public function checkExclusiveScope(){

        TokenService::needExclusiveScope();
        
    }
    
}
