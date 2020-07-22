<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:36:"themes/backed/admin/index/index.html";i:1571130914;s:72:"/www/wwwroot/iliaoapp/publication/themes/backed/layout/index/layout.html";i:1569392498;s:72:"/www/wwwroot/iliaoapp/publication/themes/backed/layout/index/header.html";i:1569392498;s:72:"/www/wwwroot/iliaoapp/publication/themes/backed/layout/index/footer.html";i:1569392498;}*/ ?>
<!DOCTYPE html>
<html class="no-js css-menubar" lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?php echo (isset($title) && ($title !== '')?$title: '直播管理后台'); ?></title>
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
    <link rel="icon" type="image/png" href="http://admin.i.iliaozhibo.cn/assets/admui/images/favicon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <!-- Safari浏览器添加到主屏幕（IOS） -->
    <link rel="icon" sizes="192x192" href="http://admin.i.iliaozhibo.cn/assets/admui/images/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Admui">
    <!-- Win8标题栏及ICON图标 -->
    <link rel="apple-touch-icon-precomposed" href="http://admin.i.iliaozhibo.cn/assets/admui/images/apple-touch-icon.png">
    <meta name="msapplication-TileImage" content="http://admin.i.iliaozhibo.cn/assets/admui/images/app-icon72x72@2x.png">
    <meta name="msapplication-TileColor" content="#62a8ea">

    <!--[if lte IE 9]>
    <meta http-equiv="refresh" content="0; url='http://admin.i.iliaozhibo.cn/ie'" />
    <![endif]-->
    <!--[if lt IE 10]>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/media-match/media.match.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/respond/respond.min.js"></script>
    <![endif]-->

    <!-- 样式 -->
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/css/bootstrap.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/themes/base/css/index.css" id="admui-siteStyle">

    <!-- 图标 CSS-->
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/fonts/font-awesome/font-awesome.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/fonts/web-icons/web-icons.css">

    <!-- 插件 CSS -->
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/animsition/animsition.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/toastr/toastr.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/nprogress/nprogress.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/mCustomScrollbar/jquery.mCustomScrollbar.css">

    <!-- 插件 -->
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/jquery/jquery.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/lodash/lodash.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/breakpoints/breakpoints.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/nprogress/nprogress.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/artTemplate/template-web.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/layer/layer.min.js"></script>

    <!-- 核心  -->
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/js/core.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/js/configs/site-configs.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/js/components.js"></script>

    <!-- 布局 -->
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/base/js/site.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/base/js/sections/content-tabs.js"></script>

    <script>
        "use strict";

        Breakpoints();
    </script>

</head>
<body>



