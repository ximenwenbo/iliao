/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    // 数据导出
    $('#toolbar')
      .find('select')
      .change(function() {
        $('#table')
          .bootstrapTable('destroy')
          .bootstrapTable({
            exportDataType: $(this).val()
          });
      });
  });
})(jQuery);
