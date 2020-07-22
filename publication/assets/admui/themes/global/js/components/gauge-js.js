/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('gauge', {
    mode: 'init',
    defaults: {
      lines: 12,
      angle: 0.12,
      lineWidth: 0.4,
      pointer: {
        length: 0.68,
        strokeWidth: 0.03,
        color: $.getColor('blue-grey', 400)
      },
      limitMax: true,
      colorStart: $.getColor('blue-grey', 200),
      colorStop: $.getColor('blue-grey', 200),
      strokeColor: $.getColor('purple', 500),
      generateGradient: true
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var Gauge = context && context.Gauge ? context.Gauge : window.Gauge;
      var defaults;

      if (typeof Gauge === 'undefined') {
        return;
      }

      defaults = $.components.getDefaults('gauge');

      frame$('[data-plugin="gauge"]', context.document).each(function() {
        var $item = frame$(this);
        var options = $.extend(true, {}, defaults, $(this).data());
        var $text = $item.find('.gauge-label');
        var $canvas = $item.find('canvas');
        var gauge = new Gauge($canvas[0]).setOptions(options);

        if ($canvas.length === 0) {
          return;
        }

        $(this).data('gauge', gauge, frame$);

        gauge.animationSpeed = 50;
        gauge.maxValue = $(this).data('max-value', frame$);

        gauge.set($(this).data('value', frame$));

        if ($text.length > 0) {
          gauge.setTextField($text[0]);
        }
      });
    }
  });

  $.components.register('donut', {
    mode: 'init',
    defaults: {
      lines: 12,
      angle: 0.3,
      lineWidth: 0.08,
      pointer: {
        length: 0.9,
        strokeWidth: 0.035,
        color: $.getColor('blue-grey', 400)
      },
      limitMax: false, // If true, the pointer will not go past the end of the gauge
      colorStart: $.getColor('blue-grey', 200),
      colorStop: $.getColor('blue-grey', 200),
      strokeColor: $.getColor('purple', 500),
      generateGradient: true
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var Donut = context && context.Donut ? context.Donut : window.Donut;
      var defaults;

      if (typeof Donut === 'undefined') {
        return;
      }

      defaults = $.components.getDefaults('donut');

      frame$('[data-plugin="donut"]', context.document).each(function() {
        var $item = frame$(this);
        var options = $.extend(true, {}, defaults, $(this).data(frame$));
        var $text = $item.find('.donut-label');
        var $canvas = $item.find('canvas');
        var donut = new Donut($canvas[0]).setOptions(options);

        if ($canvas.length === 0) {
          return;
        }

        $(this).data('donut', donut, frame$);

        donut.animationSpeed = 50;
        donut.maxValue = $(this).data('max-value', frame$);

        donut.set($(this).data('value', frame$));

        if ($text.length > 0) {
          donut.setTextField($text[0]);
        }
      });
    }
  });
})(window, document, jQuery);
