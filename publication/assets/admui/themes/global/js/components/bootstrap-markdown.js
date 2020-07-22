/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('markdown', {
    mode: 'init',
    defaults: {
      autofocus: false,
      savable: false,
      language: 'zh'
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.markdown) {
        return;
      }

      defaults = this.defaults;

      frame$('textarea[data-plugin="markdown"]').each(function() {
        var options = $.extend(true, {}, defaults, $(this).data(frame$));

        frame$(this).markdown(options);
      });
    }
  });
})(window, document, jQuery);
