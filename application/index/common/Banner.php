<?php


namespace app\index\common;
use think\Db;

class Banner
{
    //获取banner信息
    protected $banner = 'qt_banner';
    public function getBanner($type){
        $data = Db::name($this->banner)->where(['type'=>$type,'status'=>1])->select();
        return $data;
    }
}