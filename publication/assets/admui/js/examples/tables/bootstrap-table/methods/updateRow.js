/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  /* eslint no-bitwise: ["error", { "allow": ["~"] }] */

  $(function() {
    $('#button').click(function() {
      var randomId = 100 + ~~(Math.random() * 100);

      // 根据下标更新行信息
      $('#table').bootstrapTable('updateRow', {
        index: 1,
        row: {
          id: randomId,
          name: '条目 ' + randomId,
          price: '￥' + randomId
        }
      });
    });
  });
})(jQuery);
