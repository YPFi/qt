<?php


namespace app\index\common;
use think\Db;

class Footer
{
    //获取底部新闻信息
    protected $footer = 'qt_article';
    public function getFooter(){
        $data = Db::name($this->footer)->where(['status'=>1])->order('id desc')->limit('6')->select();
        return $data;
    }

}