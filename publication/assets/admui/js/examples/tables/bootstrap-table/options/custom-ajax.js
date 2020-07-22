/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window) {
  // 自定义ajax请求
  window.ajaxRequest = function(params) {
    setTimeout(function() {
      params.success({
        total: 100,
        rows: [
          {
            id: 0,
            name: '条目 0',
            price: '￥0'
          }
        ]
      });
    }, 1000);
  };
})(window);
