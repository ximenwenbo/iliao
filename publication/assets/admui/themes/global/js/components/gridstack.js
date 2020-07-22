/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('gridstack', {
    mode: 'init',
    defaults: {
      cellHeight: 80,
      verticalMargin: 20
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults = $.components.getDefaults('gridstack');

      if (!frame$.fn.gridstack) {
        return;
      }

      frame$('[data-plugin="gridstack"]', context.document).each(function() {
        var options = $.extend(true, {}, defaults, $(this).data(frame$));

        console.log(frame$(this));
        frame$(this).gridstack(options);
      });
    }
  });
})(window, document, jQuery);
