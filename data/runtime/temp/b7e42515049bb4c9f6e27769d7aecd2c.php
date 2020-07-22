<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:43:"themes/backed/admin/online_video/index.html";i:1569392498;s:73:"/www/wwwroot/iliaoapp/publication/themes/backed/layout/iframe/header.html";i:1569392498;}*/ ?>
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
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/layui/css/layui.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/css/bootstrap.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/themes/base/css/index.css" id="admui-siteStyle">


    <!-- Site CSS -->
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/themes/base/css/site.css" id="admui-siteStyle1">

    <!-- 图标 CSS-->
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/fonts/font-awesome/font-awesome.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/fonts/web-icons/web-icons.css">

    <!-- 插件 CSS -->

    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/animsition/animsition.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/toastr/toastr.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/nprogress/nprogress.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/mCustomScrollbar/jquery.mCustomScrollbar.css">

    <!-- 插件 CSS -->

    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/select2/select2.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/datatables-bootstrap/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/vendor/datatables-responsive/dataTables.responsive.css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/css/system/log.css">
    <link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/css/admin/new-common.css">


    <!-- 插件 -->
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/jquery/jquery.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/lodash/lodash.min.js"></script>

    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/breakpoints/breakpoints.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/nprogress/nprogress.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/artTemplate/template-web.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/layui/layui.all.js"></script>

    <!-- 核心  -->
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/js/core.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/js/configs/site-configs.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/js/components.js"></script>


    <!-- 插件 -->
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/global/js/plugins/responsive-tabs.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/toastr/toastr.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/ashoverscroll/jquery-asHoverScroll.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/screenfull/screenfull.min.js"></script>

    <!-- 消息通知 -->
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/lodash/lodash.min.js"></script>

    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/matchheight/jquery.matchHeight.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/moment/moment.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/select2/select2.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/select2/i18n/zh-CN.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/datatables-bootstrap/dataTables.bootstrap4.js"></script>
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/vendor/datatables-responsive/dataTables.responsive.min.js"></script>

    <!--js配置 初始化-->
    <script src="http://admin.i.iliaozhibo.cn/assets/admui/themes/base/js/app.js"></script>

    <script>
        "use strict";

        Breakpoints();
    </script>
</head>



<!-- Page CSS -->
<link rel="stylesheet" href="http://admin.i.iliaozhibo.cn/assets/admui/css/system/log.css">
<!-- Page JS -->
<script src="http://admin.i.iliaozhibo.cn/assets/admui/js/admin/online_video.js"></script>
<style>
    .table thead th{
        text-align: center;
    }
    #logList>tbody>tr>td{
        text-align: center;
    }
    .details-control{
        position: relative;
    }
    .details-control i{
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -8px;
        margin-left: -8px;
        cursor: pointer;
    }
    .addTable td{
        text-align: left;
    }
    input,audio,button{
        outline: none;
    }
</style>
<body style="padding: 0">

<div class="page page-full animation-fade page-logs">
    <div class="page-header">
        <h1 class="page-title">1V1直播监控</h1>
        <div class="page-header-actions">
            <button type="button" class="btn btn-sm btn-icon btn-info btn-outline btn-round collapsed" data-toggle="collapse"
                    data-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                <i class="icon fa-filter"></i>
            </button>
        </div>
    </div>
    <div class="page-content">
        <div class="collapse show" id="collapseFilter" aria-expanded="true">
            <div class="panel">
                <div class="panel-body">
                    <form class="form-inline" id="logForm">
                        <div class="form-group">
                            <input type="text" class="form-control" name="keywords" id="keywords" placeholder="房间号" value="">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="icon fa-search"></i> 查找</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel">
            <header class="panel-heading">
                <h3 class="panel-title">直播列表</h3>
            </header>
            <div class="panel-body">
                <table class="table table-bordered table-hover dataTable table-striped w-full" id="logList">
                    <thead>
                    <tr>
                        <th>记录IP</th>
                        <th>房间号</th>
                        <th>用户</th>
                        <th>创建时间</th>
                        <th width="20%">操作</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    /*查看直播*/
    function VideoPopup(home_id) {
        $.ajax({
            type: 'POST',
            url: "/admin/online_video/ViewVideo",
            data: {home_id:home_id},
            dataType: "json",
            success: function(data){
                if(data.code === 200){
                    layer.open({
                        type: 2,    //0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
                        title: '直播中视频', //弹出框的标题
                        shade: 0.2,
                        maxmin: false, //开启最大化最小化按钮
                        area: ['100%','100%'],
                        content: "/admin/online_video/onlineVideo"+"?id="+home_id
                    });
                }else {
                    layer.alert(data.msg, {icon: 5});
                    var index=parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                }
            },
            error:function (data) {
                console.log(data);
                layer.alert('操作失败，请重新操作', {icon: 2});
            }

        });
        //return false;//阻止form表单提交
    }

    /*直播操作*/
    function offVideo($id) {
        layer.open({
            type: 2,    //0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
            title: '直播操作', //弹出框的标题
            shade: 0.2,
            maxmin: false, //开启最大化最小化按钮
            area: ['600px','300px'],
            content: "<?php echo url('OnlineVideo/setVideo'); ?>"+"?id="+$id
        });
        //return false;//阻止form表单提交
    }
</script>
</body>
</html>



