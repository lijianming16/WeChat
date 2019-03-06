<?php
// 引入
include 'wechat.php';
// 引入数据库
$db = include 'db.php';
// 获取wechat实例对象
$wx = new wechat();
// 文件路径
$dirpath = __DIR__.'/pic/';
// 扩展名的获取
$ext = pathinfo($_FILES['type']['name'],PATHINFO_EXTENSION);
// 新的文件名
$newfile = time(). '.' .$ext;
// 素材服务器地址
$filepath = $dirpath.$newfile;
// 获取type类型
$type = $_POST['type'];
// 获取是否是临时
$is_forever = $_POST['is_forever'];
// 上传
$res = move_uploaded_file($_FILES['type']['tmp_name'],$filepath);
if($res == 1){
    $media_id = $wx->upFile($type,$filepath,$is_forever);
    // 插入数据
    $sql = "insert into material(is_forever,type,media,ctime,filepath) values(?,?,?,?,?)";
    // 预处理
    $stmt = $db->prepare($sql);
    // 执行
    $res = $stmt->execute([$is_forever,$type,$media_id,time(),$filepath]);
    echo '<script>alert("上传成功")</script>';
    echo '<script>window.location.href="http://www.wx.cn:8080/admin.php"</script>';
}else{
    echo '<script>alert("上传失败")</script>';
    echo '<script>window.location.href="http://www.wx.cn:8080/admin.php"</script>';
}
