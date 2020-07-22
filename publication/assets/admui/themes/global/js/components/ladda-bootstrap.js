/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('ladda', {
    mode: 'init',
    defaults: {
      timeout: 2000
    },
    init: function(context) {
      var Ladda = context && context.Ladda ? context.Ladda : window.Ladd;
      var defaults;

      if (typeof Ladda === 'undefined') {
        return;
      }

      defaults = $.components.getDefaults('ladda');

      Ladda.bind('[data-plugin="ladda"]', defaults);
    }
  });

  $.components.register('laddaProgress', {
    mode: 'init',
    defaults: {},
    init: function(context) {
      var Ladda = context && context.Ladda ? context.Ladda : window.Ladda;
      var defaults;
      var options;

      if (typeof Ladda === 'undefined') {
        return;
      }

      defaults = $.components.getDefaults('laddaProgress');
      options = $.extend({}, defaults, {
        callback: function(instance) {
          var progress = 0;
          var interval = setInterval(function() {
            progress = Math.min(progress + Math.random() * 0.1, 1);
            instance.setProgress(progress);

            if (progress === 1) {
              instance.stop();
              clearInterval(interval);
            }
          }, 300);
        }
      });
      Ladda.bind('[data-plugin="laddaProgress"]', options);
    }
  });
})(window, document, jQuery);
