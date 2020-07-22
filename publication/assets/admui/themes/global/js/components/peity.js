/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('peityBar', {
    mode: 'init',
    defaults: {
      delimiter: ',',
      fill: [$.getColor('purple', 400)],
      height: 18,
      max: null,
      min: 0,
      padding: 0.1,
      width: 44
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.peity) {
        return;
      }

      defaults = $.components.getDefaults('peityBar');

      frame$('[data-plugin="peityBar"]').each(function() {
        var $item = frame$(this);
        var options = $item.data();
        var skinColors = $.getColor(options.skin);

        if (options.skin) {
          if ($.getColor(options.skin)) {
            defaults.fill = [skinColors[400]];
          }
        }

        options = $.extend(true, {}, defaults, options);

        $item.peity('bar', options);
      });
    }
  });

  $.components.register('peityDonut', {
    mode: 'init',
    defaults: {
      delimiter: null,
      fill: [$.getColor('purple', 700), $.getColor('purple', 400), $.getColor('purple', 200)],
      height: null,
      innerRadius: null,
      radius: 11,
      width: null
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.peity) {
        return;
      }

      defaults = $.components.getDefaults('peityDonut');

      frame$('[data-plugin="peityDonut"]').each(function() {
        var $item = frame$(this);
        var options = $item.data();
        var skinColors = $.getColor(options.skin);

        if (options.skin) {
          if ($.getColor(options.skin)) {
            defaults.fill = [skinColors[700], skinColors[400], skinColors[200]];
          }
        }

        options = $.extend(true, {}, defaults, options);

        $item.peity('donut', options);
      });
    }
  });

  $.components.register('peityLine', {
    mode: 'init',
    defaults: {
      delimiter: ',',
      fill: [$.getColor('purple', 200)],
      height: 18,
      max: null,
      min: 0,
      stroke: $.getColor('purple', 600),
      strokeWidth: 1,
      width: 44
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.peity) {
        return;
      }

      defaults = $.components.getDefaults('peityLine');

      frame$('[data-plugin="peityLine"]').each(function() {
        var $item = frame$(this);
        var options = $item.data();
        var skinColors = $.getColor(options.skin);

        if (options.skin) {
          if ($.getColor(options.skin)) {
            defaults.fill = [skinColors[200]];
            defaults.stroke = skinColors[600];
          }
        }

        options = $.extend(true, {}, defaults, options);

        $item.peity('line', options);
      });
    }
  });

  $.components.register('peityPie', {
    mode: 'init',
    defaults: {
      delimiter: null,
      fill: [$.getColor('purple', 700), $.getColor('purple', 400), $.getColor('purple', 200)],
      height: null,
      radius: 11,
      width: null
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.peity) {
        return;
      }

      defaults = $.components.getDefaults('peityPie');

      frame$('[data-plugin="peityPie"]', context.document).each(function() {
        var $item = frame$(this);
        var options = $item.data();
        var skinColors = $.getColor(options.skin);

        if (options.skin) {
          if ($.getColor(options.skin)) {
            defaults.fill = [skinColors[700], skinColors[400], skinColors[200]];
          }
        }

        options = $.extend(true, {}, defaults, options);

        $item.peity('pie', options);
      });
    }
  });
})(window, document, jQuery);
