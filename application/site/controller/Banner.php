<?php


namespace app\site\controller;
use library\Controller;


class Banner extends Controller
{
    protected $table = 'qt_banner';

    /**
     * 轮播信息
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index(){
        $this->title = '轮播信息管理';
        $query = $this->_query($this->table)->equal('status')->like('name');
        $data = $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 添加轮播
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add(){
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑轮播
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
     * 删除轮播
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
//        if ($this->request->isPost()) {
//            $where = [['name', 'eq', $data['name']]];
//            if (!empty($data['id'])) $where[] = ['id ', 'neq', $data['id']];
//            if (Db::name($this->table)->where($where)->count() > 0) {
//                $this->error('该轮播名称已经存在，请使用其它轮播名称！');
//            }
//        }
    }

    /**
     * 禁用轮播
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用轮播
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }
}