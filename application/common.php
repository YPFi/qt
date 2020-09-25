<?php
use think\facade\Request;
use think\facade\Env;

//去除空值
function emptyArray($data){
    foreach ($data as $k=>$v){
        if (empty($data[$k])){
            unset($data[$k]);    //去空
        }
    }
    return $data;
}

//判断手机还是电脑
if (Request::isMobile()) {
        define('VIEW_PATH', __DIR__ . '/../application/index/view/mobile/');
    } else {
        define('VIEW_PATH', __DIR__ . '/../application/index/view/pc/');
}
function isMobile(){
    if (Request::isMobile()) {
        return true;
    } else {
        return false;
    }
}

//生成token
function setToken(){
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    $token = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    return $token;
}

//短信验证码
function getCode() {
            static $seed = array(0,1,2,3,4,5,6,7,8,9);
            $str = '';
            for($i=0;$i<6;$i++) {
                $rand = rand(0,count($seed)-1);
                $temp = $seed[$rand];
                $str .= $temp;
                unset($seed[$rand]);
                $seed = array_values($seed);
            }
            return $str;
        }

// 生成兑换码
function Redeem_Code($len, $chars=null)
{
    if (is_null($chars)){
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
    }
    mt_srand(10000000*(double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
        $str .= $chars[mt_rand(0, $lc)];
    }
    return $str;
}





//获取阿里云短信配置信息
function getSmsConfig($id,$sign,$tpl){
	$config = Db::name('qt_alisms_config')->where(['id'=>$id,'status'=>1])->find();
	$template = Db::name('qt_alisms')->where(['type'=> $tpl,'status'=>1,'signName'=>$sign])->field('templateCode')->find();
	$config['signName'] = $sign;
	$config['templateCode'] = $template['templateCode'];
	
	return $config;
}

//生成格灵课堂签名
function getSign($key,$data='', $times){
    if(!empty($data)){
        ksort($data); // 按键名排序
        $str = implode('', $data); //由数组元素的值组合成字符串
    }else{
        $str = '';
    }
    $str = $str.$times.$key; //生成签名字符串
    return md5($str); //返签名
}


//发送请求
function Post($url, $post_data)
{
    $postdata = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postdata,
            'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}

//$text  文本的内容
//$logo  logo图片
function code($text){
    $QRcode = new \PHPQRCode\QRcode();
    ob_start();
    $QRcode->png($text,false,'L',6);
    $imageString = base64_encode(ob_get_contents());
    ob_end_clean();
    $url='data:image/png;base64,'.$imageString;
    return $url;
}

