<?php
    include 'wechat.php';
    $wx = new wechat();
    $appid = 'wx2da6a1e66d0f22fe';
    $data = $wx->signature();
    extract($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>js-sdk</title>
    <script src="./jweixin-1.4.0.js"></script>    
</head>
<body>
<img src="##" id="img" style="width:200px;">
<input type="button" value="点击拍照" onclick="fn()">
<script>
    wx.config({
        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: '<?php echo $appid; ?>', // 必填，公众号的唯一标识
        timestamp: <?php echo $timestamp; ?>, // 必填，生成签名的时间戳
        nonceStr: '<?php echo $nonceStr; ?>', // 必填，生成签名的随机串
        signature: '<?php echo $signature; ?>',// 必填，签名
        jsApiList: [
            'onMenuShareAppMessage',
            'chooseImage'
        ] // 必填，需要使用的JS接口列表
    });
    wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
        wx.onMenuShareAppMessage({
            title: '这首唐诗在加拿大火了，看了你可能不信！', // 分享标题
            desc: '最近，这首我们耳熟能详的《枫桥夜泊》在加拿大网友中火了一把，还出现了多种英文翻译版本', // 分享描述
            link: '<?php echo $wx->currentUrl();?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: 'https://inews.gtimg.com/newsapp_bt/0/8039145275/1000', // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
            // 用户点击了分享后执行的回调函数
            alert('成功')
            }
        });
});
    function fn(){
        wx.chooseImage({
            count: 1, // 默认9
            sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success: function (res) {
                var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                document.getElementById('img').src = localIds;
                }
            });
    }
</script>
</body>
</html>