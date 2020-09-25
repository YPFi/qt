<?php


namespace app\exam\model;
use think\Db;
use think\Exception;
use think\Model;

class Member extends Model
{
    protected $member = 'qt_member';
    protected $location = 'qt_location';
    protected $vipType = 'qt_vip_type';
    protected $stage = 'qt_stage';
    protected $grade = 'qt_grade';
    protected $subject = 'qt_subject';
    protected $term = 'qt_term';
    protected $vip = 'qt_vip_info';
    protected $info = 'qt_member_info';
    //获取地理信息
    public function getLocation($province,$city,$isFrist){

        if($isFrist){
            $where = ['parent_id'=>0];
        }
        if($province && !$city){
            $where = ['parent_id'=>$province];
        }
        if (!$province && $city){
            $where = ['parent_id'=>$city];
        }
        $data = Db::name($this->location)->where($where)->select();
        return $data;

    }

    //获取vip类型
    public function getVipType(){

        $data = Db::name($this->vipType)->where('is_delete',0)->select();
        return $data;
    }

    //获取会员数据
    public function getMember($id){
        $data = Db::name($this->member)->alias('meb')->join('qt_vip_info vip','meb.id = vip.mid')
            ->where('meb.id',$id)
            ->field('meb.id,meb.name,meb.realName,meb.phone,meb.gender,meb.vipType,meb.province,meb.city,meb.area,meb.create_at,vip.stage,vip.grade,vip.subject,vip.endTime')
            ->find();
        $subject = $data['subject'];
        if($subject != 0){
            $subject_array = explode(",", $subject);
            $terms = [];
            foreach ($subject_array as $sub){

                $t = explode("-", $sub);
                $tm = $t[0].'-'.$t[2];
                array_push($terms,$tm);
            }
            $term = array_unique($terms);   //去掉重复值
        }else{
            $term = [];
        }

        $data['endTime'] = date('Y-m-d',$data['endTime']);
        $data['stageName'] = Db::name($this->stage)->where('id',$data['stage'])->value('name');
        $data['vipName'] = Db::name($this->vipType)->where('code',$data['vipType'])->value('name');
        $data['provinceName'] = Db::name($this->location)->where('id',$data['province'])->value('fullname');
        $data['cityName'] = Db::name($this->location)->where('id',$data['city'])->value('fullname');
        $data['areaName'] = Db::name($this->location)->where('id',$data['area'])->value('fullname');
        $data['term'] = implode(',',$term);
        return $data;
    }

    //更新会员数据
    public function updateMember($post){
        $status = '';
        $endTime = strtotime($post['endTime']);
        $grade = '';
        $subject = '';
        $term = '';
        if ($post['vipType'] !=1){
            $grade = implode(',',emptyArray($post['grade']));
            $subject = implode(',',emptyArray($post['subject']));
        }
        $admin = session('user');
        switch ($admin['parent_id']){
            case 0:
                $belongTo = $admin['id'];break;
            default:
                $belongTo = $admin['parent_id'];
        }
        $member_data = [
            'name' => $post['name'],
            'realName' => $post['realName'],
            'phone' => $post['phone'],
            'gender' => $post['gender'],
            'vipType' => $post['vipType'],
            'addBy' => $admin['id'],
            'belongTo' => $belongTo,
            'province' => $post['province'],
            'city' => $post['city'],
            'area' => $post['area'],
        ];

//            开启事务
        Db::startTrans();
        try{
            $mid = $post['id'];
            Db::name($this->member)->where('id',$mid)->update($member_data);
            $vip_info = [
                'vipType' => $post['vipType'],
                'stage' => $post['stage'],
                'grade' => $grade,
                'subject' => $subject,
                'endTime' => $endTime,
            ];
            Db::name($this->vip)->where('mid',$mid)->update($vip_info);
            // 提交事务
            Db::commit();
            $status = 1;
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            $status = 0;
        }
        return $status;
    }

    //获取学段
    public function getStage(){

        $data = Db::name($this->stage)->where('status',1)->select();
        return $data;
    }

