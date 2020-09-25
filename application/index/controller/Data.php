<?php


namespace app\index\controller;

use think\App;
use think\Controller;
use think\Db;

class Data extends Controller
{
    const APIKEY = "API2148ae0ce68e8c4c01f9929755cc55e5";
    const APIACCOUNT = "ACCf90e62d57f99b71de5ec485b1ca9e6e7";

    //获取学科
    public function getSubjectList()
    {
        $xueduan = 3;
        $time = $times = time();
        $sign_data = [
            'xueduan' => $xueduan,
            'jgcode' => '112',
        ];
        $sign = $this->getSign(self::APIKEY, $sign_data, $times);
        $get_data = [
            'time' => $time,
            'sign' => $sign,
            'Account' => self::APIACCOUNT,
            'xueduan' => $xueduan,
            'jgcode' => '112',
        ];
        $url = "http://yunstudy.koo6.cn/Apis/Attrbute/getSubjectList";
        $subject = json_decode($this->Post($url, $get_data), true);
//        dump($subject['result']);
        foreach ($subject['result'] as $s) {
            dump($s);
            $data = [
                'id' => $s['id'],
                'name' => $s['name'],
                'stage' => $xueduan
            ];
            $res = Db::name('subject');
            if ($res->where('id', $s['id'])->count()) {
                //存在
                echo $s['id'] . '已经提交过';
            } else {
                //不存在
                $res->insert($data, true);
            }
        }
        echo "插入完成";
    }

    //获取年级
    public function getClass()
    {
        $xueduan = 1;
        $time = $times = time();
        $sign_data = [
            'xueduan' => $xueduan,
            'jgcode' => '112',
        ];
        $sign = $this->getSign(self::APIKEY, $sign_data, $times);
        $get_data = [
            'time' => $time,
            'sign' => $sign,
            'Account' => self::APIACCOUNT,
            'xueduan' => $xueduan,
            'jgcode' => '112',
        ];
        $url = "http://yunstudy.koo6.cn/Apis/Attrbute/getClassList";
        $class = json_decode($this->Post($url, $get_data), true);
//        dump($subject['result']);
        foreach ($class['result'] as $s) {
            dump($s);
//            Db::name('grade')->insert($s);
        }
        echo "插入完成";
    }

    //获取版本
    public function getVersions()
    {
        $xueduan = 3;
        $xueke = 9;
        $nianji = 10;
        $time = $times = time();
        $sign_data = [
            'xueduan' => $xueduan,
            'xueke' => $xueke,
            'nianji' => $nianji,
            'jgcode' => '112',
        ];
        $sign = $this->getSign(self::APIKEY, $sign_data, $times);
        $get_data = [
            'time' => $time,
            'sign' => $sign,
            'Account' => self::APIACCOUNT,
            'xueduan' => $xueduan,
            'xueke' => $xueke,
            'nianji' => $nianji,
            'jgcode' => '112',
        ];
        $url = "http://yunstudy.koo6.cn/Apis/Attrbute/getVersionsList";
        $class = json_decode($this->Post($url, $get_data), true);
//        dump($subject['result']);
        foreach ($class['result'] as $s) {
            dump($s);
            $res = Db::name('version');
            if ($res->where('id', $s['id'])->count()) {
                //存在
                echo $s['id'] . '已经提交过';
            } else {
                //不存在
                $res->insert($s, true);
            }
        }
        echo "插入完成";
    }

