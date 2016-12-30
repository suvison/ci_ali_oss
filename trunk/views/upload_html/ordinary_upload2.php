<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link rel="stylesheet" href="mycommon.css">
	<meta name="viewport" content="width=device-width, initial-scale=1,minimum-scale=1.0,maximum-scale=1, user-scalable=0">
	<script src="/jquery.min.js"></script>
	<style>
		body{
			background:white;
		}
		.header{
			position: relative;
			padding-bottom:50%;
			background:#ddd;
		}
		.header .user{
			position:absolute;
			top:0;
			left:0;right:0;
			margin:auto;
			height:100%;
		}
		.header .mask{
			position:absolute;
			width:100%;
			left:0;bottom:0;
			background:rgba(120,120,120,0.5);
			font-size: 1rem;color:white;
			line-height:2.4rem;
			padding:0 0.75rem;
		}
		.header .user-name{
			font-size:1.2rem;
		}
		#user_info{
			padding:0.5em;
		}
		.input-wrap{
			position:relative;
		}
		.input-wrap.type1{
			padding:1rem 1.5rem 1rem 3.5rem;
		}
		.type1 .label{
			width:2rem;
			height:2.4rem;
			position:absolute;
			left:0.8rem;
			top:1rem;
			background:center center no-repeat;
			background-size:100% auto;
		}
		.label.icon1{
			background-image:url(img/newimg/tel.png);
		}
		.label.icon2{
			background-image:url(img/newimg/idcard.png);
		}
		.type1 input{
			width:100%;
			text-indent: 0.75rem;
			font-size: 1rem;
			border:1px solid #ccc;
			height:2.4rem;
			vertical-align: top;
		}
		.img-wraps{
			overflow:hidden;
			padding:0.5rem;
			border-bottom:1px solid #ccc;
		}
		.input-wrap.left{
			width:23%;
			margin-right:2%;
			padding:0 0.75rem;
			padding-bottom:23%;
			overflow:hidden;
			outline:1px solid gray;
			background:#ddd;
			margin-bottom:0.5rem;
		}
		.input-wrap .mask{
			position:absolute;
			width:100%;
			top:50%;
			left:0;
			margin-top:-2.5rem;
			pointer-events:none;
			text-align:center;
		}
		.input-wrap .add{
			font-size:3em;
			color:gray;
			line-height:1em;
		}
		.input-wrap .img-name{
			color:#999;
			font-size: 0.9rem;
		}
		.input-wrap .upload{
			color:rgb(245,103,53);
			font-size:1.1rem;
		}
		.input-wrap.left input{
			width:100%;
			position:absolute;
			height:100%;
			opacity: 0;
			margin:0;
			left:0;
		}
		.img-shower{
			position:relative;
			width:23%;
			margin-right:2%;
			margin-bottom:0.5rem;
			padding-bottom:23%;
			/*border-radius:0.2rem;*/
			overflow:hidden;
			text-align: center;
			background:center center no-repeat;
			background-size:contain;
			outline:1px solid gray;
		}
		.img-shower .close{
			position:absolute;
			top:0;right:0;
			width:30%;
			height:30%;
			background:rgba(0,0,0,0.5) url(img/newimg/close2.png) center center no-repeat;
			background-size:50%;
			border-radius:0 0 0 50%;
			z-index:1;
		}
		.img-shower input{
			display:none;
		}
		.title{
			padding:0.5rem 0;
			font-size: 1.2rem;
			/*border-bottom:1px solid #ccc;*/
			/*border-top:1px solid #ccc;*/
		}
		.loader{
			text-align: center;
			color:rgb(245,103,53);
			font-size:1.1rem;
		}
		.loader.ing:after{
			content:"图片上传中...";
		}
		.loader.error:after{
			content:"图片失败，请重试！";
		}
		#submit{
			width:40%;
			/*margin-left:30%;*/
			background:rgb(245,103,53);
			height:3.4rem;
			font-size:1.2rem;
			color:white;
			border-radius:0.3rem;
			margin:1em 30%;
		}
	</style>
</head>
<body>
	<div class="content">
		<form action="" id="user_info">
			<p class="title">
				照片:
			</p>
			<div class="img-wraps clear">
				<div class="input-wrap b-box left">
					<input type="file" name="IDCard" id="id_img" accept="image/*">
					
					<div class="mask">
						<p class="add">+</p>
						<p class="img-name"></p>
						<p class="upload">上传照片</p>
					</div>
				</div>
			</div>
			<div class="input-wrap">
				<input type="button" class="reset" value="提交" id="submit">
			</div>
		</form>
	</div>
</body>
<script>
	$(".content").on("change","#id_img,#dr_img,#auto_img1,#auto_img2",function(){
		var reader = new FileReader();
		var ts=$(this),blob = this.files[0],url=window.URL||window.webkitURL,src=url.createObjectURL(blob),img = document.createElement("img");
        img.onload = function(e) {
        	url.revokeObjectURL(img.src);//清除释放
        };
        var fdata=new FormData();
        fdata.append(this.name,blob);
        $.ajax({
			url: 'http://your_domain_name/upload/ordinary_upload',
			type: 'POST',
			dataType:'json',
			data:fdata,
			processData:false,
			contentType:false,
			success:function(e){
				createImg(ts,src);
			},
			error:function(e){
				
			}
		})
        img.src=src;
	});
	function createImg(obj,src){
		var ele=$('<div class="img-shower left"><span class="close"></span></div>');
		ele.css("backgroundImage","url("+src+")");
		obj.parent().before(ele).prepend(obj.clone());
		ele.append(obj);
	}
	$(".img-wraps").on("click",".close",function(){
		$(this).parent().remove();
	});
	
</script>
</html>