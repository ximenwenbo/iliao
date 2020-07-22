/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var $table = $('#table');

    // 分页操作
    $('#button').click(function() {
      $table.bootstrapTable('selectPage', +$('#page').val());
    });

    $('#button2').click(function() {
      $table.bootstrapTable('prevPage');
    });

    $('#button3').click(function() {
      $table.bootstrapTable('nextPage');
    });
  });
})(jQuery);
