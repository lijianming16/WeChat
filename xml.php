<?php
$xml = "<xml>
        <ToUserName>
            <![CDATA[gh_6a357531c082]]>
        </ToUserName>
        <FromUserName>
            <![CDATA[oCN8I53bgO0X6q4dTA-RHciAKeMY]]>
        </FromUserName>
        <CreateTime>1551537647</CreateTime>
        <MsgType>
            <![CDATA[text]]>
        </MsgType>
        <Content>
            <![CDATA[php是世界上最好的额语言]]>
        </Content>
        <MsgId>22212679731543713</MsgId>
        </xml>";
        // 实例化一个DOMDocument对象
        $dom = new DOMDocument();
        // 解析一个xml标签字符串
        $dom->loadXML($xml);
        // 创建DOMXPath对象
        $xpath = new DOMXPath($dom);
        // 不管在什么地方都要获取content的值
        $query = '//Content';
        // 执行查询
        $res = $xpath->query($query);
        // 遍历这个对象
        foreach($res as $v){
            // 对象方式获取content的值
            var_dump($v->nodeValue);
        }