<body class="site-menubar-unfold site-contabs-open" data-theme="base">
    <!--头部导航  主菜单-->
    <nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-inverse" id="admui-siteNavbar"
         role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided"
                    data-toggle="menubar">
                <span class="sr-only">切换菜单</span>
                <span class="hamburger-bar"></span>
            </button>
            <button type="button" class="navbar-toggler collapsed" data-target="#admui-navbarCollapse"
                    data-toggle="collapse">
                <i class="icon wb-more-horizontal" aria-hidden="true"></i>
            </button>
            <div class="navbar-brand navbar-brand-center site-gridmenu-toggle " data-toggle="gridmenu">
                <a href="http://admin.i.iliaozhibo.cn" >
                    <img class="navbar-brand-logo d-sm-block d-lg-block d-none navbar-logo"
                         src="http://admin.i.iliaozhibo.cn/assets/admui/images/admin_index_logo.png" title="Xchat">
                </a>

                <!--<img class="navbar-brand-logo d-sm-block d-lg-block d-none navbar-logo"
                     src="http://admin.i.iliaozhibo.cn/assets/admui/images/logo-white.svg" title="Xchat">
                <img class="navbar-brand-logo d-sm-none navbar-logo-mini" src="http://admin.i.iliaozhibo.cn/assets/admui/images/logo-white-min.svg"
                     title="Xchat">-->
            </div>
        </div>

        <div class="navbar-container container-fluid">
            <div class="collapse navbar-collapse navbar-collapse-toolbar" id="admui-navbarCollapse">
                <ul class="nav navbar-toolbar navbar-left">
                    <li class="nav-item hidden-float" id="toggleMenubar">
                        <a class="nav-link" data-toggle="menubar" href="javascript:void (0);" role="button"
                           id="admui-toggleMenubar">
                            <i class="icon hamburger hamburger-arrow-left">
                                <span class="sr-only">切换目录</span>
                                <span class="hamburger-bar"></span>
                            </i>
                        </a>
                    </li>
                    <li class="navbar-menu nav-tabs-horizontal nav-tabs-animate is-load" id="admui-navMenu">
                        <ul class="nav navbar-toolbar nav-tabs" role="tablist">
                            <?php if(!(empty($submenus) || (($submenus instanceof \think\Collection || $submenus instanceof \think\Paginator ) && $submenus->isEmpty()))): foreach($submenus as $menu){ ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $menu['key']==1?'active':''; ?>" data-toggle="tab" href="#admui-navTabsItem-<?php echo $menu['key']; ?>"
                                       aria-controls="admui-navTabsItem-<?php echo $menu['key']+1; ?>" role="tab" aria-expanded="false">
                                        <i class="icon <?php echo $menu['icon']; ?>"></i>
                                        <span><?php echo $menu['name']; ?></span>
                                    </a>
                                </li>
                                <?php } $values = !empty($menu['items']) ? $menu['items'] : ''; endif; ?>
                            <!--导航栏更多选择-->
                           <!-- <li class="nav-item dropdown" id="admui-navbarSubMenu">
                                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="javascript:void (0);"
                                   data-animation="slide-bottom">
                                    更多
                                </a>
                                <div class="dropdown-menu">
                                <a class="dropdown-item no-menu" href="/html/site-map.html" target="_blank">
                                    <i class="icon wb-list-numbered"></i>
                                    <span>网站地图</span>
                                </a>
                                <a class="dropdown-item no-menu" href="/admin/system/menu" target="_blank">
                                    <i class="icon wb-wrench"></i>
                                    <span>系统管理</span>
                                </a>
                            </div>
                            </li>-->
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                    <li class="nav-item dropdown" id="admui-navbarUser">
                        <a class="nav-link navbar-avatar" data-toggle="dropdown" href="javascript:void (0);" aria-expanded="false"
                           data-animation="scale-up"
                           role="button">
                            <span class="avatar avatar-online">
                                <img src="http://admin.i.iliaozhibo.cn/assets/admui/images/avatar.svg" alt="...">
                                <i></i>
                            </span>
                        </a>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item" href="/admin/index/headerNav?tab=display" target="_blank" role="menuitem">
                                <i class="icon wb-layout" aria-hidden="true"></i>
                                <span>显示设置</span>
                            </a>
                            <a class="dropdown-item" href="/admin/index/headerNav?tab=password" target="_blank" role="menuitem">
                                <i class="icon wb-pencil" aria-hidden="true"></i>
                                <span>修改密码</span>
                            </a>
                            <a class="dropdown-item" href="/admin/index/headerNav?tab=message" target="_blank" role="menuitem">
                                <i class="icon wb-settings" aria-hidden="true"></i>
                                <span>账户信息</span>
                            </a>
                            <div class="dropdown-divider" role="presentation"></div>
                            <a class="dropdown-item" id="admui-signOut" data-ctx="" href="/admin/public/logout" role="menuitem">
                                <i class="icon wb-power"></i>
                                <span>退出</span>
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown" id="admui-navbarMessage">
                        <a class="nav-link msg-btn" data-toggle="dropdown" href="javascript:void (0);" aria-expanded="false"
                           data-animation="scale-up" role="button">
                            <?php if(empty($msgList)){ ?>
                                <img src="/assets/admin/empty_message.png" alt="" width="20px" height="20px">
                            <?php }else{ ?>
                                <img src="/assets/admin/as_message.png" alt="" width="20px" height="20px">
                            <?php } ?>
                            <span class="badge badge-danger up msg-num"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right dropdown-menu-media" role="menu">
                            <li class="dropdown-menu-header" role="presentation">
                                <h5>最新消息</h5>
                                <span class="badge badge-round label-danger"></span>
                            </li>
                            <li class="list-group" role="presentation">

                                <?php if(empty($msgList)){ ?>
                                <p class="text-center h-200 vertical-align">
                                    <small class="vertical-align-middle opacity-four">没有新消息</small>
                                </p>
                                <?php } else { foreach($msgList as $key => $item){ if($key < 3){ ?>
                                        <a class="list-group-item" href="javascript:void (0)" data-message-id="<?php echo $item['id']; ?>" onclick="alreadyRead(<?php echo $item['id']; ?>,0)"
                                           data-title="<?php echo $item['title']; ?>" data-content="<?php echo $item['content']; ?>" role="menuitem">
                                            <div class="media">
                                                <div class="pr-10">
                                                    <i class="icon wb-medium-point red-600" aria-hidden="true"></i>
                                                </div>
                                                <div class="media-body">
                                                    <h6 class="media-heading"><?php echo $item['title']; ?></h6>
                                                    <time class="media-meta" datetime="<?php echo $item['create_time']; ?>">
                                                        <?php echo date("Y-m-d H:i:s",$item['create_time']); ?>
                                                    </time>
                                                </div>
                                            </div>
                                        </a>
                                        <?php } } } ?>
                            </li>
                            <div class="dropdown-menu-footer" role="presentation">
                                <a class="dropdown-item" href="/admin/index/headerNav?tab=message" target="_blank" role="menuitem">
                                    <i class="icon fa-navicon"></i>所有消息
                                </a>
                            </div>
                        </ul>
                    </li>
                    <!--<li class="nav-item d-none d-sm-block" id="admui-navbarDisplay" data-toggle="tooltip"
                        data-placement="bottom" title="设置主题与布局等">
                        <a class="nav-link" href="/html/system/settings/display.html" target="_blank">
                            <i class="icon wb-layout"></i>
                            <span class="sr-only">主题与布局</span>
                        </a>
                    </li>-->
                    <li class="nav-item d-none d-sm-block" id="admui-navbarFullscreen" data-toggle="tooltip"
                        data-placement="bottom" title="全屏">
                        <a class="nav-link" data-toggle="fullscreen" href="javascript:void (0);" role="button">
                            <i class="icon icon-fullscreen"></i>
                            <span class="sr-only">全屏</span>
                        </a>
                    </li>
                    <li class="nav-item d-none d-sm-block" id="admui-navbarClear" data-toggle="tooltip"
                        data-placement="bottom" title="清除缓存">
                        <a class="nav-link" href="javascript:void (0)">
                            <i class="icon fa-trash"></i>
                            <span class="sr-only">清除缓存</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 右侧栏菜单 START -->
    <nav class="site-menubar" id="admui-siteMenubar">
        <div class="site-menubar-body">
            <div class="tab-content h-full" id="admui-navTabs">
                <!--左侧菜单栏数据遍历-->
                <?php if(!(empty($submenus) || (($submenus instanceof \think\Collection || $submenus instanceof \think\Paginator ) && $submenus->isEmpty()))): if(is_array($submenus) || $submenus instanceof \think\Collection || $submenus instanceof \think\Paginator): if( count($submenus)==0 ) : echo "" ;else: foreach($submenus as $key=>$menus): ?>
                        <div class="tab-pane animation-fade h-full <?php echo $menus['key']==1?'active':''; ?>" id="admui-navTabsItem-<?php echo $menus['key']; ?>" role="tabpanel">
                            <ul class="site-menu">
                                <li class="site-menu-category"></li>
                                <?php if(!empty($menus['items'])){ foreach($menus['items'] as $value){; if(!empty($value['items'])){ ?>
                                        <li class="site-menu-item has-sub">
                                            <a href="javascript:void (0);">
                                                <i class="site-menu-icon <?php echo $value['icon']; ?>" aria-hidden="true"></i>
                                                <span class="site-menu-title"><?php echo $value['name']; ?></span>
                                                <span class="site-menu-arrow"></span>
                                            </a>
                                            <ul class="site-menu-sub">
                                                <?php if(!empty($value['items'])){ foreach($value['items'] as $val){ ?>
                                                        <li class="site-menu-item">
                                                            <a href="<?php echo $val['url']; ?>" target="_blank">
                                                                <span class="site-menu-title"><?php echo $val['name']; ?></span>
                                                            </a>
                                                        </li>
                                                    <?php } } ?>
                                            </ul>
                                        </li>
                                    <?php }else{ ?>
                                        <li class="site-menu-item">
                                            <a href="<?php echo $value['url']; ?>" target="_blank">
                                                <i class="site-menu-icon <?php echo $value['icon']; ?>" aria-hidden="true"></i>
                                                <span class="site-menu-title"><?php echo $value['name']; ?></span>
                                            </a>
                                        </li>
                                    <?php } } } ?>
                            </ul>
                        </div>
                    <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                <!--左侧菜单栏数据遍历 end-->
            </div>
        </div>
    </nav>
    <!-- 右侧栏菜单 END -->

    <!--内容区菜单栏 START-->
    <nav class="site-contabs" id="admui-siteConTabs">
        <button type="button" class="btn btn-icon btn-default float-left hide" id="admui-tabL">
            <i class="icon fa-angle-double-left"></i>
        </button>
        <div class="contabs-scroll float-left">
            <ul class="nav con-tabs">
                <li>
                    <a href="/admin/index/home" target="frame-0" title="首页"><span>首页</span></a>
                </li>
            </ul>
        </div>
        <div class="btn-group float-right">
            <button type="button" class="btn btn-icon btn-default hide" id="admui-tabR">
                <i class="icon fa-angle-double-right"></i>
            </button>
            <button type="button" class="btn btn-default dropdown-toggle btn-outline" data-toggle="dropdown" aria-expanded="false">
                <span class="caret"></span> <span class="sr-only">切换菜单</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="conTabsDropdown" role="menu">
                <a class="dropdown-item reload-page" href="javascript:void (0);" role="menuitem"><i class="icon wb-reload"></i> 刷新当前</a>
                <a class="dropdown-item close-other" href="javascript:void (0);" role="menuitem"><i class="icon wb-close"></i> 关闭其他</a>
                <a class="dropdown-item close-all" href="javascript:void (0);" role="menuitem"><i class="icon wb-power"></i> 关闭所有</a>
            </div>
        </div>
    </nav>
    <!--内容区菜单栏 END -->

    <!--内容区 START-->
    <main class="site-page">
        <div class="page-container" id="admui-pageContent">
            <iframe src="" frameborder="0" id="admui-pageContent1" name="frame-0" class="page-frame animation-fade"></iframe>
        </div>
        <div class="page-loading vertical-align text-center">
            <div class="page-loader loader-default loader vertical-align-middle" data-type="default"></div>
        </div>
    </main>
    <!--内容区 END -->
