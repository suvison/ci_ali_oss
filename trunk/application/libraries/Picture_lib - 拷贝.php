<?php
/**
 * 图片处理类
 */
class Picture_lib {
	const ONE_FILE = 'file'; //单文件上传
	const MUL_FILE = 'files'; //多文件上传
	const PIC_ATTR = 'Filedata';
	const MAX_PIC_SIZE = 2097152; // 最大上传图片2M
	
	private $upload_server_path = '/var/www/production/wssoto_static/' ;  //上传服务器地址
	private $image_server_url = 'http://static.wssoto.com/';  //图片服务器地址

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
		'wssoto' => 'wssoto',
		'wecommunity' => 'wecommunity',
		'wemember' => 'wemember',
		'xmember' => 'xmember',
		'wesite' => 'website',
		'zdstore' => 'zdstore',
		'fuwu' => 'fuwu',
		'vstoreadmin' => 'vstoreadmin',
		'test' => 'test',
        'appointment' => 'appointment'
	);

	//构造函数
	public function __construct() {
		date_default_timezone_set("PRC");
		$this->CI = & get_instance();
		//$this->CI->load->model('picture_model', 'picture');
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
	private function upload_picture($arr) { //未完成
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
		$store_info = $this->upload_server_path.$location;
		if(!move_uploaded_file($arr['tmp_name'], $store_info)) {
			return array('code' => self::ERROR_UPLOAD_FAIL_CODE, 'msg' => self::ERROR_UPLOAD_FAIL_MSG);
		}
		/*
		$postArr = array(self::PARA_ORIGINID => $arr[self::PARA_ORIGINID], 'location' => $location, 'state' => 0);
		if(!($this->CI->picture->addPicture($postArr))) {
			return array('code' =>self::ERROR_SQL_ADDFAIL_CODE, 'msg' =>ERROR_SQL_ADDFAIL_MSG);
		}*/
		return array('code' => self::SUCCESS_CODE, 'msg' => self::SUCCESS_MSG);
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
        $store_info = $this->upload_server_path.$location;

        $newFile = fopen($store_info,"w+"); //打开文件准备写入
        $fw =fwrite($newFile,$data['imgdata']); //写入图片二进制流到文件
        fclose($newFile); //关闭文件

        if(!$fw){
            return array('code' => self::ERROR_UPLOAD_FAIL_CODE, 'msg' => self::ERROR_UPLOAD_FAIL_MSG);
        }

        return array('code' => self::SUCCESS_CODE, 'msg' => self::SUCCESS_MSG);
    }
	
	//多图片上传
	private function upload_pictures($arr) {	
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
		if (!file_exists($this->upload_server_path . $path))
			mkdir($this->upload_server_path . $path);
		return $path;
	}
	
	//根据不同应用获取不同的存放图片目录
	private function getAppDir(){
		return isset($this->appDirArr[ $this->app ])?$this->appDirArr[ $this->app ]:$this->appDirArr['test'];
	}

	//生成图像名
	private function generateImageName() {
		$res = "";
		$time = time();
		$random = mt_rand(0, 100);
		$res = md5($time.$random);
		$this->token = $res;
		return $res;
	}
}