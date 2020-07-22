/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  /* global toastr */

  $(function() {
    $('#button').click(function() {
      // 根据uniqueId获取行信息
      toastr.info('详细信息请在控制台console打印信息中查看');
      console.log('getRowByUniqueId: ', $('#table').bootstrapTable('getRowByUniqueId', 1));
    });
  });
})(jQuery);
