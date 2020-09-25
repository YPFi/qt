<?php


namespace app\exam\controller;
use library\Controller;
use app\exam\model\Chapter as chap;
use think\App;
use think\Db;

class Chapter extends Controller
{

    protected $table = 'qt_chapter';
    protected $chap;

    public function __construct(App $app)
    {
        $this->chap = new chap();
        parent::__construct($app);
    }

    /**
     * 章节信息
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index(){
        $this->title = '章节信息管理';
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
        $query = $this->_query($this->table)->equal('status')->like('name');
        $data = $query->where($where)->order('id desc')->page();
    }

    /**
     * 添加章节
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
     * 编辑章节
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
     * 删除章节
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
                $this->error('该章节名称已经存在，请使用其它章节名称！');
            }
        }
    }

    /**
     * 禁用章节
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用章节
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    //获取版本信息
    public function getVersion(){
        $data = $this->chap->getVersion();
        if(isset($data)){
            return json(['code'=>200 ,'data'=>$data ,'msg'=>'查询成功']);
        }else{
            return json(['code'=>400 ,'msg'=>'查询失败']);
        }

    }

    //获取学段信息
    public function getStage(){
        $data = $this->chap->getStage();
        if(isset($data)){
            return json(['code'=>200 ,'data'=>$data ,'msg'=>'查询成功']);
        }else{
            return json(['code'=>400 ,'msg'=>'查询失败']);
        }

    }

    //获取年级信息
    public function getGrade(){
        $stage = input('stage');
        $data = $this->chap->getGrade($stage);
        if(isset($data)){
            return json(['code'=>200 ,'data'=>$data ,'msg'=>'查询成功']);
        }else{
            return json(['code'=>400 ,'msg'=>'查询失败']);
        }
    }

    //获取科目信息
    public function getSubject(){
        $grade = input('grade');
        $data = $this->chap->getSubject($grade);
        if(isset($data)){
            return json(['code'=>200 ,'data'=>$data ,'msg'=>'查询成功']);
        }else{
            return json(['code'=>400 ,'msg'=>'查询失败']);
        }
    }

    //获取分册信息
    public function getTerm(){
        $stage = input('stage');
        $data = $this->chap->getTerm($stage);
        if(isset($data)){
            return json(['code'=>200 ,'data'=>$data ,'msg'=>'查询成功']);
        }else{
            return json(['code'=>400 ,'msg'=>'查询失败']);
        }
    }

    //获取章节信息
    //获取分册信息
    public function getChapter(){
        $get = request()->get();
//        dump($get);
        $data = $this->chap->getChapter($get);
        if(isset($data)){
            return json(['code'=>200 ,'data'=>$data ,'msg'=>'查询成功']);
        }else{
            return json(['code'=>400 ,'msg'=>'查询失败']);
        }
    }

}