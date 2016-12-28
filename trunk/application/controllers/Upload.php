<?php
/**
 * 该接口作用
 * 1、来源过滤
 * 2、数据组装
 */
Class Upload extends CI_Controller{

    private $group_list = null;
 
    private $domain_filter = null;

    private $group_name = null;

    private $upload_path_base = 'upload/image';

    private $image_info = null;

    public function __construct(){
        parent::__construct();
        header('Content-type: application/json');
        $this->load->config('origin_filter');
        if(empty($this->config->item('group_list'))){
            exit_json(-1,'没有设置组列表');
        }
        if(empty($this->config->item('domain_filter'))){
            exit_json(-2,'没有域名访问控制');
        }

        $this->group_list = $this->config->item('group_list');
        $this->domain_filter = $this->config->item('domain_filter');

        $acl_res = $this->_acl();
        if($acl_res['code'] != 0){
            exit_json($acl_res['code'],$acl_res['msg']);
        }
        header('Access-Control-Allow-Origin:'.$_SERVER['HTTP_ORIGIN']);
        // header('Access-Control-Allow-Origin:http://dev-yungouadmin.wssoto.com');
        // 
        // $this->load->library('image_factory_lib');
    }

    /**
     * 域名控制
     * @author eason
     */
    private function _acl(){
        //判断域名是否允许通过
        var_dump(empty($this->domain_filter[$_SERVER['HTTP_ORIGIN']]));
        if(empty($this->domain_filter[$_SERVER['HTTP_ORIGIN']])){
            return_array(101,'域名来源未注册');
        }
        $domain_filter_value = $this->domain_filter[$_SERVER['HTTP_ORIGIN']];

        $acl_arr = explode('@',$domain_filter_value);        
        if(count($acl_arr) !== 2){
            return_array(102,'域名列表不存在');
        }

        $this->group_name = $acl_arr[0];
        $allow_ip_reg = $acl_arr[1];

        //验证来源的ip
        $patten = '/'.str_replace('*','.*',str_replace('.','\.',$allow_ip_reg)).'/';
        if(empty(preg_match($patten,$_SERVER['REMOTE_ADDR']))){
            return_array(103,'域名来源未注册');
        }
        return_array(0);
    }
    /**
     * 初始化image工厂，传入数据
     * @author eason
     */
    private function init_image_factory($type = 0){
        $config = null;
        //载入配置文件
        $this->load->config('oss');
        switch($type){
            case 1:
                $config = $this->config->item('ali_oss');
                break;
            case 2:
                $config = $this->config->item('baidu_oss');
                break;
            default :
                exit_json(111,'图片工厂初始化失败');
        }
        if(empty($config)){
            exit_json(112,'指定oss引擎配置为空');
        }
        $this->load->library('image_factory_lib',$this->image_info);
        $this->image_factory_lib->init($type,$config);
    }


    public function ordinary_upoad(){
        $file_info = array_pop($_FILES);

        if($file_info['error'] != 0){
            exit_json(110,'图片上传出错');
        }
        $extension_arr =  explode('.',$file_info['name']);
        //构造数据
        $image_info = array(
            'image_name' => false,//如果为false，由后面的类自定义生成
            'extension' => array_pop($extension_arr),
            'upload_path' => $this->upload_path_base.'/'.$this->group_name.'/'.date('Ymd',time()),
            'image_data' => file_get_contents($file_info['tmp_name']),
        );

        $this->image_info = $image_info;
        $this->init_image_factory(1);
        
        $res = $this->image_factory_lib->check_upload_path($image_info['upload_path']);
        p($res);
    }

    public function data_upload(){

    }

    public function weixin_upload(){

    }

}
?>
