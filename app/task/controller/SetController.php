<?php

namespace app\task\controller;

use cmf\controller\AdminBaseController;
use app\task\service\TaskService;
use think\facade\Db;

class SetController extends AdminBaseController
{
    public function index()
    {
        $re =  Db::name('set')->find();
        if($post    =   $this->request->param()){
            if(!$re){
                Db::name('set')->insert($post);
            }else{
                Db::name('set')->where(['id'=>$re['id']])->update($post);
            }
            return $this->success('添加成功！');
        }
        $this->assign('info',$re);
        return $this->fetch();
    }

}
