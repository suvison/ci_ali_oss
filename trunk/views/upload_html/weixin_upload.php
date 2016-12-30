<?php

function curl_get($url){
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
    );
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    if(!$result = curl_exec($ch)){
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}


$appid = 'your appid';
$appsecret = 'your appsecret';
$timestamp = time();
$noncestr = md5($timestamp);
$url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];//记得在公众号后台绑定js允许的域名
//===========获取 access_token================

$access_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
$res = json_decode(@curl_get($access_token_url),true);
if(!isset($res['access_token']) || !isset($res['expires_in'])) {
    echo 'access_token 和 expires_in 不存在';
    exit();
}
$access_token = $res['access_token'];

//===========获取 jsApi_ticket================
$jsApi_ticket_url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
$res = json_decode(@curl_get($jsApi_ticket_url),true);
if($res['errcode'] != 0) {
    echo 'jsApi_ticket 不存在';
    exit();
}
$jsApi_ticket = $res['ticket'];
$jsApi_ticket_time =  time();

$signature = sha1("jsapi_ticket=".$jsApi_ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url);

?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/third_party/jquery-1.7.1.min.js"></script>
<style>
    .wrap{padding:12px 0;margin:0px;font-size:16px;}
    h2.title{text-align: center;color:white;font-size:2em}
        .info_act{padding:12px;}
        .info_act .clear{margin-top:12px;}
        .info_act .clear input::-webkit-input-placeholder{
        　　color:white;
        }
    .info_act .clear input[type="text"],label,textarea{float:left;-webkit-box-sizing:border-box;}
    .info_act .clear input{border-radius:0.5em;width:70%;border:1px solid #bbb;padding:0.5em 0;text-indent:1em;font-size: 14px}
    .info_act .clear label{
        width:30%;color:#444;line-height:1.9em;text-align:center;padding-right:0.5em;
    }
    .clear label span{font-size:13px}
    textarea{resize:none;border-radius:12px;padding:0.5em 1em;border:1px solid #bbb;font-size: 14px;width:70%;}
    .clear.confirm{text-align: center;margin-top:20px;}
    #confirm_btn{width:42%;background:#f55;color:white;font-size:1.25em;padding:0.6em 0;display:block;margin:auto;border:0px;border-radius:6px;float:none;text-indent: 0px;text-align: center}
    .u_img_area{width:70%;float:left;overflow: hidden}
    div.imgwrap{margin-bottom:6px;position:relative;-webkit-box-sizing:border-box}
    div.imgwrap,#upload{float:left;margin-left:8px;text-indent: 0px;margin-right:6px;border:0px;border-radius:6px;color:white;font-size:30px;padding:0px;height:65px;width:65px;border:2px solid #ff756b;}
    #upload{line-height:0.5em;-webkit-box-sizing:content-box!important;background-color:none;background:url(/resource/img/add.png) center center no-repeat;background-size:90%;}
    .img_display.clear{margin:0px;}
    .close2{position:absolute;right:0px;top:0px;height:20px;width:20px;text-align:center;line-height:20px;background:rgba(255,117,107,0.5);color:white;border-radius:0 0 0 10px;font-size:16px;}
    .imgwrap img{
        width: 100%;
        height: 100%;
    }
</style>
        <div class="main">
            <div class="wrap clear">
                <h2 class="title">报名啦！</h2>
                <form action="" class="info_act">
                    <div class="clear">
                        <label for="">上传照片:<br><span>(1-5张照片)</span></label>
                        <div class="u_img_area">
                            <input type="button" value="" id="upload">
                        </div>
                    </div>
                    <div class="confirm clear">
                        <input type="button" value="确认" id="confirm_btn">
                    </div>
                </form>
            </div>
        </div>
        <div>
            <span id ='span_test'>
                    hjdashfalsldfashl
            </span>
        </div>
<script>
    var can_use_js_api = true;
    var link = window.location.href;
    wx.config({
        debug: false,
        appId: '<?php echo $appid;?>',
        timestamp: '<?php echo $timestamp;?>',
        nonceStr: '<?php echo $noncestr;?>',
        signature: '<?php echo $signature;?>',
        jsApiList:['chooseImage', 'previewImage', 'uploadImage', 'downloadImage']
    });
    wx.error(function(){
        can_use_js_api = false;
    });
    wx.ready(function() {
        if(typeof(wx.checkJsApi) == "undefined") {
            alert("当前微信版本不支持微信高级分享接口,推荐更新微信至最新版本");
        }
        wx.checkJsApi({
            jsApiList:['chooseImage', 'previewImage', 'uploadImage', 'downloadImage'],
            success: function(res) {
            }
        });
            
    });
</script>
<script>


    var wx_media_arr = [];
    var max=5;
    var now=0;
    var media_id = '';
    //增加图片方法
    function addPics(src,id){
        var oDiv=document.createElement("div");
        oDiv.className="imgwrap";
        oDiv.id_=id;
        oDiv.innerHTML='<img src="'+src+'"><div class="close2">&times;</div>';
        $(oDiv).insertBefore("#upload");
        now++;
    }

    //微信图片上传
    $(".u_img_area").on('click', '#upload', function(){
        if(now>=max) {alert("最多只可上传五张图片"); return;}
        wx.chooseImage({
            count: 1, // 选择图片上限
            sizeType: ['original', 'compressed'],
            sourceType: ['album', 'camera'],
            success: function (res) {//选择图片成功
                var localIds = res.localIds;
                wx.uploadImage({
                    localId: localIds[0], 
                    isShowProgressTips: 1, 
                    success: function (re) {//上传微信服务器成功
                        addPics(localIds[0],re.serverId);
                        media_id = re.serverId;
                        wx_media_arr.push(re.serverId);// 返回图片的服务端ID
                    }
                }); 
            },
            fail:function(re){//chooseImage调用失败
                alert("chooseImage调用失败");
            }
        });
    });


    //提交
    $(".main").on('click', '#confirm_btn', function() {
        var name = $("input[name=name]").val();
        var mobile = $("input[name=mobile]").val();
        var desc = $("#desc").val();
        if (name == '' || mobile == '' || desc == '') {//内容非空
            alert("请填写全部项目！");
            return false;
        };
        // if (wx_media_arr.length == 0) {
        //     alert("请至少上传1张图片！");
        //     return false;
        // };
        $("#confirm_btn").val("提交中...").addClass("disabled");

        $.ajax( {    
            url:'http://your_domain_name/upload/weixin_upload',// 跳转到 action    
            data:{    
                access_token : '<?php echo $access_token;?>',
                // wx_media_arr : JSON.stringify(wx_media_arr)
                media_id : media_id
            },
            type:'post',    
            dataType:'json',    
            success:function(data) {
                alert(1);
                $('#span_test').html(JSON.stringify(data));
            },
            error : function(data) { 
                alert(0);
                $('#span_test').html(JSON.stringify(data));
            }    
        });  
    });

</script>