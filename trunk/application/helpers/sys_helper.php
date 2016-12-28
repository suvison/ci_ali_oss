<?php
/**
 * 打印数组函数
 * @param $arr
 */
if (!function_exists('p')) {
    function p($arr) {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
}

/**
 * 数组返回
 * @param $arr
 */
if (!function_exists('return_array')) {
    function return_array($code = '',$msg = '',$data = '') {
        if($code == ''){
            return array('code' => 404,'msg' => '非法错误码','data' => null);
        }
        return array('code' => $code,'msg' => $msg,'data' => $data);
    }
}

/**
 * json返回
 * @param $arr
 */
if (!function_exists('exit_json')) {
    function exit_json($code = '',$msg = '',$data = '') {
        $res = null;
        if($code == ''){
            $res = array('code' => 404,'msg' => '非法错误码','data' => null);
        }
        $res = array('code' => $code,'msg' => $msg,'data' => $data);
        exit(json_encode($res));
    }
}