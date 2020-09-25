<?php


namespace app\index\model;
use think\Db;
use think\Model;

class ErrorTotal extends Model
{
    protected  $table = 'qt_exam_error_record';
    protected  $error_record = 'qt_exam_record';
    protected  $exercises = 'qt_exercises';

    //查询今日错题量
    public function getToday($mid){
        $data = Db::name($this->table)
            ->where(['mid'=>$mid,'status'=>1])
            ->whereTime('create_at', 'today')->count();
        return $data;
    }

    //查询本周错题量
    public function getWeek($mid){
        $data = Db::name($this->table)
            ->where(['mid'=>$mid,'status'=>1])
            ->whereTime('create_at', 'week')->count();
        return $data;
    }

    //查询本月错题量
    public function getMonth($mid){
        $data = Db::name($this->table)
            ->where(['mid'=>$mid,'status'=>1])
            ->whereTime('create_at', 'month')->count();
        return $data;
    }

    //累计错题绿
    public function getTotal($mid){
        $total = Db::name($this->error_record)->where('mid',$mid)->sum('total_num');
        $error = Db::name($this->table)->where('mid',$mid)->count();
        $data = [
            'rate'=> sprintf("%01.2f", ($error/$total)*100).'%',
            'total'=>$error,
        ];

        return$data;
    }

    //获取年级
    public function getGrade($mid){
        $data = Db::name($this->table)->where('mid',$mid)
            ->group('grade')
            ->field('grade')
            ->select();
        $gradeID = [];
        foreach ($data as $item){
            $gradeID[] = $item['grade'];
        }
        $grade = Db::name('qt_grade')->whereIn('id',implode(',',$gradeID))->select();
        return $grade;
    }

    //获取科目
    public function getSubject($mid){
        $data = Db::name($this->table)->where('mid',$mid)
            ->group('subject')
            ->field('subject')
            ->select();
        $subjectID = [];
        foreach ($data as $item){
            $subjectID[] = $item['subject'];
        }
        $subject = Db::name('qt_subject')->whereIn('id',implode(',',$subjectID))->select();
        foreach ($subject as $k=>$s){
            switch ($s['id']){
                case 1:
                    $subject[$k]['img'] = '/static/index/images/icon/2.png' ;
                    break;
                case 2:
                    $subject[$k]['img'] = '/static/index/images/icon/3.png' ;
                    break;
                case 3:
                    $subject[$k]['img'] = '/static/index/images/icon/4.png' ;
                    break;
                case 4:
                    $subject[$k]['img'] = '/static/index/images/icon/5.png' ;
                    break;
                case 7:
                    $subject[$k]['img'] = '/static/index/images/icon/6.png' ;
                    break;
                case 5:
                    $subject[$k]['img'] = '/static/index/images/icon/7.png' ;
                    break;
                case 6:
                    $subject[$k]['img'] = '/static/index/images/icon/8.png' ;
                    break;
                case 9:
                    $subject[$k]['img'] = '/static/index/images/icon/9.png' ;
                    break;
                case 12:
                    $subject[$k]['img'] = '/static/index/images/icon/10.png' ;
                    break;
            }
        }
        return $subject;
    }

    //获取章节
    public function getChapter($grade,$subject,$mid){
        $data = Db::name($this->table)
            ->where(['mid'=>$mid,'grade'=>$grade,'subject'=>$subject])
            ->group('chapterID')
            ->field('chapterID,chapterName')->select();
        return $data;
    }

    //获取习题
    public function getExercise($cid,$page=1,$limit=18){
        $member = session('member');
        $mid = $member['id'];
        $count = Db::name($this->table)->where(['chapterID'=>$cid,'mid'=>$mid])->count();
        $pageList = ceil($count/$limit);
        if($page>$pageList){
            $page = $pageList;
        }
        $data = Db::name($this->table)
            ->where(['chapterID'=>$cid,'mid'=>$mid])
            ->field('eid')
            ->page($page,$limit)
            ->select();
        $exercise = [];
        foreach ($data as $k=>$d){
            $exercise[$k] = Db::name($this->exercises)->where('id',$d['eid'])->find();
        }
        return $exercise;
    }

    //获取分页信息
    public function getPage($cid,$limit=2){
        $count = Db::name($this->table)->where(['chapterID'=>$cid])->count();
        $pageList = ceil($count/$limit);
        $page = [
            'count'=>$count,
            'pageList'=>$pageList+1
        ];
        return $page;
    }

    //获取习题详细信息
    public function getExerciseInfo($eid){
        $data = Db::name($this->exercises)->alias('ex')
            ->join('qt_exam_error_record exe','ex.id=exe.eid')
            ->where('ex.id',$eid)
            ->field('ex.*,exe.select_option')
            ->find();
        return $data;
    }
}