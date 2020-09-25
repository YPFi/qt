<?php
namespace app\index\controller;



use think\Db;

class Recharge extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    //获取用户余额
    public function getBalance(){
        $mid = input('mid');
        $data = Db::name('qt_member_info')->where('mid',$mid)->field('balance')->find();
        return json(['code'=>200,'msg'=>'查询成功','balance'=>$data['balance']]);

    }




}
