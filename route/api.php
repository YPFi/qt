<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


Route::rule('wxlogin','api/Wxlogin/login');
Route::rule('sinalogin','api/Wxlogin/login');
Route::rule('qqlogin','api/Qqlogin/login');
Route::rule('wxinfo','api/Wxinfo/valid');
Route::alias('login','index/login');


return [

];
