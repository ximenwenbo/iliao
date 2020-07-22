/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var $table = $('#table');
    var $buttons = $('button');

    // 中文表格
    $('#zh_Btn').click(function() {
      $buttons.removeClass('active');
      $(this).addClass('active');

      $table.bootstrapTable('changeLocale', '中文');
      $table.bootstrapTable('changeTitle', {
        id: '代码',
        name: '项目名称',
        price: '项目金额'
      });
    });

    // 英文表格
    $('#us_Btn').click(function() {
      $buttons.removeClass('active');
      $(this).addClass('active');

      $table.bootstrapTable('changeLocale', '英文');
      $table.bootstrapTable('changeTitle', {
        id: 'ID',
        name: 'Item Name',
        price: 'Item Price'
      });
    });
  });
})(jQuery);
