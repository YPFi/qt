<?php


namespace app\index\validate;
use think\Validate;

class User extends Validate
{
    protected $rule = [
        'phone' => 'mobile',
        'pwd' => 'require|length:6,24',
    ];
    protected $message = [
        'phone.mobile' => '手机格式错误',
        'pwd.require' => '密码不能为空',
        'pwd.between' => '密码长度只能在6-24位之间',
    ];
}