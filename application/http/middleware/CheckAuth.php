<?php

namespace app\http\middleware;
use think\Controller;
use think\Db;



class CheckAuth extends Controller
{
    protected $vipInfo = 'qt_vip_info';
    protected $video_log = 'qt_video_log';
    protected $chapter = 'qt_chapter';

    public function handle($request, \Closure $next)
    {
        if(request()->controller() == 'Video'){
            $post = $request->param();
            $member = session('member');
//            dump($member);
            $vipType = $member['vipType'];
            if($vipType != 1){
                //非超级会员，检查权限
                $result = $this->checkAuth($member['id'],$vipType,$post['id']);
            }else{
                //超级会员，会员有效期
                $result = $this->checkTime($member['id']);
            }
            if($result == 0){
                return $this->error('权限不足！','index/index');
            }elseif ($result == 2){
                return $this->error('会员过期请充值！','recharge/index');
            }else{
                return $next($request);
            }
        }
        return $next($request);

    }

    /*
     * 检查VIP是否过期
     * @param $id 用户ID
     *
     */
    public function checkTime($id){
        $time = time();
        $vipInfo = Db::name($this->vipInfo)->where(['mid'=>$id])->find();
        if($time < $vipInfo['endTime']){
            $flag = 1;
        }else{  //过期
            $flag = 2;
        }
        return $flag;
    }

    /*
     * 检查用户权限
     * @param $id 会员ID
     * @param $vipType 会员类型
     * @param $chapter 章节ID
     *
     */
    public function checkAuth($id,$vipType,$chapter){
        $flag = '';
        if ($vipType == 0){
            //注册会员，免费一个视频
            $count = Db::name($this->video_log)->where('mid',$id)->count();
            if ($count <= 1){
                $flag = 1;
            }else{
                $flag = 0;
            }
        }elseif ($vipType == 2){
            //普通会员，检查是否过期与是否购买
            //检查VIP是否过期
            $timeFlag = $this->checkTime($id);
            if ($timeFlag == 1){
                //未过期
                $chapterInfo = Db::name($this->chapter)->where('id',$chapter)->find();
                $vipInfo = Db::name($this->vipInfo)->where('mid',$id)->find();
                $subject = explode(",", $vipInfo['subject']);
                $term = [];
                foreach ($subject as $k=>$v){
                    $item = explode("-", $v);
                    $subject[$k] = $item[1];    //获得科目id
                    $term[$k] = $item[2];   //获取分册ID
                }
                $subject = implode('',$subject);
                $term = implode('',$term);
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
            }else{
                //过期
                $flag = 2;
            }

        }elseif($vipType == 3){
            //体验会员，免费三个视频
            $timeFlag = $this->checkTime($id);
            if ($timeFlag == 1){
                //未过期
                $count = Db::name($this->video_log)->where('mid',$id)->count();
                if ($count <= 3){
                    $flag = 1;
                }else{
                    $flag = 0;
                }
            }else{
                //过期
                $flag = 0;
            }

        }
        return $flag;
    }
}
