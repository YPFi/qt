<?php


namespace app\index\model;
use think\Db;
use think\facade\Cache;
use think\Model;

class Login extends Model
{

    protected $member = 'qt_member';
    protected $vip_info = 'qt_vip_info';

    //处理登录逻辑
    public function login($post){
        $status = ['status'=>1];
        $isPhone = Db::name($this->member)->where('phone',$post['phone'])->where($status)->count();
        if ($isPhone == 0){
            return json(['code'=>401,'msg'=>'该号码不存在，请先注册后登录']);
        }else{
            $userInfo = Db::name($this->member)
                ->where('phone',$post['phone'])
                ->where('password',md5($post['pwd']))
                ->where($status)
                ->field('id,name,realName,phone,gender,head_img,vipType,birthday,email,cardID')
                ->find();
            if (!empty($userInfo)){
                //生成唯一token
                $token = setToken();
                $userInfo['token'] = $token;
                //写入redis
                Cache::store('redis')->set($userInfo['id'],$token);
//                dump(Cache::store('redis')->get($userInfo['id']));
                session('member',$userInfo);
                return json(['code'=>200,'msg'=>'验证通过,正在跳转']);
            }else{
                return json(['code'=>400,'msg'=>'密码错误']);
            }
        }
    }
    
    //验证手机
    public function checkPhone($phone){
    	$count = Db::name($this->member)->where(['phone'=>$phone,'status'=>1])->count();
    	return $count;
    }
    
    //写入手机号码
    public function insertPhone($union,$phone,$type){
    	$data = ['phone'=>$phone];
    	switch ($type) {
    		case 'qq':
    			// code...
    			$type = 'QQunionID';
    			break;
    		case 'wx':
    			// code...
    			$type = 'WXunionID';
    			break;
    		case 'sina':
    			// code...
    			$type = 'SinaopenID';
    			break;
    	}
    	$result = Db::name($this->member)->where([$type=>$union,'status'=>1])->update($data);
    	return $result;
    }
    
    //获取用户信息
    public function getInfo($phone){
    	$data = Db::name($this->member)->where(['phone'=>$phone,'status'=>1])
    			->field('id,name,realName,phone,gender,head_img,vipType')
    			->find();
    	return $data;
    }
    
    //获取第三方用户昵称
    public function getNick($unionid,$type){
    	switch ($type) {
    		case 'qq':
    			// code...
    			$type = 'QQunionID';
    			break;
    		case 'wx':
    			// code...
    			$type = 'WXunionID';
    			break;
    		case 'sina':
    			// code...
    			$type = 'SinaopenID';
    			break;
    	}
    	$data = Db::name($this->member)->where([$type=>$unionid,'status'=>1])
    			->field('QQnick,WXnick,	Sinanick')
    			->find();
    	return $data;
    }
    
    //更新用户信息
    public function updInfo($union,$phone,$type,$nickName){
    	switch ($type) {
    		case 'qq':
    			// code...
    			$type = 'QQunionID';
    			$nick = 'QQnick';
    			$nickName = $nickName['QQnick'];
    			break;
    		case 'wx':
    			// code...
    			$type = 'WXunionID';
    			$nick = 'WXnick';
    			$nickName = $nickName['WXnick'];
    			break;
    		case 'sina':
    			// code...
    			$type = 'SinaopenID';
    			$nick = 'Sinanick';
    			$nickName = $nickName['Sinanick'];
    			break;
    	}
    	$data = Db::name($this->member)->where(['phone'=>$phone,'status'=>1])->update([$type=>$union,$nick=>$nickName]);
    	return $data;
    }
    
    //销毁用户信息
    public function delUser($union,$type){
    	switch ($type) {
    		case 'qq':
    			// code...
    			$type = 'QQunionID';
    			break;
    		case 'wx':
    			// code...
    			$type = 'WXunionID';
    			break;
    		case 'sina':
    			// code...
    			$type = 'SinaopenID';
    			break;
    	}
    	$data = Db::name($this->member)->where([$type=>$union,'status'=>1])->delete();
    	return $data;
    }

    //注册用户信息
    public function regist($post){
        $data = [
            'phone'=> $post['phone'],
            'password'=> md5($post['pwd']),
            'head_img'=> '/static/index/images/head/face1.jpg'
        ];
        Db::startTrans();
        try{
            $mid = Db::name($this->member)->insertGetId($data);
            $vip_data = [
                'mid'=> $mid,
                'vipType'=> 0,
                'startTime'=> time(),
                'endTime'=> strtotime("+100 year")
            ];
            $result2 = Db::name($this->vip_info)->insert($vip_data);
            $result3 = Db::name('qt_member_info')->insert(['mid'=>$mid]);
            $result1 = Db::name('qt_collect')->insert(['mid'=> $mid,'name'=>'默认收藏夹','pid'=>0,'isLast'=>1,'sort'=>9999]);
            if ($result1 && $result2 && $result3){
                // 提交事务
                Db::commit();
            }
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
        }
        $userInfo = Db::name($this->member)->where(['phone'=>$post['phone'],'status'=>1])
                    ->field('id,name,realName,phone,gender,head_img,vipType,birthday,email,cardID,')
                    ->find();
        if (!empty($userInfo)){
            //生成唯一token
            $token = setToken();
            $userInfo['token'] = $token;
            //写入redis
            Cache::store('redis')->set($userInfo['id'],$token);
//                dump(Cache::store('redis')->get($userInfo['id']));
            session('member',$userInfo);
            return 1;
        }else{
            return 0;
        }
    }

    //重置密码
    public function reset($phone,$pwd){
        $result = Db::name($this->member)->where('phone',$phone)->update(['password'=>$pwd]);
        return $result;
    }

    //提交激活码
    public function submitCode($post){

        $code = $post['code'];
        $codeID = substr($code,0,6);
        $userInfo = Db::name('qt_codeprovince')->where('codeID',$codeID)->where('status',1)->find();
        $starttime = time();
        $endtime = strtotime("+1 year");
        $data = [
            'name'=> $post['name'],
            'phone'=> $post['tel'],
            'password'=> md5($post['password']),
            'gender'=> 1,
            'vipType'=> 1,
            'addBy'=> $userInfo['Uid'],
            'belongTo'=> $userInfo['Uid']
        ];
        // 启动事务
        Db::startTrans();
        try {
            $mid = Db::name($this->member)->insertGetId($data);

            $result1 = Db::name('qt_code')->where('code',$post['code'])->update(['mid' => $mid]);
            $vipData = [
                'mid'=> $mid,
                'vipType'=> 1,
                'version'=> 0,
                'stage'=> 2,
                'startTime'=> $starttime,
                'endTime'=> $endtime
            ];
            $result2 = Db::name($this->vip_info)->insert($vipData);
            $result3 = Db::name('qt_member_info')->insert(['mid'=>$mid]);
            $result4 = Db::name('qt_collect')->insert(['mid'=>$mid,'name'=>'默认收藏夹','pid'=>0,'sort'=>9999]);
            // 提交事务
            if ($result1 && $result2 && $result3 && $result4){
                $status = 1;
                Db::commit();
            }else{
                $status = 0;
                Db::rollback();
            }
        } catch (\think\Exception\DbException $exception) {
            // 回滚事务
            Db::rollback();
            $status = 0;
            dump($exception);
        }

        return $status;
    }

}