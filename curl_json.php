<?php
/**
 * curl操作四步走
 *1、初始化 curl_init()
 *2、设置 curl_setopt()
 *3、执行 curl_exec()
 * 4、关闭 curl_close()
 */


// 请求的url地址
$url = 'http://www.wx.cn:8080/file.php';
// 通过curl要上传到别的服务器上的地址
$filepath = __DIR__.'\01.jpg';
// echo $filepath;exit;
$data['pic'] = new CURLFile($filepath);
$data['id']  = 100;
// PHP5.4以后  JSON_UNESCAPED_UNICODE == 256
// $json = json_encode(['ID'=>1,'name'=>'张三'],JSON_UNESCAPED_UNICODE);
// $json = json_encode(['ID'=>1,'name'=>'张三'],256);
// echo $json;exit;
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
// 告诉curl使用了post请求
curl_setopt($link,CURLOPT_POST,1);
// 如果是json类型加一个头信息说明
// curl_setopt($link,CURLOPT_HTTPHEADER,[
//     'Content-Type:application/json;charset=utf-8'
// ]);
//post数据
curl_setopt($link,CURLOPT_POSTFIELDS,$data);
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
// 正则表达式
// $preg = '/<h3>.*<\/h3>/';
// 匹配结果
// preg_match($preg,$data,$arr);
echo  $data;