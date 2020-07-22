/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  /* global toastr */

  $(function() {
    $('#button').click(function() {
      // 获取表格初始化参数
      toastr.info('详细信息请在控制台console打印信息中查看');
      console.log('getOptions: ', $('#table').bootstrapTable('getOptions'));
    });
  });
})(jQuery);
