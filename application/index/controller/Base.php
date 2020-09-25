<?php


namespace app\index\controller;
use think\App;
use think\Controller;
use think\facade\Session;
use think\facade\Cache;

class Base extends Controller
{
    public function __construct(App $app = null)
    {
    	parent::__construct($app);
        $member = session('member');
        if (!empty($member)){
            $token = Cache::store('redis')->get($member['id']);
            if ($token != $member['token']){
                Session::delete('member');
                // 清除session（当前作用域）
                Session::clear();
                return $this->error('当前账户在另一台设备登录！','login/index');
            }
        }else{
            return $this->redirect('login/index');
        }
    }
}