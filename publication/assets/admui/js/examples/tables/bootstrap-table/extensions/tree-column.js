/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    // 初始化树形列表格
    $('#table').bootstrapTable({
      treeShowField: 'name',
      url: '/public/data/examples/tables/bootstrap-table/data7.json'
    });
  });
})(jQuery);
