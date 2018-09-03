<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');

//获取banner ID
Route::get('api/:version/banner/:id','api/:version.Banner/getBanner');

//获取theme主题列表
Route::get('api/:version/theme','api/:version.Theme/getSimpleList');

//获取theme列表下一个信息
Route::get('api/:version/theme/:id','api/:version.Theme/getComplexOne');

//最近新品
Route::group('api/:version/product',function(){
    Route::get('/by_category','api/:version.Product/getAllInCategory');
    Route::get('/:id','api/:version.Product/getOne',[],['id'=>'\d+']);
    Route::get('/recent','api/:version.Product/getRecent');
});


//获取所有分类
Route::get('api/:version/category/all','api/:version.Category/getAllCategories');

//获取Token
Route::Post('api/:version/token/user','api/:version.Token/getToken');
//检测token
Route::Post('api/:version/token/verify','api/:version.Token/verifyToken');

//更新或新增收货地址
Route::Post('api/:version/address','api/:version.Address/createOrUpdateAddress');
//获取用户地址
Route::get('api/:version/address','api/:version.Address/getUserAddress');

Route::Post('api/:version/second','api/:version.Order/second');
Route::Post('api/:version/third','api/:version.Address/third');

//下单
Route::Post('api/:version/order','api/:version.Order/placeOrder');
//历史订单
Route::Post('api/:version/order/by_user','api/:version.Order/getSummaryByUser');
//订单详情
Route::get('api/:version/order/:id','api/:version.Order/getDetail',[],['id'=>'\d+']);


//预支付
Route::Post('api/:version/pay/pre_order','api/:version.Pay/getPreOrder');
//支付回调
Route::Post('api/:version/pay/notify','api/:version.Pay/receiveNotify');