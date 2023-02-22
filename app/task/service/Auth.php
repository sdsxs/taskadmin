<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/2/10
 * Time: 17:30
 */

namespace app\task\service;


use traits\controller\Jump;

class Auth
{
    use Jump;
    /**
     * @param array $array
     * @param array $condtion
     * @return bool|string
     */

    public static function getmudel(){
        return new  Auth();
    }
    public  function fieldAuth(array $array,array  $condtion){
        if(is_array($array)){
            foreach ($condtion as $key => $val){
                if(isset($array["$key"])){
                        if(trim($array["$key"]) == ''){
                            return $this->error($condtion["$key"]);die;
                        }
                }
            }
            return true;
        }else{
            return $this->error("error auth para!");die;
        }
    }
}