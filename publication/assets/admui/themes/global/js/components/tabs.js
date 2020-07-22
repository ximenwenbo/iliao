/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('verticalTab', {
    mode: 'init',
    init: function(context) {
      var frame$ = context ? context.$ : $;

      if (!frame$.fn.matchHeight) {
        return;
      }

      frame$('.nav-tabs-vertical', context.document).each(function() {
        frame$(this)
          .children()
          .matchHeight();
      });
    }
  });

  $.components.register('horizontalTab', {
    mode: 'init',
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var $nav;

      if (!frame$.fn.responsiveHorizontalTabs) {
        return;
      }

      $nav = $('[data-approve="nav-tabs"]', context);
      $nav.each(function() {
        var $item = frame$(this);
        var options = $.extend(true, {}, $item.data());

        $item.responsiveHorizontalTabs(options);
      });
    }
  });
})(window, document, jQuery);
