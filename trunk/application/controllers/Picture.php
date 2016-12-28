<?php
//图片上传统一接口
class Picture extends CI_Controller {
	
	//是否要求post过来的from字段一定得在appArr数组里面，false的时候找不到则走test字段
	private $appMatchRequired = true; 
	
	//app名字
	private $appArr = array(
		'www.ylxirang.com' => 'ylxirang',
        'dev.ylxirang.com' => 'test',
        'vstoreadmin.ylxirang.com' => 'ylxirang',
        'dev-vstoreadmin.ylxirang.com' => 'test', 
	);
	
	//构造函数
	public function __construct() {
		parent::__construct();
		
		//只支持post提交
		if( !isset($_POST) || empty($_POST) ){
			$this->jsonReturnUrl(2,'缺少参数','');
		}
		
		//要求from字段
		if( !isset($_POST['from']) ){
			$this->jsonReturnUrl(3,'缺少from字段','');
		}
		$temp_arr = explode('.',$_POST['from']);
		if( is_numeric($temp_arr[0]) && !is_numeric($temp_arr[1]))
		{
			$temp_arr[0] = '*';
		}
		$_POST['from'] = implode('.',$temp_arr);
		
		//from注册验证
	   if($this->appMatchRequired && !isset($this->appArr[$_POST['from']]) ){
		   $this->jsonReturnUrl(4,'from来源未注册','');
	   }
	}

	//上传图片，保存原图，支持单图和多图
	public function upload() {
		
		//获取app
		$app = isset($this->appArr[$_POST['from']])?$this->appArr[$_POST['from']]:$this->appArr['test'];
		
		//上传图片
		$this->load->library('picture_lib');
		$result = $this->picture_lib->upload($_POST,'file',$app);

		//返回结果
		if(isset($result['code']) && $result['code'] === 0){
			$url = isset($result['url'])?$result['url']:'';
			$this->jsonReturnUrl(0,'',$url);
		}
		else{
			$this->jsonReturnUrl($result['code'],'图片上传时错误：'.$result['msg'],'');
		}
	}

    //微信上传图片，保存原图，支持单图
    public function weixin_upload(){
        //要求from字段
        if( !isset($_POST['access_token']) ){
            $this->jsonReturnUrl(11,'缺少access_token字段','');
        }
        if( !isset($_POST['media_id']) ){
            $this->jsonReturnUrl(12,'media_id','');
        }

        //获取微信服务器上的图片地址
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=" . $_POST['access_token'] . "&media_id=" .$_POST['media_id'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            _error('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);


        //匹配获取图片图片二进制流
        preg_match("/Content-Length:\s\d*\s*([\s\S]*)/", $content,$str);
        //匹配获取图片图片名称
        preg_match("/filename=\"([^\"]+?)\"/", $content,$imgname);

        if(!$str[1] || !isset($imgname[1])){
            $this->jsonReturnUrl(22,'access_token过期或错误，获取不到微信图片数据','');
        }

        $image_size = strlen($str[1]);


        $data = array(
            'name'      => $imgname[1],
            'size'      => $image_size,
            'imgdata'   => $str[1]
        );

        //获取app
        $app = isset($this->appArr[$_POST['from']])?$this->appArr[$_POST['from']]:$this->appArr['test'];

        //上传图片
        $this->load->library('picture_lib');
        $result = $this->picture_lib->weixin_upload($data,'file',$app);


        //返回结果
        if(isset($result['code']) && $result['code'] === 0){
            $url = isset($result['url'])?$result['url']:'';
            $this->jsonReturnUrl(0,'',$url);
        }
        else{
            $this->jsonReturnUrl($result['code'],'图片上传时错误：'.$result['msg'],'');
        }

    }
	
	





	//移动端上传图片，保存原图，支持单图
    public function mobile_upload(){
        //要求from字段
        if( !isset($_POST['img_data']) ){
            $this->jsonReturnUrl(31,'缺少必要参数 img_data','');
        }
        if( !isset($_POST['img_type']) ){
            $this->jsonReturnUrl(32,'缺少必要参数 img_type','');
        }

        $img_type = $_POST['img_type'];
        $img_data = $_POST['img_data'];

        //可上传图片格式类型
        $type = array('jpeg','jpg','png');

        if(!in_array($img_type,$type)){
            $this->jsonReturnUrl(33,'上传图片格式错误！','');
        }

        //图片大小
        $image_size = strlen($img_data);

        $data = array(
            'imgtype'   => $img_type,
            'size'      => $image_size,
            'imgdata'   => base64_decode($img_data)
        );

        //获取app
        $app = isset($this->appArr[$_POST['from']])?$this->appArr[$_POST['from']]:$this->appArr['test'];

        //上传图片
        $this->load->library('picture_lib');
        $result = $this->picture_lib->mobile_upload($data,'file',$app);


        //返回结果
        if(isset($result['code']) && $result['code'] === 0){
            $url = isset($result['url'])?$result['url']:'';
            $this->jsonReturnUrl(0,'',$url);
        }
        else{
            $this->jsonReturnUrl($result['code'],'图片上传时错误：'.$result['msg'],'');
        }

    }








    //json return url
	private function jsonReturnUrl($errorCode,$errorMessage,$url=''){
		header('Content-Type: application/json; charset=utf-8');
		$result = array();
		$result['errorCode'] = $errorCode;
		$result['errorMessage'] = $errorMessage;
		$result['url'] = $url;
		echo json_encode($result);
		exit;
	}
}
