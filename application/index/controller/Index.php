<?php
namespace app\index\controller;
use app\facade\Footer;
use think\Controller;
use app\index\model\Index as ind;
use app\facade\Banner;


class Index extends Controller
{
    protected $ind;
    public function __construct(\think\App $app = null)
    {
        $this->ind = new ind();
        parent::__construct($app);
    }

    public function index()
    {

        //会员数据
        $member = session('member');
        //登录状态
        if(empty($member)){
            $status = 0;
        }else{
            $status = 1;
        }
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

        //获取教材版本
        $version = $this->ind->getVersion();
        //获取年级信息
        $grade1 = $this->ind->getGrade(1);
        $grade2 = $this->ind->getGrade(2);
//        $grade3 = $this->ind->getGrade(3);
        //获取分册信息
        $term1 = $this->ind->getTerm(1);
        $term2 = $this->ind->getTerm(2);
        //获取老师信息
        $teacher = $this->ind->getTeacher(6);
        $dataBase = [
            'title'=> '勤藤教育',
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'version'=> $version,
            'teacher'=> $teacher,
            'grade1'=> $grade1,
            'grade2'=> $grade2,
//            'grade3'=> $grade3,
            'term1'=> $term1,
            'term2'=> $term2,
            'status'=> $status,
            'footActive'=> 'index',
            'footer'=> $footer
        ];

        return $this->fetch('',$dataBase);
    }

    //获取科目
    public function getSubject(){
        $grade = input('grade');
        $subject = $this->ind->getSubject($grade);
        if(empty($subject)){
            return json(['code'=>400, 'msg'=>'未查询到相关数据', 'data'=>'']);
        }else{
            return json(['code'=>200, 'msg'=>'查询成功', 'data'=>$subject]);
        }
    }

    //获取章节信息
    public function getChapter(){
        $get =request()->get();
        unset($get['/index/index/getchapter_html']);
        $chapter = $this->ind->getChapter($get);
//        dump($chapter);
        if (!empty($chapter)){
            return json(['code'=>200, 'msg'=>'查询成功', 'data'=>$chapter]);
        }else{
            return json(['code'=>400, 'msg'=>'未查询到数据', 'data'=>'']);
        }
    }

    public function moreTeacher(){
        //获取banner
        if(isMobile()){
            //手机端
            $topBanner = Banner::getBanner(3);
        }else{
            $topBanner = Banner::getBanner(1);
        }
        //获取老师信息
        $teacher = $this->ind->getTeacher();
//        dump($teacher);
        $dataBase = [
            'title'=> '勤藤名师',
            'topBanner'=> $topBanner,
            'teacher'=> $teacher,
            'member'=> session('member'),
            'footActive'=> 'index',
        ];

        return $this->fetch('',$dataBase);
    }
}
