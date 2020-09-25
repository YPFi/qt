<?php


namespace app\site\model;
use think\Db;
use think\Model;

class Article extends Model
{
    protected $article_type = 'qt_article_type';
    protected $article = 'qt_article';

    public function getArticleType(){
        $data = Db::name($this->article_type)->where('status',1)->select();
        return $data;
    }

    //插入数据
    public function insertAtc($post){
        $data = Db::name($this->article)->insertGetId($post);
        return $data;
    }

    //更新数据
    public function updateAtc($post){
        $data = Db::name($this->article)->update($post);
        return $data;
    }

}