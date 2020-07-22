<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:40:"themes/backed/admin\vip_order\index.html";i:1569392498;s:64:"E:\www\iliao\publication\themes\backed\layout\iframe\header.html";i:1569392498;}*/ ?>
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
    <link rel="icon" type="image/png" href="http://www.liao.com/assets/admui/images/favicon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <!-- Safari浏览器添加到主屏幕（IOS） -->
    <link rel="icon" sizes="192x192" href="http://www.liao.com/assets/admui/images/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Admui">
    <!-- Win8标题栏及ICON图标 -->
    <link rel="apple-touch-icon-precomposed" href="http://www.liao.com/assets/admui/images/apple-touch-icon.png">
    <meta name="msapplication-TileImage" content="http://www.liao.com/assets/admui/images/app-icon72x72@2x.png">
    <meta name="msapplication-TileColor" content="#62a8ea">

    <!--[if lte IE 9]>
    <meta http-equiv="refresh" content="0; url='http://www.liao.com/ie'" />
    <![endif]-->
    <!--[if lt IE 10]>
    <script src="http://www.liao.com/assets/admui/vendor/media-match/media.match.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/respond/respond.min.js"></script>
    <![endif]-->

    <!-- 样式 -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/layui/css/layui.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/themes/global/css/bootstrap.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/themes/base/css/index.css" id="admui-siteStyle">


    <!-- Site CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/themes/base/css/site.css" id="admui-siteStyle1">

    <!-- 图标 CSS-->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/fonts/font-awesome/font-awesome.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/fonts/web-icons/web-icons.css">

    <!-- 插件 CSS -->

    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/animsition/animsition.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/toastr/toastr.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/nprogress/nprogress.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/mCustomScrollbar/jquery.mCustomScrollbar.css">

    <!-- 插件 CSS -->

    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/select2/select2.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/datatables-bootstrap/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/datatables-responsive/dataTables.responsive.css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/css/system/log.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/css/admin/new-common.css">


    <!-- 插件 -->
    <script src="http://www.liao.com/assets/admui/vendor/jquery/jquery.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/lodash/lodash.min.js"></script>

    <script src="http://www.liao.com/assets/admui/vendor/breakpoints/breakpoints.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/nprogress/nprogress.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/artTemplate/template-web.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/layui/layui.all.js"></script>

    <!-- 核心  -->
    <script src="http://www.liao.com/assets/admui/themes/global/js/core.js"></script>
    <script src="http://www.liao.com/assets/admui/themes/global/js/configs/site-configs.js"></script>
    <script src="http://www.liao.com/assets/admui/themes/global/js/components.js"></script>


    <!-- 插件 -->
    <script src="http://www.liao.com/assets/admui/themes/global/js/plugins/responsive-tabs.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/toastr/toastr.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/ashoverscroll/jquery-asHoverScroll.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/screenfull/screenfull.min.js"></script>

    <!-- 消息通知 -->
    <script src="http://www.liao.com/assets/admui/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/lodash/lodash.min.js"></script>

    <script src="http://www.liao.com/assets/admui/vendor/matchheight/jquery.matchHeight.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/moment/moment.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/select2/select2.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/select2/i18n/zh-CN.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/datatables-bootstrap/dataTables.bootstrap4.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/datatables-responsive/dataTables.responsive.min.js"></script>

    <!--js配置 初始化-->
    <script src="http://www.liao.com/assets/admui/themes/base/js/app.js"></script>

    <script>
        "use strict";

        Breakpoints();
    </script>
</head>



<!-- Page JS -->
<script src="http://www.liao.com/assets/admui/js/admin/vip_order.js"></script>
<style>
    .table thead th{
        text-align: center;
    }
    #logList>tbody>tr>td{
        line-height: 40px;
        text-align: center;
    }
    #logList>tbody>tr>td:last-child{
        position: relative;
    }
    #logList>tbody>tr>td:last-child>button{
        position: absolute;
        display: block;
        top:50%;
        left:50%;
        margin-top: -13px;
        margin-left: -33px;
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
    #logList>tbody>tr>td>.to_table>tbody>tr>td{
        line-height: 40px;
        text-align: left;
    }
</style>
<body data-theme="base" style="padding: 0">

<div class="page page-full animation-fade page-logs">
    <div class="page-header">
        <h1 class="page-title">Vip订单管理</h1>
        <div class="page-header-actions">
            <button type="button" class="btn btn-sm btn-icon btn-info btn-outline btn-round collapsed" data-toggle="collapse"
                    data-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                <i class="icon fa-filter"></i>
            </button>
        </div>
    </div>
    <div class="page-content">
        <div class="collapse" id="collapseFilter" aria-expanded="true">
            <div class="panel">
                <div class="panel-body">
                    <form class="form-inline" id="logForm">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="icon wb-calendar" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="filter-date" placeholder="选择日期范围" autocomplete="off">
                                <input type="hidden" name="startDate" value="" id="startTime">
                                <input type="hidden" name="endDate" value="" id="endTime">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-icon btn-default btn-outline btn-sm date-clear">
                                        <i class="icon wb-close" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="pay_channel" data-plugin="select1" title="">
                                <option value="">选择支付渠道</option>
                                <?php 
                                    if(isset($pay_conf) && !empty($pay_conf)){
                                    foreach($pay_conf as $key=>$item){
                                 ?>
                                    <option value="<?php echo $key; ?>"><?php echo $item; ?></option>
                                <?php 
                                    }
                                    }
                                 ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="status" data-plugin="select2" title="">
                                <option value="">选择支付状态</option>
                                <?php 
                                    if(isset($status) && !empty($status)){
                                        foreach($status as $key=>$item){
                                 ?>
                                    <option value="<?php echo $key; ?>"><?php echo $item; ?></option>
                                <?php 
                                        }
                                    }
                                 ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="keywords" id="keywords" placeholder="订单号/手机/UID" value="">
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
            <div class="panel-body">
                <table class="table table-bordered table-hover dataTable table-striped w-full" id="logList">
                    <thead>
                    <tr>
                        <th width="5%"></th>
                        <th>序号</th>
                        <th>订单号</th>
                        <th>用户ID</th>
                        <th>金额(元)</th>
                        <th>标题</th>
                        <th>订单生成时间</th>
                        <th>状态</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>



