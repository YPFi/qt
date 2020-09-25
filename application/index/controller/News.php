<?php


namespace app\index\controller;
use app\facade\Banner;
use app\facade\Footer;
use app\index\model\Article;
use think\Controller;




class News extends Controller
{
    public function index(){
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
            'title'=> '新闻资讯',
            'topBanner'=> $topBanner,
            'footBanner'=> $footBanner,
            'footer'=> $footer,
            'footActive'=> 'news'
        ];
        return $this->fetch('',$dataBase);
    }



    //新闻详情页
    public function details(){
        $id = input('id');
        $news = new Article();
        if (!isset($id)){
            return $this->error('非法访问！');
        }else{
            //阅读量自增
            $news->readInc($id);
            $data = $news->getNews($id,1);
            if(isMobile()){
                //手机端
                $topBanner = Banner::getBanner(3);
            }else{
                $topBanner = Banner::getBanner(1);
            }
            $footBanner = Banner::getBanner(0);

            //获取新闻
            $footer = Footer::getFooter();

            $newsHot = $news->getHot();
            $next = $news->getNext($id,$data['type']);   //上一篇
            $prev = $news->getPrev($id,$data['type']);   //下一篇
            $newsNow = $news->getNow();
            $dataBase = [
                'title'=> $data['title'],
                'topBanner'=> $topBanner,
                'footBanner'=> $footBanner,
                'news'=>$data,
                'hot'=>$newsHot,
                'now'=>$newsNow,
                'next'=> $next,
                'prev'=> $prev,
                'keywords'=>$data['keywords'],
                'footer'=> $footer,
                'footActive'=> 'news'
            ];
            return $this->fetch('',$dataBase);
        }

    }

    //新闻预览
    public function see(){
        $news = new Article();
        $id = input('id');
        $data = $news->getNews($id,3);
        $data['type'] = $news->getArticleType($data['type']);
        //获取新闻
        $footer = Footer::getFooter();
        $dataBase = [
            'title'=>$data['title'],
            'author'=>$data['author'],
            'create_at'=>$data['create_at'],
            'reprint'=>$data['reprint'],
            'tags'=>explode(",", $data['tags']),
            'content'=>$data['content'],
            'type'=>$data['type'],
            'footer'=> $footer,
            'footActive'=> 'news'
        ];
        return $this->fetch('',$dataBase);
    }

    //获取新闻信息
    public function getArticle(){
        $news = new Article();
        $type = input('type');
        $page = input('current');
//        dump($type);dump($page);
        $data = $news->getArticle($type,$page);
        if(!empty($data['data'])){
            return json(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        }else{
            return json(['code'=>400,'msg'=>'暂无数据','data'=>$data]);
        }
    }

    //关键字搜素
    public function keyword(){
        $news = new Article();
        $key = input('key');
        if (!isset($key)){
            return $this->error('关键字不存在！');
        }else{
            if(isMobile()){
            //手机端
            $topBanner = Banner::getBanner(3);
        }else{
            $topBanner = Banner::getBanner(1);
        }
            $footBanner = Banner::getBanner(0);
            $data = $news->getKeyNews($key);
            $dataBase = [
                'title'=> '相关推荐',
                'topBanner'=> $topBanner,
                'footBanner'=> $footBanner,
                'data'=> $data,
                'footActive'=> 'news'
            ];
            return $this->fetch('',$dataBase);
        }
    }
}