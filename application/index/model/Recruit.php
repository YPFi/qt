<?php


namespace app\index\model;


use think\Db;

class Recruit
{
    protected $table = 'qt_article';

    public function getRecruit(){
        $data = Db::name($this->table)->where(['status'=>1,'type'=>4])->order('id desc')
            ->field('id,title,create_at,abstract,img')->paginate(12);
        return $data;
    }
}