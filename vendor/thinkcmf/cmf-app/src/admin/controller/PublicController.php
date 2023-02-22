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

use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;
use app\task\service\TaskService;
use think\facade\Cache;

class PublicController extends AdminBaseController
{
    public function initialize()
    {
    }
    /**
     * 企业微信登陆界面
     */
    public function wxlogin()
    {
        if(isset($_GET['code'])){
            if(cache::get("wx_token")){
                $access_token = cache::get("wx_token");
            }else{
                $access_token = TaskService::gettoken();
                cache::set("wx_token",$access_token,7200);
            }
            $yg_code =  TaskService::getuserid($access_token,$_GET['code']);//员工的员工号
            /*$userinfo   =   TaskService::getUserInfo($access_token,$yg_code);
            //var_dump($userinfo);die;
            $depart =   TaskService::getWxDepartmentList($access_token);
            echo "<table border='1'>";
            echo "<tr>><td>order</td><td>父id</td><td>名称</td><td>ID</td></tr>";
            foreach ($depart['department'] as $val){
                echo "<tr><td>".$val['order']."</td><td>".$val['parentid']."</td><td>".$val['name']."</td><td>".$val['id']."</td></tr>";
            }
            echo "</table>";
            die;*/
            if($yg_code){
                $where['user_login'] = $yg_code;
                $result = UserModel::where($where)->find();
                if (!empty($result) && $result['user_type'] == 1) {
                        $groups = RoleUserModel::alias("a")
                            ->join('role b', 'a.role_id =b.id')
                            ->where(["user_id" => $result["id"], "status" => 1])
                            ->value("role_id");
                        if ($result["id"] != 1 && (empty($groups) || empty($result['user_status']))) {
                            $this->error(lang('USE_DISABLED'));
                        }
                        //登入成功页面跳转
                        session('ADMIN_ID', $result["id"]);
                        session('name', $result["user_login"]);
                        $data                    = [];
                        $data['last_login_ip']   = get_client_ip(0, true);
                        $data['last_login_time'] = time();
                        $token                   = cmf_generate_user_token($result["id"], 'web');
                        if (!empty($token)) {
                            session('token', $token);
                        }
                        UserModel::where('id', $result['id'])->update($data);
                        cookie("admin_username", $result['user_login'], 3600 * 24 * 30);
                        session("__LOGIN_BY_CMF_ADMIN_PW__", null);
                        /*if(TaskService::ismobile()){
                            $this->redirect(url("task/grade/index"));
                            echo "暂时不支持手机端";exit;
                        }*/
                        $this->redirect(url("admin/Index/index"));
                        //$this->success(lang('LOGIN_SUCCESS'), url("admin/Index/index"));
                } else {
                    $this->error(lang('USERNAME_NOT_EXIST'));
                }
            }
            //setcookie("code", $username, time() + 3600);
        }
        $config =   TaskService::getConfig();
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$config['CorpID']."&redirect_uri=" . urlEncode($_SERVER['HTTP_HOST']."/admin/public/wxlogin.html") . "&response_type=code&scope=snsapi_base&agentid=".$config['AgentId']."&state=STATE&#wechat_redirect";
        header("location:".$url);die;
        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            //$this->error('非法登录!', cmf_get_root() . '/');
            return redirect(cmf_get_root() . "/");
        }

