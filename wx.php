<?php 
$wx = new wx();

class wx{
        // 和公众平台约定好的token值
        private const TOKEN = 'weixin';

        private $obj;
        // 消息的xml
        private $config = [];
        private $db;
        public function __construct(){
            
            if($_GET['echostr']){
                // 如果有此参数就执行验证
               echo $this->checkSignature();
            }else{
                // 引入数据库
                $this->db = include 'db.php';
                $this->config = include 'config.php';
                $this->acceptMesage();
            }
        }
        // 接收消息处理
        private function acceptMesage(){
            // 获取原始数据
            $xml = file_get_contents('php://input');
            // 写接收日志
            $this->writeLog($xml);
            //将xml转化为对象
            $this->obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            // 对象方式获取xml中content的值
            // var_dump($this->obj);
            // echo $obj->Content;
            // 消息类型
             $type = $this->obj->MsgType;
            //  var_dump($type);
             $msg = '';

            //  动态方法  例:textfun
            $funName = $type.'Fun';

            // 消息管理方法处理
            echo $msg = call_user_func([$this,$funName]);

            // 写发送日志
            if(!empty($msg)){
                $this->writeLog($msg,1);
            }
        }
        // 事件处理函数
        public function eventFun(){
            // 得到事件类型
            $event = (string)$this->obj->Event;
            // 转为小写
            $event = strtolower($event);
            // 得到点击事件中点击的是哪个按钮方法
            $eventKey = (string)$this->obj->EventKey;
            // 根据openid来查询数据表是否存在此用户
            $openid = (string)$this->obj->FromUserName;
            // 根据不同的事件类型进行相关的事件处理
            if('click'== $event){
                if('click001'== $eventKey){
                    // 组件sql
                    $sql = "select info from joke order by rand() limit 1";
                    // 执行sql
                    $info = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['info'];
                    return $this->createText($info);
                }elseif('click002'== $eventKey){
                    // 组件sql
                    $sql = "select * from material where type='image' order by rand() limit 1";
                    // 执行
                    $media_id = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['media'];
                    return $this->createImage($media_id);
                }
            }elseif('subscribe' == $event){
                    // 用户关注
                // 场景中传过来的数据表中对应用户的ID
                $user_id = (int)str_replace('qrscene_','',$eventKey);
                // 添加用户
                 // 组件sql
                 $sql = "select openid from user where openid='{$openid}'";
                 // 执行sql
                 $res = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
                 if ($res) {
                    //  数据存在 update
                    $sql = "update user set dtime=0 where openid='{$openid}'";
                 }else{
                     $stime = time();
                     $sql = "insert into user (openid,pid,stime) values('{$openid}',$user_id,$stime)";
                 }
                 $this->db->exec($sql);
                 return $this->createText("🌹🌹🌹🌹🌹🌹🌹🌹🌹🌹🌹\n\n小主,奴婢在这里恭候多时啦!\n\n🌹🌹🌹🌹🌹🌹🌹🌹🌹🌹🌹");
            }elseif('unsubscribe' == $event){
                $dtime = time();
                $sql = "update user set dtime=$dtime where openid='{$openid}'";
                $this->db->exec($sql);
            }elseif ('location' == $event) {
                $latitude = $this->obj->Latitude;
                $longitude = $this->obj->Longitude;
                $sql = "update user set longitude = $longitude,latitude=$latitude where openid='{$openid}'";
                $this->db->exec($sql);
            }
        }
        /**
         * 文本消息处理方法
         * 关键词查询
         */
        private function textFun(){
            $content = (string)$this->obj->Content;
		    // 根据openid来查询数据表是否存在此用户
		    $openid = (string)$this->obj->FromUserName;
            // var_dump($content);
            if(stristr($content,'图文-')){
                //回复图文
                return $this->createNews($content);
            }elseif(stristr($content,'笑话-'))
            {
                // 截取后几位
                $str = substr($content,7);
                $sql = "select * from joke where title like '%$str%' order by rand()";
                // 执行sql
                 $content = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['info'];
                // 如果为空 回复不存在
                if($content == null)
                {
                    return $this->createText('抱歉,没有此关键词的笑话');
                }else
                    {
                    return $this->createText($content);
                    }
            }elseif (stristr($content,'位置-')) 
            {
                $sql = "select longitude,latitude from user where openid='{$openid}'";
                // 执行sql
                $res = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
                // 搜索关键词
                $kw = str_replace('位置-','',$content);
                // 接入高德周边搜索api
                $url = 'https://restapi.amap.com/v3/place/around?key=6588f5b63ced8d8033b9caf7e9bc8f41&location='.$res['longitude'].','.$res['latitude'].'&keywords='.$kw.'&types=050000&radius=10000&radius=1000&offset=20&page=1&extensions=base';
                //发送get请求
                $json = $this->http_request($url);
                $arr = json_decode($json,true);
                if (count($arr['pois'])>0) 
                {
                    // 查询到结果
                    $res = $arr['pois'][0];
                    // 把数组的下标变成变量名
                    extract($res);
                    // 查询到结果
                    $content = "名称：{$name}\n";
                    $content .= "地址：{$address}\n";
                    $content .= "距离您的位置：{$distance}米";
                }else
                {
                    $content = '对不起,没有找到相关搜索!😭😭';
                    
                } 
            }
            // 响应给公众号服务器
            return $this->createText($content);
        }
        private function imageFun(){
            // $content = (string)$this->obj->Content;
            // if(stristr($content,'图文-')){
            //     //回复图文
            //     return $this->createImage();
            // }
        }
        
