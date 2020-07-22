/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('toastr', {
    mode: 'api',
    defaults: {},
    api: function(context) {
      var toastr = context && context.toastr ? context.toastr : window.toastr;
      var frame$ = context ? context.$ : $;
      var defaults;

      if (typeof toastr === 'undefined') {
        return;
      }
      defaults = $.components.getDefaults('toastr');

      frame$(context.document).on('click.site.toastr', '[data-plugin="toastr"]', function(e) {
        var $item = frame$(this);
        var options = $.extend(true, {}, defaults, $(this).data(frame$));
        var message = options.message || '';
        var type = options.type || 'info';
        var title = options.title || '';

        switch (type) {
          case 'success':
            toastr.success(message, title, options);
            break;
          case 'warning':
            toastr.warning(message, title, options);
            break;
          case 'error':
            toastr.error(message, title, options);
            break;
          case 'info':
            toastr.info(message, title, options);
            break;
          default:
            toastr.info(message, title, options);
        }
        e.preventDefault();
      });
    }
  });
})(window, document, jQuery);
