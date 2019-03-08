<?php
// 引入
include '../wechat.php';
// 引入数据库
$db = include '../db.php';
// 获取wechat实例对象
$wx = new wechat();

$act  = $_GET['act'] ?? 'list';
if ('list' == $act) {
    $sql = "select * from user";
    $data = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data,256);
}