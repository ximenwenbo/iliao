/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    $('#button').click(function() {
      // 使用下列参数刷新表格
      $('#table').bootstrapTable('refreshOptions', {
        showColumns: true,
        search: true,
        showRefresh: true,
        url: '/public/data/examples/tables/bootstrap-table/data1.json'
      });
    });
  });
})(jQuery);
