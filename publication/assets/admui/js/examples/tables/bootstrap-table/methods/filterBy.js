/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    $('#button').click(function() {
      // 过滤表格中id为1,2,3的行
      $('#table').bootstrapTable('filterBy', {
        id: [1, 2, 3]
      });
    });
  });
})(jQuery);
