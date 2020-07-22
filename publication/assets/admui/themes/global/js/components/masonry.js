/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('masonry', {
    mode: 'init',
    defaults: {
      itemSelector: '.masonry-item'
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (typeof frame$.fn.masonry === 'undefined') {
        return;
      }

      defaults = $.components.getDefaults('masonry');

      frame$('[data-plugin="masonry"]').each(function() {
        // TODO: data方法只能使用$(this);调用方法必须使用frame获取的元素
        var options = $.extend(true, {}, defaults, $(this).data());

        frame$(this).masonry(options);
      });
    }
  });
})(window, document, jQuery);
