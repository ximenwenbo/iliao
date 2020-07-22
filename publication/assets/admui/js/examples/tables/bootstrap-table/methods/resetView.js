/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    // 重新设置表单渲染
    var $div = $('.div-table');
    var $table = $('#table');

    $('#button').click(function() {
      $div.width($div.width() + 50);
      $table.bootstrapTable('resetView');
    });

    $('#button2').click(function() {
      $div.width($div.width() - 50);
      $table.bootstrapTable('resetView');
    });
  });
})(jQuery);
