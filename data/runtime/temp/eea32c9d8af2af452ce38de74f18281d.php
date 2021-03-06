<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:37:"themes/backed/admin\public\login.html";i:1569392498;}*/ ?>

<!DOCTYPE html>
<html class="no-js css-menubar" lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- 移动设备 viewport -->
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
    <meta name="author" content="xchat.com">
    <!-- 360浏览器默认使用Webkit内核 -->
    <meta name="renderer" content="webkit">
    <!-- 禁止搜索引擎抓取 -->
    <meta name="robots" content="nofollow">
    <!-- 禁止百度SiteAPP转码 -->
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <!-- Chrome浏览器添加桌面快捷方式（安卓） -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/png" href="http://www.liao.com/assets/admui/images/favicon.png">
    <!-- Safari浏览器添加到主屏幕（IOS） -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Admui">
    <link rel="icon" sizes="192x192" href="http://www.liao.com/assets/admui/images/apple-touch-icon.png">

    <!-- Win8标题栏及ICON图标 -->
    <meta name="msapplication-TileColor" content="#62a8ea">
    <meta name="msapplication-TileImage" content="http://www.liao.com/assets/admui/images/app-icon72x72@2x.png">
    <link rel="apple-touch-icon-precomposed" href="http://www.liao.com/assets/admui/images/apple-touch-icon.png">

    <!--[if lte IE 9]>
    <meta http-equiv="refresh" content="0; url='http://www.admui.com/ie'"/>
    <![endif]-->

    <title>直播管理后台</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/themes/global/css/bootstrap.css">

    <!-- 字体图标 CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/fonts/web-icons/web-icons.css">

    <!-- Site CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/themes/base/css/site.css" id="admui-siteStyle">

    <!-- 插件 CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/animsition/animsition.css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/css/login.css">
<script>
    // 当前页面在iframe中时
    if(window.top !== window.self){
        window.top.location.reload(true);
    }
</script>

    <!--[if lt IE 10]>
    <script src="http://www.liao.com/assets/admui/vendor/media-match/media.match.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/respond/respond.min.js"></script>
    <![endif]-->

</head>
<body class="page-login layout-full page-dark">

<div class="page h-full">
    <div class="page-content h-full">
        <div class="page-login-main animation-fade">
            <div class="vertical-align">
                <div class="vertical-align-middle">
                    <div class="brand text-center">
                        <img class="" src="http://www.liao.com/assets/admui/images/admin_logo.png" height="50" alt="Admui">
                    </div>
                    <form class="login-form" action="/admin/public/dologin.html" method="post" id="loginForm">
                        <div class="form-group">
                            <label class="sr-only" for="username">用户名</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="请输入用户名">
                        </div>
                        <div class="form-group">
                            <label class="sr-only" for="password">密码</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码">
                        </div>
                        <div class="form-group">
                            <label class="sr-only" for="password">验证码</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="captcha" placeholder="请输入验证码">
                                <div class="input-group-append">
                                    <a href="javascript:void(0) ;" class="btn btn-default btn-outline p-0 m-0 reload-vify">
                                        <img src="/captcha/new.html?height=32&width=150&font_size=18" height="40">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <div class="checkbox-custom checkbox-inline checkbox-primary float-left">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">自动登录</label>
                            </div>
                            <a class="float-right collapsed" data-toggle="collapse" href="#forgetPassword"
                               aria-expanded="false" aria-controls="forgetPassword">
                                忘记密码了？
                            </a>
                        </div>
                        <div class="collapse" id="forgetPassword" aria-expanded="true">
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                请联系管理员重置密码。
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">立即登录</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 插件 -->
<script src="http://www.liao.com/assets/admui/vendor/jquery/jquery.min.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/jquery-validation/jquery.validate.min.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/jquery-validation/localization/messages_zh.js"></script>

<!-- Page JS -->
<script src="http://www.liao.com/assets/admui/js/login.js"></script>
</body>
</html>