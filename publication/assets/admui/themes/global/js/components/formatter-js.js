/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('formatter', {
    mode: 'init',
    defaults: {
      persistent: true
    },

    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults = $.components.getDefaults('formatter');
      var browserName = navigator.userAgent.toLowerCase();
      var ieOptions;

      if (!frame$.fn.formatter) {
        return;
      }

      if (/msie/i.test(browserName) && !/opera/.test(browserName)) {
        ieOptions = {
          persistent: false
        };
      } else {
        ieOptions = {};
      }

      frame$('[data-plugin="formatter"]', context).each(function() {
        var $item = frame$(this);
        var options = $.extend({}, defaults, ieOptions, $item.data(frame$));

        if (options.pattern) {
          options.pattern = options.pattern.replace(/\[\[/g, '{{').replace(/\]\]/g, '}}');
        }

        $item.formatter(options);
      });
    }
  });
})(window, document, jQuery);