        private function voiceFun(){

        }
        /**
         * 生成文本消息的xml
         */
        private function createText(string $content){
            return sprintf($this->config['text'],$this->obj->FromUserName,$this->obj->ToUserName,time(),$content);
        }

        /*
         *图文消息处理方法
         */
        private function createNews($content){
            $Title = '特特伊的糗事：来一拨动图，各种神走位，死神擦肩过！';
            $Description = '来一拨动图，各种神走位，死神擦肩过！';
            $PicUrl = 'https://qiubai-video-web.qiushibaike.com/article/gif/FFXR53L4Y0JH54HI';
            $Url = 'https://www.qiushibaike.com/article/121187618';
            return sprintf($this->config['news'],$this->obj->FromUserName,$this->obj->ToUserName,time(),$Title,$Description,$PicUrl,$Url);
        }

        /**
	    * 生成图片消息的xml
	    */
        private function createImage(string $media_id){
            return sprintf($this->config['image'],$this->obj->FromUserName,$this->obj->ToUserName,time(),$media_id);
        }
        /*
         *写日志
         *@param $flag   0 接收  1 发送
         */
        private function writeLog(string $xml,int $flag = 0){
            $title = $flag == 0 ? '接收' : '发送';
            $dtime = date('Y年m月d日 H:i:s');

            #日志内容
            $log = $title."【{$dtime}】\n";
            $log.= "*************************************************************************************\n";
            $log.= $xml."\n";
            $log.= "*************************************************************************************\n";
            // 写日志 ,追加日志记录
            file_put_contents('wx.xml',$log,FILE_APPEND);
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
        // 初次接入验证
        private function checkSignature(){
            // 公众平台传过来的数据
            $signature = $_GET['signature'];
            $timestamp = $_GET['timestamp'];
            $nonce      = $_GET['nonce'];
            $echostr = $_GET["echostr"];

            $tmpArr['token'] = self::TOKEN;
            $tmpArr['timestamp'] = $timestamp;
            $tmpArr['nonce'] = $nonce;
            # 进行字典
                sort($tmpArr, SORT_STRING);
                # 拼接成字符串
                $tmpStr = implode( $tmpArr );
                # 进行sha1加密
                $tmpStr = sha1( $tmpStr );

                # 验证通过
                if( $tmpStr == $signature ){
                    return $echostr;
                }

                # 验证不通过
                return '';
            }   
}