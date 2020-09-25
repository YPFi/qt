<?php


namespace app\index\model;
use think\Db;
use think\Model;

class Video extends Model
{
    const APIKEY='API2148ae0ce68e8c4c01f9929755cc55e5';
    const APIACCOUNT='ACCf90e62d57f99b71de5ec485b1ca9e6e7';
    const APIURL = 'http://yunstudy.koo6.cn/Apis/Video/getVideoUrl';
    protected $video_log = 'qt_video_log';
    protected $vipInfo = 'qt_vip_info';
    protected $chapter = 'qt_chapter';
    protected $version = 'qt_version';
    protected $grade = 'qt_grade';
    protected $subject = 'qt_subject';
    protected $term = 'qt_term';
    protected $watch = 'qt_watch';
    protected $collect = 'qt_collect';
    protected $collectList = 'qt_collect_list';
    protected $teacher = 'qt_teacher';
    protected $note = 'qt_notes';

    /*
     * 获取播放信息
     * @param $vkn 格灵视频播放码
     * @param $id 会员ID
     *
     */
    public function getVideoInfo($vkn,$id){
        $times=time();
        $signData = [
            'vkname'=> $vkn,
            'jgcode'=> $id
        ];
        $sign = getSign(self::APIKEY,$signData,$times);
        $videoData = [
            'time' => $times,
            'sign' => $sign,
            'Account' => self::APIACCOUNT,
            'jgcode' => $id,
            'vkname' => $vkn
        ];
        $videoInfo = json_decode(Post(self::APIURL,$videoData),true);
        if($videoInfo['code'] == 9){
            $video = [
                'url' => $videoInfo['result']['chaoqing'],
                'img' => $videoInfo['result']['img'],
                'logo_position'=>$videoInfo['result']['logo_position'],
            ];
        }else{
            $video = [];
        }

        return $video;
    }


    /*
     * 获取章节列表
     * @param $id 章节id
     *
     */
    public function getChapterList($id){
        $where = Db::name($this->chapter)->where('id',$id)
                ->field('version,stage,grade,subject,term')
            ->find();
        if (!empty($where)){
            $chapter = Db::name($this->chapter)->where($where)->select();
        }else{
            $chapter = [];
        }
        return $chapter;
    }

    /*
     * 获取面包屑导航
     * @param $id 章节id
     *
     */
    public function getHead($id){
        $chapter = Db::name($this->chapter)->where('id',$id)->find();
        $version = Db::name($this->version)->where('id',$chapter['version'])->find();
        $grade = Db::name($this->grade)->where('id',$chapter['grade'])->find();
        $subject = Db::name($this->subject)->where('id',$chapter['subject'])->find();
        $term = Db::name($this->term)->where('id',$chapter['term'])->find();
        $head = [
            'version' => $version['name'],
            'grade' => $grade['name'],
            'subject' => $subject['name'],
            'term' => $term['name'],
            'chapter' => $chapter['name'],
        ];
        return $head;
    }

    /*
     * 获取观看量
     * @param $id 章节ID
     *
     */
    public function getWatch($id){
        $watch = Db::name($this->watch)->where(['chapter'=> $id, 'status'=> 1])->find();
        return $watch['num'];
    }

    /*
     * 是否收藏该章节视频
     * @param $id 章节ID
     * @param $mid 会员ID
     *
     */
    public function isCollect($id,$mid){
        $data = Db::name($this->collectList)->where(['chapter'=>$id, 'mid'=>$mid, 'status'=>1])->find();
        if (empty($data)){
            return 0;
        }else{
            return 1;
        }
    }

    /*
     * 获取收藏夹
     * @param $id 会员ID
     *
     */
    public function getCollect($id){
        $data = Db::name($this->collect)->where(['mid'=>$id,'status'=>1])->order('sort desc')->select();
        return $data;
    }

    /*
     * 添加到收藏夹
     * @param $post 数据参数
     */
    public function addCollect($post){
        $result = Db::name($this->collectList)->insert($post);
        return $result;
    }

    /*
     * 关注老师
     * @param $id 当前会员ID
     * @param $tid  老师ID
     *
     */
    public function follow($id,$tid){
        $follow = Db::name($this->teacher)->where('id',$tid)->field('	mid,count')->find();
        $data = [
            'mid' => $follow['mid'].','.$id,
            'count' => $follow['count']+1
        ];
        $result = Db::name($this->teacher)->where('id',$id)->update($data);
    }

    /*
     * 添加笔记
     * @param $post 笔记数据
     *
     */
    public function addNote($post){
        $data = [
            'mid' => $post['mid'],
            'chapter' => $post['chapter'],
            'content' => $post['text'],
        ];
        $result = Db::name($this->note)->insert($data);
        return $result;
    }

    /*
     * 获取笔记
     * @param $post 笔记数据
     *
     */
    public function getNote($post){
        $data  = Db::name($this->note)->where($post)->order('sort')->select();
        return $data;
    }

    /*
     * 删除笔记
     * @param $id 笔记ID
     *
     */
    public function delNote($id){
        $result  = Db::name($this->note)->delete($id);
        return $result;
    }
}