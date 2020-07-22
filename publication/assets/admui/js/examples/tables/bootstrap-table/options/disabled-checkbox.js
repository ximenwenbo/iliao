/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window) {
  // 设置formatter列选项状态
  window.stateFormatter = function(value, row, index) {
    if (index === 2) {
      return {
        disabled: true
      };
    }
    if (index === 5) {
      return {
        disabled: true,
        checked: true
      };
    }
    return value;
  };
})(window);
