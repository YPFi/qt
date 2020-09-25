<?php


namespace app\site\controller;
use library\Controller;
use app\site\model\Article as atc;
use think\Db;

class Article extends Controller
{

    protected $table = 'qt_article';

    /**
     * 文章信息
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
        $this->title = '文章列表管理';
        $query = $this->_query($this->table)->like('title,author');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 添加文章
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = '发表文章';
        $atc = new atc();
        if (request()->isPost()){
            $user = session('user');
            $post = request()->post();
            $post['author'] = $user['username'];
            if (empty($post['release_date'])){
                $post['release_date'] = date('Y-m-d');
                $post['is_auto'] = 0;
            }else{
                $post['is_auto'] = 1;
            }
            $post['abstract'] = mb_substr($post['contentText'],0,50);
            //去除多余字段
            unset($post['contentText']);
            unset($post['file']);
            if (empty($post['id'])){
                $result = $atc->insertAtc($post);
            }else{
                $post['status'] = 1;
//                dump($data);
                $result = $atc->updateAtc($post);
            }
            if (!empty($result)){
                return $this->success('文章发表成功','javascript:history.back()');
            }else{
                return $this->error('文章发表失败！');
            }
        }
        return $this->fetch('form');
    }

    /**
     * 添加定时文章
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function timeAdd(){
        $atc = new atc();
        $user = session('user');
        $postData = request()->post();
        $post = $postData['data'];
        $post['status'] = 0;
        $post['author'] = $user['username'];
        if (empty($post['release_date'])){
            $post['release_date'] = date('Y-m-d');
            $post['is_auto'] = 0;
        }else{
            $post['is_auto'] = 1;
            $post['status'] = 2;
        }
        $post['abstract'] = mb_substr($post['contentText'],0,50);
        //去除多余字段
        unset($post['contentText']);
        unset($post['file']);
        if (empty($post['id'])){
            $result = $atc->insertAtc($post);
        }else{
//                dump($data);
            $result = $atc->updateAtc($post);
        }
        if (!empty($result)){
            return json(['code'=>200,'msg'=>'文章将于'.$post['release_date'].'发布']);
        }else{
            return json(['code'=>400,'msg'=>'文章发表失败']);
        }
    }

    /**
     * 编辑文章
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @auth true
     */
//    protected function _form_filter(array $data)
//    {
//        if ($this->request->isPost()) {
//            if ($data['express_code'])
//            $where = [['express_code', 'eq', $data['express_code']], ['is_deleted', 'eq', '0']];
//            if (!empty($data['id'])) $where[] = ['id ', 'neq', $data['id']];
//            if (Db::name($this->table)->where($where)->count() > 0) {
//                $this->error('该快递编码已经存在，请使用其它编码！');
//            }
//        }
//    }

    /**
     * 禁用文章
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用文章
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除文章
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->_delete($this->table);
    }


    //获取文章类型
    public function getArticleType(){
        $atc = new atc();
        $data = $atc->getArticleType();
        if(empty($data)){
            return json(['code'=>400,'data'=>'','msg'=>'查询失败']);
        }else{
            return json(['code'=>200,'data'=>$data,'msg'=>'查询成功']);
        }
    }

    /**
     * 文字预览
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function see(){
        $atc = new atc();
        $post = request()->post();
        $user = session('user');
        $data = $post['formData'];
        $data['status'] = 3;
        $data['author'] = $user['username'];
        $data['abstract'] = mb_substr($data['contentText'],0,50);
        unset($data['contentText']);
        unset($data['file']);//去除多余字段
        if (empty($data['id'])){
            unset($data['id']);
            $result = $atc->insertAtc($data);
            if (!empty($result)){
                return json(['code'=>200,'id'=>$result]);
            }else{
                return json(['code'=>400,'id'=>$result,'msg'=>'预览失败,请重试']);
            }
        }else{
            $result = $atc->updateAtc($data);
            if (!empty($result)){
                return json(['code'=>201,'id'=>$result]);
            }else{
                return json(['code'=>400,'id'=>$result,'msg'=>'预览失败,请重试']);
            }
        }

    }

}