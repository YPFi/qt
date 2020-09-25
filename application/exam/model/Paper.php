<?php


namespace app\exam\model;
use think\Db;
use think\Model;

class Paper extends Model
{

    protected $exercises = 'qt_exercises';
    protected $chapter = 'qt_chapter';
    protected $paper = 'qt_paper';
    protected $status = 0; //执行状态

    //自动生成试卷
    public function autoPaper(){
        $status = 0; //执行状态
        Db::name($this->chapter)->where(['status'=>1,'is_set_exercises'=>0])->chunk(100,function ($chapter){
            foreach ($chapter as $val){
                $exercises = Db::name($this->exercises)->where('chapter',$val['id'])->field('id,score')->select();
                //去除空数据
                if (!empty($exercises)){
                    $exercisesId = [];  //记录系统ID
                    $exercisesScore = 0;    //记录分值
                    $num = 0 ; //记录数量
                    foreach ($exercises as $k=>$v){
                        $exercisesId[$k] = $v['id'];
                        $exercisesScore += $v['score'];
                        $num++;
                    }
//                    dump($exercisesId);
//                    dump($exercisesScore);
                    $exercisesIds = implode(',',$exercisesId);  //习题ID数据转字符串

                    //插入试卷的数据
                    $data = [
                        'name' => $val['name'],
                        'exercises' => $exercisesIds,
                        'version' => $val['version'],
                        'stage' => $val['stage'],
                        'grade' => $val['grade'],
                        'subject' => $val['subject'],
                        'term' => $val['term'],
                        'chapter' => $val['id'],
                        'score' => $exercisesScore,
                        'number' => $num,
                    ];

                    // 启动事务
                    Db::startTrans();
                    try {
                        Db::name($this->paper)->insert($data);
                        Db::name($this->chapter)->where('id',$val['id'])->update(['is_set_exercises'=>1]);
                        // 提交事务
                        Db::commit();
                        $this->status = 1;
                    } catch (\Exception $e) {
                        // 回滚事务
                        Db::rollback();
                        $this->status = 0;
                    }
                }

            }

        });
        return $this->status;
    }

    public function remove($param){
        $chapter = Db::name($this->paper)->whereIn('id',$param['id'])->field('chapter')->select();
        $chapter_array = [];
        foreach ($chapter as $k=>$v){
            $chapter_array[$k] = $v['chapter'];
        }

        $chapters = implode(',',$chapter_array); //章节ID

        Db::startTrans();
        try{
            Db::name($this->paper)->whereIn('id',$param['id'])->delete();
            Db::name($this->chapter)->whereIn('id',$chapters)->update(['is_set_exercises'=>0]);
            // 提交事务
            Db::commit();
            $status = 1;
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            $status = 0;
        }

        return $status;
    }
}