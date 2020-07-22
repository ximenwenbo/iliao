/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var $table = $('#table');

    // 根据下标显示 | 隐藏行
    $('#button').click(function() {
      $table.bootstrapTable('showRow', {
        index: 1
      });
    });

    $('#button2').click(function() {
      $table.bootstrapTable('hideRow', {
        index: 1
      });
    });
  });
})(jQuery);
