<?php


namespace app\facade;
use think\Facade;


class Banner extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\index\common\Banner';
    }
}