</body>
<script>
    var status = true;
    DiGui = function (param) {
        $.ajax({
            type: 'POST',
            url: "<?php echo url('Index/getMessageNumber');; ?>",
            dataType: "json",
            data: {},
            success: function (data) {
                var message_length = data.num;
                if (data.code === 200 && message_length > 0) {
                    layer.msg('您有' +message_length +'条未读消息',{time:5000,offset:'80px'});
                }
                return  false;
            }
        })
    };
    if (status) {
        window.setInterval("DiGui()", 30000);
    }
    DiGui();



    $(".site-menu>li").click(function () {
       $(this).addClass('active');
       $(this).siblings('li').removeClass('active');
    });
    $("#admui-navbarClear").on('click',function () {
        layer.confirm('确定要清除缓存吗？', {icon: 3, title:'提示'}, function(index) {
            $.ajax({
                type: 'POST',
                url: "<?php echo url('ClearCache/action');; ?>",
                data: {},
                dataType: "json",
                success: function (data) {
                    if (data.code === 200) {
                        layer.alert('清除缓存成功', {icon: 5}, function () {
                            window.parent.location.reload();
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        });
                    } else {
                        layer.alert(data.msg, {icon: 7}, function () {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        });
                    }
                },
                error: function (data) {
                    layer.alert('操作失败，请重新操作', {icon: 2});
                }
            })
        })
    });

    //已读
    function alreadyRead(id,type){
        $.ajax({
            type: 'POST',
            url: "<?php echo url('UserCenter/alreadyRead');; ?>",
            data: {
                id: id,
                type:type
            },
            dataType: "json",
            success: function(data){
                if(data.code === 200){
                    layer.msg('已读成功', {icon: 5});
                }else{
                    layer.msg('已读失败');
                }
                window.parent.location.reload();
            },
            error:function (data) {
                layer.alert('操作失败，请重新操作', {icon: 7});
            }

        });
    }
</script>

<footer class="site-footer">
    <div class="site-footer-right">
        直播管理后台 - 当前版本：v1.0.0
        <a class="ml-5" data-toggle="tooltip" title="购买或升级" href="http://admin.i.iliaozhibo.cn" target="_blank">
            <i class="icon fa-cloud-upload"></i>
        </a>
    </div>
</footer>

<!-- 插件 -->
<script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/js/plugins/responsive-tabs.js"></script>

<script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/toastr/toastr.min.js"></script>
<script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/ashoverscroll/jquery-asHoverScroll.min.js"></script>
<script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/screenfull/screenfull.min.js"></script>

<!-- 消息通知 -->
<!--<script src="http://admin.i.iliaozhibo.cn/assets/admui/js/notify-msg.js"></script>-->
</body>
</html>

