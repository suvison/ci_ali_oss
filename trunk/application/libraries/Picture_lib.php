<?php
/**
 * 图片处理类
 */
class Picture_lib {
	const ONE_FILE = 'file'; //单文件上传
	const MUL_FILE = 'files'; //多文件上传
	const PIC_ATTR = 'Filedata';
	const MAX_PIC_SIZE = 2097152; // 最大上传图片2M
	
	private $image_server_url;  //图片服务器地址

	const SUCCESS_CODE = 0;
	const SUCCESS_MSG = "";
	const FAIL_CODE = 1;
	const FAIL_MSG = "上传失败";
	const ERROR_POST_NOPARA_CODE = 2;
	const ERROR_POST_NOPARA_MSG = "需要在post数据中传入所需参数";
	const ERROR_POST_ERRORPARA_CODE = 3;
	const ERROR_POST_ERRORPARA_MSG = "所需参数需要特定值";
	const ERROR_PIC_NOTEXIST_CODE = 4;
	const ERROR_PIC_NOTEXIST_MSG = "上传图片不存在";
	const ERROR_PIC_TOOBIG_CODE = 5;
	const ERROR_PIC_TOOBIG_MSG = "图片太大";
	const ERROR_UPLOAD_FAIL_CODE = 6;
	const ERROR_UPLOAD_FAIL_MSG = "上传失败";
	const ERROR_SQL_ADDFAIL_CODE = 7;
	const ERROR_SQL_ADDFAIL_MSG = "数据库添加纪录失败";

    const ERROR_data_ERRORPARA_CODE = 21;
    const ERROR_data_ERRORPARA_MSG = "获取不到微信图片数据";

	private $CI;

	private $url;
	
	private $app = ''; //哪个app在上传图片
	
	private $appDirArr = array( //哪个app上传在哪个文件夹里面
		'test' => 'test',
        'ylxirang' => 'ylxirang',
	);
	
	private $oss_sdk_service;
	private $oss;

	//构造函数
	public function __construct() {
		date_default_timezone_set("PRC");
		$this->CI = & get_instance();
		
		//配置区-------------------------------------------------------------------------
		$this->CI->load->config("oss");
		$this->oss = $this->CI->config->item("oss");
		$this->image_server_url = $this->oss['image_server_url'];
		
		//ACCESS_ID
        define('OSS_ACCESS_ID', $this->oss['Access_Key_ID']);
        //ACCESS_KEY
        define('OSS_ACCESS_SECRET', $this->oss['Access_Key_Secret']);
        //BUCKET
        define('BUCKET', $this->oss['bucket']);
        //ENDPOINT
        define('ENDPOINT', $this->oss['endpoint']);
        //ISCNAME
        define('ISCNAME', $this->oss['isCName']);
        //SECURITYTOKEN
        define('SECURITYTOKEN', $this->oss['securityToken']);

		// //是否记录日志
		// define('ALI_LOG', FALSE);
		// //自定义日志路径，如果没有设置，则使用系统默认路径，在 logs
		// define('ALI_LOG_PATH',''); //此变量废弃
		// //是否显示LOG输出
		// define('ALI_DISPLAY_LOG', FALSE);
		// //语言版本设置
		// define('ALI_LANG', 'zh');
		// //----------------------------------------------------------------------------
		
		// $this->CI->load->library("oss/ALIOSS");
		// $this->CI->load->library("oss/lib/requestcore/RequestCore");
		// $this->CI->load->library("oss/util/MimeTypes");
		// $this->CI->load->library("oss/lang/".ALI_LANG);
		// $this->oss_sdk_service = new ALIOSS();
		// //设置是否打开curl调试模式
		// $this->oss_sdk_service->set_debug_mode(TRUE);
		// //设置开启三级域名，三级域名需要注意，域名不支持一些特殊符号，所以在创建bucket的时候若想使用三级域名，最好不要使用特殊字符
		// //$this->oss_sdk_service->set_enable_domain_style(TRUE);
        
        $this->CI->load->library("oss/OssClient"); 
        $this->oss_sdk_service = new OssClient();
	}

	//图片上传（包括单图片上传和多图片上传）
	public function upload($post,$type = self::ONE_FILE,$app) {
		$this->app = $app;
		if($type == self::ONE_FILE) { //单文件上传
			if( isset($_FILES[self::PIC_ATTR]) ){
				$postArr = $_FILES[self::PIC_ATTR];
			}
			else{
				return array('code' => self::ERROR_POST_ERRORPARA_CODE, 'msg' => self::ERROR_POST_ERRORPARA_MSG);
			}
			$res = $this->upload_picture($postArr);
			if (is_array($res) && isset($res['code']) && $res['code']){
				return array('code' => $res['code'], 'msg' => (isset($res['msg'])?$res['msg']:'未知错误') );
			}
			return array('code' => self::SUCCESS_CODE, 'msg' => self::SUCCESS_MSG, 'url' => $this->url);
		} else if($type == self::MUL_FILE){ //多文件上传
			//多文件上传代码
		} else {
			return array('code' => self::ERROR_POST_ERRORPARA_CODE, 'msg' => self::ERROR_POST_ERRORPARA_MSG);
		}
	}