    //获取学期
    public function getSemester()
    {
        $xueduan = 3;
        $xueke = 1;
        $nianji = 12;
        $time = $times = time();
        $sign_data = [
            'xueduan' => $xueduan,
            'xueke' => $xueke,
            'nianji' => $nianji,
            'jgcode' => '112',
        ];
        $sign = $this->getSign(self::APIKEY, $sign_data, $times);
        $get_data = [
            'time' => $time,
            'sign' => $sign,
            'Account' => self::APIACCOUNT,
            'xueduan' => $xueduan,
            'xueke' => $xueke,
            'nianji' => $nianji,
            'jgcode' => '112',
        ];
        $url = "http://yunstudy.koo6.cn/Apis/Attrbute/getSemesterList";
        $class = json_decode($this->Post($url, $get_data), true);
//        dump($subject['result']);
        foreach ($class['result'] as $s) {
            dump($s);
            $res = Db::name('term');
            if ($res->where('id', $s['id'])->count()) {
                //存在
                echo $s['id'] . '已经提交过';
            } else {
                //不存在
                $data = [
                    'id' => $s['id'],
                    'name' => $s['name'],
                    'stage' => 1
                ];
                $res->insert($data, true);
            }
        }
        echo "插入完成";
    }

    //获取同步教材目录
    public function getCatalog(){
        $xueduan = 2;
        $xueke = 12;
        $nianji = 7;
        $xueqi = 2;
        $time = $times = time();
        $sign_data = [
            'xueduan' => $xueduan,
            'xueke' => $xueke,
            'nianji' => $nianji,
            'xueqi' => $xueqi,
            'jgcode' => '112',
            'num' => 100,
        ];
        $sign = $this->getSign(self::APIKEY, $sign_data, $times);
        $get_data = [
            'time' => $time,
            'sign' => $sign,
            'Account' => self::APIACCOUNT,
            'xueduan' => $xueduan,
            'xueke' => $xueke,
            'nianji' => $nianji,
            'xueqi' => $xueqi,
            'num' => 100,
            'jgcode' => '112',
        ];
        $url = "http://yunstudy.koo6.cn/Apis/Attrbute/getCatalogList";
        $class = json_decode($this->Post($url, $get_data), true);
//        dump($subject['result']);
        foreach ($class['result'] as $s) {
            dump($s);
//            $data = [
//                'name' => $s['name']
//            ];
//            Db::name('version')->insert($data);
        }
//        echo "插入完成";
    }

    //获取同步教材目录
    public function getVideo(){
        $xueduan = 2;
        $xueke = 12;
        $nianji = 7;
        $xueqi = 1;
        $v = 1;
        $m = 1324;
        $time = $times = time();
        $sign_data = [
            'tid' => 1,
            'mid' => $m,
            'num' => 60,
            'jgcode' => '112',
        ];
        $sign = $this->getSign(self::APIKEY, $sign_data, $times);
        $get_data = [
            'time' => $time,
            'sign' => $sign,
            'Account' => self::APIACCOUNT,
            'tid' => 1,
            'mid' => $m,
            'num' => 60,
            'jgcode' => '112',
        ];
        $url = "http://yunstudy.koo6.cn/Apis/Video/getVideoList";
        $class = json_decode($this->Post($url, $get_data), true);
        $i = 0;
        $order = 1;
//        $id = 2803;
        foreach ($class['result'] as $s) {
            $data = [
//                'id' => $id,
                'name' => $s['name'],
                'stage' => $xueduan,
                'grade' => $nianji,
                'subject' => $xueke,
                'term'  => $xueqi,
                'orderBy' => $order,
                'is_last' => '1',
                'version' => $v,
                'vkname' => $s['vkname']
            ];
            dump($data);
            $res = Db::name('chapter')->insert($data);
            if($res){
                $i++;
                $order++;
//                $id++;
            }
        }
        echo "插入完成".$i.'个！';
    }

//发送请求
    public function getSign($key, $data = '', $times)
    {
        // $times = time(); //当前时间戳
        if (!empty($data)) {
            ksort($data); // 按键名排序
            $str = implode('', $data); //由数组元素的值组合成字符串
        } else {
            $str = '';
        }
        $str = $str . $times . $key; //生成签名字符串
        return md5($str); //返签名
    }

    public function Post($url, $post_data)
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

    public function dataUpdate(){
        $data = [
            'term' => 2,
        ];
        Db::name('chapter')->where('id','>','4970')->update($data);
    }

}