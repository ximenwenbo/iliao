/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('sortable', {
    defaults: {},
    mode: 'init',
    init: function(context) {
      var global = context || window;

      if (typeof global.sortable === 'undefined') {
        return;
      }

      $('[data-plugin="sortable"]', context.document).each(function(i, block) {
        global.sortable(block);
      });
    }
  });
})(window, document, jQuery);
