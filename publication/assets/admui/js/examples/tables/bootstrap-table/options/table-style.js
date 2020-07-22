/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window) {
  // 设置表格行风格
  window.rowStyle = function(row, index) {
    var classes = ['table-active', 'table-success', 'table-info', 'table-warning', 'table-danger'];

    if (index % 2 === 0 && index / 2 < classes.length) {
      return {
        classes: classes[index / 2]
      };
    }
    return {};
  };
})(window);
