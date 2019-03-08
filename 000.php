<?php
session_start();
// 回调处理得到openid的值
$code = $_GET['code'];
$appid = 'wx2da6a1e66d0f22fe';
$secret = '3b6f72d1cbf6069a688ec89775930413';
$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";

$arr = json_decode(http_request($url),true);

$access_token = $arr['access_token'];
$openid = $arr['openid'];

// 写入session
$_SESSION['openid'] = $openid;

// 拉取用户信息
$url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
$arr = json_decode(http_request($url),true);
$_SESSION['userinfo'] = $arr;
// print_r($arr);die;
// 登录成功后，页面跳转
header('location:welcome.php');















function http_request(string $url,$data ='',string $filepath = ''){
    // filepath不为空就表示有文件上传
    if(!empty($filepath)){
        $data['media'] = new CURLFile($filepath);
    }
    // 初始化curl
    $link = curl_init();    
    // 设置curl
    curl_setopt($link,CURLOPT_URL,$url);
    // 设置输出的信息不直接输出
    curl_setopt($link,CURLOPT_RETURNTRANSFER,1);
    // 取消https的证书验证
    curl_setopt($link,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($link,CURLOPT_SSL_VERIFYHOST,0);
    // 设置请求超时时间  单位是秒
    curl_setopt($link,CURLOPT_TIMEOUT,15);
    // 伪造一个浏览器型号
    curl_setopt($link,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36');
    // 表示有数据上传
    if(!empty($data)){
        if(is_string($data)){
            // 如果是一个字符串就表示是json
            curl_setopt($link,CURLOPT_HTTPHEADER,[
                    'Content-Type:application/json;charset=utf-8'
                ]);
        }
        // 告诉curl使用了post请求
    curl_setopt($link,CURLOPT_POST,1);
    //post数据
    curl_setopt($link,CURLOPT_POSTFIELDS,$data);
    }
    // 执行curl
    $data = curl_exec($link);
    // 得到请求的错误码  0表示成功  大于0就表示请求有异常
    $error = curl_errno($link);
    // echo $error;exit;
    if(0 < $error){
        // 抛出自己的异常
        throw new Exception(curl_error($link), 1001);
        
    }
    // 关闭curl
    curl_close($link);
    // 返回数据
    return $data;
}