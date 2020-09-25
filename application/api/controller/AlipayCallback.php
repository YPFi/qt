<?php


namespace app\api\controller;
use lib\Config;
use lib\AlipayTradeService;

class AlipayCallback
{
    public function notify_url()
    {
        $arr = $_POST;
        $result = AliPayPc::setNotify_url($arr);//进行验签
        //验签成功后
        //判断订单号是否存在数据库
        //进行业务处理----------------------
    }
}