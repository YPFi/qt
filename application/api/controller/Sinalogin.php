<?php


namespace app\api\controller;
use lib\sina\Saetv2;
use lib\sina\Saetclient;

class Sinalogin
{
    public $oauth;
    const AUTHORIZE_URL = "https://api.weibo.com/oauth2/authorize";     //请求授权地址
    const CLIENT_ID = "****";
    const CLIENT_SECRE = "****";



    public function __construct()
    {
        $this->oauth = new Saetv2(self::CLIENT_ID,self::CLIENT_SECRE);
    }
    public function login(){
        $login_url = $this->oauth->getAuthorizeURL(self::AUTHORIZE_URL);  //获取跳转地址
        $this->redirect($login_url);  //重定向
    }

    public function callback(){
        try {
            $token = $this->oauth->getAccessToken();    //获取token
        } catch (OAuthException $e) {
            dump($e);
        }
        $client = new Saetclient(sef::CLIENT_ID,self::CLIENT_SECRE,$token);       //实例化用户类
        $uid_get = $client->get_uid();
        $uid = $uid_get['uid'];     //用户ID
    }
}