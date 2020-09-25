<?php


namespace app\exam\controller;
use library\Controller;
use think\Db;

class DataMove extends Controller
{

    public function test(){
        $old_member = Db::name('blog_member')->order('phone')->select();

        foreach ($old_member as $k=>$old){
            if(!$old['create_by']){
                $old['create_by']='10000';
            }
            if(!$old['user_id']){
                $old['user_id']='1';
            }
            if(!$old['begin_time']){
                $old['begin_time']=time();
            }
            if(!$old['ext_time']){
                $old['ext_time']=strtotime("+100 year");
            }
            if(strlen($old['phone'])==11){
                $new_member = [
                    'name'=>$old['nickname'],
                    'realName'=>'',
                    'phone'=>$old['phone'],
                    'password'=>$old['password'],
                    'gender'=>$old['sex'],
                    'age'=>$old['age'],
                    'birthday'=>$old['year'],
                    'email'=>$old['email'],
                    'address'=>$old['location'],
                    'head_img'=>$old['head_img'],
                    'cardID'=>'',
                    'remake'=>'',
                    'vipType'=> $old['vip_type'],
                    'belongTo'=>'',
                    'province'=>$old['province_id'],
                    'city'=>$old['city_id'],
                    'area'=>$old['area_id'],
                    'create_at'=>$old['create_at'],
                    'update_at'=>$old['create_at'],
                    'addBy' => $old['create_by'],
                    'belongTo' => $old['user_id'],
                ];

                Db::startTrans();   //开启事务
                try{
                    $mid =  Db::name('qt_member')->insertGetId($new_member);
                    $collect = [
                        'mid'=>$mid,
                        'name'=>'默认收藏夹',
                        'pid'=>'0',
                        'isLast'=>'1',
                        'sort'=>'999',
                    ];

                    $member_info = [
                        'mid'=>$mid,
                        'balance'=>'',
                        'total'=>'',
                        'reward'=>'',
                        'qzt'=>'',
                        'RedeemLevel'=>'',
                        'RedeemCode'=>'',
                        'startTime'=>'',
                        'endTime'=>'',
                        'create_at'=>$old['create_at'],
                        'status'=>'1',
                    ];
                    $new_vip = [
                        'mid'=>$mid,
                        'vipType' => $old['vip_type'],
                        'version' => 0,
                        'stage' => 2,
                        'grade' => '',
                        'subject' => '',
                        'startTime' => $old['begin_time'],
                        'endTime' => $old['ext_time'],
                        'status' => '1',
                    ];
                    Db::name('qt_vip_info')->insert($new_vip);
                    Db::name('qt_member_info')->insert($member_info);

                    Db::name('qt_collect')->insert($collect);

                    // 提交事务
                    Db::commit();
                    echo "插入成功".$mid.'<br>';
                }catch (\Exception $e){
                    // 回滚事务
                    Db::rollback();
                    dump($e);
                }
            }

        }
    }

    public function article_ins(){
        $art = Db::name('blog_article')->select();
        foreach ($art as $article){
            $new_article = [
                'title'=>$article['title'],
                'author'=>$article['create_by'],
                'create_at'=>$article['create_at'],
                'readNum'=>$article['praise'],
                'keywords'=>'',
                'tags'=>'',
                'reprint'=>0,
                'type'=>$article['category_id'],
                'abstract'=>$article['describe'],
                'img'=>$article['cover_img'],
                'is_auto'=> 0,
                'release_date'=>$article['update_at'],
                'content'=>$article['content'],
                'status'=>$article['status'],
            ];

            Db::startTrans();   //开启事务
            try{
                Db::name('qt_article')->insert($new_article);

                // 提交事务
                Db::commit();
                echo "插入成功".'<br>';
            }catch (\Exception $e){
                // 回滚事务
                Db::rollback();
                dump($e);
            }
        }

    }
    public function memberData(){
        $old_member = Db::name('blog_member')->where('vip_type',1)->select();
        $i = 1;
//        dump($old_member);die;
        foreach ($old_member as $old){
            if(!$old['create_by']){
                $old['create_by']='1';
            }
            if(!$old['user_id']){
                $old['user_id']='1';
            }
            if(!$old['begin_time']){
                $old['begin_time']=time();
            }
            if(!$old['ext_time']){
                $old['ext_time']=strtotime("+100 year");
            }
            if(strlen($old['phone'])>11){
                dump($old['phone']);
            }
//            dump($old);
            $new_member = [
                'name'=>$old['nickname'],
                'realName'=>'',
                'phone'=>$old['phone'],
                'password'=>$old['password'],
                'age'=>$old['age'],
                'birthday'=>date("Y-m-d"),
                'email'=>$old['email'],
                'address'=>$old['location'],
                'WXnick'=>$old['wechat_name'],
                'QQnick'=>'',
                'Sinanick'=>'',
                'WXopenID'=>$old['wechat_openid'],
                'QQopenID'=>'',
                'SinaopenID'=>'',
                'head_img'=>$old['head_img'],
                'remake'=>'',
                'vipType'=> 1,
                'create_time'=>date("Y-m-d h:i:s"),
                'addBy' => $old['create_by'],
                'belongTo' => $old['user_id'],
                'remake'=>'',
            ];
            $new_vip = [
                'mid'=>$i,
                'vipType' => 1,
                'version' => 0,
                'stage' => 2,
                'grade' => '',
                'subject' => '',
                'term' => '',
                'startTime' => $old['begin_time'],
                'endTime' => $old['ext_time'],
            ];
            Db::startTrans();   //开启事务
            try{
                Db::name('qt_member')->insert($new_member);
                Db::name('qt_vip_info')->insert($new_vip);
                // 提交事务
                Db::commit();
                $i++;
                echo "插入成功";
            }catch (\Exception $e){
                // 回滚事务
                Db::rollback();
                dump($e);
            }
        }
    }


