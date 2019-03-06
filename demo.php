<?php
echo "<pre />";
$json = file_get_contents('php://input');
var_dump(json_decode($json,true));

?>
<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h3>一个好网站</h3>
</body>
</html> -->