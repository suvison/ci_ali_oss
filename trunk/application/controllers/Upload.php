<?php
        
header('Access-Control-Allow-Origin:*');

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

    private $post = null;

    public function __construct(){
        parent::__construct();
        header('Content-type: application/json');
        date_default_timezone_set('PRC');
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

        if(empty($this->input->post())){
            exit_json(-3,'只能post访问');
        }
        $this->post = $this->input->post();

        // header('Access-Control-Allow-Origin:*');
        // 
        // $this->load->library('image_factory_lib');
    }

    /**
     * 域名控制
     * @author eason
     */
    private function _acl(){
        if(empty($_SERVER['HTTP_ORIGIN'])){
            return return_array(101,'域名列表不存在');
        }

        //去除http头
        $http_origin = str_replace('http://','',$_SERVER['HTTP_ORIGIN']);

        //不允许ip地址通过
        if(filter_var($_SERVER['HTTP_ORIGIN'], FILTER_VALIDATE_IP)) {
            return return_array(102,'不允许ip来源');
        }
        
        $http_origin_arr = explode('.',$http_origin);

        
        //判断域名是否允许通过
        if(!isset($this->domain_filter[$http_origin])){
            if(count($http_origin_arr) <= 2){
                return return_array(103,'域名来源未注册');
            }
            //如果是3级域名
            $http_origin_arr[0] = '*';
            if(!isset($this->domain_filter[implode('.',$http_origin_arr)])){
                return return_array(104,'域名来源未注册');
            }
        }
        //通过域名键值获取用户组和客户端ip限制
        $domain_filter_value = $this->domain_filter[implode('.',$http_origin_arr)];

        $acl_arr = explode('@',$domain_filter_value);        
        if(count($acl_arr) !== 2){
            return return_array(102,'域名列表不存在');
        }

        $this->group_name = $acl_arr[0];
        $allow_ip_reg = $acl_arr[1];

        //验证来源的ip
        $patten = '/'.str_replace('*','.*',str_replace('.','\.',$allow_ip_reg)).'/';
        if(empty(preg_match($patten,$_SERVER['REMOTE_ADDR']))){
            return return_array(103,'域名来源未注册');
        }
        return return_array(0);
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


    public function ordinary_upload(){
        if(count($_FILES) > 0){
            $file_info = array_shift($_FILES);
        }else{
            $file_info = $_FILES;
        }

        if($file_info['error'] != 0){
            exit_json(121,'图片上传出错');
        }
        $extension_arr =  explode('.',$file_info['name']);
        //构造数据
        $image_info = array(
            'upload_path' => $this->upload_path_base.'/'.$this->group_name.'/'.$this->get_rand_dir(),
            'image_name' => false,//如果为false，由后面的类自定义生成
            'image_extension' => array_pop($extension_arr),
            'image_data' => file_get_contents($file_info['tmp_name']),
        );

        $this->image_info = $image_info;
        $this->init_image_factory(1);
        $res = $this->image_factory_lib->check_and_create_upload_path($image_info['upload_path']);
        if($res['code'] != 0){//创建目录失败
            exit_json($res);
        }

        //上传图片
        $upload_res = $this->image_factory_lib->ordinary_upload();
        if($upload_res['code'] != 0){
            exit_json($upload_res);
        }
        exit_json(0,'上传成功',$upload_res['data']);

    }

    public function data_upload(){
        if(count($this->post) > 0){
            $base64_image_content = array_shift($this->post);
        }else{
            $base64_image_content = $this->post;
        }

        if(empty(is_string($base64_image_content))){
            exit_json(131,'图片格式错误');
        }

        //匹配 data:image/png;base64,
        if(!preg_match('/^(data:image\/(\w+);base64,)/', $base64_image_content, $image_match_arr)){
            exit_json(132,'图片格式错误');
        }
        $image_extension = $image_match_arr[2];
        $image_data = base64_decode(str_replace($image_match_arr[1], '', $base64_image_content));


        //构造数据
        $image_info = array(
            'upload_path' => $this->upload_path_base.'/'.$this->group_name.'/'.$this->get_rand_dir(),
            'image_name' => false,//如果为false，由后面的类自定义生成
            'image_extension' => $image_extension,
            'image_data' => $image_data,
        );

        $this->image_info = $image_info;
        $this->init_image_factory(1);
        $res = $this->image_factory_lib->check_and_create_upload_path($image_info['upload_path']);
        if($res['code'] != 0){//创建目录失败
            exit_json($res);
        }

        //上传图片
        $upload_res = $this->image_factory_lib->ordinary_upload();
        if($upload_res['code'] != 0){
            exit_json($upload_res);
        }
        exit_json(0,'上传成功',$upload_res['data']);
    }

    public function weixin_upload(){
        if(
            !isset($this->post['access_token']) || !is_string($this->post['access_token']) || 
            !isset($this->post['media_id']) || !is_string($this->post['media_id'])
        ){
            exit_json(131,'参数错误，请携带参数 access_token，media_id');
        }

        $access_token = $this->post['access_token'];
        $media_id = $this->post['media_id'];


         //获取微信服务器上的图片地址
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$media_id;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);

        //正则匹配
        $patten = '/filename\s*=\s*\"([^\"]*)[\s\S]*Content-Length\s*\:\s*(\d*)\s*([\s\S]*)/';
        preg_match($patten,$response,$image_res);
        if(
            empty($image_res[1]) ||
            empty($image_res[2]) ||
            empty($image_res[3])
        ){
            exit_json(132,'获取图片失败');
        }

        // $image_data = file_get_contents($url);  
        // $image_extension = image_type_to_extension(mime_content_type($url));
        // if(empty($image_data)){
        //     exit_json(132,'图片数据为空');
        // } 
        
        $image_name = $image_res[1];
        $image_size = $image_res[2]; 
        $image_data = $image_res[3]; 
        p($image_name);
        exit();
        $image_extension = array_pop(explode('.',$image_name));

        //构造数据
        $image_info = array(
            'upload_path' => $this->upload_path_base.'/'.$this->group_name.'/'.$this->get_rand_dir(),
            'image_name' => false,//如果为false，由后面的类自定义生成
            'image_extension' => $image_extension,
            'image_data' => $image_data,
        );

        $this->image_info = $image_info;
        $this->init_image_factory(1);
        $res = $this->image_factory_lib->check_and_create_upload_path($image_info['upload_path']);
        if($res['code'] != 0){//创建目录失败
            exit_json($res);
        }

        //上传图片
        $upload_res = $this->image_factory_lib->ordinary_upload();
        if($upload_res['code'] != 0){
            exit_json($upload_res);
        }
        exit_json(0,'上传成功',$upload_res['data']);
    }


    public function get_rand_dir(){
        return date('Ymd',time());
    }
}
?>
