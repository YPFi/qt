<?php


namespace app\site\controller;
use library\Controller;
use think\Db;


class Api extends Controller
{
    protected $table = 'qt_login_config';
    protected $sms = 'qt_alisms';
    protected $sms_type = 'qt_alisms_type';


    /**
     * 登录API
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function login()
    {
        $this->title = '登录API';
        $query = $this->_query($this->table)->like('name');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 添加登录API类型
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function addLogin()
    {
        $this->_form($this->table, 'logo_form');
    }

    /**
     * 编辑登录API类型
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editLogin()
    {
        $this->_form($this->table, 'logo_form');
    }



    /**
     * 禁用登录API
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbidLogin()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用登录API
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resumeLogin()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除登录API
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function removeLogin()
    {
        $this->_delete($this->table);
    }

    /**
     * 阿里云短信API
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function sms()
    {
        $this->title = '阿里云短信API';
        $query = $this->_query($this->sms)->like('name');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 添加阿里云短信类型
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function addSms()
    {
        $this->_form($this->sms, 'Sms_form');
    }

    /**
     * 编辑阿里云短信类型
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editSms()
    {
        $this->_form($this->sms, 'Sms_form');
    }



    /**
     * 禁用阿里云短信
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbidSms()
    {
        $this->_save($this->sms, ['status' => '0']);
    }

    /**
     * 启用阿里云短信
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resumeSms()
    {
        $this->_save($this->sms, ['status' => '1']);
    }

    /**
     * 删除阿里云短信
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function removeSms()
    {
        $this->_delete($this->sms);
    }

    /**
     * 获取阿里云短信模板类型
     *
     */
    public function getSmsType(){
        $data = Db::name($this->sms_type)->where('status',1)->select();
        if($data){
            return json(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        }else{
            return json(['code'=>400,'msg'=>'查询失败','data'=>'']);
        }
    }
}