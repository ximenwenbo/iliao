/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (document, window, $) {
    'use strict';

    var $sparklineCompositebarChart = $('.sparkline-compositebar-chart'),
        $sparklineCompositeline = $('.sparkline-compositeline'),
        $sparklineCompositebar = $('.sparkline-compositebar');

    // 基本
    // ---------------
    // 饼状图
    $(".sparkline-pie-chart").sparkline([4, 2, 6], {
        type: 'pie',
        height: '162px',
        sliceColors: [$.getColor("purple", 500), $.getColor("purple", 700), $.getColor("purple", 600)]
    });

    // 折线图
    $(".sparkline-line-chart").sparkline([1, 3, 4, 2, 3, 6, 5, 3], {
        type: 'line',
        height: '162px',
        width: '200px',
        normalRangeMin: 0,
        spotRadius: 2,
        spotColor: $.getColor("red", 600),
        highlightSpotColor: $.getColor("red", 700),
        lineColor: $.getColor("red", 500),
        highlightLineColor: $.getColor("red", 500),
        fillColor: $.getColor("red", 100)
    });

    // 柱状图
    $(".sparkline-bar-chart").sparkline([4, 7, 3, 2, 5, 6, 8, 5, 4, 8], {
        type: 'bar',
        height: '162px',
        barWidth: 10,
        barSpacing: 6,
        barColor: $.getColor("purple", 500),
        negBarColor: $.getColor("purple", 600)
    });

    // 组合图
    $sparklineCompositebarChart.sparkline('html', {
        type: 'bar',
        height: '162px',
        barWidth: 10,
        barSpacing: 5,
        barColor: $.getColor("blue-grey", 300)
    });

    $sparklineCompositebarChart.sparkline([4, 5, 6, 6, 5, 5, 3, 6, 4, 2], {
        composite: true,
        fillColor: false,
        lineColor: $.getColor("purple", 400)
    });

    $sparklineCompositebarChart.sparkline([1, 4, 5, 2, 3, 5, 6, 1, 3, 6], {
        composite: true,
        fillColor: false,
        lineColor: $.getColor("red", 400)
    });

    // 类型
    // ---------------
    // 折线图，从标签中取值
    $('.sparkline-line').sparkline('html', {
        height: '32px',
        width: '150px',
        lineColor: $.getColor("red", 600),
        fillColor: $.getColor("red", 100)
    });

    // 使用内联值的柱状图
    $('.sparkline-bar').sparkline('html', {
        type: 'bar',
        height: '32px',
        barWidth: 10,
        barSpacing: 5,
        barColor: $.getColor("purple", 500),
        negBarColor: $.getColor("red", 500),
        stackedBarColor: [$.getColor("purple", 500), $.getColor("red", 500)]
    });

    // 组合折线图，第二个使用JavaScript赋值
    $sparklineCompositeline.sparkline('html', {
        height: '32px',
        width: '150px',
        fillColor: false,
        lineColor: $.getColor("purple", 500),
        spotColor: $.getColor("green", 500),
        minSpotColor: $.getColor("purple", 500),
        maxSpotColor: $.getColor("green", 500),
        changeRangeMin: 0,
        chartRangeMax: 10
    });
    $sparklineCompositeline.sparkline([4, 1, 5, 7, 9, 8, 7, 6, 6, 4, 7, 8, 4, 3, 2, 5, 6, 7], {
        composite: true,
        fillColor: false,
        height: '32px',
        width: '150px',
        lineColor: $.getColor("red", 500),
        spotColor: $.getColor("green", 500),
        minSpotColor: $.getColor("purple", 500),
        maxSpotColor: $.getColor("green", 500),
        changeRangeMin: 0,
        chartRangeMax: 10
    });

    // 范围折线图
    $('.sparkline-normalline').sparkline('html', {
        fillColor: false,
        height: '32px',
        width: '150px',
        lineColor: $.getColor("red", 600),
        spotColor: $.getColor("purple", 500),
        minSpotColor: $.getColor("purple", 500),
        maxSpotColor: $.getColor("purple", 500),
        normalRangeColor: $.getColor("blue-grey", 300),
        normalRangeMin: -1,
        normalRangeMax: 8
    });

    // 折线图和柱状图的组合图
    $sparklineCompositebar.sparkline('html', {
        type: 'bar',
        height: '32px',
        barWidth: 10,
        barSpacing: 5,
        barColor: $.getColor("purple", 500)
    });

    $sparklineCompositebar.sparkline([4, 1, 5, 7, 9, 9, 8, 7, 6, 6, 4, 7, 8, 4, 3, 2, 2, 5, 6, 7], {
        composite: true,
        fillColor: false,
        lineColor: $.getColor("red", 600),
        spotColor: $.getColor("purple", 500)
    });

    // 离散图
    $('.sparkline-discrete1').sparkline('html', {
        type: 'discrete',
        height: '32px',
        lineColor: $.getColor("purple", 500),
        xwidth: 36
    });

    $('.sparkline-discrete2').sparkline('html', {
        type: 'discrete',
        height: '32px',
        lineColor: $.getColor("purple", 500),
        thresholdColor: $.getColor("red", 600),
        thresholdValue: 4
    });

    // 子弹图
    $('.sparkline-bullet').sparkline('html', {
        type: 'bullet',
        targetColor: $.getColor("red", 500),
        targetWidth: '2',
        performanceColor: $.getColor("purple", 600),
        rangeColors: [$.getColor("purple", 100), $.getColor("purple", 200), $.getColor("purple", 400)]
    });

    // 自定义
    $('.sparkline-linecustom').sparkline('html', {
        height: '32px',
        width: '150px',
        lineColor: $.getColor("red", 400),
        fillColor: $.getColor("blue-grey", 300),
        minSpotColor: false,
        maxSpotColor: false,
        spotColor: $.getColor("green", 500),
        spotRadius: 2
    });

    // 三态图
    $('.sparkline-tristate').sparkline('html', {
        type: 'tristate',
        height: '32px',
        barWidth: 10,
        barSpacing: 5,
        posBarColor: $.getColor("purple", 500),
        negBarColor: $.getColor("blue-grey", 300),
        zeroBarColor: $.getColor("red", 500)
    });

    $('.sparkline-tristatecols').sparkline('html', {
        type: 'tristate',
        height: '32px',
        barWidth: 10,
        barSpacing: 5,
        posBarColor: $.getColor("purple", 500),
        negBarColor: $.getColor("blue-grey", 300),
        zeroBarColor: $.getColor("red", 500),
        colorMap: {
            '-4': $.getColor("red", 700),
            '-2': $.getColor("purple", 600),
            '2': $.getColor("blue-grey", 400)
        }
    });

    // 箱图
    $('.sparkline-boxplot').sparkline('html', {
        type: 'box',
        height: '20px',
        width: '68px',
        lineColor: $.getColor("purple", 700),
        boxLineColor: $.getColor("purple", 400),
        boxFillColor: $.getColor("purple", 400),
        whiskerColor: $.getColor("blue-grey", 500),
        // outlierLineColor: $.getColor("blue-grey", 300),
        // outlierFillColor: false,
        medianColor: $.getColor("red", 500)
        // targetColor: $.getColor("green", 500)
    });

    $('.sparkline-boxplotraw').sparkline([1, 3, 5, 8, 10, 15, 18], {
        type: 'box',
        height: '20px',
        width: '78px',
        raw: true,
        showOutliers: true,
        target: 6,
        lineColor: $.getColor("purple", 700),
        boxLineColor: $.getColor("purple", 400),
        boxFillColor: $.getColor("purple", 400),
        whiskerColor: $.getColor("blue-grey", 500),
        outlierLineColor: $.getColor("blue-grey", 300),
        outlierFillColor: $.getColor("blue-grey", 100),
        medianColor: $.getColor("red", 500),
        targetColor: $.getColor("green", 500)
    });

    // 饼状图
    $('.sparkline-pie').sparkline('html', {
        type: 'pie',
        height: '30px',
        sliceColors: [$.getColor("purple", 500), $.getColor("purple", 700), $.getColor("purple", 600)]
    });

    $('.sparkline-pie-1').sparkline('html', {
        type: 'pie',
        height: '30px',
        sliceColors: [$.getColor("purple", 500), $.getColor("blue-grey", 300)]
    });

})(document, window, jQuery);