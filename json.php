<?php
// 获取原始数据
$xml = file_get_contents('php://input');
// 将数据转换为数组
var_dump(json_decode($xml,true));
