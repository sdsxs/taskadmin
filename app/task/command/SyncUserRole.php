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

class SyncUserRole extends Command
{
    // 更新用户（企业架构新增用户） 每天晚上同步一次
    protected function configure()
    {
        $this->setName('task:SyncUserRole')
            ->setDescription('同步coo线用户默认角色');
    }

    protected function execute(Input $input, Output $output)
    {
        $lis    =   Db::name('user')
            ->alias('a')
            ->field('a.id')
            ->join('role_user b','a.id=b.user_id','left')
            ->where('b.role_id is null')->select()->toArray();
        foreach ($lis as $val){
            $temp['role_id'] =   2;
            $temp['user_id'] =   $val['id'];
            Db::name('role_user')->insert($temp);
            unset($temp);
        }
    }


}
