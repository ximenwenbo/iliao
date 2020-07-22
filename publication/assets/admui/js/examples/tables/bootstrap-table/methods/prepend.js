/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  /* eslint no-bitwise: ["error", { "allow": ["~"] }] */

  function randomData() {
    var startId = ~~(Math.random() * 100);
    var rows = [];
    var i = 0;

    for (; i < 10; i++) {
      rows.push({
        id: startId + i,
        name: 'test' + (startId + i),
        price: '￥' + (startId + i)
      });
    }
    return rows;
  }

  $(function() {
    $('#button').click(function() {
      // 插入新的表格数据
      $('#table').bootstrapTable('prepend', randomData());
    });
  });
})(jQuery);
