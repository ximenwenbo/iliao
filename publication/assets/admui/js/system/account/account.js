/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  // 获取查询参数
  var GetRequest = function() {
    // 获取url中"?"符后的字串
    var url = window.location.search;
    var theRequest = {};
    var str;
    var strs;
    var i;

    if (url.indexOf('?') !== -1) {
      str = url.substr(1);
      strs = str.split('&');

      for (i = 0; i < strs.length; i++) {
        theRequest[strs[i].split('=')[0]] = unescape(strs[i].split('=')[1]);
      }
    }
    return theRequest;
  };

  // 选中指定tab
  var tabSelected = function() {
    var search = GetRequest();

    switch (search.tab) {
      case 'log':
        $('a[href="#log"]').tab('show');
        break;
      case 'password':
        $('a[href="#password"]').tab('show');
        break;
      case 'display':
        $('a[href="#display"]').tab('show');
        break;
      default:
        break;
    }
  };

  $(function() {
    // 定位tab
    tabSelected();

    $.extend(window.notifyFn, {
      // 扩展账户信息页面时左侧名片&&选项卡消息计数
      messageNum: function(opt) {
        var $navMsg = $('#admui-navbarMessage').find('span.msg-num');
        var $total = $('.msg-number');
        var msgNumber = $navMsg.text();
        var allMsg = Number($total.text());

        $('.tabList>li.news span').text(msgNumber);
        if (opt === '1') {
          $total.text(allMsg + 1);
        }
      },
      unReadMsg: function(count) {
        // 显示未读消息
        $('.tabList>li.news span').text(count);
      }
    });
  });
})(window, document, jQuery);
