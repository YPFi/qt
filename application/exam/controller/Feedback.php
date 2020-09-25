<?php


namespace app\exam\controller;


use think\Db;
use library\Controller;

class Feedback extends Controller
{
    protected $table = 'qt_contact';

    /**
     * 意见反馈
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '意见反馈';
        $query = $this->_query($this->table)->equal('status')->like('remake');
        $data = $query->order('isReply desc')->page();
    }

    /**
     * 留言回复
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    Public function edit()
    {
        $this->_form($this->table, 'form');
    }


}