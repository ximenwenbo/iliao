/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var $table = $('#table');

    // 显示隐藏字段为name的行
    $('#button').click(function() {
      $table.bootstrapTable('showColumn', 'name');
    });

    $('#button2').click(function() {
      $table.bootstrapTable('hideColumn', 'name');
    });
  });
})(jQuery);
