<?php


namespace app\exam\controller;
use library\Controller;
use app\exam\model\Paper as pap;
use think\App;
use think\Db;

class Video extends Controller
{

    protected $table = 'qt_video';


    /**
     * 视频信息
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index(){
        $this->title = '在线视频管理';
        $where = '';
        if (request()->isGet()){
            $get = request()->get();
            $count = count($get);
            //判断是否是搜索
            if ($count > 2){
                $where = [
                    'version' =>$get['version'],
                    'stage' =>$get['stage'],
                    'grade' =>$get['grade'],
                    'subject' =>$get['subject'],
                    'term' =>$get['term'],
                ];
                //去除空值
                $where = emptyArray($where);
            }
        }
        $query = $this->_query($this->table)->equal('status')->like('title');
        $data = $query->where($where)->order('id desc')->page();
    }

    /**
     * 添加视频
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
     * 编辑视频
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
     * 删除视频
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove(){
        $id = request()->post();
        $paper = new  pap();
        $result = $paper->remove($id);
        if ($result == 1){
            return $this->success('删除成功！');
        }else{
            return $this->error('删除失败！');
        }
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
//                $this->error('该试卷名称已经存在，请使用其它试卷名称！');
//            }
        }
    }

    /**
     * 禁用试卷
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用试卷
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }


}