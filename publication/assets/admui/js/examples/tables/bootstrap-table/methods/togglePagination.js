/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    // 切换分页选项
    $('#button').click(function() {
      $('#table').bootstrapTable('togglePagination');
    });
  });
})(jQuery);
