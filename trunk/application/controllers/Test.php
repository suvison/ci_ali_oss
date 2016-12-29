<?php
class Test extends CI_Controller {
	public function index()
	{
        // phpinfo();

		// $this->load->library("picture_lib");
		// $this->picture_lib->test_upload(); 
        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=fyWCmDnO1WreZ8PvdXiRCwKVVnK3tpixnc2K85KnVxSDE6MUOGJ7kmjaWZB-J0NjWwSScLdbbbJiGdrqpqmWRxNO3kiN0zKU4Bpf0pHLz6rjg_hgdOMK9vRORByCZ_WPIDKhACAQLS&media_id=h-3diXzk5tCAhqlEU0faUCChqbK0Z9poRztSYAzmEGzrkw2GjcX1xoaBLi9ZrnSU';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            _error('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        p($content); 
	}
}
