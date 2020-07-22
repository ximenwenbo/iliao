/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  'use strict';

  window.App.extend({
    run: function() {
      // markdown编辑器初始化
      $('.markdown-edit').markdown({
        language: 'zh',
        iconlibrary: 'fa'
      });
    }
  });

  $(function() {
    window.App.run();
  });
})(jQuery);