    public function memberupdate(){
        $member = Db::name('qt_member')->field('belongTo,id')->select();
//        dump($member);die;
        foreach ($member as $m){
            switch ($m['belongTo']){
                case 72:
                    $m['belongTo'] = 10011;
                    break;
                case 89:
                    $m['belongTo'] = 10017;
                    break;
                case 91:
                    $m['belongTo'] = 10019;
                    break;
                case 60:
                    $m['belongTo'] = 10003;
                    break;
                case 73:
                    $m['belongTo'] = 10012;
                    break;
                default:
                    $m['belongTo'] = 10006;
                    break;
            }
            dump($m);die;
            Db::name('qt_member')->where('id',$m['id'])->update($m);
        }
    }

    public function admin(){
        $member = Db::name('tf_user')->where('pid','>',0)->select();
        foreach ($member as $m){
            $data = [
                'username' => $m['username'],
                'password' => $m['password'],
                'phone' => $m['moblie'],
                'qq' => '222222',
                'is_deleted' => 0,
                'create_at' => date("Y-m-d h:i:s")
            ];
            die;
           Db::name('system_user')->insert($data);
        }
    }

    public function chapter(){
        $s = 357;
        $g = 9;
        $sub = 7;
        $t = 2;
        $i=0;
        $data = Db::name('blog_chapter')->where('subject_id',$s)->select();
        foreach ($data as $v){
            $chapter = [
                'name' => $v['chapter_name'],
                'version' => 57,
                'stage' => 2,
                'grade' => $g,
                'subject' => $sub,
                'term' => $t,
            ];die;
            $result = Db::name('qt_chapter')->insert($chapter);
            if($result){
                $i++;
            }
        }
        echo "插入".$i."条";
    }

    public function examination(){
        $c = 101047;
        $g = 9;
        $s = 5;
        $t = 2;
        $i = 0;
        $cat = 	6276;die;
        $data = Db::name('blog_examination')->where(['aid'=>50,'chapter_id'=>$c])->select();
//        dump($data);
        foreach ($data as $v){
            $exam = [
                'title'=>$v['title'],
                'optionA'=>$v['option_A'],
                'optionB'=>$v['option_B'],
                'optionC'=>$v['option_C'],
                'optionD'=>$v['option_D'],
                'answer'=>$v['answer'],
                'remake'=>$v['remark'],
                'version'=>57,
                'stage'=>2,
                'grade'=>$g,
                'subject'=>$s,
                'term' => $t,
                'chapter'=>$cat
            ];
            dump($exam);
            $result = Db::name('qt_exercises')->insert($exam);
            if($result){
                $i++;
            }
        }
        echo "插入".$i."条";
    }

    public function upd(){
        Db::name('qt_chapter')->where('version','<>','57')->update(['is_set_exercises'=>1]);
    }

    public function article(){
        $oldData = Db::name('blog_article')->select();
//        dump($oldData);
        foreach($oldData as $v){
            $data = [
                'title'=> $v['title'],
                'author'=> '勤藤教育',
                'reprint'=> '',
                'keywords'=> '',
                'tags'=> '',
                'type'=> $v['category_id'],
                'abstract'=> $v['describe'],
                'img'=> $v['cover_img'],
                'release_date'=> date('Y-m-d'),
                'readNum' => $v['clicks'],
                'content'=> $v['content'],
                'is_auto'=> 0
            ];die;
            Db::name('qt_article')->insert($data);
        }
    }

    public function setWatch(){
        $chapter = Db::name('qt_chapter')->where('status',1)->field('id,name')->select();
        foreach ($chapter as $c){
            $num = rand(1000,3000);
            $data = [
                'chapter' => $c['id'],
                'name' => $c['name'],
                'num' => $num
            ];
            $vi[] = $data;

        }
        $result = Db::name('qt_watch')->limit(100)->insertAll($vi);
        if ($result == 1){
            echo '插入中';
        }
    }

    public function setCollect(){
        $member = Db::name('qt_member')->where('status',1)->select();
        foreach ($member as $m){
            $data = [
                'mid'=>$m['id'],
                'name'=>'默认收藏夹',
                'pid'=> 0,
                'sort' => 9999
            ];
            Db::name('qt_collect')->insert($data);
        }
    }
}