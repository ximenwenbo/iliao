/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window) {
  // 配置name列
  window.nameFormatter = function(value, row) {
    var icon = row.id % 2 === 0 ? 'glyphicon-star' : 'glyphicon-star-empty';

    return '<i class="glyphicon ' + icon + '"></i> ' + value;
  };

  // 设置price列
  window.priceFormatter = function(value) {
    // 16777215 == ffffff in decimal
    var color = '#' + Math.floor(Math.random() * 6777215).toString(16);
    return (
      '<div  style="color: ' +
      color +
      '">' +
      '<i class="glyphicon glyphicon-usd"></i>' +
      value.substring(1) +
      '</div>'
    );
  };
})(window);
