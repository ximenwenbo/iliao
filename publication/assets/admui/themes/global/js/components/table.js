/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('table', {
    mode: 'api',
    api: function(context) {
      var global = context || window;
      var frame$ = global.$;
      var touch = typeof global.ontouchstart !== 'undefined';
      var type = 'click';

      if (touch) {
        type = 'touchstart';
      }

      $(global.document).on(type, '.table-section', function(e) {
        var $item = frame$(this);
        if (
          e.target.type !== 'checkbox' &&
          e.target.type !== 'button' &&
          e.target.tagName.toLowerCase() !== 'a' &&
          !$(e.target).parent('div.checkbox-custom').length
        ) {
          if ($item.hasClass('active')) {
            $item.removeClass('active');
          } else {
            $item.siblings('.table-section').removeClass('active');
            $item.addClass('active');
          }
        }
      });
    }
  });
})(window, document, jQuery);