	//单图片上传
	private function upload_picture($arr) {
		if (!isset($arr['tmp_name']) || !$this->isImageExists($arr['tmp_name'])) {
			return array('code' => self::ERROR_PIC_NOTEXIST_CODE, 'msg' => self::ERROR_PIC_NOTEXIST_MSG);
		}
		if(!isset($arr['size']) || !$this->checkFileSize($arr['size'])) {
			return array('code' => self::ERROR_PIC_TOOBIG_CODE, 'msg' => self::ERROR_PIC_TOOBIG_MSG);
		}
		$path_info = pathinfo($arr['name']);
		$image_type = $path_info['extension'];


		$destination = $this->getDestinationPath();
		$image_name = $this->generateImageName();

		$location = $destination."/".$image_name.".".$image_type; 
		$this->url = $this->image_server_url.$location;

		$resopnse = $this->oss_sdk_service->uploadFile($this->oss['bucket'],$location,$arr['tmp_name']);
		if(empty($resopnse->status) || !($resopnse->status === 200))
		{
			return array('code' => self::ERROR_UPLOAD_FAIL_CODE, 'msg' => self::ERROR_UPLOAD_FAIL_MSG);
		}
		/*
		$store_info = $this->upload_server_path.$location;
		if(!move_uploaded_file($arr['tmp_name'], $store_info)) {
			return array('code' => self::ERROR_UPLOAD_FAIL_CODE, 'msg' => self::ERROR_UPLOAD_FAIL_MSG);
		}*/
		/*
		$postArr = array(self::PARA_ORIGINID => $arr[self::PARA_ORIGINID], 'location' => $location, 'state' => 0);
		if(!($this->CI->picture->addPicture($postArr))) {
			return array('code' =>self::ERROR_SQL_ADDFAIL_CODE, 'msg' =>ERROR_SQL_ADDFAIL_MSG);
		}*/
		return array('code' => self::SUCCESS_CODE, 'msg' => self::SUCCESS_MSG);
	}
	
	//多图片上传
	private function upload_pictures($arr) {
	}

    //微信图片上传（
    public function weixin_upload($data,$type = self::ONE_FILE,$app) {
        $this->app = $app;
        if($type == self::ONE_FILE) { //单文件上传
            if( isset($data) && $data !='' ){
                $postArr = $data;
            }else{
                return array('code' => self::ERROR_data_ERRORPARA_CODE, 'msg' => self::ERROR_data_ERRORPARA_MSG);
            }
            $res = $this->weixin_upload_picture($postArr);
            if (is_array($res) && isset($res['code']) && $res['code']){
                return array('code' => $res['code'], 'msg' => (isset($res['msg'])?$res['msg']:'未知错误') );
            }
            return array('code' => self::SUCCESS_CODE, 'msg' => self::SUCCESS_MSG, 'url' => $this->url);
        }else {
            return array('code' => self::ERROR_POST_ERRORPARA_CODE, 'msg' => self::ERROR_POST_ERRORPARA_MSG);
        }
    }


    //微信单图片上传
    private function weixin_upload_picture($data) {
        if(!isset($data['size']) || !$this->checkFileSize($data['size'])) {
            return array('code' => self::ERROR_PIC_TOOBIG_CODE, 'msg' => self::ERROR_PIC_TOOBIG_MSG);
        }
        $path_info = pathinfo($data['name']);
        $image_type = $path_info['extension'];


        $destination = $this->getDestinationPath();
        $image_name = $this->generateImageName();

        $location = $destination."/".$image_name.".".$image_type;

        $this->url = $this->image_server_url.$location;
		
		
		$resopnse = $this->oss_sdk_service->putObject($this->oss['bucket'],$location,$data['imgdata'],array(
			'content' => $data['imgdata'],
			'length' => strlen($data['imgdata'])
		));
		if(empty($resopnse->status) || !($resopnse->status === 200))
		{
			return array('code' => self::ERROR_UPLOAD_FAIL_CODE, 'msg' => self::ERROR_UPLOAD_FAIL_MSG);
		}
		/*
        $store_info = $this->upload_server_path.$location;
        $newFile = fopen($store_info,"w+"); //打开文件准备写入
        $fw =fwrite($newFile,$data['imgdata']); //写入图片二进制流到文件
        fclose($newFile); //关闭文件
        if(!$fw){
            return array('code' => self::ERROR_UPLOAD_FAIL_CODE, 'msg' => self::ERROR_UPLOAD_FAIL_MSG);
        }*/

        return array('code' => self::SUCCESS_CODE, 'msg' => self::SUCCESS_MSG);
    }

