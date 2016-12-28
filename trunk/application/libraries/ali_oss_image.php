<?php
require_once 'Image.php';
require_once 'oss/OssClient.php';

Class Ali_oss_image extends Image{

    public $image_service;

    private $config = null;
    private $bucket = null;

    public function __construct($config){
        if(
            !isset($config['bucket']) || empty($config['bucket']) ||
            !isset($config['accessKeyId']) || empty($config['accessKeyId']) ||
            !isset($config['accessKeySecret']) || empty($config['accessKeySecret']) ||
            !isset($config['endpoint']) || empty($config['endpoint']) ||
            !isset($config['isCName']) || empty($config['isCName']) ||
            !isset($config['securityToken']) || empty($config['securityToken'])
        ){
            return_array(-1,'参数错误');
        }
        
        $this->image_service = new OssClient($config['accessKeyId'],$config['accessKeySecret'],$config['endpoint'],$config['isCName'],$config['securityToken']);
        // //判断bucket是否存在，否则创建
        // if(!$this->image_service->doesBucketExist($config['bucket'])){
        //     $create_bucket_res = $this->image_service->createBucket($config['bucket']);
        //     if(!$this->check_respose($create_bucket_res)){
        //         return_array(ERROR_CREATE_BUCKET,ERROR_CREATE_BUCKET_MSG);
        //     }
        // }
        $this->config = $config;
        $this->bucket = $config['bucket'];
    }


    /**
     * 判断目录存不存在
     * @author eason
     */
    public function check_upload_path($upload_path = ''){
        if(empty($upload_path)){
            return_array(ERROR_EMPTY_UPLOAD_PATH,ERROR_EMPTY_UPLOAD_PATH_MSG);
        }
        if(strpos($upload_path,'.') > 0 || strpos($upload_path,'..') > 0){
        return_array(ERROR_FORMAT_UPLOAD_PATH,ERROR_FORMAT_UPLOAD_PATH_MSG);
        }

        $upload_path = str_replace('\\','/',$upload_path);

        $check_object_res = $this->image_service->doesObjectExist($this->bucket,$upload_path);
        if(!$this->check_respose($check_object_res)){
            return_array(ERROR_UPLOAD_PATH,ERROR_UPLOAD_PATH_MSG);
        }

        return_array(SUCCESS,'',array('upload_path' => $upload_path));
    }

    /**
     * 判断目录存不存在，不存在则创建
     * @author eason
     */
    public function check_and_create_upload_path($upload_path = ''){
        $check_object_res = $this->check_upload_path($upload_path);
        if($check_arr['code'] != 0){
            return_array($check_arr['code'],$check_arr['msg']);
        }
        
        $create_res = $this->image_service->doesObjectExist($this->bucket,$upload_path);
        if(!$this->check_respose($create_res)){
            return_array(ERROR_CREATE_UPLOAD_PATH,ERROR_CREATE_UPLOAD_PATH_MSG);
        }
        return_array(SUCCESS);
    }

    /**
     * 普通图片上传
     * @author eason
     */
    public function ordinary_upload($image_upload_path = '',$image_data = ''){
        if(empty($image_upload_path) || empty($image_data)){
            return_array(ERROR_PARAMS,ERROR_PARAMS_MSG);
        }

        //分割图片路径，获取 上传的目录，图片名称，图片扩展名
        $image_upload_path = str_replace('\\','/',$a);

        $upload_path = substr($a,0,strrpos($image_upload_path,'/'));
        $image_name = array_pop(explode('/',$image_upload_path));
        $image_extension = array_pop(explode($image_name));

        //检验路径，并以返回的上传目录为基准
        $check_object_res = $this->check_upload_path($upload_path);
        if($check_arr['code'] != 0){
            return_array($check_arr['code'],$check_arr['msg']);
        }
        $upload_path = $check_arr['data']['upload_path'];

        //检验扩展名
        if(!in_array($image_extension,IAMGE_EXTENSION_TYPE)){
            return_array(ERROR_IAMGE_EXTENSION_TYPE,ERROR_IAMGE_EXTENSION_TYPE_MSG);
        }

        //重置图片路径
        $image_upload_path = $upload_path.'/'.$image_name;

        //组装数据
        $resopnse = $this->image_service->putObject($this->bucket,$image_upload_path,$imgdata);

        if(!$this->check_respose($response)){
            return_array(ERROR_UPLOAD,ERROR_UPLOAD_MSG);
        }
        return_array(SUCCESS,'');
    }
    

    /**
     * 检查响应
     * @author eason
     */
    public function check_respose($response){
        if(!isset($response->status) || !($response->status === 200)){
            return true;
        }
        return false;
    }
    
}

?>