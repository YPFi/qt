<?php


namespace app\index\controller;
use think\Controller;
use \app\index\model\Member;


class Course extends Controller
{
    public function index(){

        return $this->fetch();
    }
}