<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:35:"themes/backed/admin\index\home.html";i:1569392498;}*/ ?>

<!DOCTYPE html>
<html class="no-js css-menubar" lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>首页统计</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- 移动设备 viewport -->
    <meta name="viewport"
          content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
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
    <meta http-equiv="refresh" content="0; url='http://www.admui.com/ie'"/>
    <![endif]-->
    <!--<script src="http://www.liao.com/assets/admui/vendor/morris/morris.css"></script>-->
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/themes/global/css/bootstrap.css">
    
    <!-- 字体图标 CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/fonts/web-icons/web-icons.css">
    
    <!-- Site CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/themes/base/css/site.css" id="admui-siteStyle">
    
    <!-- 插件 CSS -->
    
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/chartist/chartist.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.css">
    
    <!-- Page CSS -->
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/css/examples/pages/home/v1.css">
    <link rel="stylesheet" href="http://www.liao.com/assets/admui/css/admin/home.css">

    <!-- 插件 -->
    <script src="http://www.liao.com/assets/admui/vendor/jquery/jquery.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="http://www.liao.com/assets/admui/vendor/lodash/lodash.min.js"></script>

</head>
<body data-theme="base">

<div class="page animation-fade page-index-v1">
    <div class="page-header">
        <h1 class="page-title">网站概况</h1>
    </div>
    <div class="page-content container-fluid">
        <div class="row" data-plugin="matchHeight" data-by-row="true">
            <div class="col-xxl-7 col-lg-7 dis_ab">
                <div class="card card-shadow widget-responsive" id="widgetLineareaColor">
                    <div class="widget-content">
                        <div class="pt-30 p-30" style="height:calc(100% - 250px);">
                            <div class="row">
                                <div class="col-7">
                                    <p class="font-size-20 blue-grey-700">能量预测</p>
                                    <p>基础数据来源于胡编乱造网</p>
                                    <div class="counter counter-md text-left">
                                        <div class="counter-number-group">
                                            <span class="counter-icon red-600">
                                                <i class="icon wb-triangle-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="counter-number red-600">2,250</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="float-right clearfix">
                                        <ul class="list-unstyled">
                                            <li class="mb-5 text-truncate">
                                                <i class="icon wb-medium-point red-600 mr-5" aria-hidden="true"></i>
                                                膳食摄入量
                                            </li>
                                            <li class="mb-5 text-truncate">
                                                <i class="icon wb-medium-point orange-600 mr-5" aria-hidden="true"></i>
                                                运动
                                            </li>
                                            <li class="mb-5 text-truncate">
                                                <i class="icon wb-medium-point green-600 mr-5" aria-hidden="true"></i>
                                                其他
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ct-chart h-250"></div>
                    </div>
                </div>

            </div>
            <div class="col-xxl-5 col-lg-5 dis_ab">
            <div class="card card-shadow" id="widgetStackedBar">
                <div class="widget-content">
                    <div class="p-30 h-150">
                        <p>市场份额</p>
                        <div class="red-600">
                            <i class="wb-triangle-up font-size-20 mr-5"></i>
                            <span class="font-size-30">26,580.62</span>
                        </div>
                    </div>
                    <div class="counters pb-20 px-30" style="height:calc(100% - 350px);">
                        <div class="row no-space">
                            <div class="col-4">
                                <div class="counter counter-sm">
                                    <div class="counter-label text-uppercase">阿里巴巴</div>
                                    <div class="counter-number-group text-truncate">
                                        <span class="counter-number-related green-600">+</span>
                                        <span class="counter-number green-600">82.24</span>
                                        <span class="counter-number-related green-600">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="counter counter-sm">
                                    <div class="counter-label text-uppercase">腾讯</div>
                                    <div class="counter-number-group text-truncate">
                                        <span class="counter-number-related red-600">-</span>
                                        <span class="counter-number red-600">12.06</span>
                                        <span class="counter-number-related red-600">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="counter counter-sm">
                                    <div class="counter-label text-uppercase">百度</div>
                                    <div class="counter-number-group text-truncate">
                                        <span class="counter-number-related green-600">+</span>
                                        <span class="counter-number green-600">24.86</span>
                                        <span class="counter-number-related green-600">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ct-chart h-200"></div>
                </div>
            </div>

        </div>

            <!--昨日今日统计-->
            <div class="col-xxl-12 col-lg-12">
                <div id="productOverviewWidget" class="card card-shadow">
                    <div class="card-header card-header-transparent p-20">
                        <ul class="nav nav-pills nav-pills-rounded product-filters" id="chartViewNav">
                            <li class="nav-item">
                                <a class="nav-link" href="#scoreLineDay" data-toggle="tab">昨日</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="#scoreLineToDay" data-toggle="tab">本日</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#scoreLineToWeek" data-toggle="tab">本周</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#scoreLineToMonth" data-toggle="tab">本月</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-block p-20">
                        <div class="tab-content">
                            <div class="ct-chart tab-pane" id="scoreLineDay">
                                <div class="text-center">
                                    <div class="row no-space">
                                        <div class="col-lg-3 col-sm-6 col-xs-12">
                                            <div class="counter">
                                                <div class="counter-label"><i class="icon wb-user"> 注册用户</i></div>
                                                <div class="counter-number-group text-truncate">
                                                    <span class="counter-number"><?php echo $data[0]['member']; ?></span>
                                                </div>
                                                <div class="ct-chart" data-counter-type="productVist"></div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-6 col-xs-12">
                                            <div class="counter">
                                                <div class="counter-label"><i class="icon fa-yen"> 充值金币</i></div>
                                                <div class="counter-number-group text-truncate">
                                                    <span class="counter-number"><?php echo $data[0]['money']; ?></span>
                                                </div>
                                                <div class="ct-chart" data-counter-type="productVistors"></div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-6 col-xs-12">
                                            <div class="counter">
                                                <div class="counter-label"><i class="site-menu-icon wb-order" aria-hidden="true"></i> 充值vip</div>
                                                <div class="counter-number-group text-truncate">
                                                    <span class="counter-number"><?php echo $data[0]['vip']; ?></span>
                                                </div>
                                                <div class="ct-chart" data-counter-type="productPageViews"></div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-6 col-xs-12">
                                            <div class="counter">
                                                <div class="counter-label">
                                                    <i class="icon fa-cart-plus" aria-hidden="true"></i>
                                                    <span class="site-menu-title">消费金币</span>
                                                </div>
                                                <div class="counter-number-group text-truncate">
                                                    <span class="counter-number"><?php echo $data[0]['monetary']; ?></span>
                                                </div>
                                                <div class="ct-chart" data-counter-type="productBounceRate"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="ct-chart tab-pane active" id="scoreLineToDay">
                                <div class="row no-space">

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="icon wb-user"> 注册用户</i></div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[1]['member']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productVist"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="icon fa-yen"> 充值金币</i></div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[1]['money']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productVistors"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="site-menu-icon wb-order" aria-hidden="true"></i> 充值vip</div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[1]['vip']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productPageViews"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label">
                                                <i class="icon fa-cart-plus" aria-hidden="true"></i>
                                                <span class="site-menu-title">消费金币</span>
                                            </div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[1]['monetary']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productBounceRate"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="ct-chart tab-pane" id="scoreLineToWeek">
                                <div class="row no-space">

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="icon wb-user"> 注册用户</i></div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[2]['member']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productVist"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="icon fa-yen"> 充值金币</i></div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[2]['money']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productVistors"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="site-menu-icon wb-order" aria-hidden="true"></i> 充值vip</div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[2]['vip']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productPageViews"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label">
                                                <i class="icon fa-cart-plus" aria-hidden="true"></i>
                                                <span class="site-menu-title">消费金币</span>
                                            </div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[2]['monetary']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productBounceRate"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="ct-chart tab-pane" id="scoreLineToMonth">
                                <div class="row no-space">

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="icon wb-user"> 注册用户</i></div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[3]['member']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productVist"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="icon fa-yen"> 充值金币</i></div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[3]['money']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productVistors"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label"><i class="site-menu-icon wb-order" aria-hidden="true"></i> 充值vip</div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[3]['vip']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productPageViews"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-6 col-xs-12">
                                        <div class="counter">
                                            <div class="counter-label">
                                                <i class="icon fa-cart-plus" aria-hidden="true"></i>
                                                <span class="site-menu-title">消费金币</span>
                                            </div>
                                            <div class="counter-number-group text-truncate">
                                                <span class="counter-number"><?php echo $data[3]['monetary']; ?></span>
                                            </div>
                                            <div class="ct-chart" data-counter-type="productBounceRate"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <!--今日收入图标统计-->
            <div class="col-xxl-12 col-lg-12">
                <div class="row h-full">
                    <div class="col-xxl-6 col-lg-6" style="height:50%;">
                        <div class="card card-shadow bg-blue-600 white" id="widgetLinepoint">
                            <div class="widget-content">
                                <div class="pt-25 px-30">
                                    <div class="row no-space">
                                        <div class="col-6">
                                            <p>今日充值金币收入</p>
                                            <p class="blue-200">最新单笔收入 &yen;<?php echo $money['coin']; ?></p>
                                        </div>
                                        <div class="col-6 text-right">
                                            <p class="font-size-30 text-nowrap">&yen;<?php echo $money['coin_amount_total']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="ct-chart h-120"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-6 col-lg-6" style="height:50%;">
                        <div class="card card-shadow bg-purple-600 white" id="widgetLinepoint1">
                            <div class="widget-content">
                                <div class="pt-25 px-30">
                                    <div class="row no-space">
                                        <div class="col-6">
                                            <p>今日充值vip</p>
                                            <p class="purple-200">最新单笔收入 &yen;<?php echo $money['vip']; ?></p>
                                        </div>
                                        <div class="col-6 text-right">
                                            <p class="font-size-30 text-nowrap">&yen;<?php echo $money['vip_amount_total']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="ct-chart h-120"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-12 col-lg-12">
                <div class="card card-shadow widget-responsive" id="widgetOverallViews">
                    <div class="widget-content p-30">
                        <div class="row pb-30" style="height:calc(100% - 250px);">
                            <div class="col-12 col-md-4">
                                <div class="counter counter-md text-left">
                                    <div class="device_title" style="margin-left: -30px;margin-top: -30px">消费概况</div>
                                    <div class="counter-number-group text-truncate">
                                        <span class="counter-number-related red-600">&yen;</span>
                                        <span class="counter-number red-600"><?php echo $coin['coin_total']; ?></span>
                                    </div>
                                   <!-- <div class="counter-label">同比增长 2%</div>-->
                                </div>
                            </div>
                            <div class="col-6 col-md-4 line_sty">
                                <div class="counter counter-sm text-left">
                                    <div class="counter-label">聊天消费</div>
                                    <div class="counter-number-group">
                                        <span class="counter-number-related">&yen;</span>
                                        <span class="counter-number"><?php echo $coin['coin_video']; ?></span>
                                    </div>
                                </div>
                                <div class="ct-chart small-bar-one"></div>
                            </div>
                            <div class="col-6 col-md-4 line_sty">
                                <div class="counter counter-sm text-left">
                                    <div class="counter-label">礼物消费</div>
                                    <div class="counter-number-group">
                                        <span class="counter-number-related">&yen;</span>
                                        <span class="counter-number"><?php echo $coin['coin_gift']; ?></span>
                                    </div>
                                </div>
                                <div class="ct-chart small-bar-two"></div>
                            </div>
                        </div>
                        <div class="ct-chart line-chart h-250"></div>
                    </div>
                </div>

            </div>
            <div class="col-xxl-6 col-lg-12 dis_ab">
                <div class="card card-shadow widget-responsive" id="widgetTimeline">
                    <div class="widget-content" style="padding: 1.2rem;">
                        <div class="p-30" style="height:120px;">
                            <div class="row">
                                <div class="col-4">
                                    <div class="counter text-left">
                                        <div class="counter-label blue-grey-700">总流量</div>
                                        <div class="counter-number-group">
                                            <span class="counter-number red-600">21,451</span>
                                            <span class="counter-number-related red-600">MB</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="counter text-left">
                                        <div class="counter-label">当前</div>
                                        <div class="counter-number-group">
                                            <span class="counter-number">227.34</span>
                                            <span class="counter-number-related">KB</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="counter text-left">
                                        <div class="counter-label">平均</div>
                                        <div class="counter-number-group">
                                            <span class="counter-number">117.65</span>
                                            <span class="counter-number-related">MB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="list-unstyled pb-50" style="height:calc(100% - 270px);">
                            <li class="px-30 py-15 container-fluid">
                                <div class="row">
                                    <div class="col-3">QQ</div>
                                    <div class="col-6">210,685,943 用户正在使用</div>
                                    <div class="col-3 green-600">227.34KB</div>
                                </div>
                            </li>
                            <li class="px-30 py-15 container-fluid">
                                <div class="row">
                                    <div class="col-3">微信</div>
                                    <div class="col-6">560,685,943 用户正在使用</div>
                                    <div class="col-3 green-600">1218.62KB</div>
                                </div>
                            </li>
                        </ul>
                        <div class="ct-chart h-150"></div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-lg-12 dis_ab">
                <div class="card card-shadow" id="widgetWeather">
                    <div class="widget-content">
                        <div class="row no-space h-full">
                            <div class="col-md-7 h-full">
                                <div class="p-35 text-center">
                                    <h4>北京</h4>
                                    <p class="blue-grey-400 mb-35">2016年9月9日 星期一</p>
                                    <canvas id="widgetSunny" height="60" width="60"></canvas>
                                    <div class="font-size-40 red-600">
                                        26°
                                        <span class="font-size-30">C</span>
                                    </div>
                                    <div>晴</div>
                                </div>
                                <div class="weather-times p-30">
                                    <div class="row no-space text-center">
                                        <div class="col-3">
                                            <div class="weather-day vertical-align">
                                                <div class="vertical-align-middle">
                                                    <div class="mb-5">12:00</div>
                                                    <i class="wi-day-cloudy font-size-24 mb-5"></i>
                                                    <div class="red-600">24°
                                                        <span class="font-size-12">C</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="weather-day vertical-align">
                                                <div class="vertical-align-middle">
                                                    <div class="mb-5">12:30</div>
                                                    <i class="wi-day-sunny font-size-24 mb-5"></i>
                                                    <div class="red-600">26°
                                                        <span class="font-size-12">C</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="weather-day vertical-align">
                                                <div class="vertical-align-middle">
                                                    <div class="mb-5">13:00</div>
                                                    <i class="wi-day-sunny font-size-24 mb-5"></i>
                                                    <div class="red-600">28°
                                                        <span class="font-size-12">C</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="weather-day vertical-align">
                                                <div class="vertical-align-middle">
                                                    <div class="mb-5">13:30</div>
                                                    <i class="wi-day-sunny font-size-24 mb-5"></i>
                                                    <div class="red-600">30°
                                                        <span class="font-size-12">C</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 bg-blue-grey-100 h-full">
                                <div class="weather-list">
                                    <ul class="list-unstyled m-0">
                                        <li class="container-fluid">
                                            <div class="row no-space">
                                                <div class="col-4">
                                                    周二
                                                </div>
                                                <div class="col-4">
                                                    <i class="wi-day-cloudy font-size-24"></i>
                                                </div>
                                                <div class="col-4">
                                                    24 - 26
                                                </div>
                                            </div>
                                        </li>
                                        <li class="container-fluid">
                                            <div class="row no-space">
                                                <div class="col-4">
                                                    周三
                                                </div>
                                                <div class="col-4">
                                                    <i class="wi-day-cloudy font-size-24"></i>
                                                </div>
                                                <div class="col-4">
                                                    24 - 26
                                                </div>
                                            </div>
                                        </li>
                                        <li class="container-fluid">
                                            <div class="row no-space">
                                                <div class="col-4">
                                                    周四
                                                </div>
                                                <div class="col-4">
                                                    <i class="wi-day-cloudy font-size-24"></i>
                                                </div>
                                                <div class="col-4">
                                                    24 - 26
                                                </div>
                                            </div>
                                        </li>
                                        <li class="container-fluid">
                                            <div class="row no-space">
                                                <div class="col-4">
                                                    周五
                                                </div>
                                                <div class="col-4">
                                                    <i class="wi-day-cloudy font-size-24"></i>
                                                </div>
                                                <div class="col-4">
                                                    24 - 26
                                                </div>
                                            </div>
                                        </li>
                                        <li class="container-fluid">
                                            <div class="row no-space">
                                                <div class="col-4">
                                                    周六
                                                </div>
                                                <div class="col-4">
                                                    <i class="wi-day-cloudy font-size-24"></i>
                                                </div>
                                                <div class="col-4">
                                                    24 - 26
                                                </div>
                                            </div>
                                        </li>
                                        <li class="container-fluid">
                                            <div class="row no-space">
                                                <div class="col-4">
                                                    周日
                                                </div>
                                                <div class="col-4">
                                                    <i class="wi-day-cloudy font-size-24"></i>
                                                </div>
                                                <div class="col-4">
                                                    24 - 26
                                                </div>
                                            </div>
                                        </li>
                                        <li class="container-fluid">
                                            <div class="row no-space">
                                                <div class="col-4">
                                                    周一
                                                </div>
                                                <div class="col-4">
                                                    <i class="wi-day-cloudy font-size-24"></i>
                                                </div>
                                                <div class="col-4">
                                                    24 - 26
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-xxl-3 col-lg-6 dis_ab">
                <div class="card card-shadow" id="widgetTable">
                    <div class="card-block p-30">
                        <h3 class="card-title">
                            <span class="text-truncate">收藏夹</span>
                            <span class="float-right red-600 font-size-24">&yen; 102,967</span>
                        </h3>
                        <form class="mt-25" action="#" role="search">
                            <div class="input-search input-search-dark">
                                <i class="input-search-icon wb-search" aria-hidden="true"></i>
                                <input type="text" class="form-control" placeholder="搜索..">
                            </div>
                        </form>
                    </div>
                    <table class="table mb-0">
                        <tbody>
                        <tr>
                            <td>MacBook</td>
                            <td>&yen; 9,500</td>
                            <td class="green-600">+ 458</td>
                        </tr>
                        <tr>
                            <td>ThinkServer TD350</td>
                            <td>&yen; 33,425</td>
                            <td class="red-600">- 1,632</td>
                        </tr>
                        <tr>
                            <td>天逸5050</td>
                            <td>&yen; 3,199</td>
                            <td class="green-600">+ 26</td>
                        </tr>
                        <tr>
                            <td>荣耀畅玩5</td>
                            <td>&yen; 550</td>
                            <td class="green-600">+ 0</td>
                        </tr>
                        <tr>
                            <td>HTC VIVE虚拟现实头盔</td>
                            <td>&yen; 18,500</td>
                            <td class="red-600">- 586</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="col-xxl-3 col-lg-6 dis_ab">
                <div class="card card-shadow" id="widgetLinepointDate">
                    <div class="card-block p-30">
                        <h3 class="card-title">销售统计
                            <span Class="badge badge-dark label-round float-right">查看</span>
                        </h3>
                        <div class="row text-center my-25">
                            <div class="col-4">
                                <div class="counter">
                                    <div class="counter-label">总计</div>
                                    <div class="counter-number red-600">20,186</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="counter">
                                    <div class="counter-label">今日</div>
                                    <div class="counter-number red-600">36</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="counter">
                                    <div class="counter-label">本周</div>
                                    <div class="counter-number red-600">261</div>
                                </div>
                            </div>
                        </div>
                        <p>数据每 30 分钟更新一次</p>
                    </div>
                    <div class="ct-chart h-150"></div>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="span6" style="background: #fff">
                    <div class="device_title">设备终端</div>
                    <div class="graph-container">
                        <div class="caption">已注册用户使用设备类型占比</div>
                        <div id="hero-bar" class="graph" style="position: relative"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="example-wrap m-md-0" style="background: #fff;height: 400px;">
                    <div class="device_title">注册用户</div>
                    <p style="padding-left: 300px;color: #a9a9a9">用户性别占比</p>
                    <div class="example">
                        <div id="exampleC3Pie"></div>
                    </div>
                </div>

            </div>
        </div>



        <div class="row" style="margin-top: 2rem;margin-left: 0">
            <div class="statistics w33 mr10 anchor">
                <div class="title">消费数据</div>
                <div class="bd">
                    <div class="data_list">
                        <?php if(!empty($consume)){ ?>
                        <ul>
                            <li>
                                <div class="data_list_left">礼物支付</div>
                                <div class="data_list_right"><?php echo (isset($consume['gift'] ) && ($consume['gift']  !== '')?$consume['gift'] : 0); ?>&nbsp;币</div>
                            </li>
                            <li>
                                <div class="data_list_left">守护支付</div>
                                <div class="data_list_right">
                                    <span id="anchor_live_long_today"><?php echo (isset($consume['guard'] ) && ($consume['guard']  !== '')?$consume['guard'] : 0); ?></span>&nbsp;币
                                </div>
                            </li>
                            <li>
                                <div class="data_list_left">音视频支付</div>
                                <div class="data_list_right"><?php echo (isset($consume['audio'] ) && ($consume['audio']  !== '')?$consume['audio'] : 0); ?>&nbsp;币</div>
                            </li>
                            <li>
                                <div class="data_list_left">直播间门票</div>
                                <div class="data_list_right">
                                    <span id="anchor_live_today"><?php echo (isset($consume['live'] ) && ($consume['live']  !== '')?$consume['live'] : 0); ?></span>&nbsp;币</div>
                            </li>
                            <li class="last">
                                <div class="data_list_left">总支付消费</div>
                                <div class="data_list_right"><?php echo (isset($consume['total'] ) && ($consume['total']  !== '')?$consume['total'] : 0); ?>&nbsp;币</div>
                            </li>
                        </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="statistics w33 mr10 votestotal">
                <div class="title">守护榜</div>
                <div class="bd">
                    <div class="list">
                        <?php if(!empty($red_list)){ ?>
                        <ul>
                            <?php foreach($red_list as $k1 => $v1){ ?>
                            <li>
                                <img class="list_order" src="/assets/admin/<?php echo $k1+1; ?>.png">
                                <img class="list_avatar" src="<?=app\admin\service\MaterialService::getFullUrl($v1['avatar']);?>">
                                <div class="list_info">
                                    <p class="list_name"><?php echo $v1['user_nickname']; ?></p>
                                    <p>累计被守护时间<span> <?php echo $v1['w_day']; ?> </span>天</p>
                                </div>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="statistics w33 rich">
                <div class="title">富豪榜</div>
                <div class="bd">
                    <div class="list">
                        <?php if(!empty($rich_list)){ ?>
                        <ul>
                            <?php foreach($rich_list as $k2 => $v2){ ?>
                            <li>
                                <img class="list_order" src="/assets/admin/<?php echo $k2+1; ?>.png">
                                <img class="list_avatar" src="<?=app\admin\service\MaterialService::getFullUrl($v2['avatar']);?>">
                                <div class="list_info">
                                    <p class="list_name"><?php echo $v2['user_nickname']; ?></p>
                                    <p>累计消费<span> <?php echo $v2['sum_coin']; ?> <span>金币</span></span></p>
                                </div>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="chartist-tooltip"></div>
<!-- 插件 -->
<script src="http://www.liao.com/assets/admui/vendor/matchheight/jquery.matchHeight.min.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/skycons/skycons.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/matchheight/jquery.matchHeight.min.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/chartist/chartist.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/raphael/raphael.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/morris/morris.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/c3/c3.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/d3/d3.js"></script>
<script src="http://www.liao.com/assets/admui/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.js"></script>


<script src="http://www.liao.com/assets/admui/themes/base/js/app.js"></script>

<!-- Page JS -->
<script src="http://www.liao.com/assets/admui/js/examples/pages/home/home-v1.js"></script>
<script>
    $(function () {
        // data stolen from http://howmanyleft.co.uk/vehicle/jaguar_'e'_type
        //设备类型占比
        $.ajax({
            url:"<?php echo url('index/getUserDevice');; ?>",
            type:'POST',
            data:{
                type:1
            },
            dataType:'json',
            success:function(data){
                //console.log(data.android);
                //$("input[name='android']").val(data.android);
                //$("input[name='iphone']").val(data.iphone);

                Morris.Bar({
                    element: 'hero-bar',
                    data: [
                        {device: 'iPhone', geekbench:  data.iphone },
                        {device: 'Android', geekbench: data.android },
                    ],
                    xkey: 'device',
                    ykeys: ['geekbench'],
                    labels: ['设备终端'],
                    barRatio: 0,
                    xLabelAngle: 0,
                    hideHover: 'auto',
                    barColors : [ '#6dc5a3', '#db7256' ],
                });
            },
            error:function(){

            }
        });

        //注册性别占比
        $.ajax({
            url:"<?php echo url('index/getUserReg');; ?>",
            type:'POST',
            data:{
                type:1
            },
            dataType:'json',
            success:function(data){
                c3.generate({
                    bindto: '#exampleC3Pie',
                    data: {
                        // iris data from R
                        columns: [
                            ['男' + '('+ data.data.man + ')', data.data.man],
                            ['女' + '('+ data.data.woman + ')', data.data.woman],
                            ['保密' + '('+ data.data.secrecy+')', data.data.secrecy]
                        ],
                        type: 'pie',
                        key:{
                            value:[200,300,50]
                        }
                    },
                    color: {
                        pattern: [$.getColor("blue", 300),$.getColor("red", 300), $.getColor("blue-grey", 300)]
                    },
                    legend: {
                        position: 'right'
                    },
                    pie: {
                        label: {
                            show: 'center'
                        }
                    },
                });
            },
            error:function(){

            }
        });
    });

</script>
</body>
</html>