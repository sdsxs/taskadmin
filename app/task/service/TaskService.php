<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/2/10
 * Time: 17:28
 */
namespace app\task\service;


use think\Db;
use think\Response;
use think\exception\HttpResponseException;
use think\facade\Cache;

class TaskService
{
    /**获取部门
     * @param null $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public static function getDepartment($id = null){
        if($id){
            return Db("user_department")->where(['id'=>$id])->select()->toArray();
        }
        return Db("user_department")->group('one')->field('one')->order("one asc")->select()->toArray();
    }

    /**获取企微配置
     * @param null $CorpID
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getConfig($CorpID = null){
        if($CorpID){
            return Db("set")->where(['CorpID'=>$CorpID])->find()->toArray();
        }
        return Db("set")->find()->toArray();
    }
    /**获取企业微信的部门信息
     * @param null $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getWxDepartment($id = null){
        if($id){
            return Db("wxdepartment")->where(['id'=>$id])->select()->toArray();
        }
        return Db("wxdepartment")->where(['parentid'=>0])->order('name asc')->select()->toArray();
    }

    public static function getChildWxDepartment($id = null){
        if($id){
            return Db("wxdepartment")->where(['parentid'=>$id])->select()->toArray();
        }
        return Db("wxdepartment")->where(['parentid'=>1])->select()->toArray();
    }
    /**根据部门获取用户
     * @param $departmentid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserByDepartment($departmentid){
        return Db("user")->where(['departmentid'=>$departmentid,'user_status'=>1])->select()->toArray();
    }
    public static function getUserByName($name){
        return Db("user")->where('user_nickname like "%'.$name.'%"')->select()->toArray();
    }

    /**获取标签
     * @param bool $labelid
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getLabel($labelid=false){
        if($labelid){
            return Db::name('user_label')->where(['labelid'=>$labelid])->find();
        }
        return Db::name('user')->field('label')->where('label is not null')->group('label')->select()->toArray();
    }

    /**获取我的任务列表
     * @param $userid
     * @return bool|\think\db\Query[]|\think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getMyTask($userid){
        if(!$userid){
            return  false;
        }
        return Db::table('task_task')
            ->alias('a')
            ->join('task_task_user b','a.task_id=b.task_id','left')
            ->where("b.user_id =$userid and b.task_user_type=2")
            ->order('a.task_id desc')
            ->paginate(10);
        //echo Db::name('task')->getLastSql();die();
    }

    /**更改任务状态
     * @param $taskid
     * @param $status
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function changeTaskStatus($taskid,$status){
        if($taskid){
            $data['task_status']    =   $status;
            $data['task_complete_time'] =   time();
            return Db::name('task')->where(['task_id'=>$taskid])->update($data);
        }
        return false;
    }

    /**获取任务信息
     * @param $taskid
     * @param null $field
     * @return array|mixed|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getTaskValue($taskid,$field=null){
        if($field){
            return Db::name('task')->where(['task_id'=>$taskid])->value($field);
        }
        return Db::name('task')->where(['task_id'=>$taskid])->find();
    }
    /**获取任务详情
     * @param $taskid
     */
    public static function getTaskDetail($taskid){
        return Db::table('task_task')
            ->alias('a')
            ->field('a.*,b.project_title,c.user_nickname as task_create_user,f.user_nickname as accept_user,g.*')
            ->join('task_project b','a.task_project_id=b.project_id','left')
            ->join('task_user c','a.task_create_id=c.id','left')
            ->join('task_task_user d','a.task_id=d.task_id','left')
            ->join('task_user f','f.id=d.user_id','left')
            ->join('task_user_department g','f.departmentid=g.id','left')
            ->where(['a.task_id'=>$taskid])->find();
    }

    public static function getTaskDynamic($taskid){
        return Db::name('dynamic')
            ->alias('a')
            ->field('a.*,b.user_nickname')
            ->join('user b','a.task_dynamic_user_id=b.id','left')
            ->where(['a.task_dynamic_task_id'=>$taskid])->select();
        // echo  Db::name('dynamic')->getLastSql();die;
    }

