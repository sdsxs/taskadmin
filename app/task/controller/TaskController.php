<?php
// +----------------------------------------------------------------------
// 任务管理系统
// +----------------------------------------------------------------------

namespace app\task\controller;

use app\admin\model\UserModel;
use cmf\controller\HomeBaseController;
use app\task\service\Auth;
use app\task\model\TaskModel;
use app\task\service\TaskService;
use think\facade\Db;
use think\Validate;
use think\Response;

class TaskController extends HomeBaseController
{


    public function projectList(){
        $list   =   TaskService::getProject();
        return  $this->assign('list',$list)->fetch();
    }

    /**添加项目
     * @return mixed|void
     */
    public function addProject(){
        $post   =   $this->request->param();
        if($this->request->isPost()){
            $validate = new Validate([
                '__token__'  =>  'require|max:65|token',
                'title' =>  'require',
                'content' =>  'require',
            ]);
            if (!$validate->check($post)) {
                return $this->error($validate->getError());
            }
            $data['project_title']  =   $post['title'];
            $data['project_content']  =   $post['content'];
            $data['project_listsort']  =   $post['listsort'];
            if(TaskService::addProject($data)){
                return TaskService::result(200,'新增项目成功！',url("task/task/projectList"));
            }
            return $this->error('新增项目失败！');
        }
        //$list   =   TaskService::getProject();
        return  $this->fetch();
    }

    /**
     * 删除项目
     */
    public function delProject(){
        $project_id =   $this->request->param('project_id');
        if($project_id){
            if(TaskService::delProject($project_id)){
                return TaskService::result(200,'删除项目成功！',url("task/task/projectList"));
            }
        }
        return $this->error('删除失败');
    }

    /**编辑项目
     * @return mixed|void
     */
    public function editProject(){
        $post   =   $this->request->param();
        $id = $post['project_id'];
        if($this->request->isPost()){
            $data['project_title']  =   $post['title'];
            $data['project_content']  =   $post['content'];
            $data['project_listsort']  =   $post['listsort'];
            $validate = new Validate([
                'title' =>  'require',
                'content' =>  'require',
            ]);
            if (!$validate->check($post)) {
                return $this->error($validate->getError());
            }
            if(TaskService::updateProject($id,$data)){
                return TaskService::result(200,'项目保存成功！',url("task/task/projectList"));
            }
        }
        $data   =   TaskService::getProject($id);
        return  $this->assign('data',$data)->fetch();
    }
    /**
     * 添加单人任务
     */
    public function addTask(){
        if($taskid  =   $this->request->param('parentid')){
            $this->assign('list',TaskService::getTaskDetail($taskid));
        }
        return $this->assign('label',TaskService::getLabel())
         ->assign('project',TaskService::getProject())
            ->fetch();
    }
    /**
     * 添加多人任务
     */
   /* public function manyTask(){
        if($taskid  =   $this->request->param('parentid')){
            $this->assign('list',TaskService::getTaskDetail($taskid));
        }
        return $this->assign('getDepartment',TaskService::getDepartment())
            ->assign('label',TaskService::getLabel())
            ->assign('project',TaskService::getProject())
            ->fetch();
    }*/
    public function manyTask(){
        if($taskid  =   $this->request->param('parentid')){
            $this->assign('list',TaskService::getTaskDetail($taskid));
        }
        return $this->assign('getDepartment',TaskService::getWxDepartment())
            ->assign('label',TaskService::getLabel())
            ->assign('project',TaskService::getProject())
            ->fetch();
    }
    public function getNextDeparment(){
        $condition    =   $this->request->param();
        $class  =  $condition['class'];
        unset($condition['class']);
        if($condition){
            return  $this->result(TaskService::getNextDeparment($condition,$class));
        }
        return false;
    }
    public function getChildWxDepartment(){
        $id    =   $this->request->param('id');
        if($id){
            $data['department'] =   TaskService::getChildWxDepartment($id);
            $data['user'] =   TaskService::getUserByDepartment($id);
            return  $this->result($data);
        }
        return false;
    }
    public function getUserDeparment(){
        $department =   $this->request->param();
        if($department){
            $departmentid   =   TaskService::getDepartmentId($department);
            return   $this->result(TaskService::getUserByDepartment($departmentid));
        }
    }

