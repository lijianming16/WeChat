<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>文件上传</title>
	<!--引入CSS-->
	<link rel="stylesheet" type="text/css" href="static/webuploader.css">

</head>
<body>
    <form action="fileupload.php" method="post" enctype="multipart/form-data">
            素材:
            <input type="radio" name="is_forever" value="0" checked="checked">临时
            <input type="radio" name="is_forever" value="1" >永久<br>
            素材类型:
            <input type="radio" name="type" value="image" checked="checked">图片
            <input type="radio" name="type" value="voice" >语音
            <input type="radio" name="type" value="video" >视频
            <input type="radio" name="type" value="thumb" >缩略图<br>
            <input type="file" name="type"><br>
            <input type="submit" value="上传">
    </form>
    <!--引入JS-->
	<script type="text/javascript" src="static/jquery.js"></script>
	<script type="text/javascript" src="static/webuploader.js"></script>
    <!-- <script>
        var uploader = WebUploader.create({
            auto: true,
            // swf文件路径
		    swf: 'static/Uploader.swf',

            // 文件接收服务端。
            server: 'fileupload.php',

            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#picker',

            // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
            resize: false
            });
    </script> -->
</body>
</html>