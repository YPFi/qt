<?php


namespace app\index\controller;
use app\facade\Banner;
use app\facade\Footer;
use think\Controller;

class Aboutus extends Controller
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
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'footer'=> $footer
        ];
        return $this->fetch('',$dataBase);
    }
}