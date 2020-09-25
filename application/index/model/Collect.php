<?php
namespace app\index\model;
use think\Db;
use think\Model;


class Collect extends Model
{

    /*
     * 获取收藏
     * @param $mid 会员ID
     *
     */
    protected $collect = 'qt_collect';
    protected $collectList = 'qt_collect_list';

    public function getCollect($mid){
        $data = Db::name($this->collect)->where('mid',$mid)->select();
        return $data;
    }

    /*
     * 获取收藏数量
     * @param $id 收藏夹ID
     *
     */
    public function getCount($id){
        $count = Db::name($this->collectList)->where(['cid'=>$id,'status'=>1])->count();
        return $count;
    }

    /*
     * 添加收藏夹
     * @param $post 收藏夹信息
     *
     */
    public function addCollect($post){
        $result = 0;
        Db::startTrans();
        try{
            $result1 = Db::name($this->collect)->insert($post);
            $result2 = Db::name($this->collect)->where('id',$post['pid'])->update(['isLast'=>0]);
            if ($result1 && $result2){
                // 提交事务
                Db::commit();
                $result = 1;
            }
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
        }

        return $result;
    }

    /*
     * 获取收藏夹内容
     * @param $id 收藏夹ID
     *
     */
    public function getCollectList($id){
        $data = Db::name($this->collectList)->where(['cid'=>$id,'status'=>1])->select();
        return $data;
    }
}