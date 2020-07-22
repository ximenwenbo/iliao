/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var $table = $('#table');

    $('#button').click(function() {
      // 选中所有项
      $table.bootstrapTable('checkAll');
    });
    $('#button2').click(function() {
      // 取消勾选所有项
      $table.bootstrapTable('uncheckAll');
    });
  });
})(jQuery);
