/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('card', {
    mode: 'init',
    defaults: {},
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.card) {
        return;
      }

      defaults = $.components.getDefaults('card');

      frame$('[data-plugin="card"]', context).each(function() {
        var $item = frame$(this);
        var options = $.extend({}, defaults, $item.data(frame$));

        if (options.target) {
          options.container = frame$(options.target);
        }

        $item.card(options);
      });
    }
  });
})(window, document, jQuery);
