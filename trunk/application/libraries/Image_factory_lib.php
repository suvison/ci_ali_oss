<?php
require_once 'Ali_oss_image.php';
Class Image_factory_lib{

    protected $image_info;//图片信息

    public $image_server;//

    private $type = array( 
        1 => 'ali_oss_image'
    );
    /**
     * @author eason
     * @param type 类型
     * @param image_info array('image_name','extension','upload_path','image_data')
     */
    public function __construct($image_info = null){
        if(empty($image_info)){
            return_array(1,'文件资源错误');
        }
        if(
            !isset($image_info['upload_path']) || empty($image_info['upload_path']) ||
            !isset($image_info['image_name']) || 
            !isset($image_info['image_extension']) || empty($image_info['image_extension']) ||
            !isset($image_info['image_data']) || empty($image_info['image_data'])
        ){
            exit_json(2,'参数错误');
        }
        if(empty($image_info['image_name'])){
            $image_info['image_name'] = $this->generation_image_name();
        }

        $this->image_info = $image_info;
    }

    /**
     * 初始化
     * @author eason
     */
    public function init($type = 1,$config = null){
        if(!array_key_exists($type,$this->type)){
            return_array(2,'对象类型不存在');
        }
        switch($type){
            case 1:
               $this->image_server = new Ali_oss_image($config);
               break;
        }
    }

    /**
     * 检测上传路径
     * @author eason
     */
    public function check_upload_path($upload_path = ''){
        return $this->image_server->check_upload_path($upload_path);
    }

    /**
     * 判断目录存不存在，不存在则创建
     * @author eason
     */
    public function check_and_create_upload_path($upload_path = ''){
        return $this->image_server->check_and_create_upload_path($upload_path);
    } 

    /**
     * 普通图片上传
     * @author eason
     */
    public function ordinary_upload(){
        return $this->image_server->ordinary_upload($this->image_info['upload_path'],$this->image_info['image_name'],$this->image_info['image_extension'],$this->image_info['image_data']);
    }

    /**
     * 生成图片名称
     * @author eason
     */
    public function generation_image_name(){
        $date = date('YmdHis',time());
        $rand = rand(1000,9999);
        return $date.$rand;
    }
}

?>
