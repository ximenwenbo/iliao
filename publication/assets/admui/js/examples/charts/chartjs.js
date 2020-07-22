/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global Chart */

  var charts;

  Chart.defaults.global.responsive = true;

  // 图表示例
  charts = {
    example1: function() {
      // 曲线图示例
      var lineChartData = {
        labels: ['1月', '2', '3月', '4月', '5月', '6月', '7月'],
        scaleShowGridLines: true,
        scaleShowVerticalLines: false,
        scaleGridLineColor: '#ebedf0',
        datasets: [
          {
            label: '同比增长',
            backgroundColor: 'rgba(204, 213, 219, .1)',
            borderColor: $.getColor('blue-grey', 300),
            pointBackgroundColor: $.getColor('blue-grey', 300),
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: $.getColor('blue-grey', 300),
            data: [65, 59, 80, 81, 56, 55, 40]
          },
          {
            label: '同步增长',
            backgroundColor: 'rgba(98, 168, 234, .1)',
            borderColor: $.getColor('purple', 600),
            pointBackgroundColor: $.getColor('purple', 600),
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: $.getColor('purple', 600),
            data: [28, 48, 40, 19, 86, 27, 90]
          }
        ]
      };

      return new Chart(document.getElementById('exampleChartjsLine').getContext('2d'), {
        type: 'line',
        data: lineChartData
      });
    },
    example2: function() {
      // 柱状图示例
      var barChartData = {
        labels: ['1月', '2', '3月', '4月', '5月'],
        scaleShowGridLines: true,
        scaleShowVerticalLines: false,
        scaleGridLineColor: '#ebedf0',
        barShowStroke: false,
        datasets: [
          {
            label: '同比增长',
            backgroundColor: $.getColor('blue', 500),
            borderColor: $.getColor('blue', 500),
            hoverBackgroundColor: $.getColor('blue', 500),
            hoverBorderColor: $.getColor('blue', 500),
            data: [65, 45, 75, 50, 60]
          },
          {
            label: '同步增长',
            backgroundColor: $.getColor('blue-grey', 300),
            borderColor: $.getColor('blue-grey', 300),
            hoverBackgroundColor: $.getColor('blue-grey', 300),
            hoverBorderColor: $.getColor('blue-grey', 300),
            data: [30, 20, 40, 25, 45]
          }
        ]
      };

      return new Chart(document.getElementById('exampleChartjsBar').getContext('2d'), {
        type: 'bar',
        data: barChartData
      });
    },
    example3: function() {
      // 雷达图示例
      var radarChartData = {
        labels: ['吃饭', '喝水', '睡觉', '设计', '编码', '娱乐', '跑步'],
        pointLabelFontSize: 14,
        datasets: [
          {
            label: '同比增长',
            backgroundColor: 'rgba(204,213,219,0.35)',
            borderColor: 'rgba(0,0,0,0)',
            pointBackgroundColor: $.getColor('blue-grey', 300),
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: $.getColor('blue-grey', 300),
            data: [65, 59, 90, 81, 56, 55, 40]
          },
          {
            label: '同步增长',
            backgroundColor: 'rgba(250,122,122,0.25)',
            borderColor: 'rgba(0,0,0,0)',
            pointBackgroundColor: $.getColor('red', 500),
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: $.getColor('red', 500),
            data: [28, 48, 40, 19, 96, 27, 100]
          }
        ]
      };

      return new Chart(document.getElementById('exampleChartjsRadar').getContext('2d'), {
        type: 'radar',
        data: radarChartData,
        options: {
          scaleShowLabels: false,
          pointLabelFontSize: 10
        }
      });
    },
    example4: function() {
      // 极地区域图示例
      var chartData = {
        datasets: [
          {
            data: [300, 200, 100, 50],
            backgroundColor: [
              $.getColor('red', 600),
              $.getColor('purple', 500),
              $.getColor('blue-grey', 200),
              $.getColor('blue-grey', 300)
            ]
          }
        ],
        labels: ['红色', '蓝色', '蓝灰色', '深蓝灰色']
      };

      return new Chart(document.getElementById('exampleChartjsPloarArea').getContext('2d'), {
        type: 'polarArea',
        data: chartData
      });
    }
  };

  $(function() {
    // 初始化图表示例
    $.each(charts, function(i, n) {
      n();
    });
  });
})(document, window, jQuery);
