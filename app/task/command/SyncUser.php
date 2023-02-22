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

class SyncUser extends Command
{
    // 更新用户（企业架构新增用户） 每天晚上同步一次
    protected function configure()
    {
        $this->setName('task:SyncUser')
            ->setDescription('同步coo线用户');
    }

    protected function execute(Input $input, Output $output)
    {
        // 更新用户（企业架构新增用户）
        $access_token = TaskService::gettoken();
        $url    =   "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=1194540962&fetch_child=1";
        $re = file_get_contents($url);
        $val = json_decode($re,true);
        //var_dump($val['userlist']);die;
        if(!isset($val['userlist'])){
            return '';
        }
        foreach ($val['userlist'] as $va){
//echo $va['department'][0].'  ';continue;
              if(Db::name('user')->where(['user_login'=>$va['userid']])->count()>0){
                  //echo '更新'.$va['userid'].' ****';
                  Db::name('user')->where(['user_login'=>$va['userid']])->update(
                      [
                          'user_nickname'=>$va['name'],
                          'departmentid'=>$va['department'][0]
                          ]
                  );
              }else{
                  //echo '添加'.$va['userid'].'******';
                  $url1    =   "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=$access_token&userid=".$va['userid'];
                  $res = file_get_contents($url1);
                  $userdetail = json_decode($res,true);
                  Db::name('user')->insert(
                      [
                          'user_type'=>1,
                          'user_status'=>1,
                          'job'=>$userdetail['position'],
                          'label'=>$userdetail['position'],
                          'user_pass'=>cmf_password($userdetail['mobile']),
                          'code'=>$va['userid'],
                          'user_login'=>$va['userid'],
                          'mobile'  =>$userdetail['mobile'],
                          'user_nickname'=>$va['name'],
                          'departmentid'=>$va['department'][0]
                      ]
                  );
              }
            //cache::set(date('Y-m-d').'syncuser',$val['id'],7200);
        }
        //echo cache::get(date('Y-m-d').'syncuser');
        die;
        foreach ($depart['department'] as $val){
            $temp['id'] =   $val['id'];
            $temp['name'] =   $val['name'];
            $temp['parentid'] =   $val['parentid'];
            $temp['order'] =   $val['order'];
            $temp['update_time'] =   date('Y-m-d H:i:s');
            if( Db::name('wxdepartment')->where(['id'=>$temp['id']])->count() >0){
                Db::name('wxdepartment')->where(['id'=>$temp['id']])->update($temp);
            }else{
                Db::name('wxdepartment')->insert($temp);
            }
            unset($temp);
        }

    }


}
