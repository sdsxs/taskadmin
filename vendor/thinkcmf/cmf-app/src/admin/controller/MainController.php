<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\task\service\TaskService;
use cmf\controller\AdminBaseController;
use app\admin\model\Menu;
use think\Db;

class MainController extends AdminBaseController
{

    /**
     *  后台欢迎页
     */
    public function index()
    {
        $uid = cmf_get_current_admin_id();
        //var_dump(Db::name('task_user')->getTableFields());die;
        $totaltask  =   Db::name('task_user')->where(
            [
               ['user_id','=',$uid]
            ]
        )->count();//我的总任务数
        //今天任务
        $time = time();
        $todaytask  =   Db::name('task_user')
            ->distinct(true)
            ->alias('a')
            ->join('task b','a.task_id=b.task_id','left')
            ->where(
            [
                ['a.user_id','=',$uid],
                ['b.task_start_time','<',strtotime(date("Y-m-d"))+24*60*60],
                ['b.task_end_time','>',strtotime(date("Y-m-d"))]
            ]
        )->count();
        //完成任务
        $finistask   =   Db::name('task_user')
            ->distinct(true)
            ->alias('a')
            ->join('task b','a.task_id=b.task_id','left')
            ->where(
                [
                    ['a.user_id','=',$uid],
                    ['b.task_status','=','3']
                ]
            )->count();
        //逾期任务
        $beoverdue  =    Db::name('task_user')
            ->distinct(true)
            ->alias('a')
            ->join('task b','a.task_id=b.task_id','left')
            ->where(
                [
                    ['a.user_id','=',$uid],
                    ['b.task_status','=','4'],
                ]
            )->count();
        //公告列表
        $getBulletin = Db::name('bulletin')->order('bulletin_id desc')->paginate(8);
        $Completionrate =   ($totaltask == 0) ? 0: intval(($finistask/$totaltask)*100) ;
        $this->assign('aging', TaskService::taskAgingGapFigureLineChart(cmf_get_current_admin_id()));
        $this->assign('totaltask', $totaltask)
            ->assign('finistask',$finistask)
            ->assign('beoverdue',$beoverdue)
            ->assign('todaytask',$todaytask);
        $this->assign('Completionrate', $Completionrate);
        $this->assign('Bulletin', $getBulletin);
        return $this->fetch();
    }

    public function dashboardWidget()
    {
        $dashboardWidgets = [];
        $widgets          = $this->request->param('widgets/a');
        if (!empty($widgets)) {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 1]);
                } else {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 0]);
                }
            }
        }

        cmf_set_option('admin_dashboard_widgets', $dashboardWidgets, true);

        $this->success('更新成功!');

    }

}
