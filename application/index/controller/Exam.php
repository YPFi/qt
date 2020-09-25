<?php


namespace app\index\controller;
use app\facade\Footer;
use app\index\model\Exam as Exams;
use app\facade\Banner;
use app\index\model\ErrorTotal;
use think\Db;

class Exam extends Base
{


    //主页
    public function index(){
        $exam = new Exams();
        //获取banner
        if(isMobile()){
            //手机端
            $topBanner = Banner::getBanner(3);
        }else{
            $topBanner = Banner::getBanner(1);
        }
        $footBanner = Banner::getBanner(0);

        //获取新闻
        $footer = Footer::getFooter();

        $grade = $exam->getGrade();
        $dataBase = [
            'title'=> '习题首页',
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'grade'=> $grade,
            'footer'=> $footer,
            'footActive'=> 'exam'
        ];
        return $this->fetch('',$dataBase);
    }

    //做题页面
    public function details(){
        $exam = new Exams();
        $id = input('id');
        $member = session('member');
        //获取banner
        if(isMobile()){
            //手机端
            $topBanner = Banner::getBanner(3);
        }else{
            $topBanner = Banner::getBanner(1);
        }
        $footBanner = Banner::getBanner(0);
        $nav = $exam->getNav($id);  //面包屑
        $page = $exam->getPage($id);
//        dump($nav);die;
        $dataBase = [
            'title'=> '习题详情页',
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'page'=> $page,
            'nav'=> $nav,
            'footActive'=> 'exam'
        ];
        //添加做题记录
        $work = [
            'mid'=>$member['id'],
            'chapter'=>$id
        ];
        $result = $exam->addLog($work);
        if($result){
            return $this->fetch('',$dataBase);
        }
    }

//    做题结果
    public function examResult(){
        $exam = new Exams();
        $post = request()->post();
        $member = session('member');
        $total = count($post)-1;    //总题数
        if ($total<=0){
            //未做题，直接提交
            return $this->error('请认真做题哦');
        }
        $chapter = $post['chapter'];
        $next = $exam->getNext($chapter); //下一个章节内容
        unset($post['chapter']);
        $isTrue = 0;    //正确题数
        $isFalse = 0;    //错误题数
        $falseTotal = [];    //错误题目
        $paper = $exam->getPaper($chapter);
        if(isMobile()){
            //手机端
            $topBanner = Banner::getBanner(3);
        }else{
            $topBanner = Banner::getBanner(1);
        }
        $footBanner = Banner::getBanner(0);
        $mid = $member['id'];
        foreach ($post as $p){
            $eid = explode('_',$p)[0];
            $select = explode('_',$p)[1];
            $answer = $exam->getAnswer($eid);
            if(strtoupper($select) == strtoupper($answer)){
                $isTrue++;
            }else{
                $isFalse++;
                array_push($falseTotal,$eid);
                $checkCount = $exam->getCount($mid,$eid);  //是否已存在

                if($checkCount == 0){
                    $errorData = [
                        'eid'=> $eid,
                        'mid'=> $mid,
                        'version'=> $paper['version'],
                        'stage'=> $paper['stage'],
                        'grade'=> $paper['grade'],
                        'subject'=> $paper['subject'],
                        'term'=> $paper['term'],
                        'chapterID'=> $paper['chapter'],
                        'chapterName'=> $paper['name'],
                        'right_option'=> $answer,
                        'select_option'=> $select,
                        'create_at'=> date('Y-m-d')
                    ];
                    $result = $exam->insertError($errorData);     //写入错题记录
                }

            }
        }
        $checkMember = $exam->getMemberCount($mid);  //会员详细是否已存在
        $qzt = $isTrue*5;
        $mdata = [
            'mid'=> $mid,
            'balance'=> 0,
            'total'=> 0,
            'RedeemLevel'=> '',
            'RedeemCode'=> '',
            'startTime'=> '',
            'endTime'=> '',
            'reward'=> $qzt
        ];
        $data = [
            'Pid'=> $paper['id'],
            'right_number'=> $isTrue,
            'wrong_number'=> $isFalse,
            'total_score'=> $total*5,
            'total_num'=> $total,
            'mid'=> $mid
        ];
        Db::startTrans();
            try{
                $result1 = $exam->memberInfo($mdata,$checkMember,$qzt,$mid);   //写入做题记录
                $result2 = $exam->insertRecord($data);   //写入做题记录
                if ($result1 && $result2){
                    Db::commit();
                }
            }catch (\Exception $e){
                // 回滚事务
                Db::rollback();
                return $this->error('阅卷出错!');
        }

        $baseData = [
            'title'=> '成绩单',
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'success'=> $isTrue,
            'error'=> $isFalse,
            'score'=>$isTrue*5,
            'next'=> $next,
            'errorList'=> implode(',',$falseTotal),
            'footActive'=> 'exam'
        ];
        return $this->fetch('',$baseData);
    }

