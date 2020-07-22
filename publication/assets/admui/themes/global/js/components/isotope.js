/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('isotope', {
    mode: 'init',
    defaults: {},
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var global = context || window;
      var defaults = $.components.getDefaults('isotope');

      var callback = function() {
        frame$('[data-plugin="isotope"]', context).each(function() {
          var $item = frame$(this);
          var options = $.extend(true, {}, defaults, $item.data());

          $item.isotope(options);
        });
      };

      if (typeof frame$.fn.isotope === 'undefined') {
        return;
      }

      if (context.document !== window.document) {
        callback();
      } else {
        $(global).on('load', function() {
          callback();
        });
      }
    }
  });
})(window, document, jQuery);
