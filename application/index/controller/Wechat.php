<?php


namespace app\index\controller;
use think\Db;
use think\Controller;
use think\facade\Cache;



class Wechat extends Controller
{

    private $appid ; //公众号appid
    private $app_secret ; //公众号app_secret
    private $redirect_uri ; //授权之后跳转地址
    private $state = '';
    /*
     * 获取微信授权链接
     *
     * @param string $redirect_uri 跳转地址
     * @param mixed $state 参数
     */
    function __construct(){
        //-------生成唯一随机串防CSRF攻击
        $this->state = md5(uniqid(rand(), TRUE));
         $info = Db::name('qt_login_config')->where(['name'=>'wxlogin','status'=>1])->find();
         $this->appid = $info['appID'];
         $this->app_secret = $info['appKey'];
        $this->redirect_uri = $info['callback'];
    }

    public function get_authorize_url()
    {
        $redirect_uri = urlencode($this->redirect_uri);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$this->state}#wechat_redirect";
    }

    public function login(){
        $url = $this->get_authorize_url();
        header("Location:" . $url);
    }


    /*
     * 回调地址
     **/
    public function callback(){
        $code = input('code');
        if(isset($code)){
            $token = $this->get_access_token($code);
        }
        $access_token = $token['access_token'];
        $open_id = $token['openid'];
        if(isset($access_token)){
            $info = $this->get_user_info($access_token,$open_id);
        }
        if(isset($info)){
             $unionid = $info['unionid'];
//            $unionid = $info['openid'];
//            $nickname = $info['nickname'];
//            $headimgurl = $info['headimgurl'];
            $member = $this->isMember($unionid);
            $userInfo = $this->getInfo($unionid);

            if($member == 1){
                $token = setToken();
                $userInfo['token'] = $token;
                Cache::store('redis')->set($userInfo['id'],$token);
                session('member',$userInfo);
                return $this->redirect('index/index');
            }else {
                return $this->redirect('login/submitTel', ['unionid' => $unionid,'type'=>'wx']);
            }
        }

    }




    /*
     * 获取授权token
     *
     * @param string $code 通过get_authorize_url获取到的code
     */
    public function get_access_token($code)
    {
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
        $token_data = $this->http($token_url);


        if($token_data[0] == 200)
        {
            return json_decode($token_data[1], TRUE);
        }

        return FALSE;
    }

    /*
     * 获取授权后的微信用户信息
     *
     * @param string $access_token
     * @param string $open_id
     */
    public function get_user_info($access_token,$open_id)
    {
        if($access_token && $open_id)
        {
            $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $info_data = $this->http($info_url);

            if($info_data[0] == 200)
            {
                return json_decode($info_data[1], TRUE);
            }
        }

        return FALSE;
    }

    public function http($url, $method='GET', $postfields = null, $headers = array()){
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        curl_close($ci);
        return array($http_code, $response);
    }

    //判断用户是否已存在
    public function isMember($unionid){
        $count = Db::name('qt_member')->where(['WXunionID'=>$unionid,'status'=>1])->count();
        return $count;
    }

    //获取授权后的微信用户信息
    public function getInfo($unionid){
        $data = Db::name('qt_member')->where(['WXunionID'=>$unionid,'status'=>1])->find();
        return $data;
    }
}