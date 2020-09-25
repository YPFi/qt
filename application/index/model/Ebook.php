<?php


namespace app\index\model;

use think\Db;
use think\Model;

class Ebook extends Model
{
    protected $table = 'qt_ebook';


    public function getEbook($stage){
        $data = Db::name($this->table)->where(['status'=>1,'stage'=>$stage])
            ->field('id,name')
            ->order('id')->select();
        return $data;
    }

    public function getEbookInfo($id){
        $data = Db::name($this->table)->where(['status'=>1,'id'=>$id])->find();
        return $data;
    }
}