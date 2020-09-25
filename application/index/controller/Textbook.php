<?php


namespace app\index\controller;
use app\facade\Banner;
use app\facade\Footer;
use app\index\model\Ebook;


class Textbook extends Base
{
    public function index(){
        $ebook = new Ebook();
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

        $primary = $ebook->getEbook(1);
        $middle = $ebook->getEbook(2);
        $dataBase = [
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'primary'=>$primary,
            'middle'=>$middle,
            'footer'=> $footer,
            'footActive'=>''
        ];
        return $this->fetch('',$dataBase);
    }

    public function ebook(){
        $ebook = new Ebook();
        $id = input('id');
        $book = $ebook->getEbookInfo($id);
        return $this->fetch('',$book);
    }
}