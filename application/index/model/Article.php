<?php


namespace app\index\model;


use think\Db;
use think\Model;

class Article extends Model
{

    protected $articleType = 'qt_article_type';
    protected $article = 'qt_article';

    //获取新闻信息
    public function getArticle($type,$page=1,$limit=7){
        $count = Db::name($this->article)->where(['type'=>$type,'status'=>1])->count();
        $page_list = ceil($count/$limit);
        if($page>$page_list){
            $page = $page_list;
        }
        $data = Db::name($this->article)->where(['type'=>$type,'status'=>1])->page($page,$limit)->select();
        $page = [
            'data'=> $data,
            'count'=> $count,
            'list'=> $page_list
        ];
        return $page;
    }



    /*
     * 获取文章详细信息
     * $id 文章ID
     * $status 文章状态
     * */
    public function getNews($id,$status){
        if($status == 3){
            $data = Db::name($this->article)
                ->where(['id'=>$id,'status'=>$status])
                ->whereTime('create_at', 'week')
                ->order('id desc')->find();
        }else{
            $data = Db::name($this->article)->alias('art')
                ->join('qt_article_type aty','art.type=aty.id')
                ->where(['art.id'=>$id,'art.status'=>$status])
                ->field('art.*,aty.name')
                ->find();
            $keywords = explode(';',$data['keywords']);
            $data['keywords'] = $keywords;
        }
        return $data;
    }

    //获取文章类型
    public function getArticleType($id){
        $data = Db::name($this->articleType)->where(['id'=>$id,'status'=>1])->find();
        return $data['name'];
    }

    //阅读量自增
    public function readInc($id){
        $result = Db::name($this->article)->where('id',$id)->setInc('readNum');
        return $result;
    }

    //获取热门资讯
    public function getHot(){
        $data = Db::name($this->article)->where(['type'=>3,'status'=>1])
            ->order('id desc')
            ->limit(6)
            ->field('id,title')
            ->select();
        return $data;
    }

    //获取最新资讯
    public function getNow(){
        $data = Db::name($this->article)->where(['status'=>1])
            ->order('id desc')
            ->limit(8)
            ->field('id,title,img')
            ->select();
        return $data;
    }

    //获取下一篇id
    public function getNext($id,$type){
        $data = Db::name($this->article)->where('id','>',$id)->where('type',$type)->limit(1)->find();
        return $data['id'];
    }

    //获取上一篇id
    public function getPrev($id,$type){
        $data = Db::name($this->article)->where('id','<',$id)->where('type',$type)->limit(1)->find();
        return $data['id'];
    }

    //获取相关关键字文章
    public function getKeyNews($key){
        $data = Db::name($this->article)->whereLike('keywords',"%{$key}%")->paginate();
        return $data;
    }

}