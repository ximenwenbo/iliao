/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('matchHeight', {
    mode: 'init',
    defaults: {},
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (typeof frame$.fn.matchHeight === 'undefined') {
        return;
      }
      defaults = $.components.getDefaults('matchHeight');

      frame$('[data-plugin="matchHeight"]').each(function() {
        var $item = frame$(this);
        var options = $.extend(true, {}, defaults, $item.data());
        var matchSelector = $item.data('matchSelector');

        if (matchSelector) {
          $item.find(matchSelector).matchHeight(options);
        } else {
          $item.children().matchHeight(options);
        }
      });
    }
  });
})(window, document, jQuery);
