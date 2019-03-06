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
            // 根据不同的事件类型进行相关的事件处理
            if('click'== $event){
                // 得到点击事件中点击的是哪个按钮方法
                $eventKey = (string)$this->obj->EventKey;
                if('click001'== $eventKey){
                    // 组件sql
                    $sql = "select info from joke order by rand() limit 1";
                    // 执行sql
                    $info = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['info'];
                    return $this->createText($info);
                }else if('click002'== $eventKey){
                    // 组件sql
                    $sql = "select * from material where type='image' order by rand() limit 1";
                    // 执行
                    $media_id = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['media'];
                    return $this->createImage($media_id);
                }
            }
        }
        /**
         * 文本消息处理方法
         * 关键词查询
         */
        private function textFun(){
            $content = (string)$this->obj->Content;
            // var_dump($content);
            if(stristr($content,'图文-')){
                //回复图文
                return $this->createNews($content);
            }
                $sql = "select * from joke where title like '%$content%' order by rand()";
                // 执行sql
                 $title = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['info'];
                // 如果为空 回复不存在
                 if($title == null){
                    return $this->createText('抱歉,没有此关键词的笑话');
                }
                // 响应给公众号服务器
                return $this->createText($title);
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