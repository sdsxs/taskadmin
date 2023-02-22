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

class SyncDepartment extends Command
{
    // 同步企业微信架构到系统 每天晚上同步一次
    protected function configure()
    {
        $this->setName('task:SyncDepartment')
            ->setDescription('同步企业微信架构到系统');
    }

    protected function execute(Input $input, Output $output)
    {
        $access_token = TaskService::gettoken();
        $depart =   TaskService::getWxDepartmentList($access_token);
        foreach ($depart['department'] as $val){
            $temp['id'] =   $val['id'];
            $temp['name'] =   $val['name'];
            $temp['parentid'] =   $val['parentid'];
            $temp['order'] =   $val['order'];
            $temp['update_time'] =   date('Y-m-d H:i:s');
            if( Db::name('wxdepartment')->where(['id'=>$temp['id']])->count() > 0){
                Db::name('wxdepartment')->where(['id'=>$temp['id']])->update($temp);
            }else{
                Db::name('wxdepartment')->insert($temp);
            }
            unset($temp);
        }

    }


}
