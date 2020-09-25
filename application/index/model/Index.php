<?php


namespace app\index\model;
use think\Db;
use think\Model;

class Index extends Model
{
    protected $banner = 'qt_banner';
    protected $version = 'qt_version';
    protected $teacher = 'qt_teacher';
    protected $subject = 'qt_subject';
    protected $grade = 'qt_grade';
    protected $term = 'qt_term';
    protected $chapter = 'qt_chapter';


    //获取教材版本
    public function getVersion(){
        $data = Db::name($this->version)->where('status',1)->order('id')->select();
        return $data;
    }

    //获取老师信息
    public function getTeacher($limit=null){
        $data = Db::name($this->teacher)->alias('tea')->join('qt_subject sub','tea.subject = sub.id')
            ->limit($limit)
            ->field('tea.*,sub.name as subject')
            ->select();
        return $data;
    }

    //获取年级信息
    public function getGrade($stage){
        $data = Db::name($this->grade)->where(['stage'=>$stage,'status'=>1])->select();
        return $data;
    }

    //获取科目信息
    public function getSubject($grade){
        $data = Db::name($this->subject)
            ->where('find_in_set(:grade,grade)',['grade'=>$grade])
            ->whereOr('grade',0)
            ->select();
        return $data;
    }

    //获取分册信息
    public function getTerm($stage){
        $data = Db::name($this->term)->where('find_in_set(:stage,stage)',['stage'=>$stage])->select();
        return $data;
    }

    //获取章节信息
    public function getChapter($get){
        $data = Db::name($this->chapter)->where($get)->select();
        return $data;
    }
}