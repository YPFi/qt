<?php


namespace app\index\controller;
use think\Controller;
use think\facade\Cache;
use app\index\validate\User;
use app\index\model\Login as logn;
use app\api\controller\Wxlogin as Wx;
use app\api\controller\Qqlogin as QQ;
use alisms\Alisms;
use think\facade\Session;
use think\Db;

class Login extends Controller
{

    protected $wx;
    protected $qq;
    protected $sms;

    public function __construct() {
        parent::__construct();
        $this->wx = new Wx();   //实例化wx类
        $this->qq = new QQ();   //实例化QQ类
        $this->sms = new Alisms();   //实例化阿里云短信类
    }

    //登录首页
    public function index()
    {

        //获取运行浏览器环境
        $isWechat = $this->request->InApp;
        //微信浏览器
        if($isWechat == 'WeChat'){
            $member = session('member');
            if($member == NULL){
                return $this->redirect('Wechat/login');
            }else{
                return $this->redirect('index/index');
            }
        }

        $logn = new logn();
        if(request()->isPost()){
            $post = request()->post();
            $validate = new User();
            if ($validate->check($post)) {
                //处理业务逻辑
                $json = $logn->login($post);
                return $json;
            }else{
                return json(['code'=>0,'msg'=>$validate->getError()]);
            }
        }
        return $this->fetch();
    }

    //绑定手机
    public function submitTel()
    {
    	$logn = new logn();
    	$key = request()->param();
 
//    	$data = [
//    		'unionid' => $key[0],
//    		'type' =>$key[1]
//    	];
        $data = [
            'unionid' => '123',
            'type' => '456'
        ];
		
		//表单提交
		if(request()->isPost()){
			$post = request()->param();
			$count = $logn->checkPhone($post['phone']);
			if($count == 1){
				$nickName = Cache::store('redis')->get($post['unionid']);
				$result = $logn->updInfo($post['unionid'],$post['phone'],$post['type'],$nickName);
			}else{
				//写入数据库
	    		$result = $logn->insertPhone($post['unionid'],$post['phone'],$post['type']);
			}
			
			if($result){
	    		$token = setToken();
	    		$userInfo = $logn->getInfo($post['phone']); 
	    		if(!empty($userInfo)){
	    			$userInfo['token'] = $token;
	    			//写入redis
                        unset($userInfo['password']);
		               Cache::store('redis')->set($userInfo['id'],$token);
		               session('member',$userInfo);
		               return $this->success('绑定成功,默认密码：123456', 'index/index');
	    		}else{
	    			return $this->error('绑定失败！请重新绑定');
	    		}
	    	}else{
	    		return $this->error('绑定失败！请确认手机号码');
	    	}
			
		}
		
        return $this->fetch('',$data);
    }

