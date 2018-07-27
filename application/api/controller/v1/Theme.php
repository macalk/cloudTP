<?php

namespace app\api\controller\v1;

use app\api\validate\IDCollection;
use app\api\model\Theme as ThemeModel;
use app\lib\exception\ThemeException;
use app\api\validate\IDMustBePostiveInt;

class Theme
{
    //@url /theme?id=id1,id2,id3...
    //return 一组theme模型
    public function getSimpleList($ids=''){
        if((new IDCollection())->goCheck()){
            $ids = explode(',',$ids);
            $result = ThemeModel::with(['topicImg','headImg'])->select($ids);
            if($result->isEmpty()) {
                throw new ThemeException();
            }
            return $result;
        };
    }

    //@url /theme/:id
    public function getComplexOne($id) {
        // return 'success';
        validate('IDMustBePostiveInt')->goCheck();
        $theme = ThemeModel::getThemeWithProducts($id);
        if(!$theme) {
            throw new ThemeException();
        }
        return $theme;
    }
}
