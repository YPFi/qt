<?php


namespace app\exam\model;
use think\Db;
use think\Model;

class Subject extends Model
{
    protected $grade = 'qt_grade';
    protected $stage = 'qt_stage';
    protected $subject = 'qt_subject';

    //获取学段，年级信息
    public function getInfo(){
        $grade = Db::name($this->grade)->where('status',1)->select();
        $stage = Db::name($this->stage)->where('status',1)->select();
        $data = [
            'grade' => $grade,
            'stage' => $stage
        ];
        return $data;
    }

    //插入科目
    public function insertSub($data){

        $result = Db::name($this->subject)->insert($data);
        return $result;
    }

    //更新科目
    public function updateSub($data,$id){

        $result = Db::name($this->subject)->where('id',$id)->update($data);
        return $result;
    }

    public function getSubject($id){
        $data = Db::name($this->subject)->where('id',$id)->find();
        return $data;
    }
}