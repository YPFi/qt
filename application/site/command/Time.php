<?php


namespace app\site\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Time extends Command
{

    protected $table = 'qt_article';
    protected $time_log = 'qt_time_log';

    protected function configure()
    {
        $this->setName('time')
            ->setDescription('定时计划：定时发布文章');
    }

    protected function execute(Input $input, Output $output)
    {
        // 输出到日志文件
        $output->writeln("定时发布文章开始:");
        $this->timeArticle();
        $output->writeln("结束");
    }
    
    
    // 定时发布文章
    protected function timeArticle(){
    	$now = time();
        $acticle = Db::name($this->table)->where(['status'=>2,'is_auto'=>1])->field('id,release_date')->select();
//        dump($acticle);
        $id=[];
        foreach ($acticle as $k=>$v){
            $setTime = strtotime($v['release_date']);
//            dump($setTime);
            if ($setTime<$now){
                $id[] = $v['id'];
            }
        }
//        dump($id);
        Db::startTrans();
        try{
            foreach ($id as $i){
                $result1 = Db::name($this->table)->where('id',$i)->update(['status'=>1,'is_auto'=>0]);
                $data = [
                    'date'=> date('Y-m-d'),
                    'articleID'=>$i,
                    'result'=>$result1
                ];
//                dump($data);
                $result2 = Db::name($this->time_log)->insert($data);
                // 提交事务
                if($result1 && $result2) {
                    Db::commit();
                }
            }
        }catch (\Exception $e){
            // 回滚事务
            dump($e);
            Db::rollback();
        }
    }

}