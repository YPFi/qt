<?php


namespace app\exam\controller;

use app\exam\model\Subject as sub;
use library\Controller;
use think\Db;

class Subject extends Controller
{

    protected $table = 'qt_subject';

    /**
     * 科目信息
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
        $this->title = '科目信息管理';
        $query = $this->_query($this->table)->equal('status')->like('name');
        $data = $query->order('id desc')->page();
    }

    /**
     * 添加科目
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $sub = new  sub();
        $data = $sub->getInfo();
        if (request()->isPost()) {
            $post = request()->post();
            $count = count($post);
            if ($count != 3){
                return $this->error('学段/年级为必填项');
            }else{
                $post['grade'] = implode(',', $post['grade']);
                $post['stage'] = implode(',', $post['stage']);
            }
            $result = $sub->insertSub($post);
            if ($result == 1) {
                return $this->success('添加科目成功');
            } else {
                return $this->error('添加科目失败');
            }


        }
        return $this->fetch('form', $data);
//        $this->_form($this->table, 'form');
    }

    /**
     * 编辑科目
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    Public function edit()
    {
        $sub = new  sub();
        $id = input('id');
        $data = $sub->getInfo();
        $subject = $sub->getSubject($id);
        $data_base = [
            'subject' => $subject,
            'grade' => $data['grade'],
            'stage' => $data['stage'],
        ];
        if (request()->isPost()) {
            $post = $this->getPost();
            $count = count($post);
            $id = $post['id'];
            if ($count != 4){
                return $this->error('学段/年级为必填项');
            }else{
                $post['grade'] = implode(',', $post['grade']);
                $post['stage'] = implode(',', $post['stage']);
            }
            $result = $sub->updateSub($post, $id);
            if ($result == 1) {
                return $this->success('更新科目成功');
            } else {
                return $this->error('更新科目失败');
            }
        }
        return $this->fetch('form', $data_base);
    }

    /**
     * 删除章节
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
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
                $this->error('该科目名称已经存在，请使用其它科目名称！');
            }
        }
    }

    /**
     * 禁用科目
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用科目
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }
    
}