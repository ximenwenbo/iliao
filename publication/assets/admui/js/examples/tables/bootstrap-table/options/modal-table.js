/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    // modal显示完成后初始化表格
    $('#modalTable').on('shown.bs.modal', function() {
      $('#table').bootstrapTable('resetView');
    });
  });
})(jQuery);
