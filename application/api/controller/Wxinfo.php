<?php


namespace app\api\controller;


define("TOKEN", "yinpengfei");

class Wxinfo
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($this->checkSignature() && isset($echoStr)){
            echo $echoStr ;
            exit;
        }
    }


    private function checkSignature(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            // echo("验证成功");
            return true;
        }else{
            // echo("验证失败");
            return false;
        }
    }
}