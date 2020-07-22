/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var $table = $('#table');

    $('#button').click(function() {
      // 选中ID为1,2,3的行
      $table.bootstrapTable('checkBy', {field: 'id', values: [1, 2, 3]});
    });
    $('#button2').click(function() {
      // 取消勾选ID为1,2,3的行
      $table.bootstrapTable('uncheckBy', {field: 'id', values: [1, 2, 3]});
    });
  });
})(jQuery);
