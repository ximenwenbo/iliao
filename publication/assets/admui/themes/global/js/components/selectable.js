/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('selectable', {
    mode: 'init',
    defaults: {
      allSelector: '.selectable-all',
      itemSelector: '.selectable-item',
      rowSelector: 'tr',
      rowSelectable: false,
      rowActiveClass: 'active',
      onChange: null
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.asSelectable) {
        return;
      }

      defaults = $.components.getDefaults('selectable');

      frame$('[data-plugin="selectable"], [data-selectable="selectable"]', context.document).each(
        function() {
          var options = $.extend({}, defaults, $(this).data(frame$));

          frame$(this).asSelectable(options);
        }
      );
    }
  });
})(window, document, jQuery);
