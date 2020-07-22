/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';
  /* global template, toastr */

  var messagePage = {
    loadMsg: function(page) {
      // 加载当前页通知
      window.top.notifyFn.render();

      $.ajax({
        url: '/admin/user_center/messageAjax',
        dataType: 'JSON',
        type: 'GET',
        data: {pageIndex: page, pageSize: 10},
        success: function(res) {
          var html;
          if (res && typeof res.pageList !== 'undefined') {
            html = template('newMessge', res.pageList);
            $('#messageLists').html(html);
          } else {
            toastr.error(res.msg);
          }
        },
        error: function() {
          toastr.error('服务器异常，请稍后再试！');
        }
      });
    },
    fnExtend: function() {
      // 扩展notifyFn对象，便于socket消息通知中回调操作
      var self = this;

      $.extend(window.top.notifyFn, {
        messagePage: function() {
          // 在消息页面时的操作
          var $adminMsg = $('#messageLists');

          if (!$('.account-msg').hasClass('active')) {
            return;
          }

          if ($adminMsg.children('.no-message').length !== 0 || $('#paging').data('page') === 1) {
            self.loadMsg(1);
          }
        },
        msgStatus: function(Id) {
          // 改变消息状态
          var $element = $('[data-message-id="' + Id + '"]').children('.media');
          var $readTab = $element.children('.media-right').find('a');
          var $title = $element.children('.media-body').find('i');

          $title.remove();
          $readTab.attr('title', '删除');
          $readTab.removeClass('wb-check').addClass('wb-close');
        }
      });
    },
    run: function() {
      var self = this;
      var fn = window.top.notifyFn.unReadMsg;

      this.loadMsg(1);
      this.fnExtend();

      if (fn && $.isFunction(fn)) {
        fn(
          $('#admui-navbarMessage')
            .find('span.msg-num')
            .text()
        );
      }

      // 查看当前消息
      $(document).on('click', '.news-list', function(e) {
        var $that = $(this);
        var opts = $that.parents('.list-group-item').data();

        opts.readFlag = $that.find('i.icon').length <= 0;
        window.top.notifyFn.readMsg(opts);
        e.preventDefault();
      });

      // 直接更改状态为已读
      $(document).on('click', '.wb-check', function(e) {
        e.preventDefault();

        window.top.notifyFn.readMsg(
          $(this)
            .parents('[data-message-id]')
            .data()
        );
      });

      // 删除已读消息
      $(document).on('click', '.wb-close', function(e) {
        var $item = $(this).closest('.list-group-item');
        var $total = $('.msg-number');
        var total = Number($total.text());
        var page = $('#paging').data('page');
        var messageId = $item.data('messageId');

        window.top.layer.confirm('你确定要删除吗？', function(index) {
          $.ajax({
            url: '/message/' + messageId,
            type: 'DELETE',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                self.loadMsg(page);
                total -= 1;
                $total.text(total);

                toastr.clear();
                toastr.success(res.msg);
                window.top.layer.close(index);
              } else {
                toastr.error(res.msg);
              }
            },
            error: function() {
              toastr.error('服务器异常，请配合后端程序使用');
            }
          });
        });
        e.preventDefault();
      });

      // 分页
      $(document).on('click', '.previous, .next', function() {
        var $item = $(this);
        var page = $item.parents('#paging').data('page');
        var maxPage = $item.parents('#paging').data('max-page');
        var $prev = $('.previous');
        var $next = $('.next');

        if ($item.is('.previous')) {
          if ($next.is(':hidden')) {
            $next.show();
          }
          if (page === 2) {
            $prev.hide();
          }
          page -= 1;
        } else if ($item.is('.next')) {
          if ($prev.is(':hidden')) {
            $prev.show();
          }
          if (page === maxPage - 1) {
            $next.hide();
          }
          page += 1;
        }
        self.loadMsg(page);
      });
    }
  };

  template.defaults.imports.iconType = function(type) {
    switch (type) {
      case 'SYSTEM':
        return 'fa-desktop system';
      case 'TASK':
        return 'fa-tasks task';
      case 'SETTING':
        return 'fa-cog setting';
      case 'EVENT':
        return 'fa-calendar event';
      default:
        return 'fa-comment-o other';
    }
  };

  template.defaults.imports.timeMsg = function(date) {
    var currentTime = new Date();
    // ios new Data兼容
    var arr = date.split(/[- :/]/);
    var msgTime = new Date(arr[0], arr[1] - 1, arr[2], arr[3], arr[4], arr[5]);

    return window.top.notifyFn.timeDistance(msgTime, currentTime);
  };

  $(function() {
    messagePage.run();
  });
})(window, document, jQuery);
