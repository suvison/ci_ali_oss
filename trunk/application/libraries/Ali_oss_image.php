<?php
require_once 'Image.php';
require_once 'oss/OssClient.php';

Class Ali_oss_image extends Image{

    protected $image_service;

    private $config = null;
    private $bucket = null;

    
    public function __construct($config = null){
        parent::__construct();
        if(
            !isset($config['bucket']) || empty($config['bucket']) ||
            !isset($config['accessKeyId']) || empty($config['accessKeyId']) ||
            !isset($config['accessKeySecret']) || empty($config['accessKeySecret']) ||
            !isset($config['endpoint']) || empty($config['endpoint']) ||
            !isset($config['isCName'])
            // !isset($config['securityToken'])
        ){
            exit_json(self::ERROR_PARAMS,self::ERROR_PARAMS_MSG);
        }
        $this->image_service = new OssClient($config['accessKeyId'],$config['accessKeySecret'],$config['endpoint'],$config['isCName'],$config['securityToken']);
        // //判断bucket是否存在，否则创建
        // // if(!$this->image_service->doesBucketExist($config['bucket'])){
        // //     $create_bucket_res = $this->image_service->createBucket($config['bucket']);
        // //     if(!$this->check_respose($create_bucket_res)){
        // //         return_array(ERROR_CREATE_BUCKET,ERROR_CREATE_BUCKET_MSG);
        // //     }
        // // }
        $this->config = $config;
        $this->bucket = $config['bucket'];
    }


    /**
     * 判断目录存不存在
     * @author eason
     */
    public function check_upload_path($upload_path = ''){
        if(empty($upload_path)){
            return return_array(self::ERROR_EMPTY_UPLOAD_PATH,self::ERROR_EMPTY_UPLOAD_PATH_MSG);
        }
        //目录不能带 . ..
        if(strpos($upload_path,'.') > 0 || strpos($upload_path,'..') > 0){
            return return_array(self::ERROR_FORMAT_UPLOAD_PATH,self::ERROR_FORMAT_UPLOAD_PATH_MSG);
        }
        //将\\替换/
        $upload_path = str_replace('\\','/',$upload_path);
        
        //去除最左边和最右边出现的 /
        $upload_path = ltrim(rtrim($upload_path,'/'),'/').'/';
        $check_object_res = $this->image_service->doesObjectExist($this->bucket,$upload_path);
        if(!$this->check_respose($check_object_res)){
            return return_array(self::ERROR_UPLOAD_PATH,self::ERROR_UPLOAD_PATH_MSG);
        }

        return return_array(self::SUCCESS,'',array('upload_path' => $upload_path));
    }

    /**
     * 判断目录存不存在，不存在则创建
     * @author eason
     */
    public function check_and_create_upload_path($upload_path = ''){
        $check_arr = $this->check_upload_path($upload_path);
        if($check_arr['code'] == 0){
            return return_array(self::SUCCESS);
        }
        
        $create_res = $this->image_service->createObjectDir($this->bucket,$upload_path);
        if(!$this->check_respose($create_res)){
            return return_array(self::ERROR_CREATE_UPLOAD_PATH,self::ERROR_CREATE_UPLOAD_PATH_MSG);
        }
        return return_array(self::SUCCESS);
    }

    /**
     * 普通图片上传
     * @author eason
     */
    public function ordinary_upload($upload_path = '',$image_name = '',$image_extension = '',$image_data = '',$image_size){
        if(
            empty($upload_path) || 
            empty($image_name) || 
            empty($image_extension) || 
            empty($image_data) ||
            empty($image_size)
        ){
            return return_array(self::ERROR_PARAMS,self::ERROR_PARAMS_MSG);
        }

        //检验路径，并以返回的上传目录为基准
        $check_object_res = $this->check_upload_path($upload_path);
        
        if($check_object_res['code'] != 0){
            return return_array($check_object_res['code'],$check_object_res['msg']);
        }
        $upload_path = $check_object_res['data']['upload_path'];

        //检验扩展名
        if(!in_array($image_extension,$this->image_type)){
            return return_array(self::ERROR_IAMGE_EXTENSION_TYPE,self::ERROR_IAMGE_EXTENSION_TYPE_MSG);
        }
        //重置图片路径
        $image_upload_path = $upload_path.$image_name.'.'.$image_extension;

        //组装数据
        $option = array();
        if($image_size > 0){
            $option['length'] = $image_size;
        }
        $response = $this->image_service->putObject($this->bucket,$image_upload_path,$image_data,$option);
        if(!$this->check_respose($response)){
            return return_array(self::ERROR_UPLOAD,self::ERROR_UPLOAD_MSG);
        }
        return return_array(self::SUCCESS,'',array('image_url' => $response->header['info']['url']));
    }
    

    /**
     * 检查响应
     * @author eason
     */
    public function check_respose($response){
        if(!isset($response->status) || !($response->status === 200)){
            return false;
        }
        return true;
    }
    
}

?>