    //查看错题
    public function seeError(){
        $exam = new Exams();
        if(isMobile()){
            //手机端
            $topBanner = Banner::getBanner(3);
        }else{
            $topBanner = Banner::getBanner(1);
        }
        $footBanner = Banner::getBanner(0);
        $ids = input('id');
        $exam_list = $exam->getErrorExam($ids);
        $nav = $exam->getNav($exam_list[0]['chapterID']);  //面包屑
        $next = $exam->getNext($exam_list[0]['chapterID']); //下一个章节内容
        $dataBase = [
            'title'=> '查看错题',
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'nav'=> $nav,
            'next'=> $next,
            'page'=> $exam_list,
            'footActive'=> 'exam'
        ];
        return $this->fetch('',$dataBase);
    }

    //错题本
    public function errorBook(){
        $error = new ErrorTotal();
        $member = session('member');
        //获取banner
        if(isMobile()){
            //手机端
            $topBanner = Banner::getBanner(3);
        }else{
            $topBanner = Banner::getBanner(1);
        }
        $footBanner = Banner::getBanner(0);

        //获取新闻
        $footer = Footer::getFooter();

        //错题信息
        $today = $error->getToday($member['id']);
        $week = $error->getWeek($member['id']);
        $mouth = $error->getMonth($member['id']);
        $total = $error->getTotal($member['id']);
        $grade = $error->getGrade($member['id']);
        $subject = $error->getSubject($member['id']);

        $dataBase = [
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'today'=> $today,
            'week'=> $week,
            'mouth'=> $mouth,
            'total'=> $total,
            'grade'=>$grade,
            'subject'=>$subject,
            'footer'=> $footer,
            'footActive'=> 'exam'
        ];
        return $this->fetch('',$dataBase);
    }


    //获取章节信息
    public function getChapter(){
        $error = new ErrorTotal();
        $member = session('member');
        $grade = input('grade');
        $subject = input('subject');
        $data = $error->getChapter($grade,$subject,$member['id']);
        if (empty($data)){
            return json(['code'=>400, 'msg'=>'未查询到相关数据','data'=>'']);
        }else{
            return json(['code'=>200, 'msg'=>'查询成功','data'=>$data]);
        }
    }

//    获取习题信息
    public function getExercise(){
        $error = new ErrorTotal();
        $cid = input('cid');
        if(isMobile()){
            $limit = 10;
        }else{
            $limit = 18;
        }
        $data = $error->getExercise($cid,1,$limit);
        $page = $error->getPage($cid);
        if (empty($data)){
            return json(['code'=>400, 'msg'=>'未查询到相关数据','data'=>'','page'=>'']);
        }else{
            return json(['code'=>200, 'msg'=>'查询成功','data'=>$data,'page'=>$page]);
        }
    }

    //分页信息
    public function page(){
        $error = new ErrorTotal();
        $page = input('current');
        $cid = input('cid');
        if(isMobile()){
            $limit = 10;
        }else{
            $limit = 18;
        }
        $data = $error->getExercise($cid,$page,$limit);
        if (empty($data)){
            return json(['code'=>400, 'msg'=>'未查询到相关数据','data'=>'','page'=>'']);
        }else{
            return json(['code'=>200, 'msg'=>'查询成功','data'=>$data,'page'=>$page]);
        }
    }


    //获取习题详细信息
    public function getExerciseInfo(){
        $error = new ErrorTotal();
        $eid = input('eid');
        $data = $error->getExerciseInfo($eid);
        if (empty($data)){
            return json(['code'=>400, 'msg'=>'未查询到相关数据','data'=>'']);
        }else{
            return json(['code'=>200, 'msg'=>'查询成功','data'=>$data]);
        }
    }

    //获取科目信息
    public function getSubject(){
        $exam = new Exams();
        $grade = input('grade');
        $data = $exam->getSubject($grade);
        if(empty($data)){
            return json(['code'=>400,'msg'=>'获取数据失败，请刷新页面','data'=>'']);
        }else{
            return json(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        }
    }

    //获取分册信息
    public function getTerm(){
        $exam = new Exams();
        $subject = input('subject');
        $data = $exam->getTerm($subject);
        if(empty($data)){
            return json(['code'=>400,'msg'=>'获取数据失败，请刷新页面','data'=>'']);
        }else{
            return json(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        }
    }

    //获取章节信息
    public function getExamChapter(){
        $exam = new Exams();
        $data = [
            'version'=>57,
            'stage'=>2,
            'status'=>1,
            'grade'=>input('grade'),
            'subject'=>input('subject'),
            'term'=>input('term')
        ];
        $result = $exam->getExamChapter($data);
        if(empty($result)){
            return json(['code'=>400,'msg'=>'未查询到章节信息','data'=>'']);
        }else{
            return json(['code'=>200,'msg'=>'查询成功','data'=>$result]);
        }
    }

}