/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, $) {
  $(function() {
    var $table = $('#table');

    window.detailFormatter = function(index, row) {
      var html = [];

      console.log(index);
      $.each(row, function(key, value) {
        html.push('<div><b>' + key + ':</b> ' + value + '</div>');
      });
      return html.join('');
    };

    $('#button').click(function() {
      // 展开表格行信息
      $table.bootstrapTable('expandRow', 1);
    });
    $('#button2').click(function() {
      // 收起表格行信息
      $table.bootstrapTable('collapseRow', 1);
    });
  });
})(window, jQuery);
