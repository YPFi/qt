<?php


namespace app\index\model;
use think\Db;
use think\Model;

class Contact extends Model
{
    public function insertData($data){
        $result = Db::name('qt_contact')->insert($data);
        return $result;
    }
}