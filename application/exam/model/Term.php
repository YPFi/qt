<?php


namespace app\exam\model;
use think\Db;
use think\Model;

class Term extends Model
{
    protected $term = 'qt_term';
    protected $stage = 'qt_stage';
    protected $subject = 'qt_subject';

    //获取学段
    public function getStage(){
        $stage = Db::name($this->stage)->where('status',1)->select();
        return $stage;

    }

    //获取科目
    public function getSubject($stage){

        $subject = Db::name($this->subject)->where('find_in_set(:id,stage)',['id'=>$stage])->select();
        return $subject;

    }

    //插入科目
    public function insertTer($data){

        $result = Db::name($this->term)->insert($data);
        return $result;
    }

    //更新科目
    public function updateSub($data,$id){

        $result = Db::name($this->subject)->where('id',$id)->update($data);
        return $result;
    }

}