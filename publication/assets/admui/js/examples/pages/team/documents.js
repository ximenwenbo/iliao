/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  $(function() {
    // 初始化滚动跟踪
    $('.page-documents').scrollspy({
      target: '#articleAffix',
      offset: 20
    });
  });
})(document, window, jQuery);
