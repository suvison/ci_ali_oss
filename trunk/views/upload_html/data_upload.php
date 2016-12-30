<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link rel="stylesheet" href="mycommon.css">
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
			padding-top:0.5em;
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
		}
		.input-wrap.left{
			width:44%;
			margin:0 3% 3%;
			padding:0 0.75rem;
			padding-bottom:30%;
			overflow:hidden;
			border:1px solid gray;
			background:#ddd;
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
			font-size:1.25rem;
		}
		.input-wrap.left input{
			width:100%;
			position:absolute;
			height:100%;
			opacity: 0;
			margin:0;
			left:0;
		}
		.input-wrap .img-shower{
			position:absolute;
			top:0;left:0;
			width:100%;
			height:100%;
			overflow:hidden;
			text-align: center;
			pointer-events:none;
			visibility: hidden;
			background:center center no-repeat;
			background-size:contain;
		}
		.img-shower.active{
			visibility: visible;
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
			margin:2em 30%;
		}
	</style>
</head>
<body>
	<div class="content">
		<form action="" id="user_info">
			
			<div class="img-wraps clear">
			<div class="input-wrap b-box left">
				<input type="file" id="id_img">
				<div class="img-shower"></div>
				<div class="mask">
					<p class="add">+</p>
					<p class="img-name">照片</p>
					<p class="upload">上传照片</p>
					<p class="loader"></p>
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
	$("#id_img,#dr_img,#auto_img1,#auto_img2").on("change",function(){
		var reader = new FileReader();
		var ts=$(this),loader=ts.next().next().children(".loader");
		loader.addClass("ing");
		reader.onload = function(e) {
			var src = e.target.result;
			ts.next().addClass("active").css("backgroundImage","url("+src+")");
			loader.removeClass("error ing");
			$.ajax({
			url: 'http://your_domain_name/upload/data_upload',
			type: 'POST',
			dataType:'json',
			data:{img:src},
			// processData:false,
			// contentType:false,
			success:function(e){
				createImg(ts,src);
			},
			error:function(e){
				
			}
		})
		};
		reader.onerror = function() {
			loader.removeClass("ing").addClass("error");
			this.onerror = null
		};
		reader.readAsDataURL(this.files[0]);
	});
</script>
</html>