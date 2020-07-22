/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window) {
  // 创建格式化函数
  window.operateFormatter = function(value) {
    return [
      '<div class="float-left">',
      '<a href="https://github.com/wenzhixin/' + value + '" target="_blank">' + value + '</a>',
      '</div>',
      '<div class="float-right">',
      '<a class="like" href="javascript:;" title="Like">',
      '<i class="icon fa-heart"></i>',
      '</a>  ',
      '<a class="remove" href="javascript:;" title="Remove">',
      '<i class="icon fa-remove"></i>',
      '</a>',
      '</div>'
    ].join('');
  };

  // 创建表格事件
  window.operateEvents = {
    'click .like': function(e, value, row) {
      alert('You click like action, row: ' + JSON.stringify(row));
    },
    'click .remove': function(e, value, row) {
      alert('You click remove action, row: ' + JSON.stringify(row));
    }
  };
})(window);
