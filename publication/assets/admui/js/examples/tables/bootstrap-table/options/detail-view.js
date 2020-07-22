/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  // 格式化处理数据
  window.detailFormatter = function(index, row) {
    var html = [];
    $.each(row, function(key, value) {
      html.push('<div><b>' + key + ':</b> ' + value + '</div>');
    });
    return html.join('');
  };
})(jQuery);
