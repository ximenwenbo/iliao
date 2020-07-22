/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var data = [
      {
        id: 0,
        name: '条目 0',
        price: '￥0'
      },
      {
        id: 1,
        name: '条目 1',
        price: '￥1'
      },
      {
        id: 2,
        name: '条目 2',
        price: '￥2'
      },
      {
        id: 3,
        name: '条目 3',
        price: '￥3'
      },
      {
        id: 4,
        name: '条目 4',
        price: '￥4'
      },
      {
        id: 5,
        name: '条目 5',
        price: '￥5'
      }
    ];
    $('#table').bootstrapTable({data: data});
  });
})(jQuery);
