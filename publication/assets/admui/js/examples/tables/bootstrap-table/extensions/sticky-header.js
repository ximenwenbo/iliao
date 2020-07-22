/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  // 创建固定表头表格
  function buildTable($el, cells, rows) {
    var i;
    var j;
    var row;
    var columns = [];
    var data = [];
    var stickyHeaderOffsetY = 0;
    var $navbarFixed = $('.navbar-fixed-top');

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

    if ($navbarFixed.css('height')) {
      stickyHeaderOffsetY = +$navbarFixed.css('height').replace('px', '');
    }
    if ($navbarFixed.css('margin-bottom')) {
      stickyHeaderOffsetY += +$('.navbar-fixed-top')
        .css('margin-bottom')
        .replace('px', '');
    }

    $el.bootstrapTable('destroy').bootstrapTable({
      columns: columns,
      data: data,
      search: true,
      stickyHeader: true,
      stickyHeaderOffsetY: stickyHeaderOffsetY + 'px'
    });
  }

  $(function() {
    var $table = $('#table');
    buildTable($table, 20, 50);
  });
})(jQuery);
