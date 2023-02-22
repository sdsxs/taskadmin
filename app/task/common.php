<?php

    /**返回任务状态
     * @param $num
     * @return mixed|string
     */
    function getStatus($num){
        //if(!$num) return '';
        $array  =   [
            '0' =>'待办',
            '1' =>'进行',
            '2' =>'审核',
            '3' =>'完成',
            '4' =>'逾期',
            '5' =>'驳回',
            '10' =>'关闭',
        ];
        return $array[$num];
    }

    /**
     * 过滤空和null的数组
     */

    function filter($val){
        return ($val === '' || $val === null) ? false : true;
    }
?>