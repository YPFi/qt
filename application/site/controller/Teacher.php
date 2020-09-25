<?php


namespace app\site\controller;
use library\Controller;
use app\site\model\Teacher as teac;
use think\Db;

class Teacher extends Controller
{

    protected $table = 'qt_teacher';

    /**
     * 老师信息
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index(){
        $this->title = '老师信息管理';
        $query = $this->_query($this->table)->equal('status')->like('name');
        $data = $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 添加老师
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add(){
        $teac = new teac();
        if (request()->isPost()){
            $post = request()->post();
            $post['password'] = md5($post['password']);
            $result = $teac->addTeacher($post);
            if($result == 1){
                return $this->success('添加老师成功！');
            }else{
                return $this->error('添加老师失败！');
            }
        }
        return $this->fetch('form');
    }

    /**
     * 编辑老师
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    Public function edit(){
        $this->_form($this->table, 'form');
    }

    /**
     * 删除老师
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove(){
        $this->_delete($this->table);
    }

    /**
     * 表单数据处理
     * @param array $data
     * @auth true
     */
    protected function _form_filter(array $data)
    {
        if ($this->request->isPost()) {
            $where = [['name', 'eq', $data['name']]];
            if (!empty($data['id'])) $where[] = ['id ', 'neq', $data['id']];
            if (Db::name($this->table)->where($where)->count() > 0) {
                $this->error('该老师名称已经存在，请使用其它老师名称！');
            }
        }
    }

    /**
     * 禁用老师
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用老师
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    //获取学段信息
    public function getStage(){
        $teac = new teac();
        $data = $teac->getStage();
        if (empty($data)){
            return json(['code'=>400,'msg'=>'查询失败','data'=>$data]);
        }else{
            return json(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        }
    }

    //获取学段信息
    public function getGrade(){
        $stage = input('stage');
        $teac = new teac();
        $data = $teac->getGrade($stage);
        if (empty($data)){
            return json(['code'=>400,'msg'=>'查询失败','data'=>$data]);
        }else{
            return json(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        }
    }

    //获取学段信息
    public function getSubject(){
        $grade = input('grade');
        $teac = new teac();
        $data = $teac->getSubject($grade);
        if (empty($data)){
            return json(['code'=>400,'msg'=>'查询失败','data'=>$data]);
        }else{
            return json(['code'=>200,'msg'=>'查询成功','data'=>$data]);
        }
    }
}