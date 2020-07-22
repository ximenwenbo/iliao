/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('appear', {
    defaults: {},
    api: function(context) {
      var frame$ = context ? context.$ : $;

      if (!frame$.fn.appear) {
        return;
      }

      frame$(context.document).on('appear', '[data-plugin="appear"]', function() {
        var $item = frame$(this);
        var animate = $item.data('animate', frame$);

        if ($item.hasClass('appear-no-repeat')) {
          return;
        }
        $item.removeClass('invisible').addClass('animation-' + animate);

        if ($item.data('repeat') === false) {
          $item.addClass('appear-no-repeat');
        }
      });

      frame$(context.document).on('disappear', '[data-plugin="appear"]', function() {
        var $item = frame$(this);
        var animate = $item.data('animate', frame$);

        if ($item.hasClass('appear-no-repeat')) {
          return;
        }

        $item.addClass('invisible').removeClass('animation-' + animate);
      });
    },

    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults = $.components.getDefaults('appear');

      if (!frame$.fn.appear) {
        return;
      }

      frame$('[data-plugin="appear"]', context).appear(defaults);
      frame$('[data-plugin="appear"]', context)
        .not(':appeared')
        .addClass('invisible');
    }
  });
})(window, document, jQuery);