    //获取年级
    public function getGrade($stage){

        $data = Db::name($this->grade)->where(['stage'=>$stage,'status'=>1])->select();
        return $data;

    }

    //获取科目
    public function getSubject($gradeId){
        $gradeID = explode('-',$gradeId)[0];
        $termID = explode('-',$gradeId)[1];
//        年级数据
        $grade = Db::name($this->grade)->where('id',$gradeID)->field('name')->find();
//        学期数据
        $term = Db::name($this->term)->where('id',$termID)->find();
//        学科数据
        $data = Db::name($this->subject)->where('find_in_set(:id,grade)',['id'=>$gradeID])
            ->whereOr('grade',0)
            ->order('id')->select();
        foreach ($data as $key=>$item){
            $data[$key]['id'] =  $gradeID.'-'.$data[$key]['id'].'-'.$termID;
            $data[$key]['name'] =  $grade['name'].$data[$key]['name'].'('.$term['name'].')';
        }

        return $data;

    }

    //获取学期
    public function getTerm($stage,$type){
        //        学期数据
        $data = '';
        if($type == 2){
            $data = Db::name($this->term)->whereIn('id',$stage)->select();
        }else if ($type == 0){
            $data = Db::name($this->term)->where('find_in_set(:id,stage)',['id'=>$stage])->select();
        }
        return $data;
    }

    //插入会员数据
    public function insertMember($post){
        $status = '';
        $now = date('Y-m-d H:i:s');
        $startTime = time();
        $endTime = strtotime($post['endTime']);
        $grade = '';
        $subject = '';
        if ($post['vipType'] !=1){
            $grade = implode(',',emptyArray($post['grade']));
            if(isset($post['subject'])){
                $subject = implode(',',emptyArray($post['subject']));
            }
        }else{
            //防止点击普通会员选择科目/分册后又点击超级会员
            if (isset($_POST['subject'])){
                $post['subject'] = '';
            }
        }

        $admin = session('user');
        switch ($admin['parent_id']){
            case 0:
                $belongTo = $admin['id'];break;
            default:
                $belongTo = $admin['parent_id'];
        }
        $member_data = [
            'name' => $post['name'],
            'realName' => $post['realName'],
            'phone' => $post['phone'],
            'password' => md5($post['password']),
            'gender' => $post['gender'],
            'vipType' => $post['vipType'],
            'addBy' => $admin['id'],
            'belongTo' => $belongTo,
            'province' => $post['province'],
            'city' => $post['city'],
            'area' => $post['area'],
            'create_at' => $now,
        ];

//            开启事务
        Db::startTrans();
        try{
            $userId = Db::name($this->member)->insertGetId($member_data);
            $vip_info = [
                'mid' => $userId,
                'vipType' => $post['vipType'],
                'stage' => $post['stage'],
                'grade' => $grade,
                'subject' => $subject,
                'startTime' => $startTime,
                'endTime' => $endTime,
            ];
            if(!empty($userId)){
                $result = Db::name($this->vip)->insert($vip_info);
                $result2 = Db::name($this->info)->insert(['mid'=> $userId,]);
            }else{
                $result = '';
            }
            if (!empty($result) && !empty($result2)){
                // 提交事务
                Db::commit();
                $status = 1;
            }

        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            $status = 0;
        }
        return $status;

    }

    //检查手机号码唯一性
    public function checkPhone($phone){
        $data = Db::name($this->member)->where('phone',$phone)->count();
        return $data;
    }

    //删除会员
    public function remove($id){
        $status = '';
        Db::startTrans();
            try{
                $member = Db::name($this->member)->whereIn('id',$id)->delete();
                $vip = Db::name($this->vip)->whereIn('mid',$id)->delete();
                $info = Db::name($this->info)->whereIn('mid',$id)->delete();
                if ($member && $vip && $info){
                    // 提交事务
                    Db::commit();
                    $status = 1;
                }

            }catch (\Exception $e){
                // 回滚事务
                Db::rollback();
                $status = 0;
            }

            return $status;
    }




}