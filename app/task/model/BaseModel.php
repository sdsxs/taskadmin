<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/2/10
 * Time: 17:26
 */

namespace app\task\model;

use think\Model;

class BaseModel  extends Model{

    static private $instance;

     static public function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}