    /**添加团队任务
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function teamTask(){
        $access_token = TaskService::gettoken();
        if($taskid  =   $this->request->param('parentid')){
            $this->assign('list',TaskService::getTaskDetail($taskid));
        }
        return $this->assign('getDepartment',TaskService::getWxDepartment())
            ->assign('label',TaskService::getLabel())
            ->assign('project',TaskService::getProject())
            ->fetch();
    }
    /**
     * 添加部门任务
     */
   /* public function departmentTask(){
        if($taskid  =   $this->request->param('parentid')){
            $this->assign('list',TaskService::getTaskDetail($taskid));
        }
        return $this->assign('getDepartment',TaskService::getDepartment())
            ->assign('label',TaskService::getLabel())
            ->assign('project',TaskService::getProject())
            ->fetch();
    }*/
    public function departmentTask(){
        if($taskid  =   $this->request->param('parentid')){
            $this->assign('list',TaskService::getTaskDetail($taskid));
        }
        return $this->assign('getDepartment',TaskService::getWxDepartment())
            ->assign('label',TaskService::getLabel())
            ->assign('project',TaskService::getProject())
            ->fetch();
    }
    /**
     * 添加标签任务
     */
   /* public function labelTask(){
        if($taskid  =   $this->request->param('parentid')){
            $this->assign('list',TaskService::getTaskDetail($taskid));
        }
        return $this->assign('getDepartment',TaskService::getDepartment())
            ->assign('label',TaskService::getLabel())
            ->assign('project',TaskService::getProject())
            ->fetch();
    }*/
    public function labelTask(){
        if($taskid  =   $this->request->param('parentid')){
            $this->assign('list',TaskService::getTaskDetail($taskid));
        }
        return $this->assign('getDepartment',TaskService::getWxDepartment())
            ->assign('label',TaskService::getLabel())
            ->assign('project',TaskService::getProject())
            ->fetch();
    }
    /**
     * 添加任务
     */
    public function addpost()
    {
        if ($data = $this->request->param()) {
           /* if ($data['task_multiplayer'] == 3) {
                $label = $data['label'];
                unset($data['label']);
            };*/
            if (isset($data['userid'])) {
                $userid = $data['userid'];
                unset($data['userid']);
            };
            /*if ($data['task_multiplayer'] == 3) {
                $label = $data['label'];
                unset($data['label']);
                if($this->request->param('alldepartment') == 1){
                    $departmentid = false;
                    unset($data['alldepartment']);
                }else{
                    $departmentid = $this->request->param('departmentstr');
                }
                unset($data['departmentstr']);
                unset($data['one']);
                unset($data['two']);
                unset($data['three']);
                unset($data['four']);
                unset($data['five']);
                unset($data['six']);
                unset($data['seven']);
            }*/
            if ($data['task_multiplayer'] == 3) {
                $label = $data['label'];
                unset($data['label']);
                if($this->request->param('alldepartment') == 1){
                    $departmentid = false;
                    unset($data['alldepartment']);
                }else{
                    $departmentid = $this->request->param('departmentstr');
                }
                unset($data['departmentstr']);

            }
            if ($data['task_multiplayer'] == 4) {

                $departmentid = $this->request->param('departmentstr');
                if($departmentid && is_array($departmentid)){
                    $temp   =   [];
                    $access_token = TaskService::gettoken();
                    foreach ($departmentid as $val){
                        $url    =   "https://qyapi.weixin.qq.com/cgi-bin/department/simplelist?access_token=$access_token";
                        if($val){
                            $url    .=  "&id=".$val['id'];
                        }else{
                            continue;
                        }
                        $re = file_get_contents($url);
                        $tr = json_decode($re,true);
                        if(isset($tr['department_id'])){
                            foreach ($tr['department_id'] as $va){
                                if(!in_array($va['id'],$temp)){
                                    $temp[] =   $va['id'];
                                }
                            }
                        }
                    }
                    $departmentid   =   $temp;
                }else{
                    return $this->error('请至少选择一个部门！');
                }
                //var_dump($departmentid);die;
                unset($data['departmentstr']);
            }
            if ($data['task_multiplayer'] == 2) {
                if (!isset($data['departmentstr'])) {
                    return $this->error('请至少选择一个部门！');
                }
                $departmentid = $data['departmentstr'];
                unset($data['departmentstr']);
            }
            if (isset($data['userlist'])) {
                $userlist = $data['userlist'];
                unset($data['userlist']);
            };
            if($data['task_multiplayer'] == 1){
                $userlist   =   [];
                if ($_FILES["file"]["error"] == 0)
                {
                    if (!file_exists("/upload/" . $_FILES["file"]["name"])) {
                        $edn = explode('.', $_FILES["file"]["name"]);
                        $ext = end($edn);
                        $uid    =   cmf_get_current_admin_id();
                        $name = "upload/" .date("Ymdhis").'-'.$uid.'.'.$ext;
                        move_uploaded_file($_FILES["file"]["tmp_name"], $name);
                        //$info = TaskService::exportUser($name,$uid);
                        $usercode = TaskService::getuserlist($name);
                        $userarray   =   Db::name('user')->field('id,code')->where("user_login in ($usercode)")->select()->toArray();
                        $userlist   =   [];
                        $codelist   =   [];
                        foreach ($userarray as $ke=>$va){
                            $userlist[] = $va['id'];
                            $temp = $va['id'];
                            $codelist["$temp"] = $va['code'];
                        }

                    }
                }else{
                    return $this->error('上传文件失败！');
                }
                unset($data['file']);
            }
        if (Auth::getmudel()->fieldAuth($data, [
                'task_title' => "任务名不能为空！",
                'task_mark' => "任务分不能为空！",
                'task_content' => "任务内容不能为空",
                'task_start_time' => "任务内容不能为空！",
                'task_start_time' => "任务开始时间不能为空！",
                'task_end_time' => "任务结束时间不能为空！"
            ]) === true) {
            $data['task_create_id'] = cmf_get_current_admin_id();
            $data['task_start_time'] = isset($data['task_start_time']) ? strtotime($data['task_start_time']) : 0;
            $data['task_end_time'] = isset($data['task_end_time']) ? strtotime($data['task_end_time']) : 0;
            $data['task_submit_time'] = time();
            $data['identifier'] = date('YmdHis') . 'task' . cmf_get_current_admin_id();
            switch ($data['task_multiplayer']) {
                case 0:
                    if (!isset($userid)) {
                        return $this->error('请填写任务接受人！');
                    }
                    if (TaskModel::task()->createTaskByUserId($data, $userid)) {
                        return $this->success('添加任务成功！');
                    }
                    break;
                case 1:
                    if (TaskModel::task()->createTaskByUserList($data, $userlist,$codelist)) {
                        return $this->success('添加任务成功！');
                    }
                    break;
                case 2:
                    if (TaskModel::task()->createTaskByDepartment($data, $departmentid)) {
                        return $this->success('添加任务成功！');
                    }
                    break;
                case 3:
                    if (TaskModel::task()->createTaskByLabel($data, $label, $departmentid)) {
                        return $this->success('添加任务成功！');
                    }
                    break;
                case 4:
                    if (TaskModel::task()->createTaskByTeam($data,$departmentid)) {
                        return $this->success('添加任务成功！');
                    }
                    break;
            }
            return $this->error('创建任务错误，并发量过大，请谨慎使用！');
        }
    }
        return $this->error('服务器接收数据失败！');
    }

