<?php
return [
    //获取access_token值的地址 
    'access_token_url' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
    // 自定义菜单创建地址
    'create_menu_url' => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s',
    // 删除自定义菜单地址
    'delete_menu_url' => 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=%s',

];