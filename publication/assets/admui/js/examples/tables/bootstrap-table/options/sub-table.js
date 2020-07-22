/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  // 展开表格
  function expandTable($detail, cells, callback) {
    callback($detail.html('<table></table>').find('table'), cells, 1);
  }

  // 创建表格
  function buildTable($el, cells, rows) {
    var i;
    var j;
    var row;
    var columns = [];
    var data = [];

    for (i = 0; i < cells; i++) {
      columns.push({
        field: 'field' + i,
        title: '单元格 ' + i,
        sortable: true
      });
    }

    for (i = 0; i < rows; i++) {
      row = {};
      for (j = 0; j < cells; j++) {
        row['field' + j] = '行-' + i + '-' + j;
      }
      data.push(row);
    }

    $el.bootstrapTable({
      columns: columns,
      data: data,
      detailView: cells > 1,
      onExpandRow: function(index, row1, $detail) {
        expandTable($detail, cells - 1, buildTable);
      }
    });
  }

  $(function() {
    buildTable($('#table'), 8, 1);
  });
})(jQuery);
