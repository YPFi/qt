<?php


namespace app\facade;
use think\Facade;

class Footer extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\index\common\Footer';
    }

}