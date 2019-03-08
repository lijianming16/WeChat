<?php
// $wx = new wechat();
// echo $wx->signature();
// 创建自定义菜单提示
// echo $wx->createMenu() ? '创建自定义菜单成功': '创建自定义菜单失败';
// echo $wx->createMenu() ? '删除自定义菜单成功': '删除自定义菜单失败';
// $wx->qrcode();
// echo "<pre/>";
// $arr = $wx->getOpenToInfo('onnJv1dCgQInLJEFrUSRYvcuhOoM');
// var_dump($arr);
// 主动微信公众号请求类
class wechat{

    const APPID = 'wx2da6a1e66d0f22fe';
    const SECRET = '3b6f72d1cbf6069a688ec89775930413';
    // 接口数组
    private $config = [];
    public function __construct(){
        $this->config = include __DIR__.'./apiconfig.php';

    }
    // 根据用户id获取用户信息
    public function getOpenToInfo(string $openid){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
        $url = sprintf($url,$this->getAccessToken(),$openid);
        // get请求获取用户信息
        $arr = json_decode($this->http_request($url),true);
        return $arr;
    }
    // 实现素材上传功能
    public function upFile(string $type,string $filepath = '',int $is_forever = 0){
        if(0 == $is_forever){
            $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s";
        }else{
            // 永久地址
            $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=%s&type=%s";
        }
        // 格式化url字符串
        $url = sprintf($url,$this->getAccessToken(),$type);
        // 发起post请求
        $json = $this->http_request($url,[],$filepath);
        $arr = json_decode($json,true);

        return $arr['media_id'];
    }
    /**
     * 生成场景二维码(临时|永久)
     * @parma  int is_forever   0表示临时  1表示永久
     */
    public function qrcode(int $scene_id = 1,int $is_forever = 0){
        // 请求的url地址
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
        // 数据准备
        if($is_forever ==0){
            // 1.得到请求图片资源的凭据
            // 临时
            // scene_id 此处的值一般用,用户表中的自增ID来区分
            $data = 
                '{
                    "expire_seconds": 2592000, 
                    "action_name": "QR_SCENE", 
                    "action_info": {"scene": {"scene_id": '.$scene_id.'}}
                }';
        }else{
            // 永久
            $data = '{
                    "action_name": "QR_LIMIT_SCENE", 
                    "action_info": {"scene": {"scene_id": '.$scene_id.'}}
                    }';
        }
        // 发送post请求 得到凭据
        $ticket = json_decode($this->http_request($url,$data),true)['ticket'];
        // 2.换取图片资源
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
        // 发起get请求得到资源
        $res = $this->http_request($url);
        // 在服务器中生成图片文件
        file_put_contents("qrcode/{$scene_id}.jpg",$res);
        // 返回图片在服务器中的请求url
        return "qrcode/{$scene_id}.jpg";
    }
    // 创建自定义菜单
    public function createMenu(){
        // 创建自定义菜单URL地址
        $url = sprintf($this->config['create_menu_url'],$this->getAccessToken());
        // POST请求数据
        $json = '{
                    "button":[
                    {    
                        "type":"click",
                        "name":"随机笑话",
                        "key":"click001"
                    },
                    {
                        "name":"开心时刻",
                        "sub_button":[
                        {    
                            "type":"view",
                            "name":"商城首页",
                            "url":"http://n548q4.natappfree.cc/webAuth.php"
                        },
                        {
                            "type":"view",
                            "name":"最右",
                            "url":"http://www.izuiyou.com/home"
                        },
                        {
                            "type":"view",
                            "name":"糗事百科",
                            "url":"https://www.qiushibaike.com/"
                        }]
                    },
                    {    
                        "type":"click",
                        "name":"搞笑图片",
                        "key":"click002"
                    }] 
                }';
                $errcode = json_decode($this->http_request($url,$json),true)['errcode'];
                return $errcode == 0 ? true : false;
    }
    // 删除自定义菜单
    public function deleteMenu(){
        $url = sprintf($this->config['delete_menu_url'],$this->getAccessToken());
        // 发起get请求
        $errcode = json_decode($this->http_request($url,$json),true)['errcode'];
        return $errcode == 0 ? true : false;
    }
    // 获取jsapi_ticket认证
    public function jsapiTicket(){
        // 缓存的key  有效时长是两小时  
        $key = self::APPID.'_jsapiTicket';
        if(false != ($jsapiTicket = $this->mem()->get($key))){
            // 缓存中有数据
            return $jsapiTicket;
        }
        // 请求得到了jsapiTicket
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token='.$this->getAccessToken();
        $jsapiTicket = json_decode($this->http_request($url),true)['ticket'];
        // 写入到缓存中
        $this->mem()->set($key,$jsapiTicket,0,3600);
        return $jsapiTicket;

    }
    // 随机字符串
    public function noncestr(){
        $str = 'sdfdsfgdfhgdxfdsfefdsdsfdsg';
        // 随机打乱一个字符串
        $str =  str_shuffle($str);
        return substr($str,0,$len);
    }
    /**
     * 当前url地址
     */
    public function currentUrl(){
        return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    }
    /**
     * 生成签名
     */
    public function signature(){
        // 时间戳
        $time = time();
        // 随机字符串
        $noncestr = $this->noncestr();
        // 拼接加密字符串
        $str = 'jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s';
        $str = sprintf($str,$this->jsapiTicket(),$nonceStr,$time,$this->currentUrl());
        //对拼接后的字符串进行sha1加密
        $signature =  sha1($str);
        // return $signature;
        return [
            'timestamp' =>$time,
            'nonceStr' => $nonceStr, 
			'signature' => $signature
        ];
    }
    // 获取access_token值
    private function getAccessToken(){
        // 判断缓存中是否有缓存的access_token的值
        // 如果有,就读缓存,如果没有就请求接口,写入缓存并返回结果
        if(false != ($accessToken = $this->mem()->get(self::APPID))){
            return $accessToken;
        }

        // 访问的url地址
        $url = sprintf($this->config['access_token_url'],self::APPID,self::SECRET);
        // get请求
        $arr = json_decode($this->http_request($url),true);
        $this->mem()->set(self::APPID,$arr['access_token'],0,3600);
        return $arr['access_token'];
    }
    // 得到memcache对象
    private function mem(){
        $memcache = new Memcache();
        $memcache->addServer('127.0.0.1',11211);
        return $memcache;
    }
    private function http_request(string $url,$data ='',string $filepath = ''){
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
}