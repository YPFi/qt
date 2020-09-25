<?php


namespace app\index\model;


use think\Db;

class Exam
{
    protected $table = 'qt_exercises';
    protected $version = 'qt_version';
    protected $grade = 'qt_grade';
    protected $subject = 'qt_subject';
    protected $term = 'qt_term';
    protected $chapter = 'qt_chapter';
    protected $vipInfo = 'qt_vip_info';
    protected $paper = 'qt_paper';
    protected $member;
    protected $exercises = 'qt_exercises';

    public function __construct()
    {
        $this->member = session('member');
    }

    //获取教材版本
    public function getGrade(){
        $member = $this->member;
        if ($member['vipType']== 1 || $member['vipType']== 0){
            $data = Db::name($this->grade)->where(['status'=>1,'stage'=>2])->select();
        }else{
            $vip = Db::name($this->vipInfo)->where('mid',$member['id'])->find();
            $data = Db::name($this->grade)->whereIn('id',$vip['grade'])->select();
        }
        return $data;
    }

    //获取科目信息
    public function getSubject($grade){
        $member = $this->member;
        $subject = [];
        if ($member['vipType']== 1 || $member['vipType']== 0){
            $data = Db::name($this->subject)->where(['status'=>1])
                ->where('find_in_set(:id,grade)',['id'=>$grade])
                ->whereOr('grade',0)
                ->select();
        }else{
            $vip = Db::name($this->vipInfo)->where('mid',$member['id'])->find();
            if (empty($vip['subject'])){
                //全科
                $data = Db::name($this->subject)->where(['status'=>1])
                    ->where('find_in_set(:id,grade)',['id'=>$grade])
                    ->select();
            }else{
                //非全科
                foreach (explode(",",$vip['subject']) as $k=>$sub){
                    $subInfo = explode("-", $sub);
//                    dump($subInfo);
                    if($subInfo[0] == $grade){
                        array_push($subject,$subInfo[1]);
                    }
                }
                array_unique($subject);
                $data = Db::name($this->subject)->whereIn('id',$subject)->select();
            }

        }
       return $data;
    }

    //获取分册信息
    public function getTerm($subject){
        $member = $this->member;
        $term = [];
        if ($member['vipType']== 1 || $member['vipType']== 0){
            $data = Db::name($this->term)->where(['status'=>1])
                ->where('find_in_set(:id,stage)',['id'=>2])
                ->select();
        }else{
            $vip = Db::name($this->vipInfo)->where('mid',$member['id'])->find();
            $subList = $vip['subject'];
            if (empty($subList)){
                $data = Db::name($this->term)->where(['status'=>1])
                    ->where('find_in_set(:id,stage)',['id'=>2])
                    ->select();
            }else{
                //非全科
                foreach (explode(",",$subList) as $k=>$sub){
                    $subInfo = explode("-", $sub);
//                    dump($subInfo);
                    if($subInfo[1] == $subject) {
                        array_push($term, $subInfo[2]);
                    }
                }
                array_unique($term);
                $data = Db::name($this->term)->where(['status'=>1])
                    ->whereIn('id',$term)
                    ->select();
            }
        }
        return $data;
    }

    //获取章节信息
    public function getExamChapter($data){
        $data = Db::name($this->chapter)->where($data)->field('id,name')
            ->order('orderBy')
            ->select();
        return $data;
    }

    //获取试卷信息
    public function getPage($chapter){
        $data = Db::name($this->exercises)->where(['chapter'=>$chapter,'status'=>1])
            ->field('id,chapter,title,optionA,optionB,optionC,optionD')
            ->select();
        return $data;
    }

    //添加做题日志
    public function addLog($data){
        $result = Db::name('qt_paper_log')->insert($data);
        return $result;
    }

    //获取习题详情页导航
    public function getNav($id){
        $chapter = Db::name($this->chapter)->where('id',$id)->find();
        $grade = Db::name($this->grade)->where('id',$chapter['grade'])->find();
        $subject = Db::name($this->subject)->where('id',$chapter['subject'])->find();
        $data = [
            'chapter'=> $chapter['name'],
            'grade'=> $grade['name'],
            'subject'=> $subject['name']
        ];
        return $data;
    }

    //获取下一个章节内容
    public function getNext($id){
        $chapter = Db::name($this->chapter)->where('id',$id)->find();
        $data = Db::name($this->chapter)
                    ->where('id','>',$id)
                    ->where(['subject'=>$chapter['subject'],'term'=>$chapter['term']])
                    ->field('id,name')
                    ->find();
        return $data;
    }

    //获取正确答案
    public function getAnswer($eid){
        $data = Db::name($this->exercises)->where(['id'=>$eid])->find();
        return $data['answer'];
    }

    //获取试卷信息
    public function getPaper($cid){
        $data = Db::name($this->paper)->where(['chapter'=>$cid])->find();
        return $data;
    }

    //添加错题记录
    public function insertError($data){
        $result = Db::name('qt_exam_error_record')->insert($data);
        return $result;
    }

    //添加做题记录
    public function insertRecord($data){
        $result = Db::name('qt_exam_record')->insert($data);
        return $result;
    }

    //获取错题信息
    public function getErrorExam($ids){
        $mid = session('member')['id'];
        $data = Db::name('qt_exam_error_record')->alias('er')
            ->join('qt_exercises ex','ex.id=er.eid')
            ->whereIn('eid',$ids)
            ->where('mid',$mid)
            ->field('er.*,ex.title,ex.optionA,ex.optionB,ex.optionC,ex.optionD,ex.remake')
            ->select();
        return $data;
    }

    //是否已写入错题本
    public function getCount($mid,$eid){
        $data = Db::name('qt_exam_error_record')->where(['mid'=>$mid,'eid'=>$eid])->count();
        return $data;
    }

    //是否存在会员详细
    public function getMemberCount($mid){
        $data = Db::name('qt_member_info')->where(['mid'=>$mid])->count();
        return $data;
    }

    //写入会员详细
    public function memberInfo($data,$count,$qzt,$mid){
        if($count == 0){
            $data = Db::name('qt_member_info')->insert($data);
        }else{
            $member = Db::name('qt_member_info')->where('mid',$mid)->field('reward')->find();
            $qt = $member['reward'] + $qzt;
            $data = Db::name('qt_member_info')->where('mid',$mid)->update(['reward'=>$qt]);
        }
        if($data){
            return true;
        }else{
            return false;
        }
    }

}