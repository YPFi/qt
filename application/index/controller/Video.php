<?php


namespace app\index\controller;
use app\index\controller\Base;
use app\index\model\Video as v;
use think\App;
use think\Request;

class Video extends Base
{
    public $result;
    protected $middleware = ['CheckAuth'];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(){
        $v = new v();
        $post = request()->param();
        $member = session('member');
        $video = $v->getVideoInfo($post['vkn'],$member['id']);  //视频信息
        $chapter = $v->getChapterList($post['id']); //章节列表
        $head = $v->getHead($post['id']);   //面包屑导航
        $watch = $v->getWatch($post['id']);   //观看量
        $isCollect = $v->isCollect($post['id'],$member['id']);   //是否收藏
        $collect = $v->getCollect($member['id']);   //收藏夹
        $dataBase = [
            'title'=> $head['chapter'],
            'video' => $video,
            'chapter' => $chapter,
            'head' => $head,
            'watch' => $watch,
            'isCollect' => $isCollect,
            'vkn' => $post['vkn'],
            'id' => $post['id'],
            'collect' => $collect,
            'footActive'=> ''
        ];
        return $this->fetch('',$dataBase);
    }


    /*
     * 添加到收藏
     */
    public function addCollect(){
        $v = new v();
        $post = request()->post();
        $post['mid'] = session('member')['id'];
        $result = $v->addCollect($post);
        if($result == 1){
            return json(['code'=>200, 'msg'=>'已添加到收藏夹']);
        }else{
            return json(['code'=>400, 'msg'=>'添加到收藏夹失败']);
        }
    }

    /*
     * 获取播放地址
     *
     */
    public function getVideoUrl(){
        $v = new v();
        $vkn = input('vkn');
        $video = $v->getVideoInfo($vkn,session('member')['id']);
        if (empty($video)){
            return json(['code'=>400 ,'msg'=>'查询失败', 'data'=>'']);
        }else{
            return json(['code'=>200 ,'msg'=>'查询成功', 'data'=>$video]);
        }
    }

    /*
     * 关注老师
     */
    public function follow(){
        $v = new v();
        $tid = input('tid');
        $id = session('member')['id'];
        $result = $v->follow($id,$tid);
        if($result){
            return json(['code'=> 200, 'msg'=> '关注成功']);
        }else{
            return json(['code'=> 400, 'msg'=> '关注失败']);
        }
    }

    /*
     * 添加笔记
     */
    public function addNote(){
        $v = new v();
        $post = request()->post();
        $post['mid'] = session('member')['id'];
        $post['chapter'] = $post['id'];
        unset($post['id']);
        $result = $v->addNote($post);
        if($result == 1){
            return json(['code'=> 200, 'msg'=> '添加笔记成功！']);
        }else{
            return json(['code'=> 400, 'msg'=> '添加笔记失败！']);
        }
    }

    /*
     * 获取笔记
     */
    public function getNote(){
        $v = new v();
        $get = request()->get();
        unset($get['/index/video/getnote_html']);
        $get['chapter'] = $get['id'];
        unset($get['id']);
        $result = $v->getNote($get);
        if(!empty($result)){
            return json(['code'=> 200, 'msg'=> '查询笔记成功！', 'data'=>$result]);
        }else{
            return json(['code'=> 400, 'msg'=> '您还没有记过笔记哦！', 'data'=>'']);
        }
    }

    /*
     * 删除笔记
     */
    public function delNote(){
        $v = new v();
        $id = input('id');
        $result = $v->delNote($id);
        if($result == 1){
            return json(['code'=> 200, 'msg'=> '删除笔记成功！']);
        }else{
            return json(['code'=> 400, 'msg'=> '删除笔记失败！']);
        }
    }


    public function test(){
        return $this->fetch();
    }
}