        $admin_id = session('ADMIN_ID');
        if (!empty($admin_id)) {//已经登录
            return redirect(url("admin/Index/index"));
        } else {
            session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__", true);
            $result = hook_one('admin_login');
            if (!empty($result)) {
                return $result;
            }
            return $this->fetch(":login");
        }
    }

    /**
     * 跨业态资源系统
     */
    public function resource()
    {
        if(isset($_GET['code'])){
            if(cache::get("wx_token")){
                $access_token = cache::get("wx_token");
            }else{
                $access_token = TaskService::gettoken();
                cache::set("wx_token",$access_token,7200);
            }
            $yg_code =  TaskService::getuserid($access_token,$_GET['code']);//员工的员工号
            /*$userinfo   =   TaskService::getUserInfo($access_token,$yg_code);
            //var_dump($userinfo);die;
            $depart =   TaskService::getWxDepartmentList($access_token);
            echo "<table border='1'>";
            echo "<tr>><td>order</td><td>父id</td><td>名称</td><td>ID</td></tr>";
            foreach ($depart['department'] as $val){
                echo "<tr><td>".$val['order']."</td><td>".$val['parentid']."</td><td>".$val['name']."</td><td>".$val['id']."</td></tr>";
            }
            echo "</table>";
            die;*/
            if($yg_code){
                $where['user_login'] = $yg_code;
                $result = UserModel::where($where)->find();
                if (!empty($result) && $result['user_type'] == 1) {
                   /* $groups = RoleUserModel::alias("a")
                        ->join('role b', 'a.role_id =b.id')
                        ->where(["user_id" => $result["id"], "status" => 1])
                        ->value("role_id");
                    if ($result["id"] != 1 && (empty($groups) || empty($result['user_status']))) {
                        $this->error(lang('USE_DISABLED'));
                    }*/
                    //登入成功页面跳转
                    session('ADMIN_ID', $result["id"]);
                    session('name', $result["user_login"]);
                    $data                    = [];
                    $data['last_login_ip']   = get_client_ip(0, true);
                    $data['last_login_time'] = time();
                    $token                   = cmf_generate_user_token($result["id"], 'web');
                    if (!empty($token)) {
                        session('token', $token);
                    }
                    UserModel::where('id', $result['id'])->update($data);
                    cookie("admin_username", $result['user_login'], 3600 * 24 * 30);
                    session("__LOGIN_BY_CMF_ADMIN_PW__", null);
                    $this->redirect(url("admin/Index/resource"));
                    //$this->success(lang('LOGIN_SUCCESS'), url("admin/Index/index"));
                } else {
                    $this->error(lang('USERNAME_NOT_EXIST'));
                }
            }
            //setcookie("code", $username, time() + 3600);
        }
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=ww2f6738b92666c63a&redirect_uri=" . urlEncode("http://task.wzanli.com/admin/public/resource.html") . "&response_type=code&scope=snsapi_base&agentid=1000073&state=STATE&#wechat_redirect";
        header("location:".$url);die;
        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            //$this->error('非法登录!', cmf_get_root() . '/');
            return redirect(cmf_get_root() . "/");
        }

        $admin_id = session('ADMIN_ID');
        if (!empty($admin_id)) {//已经登录
            return redirect(url("admin/Index/index"));
        } else {
            session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__", true);
            $result = hook_one('admin_login');
            if (!empty($result)) {
                return $result;
            }
            return $this->fetch(":login");
        }
    }


    /**
     * 后台登陆界面
     */
    public function login()
    {
        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            //$this->error('非法登录!', cmf_get_root() . '/');
            return redirect(cmf_get_root() . "/");
        }

        $admin_id = session('ADMIN_ID');
        if (!empty($admin_id)) {//已经登录
            return redirect(url("admin/Index/index"));
        } else {
            session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__", true);
            $result = hook_one('admin_login');
            if (!empty($result)) {
                return $result;
            }
            return $this->fetch(":login");
        }
    }

    /**
     * 登录验证
     */
    public function doLogin()
    {
        if (!$this->request->isPost()) {
            $this->error('非法登录!');
        }
        if (hook_one('admin_custom_login_open')) {
            $this->error('您已经通过插件自定义后台登录！');
        }

        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            $this->error('非法登录!', cmf_get_root() . '/');
        }

       /* $captcha = $this->request->param('captcha');
        if (empty($captcha)) {
            $this->error(lang('CAPTCHA_REQUIRED'));
        }
        //验证码
        if (!cmf_captcha_check($captcha)) {
            $this->error(lang('CAPTCHA_NOT_RIGHT'));
        }*/

        $name = $this->request->param("username");
        if (empty($name)) {
            $this->error(lang('USERNAME_OR_EMAIL_EMPTY'));
        }
        $pass = $this->request->param("password");
        if (empty($pass)) {
            $this->error(lang('PASSWORD_REQUIRED'));
        }
        if (strpos($name, "@") > 0) {//邮箱登陆
            $where['user_email'] = $name;
        } else {
            $where['user_login'] = $name;
        }

        $result = UserModel::where($where)->find();

        if (!empty($result) && $result['user_type'] == 1) {
            if (cmf_compare_password($pass, $result['user_pass'])) {
                $groups = RoleUserModel::alias("a")
                    ->join('role b', 'a.role_id =b.id')
                    ->where(["user_id" => $result["id"], "status" => 1])
                    ->value("role_id");
                if ($result["id"] != 1 && (empty($groups) || empty($result['user_status']))) {
                    $this->error(lang('USE_DISABLED'));
                }
                //登入成功页面跳转
                session('ADMIN_ID', $result["id"]);
                session('name', $result["user_login"]);
                $data                    = [];
                $data['last_login_ip']   = get_client_ip(0, true);
                $data['last_login_time'] = time();
                $token                   = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                UserModel::where('id', $result['id'])->update($data);
                cookie("admin_username", $name, 3600 * 24 * 30);
                session("__LOGIN_BY_CMF_ADMIN_PW__", null);
                $this->success(lang('LOGIN_SUCCESS'), url("admin/Index/index"));
            } else {
                $this->error(lang('PASSWORD_NOT_RIGHT'));
            }
        } else {
            $this->error(lang('USERNAME_NOT_EXIST'));
        }
    }

    /**
     * 后台管理员退出
     */
    public function logout()
    {
        session('ADMIN_ID', null);
        return redirect(url('/', [], false, true));
    }
}