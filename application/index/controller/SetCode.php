<?php


namespace app\index\controller;


class SetCode
{
    public function setVipCode()
    {
        $num = 1000;
        $code = $this->vipcode($num);
        $i = 0;
        foreach ($code as $c) {
//            dump($c);
            $data = [
                'code' => $c,
                'status' => 1
            ];
            $result = Db::name('qt_code')->insert($data);
            if ($result == 1) {
                $i++;
            }
        }
        if ($i == $num) {
            echo "添加激活码成功！";
        }
    }


    public function vipcode($num)
    {
        $i = 0;
        $code = array();
        $j=100;
        while ($i < $num) {
            $english = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $rand = $english[rand(0, 25)]
                . strtoupper(dechex(date('m')))
                . date('d') . substr(time(), -5)
                . substr(microtime(), 2, 5)
                . sprintf('%02d', rand(0, 99));
            for (
                $a = md5($rand, true),
                $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
                $d = '',
                $f = 0;
                $f < 5;
                $g = ord($a[$f]),
                $d .= $s[($g ^ ord($a[$f + 5])) - $g & 0x1F],
                $f++
            ) ;
            $p = 'qzt117'.$d;
            array_push($code, $p);
            $i++;

        }
        return $code;
    }
}