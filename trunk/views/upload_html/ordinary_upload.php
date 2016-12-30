<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style>
		#target{
			visibility:hidden;
			width:0;height:0;
			position: absolute;
		}
	</style>
</head>
<body>
	<iframe src="" name="img" id="target" frameborder="0"></iframe>
	<form action="http://your_domain_name/upload/ordinary_upload" target="img" id="data" enctype="multipart/form-data" method="POST">
		<input type="file" accept="image/*" name="image">
		<input type="file" accept="image/*" name="upload">
		<input type="submit" id="submit" value="上传图片">
	</form>
</body>
<script>
	var fr=document.getElementById("target"),fm=document.getElementById("data");
	fr.onload=function(){
		try{
			var src=fr.contentWindow.name;
			alert(src);
		}
		catch(e){
			console.log(e);
		}
	}
</script>
</html>