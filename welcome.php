<?php
   session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div>你好,世界</div>
    <img src="<?php echo $_SESSION['userinfo']['headimgurl'] ?>" style="width: 200px;">
</body>
</html>