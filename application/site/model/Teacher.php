<?php


namespace app\site\model;
use think\Db;
use think\Model;


class Teacher extends Model
{
    protected $table = 'qt_teacher';
    protected $stage = 'qt_stage';
    protected $grade = 'qt_grade';
    protected $subject = 'qt_subject';

    //查询学段信息
    public function  getStage(){
        $data = Db::name($this->stage)->where('status',1)->select();
        return $data;
    }

    //查询年级信息
    public function  getGrade($stage){
        $data = Db::name($this->grade)->where(['status'=>1,'stage'=>$stage])->select();
        return $data;
    }

    //查询科目信息
    public function  getSubject($grade){
        $data = Db::name($this->subject)
            ->where('find_in_set(:id,grade)',['id'=>$grade])
            ->where('status', 1)
            ->whereOr('grade',0)
            ->select();
        return $data;
    }

    //添加老师
    public function addTeacher($data){
        $result = Db::name($this->table)->insert($data);
        return $result;
    }
}