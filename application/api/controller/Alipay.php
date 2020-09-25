<?php


namespace app\api\controller;


class Alipay
{
    protected $config = [
        'app_id' => '2019091167192436',
        'notify_url' => '{:url(@api/Alipay/notify)}',
        'return_url' => '{:url(@api/Alipay/returnData)}',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAp7/RGfKXghi02OdssDUzPPKPoLavY7t9xrOSbI+lX4o5RyI5JHyuyJQRF0HJzBl+owD+OrpTMeJDHHsHzsPSbp23KKYjeLGQRohZE2Uatzho1iPVYQxUOEiUq2NtT1WT+9o6ElDOIwGOD1U1cHP9XpS8thbrSocv5XnonReNRftYvYIpZVyUCeqBa1RHBMbg6wvbqzWdug6cUN+t2E8kMnXl7GMolvoaRGOQ/9JWrq2jzXXqmEiBp86Otz0m7j/fmHcvT84+rhRwRFmEMlmLZu1NQ79ECJZDJxDpnav5b2T1kWGqBn5hHfynbAYLo0+f0M0/CxlEaPYMwodxnmmHjQIDAQAB',
        // 加密方式： **RSA2**
        'private_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAigJSX3qFiNCB/+WMz7K55Wj0WGrtHIFPpQqqYfdxNWbXaWJdtMjYIMMGxqTlsXpR2+8JN3j3ZjBSR9fdpMFOKwQ/tXACMuFGaG/nB6z3NFIAqaJmjJDw0sipl1qL6QzAG95xII4MSNH62v95MQkD/bsf+hLasq0VKoNgfDWeTP5AeOjy4qh7pARAvZWB48w8WYCI3OgaI2eW6OK1lcdY3AuDdc5u2DGUphJcdJtqUUze1B5VJehdaqjLOehcfyn7ypFSm0wrk4ctvUjx+XROhYqrbk3dsv/Mjs/4t/KTZpGqa7OI413CM/zUS5lS715YTWAOt8Rmx49Gq3++HchJ+wIDAQAB',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
//        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ];

    public function index()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '1',
            'subject' => 'test subject - 测试',
        ];

        $alipay = Pay::alipay($this->config)->scan($order);
        dump($alipay);

        return $alipay->send();// laravel 框架中请直接 `return $alipay`
    }

    public function returnData()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！

        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    public function notify()
    {
        $alipay = Pay::alipay($this->config);

        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况

            Log::debug('Alipay notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
        }

        return $alipay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }
}