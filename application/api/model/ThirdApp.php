<?php

namespace app\api\model;

class ThirdApp extends BaseModel{
    public static function check($ac,$se){
        $app = self::where('ac','=',$ac)->where('se','=',$se)->find();
        return $app;
    }
}