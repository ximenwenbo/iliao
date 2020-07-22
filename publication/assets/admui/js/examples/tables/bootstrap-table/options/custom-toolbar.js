/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, $) {
  // 搜索参数
  window.queryParams = function() {
    var params = {};
    $('#toolbar')
      .find('input[name]')
      .each(function() {
        params[$(this).attr('name')] = $(this).val();
      });
    return params;
  };

  // 返回响应数据
  window.responseHandler = function(res) {
    return res.rows;
  };

  $(function() {
    $('#ok').click(function() {
      $('#table').bootstrapTable('refresh');
    });
  });
})(window, jQuery);
