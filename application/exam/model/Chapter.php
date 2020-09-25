<?php


namespace app\exam\model;
use think\Db;
use think\Model;

class Chapter extends Model
{
    protected $version = 'qt_version';
    protected $stage = 'qt_stage';
    protected $grade = 'qt_grade';
    protected $subject = 'qt_subject';
    protected $term = 'qt_term';
    protected $chapter = 'qt_chapter';

    //获取教材版本信息
    public function getVersion(){
        $data = Db::name($this->version)->where('status',1)->select();
        return $data;
    }

    //获取学段信息
    public function getStage(){
        $data = Db::name($this->stage)->where('status',1)->select();
        return $data;
    }

    //获取年级信息
    public function getGrade($get){
        $data = Db::name($this->grade)->where(['stage'=>$get,'status'=>1])->select();
        return $data;
    }

    //获取科目信息
    public function getSubject($get){
        $data = Db::name($this->subject)
            ->where('find_in_set(:id,grade)',['id'=>$get])
            ->where('status', 1)
            ->whereOr('grade',0)
            ->select();
        return $data;
    }

    //获取分册信息
    public function getTerm($get){
        $data = Db::name($this->term)
            ->where('find_in_set(:id,stage)',['id'=>$get])
            ->where('status', 1)
            ->select();
        return $data;
    }

    //获取章节信息
    public function getChapter($get){
        $data = Db::name($this->chapter)->where($get)->select();
        return $data;
    }

}