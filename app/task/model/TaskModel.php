<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/2/10
 * Time: 17:26
 */

namespace app\task\model;

use app\task\service\TaskService;
use think\Db;
use think\Model;
use app\admin\model\UserModel;

class TaskModel  extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'task';
    protected $taskid;

    /**实例化任务对象
     * @param int $taskid
     * @return TaskModel
     */
    public static function task( $taskid = 0){
        $task = new self();
        $task->taskid   =   $taskid;
        return $task;
    }

    /**获取任务动态
     * @param int $taskid
     * @return \think\Collection|Model[]
     */
    protected function getDynamic(){
        return $this->getModel()->where(['task_dynamic_task_id'=>$this->taskid])->select();
    }

    /**给部门的每个员工分配一个相同的任务
     * @param $taskfield
     * @param $departmentid
     */
    public function createTaskByDepartment($taskfield,$departmentid){
        $user = new UserModel();
        if(is_array($departmentid)){
            foreach ($departmentid as $val){
                $tem[]=$val['id'];
            }
            $con    =   join(',',$tem);
            $where  =   "departmentid in ($con) and user_status=1";
        }else{
            $where  =   ['departmentid'=>$departmentid,'user_status'=>1];
        }
        //print_r($where);die;
        $userlist   =   $user
            ->field('id,code')
            ->where($where)->select()->toArray();
        if($userlist){
            Db::startTrans();
            try{
                foreach ($userlist as $key => $value){

                    $id = Db::name('task')->insertGetId($taskfield);
                    Db::name('task_user')->insert([
                        'task_id'=>  $id,
                        'user_id'=>$value['id'],
                        'task_user_type'=>2
                    ]);
                }
                // 提交事务
                Db::commit();

            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                echo $e->getMessage();
                return false;
            }
            foreach ($userlist as $key => $va){
                $re =TaskService::sendTaskMessage($va['code'],'您有一个新任务:'.$taskfield['task_title']);
            }
            return true;
        }
    }

    public function createTaskByTeam($taskfield,$departmentid){
        $user = new UserModel();
        if(is_array($departmentid)){
            $con    =   join(',',$departmentid);
            $where  =   "departmentid in ($con) and user_status=1";
        }else{
            $where  =   ['departmentid'=>$departmentid,'user_status'=>1];
        }
        //print_r($where);die;
        $userlist   =   $user
            ->field('id,code')
            ->where($where)->select()->toArray();
        if($userlist){
            Db::startTrans();
            try{
                foreach ($userlist as $key => $value){
                    if(isset($value['id']) && $value['id']){
                        $id = Db::name('task')->insertGetId($taskfield);
                        Db::name('task_user')->insert([
                            'task_id'=>  $id,
                            'user_id'=>$value['id'],
                            'task_user_type'=>2
                        ]);
                    }else{
                        //var_dump($value) ;
                        continue;
                    }
                }
                // 提交事务
                Db::commit();

            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                echo $e->getMessage();
                return false;
            }
            foreach ($userlist as $key => $va){
                $re =TaskService::sendTaskMessage($va['code'],'您有一个新任务:'.$taskfield['task_title']);
            }
            return true;
        }
    }
    /**给多个员工分配一个相同的任务
     * @param $taskfield
     * @param $departmentid
     */

    public function createTaskByUserList($taskfield,$userlist,$codelist){
        if(!is_array($userlist)){
            $userlist   =   explode(',',$userlist);
            $userlist = array_filter($userlist);
            $userlist = array_unique($userlist);
        }
        if($userlist){
            Db::startTrans();
            try{
                foreach ($userlist as $key => $value){
                    if(!$value){
                        continue;
                    }
                    $id = Db::name('task')->insertGetId($taskfield);
                    Db::name('task_user')->insert([
                        'task_id'=>  $id,
                        'user_id'=>$value,
                        'task_user_type'=>2
                    ]);
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return false;
            }
            foreach ($codelist as $value){
                if($value){
                    $re =TaskService::sendTaskMessage($value,'您有一个新任务:'.$taskfield['task_title']);
                }
            }
            return true;
        }
    }
    /**给标签员工分配一个相同的任务
     * @param $taskfield
     * @param $departmentid
     */
    public function createTaskByLabel($taskfield,$label,$departmentid){
        $user = new UserModel();
        if(is_array($departmentid)){
            foreach ($departmentid as $val){
                $tem[]=$val['id'];
            }
            $con    =   join(',',$tem);
            $where  =   "departmentid in ($con) and user_status=1 and label='$label'";
        }else{
            if($departmentid){
                $where  =   ['departmentid'=>$departmentid,'user_status'=>1,'label'=>$label];
            }else{
                $where  =   ['user_status'=>1,'label'=>$label];
            }
        }

        $userlist   =   $user
            ->field('id,code')
            ->where($where)->select()->toarray();
        if($userlist){
            Db::startTrans();
            try{
                foreach ($userlist as $key => $value){
                    $id = Db::name('task')->insertGetId($taskfield);
                    Db::name('task_user')->insert([
                        'task_id'=>  $id,
                        'user_id'=>$value['id'],
                        'task_user_type'=>2
                    ]);
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                echo $e->getMessage();
                Db::rollback();
                return false;
            }
            foreach ($userlist as $key => $va){
                $re =TaskService::sendTaskMessage($va['code'],'您有一个新任务:'.$taskfield['task_title']);
            }
            return true;
        }
    }

    /**根据userid生成单个任务
     * @param $taskfield
     * @param $userid
     */

    public function createTaskByUserId($taskfield,$userid){
        $code   =   Db::name('user')->where(['id'=>$userid])->value('code');
        Db::startTrans();
        try{
            //var_dump($taskfield);die;
            $id = Db::name('task')->insertGetId($taskfield);
            Db::name('task_user')->insert([
                'task_id'=>  $id,
                'user_id'=>$userid,
                'task_user_type'=>2
            ]);
            // 提交事务
            Db::commit();

        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            echo $e->getMessage();die;
        }
        if($code){
            $re =TaskService::sendTaskMessage($code,'您有一个新任务:'.$taskfield['task_title']);
            //print_r($re);
        }
        return true;
    }

}