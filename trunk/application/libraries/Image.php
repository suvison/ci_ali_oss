<?php
    Class Image{
        protected $image_type = array('jpg','jpeg','gif','bmp','png');//图片上传支持的格式

        //错误码规则， 0 为 成功，<0 为非法，>0 为普通错误
        CONST SUCCESS = 0;
        CONST ERROR_PARAMS = 1;
        CONST ERROR_PARAMS_MSG = '参数错误';
        CONST ERROR_UPLOAD = 2;
        CONST ERROR_UPLOAD_MSG = '上传失败';

        
        CONST ERROR_UPLOAD_PATH = 101;//上传目录不存在
        CONST ERROR_UPLOAD_PATH_MSG = '上传目录不存在';
        CONST ERROR_CREATE_UPLOAD_PATH = 102;//创建上传目录失败
        CONST ERROR_CREATE_UPLOAD_PATH_MSG = '创建上传目录失败';
        CONST ERROR_EMPTY_UPLOAD_PATH = 102;//上传目录不能为空
        CONST ERROR_EMPTY_UPLOAD_PATH_MSG = '上传目录不能为空';
        CONST ERROR_FORMAT_UPLOAD_PATH = 103;//上传目录格式错误
        CONST ERROR_FORMAT_UPLOAD_PATH_MSG = '上传目录格式错误';

        CONST ERROR_BUCKET = 111;//bucket不存在
        CONST ERROR_BUCKET_MSG = 'bucket不存在';
        CONST ERROR_CREATE_BUCKET = 112;//创建bucket失败
        CONST ERROR_CREATE_BUCKET_MSG = '创建bucket失败';

        CONST ERROR_IAMGE_EXTENSION_TYPE = '121';//图片格式错误
        CONST ERROR_IAMGE_EXTENSION_TYPE_MSG = '图片格式错误,图片仅支持jpg,jpeg,gif,bmp格式';


        public function __construct(){
            
        }
    }
?>