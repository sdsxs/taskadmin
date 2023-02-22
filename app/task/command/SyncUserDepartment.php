<?php

namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use app\task\service\TaskService;
use think\db\Expression;
use think\Exception;
use think\facade\Cache;

class SyncUserDepartment extends Command
{
    // 更新用户的部门 每天 晚上执行1次
    protected function configure()
    {
        $this->setName('task:SyncUserDepartment')
            ->setDescription('同步用户的部门');
    }

    protected function execute(Input $input, Output $output)
    {
        $access_token = TaskService::gettoken();
        $id = cache::get(date('Y-m-d').'syncuser')?cache::get(date('Y-m-d').'syncuser'):0;
        //$userlist   =   Db::name('user')->field('id,user_login')->where('id > '.$id)->order('id asc')->limit(100)->select()->toArray();
        $userlist   =   Db::name('user')->field('id,user_login')->order('id asc')->select()->toArray();
        //$user =   TaskService::getWxUserInfo($access_token,'liulijuan');
        //var_dump($user);die;
        foreach ($userlist as $val){
            $user =   TaskService::getWxUserInfo($access_token,$val['user_login']);
            if($user['errcode'] !=  0){
                Db::name('user')->where(['user_login'=>$val['user_login']])->update(['departmentid'=>'','job'=>'','user_status'=>0]);
                continue;
            }
            //cache::set(date('Y-m-d').'syncuser',$val['id'],7200);
            if(isset($user['main_department'])){
                Db::name('user')->where(['user_login'=>$val['user_login']])->update(['departmentid'=>$user['main_department'],'job'=>$user['position']]);
            }
        }
    }


}
