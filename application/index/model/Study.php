<?php


namespace app\index\model;


use think\Db;
use think\Model;

class Study extends Model
{

    protected $exam_record = 'qt_exam_record';

    /*
     * 查询今日做题数据
     * $id 会员ID
     */
    public function getToday($id){
        $today = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
            ->whereTime('create_at', 'today')
            ->field('SUM(total_num) as total')->find();
        $yesterday = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
            ->whereTime('create_at', 'yesterday')
            ->field('SUM(total_num) as total')->find();
        $today = $today['total'];
        $yesterday = $yesterday['total'];
       if($today-$yesterday > 0){
           $flag = 1;
       }else{
           $flag = 0;
       }
       if ($today == 0){
           $rate = '0';
           $flag = 0;
       }else{
           $rate = sprintf("%01.2f", ($today-$yesterday)/$today*100);   //概率
       }
       $data = [
           'today'=>$today,
           'flag'=> $flag,
           'rate'=> $rate
       ];
       return $data;
    }

    /*
     * 查询本周做题数据
     * $id 会员ID
     */
    public function getWeek($id){
        $week = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
            ->whereTime('create_at', 'week')
            ->field('SUM(total_num) as total')->find();
        $last_week = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
            ->whereTime('create_at', 'last week')
            ->field('SUM(total_num) as total')->find();
        $week = $week['total'];
        $last_week = $last_week['total'];
        if($week-$last_week > 0){
            $flag = 1;
        }else{
            $flag = 0;
        }
        if ($week == 0){
            $rate = '0';
            $flag = 0;
        }else{
            $rate = sprintf("%01.2f", ($week-$last_week)/$week*100);   //概率
        }
        $data = [
            'week'=>$week,
            'flag'=> $flag,
            'rate'=> $rate
        ];
        return $data;
    }

    /*
     * 查询本月做题数据
     * $id 会员ID
     */
    public function getMonth($id){
        $month = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
            ->whereTime('create_at', 'month')
            ->field('SUM(total_num) as total')->find();
        $last_month = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
            ->whereTime('create_at', 'last month')
            ->field('SUM(total_num) as total')->find();
        $month = $month['total'];
        $last_month = $last_month['total'];
        if($month-$last_month > 0){
            $flag = 1;
        }else{
            $flag = 0;
        }
        if ($month == 0){
            $rate = '0';
            $flag = 0;
        }else{
            $rate = sprintf("%01.2f", ($month-$last_month)/$month*100);   //概率
        }

        $data = [
            'month'=>$month,
            'flag'=> $flag,
            'rate'=> $rate
        ];
        return $data;
    }

    /*
     * 查询本学期做题数据
     * $id 会员ID
     */
    public function getTerm($id,$type = 1){
        $time = time();
        $term = '';  //学期
        $y=date("Y",time());    //当前年
        $first_start_date = $y.'-03-01';
        $first_end_date = $y.'-08-31';
        $last_start_date = $y.'-09-01';
        $last_end_date = ($y+1).'-02-28';
        if ($type == 1){
            if (strtotime($first_start_date) <= $time && $time <= strtotime($first_end_date)){
                //上学期
                $term = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
                    ->whereBetweenTime('create_at', $first_start_date, $first_end_date)
                    ->field('SUM(total_num) as total')->find();
            }

            if (strtotime($last_start_date) <= $time && $time <= strtotime($last_end_date)){
                //下学期
                $term = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
                    ->whereBetweenTime('create_at', $last_start_date, $last_end_date)
                    ->field('SUM(total_num) as total')->find();
            }
            return $term['total'];
        }

        if ($type == 2){
            if (strtotime($first_start_date) <= $time && $time <= strtotime($first_end_date)){
                //上学期
                $date_list = [$y."-3",$y."-4",$y."-5",$y."-6",$y."-7",$y."-8"];
            }

            if (strtotime($last_start_date) <= $time && $time <= strtotime($last_end_date)){
                //下学期
                $date_list = [$y."-9",$y."-10",$y."-11",$y."-12",$y."-1",$y."-2"];
            }
            foreach ($date_list as $k=>$date){
                switch ($date){
                    case $y."-1":
                        $start = $y."-1-1";
                        $end = $y."-1-31";
                        break;
                    case $y."-2":
                        $start = $y."-2-1";
                        $end = $y."-2-28";
                        break;
                    case $y."-3":
                        $start = $y."-3-1";
                        $end = $y."-3-31";
                        break;
                    case $y."-4":
                        $start = $y."-4-1";
                        $end = $y."-4-30";
                        break;
                    case $y."-5":
                        $start = $y."-5-1";
                        $end = $y."-5-31";
                        break;
                    case $y."-6":
                        $start = $y."-6-1";
                        $end = $y."-6-30";
                        break;
                    case $y."-7":
                        $start = $y."-7-1";
                        $end = $y."-7-31";
                        break;
                    case $y."-8":
                        $start = $y."-8-1";
                        $end = $y."-8-31";
                        break;
                    case $y."-9":
                        $start = $y."-9-1";
                        $end = $y."-9-30";
                        break;
                    case $y."-10":
                        $start = $y."-10-1";
                        $end = $y."-10-31";
                        break;
                    case $y."-11":
                        $start = $y."-11-1";
                        $end = $y."-11-10";
                        break;
                    case $y."-12":
                        $start = $y."-12-1";
                        $end = $y."-12-31";
                        break;
                }
                $count = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
                    ->whereBetweenTime('create_at', $start,$end)
                    ->field('SUM(total_num) as total')->find();
                $error = Db::name($this->exam_record)->where(['mid'=>$id,'status'=>1])
                    ->whereBetweenTime('create_at', $start,$end)->field('SUM(wrong_number) as error')->find();

                if(!isset($error['error'])){
                    $error['error'] = 0;
                }

                if(!isset($count['total'])){
                    $count['total'] = 0;
                }

                if($count['total'] == 0){
                    $rate = 0;
                }else{
                    $rate = sprintf("%01.2f", ($error['error'])/$count['total']*100);   //概率
                }

                $count_list[] = $count['total'];
                $error_list[]= $error['error'];
                $rate_list[] = $rate;
            }
            $data = [
                'count'=>implode(',',$count_list),
                'error'=>implode(',',$error_list),
                'rate'=>implode(',',$rate_list),
                'date_list'=>implode(',',$date_list),
            ];
            return $data;

        }

    }
}