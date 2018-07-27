<?php

namespace app\lib\exception;

class ForbiddenExcpetion extends BaseException {
    public $code = 403;
    public $msg = '权限不够';
    public $errorCode = 100001;
}
