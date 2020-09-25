<?php


namespace app\index\controller;
use app\facade\Banner;
use app\facade\Footer;
use \app\index\model\Contact as cont;

class Contact extends Base
{
    public function index(){
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

        $dataBase = [
            'title'=> '意见反馈',
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'footActive'=> '',
            'footer'=> $footer
        ];

        return $this->fetch('',$dataBase);
    }

    //提交数据
    public function submit(){
        $cont = new cont();
        $member = session('member');
        $post = request()->post();
        $post['mid'] = $member['id'];
        $post['ip'] = request()->ip();
        $result = $cont->insertData($post);
        if ($result ==1){
            return $this->success('感谢您的反馈！');
        }else{
            return $this->success('反馈失败！');
        }
    }
}