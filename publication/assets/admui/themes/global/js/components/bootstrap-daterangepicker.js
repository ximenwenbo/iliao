/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('daterangepicker', {
    mode: 'init',
    defaults: {
      locale: {
        format: 'YYYY-MM-DD',
        separator: ' 至 ',
        applyLabel: '确定',
        cancelLabel: '取消',
        fromLabel: '从',
        toLabel: '到',
        customRangeLabel: '自定义',
        weekLabel: 'W',
        daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
        monthNames: [
          '一月',
          '二月',
          '三月',
          '四月',
          '五月',
          '六月',
          '七月',
          '八月',
          '九月',
          '十月',
          '十一月',
          '十二月'
        ],
        firstDay: 1
      },
      // "parentEl": "#admui-pageContent > .page",
      alwaysShowCalendars: true
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var moment = context ? context.moment : window.moment;
      var defaults;
      var defaultsRanges;

      if (!frame$.fn.daterangepicker) {
        return;
      }

      defaults = $.components.getDefaults('daterangepicker');
      defaultsRanges = {
        ranges: {
          今天: [moment(), moment()],
          昨天: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          最近7天: [moment().subtract(6, 'days'), moment()],
          最近30天: [moment().subtract(29, 'days'), moment()],
          本月: [moment().startOf('month'), moment().endOf('month')],
          上月: [
            moment()
              .subtract(1, 'month')
              .startOf('month'),
            moment()
              .subtract(1, 'month')
              .endOf('month')
          ]
        }
      };

      frame$('[data-plugin="daterangepicker"]', context).each(function() {
        var $item = frame$(this);
        var options = $.extend(true, {}, defaults, defaultsRanges, $item.data(frame$));

        $item.daterangepicker(options);
      });
    }
  });
})(window, document, jQuery);
