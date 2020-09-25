<?php

namespace app\api\controller;
use lib\qq\Qc;
use think\Controller;
use think\Db;
use think\facade\Cache;
use app\index\controller\Login;


class Qqlogin extends Controller
{
    public function login()
    {
        $qc = new Qc();
        $url = $qc->qq_login();
        $this->redirect($url);  //重定向
    }


    public function callback(){
        $login = new Login();
        $qc = new Qc();
        $endTime = strtotime("+100 year");
        $qc->qq_callback();     //回调
        $qc->get_openid();      //获取openid
        $qc = new Qc();     //再次实例化QC方法，才能获取到完整的用户参数
        $datas = $qc->get_user_info();      //获取用户数据保存到$datas中，该数据不包含openid
        // $datas['openId'] = session('openid');   //将用户的openid加入到$datas数组中
        $datas['unionid'] = session('unionid');   //将用户的unionid加入到$datas数组中
        // dump($datas);die;
        $user = Db::name('qt_member')->where('QQunionID',$datas['unionid'])->find();
        //判断unionid是否存在
        if(!empty($user)){
        	//存在且无绑定号码
        	if(empty($user['phone'])){
        		//绑定号码
        		$this->redirect('@index/login/submitTel',['unionid'=>$datas['unionid'],'type'=>'qq']);
        	}else {
        		//设置登录信息
        		$token = setToken();
                $user['token'] = $token;
                //写入redis
                Cache::store('redis')->set($user['id'],$token);
                session('member',$user);
        		$this->redirect('@index/index/index');
        	}
        }else{
        	//uniondid不存在，创建用户

            Db::startTrans();
            try{
                $userData = [
                    'QQnick' => $datas['nickname'],
                    'phone' => '',
                    'password' => md5('123456'),
                    'gender' => $datas['gender_type'],
                    'head_img' => $datas['figureurl'],
                    'QQunionID' => $datas['unionid'],
                ];
                $mid = Db::name('qt_member')->insertGetId($userData);
                $vip_info = [
                    'mid' => $mid,
                    'vipType' => 0,
                    'stage' => '',
                    'grade' => '',
                    'subject' => '',
                    'term' => '',
                    'startTime' => time(),
                    'endTime' => $endTime,
                ];
                $res = Db::name('qt_vip_info')->insert($vip_info);
                // 提交事务
                if($res && $mid){
                    Db::commit();
                    $status = 1;
                }
            }catch (\Exception $e){
                // 回滚事务
                Db::rollback();
                $status = 0;
            }
        	if( $status == 1){
        		$this->redirect('@index/login/submitTel',['unionid'=>$datas['unionid'],'type'=>'qq']);
        	}else{
        		return $this->error('登录失败，请重新登录');
        	}
            
        }

    }
}