/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var $table = $('#table');

    $('#button').click(function() {
      // 选中ID为1的行
      $table.bootstrapTable('check', 1);
    });
    $('#button2').click(function() {
      // 取消选中ID为1的行
      $table.bootstrapTable('uncheck', 1);
    });
  });
})(jQuery);
