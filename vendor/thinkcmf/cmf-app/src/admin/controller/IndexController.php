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

use cmf\controller\AdminBaseController;
use think\Exception;
use think\facade\Db;
use app\admin\model\AdminMenuModel;

class IndexController extends AdminBaseController
{

    public function initialize()
    {
        $adminSettings = cmf_get_option('admin_settings');
        if (empty($adminSettings['admin_password']) || $this->request->path() == $adminSettings['admin_password']) {
            $adminId = cmf_get_current_admin_id();
            if (empty($adminId)) {
                session("__LOGIN_BY_CMF_ADMIN_PW__", 1);//设置后台登录加密码
            }
        }

        parent::initialize();
    }

    /**
     * 跨业态资源系统
     */

    public function resource(){
        //echo session('ADMIN_ID');
        if($this->request->isPost()){
            $data   =   $this->request->param();
            if(!$data['transfer_format']){
                return $this->error('请选择转介绍人业态！');
            }
            if(!$data['accept_code']){
                return $this->error('请选择转接收人！');
            }
            if(!$data['accept_format']){
                return $this->error('请选择接收人业态！');
            }
            if(!$data['opportunity_number']){
                return $this->error('请输入商机编号！');
            }
            if(!$data['customer_name']){
                return $this->error('请输入客户名称！');
            }
            if(!$data['customer_demand']){
                return $this->error('请输入客户需求！');
            }
            try{
                if( Db::table('task_source')->where(['opportunity_number'=>$data['opportunity_number']])->count()>0)
                {
                    $RE =   Db::table('task_source')->
                    where("opportunity_number='".$data['opportunity_number']."'")
                        ->update(['status'=>$data['status'],'amount'=>$data['amount']]);
                }else{
                    $data['number'] =   session('name');
                    $RE =   Db::table('task_source')->insert($data);
                }
            }catch (Exception $e){
                return $this->error('提交数据格式有误，*号为必填项！');
            }
            return $this->success('添加信息成功！');
          }
        $format =    Db::table('task_source_format')->field('format')
            ->group('format')->order('format asc')->select()->toArray();
       /* $company    =   Db::table('task_wxdepartment')->where('name like "%分公司"')->order('name desc')->select()->toArray();
        $division   =   Db::table('task_wxdepartment')->where('name like "%事业部"')->order('name desc')->select()->toArray();
        $format   =   Db::table('task_wxdepartment')->where('name like "%事业群"')->order('name desc')->select()->toArray();*/
        return  $this->assign('format',$format)->fetch();

    }

    public function getNextCategory(){
        $words  =   addslashes($this->request->param('words'));
        $nowCategory   =    $this->request->param('nowCategory');
        if($words   &&  $nowCategory){
            switch ($nowCategory){
                case 'format':$nextCategory='primary_category';break;
                case 'primary_category':$nextCategory='secondary_category';break;
                case 'secondary_category':$nextCategory='tertiary_category';break;
                default:$nextCategory='';break;
            }
            $data =    Db::table('task_source_format')
                ->where("$nowCategory='$words'")
                ->group("$nextCategory")->order("$nextCategory asc")->select()->toArray();
            return  $this->result($data);
        }
        return  $this->result([]);
    }

    /**我的转介绍
     * @return mixed
     */
    public function myTransfer(){
        $code   =   session('name');
        $data   =   Db::table('task_source')
            ->alias('a')
            ->field('a.id,a.status,a.opportunity_number,a.accept_code,b.format as accept_format,c.user_nickname as accept_name')
            ->join('source_format b','a.accept_format=b.id','left')
            ->join('task_user c','a.accept_code=c.code','left')
            ->where(['a.number'=>$code])->paginate(10);
        return  $this->assign('data',$data)->fetch();
    }

    /**我的资源
     * @return mixed
     */
    public function mySourceList(){
        $code   =   session('name');
        $data   =   Db::table('task_source')
            ->alias('a')
            ->field('a.id,a.status,a.opportunity_number,a.transfer_code,b.format as transfer_format,c.user_nickname as transfer_name')
            ->join('task_source_format b','a.transfer_format=b.id','left')
            ->join('task_user c','a.number=c.code','left')
            ->where(['a.accept_code'=>$code])->paginate(10);
        return  $this->assign('data',$data)->fetch();
    }

    /**我的资源状态变更
     * @return mixed
     */
    public function changStatus(){
        $code   =   session('name');
        $id =   $this->request->param('id');
        $status =   $this->request->param('status');
        if($id){
            $old_status    =    Db::table('task_source')->where(['id'=>$id])->value('status');
            $re    =   Db::table('task_source')->where(['id'=>$id])->update(['status'=>$status]);
            $this->addSourceLog($code,$id,'修改了状态'.$old_status.'->'.$status);
            if($re){
                return  $this->success('修改状态成功！');
            }
            return  $this->success('修改状态失败！');
        }
    }

    private function addSourceLog($code,$sourceid,$content){
       return Db::table('task_source_log')->insert(['code'=>$code,'sourceid'=>$sourceid,'content'=>$content]);
    }
    /**
     * 后台首页
     */
    public function index()
    {
        $content = hook_one('admin_index_index_view');

        if (!empty($content)) {
            return $content;
        }

        $adminMenuModel = new AdminMenuModel();
        $menus          = cache('admin_menus_' . cmf_get_current_admin_id(), '', null, 'admin_menus');

        if (empty($menus)) {
            $menus = $adminMenuModel->menuTree();
            cache('admin_menus_' . cmf_get_current_admin_id(), $menus, null, 'admin_menus');
        }

        $this->assign("menus", $menus);


        $result = AdminMenuModel::order(["app" => "ASC", "controller" => "ASC", "action" => "ASC"])->select();
        $menusTmp = array();
        foreach ($result as $item){
            //去掉/ _ 全部小写。作为索引。
            $indexTmp = $item['app'].$item['controller'].$item['action'];
            $indexTmp = preg_replace("/[\\/|_]/","",$indexTmp);
            $indexTmp = strtolower($indexTmp);
            $menusTmp[$indexTmp] = $item;
        }
        $this->assign("menus_js_var",json_encode($menusTmp));

        //$admin = Db::name("user")->where('id', cmf_get_current_admin_id())->find();
        //$this->assign('admin', $admin);
        return $this->fetch();
    }
}
