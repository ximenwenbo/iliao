
<!DOCTYPE html>
<html class="no-js css-menubar" lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- 移动设备 viewport -->
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
    <meta name="author" content="admui.com">
    <!-- 360浏览器默认使用Webkit内核 -->
    <meta name="renderer" content="webkit">
    <!-- 禁止搜索引擎抓取 -->
    <meta name="robots" content="nofollow">
    <!-- 禁止百度SiteAPP转码 -->
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <!-- Chrome浏览器添加桌面快捷方式（安卓） -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/png" href="__ABS__/assets/admui/images/favicon.png">
    <!-- Safari浏览器添加到主屏幕（IOS） -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Admui">
    <link rel="icon" sizes="192x192" href="__ABS__/assets/admui/images/apple-touch-icon.png">

    <!-- Win8标题栏及ICON图标 -->
    <meta name="msapplication-TileColor" content="#62a8ea">
    <meta name="msapplication-TileImage" content="__ABS__/assets/admui/images/app-icon72x72@2x.png">
    <link rel="apple-touch-icon-precomposed" href="__ABS__/assets/admui/images/apple-touch-icon.png">

    <!--[if lte IE 9]>
    <meta http-equiv="refresh" content="0; url='http://www.admui.com/ie'"/>
    <![endif]-->

    <title>出错了- Xchat</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="__ABS__/assets/admui/themes/global/css/bootstrap.css">

    <!-- 字体图标 CSS -->
    <link rel="stylesheet" href="__ABS__/assets/admui/fonts/web-icons/web-icons.css">

    <!-- Site CSS -->
    <link rel="stylesheet" href="__ABS__/assets/admui/themes/base/css/site.css" id="admui-siteStyle">

    <!-- 插件 CSS -->


    <!-- Page CSS -->
    <link rel="stylesheet" href="__ABS__/assets/admui/css/errors.css">

    <!--[if lt IE 10]>
    <script src="__ABS__/assets/admui/vendor/media-match/media.match.min.js"></script>
    <script src="__ABS__/assets/admui/vendor/respond/respond.min.js"></script>
    <![endif]-->

</head>
<body class="page-errors layout-full">
<div class="site-page">
    <div class="page page-full vertical-align text-center animation-fade page-error">
        <div class="page-content vertical-align-middle">
            <!--<div class="brand text-center">
                <img class="" src="__ABS__/assets/admui/images/logo.svg" height="50" alt="Admui" style="padding-bottom: 0.5rem;margin-bottom: 0.3rem">
            </div>*}-->
            <header>
                <h1 class="animation-slide-top">error</h1>
                <p style="font-size: 1.3rem;text-align: center"><?php echo isset($msg) ? strip_tags($msg) : '找不到您访问的页面';?></p>
            </header>
            <p class="jump">
                页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
            </p>
            <footer class="page-copyright">
                <p>&copy;2016
                    <a href="http://admui.com" target="_blank">直播系统</a>
                </p>
            </footer>
        </div>
    </div>
</div>

<!-- 插件 -->


<!-- Page JS -->
<script src="__ABS__/assets/admui/js/error.js"></script>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            }
        }, 1000);
    })();
</script>
</body>
</html>