<?php


namespace app\exam\controller;

use app\exam\model\Term as tem;
use library\Controller;
use think\Db;

class Term extends Controller
{

    protected $table = 'qt_term';

    /**
     * 学期信息
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
        $this->title = '学期分册管理';
        $query = $this->_query($this->table)->equal('status')->like('name');
        $data = $query->order('id')->page();
    }

    /**
     * 添加学期
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $tem = new tem();
        $stage = $tem->getStage();
        $data = [
            'stage' => $stage
        ];
        if (request()->isPost()) {
            $post = request()->post();
           if (empty($_POST['stage'])){
               return $this->error('学段不能为空');
           }else{
               $post['stage'] = implode(',',emptyArray($post['stage']));
           }
            if (empty($_POST['Sid'])){
                return $this->error('科目不能为空');
            }else{
                $post['Sid'] = implode(',',emptyArray($post['Sid']));
                if (empty($post['Sid'])){   //防止全科时值为空
                    $post['Sid'] = 0;
                }
            }
            $result = $tem->insertTer($post);
            if ($result == 1){
                return $this->success('添加分册成功！');
            }else{
                return $this->error('添加分册失败！');
            }

        }
        return $this->fetch('form', $data);
    }

    /**
     * 编辑学期
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    Public function edit()
    {
        $this->_form($this->table, 'form_edit');
    }

    /**
     * 删除学期
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
                $this->error('该分册名称已经存在，请使用其它分册名称！');
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

    //获取科目信息
    public function getSubject(){
        $stage = input('stage');
        $tem = new  tem();
        $data = $tem->getSubject($stage);
        if(isset($data)){
            return json(['code'=> 200 ,'msg'=>'查询成功' ,'data'=>$data]);
        }else{
            return json(['code'=> 400 ,'msg'=>'查询失败']);
        }

    }

}