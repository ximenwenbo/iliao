/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('toolbar', {
    mode: 'init',
    defaults: {
      adjustment: 15,
      zIndex: 1900
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.toolbar) {
        return;
      }

      defaults = $.components.getDefaults('toolbar');

      frame$('[data-plugin="toolbar"]', context.document).each(function() {
        var $item = frame$(this);
        var content = $item.data('toolbar');

        var options = $.extend(true, {}, defaults);

        if (content) {
          options.content = content;
        }

        $item.toolbar(options);
      });
    }
  });
})(window, document, jQuery);