	/**
	 * 判断temp图片是否存在
	 */
	private function isImageExists($tmpfile) {
		if(!is_uploaded_file($tmpfile))
			return false;
		return true;
	}

	//判断图片大小是否在允许范围内
	private function checkFileSize($size) {
		if($size > self::MAX_PIC_SIZE) 
			return false;
		return true;
	}
	
	//获得上传路径
	private function getDestinationPath() {
		// 将不同日期上传文件放在不同的目录下，
		//以日期（如20140425）作为文件夹名
		$appDir = $this->getAppDir();
		$path =  "uploads/image/".$appDir."/".date('Ymd');			
		$response = $this->oss_sdk_service->doesObjectExist($this->oss['bucket'],$path.'/');
		if(!isset($response->status) || !($response->status === 200))
		{
			$create_dir_response = $this->oss_sdk_service->createObjectDir($this->oss['bucket'],$path);
			if(!isset($create_dir_response->status) || !($create_dir_response->status === 200))
			{
				 return array('code' => 101, 'msg' => '无法创建目录');
			}
		}
		return $path;
	}
	
	//根据不同应用获取不同的存放图片目录
	private function getAppDir(){
		return isset($this->appDirArr[ $this->app ])?$this->appDirArr[ $this->app ]:$this->appDirArr['test'];
	}

	//生成图像名
	private function generateImageName() {
		$res = "";
		list($usec, $sec) = explode(" ", microtime());
		$time = ((float)$usec + (float)$sec);
		$random1 = mt_rand();
		$random2 = mt_rand();
		$random3 = mt_rand();
		$res = md5($time.$random1.$random2.$random3);
		$this->token = $res;
		return $res;
	}
	






 //移动端图片上传
    public function mobile_upload($data=array(),$type=self::ONE_FILE,$app='') {
        if($type == self::ONE_FILE) { //单文件上传
            if(!empty($data) && $app !=''){
                $this->app = $app;
                $postArr = $data;
            }else{
                return array('code' => self::ERROR_data_ERRORPARA_CODE, 'msg' => self::ERROR_data_ERRORPARA_MSG);
            }
            $res = $this->mobile_upload_picture($postArr);
            if (is_array($res) && isset($res['code']) && $res['code']){
                return array('code' => $res['code'], 'msg' => (isset($res['msg'])?$res['msg']:'未知错误') );
            }
            return array('code' => self::SUCCESS_CODE, 'msg' => self::SUCCESS_MSG, 'url' => $this->url);
        }else {
            return array('code' => self::ERROR_POST_ERRORPARA_CODE, 'msg' => self::ERROR_POST_ERRORPARA_MSG);
        }
    }


    //移动端单图片上传
    private function mobile_upload_picture($data) {
        if(!isset($data['size']) || !$this->checkFileSize($data['size'])) {
            return array('code' => self::ERROR_PIC_TOOBIG_CODE, 'msg' => self::ERROR_PIC_TOOBIG_MSG);
        }

        $destination = $this->getDestinationPath();
        $image_name = $this->generateImageName();

        $location = $destination."/".$image_name.".".$data['imgtype'];

        $this->url = $this->image_server_url.$location;//图片访问地址
        //$store_info = $this->upload_server_path.$location;//图片存储在服务器地址

        //$newFile = fopen($store_info,"w+"); //打开文件准备写入
        //$fw =fwrite($newFile,$data['imgdata']); //写入图片二进制流到文件
        //fclose($newFile); //关闭文件
		
		$resopnse = $this->oss_sdk_service->upload_file_by_content($this->oss['bucket'],$location,array(
			'content' => $data['imgdata'],
			'length' => strlen($data['imgdata'])
		));

        if(empty($resopnse->status) || !($resopnse->status === 200)){
            return array('code' => self::ERROR_UPLOAD_FAIL_CODE, 'msg' => self::ERROR_UPLOAD_FAIL_MSG);
        }

        return array('code' => self::SUCCESS_CODE, 'msg' => self::SUCCESS_MSG);
    }





	
	//test
	public function test_upload()
	{
		$dir = '/var/www/production/wssoto_static/image';
		//测试 上传整个目录
		ini_set('max_execution_time', '0');
		set_time_limit(0);
		//$response = $this->oss_sdk_service->create_mtu_object_by_dir($this->oss['bucket'],$dir,true);
		$response = $this->oss_sdk_service->batch_upload_file(array(
			'bucket' => $this->oss['bucket'],
			'directory' => $dir,
			'recursive' => true
		));
		var_dump($response);
		exit;
	}
}