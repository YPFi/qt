<?php
namespace app\index\model;
use think\Db;
use think\Model;

class Member extends Model
{

    protected $memberInfo = 'qt_member_info';
    protected $member = 'qt_member';
    protected $examRecord = 'qt_exam_record';
    protected $vipType = 'qt_vip_info';
    protected $course = 'qt_course';
    /*
     * 获取会员信息
     * @param $id 会员ID
     *
     */
    public function getMember($id,$type=1){
        if ($type == 1){
            $data = Db::name($this->memberInfo)->where(['mid'=>$id,'status'=>1])->find();
        }else{
            $data = Db::name($this->member)->where(['id'=>$id,'status'=>1])->find();
        }

        return $data;
    }

    /*
     * 获取错题信息
     * @param $id 会员ID
     *
     */
    public function getExam($id){
        $data = Db::name($this->examRecord)->where(['mid'=>$id,'status'=>1])
                ->field('id,SUM(wrong_number),SUM(right_number),SUM(total_num)')->find();
        if ($data['SUM(wrong_number)'] > 180){
            $data['wrong'] = 180;
        }else{
            $data['wrong'] = $data['SUM(wrong_number)'];
        }
        return $data;
    }

    /*
     * 更新用户信息
     * @param $post 修改信息
     */
    public function updateInfo($post){
        $result = Db::name($this->member)->update($post);
        return $result;
    }

    /*
     * 验证手机号码
     * @param $id 会员ID
     * @param $phone 手机号码
     *
     */
    public function checkPhone($id,$phone){
        $result = Db::name($this->member)->where(['id'=>$id,'phone'=>$phone])->count();
        return $result;
    }

    /*
    * 修改密码
    * @param $id 会员ID
    * @param $pwd 密码
    *
    */
    public function updatePwd($id,$pwd){
        $result = Db::name($this->member)->where('id',$id)->update(['password'=>$pwd]);
        return $result;
    }


    /*
     * 更换头像
     * @param $id 会员ID
     *  @param $url 头像地址
     */
    public function updateImg($id,$url){
        $result = Db::name($this->member)->where('id',$id)->update(['head_img'=>$url]);
        return $result;
    }

    /*
     * 获取课程信息
     * @param $id 会员ID
     */
    public function getCourse($id){
        $info = Db::name($this->vipType)->where(['mid'=>$id,'status'=>1])->find();
        if($info['vipType']==1){
            $data = Db::name($this->course)->where('status',1)->select();
        }else{
            $subjects = explode(',',$info['subject']);
            $data=[];
            foreach ($subjects as $k=>$s){
                $grade = explode('-',$s)[0];
                $subject = explode('-',$s)[1];
                $term = explode('-',$s)[2];
                $where = [
                    'grade'=>$grade,
                    'subject'=> $subject,
                    'term'=> $term,
                    'status'=> 1
                ];
                $data[$k] = Db::name($this->course)->where($where)->find();
            }
        }

        return emptyArray($data);
    }

    /**
     * 意见反馈
     * $mid 会员id
     */
    public function getFeedback($mid){
        $info = Db::name('qt_contact')->where(['mid'=>$mid,'status'=>1])->select();
        $count = Db::name('qt_contact')->where(['mid'=>$mid,'status'=>1])->count();
        $data = [
            'info'=> $info,
            'count'=> $count
        ];
        return $data;
    }
}