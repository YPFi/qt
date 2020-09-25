<?php


namespace app\api\controller;
use lib\wechat\Wx;
use think\Controller;
use think\Db;
use think\facade\Cache;

class Wxlogin extends Controller
{
    protected $wx;
    public  function __construct()
    {
        $this->wx = new Wx();   //实例化wx类
    }

    //微信登录
    public function login(){
        $loginUrl = $this->wx->wx_login();  //登录
        $this->redirect($loginUrl);
    }

    //回调地址
    public function callback(){
        $endTime = strtotime("+100 year");
        $token = $this->wx->accessToken();    //获取token
        $data = $this->wx->get_user_info($token['access_token'],$token['openid']);     //获取用户信息
        $user = Db::name('qt_member')->where('WXunionID',$data['unionid'])->find();
        if(!empty($user)){
        	//存在且无绑定号码
        	if(empty($user['phone'])){
        		//未绑定号码
        		$this->redirect('@index/login/submitTel',['unionid'=>$data['unionid'],'type'=>'wx']);
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
                    'WXnick' => $data['nickname'],
                    'phone' => '',
                    'password' => md5('123456'),
                    'gender' => $data['sex'],
                    'head_img' => $data['headimgurl'],
                    'WXunionID' => $data['unionid'],
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

        	if($status == 1){
        		$this->redirect('@index/login/submitTel',['unionid'=>$data['unionid'],'type'=>'wx']);
        	}else{
        		return $this->error('登录失败，请重新登录');
        	}
            
        }
    }


}