	//发送验证码
	public function getCode(){
		$signName = '勤藤教育';
		//获取阿里云短信配置信息
		$config = getSmsConfig(1,$signName,1);	//$id,$sign,$tpl
		//销毁无用变量
		unset($config['id']);
		unset($config['status']);
		unset($config['type']);
		$logn = new logn();
		//点击发送验证码
		if(request()->isGet()){
		    $post = request()->param();
		    if(isset($post['unionid'])){
		        //绑定手机请求验证码
                $phone = input('phone');
                $unionid = input('unionid');
                $type = input('type');
                if(!empty($phone)){
                    $count = $logn->checkPhone($phone);
                    if($count == 1){
                        //会员信息已存在,获取用户信息昵称存入redis,删除
                        $nick = $logn->getNick($unionid,$type);
                        Cache::store('redis')->set($unionid,$nick,60);
                        $del = $logn->delUser($unionid,$type);
                        $smscode = getCode(); //生成验证码
                        //发送短信
                        $config['phone'] = $phone;
                        $templateCode = "{'code':'".$smscode."'}";
                        // dump($templateCode);
                        $result = $this->sms->sendSms($config,$templateCode);
                        if($result['Code'] == 'OK'){
                            Cache::store('redis')->set($phone,$smscode,300);
                        }
                        $flag = $result['Code'];
                        $msg = $result['Message'];
                    }else{
                        $smscode = getCode(); //生成验证码
                        //发送短信
                        $config['phone'] = $phone;
                        $templateCode = "{'code':'".$smscode."'}";
                        // dump($templateCode);die;
                        $result = $this->sms->sendSms($config,$templateCode);
                        if($result['Code'] == 'OK'){
                            Cache::store('redis')->set($phone,$smscode,300);
                        }
                        $flag = $result['Code'];
                        $msg = $result['Message'];
                    }
                    return json(['code'=>$flag,'msg'=>$msg]);

                }else{
                    return json(['code'=> 400,'msg'=> '手机号码不能为空']);
                }
            }else{
		        //注册请求验证码
                $smscode = getCode(); //生成验证码
                //发送短信
                $config['phone'] = $post['phone'];
                $templateCode = "{'code':'".$smscode."'}";
                // dump($templateCode);
                $result = $this->sms->sendSms($config,$templateCode);
                if($result['Code'] == 'OK'){
                    Cache::store('redis')->set($post['phone'],$smscode,300);
                }
                $flag = $result['Code'];
                $msg = $result['Message'];
            }
            return json(['code'=>$flag,'msg'=>$msg]);

    	}
    	
    	//验证码校验
    	if(request()->isPost()){
    		$phone = input('phone');
    		$inputCode = input('code');
			$senCode = Cache::store('redis')->get($phone);
    		if(isset($senCode)){
    			if($senCode == $inputCode){
    				return json(['code'=>200,'msg'=>'验证码通过！']);
    			}else{
    				return json(['code'=>400,'msg'=>'验证码错误！']);
    			}
    		}else{
    			return json(['code'=>404,'msg'=>'验证码过期']);	
    		}
    	}
	}

	//验证手机唯一性
    public function checkPhone(){
        $phone = input('phone');
        $login = new logn();
        $result = $login->checkPhone($phone);
        if($result == 1){
            return json(['code'=>400, 'msg'=>'该号码已被注册！']);
        }else{
            return json(['code'=>200, 'msg'=>'验证通过！']);
        }
    }

    //注册
    public function  regist(){
        $post = request()->param();
        $login = new logn();
        $result = $login->checkPhone($post['phone']);
        if($result == 1){
            //手机号码存在
            return json(['code'=>400, 'msg'=>'手机码号已存在，请更换手机号码注册']);
        }else{
            $res = $login->regist($post);
            if($res == 1){
                return json(['code'=>200, 'msg'=>'注册成功！正在跳转...']);
            }else{
                return json(['code'=>200, 'msg'=>'注册失败，请重新注册']);
            }
        }
    }

    //退出登录
    public function logout(){
        $member = session('member');
        // dump($member);
        $id = $member['id'];
        $redis = Cache::store('redis')->get($id);
        if(!empty($redis)){
            Cache::store('redis')->rm($id);  //销毁redis
        }
        unset($member,$id); //销毁数据
        Session::delete('member');
        // 清除session（当前作用域）
        Session::clear();
        return $this->redirect('login/index');
    }

    public function resetpwd(){
        $logn = new logn();
        if(request()->isPost()){
            $phone = input('phone');
            $pwd = input('pwd');
            $result = $logn->reset($phone,$pwd);
            if($result){
                return json(['code'=>200,'msg'=>'密码重置成功！']);
            }else{
                return json(['code'=>400,'msg'=>'密码重置失败！']);
            }
        }
        return $this->fetch();

    }

    //vip体验验证
    public function experience()
    {
        return $this->fetch();
    }

//    验证激活码
    public function testCode()
    {
        $str= input('code');
//        $code = "2IER";
        $code = strtoupper($str);
        $mid = Db::name('qt_code')->where('code', $code)->value("mid");
        if ($mid == -1) {
            $status = 1;
        } else {
            $status = 0;
        }
//        dump($status);die;
        return json(['code' => $status]);
    }

    //体验VIP提交
    public function submit()
    {
        $post = request()->param();
        $logn = new logn();
        $status = $logn->submitCode($post);
        return json(['code' => $status]);
    }


	
}