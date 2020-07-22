/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $(document).on('shown.bs.tab', '#DTExample a[data-toggle="tab"]', function() {
    $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
  });

  $('#DTExample table.dataTable').DataTable(
    $.concatCpt('dataTable', {
      ajax: $.configs.ctx + '/public/data/examples/tables/data-tables/dt-ajax.json',
      scrollY: 200,
      scrollCollapse: true,
      paging: false
    })
  );

  // 将搜索应用于第二个表格
  $('#myTable2')
    .DataTable()
    .search('北京')
    .draw();
})(window, document, jQuery);
