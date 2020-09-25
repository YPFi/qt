<?php


namespace app\api\controller;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
use think\Db;

class Wxpay
{

    protected $config = [
        'appid' => '', // APP APPID
        'app_id' => 'wxcd5e81cdf47b9438', // 公众号 APPID
        'miniapp_id' => '', // 小程序 APPID
        'mch_id' => '1555557601',
        'key' => 'Sam888qcZ2s0tzNsRURKcN5wTjVO9gUB',
        'notify_url' => '{:url("@api/Wxpay/notify")}',
        'cert_client' => '', // optional，退款等情况时用到
        'cert_key' => '',// optional，退款等情况时用到
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        // 'mode' => 'dev', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ];


    //微信支付
    public function index()
    {
        $data = request()->param();
        $out_rtrade_no = 'Q' . date('YmdHis') . mt_rand(100000, 999999);
        $order = [
            'out_trade_no' => $out_rtrade_no,
            'total_fee' => $data['money']*100, // **单位：分**
            'body' => $data['name'],
        ];

        $result = Pay::wechat($this->config)->scan($order);
        if($result['return_code']=='SUCCESS' && $result['result_code']=='SUCCESS'){
            $src = code($result['code_url']);
            $data = [
                'mid'=> session('member')['id'],
                'orderID'=> $out_rtrade_no,
                'money'=> $data['money']*100,
                'payType'=> 'wx',
                'payStatus'=> 0,
            ];
            $result = Db::name('qt_recharge_record')->insert($data);
            if($result){
                return json(['code'=>200,'msg'=>$result['return_msg'],'src'=>$src,'order_id'=>$out_rtrade_no]);
            }
        }else {
            return json(['code'=>400,'msg'=>$result['return_msg'],'src'=>'']);
        }

    }

    public function notify()
    {
        $pay = Pay::wechat($this->config);
        try{
            $data = $pay->verify(); // 是的，验签就这么简单！
            Log::debug('notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
        }
        return $pay->success()->send();// laravel 框架中请直接 `return $pay->success()`
    }

    public function getOrderStatus(){
        $order_id = input('order_id');
        $result = Pay::wechat($this->config)->find($order_id);
        if($result['result_code'] == 'SUCCESS' && $result['return_code'] == 'SUCCESS'){
            if($result['trade_state']=='SUCCESS'){
                $payStatus = 1;
            }else{
                $payStatus = 0;
            }
            Db::name('qt_recharge_record')->where('orderID',$order_id)->update(['payStatus'=> $payStatus]);
            return json(['code'=>200,'status'=>$result['trade_state'],'msg'=>$result['trade_state_desc']]);
        }else{
            return json(['code'=>400,'status'=>'ERROR','msg'=>'交易失败，请刷新后重试']);
        }

    }
}