    public static function addTaskDynamic($data){
        return  Db::name('dynamic')->insert($data);
    }

    public static function getProject($project_id = null){
        if($project_id){
            return  Db::name('project')->where(['project_id'=>$project_id])->find();
        }
        return  Db::name('project')->order('project_id desc')->paginate(10);
    }

    /**新增项目
     * @param $data
     * @return bool|int|string
     */
    public static function addProject($data){
        if($data){
            return  Db::name('project')->insert($data);
        }
        return  false;
    }
    /**新增项目
     * @param $data
     * @return bool|int|string
     */
    public static function delProject($project_id){
        if($project_id){
            return  Db::name('project')->where(['project_id'=>$project_id])->delete();
        }
        return  false;
    }

    public static function updateProject($id,$data){
        if($id){
            return  Db::name('project')->where(['project_id'=>$id])->update($data);
        }
        return  false;
    }
    /**删除任务 及其动态
     * @param $taskid
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function taskDel($taskid){
        Db::name('dynamic')->where(['task_dynamic_task_id'=>$taskid])->delete();
        Db::name('task_user')->where(['task_id'=>$taskid])->delete();
        Db::name('task')->where(['task_id'=>$taskid])->delete();
    }

    /**
     * 对应的任务时效图。主要快速展示整体、个人、项目的新任务与完成任务的时效
     * 本功能，结合任务
     * @param string $day 时效日期，默认为30天
     * @return array
     */

