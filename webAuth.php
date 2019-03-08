<?php
/**
 * 网页授权生成url地址
 * 
 */
$appid = 'wx2da6a1e66d0f22fe';
$redirect_uri = urlencode('http://n548q4.natappfree.cc/000.php');
$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state=100#wechat_redirect";
// 跳转
header('location:'.$url);