    /**新建任务列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function taskList(){
        $para   =   $this->request->param();
        $para   =   array_filter($para,'filter');
        $where  =   [];
        if(isset($para['task_status'])) $where[]    =   ['a.task_status','=',$para['task_status']];
        if(isset($para['task_start_time'])) $where[]    =   ['a.task_start_time','=',strtotime($para['task_start_time'])];
        if(isset($para['task_end_time'])) $where[]    =   ['a.task_status','=',strtotime($para['task_end_time'])];
        if(isset($para['task_title'])) $where[]    =   ['a.task_title','=',$para['task_title']];
        if(isset($para['user_nickname'])) $where[]    =   ['c.user_nickname','=',$para['user_nickname']];
        if(isset($para['task_multiplayer'])) $where[]    =   ['a.task_multiplayer','=',$para['task_multiplayer']];
        $where[]    =   ['a.task_create_id','=',cmf_get_current_admin_id()];
        $obj = Db::table('task_task')->alias('a')
            ->field('a.*,b.*,c.user_nickname,c.user_login')
            ->join('task_task_user b','a.task_id=b.task_id','left')
            ->join('task_user c','b.user_id=c.id','left')
            ->where($where)
            ->order('a.task_id desc');
        if(isset($para['export'])){
            $data   =   $obj->select();
            TaskService::export($data);exit;
        }else{
            $data   =   $obj ->paginate(10,false,['query'=>$para]);
        }
        return  $this->assign("list", $data)->fetch();
    }

    public function getDepartment(){
        $id =   $this->request->param('id');
        return response(TaskService::getDepartment($id));
    }

    public function getUserByDepartment(){
        $id =   $this->request->param('id');
        return $this->result(TaskService::getUserByDepartment($id));
    }

    public function getUserByName(){
        $name =   $this->request->param('name');
        return $this->result(TaskService::getUserByName($name));
    }

    /**我的任务列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function myTask(){
       /* $access_token = TaskService::gettoken();
        $depart= TaskService::getchild($access_token,1471341750);
        print_r($depart['userlist']);die;*/
        $para   =   $this->request->param();
        $para   =   array_filter($para,'filter');
        $where  =   [];
        if(isset($para['day'])) {
            $where[]    =    ['a.task_start_time','<',strtotime($para['day'])+24*60*60];
            $where[]    =    ['a.task_end_time','>',strtotime($para['day'])];
        }
        if(isset($para['task_status'])) $where[]    =   ['a.task_status','=',$para['task_status']];
        if(isset($para['task_start_time'])) $where[]    =   ['a.task_start_time','=',strtotime($para['task_start_time'])];
        if(isset($para['task_end_time'])) $where[]    =   ['a.task_end_time','=',strtotime($para['task_end_time'])];
        if(isset($para['task_title'])) $where[]    =   ['a.task_title','=',$para['task_title']];
        //if(isset($para['user_nickname'])) $where[]    =   ['c.user_nickname','=',$para['user_nickname']];
        $where[]    =   ['b.user_id','=',cmf_get_current_admin_id()];
        $list =   Db::table('task_task')->alias('a')
            ->distinct(true)
            ->field('a.*,b.task_user_type,c.user_nickname')
            ->join('task_task_user b','a.task_id=b.task_id','left')
            ->join('task_user c','a.task_create_id=c.id','left')
            ->where($where)
            ->order('a.task_id desc')->paginate(10,false,['query'=>$para]);
        return  $this->assign("list", $list)->fetch();
    }

    /**更改任务状态
     * @return \think\Response
     */
    public function changeTaskStatus(){
        $taskid =   $this->request->param('taskid');
        $re_status =   $this->request->param('status');
        $data['task_dynamic_user_id']   =   cmf_get_current_admin_id();
        $data['task_dynamic_task_id']   =  $taskid;
        $data['task_dynamic_content']   =  '修改任务状态为：'.getStatus($re_status);
        $data['task_dynamic_createtime']   =  time();
        $status =   TaskService::getTaskValue($taskid,'task_status');
        if($status  == 4 || $status ==  10 ){
            return $this->error('当前任务状态不可更改！');
        }
        Db::startTrans();
        try{
            TaskService::addTaskDynamic($data);
            $re =   TaskService::changeTaskStatus($taskid,$re_status);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $this->error('error');
        }
        if($re_status == '2'){
            $user = Db::name('task_user')->alias('a')->field('a.task_id,b.code')->join('user b','a.user_id=b.id','left')->where(['a.task_id'=>$taskid])->find();
            if(isset($user['code']) && $user['code']){
                TaskService::sendTaskMessage($user['code'],'您有一个新任务需要审核id:'.$user['task_id']);
            }
        }
        return $this->success('提交任务状态成功');
    }

    /**任务详情
     * @return mixed
     */
    public function taskDeatil(){
        $taskid =   $this->request->param('taskid');
        $data   =   TaskService::getTaskDetail($taskid);
        //任务动态信息
        $dynamic    =   TaskService::getTaskDynamic($taskid);
        //var_dump($dynamic);die;
        return  $this->assign("list", $data)->assign('currentid',cmf_get_current_admin_id())->assign('dynamic',$dynamic)->fetch();
    }
    /**任务审核
     * @return mixed
     */
    public function examine(){
        $taskid =   $this->request->param('taskid');
        $data   =   TaskService::getTaskDetail($taskid);
        //任务动态信息
        $dynamic    =   TaskService::getTaskDynamic($taskid);
        //var_dump($dynamic);die;
        return  $this->assign("list", $data)->assign('dynamic',$dynamic)->fetch();
    }
    public function getUserById(){
        if($uid = $this->request->param('uid')){
             $data  =   (new UserModel())->field('id,user_nickname,job,label')->where(['id'=>$uid])->find();
             return $this->result($data);
        }
        return $this->result('');
    }

    /**
     * 给任务添加动态
     */
    public function addTaskDynamic(){
        $data   =   $this->request->param();
        if(isset($data['task_dynamic_task_id']) && $data['task_dynamic_task_id'] && isset($data['task_dynamic_content'])){
            if(!$data['task_dynamic_content']){
                return  $this->error('添加动态内容不能为空！');
            }
            //sain modify 2022/7/25
            /*$task_target    =   TaskService::getTaskValue($data['task_dynamic_task_id'],'target');
            if( $task_target>0){
                if($data['actual'] >= $task_target){
                    $status['task_status'] = 3;
                    $status['task_complete_time'] = time();
                }
            }*/
            $status['actual'] =   $data['actual'];
            Db::name('task')->where(['task_id'=>$data['task_dynamic_task_id']])->update($status);
            $data['task_dynamic_user_id']   =   cmf_get_current_admin_id();
            $data['task_dynamic_createtime']   =  time();
            if(TaskService::addTaskDynamic($data)){
                return  $this->success('添加动态成功');
            }
            return  $this->error('添加动态失败！');
         }
    }
    /**
     * 给任务添加审核
     */
    public function examineStatus(){
        $taskid =   $this->request->param('taskid');
        $task_dynamic_content   =   $this->request->param('task_dynamic_content');
        $task_status   =   $this->request->param('task_status');
        if($taskid && $task_dynamic_content){
            $data['task_dynamic_user_id']   =   cmf_get_current_admin_id();
            $data['task_dynamic_task_id']   =  $taskid;
            $data['task_dynamic_content']   =  $task_dynamic_content;
            $data['task_dynamic_createtime']   =  time();
            $data['task_dynamic_title']   =  '审核任务';
            if($task_status ==  "3"){
                $data['task_dynamic_title'] .= '通过';
            }
            if($task_status == "5"){
                $data['task_dynamic_title'] .= '驳回';
            }
            if(TaskService::addTaskDynamic($data)){
                TaskService::changeTaskStatus($taskid,$task_status);
                $user = Db::name('task_user')
                    ->alias('a')->field('a.task_id,b.code')
                    ->join('user b','a.user_id=b.id','left')
                    ->where(['a.task_id'=>$taskid])->find();

                if(isset($user['code']) && $user['code']){
                    TaskService::sendTaskMessage($user['code'],'您的任务id:'.$user['task_id'].$data['task_dynamic_title']);
                }
                return  $this->success('添加动态成功');
            }
            return  $this->error('添加动态失败！');
        }
    }

    /**
     * 特殊编辑
     */
    public function edit(){
        $taskid =   $this->request->param('taskid');
        if($taskid){
            $info   =   TaskService::getTaskDetail($taskid);
        }
        if($this->request->isPost()){
              if($data=$this->request->param()){
                    Db::name('task')->where(['task_id'=>$taskid])->update($data);
                    return  $this->success('编辑成功');
            }
            return  $this->error('编辑失败，请稍后操作');
        }
        $dynamic    =   TaskService::getTaskDynamic($taskid);
        return  $this->assign("list", $info)->assign('dynamic',$dynamic)->fetch();
    }
    /**
     * 删除任务
     */
    public function taskDel(){
        $taskid =   $this->request->param('task_id');
        if($taskid){
            Db::startTrans();
            try{
                $redundancy =   TaskService::getTaskDetail($taskid);
                if(!$redundancy)  return $this->error('删除失败！任务不存在');
                TaskService::taskDel($taskid);
                $log    =   '删除任务'.$taskid.'--'.$redundancy['task_title'];
                $redundancy =   json_encode($redundancy);
                TaskService::setOperationLog(cmf_get_current_admin_id(),$log,$redundancy,$taskid);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return error('error');
            }
        }
        return $this->success('删除成功！');
    }

    /**特殊操作
     * @return mixed|void
     */
    public function delIdentifier(){
        $identifier =   $this->request->param('identifier');
        $task_status =   $this->request->param('task_status');
        $taskid =   $this->request->param('taskid');
        if($identifier){
            Db::startTrans();
            try{
                TaskService::delAllTask($identifier,$task_status);
                $log    =   '批量删除任务'.$identifier;
                TaskService::setOperationLog(cmf_get_current_admin_id(),$log);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return $this->success('删除失败！'.$e->getMessage());
            }
            return $this->success('删除成功！');
        }
        $data   =   TaskService::getAllTask($taskid);
        return $this->assign('list',$data)->fetch();
    }

    /**统计
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function statistics(){
        $para   =   $this->request->param();
        $para   =   array_filter($para,'filter');
        $uid =  cmf_get_current_admin_id();
        $where[]=['b.user_id','=',$uid];
        if(isset($para['task_start_time'])) $where[]    =   ['a.task_start_time','>=',strtotime($para['task_start_time'])];
        if(isset($para['task_end_time'])) $where[]    =   ['a.task_end_time','<=',strtotime($para['task_end_time'])];
        $data   =   Db::table('task_task')
            ->field('a.task_status,sum(a.task_mark) as score')
            ->alias('a')
            ->join('task_task_user b','a.task_id=b.task_id','left')
            ->group('a.task_status')
            ->where($where)
            ->select()->toArray();
        $count =  Db::table('task_task')
            ->alias('a')
            ->field('a.task_status,count(*) as count')
            ->join('task_task_user b','a.task_id=b.task_id','left')
            ->group('a.task_status')
            ->where($where)
            ->select()->toArray();
        foreach ($data as $key =>$val){
            $data[$key]['name'] =   getStatus($val['task_status']);
            foreach ($count as $ke =>$va){
                if($va['task_status'] == $val['task_status']){
                    $data[$key]['count'] =   $va['count'];
                }
            }
        }
        return  $this->assign("list", $data)->fetch();
    }

    public function zone(){
        //统计城市
        /* $data1   =   Db::name('district')
             ->where("parent_id >4 and parent_id <33")
             ->order('id asc')->select()->toArray();//省里面的市
         $data2 =  Db::name('district')->where('parent_id = 0 and id  <5 ')->select()->toArray();
         $data3 =  Db::name('district')->where('parent_id = 0 and id  >32 ')->select()->toArray();
         $data   =   array_merge($data2,$data3,$data1);
         foreach ($data as $val){
             //unset($val['id']);
             $up['id']   =   $val['id'];
             $up['uniacid']   =   4;
             $up['name']   =   $val['name'];
             $up['sort']   =   $val['order'];
             $up['enabled']   =  1;
             $up['firstname']    = strtoupper($val['initial']);
             $up['ison']   =  1;
             Db::name('weixinmao_zp_city')->insert($up);
         }*/

        //统计区县
        $data1   =   Db::name('district')
            ->where("parent_id <=4 and parent_id >0")
            ->order('id asc')->select()->toArray();//直辖市的区
        $data2   =   Db::name('district')
            ->where("parent_id >=33")
            ->order('id asc')->select()->toArray();//直辖市的区
        //$data3 =  Db::name('district')->where('parent_id = 0 and id  >32 ')->select()->toArray();
        $data   =   array_merge($data1,$data2);
        foreach ($data as $val){
            //unset($val['id']);
            //$up['id']   =   $val['id'];
            $up['uniacid']   =   4;
            $up['name']   =   $val['name'];
            $up['sort']   =   $val['order'];
            $up['enabled']   =  1;
            //$up['firstname']    = strtoupper($val['initial']);
            $up['cityid']   =  $val['parent_id'];
            Db::name('weixinmao_zp_area')->insert($up);
        }
        /*
         $json = [];
        foreach ($data as $val){
            $id = $val['id'];//区域id
            if($val['parent_id'] == 0){
                $json["$id"] =  $val;
            }else{

            }

        }
        print_r($json);*/
        die;
    }

    /**
     * 获取公告详情
     */
    public function getBulletin(){
        $id =   $this->request->param('id');
        if($id){
              $data =   Db::name('bulletin')->where("bulletin_id=$id")->find();
            return '<div class="admin-content am-padding am-padding-top-0">
                    <div class="task-list">
                        <h2 class="am-margin-top-sm am-margin-bottom-sm">'.$data['bulletin_title'].'</h2>
                    </div>
                    <article class="am-article am-margin-top-sm">
                                <div class="am-article-bd">
                           '.htmlspecialchars_decode($data['bulletin_content']).'       </div>
                     </article>
                        </div>';
        }
        return  "";
    }

    /**公告列表
     * @return mixed
     */
    public function bulletin(){
        $bulletin = TaskService::getBulletin();
        return  $this->assign("bulletin", $bulletin)->fetch();
    }

    /**添加公告
     * @return mixed|void
     */
    public function addBulletin(){
        $post   =   $this->request->param();
        if($this->request->isPost()){
            $data['bulletin_listsort']  =   $post['bulletin_listsort'];
            $data['bulletin_title']  =   $post['bulletin_title'];
            $data['bulletin_content']  =   $post['content'];
            $data['bulletin_createtime'] =   strtotime($post['createtime']);
            if(TaskService::addBulletin($data)){
                return TaskService::result(200,'公告成功',url("task/task/bulletin"));
            }
            return TaskService::result(500,'公告失败',url("task/task/bulletin"));
        }
        return  $this->fetch();
    }

    /**编辑公告
     * @return mixed|void
     */
    public function editBulletin(){
        if($id = $this->request->param('bulletin_id')){
            if($this->request->isPost()){
                $post   =   $this->request->param();
                $data['bulletin_listsort']  =   $post['bulletin_listsort'];
                $data['bulletin_title']  =   $post['bulletin_title'];
                $data['bulletin_content']  =   $post['content'];
                $data['bulletin_createtime'] =   strtotime($post['createtime']);
                if(TaskService::updateBulletin($id,$data)){
                    return TaskService::result(200,'公告成功',url("task/task/bulletin"));
                }
                return TaskService::result(500,'公告失败',url("task/task/bulletin"));
            }
            $data   =   TaskService::getBulletin($id);
            return  $this->assign('data',$data)->fetch();
        }
    }

    /**删除公告
     * @return mixed|void
     */
    public function delBulletin(){
        if($id = $this->request->param('bulletin_id')){
                if(TaskService::delBulletin($id)){
                    return TaskService::result(200,'删除公告成功',url("task/task/bulletin"));
                }
                return TaskService::result(500,'删除公告失败',url("task/task/bulletin"));
            }

    }

    public function exportUser(){
        if($this->request->isPost()){
            $roleid = $this->request->param('role');
            $token = TaskService::gettoken();
           /* $url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=".$token;
            $re = file_get_contents($url);
            $val = json_decode($re,true);
            if(!$this->request->param('page')){
                Db::name("wxdepartment")->update(['isup'=>0]);
            }*/
           if($this->request->param('status') == 1){
               Db::name("wxdepartment")->where(['isup'=>1])->update(['isup'=>0]);
           }
            $page = $this->request->param('page') ?$this->request->param('page') :0;
            if(!$count =  $this->request->param('count')){
                $count = Db::name("wxdepartment")->where("isup=0")->count();
            }
            $com = Db::name("wxdepartment")->where("isup=0")->limit(10)->order("id asc")->select();
            if(isset($com)){
                foreach ($com as $key => $val){
                    $url = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token=".$token."&department_id=".$val['id'];
                    $re = file_get_contents($url);
                    $val1 = json_decode($re,true);
                    if(isset($val1['userlist'])){
                        foreach ($val1['userlist'] as $ke => $va){
                            $data['user_login'] = $va['userid'];
                            $data['code'] = $va['userid'];
                            $data['user_nickname'] = $va['name'];
                            //$data['role_id'] = 2;
                            $data['user_status'] = 1;
                            $data['user_pass'] = '###820798c3e811eaad5783ea010ce471ea';
                            $data['departmentid'] = $val['id'];

                            $data['user_email'] = $va['email'];
                            $data['mobile'] = $va['mobile'];
                            $data['job'] = $va['position'];

                            $data['label'] = $va['alias'];
                            $data['avatar'] = $va['avatar'];
                            $data['sex'] = $va['gender'];
                            if( Db::name("user")->where(['user_login'=>$va['userid']])->find()){
                                Db::name("user")->where(['user_login'=>$va['userid']])->update($data);
                            }else{
                                Db::startTrans();
                                try{
                                    $uid    =   Db::name("user")->insertGetId($data);
                                    Db::name("role_user")->insert(['role_id'=>$roleid,'user_id'=>$uid]);
                                    Db::commit();
                                }catch (\Exception $e){
                                    Db::rollback();
                                    echo $e->getMessage();
                                }

                            }
                            unset($data);
                            unset($temp);
                        }
                    }
                    unset($data);
                    Db::name("wxdepartment")->where("id=".$val['id'])->update(['isup'=>1]);
                }

            }
            //echo $count;die;
            if($count == 0){
                return TaskService::result(200,'成功！',url("task/task/exportUser"),['page'=>$page+1,'total'=>ceil($count/10),'count'=>$count,'speed'=>100,'role'=>$roleid]);
            }
            $speed = floor((($page+1)*1000)/$count);
            $speed = $speed >100 ? 100 :$speed;
            return TaskService::result(200,'成功！',url("task/task/exportUser"),['page'=>$page+1,'total'=>ceil($count/10),'count'=>$count,'role'=>$roleid,'speed'=>$count>10 ? $speed: 100]);
            //return json_encode();
        }
        $role = Db::name("role")->field('id,name')->where(['status'=>1])->select();
        $info   =   Db::name('user')->alias('a')->field('a.*,b.name as department_name')->join('wxdepartment b','a.departmentid=b.id','left')->where('a.id > 1')->order('a.id asc')->paginate(10);
        return $this->assign('list',$info)->assign('role',$role)->fetch();
    }

    public function exportDepartment(){

        if($this->request->isPost()){
            $token = TaskService::gettoken();
            $url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=".$token;
            $re = file_get_contents($url);
            $val = json_decode($re,true);
            if(isset($val['department'])){
                foreach ($val['department'] as $key => $val){
                    $data['id'] = $val['id'];
                    $data['name'] = $val['name'];
                    $data['parentid'] = $val['parentid'];
                    $data['order'] = $val['order'];
                    $data['update_time'] = date("Y-m-d H:i:s");
                    if( Db::name("wxdepartment")->where(['id'=>$val['id']])->find()){
                        continue;
                    }
                    Db::name("wxdepartment")->insert($data);
                    unset($data);
                }

            }
        }
        $info   =   Db::name('wxdepartment')->order('order asc')->paginate(10);
        return $this->assign('list',$info)->fetch();
    }

    public function taskTransfer(){
        $taskid =   $this->request->param('taskid');
        if($taskid){
            $info   =   TaskService::getTaskDetail($taskid);
        }
        if($this->request->isPost()){
            $taskid =   $this->request->param('transferid');
            $userid =   $this->request->param('userid');
            if(!$userid){
                return  $this->error('转移失败，找不到改用户');
            }
            $uid    =   cmf_get_current_admin_id();
            if($info['task_create_id'] == $uid){
                if(TaskService::updateTaskUser($taskid,$userid)){
                    $data['task_dynamic_user_id']   =   $uid;
                    $data['task_dynamic_task_id']   =  $taskid;
                    $data['task_dynamic_content']   =  TaskService::getUser($uid,'user_nickname').'修改任务归属人为：'.TaskService::getUser($userid,'user_nickname');
                    $data['task_dynamic_createtime']   =  time();
                    TaskService::addTaskDynamic($data);
                    return  $this->success('转移成功!');
                }
            }else{
                $data['taskid'] =   $taskid;
                $data['applicant'] =   $uid;
                $data['acceptid'] =   $userid;
                if(TaskService::addTaskTransfer($data)){
                    $Dynamic['task_dynamic_user_id']   =   $uid;
                    $Dynamic['task_dynamic_task_id']   =  $taskid;
                    $Dynamic['task_dynamic_content']   =  TaskService::getUser($uid,'user_nickname').'申请任务转移给'.TaskService::getUser($userid,'user_nickname');
                    $Dynamic['task_dynamic_createtime']   =  time();
                    TaskService::addTaskDynamic($Dynamic);
                    return  $this->success('申请成功!等待接收人确认');
                }
            }
            return  $this->error('转移失败，请稍后操作');
        }
        $dynamic    =   TaskService::getTaskDynamic($taskid);
        return  $this->assign("list", $info)->assign('dynamic',$dynamic)->fetch();
    }

    public function transferList(){
        $uid    =   cmf_get_current_admin_id();
        $info   =   DB::name('transfer')
            ->alias('a')
            ->join('task b','a.taskid=b.task_id','left')
            ->where("a.acceptid=$uid")
            ->select()->toArray();
        return $this->assign('info',$info)->fetch();
    }

    public function doTransfer(){
        $id =   $this->request->param('id');
        $transfer_status    =   $this->request->param('transfer_status');
        if($id  &&  $transfer_status){
            $uid    =   cmf_get_current_admin_id();
            $task =    Db::table('task_transfer')
                    ->alias('a')
                    ->join('task_task b','a.taskid=b.task_id','left')
                    ->join('task_task_user c',"b.task_id=c.task_id",'left')
                    ->where("a.id=$id")
                    ->find();
            //var_dump($task);die;
            $data['task_dynamic_user_id']   =   $uid;
            $data['task_dynamic_task_id']   =  $task['task_id'];
            if($transfer_status==1){
                $data['task_dynamic_content']   =  TaskService::getUser($uid,'user_nickname').'接受了转移任务';
            }else{
                $data['task_dynamic_content']   =  TaskService::getUser($uid,'user_nickname').'拒绝了转移任务';
            }
             $data['task_dynamic_createtime']   =  time();
            Db::startTrans();
            try{
                TaskService::addTaskDynamic($data);
                $re =    Db::name('transfer')->where(['id'=>$id])->update(['transfer_status'=>$transfer_status,'update_time'=>time()]);
                $re =    Db::name('task_user')->where(['task_id'=> $task['task_id']])->update(['user_id'=>$task['acceptid']]);
                Db::commit();
                return  $this->success('success');
            }catch (\think\Exception $e){
            //Db::rollback();
            //echo $e->getMessage();
                return  $this->error('error');
        }
        }
    }
    public function modifyDepartment(){
        $id =   $this->request->param('id');
        if($id){
            if($this->request->isPost()){
                $post   =   $this->request->param('post');
                $departmentid   =   TaskService::getDepartmentId($post);
                return TaskService::setDepartment($id,$departmentid) ? $this->success('修改成功') : $this->error('修改失败');
            }
            $data   =   TaskService::getDepartmentByUid($id);
            return  $this->assign("user", $data)->assign('getDepartment',TaskService::getDepartment())->fetch();
        }
        return false;
    }

    public function delLog(){
        $data   =   Db::name('operation_log')
            ->alias('a')
            ->field('a.*,b.user_nickname')
            ->join('user b','a.userid=b.id','left')
            ->order('a.id desc')->paginate(10);
        $data_array =   $data->toArray();
        $list   =   $data_array['data'];
        //print_r($data_array);die;
        foreach ($list as $key =>$val){
            $list["$key"]['redundancy'] =   json_decode($val['redundancy'],true);
        }
        return  $this->assign("data", $list)->assign('data_array',$data)->fetch();
    }
    public function getLabel(){
        $name   =   $this->request->param('name');
        if($name){
            $re =   Db::name('user')->field('label')->where("label like '%$name%'")->group('label')->select()->toArray();
            return $this->result($re);
        }
    }
}
