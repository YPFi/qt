<?php


namespace app\exam\controller;
use library\Controller;
use app\exam\model\Member as Meb;
use think\App;
use think\Db;

class Member extends Controller
{

    protected $table = 'qt_member';
    protected $vip = 'qt_vip_type';
    protected $meb ;

    public function __construct(App $app)
    {
        $this->meb = new Meb();
        parent::__construct($app);
    }

    /**
     * 会员信息
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
        $this->title = '会员信息管理';
        $query = $this->_query($this->table)->equal('status')->like('name,phone,vipType');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * VIP类型
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function vip(){
        $this->title = '会员类型管理';
        $query = $this->_query($this->vip)->equal('status')->like('name,code');
        $data = $query->dateBetween('create_at')->order('sort')->page();
    }

    /**
     * 编辑VIP
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editVIP()
    {
        $this->_form($this->vip, 'form_vip');
    }

    /**
     * 添加VIP
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function vipAdd()
    {
        $this->_form($this->vip, 'form_vip');
    }

    /**
     * 禁用VIP
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbidVIP()
    {
        $this->_save($this->vip, ['status' => '0']);
    }

    /**
     * 启用VIP
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resumeVIP()
    {
        $this->_save($this->vip, ['status' => '1']);
    }

    /**
     * 删除VIP
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function removeVIP()
    {
        $this->_delete($this->vip);
    }

    /**
     * 添加会员
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {

        //提交请求数据
        if ($this->request->isPost()) {
            $post = request()->post();
            unset($post['term']);
            $result = $this->meb->insertMember($post);
            if($result == 1){
                return $this->success('添加会员成功');
            }else{
                return $this->error('添加会员失败');
            }
        }

        $data = $this->getTableFiled($this->table);
//        dump($data);die;
        return $this->fetch('form',$data);
    }

    /**
     * 编辑会员
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $id = input('id');
        $memberData = $this->meb->getMember($id);
//        dump($memberData);
        $dataBase = [
            'vo'=>$memberData,
        ];
        if (request()->isPost()){
            $post = request()->post();
            $result = $this->meb->updateMember($post);
            if ($result == 1){
                return $this->success('更新会员信息成功');
            }else{
                return $this->error('更新会员信息失败');
            }
        }
        return $this->fetch('form',$dataBase);
    }

    /**
     * 表单数据处理
     * @param array $data
     * @auth true
     */
    protected function _form_filter(array $data)
    {
        if ($this->request->isPost()) {
            $where = [['code', 'eq', $data['code']]];
            if (!empty($data['id'])) $where[] = ['id ', 'neq', $data['id']];
            if (Db::name($this->vip)->where($where)->count() > 0) {
                $this->error('vip代码已经存在，请使用其它代码！');
            }
        }
    }

    /**
     * 禁用会员
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用会员
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除会员
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $post = request()->post();
//        dump($post);
        $result = $this->meb->remove($post['id']);
        if ($result == 1){
            return $this->success('删除会员成功！');
        }else{
            return $this->error('删除会员失败！');
        }
    }

    //获取省份
    public function getLocation(){
        $post = request()->param();
        $data = $this->meb->getLocation($post['province'],$post['city'],$post['isFrist']);
        if ($data){
            return json(['code'=>200,'data'=>$data]);
        }else{
            return json(['code'=>400,'msg'=>'未获取到相关数据']);
        }

    }

    //获取会员数据
    public function getMemberData(){
        $option = input('param');
        switch ($option){
            case 1:
                $data = $this->meb->getVipType();break;
            case 2:
                $data = $this->meb->getStage();break;
        }
        if ($data){
            return json(['code'=>200,'data'=>$data]);
        }else{
            return json(['code'=>400,'msg'=>'未获取到相关数据']);
        }
    }

    //获取年级
    public function getGrade(){
        $stage = input('stage');

        $data = $this->meb->getGrade($stage);
        if ($data){
            return json(['code'=>200,'data'=>$data]);
        }else{
            return json(['code'=>400,'msg'=>'未获取到相关数据']);
        }
    }

    //获取学段
    public function getStage(){
        $data = $this->meb->getStage();
        if ($data){
            return json(['code'=>200,'data'=>$data]);
        }else{
            return json(['code'=>400,'msg'=>'未获取到相关数据']);
        }
    }

    //获取科目
    public function getSubject(){
        $grade = input('grade');
        $data = $this->meb->getSubject($grade);
        if ($data){
            return json(['code'=>200,'data'=>$data]);
        }else{
            return json(['code'=>400,'msg'=>'未获取到相关数据']);
        }
    }

    //获取学期
    public function getTerm(){
        $stage = input('stage');
        $type = input('type');
        $data = $this->meb->getTerm($stage,$type);
        if ($data){
            return json(['code'=>200,'data'=>$data]);
        }else{
            return json(['code'=>400,'msg'=>'未获取到相关数据']);
        }
    }

    //验证手机
    public function  checkPhone(){
        $phone = input('phone');
        $count = $this->meb->checkPhone($phone);
        if ($count == 0){
            return json(['code'=>200,'msg'=>'验证通过']);
        }else{
            return json(['code'=>400,'msg'=>'该号码已存在！']);
        }
    }

    //获取数据表的字段名称
    public function getTableFiled($table){
        $sql = "show COLUMNS FROM ".$table;
        $tableFileds = Db::query($sql);
        foreach ($tableFileds as $v){
            $data[$v['Field']] = '';
        }

        return $data;
    }
}