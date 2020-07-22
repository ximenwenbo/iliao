/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global Gauge, Donut */

  // 图表示例
  var gaugesPage = {
    gaugeLine: function() {
      // 仪表盘--动态变化
      var dynamicGauge = document.getElementById('exampleDynamicGauge');
      var random;
      var options = {
        strokeColor: $.getColor('purple', 500)
      };

      setInterval(function() {
        var gauge;
        random = Math.round(Math.random() * 1000);
        if (random > 700) {
          options.strokeColor = $.getColor('pink', 500);
        } else if (random < 300) {
          options.strokeColor = $.getColor('green', 500);
        }

        gauge = new Gauge(dynamicGauge).setOptions($.concatCpt('gauge', options));
        gauge.maxValue = 1000;
        gauge.set(random);
      }, 1500);
    },
    gagueDonut: function() {
      // 环形图--动态变化
      var dynamicDonut = document.getElementById('exampleDynamicDonut');
      var random;
      var options = {
        strokeColor: $.getColor('purple', 500)
      };

      setInterval(function() {
        var donut;
        random = Math.round(Math.random() * 1000);
        if (random > 700) {
          options.strokeColor = $.getColor('pink', 500);
        } else if (random < 300) {
          options.strokeColor = $.getColor('green', 500);
        }

        donut = new Donut(dynamicDonut).setOptions($.concatCpt('donut', options));
        donut.animationSpeed = 50;
        donut.maxValue = 2000;
        donut.set(random);
      }, 1500);
    }
  };

  $(function() {
    // 初始化图表示例
    $.each(gaugesPage, function(i, n) {
      n();
    });
  });
})(document, window, jQuery);