    public static function taskAgingGapFigureLineChart($uid,$day = '30') {
        $param = [];

        if (!empty($_GET['begin']) && !empty($_GET['end'])) {
            $param['start'] = strtotime($_GET['begin'] . '00:00:00');
            $param['end'] = strtotime($_GET['end'] . '23:59:59');
        } else {
            $param['start'] = time() - ($day * 86400);
            $param['end'] = time();
        }

        //$prefix = self::$modelPrefix;

        $field = ['task_submit_time', 'task_complete_time'];
        $list = [];
        //组装基础的内容
        foreach ($field as $color => $timeField) {
            $result =   Db::table('task_task')
                ->alias('a')
                ->field("a.*,b.user_id,b.task_user_type")
                ->join('task_task_user b','a.task_id=b.task_id')
                ->where("b.user_id = '$uid' and b.task_user_type = 2 and a.$timeField Between ".$param['start']." and ".$param['end'])
           // ->whereBetween('t.task_submit_time',[$param['start'], $param['end']],'AND' )
            ->select()->toArray();
            if (!empty($result)) {
                foreach ($result as $key => $value) {
                    $list['date'][$date] = $date = date('Y-m-d', $value[$timeField]);

                    if (empty($array[$timeField][$date])) {
                        $array[$timeField][$date] = 0;
                    }
                    $array[$timeField][$date]++;
                }
            }
        }

        if (empty($list)) {
            return false;
        }

        /**
         * 重组一次数据，让数据可以直接用于图表
         */
        foreach ($list['date'] as $date) {
            foreach ($field as $key => $timeField) {
                if (empty($array[$timeField][$date])) {
                    $list['list'][$key]['data'][] = 0;
                } else {
                    $list['list'][$key]['data'][] = $array[$timeField][$date];
                }

                switch ($timeField) {
                    case 'task_submit_time':
                        $list['list'][$key]['color'] = '#dd514c';
                        $list['list'][$key]['name'] = '新任务';
                        break;
                    case 'task_complete_time':
                        $list['list'][$key]['color'] = '#5eb95e';
                        $list['list'][$key]['name'] = '已完成任务';
                        break;
                }

            }
        }
//var_dump($list);die;
        return $list;
    }

    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param  mixed     $data 要返回的数据
     * @param  integer   $code 返回的code
     * @param  mixed     $msg 提示信息
     * @param  string    $type 返回数据格式
     * @param  array     $header 发送的Header信息
     * @return void
     */
    public static function result( $status = 200, $msg = '',$back_url='',$data="", $type = 'json', array $header = [])
    {
        $result = [
            'status' => $status,
            'msg'  => $msg,
            'time' => time(),
            'url' => $back_url,
            'data' => $data
        ];

        $type     = $type ?: self::getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**获取公告内容
     * @param null $id
     * @return array|null|\PDOStatement|string|\think\db\Query[]|\think\Model|\think\Paginator
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public static function getBulletin($id  =   null){
        if($id){
            return Db::name('bulletin')->where(['bulletin_id'=>$id])->find();
        }
        return Db::name('bulletin')->order('bulletin_id desc')->paginate(10);
    }

    /**添加公告
     * @param $data
     * @return bool|int|string
     */

    public static function addBulletin($data){
        if($data){
            return Db::name('bulletin')->insert($data);
        }
        return false;
    }

    /**更新公告
     * @param $id
     * @param $data
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */

    public static function updateBulletin($id,$data){
        if($data && $id){
            return Db::name('bulletin')->where(['bulletin_id'=>$id])->update($data);
        }
        return false;
    }

    /**删除公告
     * @param $id
     * @return bool|int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */

    public static function delBulletin($id){
        if($id){
            return Db::name('bulletin')->where(['bulletin_id'=>$id])->delete();
        }
        return false;
    }

    public static function exportUser($filepath,$uid){
        $PHPExcel = self::getExcelObj($filepath); // 载入excel文件
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        //echo $highestRow;die;
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $info='';
        /** 循环读取每个单元格的数据 */
        //Db::startTrans();
        try{
            for ($row = 2; $row <= $highestRow; $row++){//行数是以第1行开始
                if(!$sheet->getCell("A".$row)->getValue()){
                    continue;
                }
                //$data["tid"] = $sheet->getCell("A".$row)->getValue();
                $data["user_nickname"] = $sheet->getCell("C".$row)->getValue();//姓名
                $data["code"] = $sheet->getCell("B".$row)->getValue();//员工号
                $data['user_login']  =   $sheet->getCell("B".$row)->getValue();//登录账号
                $data['user_pass']  =   cmf_password($sheet->getCell("D".$row)->getValue());//密码
                $data['job']  =   $sheet->getCell("P".$row)->getValue();//职位
                $data['label']  =   $sheet->getCell("P".$row)->getValue();
                $data['user_status']    =   1;
                $data['mobile'] =   $sheet->getCell("D".$row)->getValue();

                $depart['one']   =   $sheet->getCell("H".$row)->getValue();
                $depart['two']   =   $sheet->getCell("I".$row)->getValue();
                $depart['three']   =   $sheet->getCell("J".$row)->getValue();
                $depart['four']   =   $sheet->getCell("K".$row)->getValue();
                $depart['five']   =   $sheet->getCell("L".$row)->getValue();
                $depart['six']   =   $sheet->getCell("M".$row)->getValue();
                $depart['seven']   =   $sheet->getCell("N".$row)->getValue();

                if( $departmentid   =   Db::name("user_department")->where($depart)->value('id')){
                    $data['departmentid']   =   $departmentid;
                }else{
                    $data['departmentid']   =   Db::name("user_department")->insertGetId($depart);
                }
                $role   =   $sheet->getCell("S".$row)->getValue();
                //ECHO $role."***";DIE;
                switch ($role){
                    case '普通角色':
                        $roleid =   2;break;
                    case '普通角色-分总':
                        $roleid =   3;break;
                    case '普通角色-事业部总':
                        $roleid =   4;break;
                    case '普通角色-业务经理':
                        $roleid =   5;break;
                    case '普通角色-业态总':
                        $roleid =   6;break;
                    case '普通角色-业务主管':
                        $roleid =   7;break;
                }
                if(!isset($roleid)){
                    return "无角色";
                }
                $info .=  '<span>'.$data["user_nickname"]."更新成功</span><br>";
                $userid =   Db::name('user')->insertGetId($data);
                Db::name('role_user')->insert(['role_id'=>$roleid,'user_id'=>$userid]);
                unset($data);
                unset($depart);
            }
            // Db::commit();
        }catch (\think\Exception $e){
            //Db::rollback();
            echo $e->getMessage();
            //echo $e->getMessage();
        }
        return $info;
    }


    public static function exportFormat($filepath,$uid){
        $PHPExcel = self::getExcelObj($filepath); // 载入excel文件
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        //echo $highestRow;die;
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $info='';
        /** 循环读取每个单元格的数据 */
        //Db::startTrans();
        try{
            for ($row = 2; $row <= $highestRow; $row++){//行数是以第1行开始
                if(!$sheet->getCell("A".$row)->getValue()){
                    continue;
                }
                //$data["tid"] = $sheet->getCell("A".$row)->getValue();
                $data["primary_category"] = $sheet->getCell("A".$row)->getValue();//姓名
                $data["secondary_category"] = $sheet->getCell("B".$row)->getValue();//员工号
                $data['tertiary_category']  =   $sheet->getCell("C".$row)->getValue();//登录账号
                $data['format']  =   $sheet->getCell("D".$row)->getValue();//密码
                //var_dump($data);die;
                if(Db::table('source_format')->where($data)->value('id') >0){
                    continue;
                }
                Db::table('source_format')->insert($data);
            }
        }catch (\think\Exception $e){
            //Db::rollback();
            echo $e->getMessage();
            //echo $e->getMessage();
        }
        return $info;
    }

    public static function getExcelObj($filepath){
        include_once dirname($_SERVER['DOCUMENT_ROOT'])."/extend/PHPExcel/PHPExcel.php";
        header("Content-type: text/html; charset=utf-8");
        error_reporting(E_ALL);
        date_default_timezone_set('Asia/ShangHai');
        /** PHPExcel_IOFactory */
        $file_temp = $_SERVER['DOCUMENT_ROOT'] ;
        //$file = $file_temp."/public/".$filepath;
        $file = $file_temp."/".$filepath;
        if (!file_exists($file)) {
            exit("not found $file.\n");
        }
        $ext_arr= explode(".",$filepath);
        $ext = end($ext_arr);
        if($ext == "xls"){
            $reader = PHPExcel_IOFactory::createReader('Excel5'); //设置以Excel5格式(Excel97-2003工作簿)
        }else if($ext == "xlsx"){
            $reader = new \PHPExcel_Reader_Excel2007();
        }
        //$reader  = new \PHPExcel_Reader_Excel2007();
        $PHPExcel = $reader->load($file); // 载入excel文件
        return $PHPExcel;
    }

    public static function getNextDeparment($condition,$class){
        if($class   ==  'seven'){
            return  Db::name('user_department')->field("id,$class")->group("$class")->where($condition)->select()->toArray();
        }
        return  Db::name('user_department')->field("$class")->group("$class")->where($condition)->select()->toArray();
    }

    public static function getDepartmentId($data){
        return  Db::name('user_department')->where($data)->value('id');

    }

    public static function updateTaskUser($taskid,$userid){
        return  Db::name('task_user')->where(['task_id'=>$taskid])->update(['user_id'=>$userid]);
    }

    public static function getUser($uid,$field = null){
        $user   =    Db::name('user')->where(['id'=>$uid])->find();
        if($field) return  $user["$field"];
        return $user;
    }
    public static function getDepartmentByUid($uid){
        return Db::name('user')
            ->alias('a')
            ->field('a.user_nickname,b.*')
            ->join('user_department b','a.departmentid=b.id')
            ->where("a.id=$uid")
            ->find();
    }
    public static function setDepartment($uid,$departmentid){
        return Db::name('user')->where("id=$uid")->update(['departmentid'=>$departmentid]);
    }

    public static function getAllTask($taskid=null){
        if($taskid){
            return Db::name('task')->alias('a')
                ->field('a.*,b.user_nickname')
                ->join('user b','a.task_create_id=b.id','left')
                ->where("a.task_id=$taskid")
                ->order("a.identifier desc")->paginate(10);
        }
        return Db::name('task')->alias('a')
            ->field('a.*,b.user_nickname')
            ->join('user b','a.task_create_id=b.id','left')
            ->order("a.identifier desc")->paginate(10);
    }

    public static function delAllTask($identifier,$task_status=false){
        $where[] =  ['identifier','=',$identifier];
        $sql = 'DELETE  a from task_task_user a 
          LEFT JOIN task_task b on a.task_id=b.task_id where b.identifier="'.$identifier.'"';
        if($task_status != false && $task_status != ""){
            $where[]    =   ['task_status','=',$task_status];
            $sql .= " and b.task_status='$task_status'";
        }
        DB::query($sql);
         Db::table('task_task')->where($where)->delete();
    }


    /**
     * 获取企业微信的token
     * @param string $corpsecret  自建应用secreat
     * @return bool
     */
    public static function gettoken(){
        if(cache::get("wx_token")){
            return cache::get("wx_token");
        }
        $corpsecret =   Db::name('set')->value('Secret');
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=ww2f6738b92666c63a&corpsecret=$corpsecret";
        $re = file_get_contents($url);
        $val = json_decode($re,true);
        if(isset($val['access_token'])){
            cache::set("wx_token",$val['access_token'],1600);
            return $val['access_token'];
        }
        return  false;
    }
    /*
     * 提交数据
     * */
    public static function curl($url, $data = [], $header=null, $method = 'POST', $time_out = 3)
    {
        if(!$header){
            $header =   [
                'Accept:application/json',
                'Content-Type:application/json',
            ];
        }
        $curl = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $time_out);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        // 执行操作
        $result = curl_exec($curl);

        curl_close($curl);
        return $result;
    }

