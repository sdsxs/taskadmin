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

class SyncUserDivision extends Command
{
    // 更新用户（企业架构新增用户） 每天晚上同步一次
    protected function configure()
    {
        $this->setName('task:SyncUserDivision')
            ->setDescription('同步coo线用户事业部');
    }

    protected function execute(Input $input, Output $output)
    {
        $lis    =   Db::name('wxdepartment')
            ->where("name like '%事业部'")->select()->toArray();
        //var_dump($lis);die;
        $access_token = TaskService::gettoken();
        /* $url    =   "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=1235822622&fetch_child=1";
         $re = file_get_contents($url);
         $val = json_decode($re,true);
         var_dump($val);die;*/
        foreach ($lis as $key => $val){
            $url    =   "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=".$val['id']."&fetch_child=1";
            $re = file_get_contents($url);
            $user = json_decode($re,true);
            //$user   =   TaskService::getchild($val['id'],$access_token);
            $str    =   '';

            foreach ($user['userlist'] as $key => $va){
                if(isset($va['userid']) && $va['userid']){
                    $str    .= $str ? ','."'".$va['userid']."'" : "'".$va['userid']."'";
                }
            }
            if($str){
                Db::name('user')->where("code in ($str)")->update(['division'=>$val['name']]);
            }
            //
            //var_dump($str);
            //echo Db::name('user')->getLastSql();
            //var_dump($str);//die;
            //echo Db::name('user')->getLastSql();
            unset($str);
        }
    }


}
