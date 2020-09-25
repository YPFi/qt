<?php


namespace app\exam\controller;
use library\Controller;
use think\App;
use think\Db;

class Exercises extends Controller
{

    protected $table = 'qt_exercises';


    /**
     * 习题信息
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index(){
        $this->title = '在线习题管理';
        $where = '';
        if (request()->isGet()){
            $get = request()->get();
            $count = count($get);
            //判断是否是搜索
            if ($count > 3){
                $where = [
                    'version' =>$get['version'],
                    'stage' =>$get['stage'],
                    'grade' =>$get['grade'],
                    'subject' =>$get['subject'],
                    'term' =>$get['term'],
                ];
                //去除空值
                emptyArray($where);
            }
        }
        $query = $this->_query($this->table)->equal('status')->like('title+');
        $data = $query->where($where)->order('id desc')->page();
    }

    /**
     * 添加习题
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
     * 批量添加习题
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function adds(){
        $this->_form($this->table, 'forms');
    }

    /**
     * 编辑习题
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
     * 删除习题
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
//            $where = [['title', 'eq', $data['title']]];
//            if (!empty($data['id'])) $where[] = ['id ', 'neq', $data['id']];
//            if (Db::name($this->table)->where($where)->count() > 0) {
//                $this->error('该习题名称已经存在，请使用其它习题名称！');
//            }
        }
    }

    /**
     * 禁用习题
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用习题
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }


}