    /*
     * 发送企业微信消息
     * */
    public static function sendMessage($access_token,$code,$content='',$toparty='',$enable_duplicate_check = 0)
    {
        $url    =   "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$access_token;
        $data   =   [
            'touser' =>$code,
            'msgtype'=>'text',
            "text" =>[
                'content'=>$content
            ],
            'toparty'=>$toparty,
            'enable_duplicate_check'=>$enable_duplicate_check,
        ];
        try{
            $re = TaskService::curl($url,json_encode($data));
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }
    /*
     * 发送任务系统企业微信推送消息
     * */
    public static function sendTaskMessage($code,$content='',$agentid)
    {
        $access_token = self::gettoken();
        $url    =   "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$access_token;
        $data   =   [
            'touser' =>$code,
            'msgtype'=>'text',
            "text" =>[
                'content'=>$content
            ],
            'agentid'=>$agentid,
            'enable_duplicate_check'=>1,
        ];
        try{
            $re = TaskService::curl($url,json_encode($data));
            return $re;
        }catch (Exception $e){
            echo $e->getMessage();
        }

    }
    /**
     * 获取企业微信的员工号
     * @param $access_token
     * @param $code
     * @return false|mixed
     */
    public static function getuserid($access_token,$code){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=".$access_token."&code=".$code;
        $re = file_get_contents($url);
        $val = json_decode($re,true);
        if(isset($val['UserId'])){
            return $val['UserId'];
        }else{
          var_dump($val) ;  die;
        }
        return  false;
    }

    public static function getUserInfo($access_token,$userid){
        //$url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=".$access_token."&code=".$code;
        $url    =   "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&userid=$userid";
        $re = file_get_contents($url);
        $val = json_decode($re,true);
        if(isset($val)){
            return $val;
        }
        return  false;
    }

    /**获取企业微信的部门列表
     * @param $access_token
     * @param null $ID
     * @return bool|mixed
     */
    public static function getWxDepartmentList($access_token,$ID=NULL){
        $url    =   "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=".$access_token;
        if($ID){
            $url    .=  "&id=$ID";
        }
        $re = file_get_contents($url);
        $val = json_decode($re,true);
        if(isset($val)){
            return $val;
        }
        return  false;
    }

    /**获取微信企业用户的详细信息
     * @param $access_token
     * @param $code
     * @return bool|mixed
     */
    public static function getWxUserInfo($access_token,$code){
    $url    =   "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=$access_token&userid=".$code;
    $re = file_get_contents($url);
    $val = json_decode($re,true);
    if(isset($val)){
        return $val;
    }
    return  false;
}

    /**获取微信企获取部门成员
     * @param $access_token
     * @param $code
     * @return bool|mixed
     */
    public static function getWxSimplelist($access_token,$department_id){
        $url    =   "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=$department_id&fetch_child=1";
        $re = file_get_contents($url);
        $val = json_decode($re,true);
        if(isset($val)){
            return $val;
        }
        return  false;
    }

    public static function setOperationLog($userid,$content,$redundancy='',$taskid=''){
        return  Db::name('operation_log')->insert(['userid'=>$userid,'content'=>$content,'redundancy'=>$redundancy,'taskid'=>$taskid]);
    }

    public static function getFreamwork($id){
        $seven =   Db::name('wxdepartment')->field('name,parentid')->where(['id'=>$id])->find();
        $data['seven']  =   $seven['name'];
    }
    public static function getchild($access_token,$department_id){
        $url    =   "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=$access_token&department_id=$department_id&fetch_child=1";
        $re = file_get_contents($url);
        $val = json_decode($re,true);
        if(isset($val)){
            return $val;
        }
        return  false;
    }
    public static function addTaskTransfer($data){
        if( Db::name('transfer')->where($data)->count()<1){
            $data['create_time']    =   time();
           return Db::name('transfer')->insert($data);
        }
        return false;
    }

    public static function getuserlist($filepath){
        $PHPExcel = self::getExcelObj($filepath); // 载入excel文件
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        //echo $highestRow;die;
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $info='';
        /** 循环读取每个单元格的数据 */
        //Db::startTrans();
        $data   =   '';
        try{
            for ($row = 2; $row <= $highestRow; $row++){//行数是以第1行开始
                if(!$sheet->getCell("A".$row)->getValue()){
                    continue;
                }
                //$data["tid"] = $sheet->getCell("A".$row)->getValue();
                $data .= $data ? ','.$sheet->getCell("A".$row)->getValue() : $sheet->getCell("A".$row)->getValue();//姓名
            }
        }catch (\think\Exception $e){

            echo $e->getMessage();
        }
        return $data;
    }

    public static  function export($data){
        include_once dirname($_SERVER['DOCUMENT_ROOT'])."/extend/PHPExcel/PHPExcel.php";
        header("Content-type: text/html; charset=utf-8");
        error_reporting(E_ALL);
        $filename = date('Y-m-dHis');
        $startRow=2;
        $excel2007 = true;
        $header_arr = [
            'A'=>'任务标题',
            'B'=>'任务分类',
            'C'=>'任务标识',
            'D'=>'接受人',
            'E'=>'创建时间',
            'F'=>'开始时间',
            'G'=>'结束时间',
            'H'=>'目标值',
            'I'=>'实际值',
            'J'=>'分数',
            'K'=>'状态'
        ];
        $indexKey   =   [
            'task_title','task_type','identifier','user_nickname','task_submit_time',
            'task_start_time','task_end_time','target','actual',
            'task_mark','task_status'];
        //初始化PHPExcel()
        $objPHPExcel = new \PHPExcel();
        //设置保存版本格式
        if($excel2007){
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $filename = $filename.'.xlsx';
        }else{
            $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
            $filename = $filename.'.xls';
        }
        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($header_arr as $key => $val){
            $objActSheet->setCellValue($key.'1',$val);
        }
        $array_keys_s    =   array_keys($header_arr);
        foreach ($data as $row) {
            $row['task_submit_time']    =   date('Y-m-d H:i:s',$row['task_submit_time']);
            $row['task_start_time']    =   date('Y-m-d H:i:s',$row['task_start_time']);
            $row['task_end_time']    =   date('Y-m-d H:i:s',$row['task_end_time']);
            switch ($row['task_status']){
                case 0:$row['task_status']='待办';break;
                case 1:$row['task_status']='执行中';break;
                case 2:$row['task_status']='审核';break;
                case 3:$row['task_status']='完成';break;
                case 4:$row['task_status']='逾期';break;
                case 10:$row['task_status']='关闭';break;
            }
            foreach ($indexKey as $key => $value){
                //这里是设置单元格的内容
                $objActSheet->setCellValueExplicit($array_keys_s[$key].$startRow,$row[$value],\PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $startRow++;
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename='.$filename.'');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

    public static function ismobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if (isset($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT']) {
            return true;
        }
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA']))
            //找不到为flase,否则为true
        {
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        }
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile',
            );
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    public static function importGrade($filepath,$uid){
        $PHPExcel = self::getExcelObj($filepath); // 载入excel文件
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        //echo $highestRow;die;
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $info='';
        /** 循环读取每个单元格的数据 */
        //Db::startTrans();

        try{
            $data = self::yieldData($highestRow, $sheet);
            //开始使用
            foreach ($data as $k => $v) {
                $where  =   [
                    'code'=>$v['code'],
                    'month1_name'=>$v['month1_name'],
                    'month2_name'=>$v['month2_name'],
                    'year'=>$v['year']
                ];
                $v['filename']  =   $filepath;
                if(Db::table('task_grade_point')->where($where)->value('id') >0){
                    Db::table('task_grade_point')->where($where)->update($v);
                }else{
                    Db::table('task_grade_point')->insert($v);
                }
                unset($where);
            }
           /* exit;
            for ($row = 2; $row <= $highestRow; $row++){//行数是以第1行开始
                if(!$sheet->getCell("A".$row)->getValue()){
                    continue;
                }
                //$data["tid"] = $sheet->getCell("A".$row)->getValue();
                $data["primary_category"] = $sheet->getCell("A".$row)->getValue();//姓名
                $data["secondary_category"] = $sheet->getCell("B".$row)->getValue();//员工号
                $data['tertiary_category']  =   $sheet->getCell("C".$row)->getValue();//登录账号
                $data['format']  =   $sheet->getCell("D".$row)->getValue();//密码
                //var_dump($data);die;
                if(Db::table('source_format')->where($data)->value('id') >0){
                    continue;
                }
                Db::table('source_format')->insert($data);
            }*/
        }catch (\think\Exception $e){
            //Db::rollback();
            echo $e->getMessage();
            //echo $e->getMessage();
        }
        return $info;
    }

    public static function yieldData($highestRow, $worksheet)
    {
        for ($row = 2; $row <= $highestRow; ++$row) {
            $data[$row]['code'] = $worksheet->getCell("A".$row)->getValue();
            $data[$row]['name'] = $worksheet->getCell("B".$row)->getValue();
            $data[$row]['port'] = $worksheet->getCell("C".$row)->getValue();
            $data[$row]['division'] = $worksheet->getCell("D".$row)->getValue();
            $data[$row]['center'] = $worksheet->getCell("E".$row)->getValue();
            $data[$row]['depart'] = $worksheet->getCell("F".$row)->getValue();
            $data[$row]['group'] = $worksheet->getCell("G".$row)->getValue();
            $data[$row]['job'] = $worksheet->getCell("H".$row)->getValue();
            $data[$row]['average'] = $worksheet->getCell("K".$row)->getValue();
            $data[$row]['rank'] = $worksheet->getCell("L".$row)->getValue();
            $data[$row]['grade'] = $worksheet->getCell("M".$row)->getValue();
            $data[$row]['format'] = $worksheet->getCell("N".$row)->getValue();

            $data[$row]['month1_name'] = $worksheet->getCell("I1")->getValue();
            $data[$row]['month1_points'] = $worksheet->getCell("I".$row)->getValue();
            $data[$row]['month2_name'] = $worksheet->getCell("J1")->getValue();
            $data[$row]['month2_points'] = $worksheet->getCell("J".$row)->getValue();
            $data[$row]['year'] = $worksheet->getCell("O".$row)->getValue();
            yield $data[$row];
        }
    }

    public static function importVoucher($filepath,$uid){
        $PHPExcel = self::getExcelObj($filepath); // 载入excel文件
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $info='';
        /** 循环读取每个单元格的数据 */
        //Db::startTrans();
        $cou = 0;
        $fail = '';
        try{
            $data = self::yieldVoucherData($highestRow, $sheet);
            //开始使用
            foreach ($data as $k => $v) {
                $where  =   [
                    'code'=>$v['code'],
                ];
                $data   =   [
                    'code'=>$v['code'],
                    'name'=>$v['name'],
                    'count'=>$v['count'],
                    'end_time'=>$v['end_time'],
                    'year'=>$v['year'],
                ];

                if(Db::table('task_voucher')->where($where)->value('vid')){
                    if(Db::table('task_voucher')->where($where)->update($data)){
                        $cou++;
                    }else{
                        $fail .= ' '.$data['name'].'';
                    }
                }else{
                    if(Db::table('task_voucher')->insert($data)){
                        $cou++;
                    }else{
                        $fail .= ' '.$data['name'].' ';
                    }
                }
                unset($where);
                //unset($data);
            }
        }catch (\think\Exception $e){
            //Db::rollback();
            echo $e->getMessage();
            //echo $e->getMessage();
        }
        return "上传成功".$cou."条"." 失败 ：".$fail;
    }

    public static function yieldVoucherData($highestRow, $worksheet)
    {
        for ($row = 2; $row <= $highestRow; ++$row) {
            $data[$row]['code'] = $worksheet->getCell("A".$row)->getValue();
            $data[$row]['name'] = $worksheet->getCell("B".$row)->getValue();
            $data[$row]['count'] = $worksheet->getCell("C".$row)->getValue();
            $data[$row]['end_time'] = $worksheet->getCell("D".$row)->getValue();
            $data[$row]['year'] = $worksheet->getCell("E".$row)->getValue();
            yield $data[$row];
        }

    }

    public static function importRank($filepath,$uid){
        $PHPExcel = self::getExcelObj($filepath); // 载入excel文件
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $info='';
        /** 循环读取每个单元格的数据 */
        //Db::startTrans();
        $cou = 0;
        $fail = '';
        try{
            $data = self::yieldRankrData($highestRow, $sheet);
            //开始使用
            foreach ($data as $k => $v) {
                $where  =   [
                    'code'=>$v['code'],
                ];


                if(Db::table('task_rank')->where($where)->value('id')){
                    if(Db::table('task_rank')->where($where)->update($v)){
                        $cou++;
                    }else{
                        $fail .= ' '.$v['name'].'';
                    }
                }else{
                    if(Db::table('task_rank')->insert($v)){
                        $cou++;
                    }else{
                        $fail .= ' '.$v['name'].' ';
                    }
                }
                unset($where);
                //unset($data);
            }
        }catch (\think\Exception $e){
            //Db::rollback();
            echo $e->getMessage();
            //echo $e->getMessage();
        }
        return "上传成功".$cou."条"." 失败 ：".$fail;
    }

    public static function yieldRankrData($highestRow, $worksheet)
    {
        for ($row = 2; $row <= $highestRow; ++$row) {
            $data[$row]['code'] = $worksheet->getCell("A".$row)->getValue();
            $data[$row]['name'] = $worksheet->getCell("B".$row)->getValue();
            $data[$row]['month_start_score'] = $worksheet->getCell("C".$row)->getValue();
            $data[$row]['rank'] = $worksheet->getCell("D".$row)->getValue();
            $data[$row]['actual_profit'] = $worksheet->getCell("E".$row)->getValue();
            $data[$row]['actual_score'] = $worksheet->getCell("F".$row)->getValue();
            $data[$row]['base_score'] = $worksheet->getCell("G".$row)->getValue();
            $data[$row]['total_score'] = $worksheet->getCell("H".$row)->getValue();
            $data[$row]['standard'] = $worksheet->getCell("I".$row)->getValue();
            $data[$row]['estimate_score'] = $worksheet->getCell("J".$row)->getValue();
            $data[$row]['estimate_lost_class'] = $worksheet->getCell("K".$row)->getValue();
            $data[$row]['estimate_promotion_gap'] = $worksheet->getCell("L".$row)->getValue();
            $data[$row]['estimate_relegation_gap'] = $worksheet->getCell("M".$row)->getValue();
            $data[$row]['early_warning'] = $worksheet->getCell("N".$row)->getValue();
            $data[$row]['end_time'] = $worksheet->getCell("O".$row)->getValue();
            yield $data[$row];
        }
    }
}