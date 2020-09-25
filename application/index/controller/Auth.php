<?php


namespace app\index\controller;
use think\Controller;
use think\Db;

class Auth extends Controller
{

    protected $paper_log = 'qt_paper_log';
    protected $chapter = 'qt_chapter';
    protected $vipInfo = 'qt_vip_info';

    public function checkMember(){
        $chapter = input('chapter');
        $member = session('member');
        $vip = Db::name($this->vipInfo)->where('mid',$member['id'])->find();
        $time = time();
        if($time > $vip['endTime']){
            return json(['code'=>403,'msg'=>'会员过期']);
        }else{
            if ($vip['vipType']>=0){
                $flag = $this->checkAuth($member['id'],$vip['vipType'],$chapter);
                if ($flag == 1){
                    return json(['code'=>200,'msg'=>'验证通过']);
                }else{
                    return json(['code'=>400,'msg'=>'权限不足！']);
                }
            }else{
                return json(['code'=>404,'msg'=>'会员信息不存在！']);
            }
        }
    }

    public function checkAuth($id,$vipType,$chapter){
        $flag = '';
        if ($vipType == 0){
            //注册会员，免费一个视频
            $count = Db::name($this->paper_log)->where('mid',$id)->count();
            if ($count < 1){
                $flag = 1;
            }else{
                $flag = 0;
            }
        }elseif ($vipType == 2){
            //普通会员，检查是否过期与是否购买
            $chapterInfo = Db::name($this->chapter)->where('id',$chapter)->find();
            $vipInfo = Db::name($this->vipInfo)->where('mid',$id)->find();
            $subject = explode(",", $vipInfo['subject']);
            $term = [];
            foreach ($subject as $k=>$v){
                $item = explode("-", $v);
                $subject[$k] = $item[1];    //获得科目id
                $term[$k] = $item[2];   //获取分册ID
            }
//            dump($term);
//            dump($subject);
            $term = implode('',$term);
            $subject = implode('',$subject);
            if($vipInfo['stage'] != $chapterInfo['stage']){
                //学段不一致
                $flag = 0;

            }elseif( !strstr($vipInfo['grade'], $chapterInfo['grade']) ){
                //章节年级未包含于vip信息中
                $flag = 0;
            }elseif( !strstr($subject, $chapterInfo['subject']) ){
                //章节科目未包含于vip信息中
                $flag = 0;
            }elseif ( !strstr($term, $chapterInfo['term']) ){
                //章节学期未包含于vip信息中
                $flag = 0;
            }else{
                $flag = 1;
            }
        }elseif($vipType == 3){
            //体验会员，免费三个视频
                //未过期
            $count = Db::name($this->paper_log)->where('mid',$id)->count();
            if ($count < 3){
                $flag = 1;
            }else{
                $flag = 0;
            }
        }elseif($vipType == 1){
            $flag = 1;
        }
        return $flag;
    }
}