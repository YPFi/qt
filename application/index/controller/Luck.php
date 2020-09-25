<?php


namespace app\index\controller;
use app\facade\Banner;
use app\facade\Footer;
use app\index\model\Login as logn;
use think\Controller;
use think\Db;
use alisms\Alisms;
use think\facade\Cache;

class Luck extends Controller
{
    protected $sms;

    public function __construct() {
        parent::__construct();
        $this->sms = new Alisms();   //实例化阿里云短信类
    }




    public function index()
    {
        if(isMobile()){
            //手机端
            $topBanner = Banner::getBanner(3);
        }else{
            $topBanner = Banner::getBanner(1);
        }

        //获取新闻
        $footer = Footer::getFooter();
        $member = session('member');


        $memberInfo = Db::name('qt_member_info') ->where('mid',$member['id'])->field('qzt,reward,RedeemLevel,RedeemCode')->find();
        $memberInfo['sum'] = (intval($memberInfo['reward'] / 20));

        $dataBase = [
            'title'=> '幸运转盘',
            'topBanner'=> $topBanner,
            'footer'=> $footer,
            'memberInfo' => $memberInfo
        ];

        $member = session('member');
        if(!$memberInfo['RedeemCode'] && !$memberInfo['RedeemLevel']){
            $smscode = Redeem_Code(8); //生成兑换码
            $sex_months = strtotime("+6 months");
            if($memberInfo['qzt'] >= 1000 && $memberInfo['qzt'] < 2000){
                Db::name('qt_member_info')->where('mid',$member['id'])->update(['RedeemLevel'=>'三等奖','RedeemCode'=>$smscode,'startTime'=>time(),'endTime'=>$sex_months]);
                $this->note();


            }else if($memberInfo['qzt'] >= 2000 && $memberInfo['qzt'] < 5000){
                Db::name('qt_member_info')->where('mid',$member['id'])->update(['RedeemLevel'=>'二等奖','RedeemCode'=>$smscode,'startTime'=>time(),'endTime'=>$sex_months]);
                $this->note();


            }else if($memberInfo['qzt'] > 5000){
                Db::name('qt_member_info')->where('mid',$member['id'])->update(['RedeemLevel'=>'一等奖','RedeemCode'=>$smscode,'startTime'=>time(),'endTime'=>$sex_months]);
                $this->note();
            }
        }
        return $this->fetch('',$dataBase);
    }

    public function note(){
        $member = session('member');
        $memberInfo_phone = Db::name('qt_member') ->where('id',$member['id'])->field('phone,name')->find();
        $memberInfo = Db::name('qt_member_info') ->where('mid',$member['id'])->field('qzt,reward,RedeemLevel,RedeemCode')->find();
        $memberInfo['sum'] = (intval($memberInfo['reward'] / 20));
        $signName = '勤藤教育';
        //获取阿里云短信配置信息
        $config = getSmsConfig(1,$signName,6);	//$id,$sign,$tpl
        //销毁无用变量
        unset($config['id']);
        unset($config['status']);
        unset($config['type']);
        //绑定手机请求验证码
        $phone = $memberInfo_phone['phone'];
        //发送短信
        $config['phone'] = $phone;
        $name = $memberInfo_phone['name'];
        $num = $memberInfo['qzt'];
        $prizes = $memberInfo['RedeemLevel'];
        $code = $memberInfo['RedeemCode'];
        $templateCode = "{'name':'$name','num':'$num','prizes':'$prizes','code':'$code'}";
        $this->sms->sendSms($config,$templateCode);

    }

    //抽奖
    public function rand(){

        $prize_arr  = array(
            '0'=>array('id'=>1,'prize'=>'一等奖','v'=>10),
            '1'=>array('id'=>2,'prize'=>'二等奖','v'=>20),
            '2'=>array('id'=>3,'prize'=>'三等奖','v'=>30),
            '3'=>array('id'=>4,'prize'=>'下次没准就能中哦','v'=>40),
        );
        foreach($prize_arr as $key=>$val){
            $arr[$val['id']] = $val['v'];
        }
        $rid = $this->get_rand($arr);//根据概率获取奖项id
        $res['prize'] = $prize_arr[$rid-1]['prize'];//中奖项
        $res['id'] = $prize_arr[$rid-1]['id'];//中奖项
        unset($prize_arr[$rid-1]);//将中奖项从数组中剔除,剩下未中奖项
        shuffle($prize_arr);//打乱数组顺序
        for($i = 0;$i < count($prize_arr);$i++){
            $pr[] = $prize_arr[$i]['prize'];
        }
        $res['no'] = $pr;
        switch ($res['id']){
            case 1:
                $res['angle'] = mt_rand(330,359);
                break;
            case 2:
                $data = array(mt_rand(30,59),mt_rand(150,179));
                $angle = array_rand($data,1);
                $res['angle'] = $data[$angle];
                break;
            case 3:
                $data = array(mt_rand(90,119),mt_rand(210,239),mt_rand(270,299));
                $angle = array_rand($data,1);
                $res['angle'] = $data[$angle];
                break;
            case 4:
                $data = array(mt_rand(0,29),mt_rand(60,89),mt_rand(120,149),mt_rand(180,209),mt_rand(240,269),mt_rand(300,329),);
                $angle = array_rand($data,1);
                $res['angle'] = $data[$angle];
                break;
        }
            return json(['data'=>$res,'code'=>1]);
    }
    
    // 概率计算函数
    public function get_rand($proArr){
        $result = '';
        //概率是数组的总概率精度
        $proSum = array_sum($proArr); //对数组中所有值求和
        //概率数组循环
            foreach($proArr as $key=>$proCur){
                $randNum = mt_rand(1,$proSum);
                    if($randNum <= $proCur){
                        $result = $key;
                        break;
                    }else{
                        $proSum -= $proCur; 
                    }
            }
        unset($proArr);
        return $result;
    }

    //抽奖礼品写入数据库
    public function setqzt(){
        $qzt = input('qzt');
        $member = session('member');
        //开启事务
        if($qzt == 0){
            $result = 1;
        }else{
            $result = 0;
        }

        Db::startTrans();
        try{
            //修改数据
            $result1 = Db::name('qt_member_info')->where('mid',$member['id'])->setInc('qzt',$qzt);
            $result2 = Db::name('qt_member_info')->where('mid',$member['id'])->setDec('reward',20);
            if($result1 && $result2){
                Db::commit();
                $result = 1;
            }
        }catch (\Exception $e){
            // 回滚事务
            $result = 0;
            Db::rollback();

        }
        //查询修改后的数据返回
        $memberInfo = Db::name('qt_member_info') ->where('mid',$member['id'])->field('qzt,reward')->find();
        $memberInfo['sum'] = (intval($memberInfo['reward'] / 20));
        if($result == 1){
            return json(['code'=>200,'msg'=>'成功','qzt'=> $memberInfo['qzt'],'reward'=>$memberInfo['reward'],'sum'=>$memberInfo['sum']]);
        }else{
            return json(['code'=>400,'msg'=>'出错了，请刷新后重试','qzt'=> $memberInfo['qzt'],'reward'=>$memberInfo['reward'],'sum'=>$memberInfo['sum']]);
        }
    }
}